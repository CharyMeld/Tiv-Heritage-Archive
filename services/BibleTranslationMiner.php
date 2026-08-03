<?php
/**
 * Bible Translation Miner
 *
 * Uses aligned Tiv–English Bible verses to feed the translation engine:
 *
 *  Feature 1 — Verse lookup       : exact/near verse match (called by TranslationEngine)
 *  Feature 2 — Phrase sync        : push aligned verse pairs into translation_phrases
 *  Feature 3 — Word mining        : extract Tiv↔English word pairs into daily_words
 *  Feature 4 — Missing word check : resolve unknown tokens against Bible verses
 *
 * Mining runs automatically when a chapter is saved, and is also available
 * as an admin batch operation.
 */

class BibleTranslationMiner
{
    private PDO $db;

    // English stop words — not useful as stand-alone translation pairs
    private const EN_STOP = [
        'the','a','an','is','are','was','were','be','been','being','have','has',
        'had','do','does','did','will','would','could','should','may','might',
        'shall','to','of','in','on','at','by','for','with','about','into','from',
        'up','out','as','it','its','this','that','these','those','and','or','but',
        'not','no','i','me','my','we','our','you','your','he','his','she','her',
        'they','their','them','us','him','so','if','then','when','where','which',
        'who','whom','what','how','all','one','two','said','says','also','more',
        'than','so','just','very','can','now','he','lord','god','jesus','christ',
    ];

    // Tiv stop words / grammatical particles
    private const TIV_STOP = [
        'a','u','i','sha','la','ne','ngu','yo','kpam','ga','ka','nan','ma',
        'na','ve','yô','maa','kà','er','ér','nande','lô','tô','mba',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ═══════════════════════════════════════════════════════════════════
    // FEATURE 1 — Verse Lookup (used by TranslationEngine at query time)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Find a Bible verse whose English text closely matches $input.
     * Returns ['english_web'=>..., 'tiv'=>..., 'ref'=>..., 'confidence'=>...] or null.
     */
    public function lookupVerse(string $input, string $sourceLang): ?array
    {
        $clean = $this->normalise($input);
        if (mb_strlen($clean) < 8) return null;
        // Don't match single words or short phrases — those belong to the dictionary
        if (str_word_count($clean) < 3) return null;

        $col = $sourceLang === 'tiv' ? 'tiv' : 'english_web';

        // 1. Exact match
        $stmt = $this->db->prepare(
            "SELECT *, CONCAT(book,' ',chapter,':',verse) AS ref
             FROM bible_verses
             WHERE LOWER({$col}) = ? AND tiv IS NOT NULL AND tiv != ''
             LIMIT 1"
        );
        $stmt->execute([$clean]);
        $row = $stmt->fetch();
        if ($row) {
            return $this->verseResult($row, 98);
        }

        // 2. Input is fully contained in a verse (user typed partial verse)
        $stmt = $this->db->prepare(
            "SELECT *, CONCAT(book,' ',chapter,':',verse) AS ref
             FROM bible_verses
             WHERE LOWER({$col}) LIKE ? AND tiv IS NOT NULL AND tiv != ''
             ORDER BY LENGTH({$col}) ASC
             LIMIT 1"
        );
        $stmt->execute(['%' . $clean . '%']);
        $row = $stmt->fetch();
        if ($row) {
            $similarity = mb_strlen($clean) / mb_strlen($this->normalise($row[$col]));
            if ($similarity >= 0.75) {
                return $this->verseResult($row, (int) round(70 + $similarity * 20));
            }
        }

        return null;
    }

    private function verseResult(array $row, int $confidence): array
    {
        return [
            'tiv'          => $row['tiv'],
            'english_web'  => $row['english_web'],
            'ref'          => $row['ref'],
            'book'         => $row['book'],
            'book_key'     => $row['book_key'],
            'chapter'      => (int) $row['chapter'],
            'verse'        => (int) $row['verse'],
            'confidence'   => $confidence,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // FEATURE 2 — Phrase Sync
    // Push aligned verse pairs into translation_phrases so the engine
    // finds them in its Step 2 phrase-match lookup.
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Sync a batch of verse rows (already fetched) to translation_phrases.
     * Skips verses with no Tiv text or very long verses (> 300 chars).
     * Returns count of rows inserted/updated.
     */
    public function syncPhrasesFromVerses(array $verses): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO translation_phrases
                (source_text, source_language, target_text, target_language,
                 context_tag, confidence_score, status)
             VALUES (?, 'english', ?, 'tiv', 'bible', 88, 'active')
             ON DUPLICATE KEY UPDATE
                target_text      = IF(VALUES(target_text) != '', VALUES(target_text), target_text),
                confidence_score = GREATEST(confidence_score, 88),
                status           = 'active'"
        );

        $stmtRev = $this->db->prepare(
            "INSERT INTO translation_phrases
                (source_text, source_language, target_text, target_language,
                 context_tag, confidence_score, status)
             VALUES (?, 'tiv', ?, 'english', 'bible', 88, 'active')
             ON DUPLICATE KEY UPDATE
                target_text      = IF(VALUES(target_text) != '', VALUES(target_text), target_text),
                confidence_score = GREATEST(confidence_score, 88),
                status           = 'active'"
        );

        $count = 0;
        foreach ($verses as $v) {
            if (empty($v['tiv']) || empty($v['english_web'])) continue;
            if (mb_strlen($v['english_web']) > 300) continue; // skip very long verses

            $eng = trim($v['english_web']);
            $tiv = trim($v['tiv']);

            $stmt->execute([$eng, $tiv]);
            $stmtRev->execute([$tiv, $eng]);
            $count++;
        }
        return $count;
    }

    /**
     * Batch-sync ALL aligned verses in the database to translation_phrases.
     */
    public function syncAllPhrases(): int
    {
        $stmt = $this->db->query(
            "SELECT english_web, tiv FROM bible_verses
             WHERE tiv IS NOT NULL AND tiv != '' AND LENGTH(english_web) <= 300"
        );
        return $this->syncPhrasesFromVerses($stmt->fetchAll());
    }

    // ═══════════════════════════════════════════════════════════════════
    // FEATURE 3 — Word Mining
    // Extract likely Tiv↔English word pairs from short aligned verses.
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Mine word pairs from a batch of verse rows.
     * Returns count of new word pairs inserted into daily_words.
     */
    public function mineWordPairsFromVerses(array $verses): int
    {
        $pairs  = $this->extractWordPairs($verses);
        return $this->insertWordPairs($pairs);
    }

    /**
     * Batch-mine ALL aligned verses in the database.
     */
    public function mineAllWordPairs(): int
    {
        $stmt = $this->db->query(
            "SELECT english_web, tiv FROM bible_verses
             WHERE tiv IS NOT NULL AND tiv != ''"
        );
        $verses = $stmt->fetchAll();
        $pairs  = $this->extractWordPairs($verses);
        return $this->insertWordPairs($pairs);
    }

    /**
     * Build a co-occurrence frequency table from aligned verse pairs,
     * then keep only pairs where one Tiv word dominates for a given English word.
     *
     * Returns [['english'=>..., 'tiv'=>..., 'confidence'=>...], ...]
     */
    private function extractWordPairs(array $verses): array
    {
        // freq[eng_word][tiv_word] = co-occurrence count
        $freq     = [];
        $engTotal = []; // total verse count for each English word

        foreach ($verses as $v) {
            if (empty($v['tiv']) || empty($v['english_web'])) continue;

            $engTokens = $this->tokenise($v['english_web'], 'english');
            $tivTokens = $this->tokenise($v['tiv'], 'tiv');

            if (empty($engTokens) || empty($tivTokens)) continue;

            // Only mine from "short" verse pairs (≤ 8 content words each)
            // to reduce noise from long ambiguous sentences
            if (count($engTokens) > 8 || count($tivTokens) > 10) continue;

            foreach ($engTokens as $ew) {
                $engTotal[$ew] = ($engTotal[$ew] ?? 0) + 1;
                foreach ($tivTokens as $tw) {
                    $freq[$ew][$tw] = ($freq[$ew][$tw] ?? 0) + 1;
                }
            }
        }

        $pairs = [];
        foreach ($freq as $ew => $tivCounts) {
            if (($engTotal[$ew] ?? 0) < 2) continue; // need ≥ 2 co-occurrences

            arsort($tivCounts);
            $topTiv   = array_key_first($tivCounts);
            $topCount = $tivCounts[$topTiv];
            $total    = $engTotal[$ew];

            // Dominance: top Tiv word appears in ≥ 55% of verses containing this English word
            $dominance = $topCount / $total;
            if ($dominance < 0.55) continue;

            // Skip very short tokens (likely noise)
            if (mb_strlen($ew) < 2 || mb_strlen($topTiv) < 2) continue;

            $confidence = (int) min(92, round(60 + $dominance * 35));

            $pairs[] = [
                'english'    => $ew,
                'tiv'        => $topTiv,
                'confidence' => $confidence,
                'occurrences'=> $topCount,
            ];
        }

        return $pairs;
    }

    /**
     * Insert extracted word pairs into daily_words (skip if already exists).
     */
    private function insertWordPairs(array $pairs): int
    {
        if (empty($pairs)) return 0;

        // Check existing entries to avoid duplicates
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM daily_words
             WHERE LOWER(tiv_word) = ? AND LOWER(english_meaning) = ?"
        );
        $insert = $this->db->prepare(
            "INSERT INTO daily_words
                (tiv_word, english_meaning, part_of_speech, category, is_active)
             VALUES (?, ?, 'noun', 'bible', 1)"
        );

        $count = 0;
        foreach ($pairs as $p) {
            $check->execute([mb_strtolower($p['tiv']), mb_strtolower($p['english'])]);
            if ($check->fetchColumn() > 0) continue;

            $insert->execute([$p['tiv'], $p['english']]);
            $count++;
        }

        return $count;
    }

    // ═══════════════════════════════════════════════════════════════════
    // FEATURE 4 — Missing Word Resolution
    // Find Bible verses that contain an unknown token, to give context.
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Given an unknown word in the source language, find Bible verses
     * that contain it AND have a translation, returning up to $limit results.
     * Used by TranslationEngine to annotate missing-word suggestions.
     */
    public function findBibleContextForWord(string $word, string $sourceLang, int $limit = 3): array
    {
        $col = $sourceLang === 'tiv' ? 'tiv' : 'english_web';
        $otherCol = $sourceLang === 'tiv' ? 'english_web' : 'tiv';

        $stmt = $this->db->prepare(
            "SELECT CONCAT(book,' ',chapter,':',verse) AS ref,
                    {$col} AS source_text,
                    {$otherCol} AS translation
             FROM bible_verses
             WHERE LOWER({$col}) LIKE ?
               AND {$otherCol} IS NOT NULL AND {$otherCol} != ''
             ORDER BY LENGTH({$col}) ASC
             LIMIT ?"
        );
        $stmt->execute(['% ' . mb_strtolower($word) . ' %', $limit]);
        return $stmt->fetchAll();
    }

    /**
     * When a missing word is first logged, check Bible verses.
     * If a clear translation is found, immediately mark the word as resolved
     * (status = 'approved') in the missing_words table.
     */
    public function autoResolveMissingWord(int $missingWordId, string $word, string $sourceLang): bool
    {
        $col    = $sourceLang === 'tiv' ? 'tiv' : 'english_web';
        $retCol = $sourceLang === 'tiv' ? 'english_web' : 'tiv';

        // Find short verses containing exactly this word
        $stmt = $this->db->prepare(
            "SELECT {$retCol} AS translation, {$col} AS source_verse
             FROM bible_verses
             WHERE LOWER({$col}) LIKE ?
               AND {$retCol} IS NOT NULL AND {$retCol} != ''
               AND CHAR_LENGTH({$col}) < 80
             ORDER BY CHAR_LENGTH({$col}) ASC
             LIMIT 1"
        );
        $stmt->execute(['% ' . mb_strtolower($word) . ' %']);
        $row = $stmt->fetch();

        if (!$row) return false;

        // Only auto-resolve if the verse is short (high confidence it's relevant)
        $wordCount = str_word_count($row['source_verse'] ?? '');
        if ($wordCount > 6) return false;

        try {
            $upd = $this->db->prepare(
                "UPDATE missing_words
                 SET approved_meaning = ?, status = 'has_suggestions'
                 WHERE id = ? AND status = 'missing'"
            );
            $upd->execute([$row['translation'], $missingWordId]);
            return $upd->rowCount() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Look up a single word in the Bible bilingual corpus.
     * Returns the most likely translation for the word, or null.
     * Used by TranslationEngine::refineLocally() to resolve missing tokens.
     */
    public function lookupWordInBible(string $word, string $sourceLang): ?string
    {
        $col    = $sourceLang === 'tiv' ? 'tiv' : 'english_web';
        $retCol = $sourceLang === 'tiv' ? 'english_web' : 'tiv';
        $like   = '% ' . mb_strtolower($word) . ' %';

        // Find short bilingual verses where the word appears in isolation
        $stmt = $this->db->prepare(
            "SELECT {$retCol} AS translated, CHAR_LENGTH({$col}) AS src_len
             FROM bible_verses
             WHERE LOWER({$col}) LIKE ?
               AND {$retCol} IS NOT NULL AND {$retCol} != ''
               AND CHAR_LENGTH({$col}) < 60
             ORDER BY src_len ASC
             LIMIT 3"
        );
        $stmt->execute([$like]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) return null;

        // Pick the shortest matching verse translation (most likely a clean word pair)
        $best = $rows[0]['translated'] ?? '';
        $bestLen = mb_strlen($best);
        foreach ($rows as $r) {
            $len = mb_strlen($r['translated']);
            if ($len < $bestLen) {
                $best    = $r['translated'];
                $bestLen = $len;
            }
        }

        // Only return if the result is short enough to be a meaningful word/phrase
        if ($bestLen > 80) return null;

        return trim($best);
    }

    // ═══════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════

    public function normalise(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace("/[^\p{L}\p{N}\s'\-]/u", '', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function tokenise(string $text, string $lang): array
    {
        $stop  = $lang === 'tiv' ? self::TIV_STOP : self::EN_STOP;
        $clean = preg_replace("/[^\p{L}\s'\-]/u", ' ', mb_strtolower(trim($text), 'UTF-8'));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_filter($words, function ($w) use ($stop) {
            return mb_strlen($w) >= 2 && !in_array($w, $stop, true);
        }));
    }
}
