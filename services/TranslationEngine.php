<?php
/**
 * Tiv Translation Engine v2
 *
 * Database-driven, rule-based Tiv <-> English translation.
 * Layered matching priority:
 *   1. Proverbs (exact match)
 *   2. Exact phrase match
 *   3. Partial / sub-phrase match
 *   4. Dictionary word match
 *   5. Category content (names, foods, plants, festivals, animals)
 *   6. Bible verse match
 *   7. Word-by-word rebuild with grammar rule application
 *   8. Missing word tracking for unknown tokens
 *
 * NLLB refinement runs after the full DB pipeline (Step 9) so curated
 * dictionary and phrase matches always take priority. NLLB only refines
 * word-by-word and unmatched results into natural-sounding Tiv sentences.
 */

class TranslationEngine
{
    private PDO             $db;
    private ?NllbTranslator $nllb = null;

    // Free tier: max daily translations per guest/user
    const FREE_DAILY_LIMIT_GUEST = 5;
    const FREE_DAILY_LIMIT_USER  = 15;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        require_once BASE_PATH . '/services/NllbTranslator.php';
        $nllb = new NllbTranslator();
        if ($nllb->isAvailable()) {
            $this->nllb = $nllb;
        }

        require_once BASE_PATH . '/services/BibleTranslationMiner.php';
        $this->miner = new BibleTranslationMiner($db);
    }

    private BibleTranslationMiner $miner;

    // -------------------------------------------------------
    // PUBLIC: Main translate method
    // -------------------------------------------------------

    /**
     * Translate input text and return a full result array.
     *
     * @param string   $input       Raw user input
     * @param string   $sourceLang  'tiv' or 'english'
     * @param string   $targetLang  'tiv' or 'english'
     * @param int|null $userId      Logged-in user ID (null = guest)
     * @param bool     $saveLog     Whether to persist a translation_log entry
     * @return array
     */
    public function translate(
        string $input,
        string $sourceLang,
        string $targetLang,
        ?int $userId = null,
        bool $saveLog = true
    ): array {
        // Normalise line endings
        $input = str_replace("\r\n", "\n", $input);
        $input = str_replace("\r", "\n", $input);
        $input = trim($input);

        if ($input === '') {
            return $this->emptyResult();
        }

        // ── Multi-line input: translate segment-by-segment so that:
        //    (a) each sentence/paragraph gets a real shot at DB phrase/word matching, and
        //    (b) paragraph structure is preserved in the output.
        if (strpos($input, "\n") !== false) {
            return $this->translateMultiLine($input, $sourceLang, $targetLang, $userId, $saveLog);
        }

        // ── Single line — run the full pipeline ──────────────────────────────
        $result = $this->translateSegment($input, $sourceLang, $targetLang);

        // Ensure all required keys exist
        $result = array_merge($this->emptyResult(), $result);
        $result['input_text']      = $input;
        $result['source_language'] = $sourceLang;
        $result['target_language'] = $targetLang;

        // Record missing words to DB for suggest links
        if (!empty($result['missing_tokens'])) {
            $result['missing_word_ids'] = $this->saveMissingWords(
                $result['missing_tokens'], $sourceLang, $targetLang
            );
        }

        // Save translation log
        $logId = null;
        if ($saveLog) {
            $logId = $this->saveLog($result, $userId);
        }
        $result['log_id'] = $logId;

        return $result;
    }

    // -------------------------------------------------------
    // Multi-line dispatcher
    // Splits on line breaks, translates each non-blank segment
    // independently so DB resources are consulted per sentence,
    // then reassembles preserving blank lines (paragraph breaks).
    // -------------------------------------------------------

    private function translateMultiLine(
        string $input,
        string $sourceLang,
        string $targetLang,
        ?int $userId,
        bool $saveLog
    ): array {
        $lines = explode("\n", $input);

        $outputLines    = [];
        $allMissing     = [];
        $allWordResults = [];
        $confidences    = [];
        $matchTypes     = [];

        foreach ($lines as $line) {
            // Preserve blank lines exactly (paragraph separators)
            if (trim($line) === '') {
                $outputLines[] = '';
                continue;
            }

            $seg = $this->translateSegment($line, $sourceLang, $targetLang);

            $outputLines[]    = $seg['translated_text'] ?? $line;
            $confidences[]    = $seg['confidence_score'] ?? 0;
            $matchTypes[]     = $seg['match_type'] ?? 'none';
            $allMissing       = array_merge($allMissing,     $seg['missing_tokens']  ?? []);
            $allWordResults   = array_merge($allWordResults, $seg['word_results']    ?? []);
        }

        // Reassemble — blank lines become paragraph breaks
        $translatedText = implode("\n", $outputLines);

        // Aggregate confidence: average of non-zero scores
        $nonZero = array_filter($confidences, fn($c) => $c > 0);
        $avgConf = count($nonZero) > 0 ? (int) round(array_sum($nonZero) / count($nonZero)) : 0;

        // Dominant match type (most frequent)
        $dominantType = 'none';
        if (!empty($matchTypes)) {
            $freq = array_count_values($matchTypes);
            arsort($freq);
            $dominantType = array_key_first($freq);
        }

        $result = array_merge($this->emptyResult(), [
            'translated_text'  => $translatedText,
            'match_type'       => $dominantType,
            'confidence_score' => $avgConf,
            'input_text'       => $input,
            'source_language'  => $sourceLang,
            'target_language'  => $targetLang,
            'word_results'     => $allWordResults,
            'missing_tokens'   => array_values(array_unique($allMissing)),
        ]);

        if (!empty($result['missing_tokens'])) {
            $result['missing_word_ids'] = $this->saveMissingWords(
                $result['missing_tokens'], $sourceLang, $targetLang
            );
        }

        $logId = null;
        if ($saveLog) {
            $logId = $this->saveLog($result, $userId);
        }
        $result['log_id'] = $logId;

        return $result;
    }

    // -------------------------------------------------------
    // Core single-line translation pipeline (no logging)
    // Steps 1-9: proverb → phrase → partial phrase → word → category →
    //            bible → word-by-word → grammar rules → NLLB refinement
    // -------------------------------------------------------

    private function translateSegment(string $line, string $sourceLang, string $targetLang): array
    {
        $line       = trim($line);
        $normalized = $this->normalize($line);

        if ($normalized === '') {
            return $this->emptyResult();
        }

        $result = null;

        // Step 1 — Proverb match
        $result = $this->matchProverb($normalized, $sourceLang, $targetLang);
        if ($result) {
            $result['match_type']       = 'proverb';
            $result['confidence_score'] = 95;
        }

        // Step 2 — Exact phrase match
        if (!$result) {
            $result = $this->matchPhrase($normalized, $sourceLang, $targetLang);
            if ($result) {
                $result['match_type']       = 'phrase';
                $result['confidence_score'] = $result['confidence_score'] ?? 90;
            }
        }

        // Step 3 — Partial / sub-phrase match
        if (!$result) {
            $result = $this->matchPartialPhrase($normalized, $sourceLang, $targetLang);
            if ($result) {
                $result['match_type']       = 'phrase';
                $result['confidence_score'] = $result['confidence_score'] ?? 80;
            }
        }

        // Step 4 — Dictionary word match
        if (!$result) {
            $result = $this->matchWord($normalized, $sourceLang, $targetLang);
            if ($result) {
                $result['match_type']       = 'word';
                $result['confidence_score'] = 85;
            }
        }

        // Step 5 — Category content match
        if (!$result) {
            $result = $this->matchCategory($normalized, $sourceLang, $targetLang);
            if ($result) {
                $result['match_type']       = 'category';
                $result['confidence_score'] = 80;
            }
        }

        // Step 6 — Bible verse match (last curated resort before word-by-word)
        if (!$result) {
            $result = $this->matchBibleVerse($line, $sourceLang, $targetLang);
        }

        // Step 7 — Word-by-word fallback
        if (!$result) {
            $result = $this->wordByWord($normalized, $sourceLang, $targetLang, $line);
        }

        // Step 8 — Apply grammar rules + cleanup to all non-AI results
        $isAiResult = in_array($result['match_type'] ?? '', ['ai', 'ai_refined'], true);
        if (!$isAiResult && !empty($result['translated_text'])) {
            $result['translated_text'] = $this->applyRules(
                $result['translated_text'],
                $sourceLang,
                $targetLang
            );
            $result['translated_text'] = $this->cleanupOutput($result['translated_text']);
        }

        // Step 9 — NLLB refinement pass
        // Runs after the full DB pipeline so curated matches are found first.
        // For word-by-word and "none" results, NLLB receives the original source
        // text and produces a more natural translation. Placeholder protection
        // inside translateWithNllb() preserves any DB-curated phrases.
        $needsRefinement = in_array($result['match_type'] ?? '', ['word_by_word', 'none'], true);
        if ($needsRefinement) {
            $refined = $this->translateWithNllb($line, $sourceLang, $targetLang);
            if ($refined !== null) {
                $result = $refined;
            }
        }

        return $result ?? $this->emptyResult();
    }

    // -------------------------------------------------------
    // ACCESS CONTROL
    // -------------------------------------------------------

    public function canTranslate(?int $userId): bool
    {
        if ($userId !== null && $this->hasPaidAccess($userId)) {
            return true;
        }

        $limit = ($userId !== null) ? self::FREE_DAILY_LIMIT_USER : self::FREE_DAILY_LIMIT_GUEST;
        $used  = $this->getDailyUsage($userId);

        return $used < $limit;
    }

    public function getDailyUsage(?int $userId): int
    {
        if ($userId !== null) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM translation_logs
                 WHERE user_id = ? AND DATE(created_at) = CURDATE()"
            );
            $stmt->execute([$userId]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM translation_logs
                 WHERE user_id IS NULL AND ip_address = ? AND DATE(created_at) = CURDATE()"
            );
            $stmt->execute([$ip]);
        }
        return (int) $stmt->fetchColumn();
    }

    public function hasPaidAccess(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM translation_payments
             WHERE user_id = ?
               AND status = 'active'
               AND (
                   (payment_type IN ('subscription_monthly','subscription_yearly') AND valid_until > NOW())
                   OR
                   (payment_type = 'credit_pack' AND credits_remaining > 0)
               )"
        );
        $stmt->execute([$userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function remainingFree(?int $userId): int
    {
        if ($userId !== null && $this->hasPaidAccess($userId)) {
            return PHP_INT_MAX;
        }
        $limit = ($userId !== null) ? self::FREE_DAILY_LIMIT_USER : self::FREE_DAILY_LIMIT_GUEST;
        return max(0, $limit - $this->getDailyUsage($userId));
    }

    // -------------------------------------------------------
    // STEP 3: NLLB AI Translation (primary translator)
    // -------------------------------------------------------

    /**
     * Translate $input via NLLB-200 with curated-phrase protection.
     *
     * Strategy:
     *  1. Load all active (source → target) phrases sorted longest-first.
     *  2. Replace matches in the input with short placeholders (NLLBPH0, NLLBPH1…)
     *     so they pass through NLLB untouched.
     *  3. Call NLLB on the placeholder-replaced text.
     *  4. Restore placeholders with the curated target translations.
     *  5. Apply grammar rules and final cleanup.
     *
     * Returns null if NLLB is not configured or the API call fails,
     * allowing the engine to fall through to the rule-based fallbacks.
     */
    private function translateWithNllb(string $input, string $sourceLang, string $targetLang): ?array
    {
        if ($this->nllb === null) {
            return null;
        }

        // 1. Load curated phrases (longest first for greedy left-to-right matching)
        $phrases = $this->loadActivePhrases($sourceLang, $targetLang);

        // 2. Replace known source phrases with placeholders
        $placeholders  = [];   // ph → curated target text
        $protectedText = $input;

        foreach ($phrases as $i => $phrase) {
            $src = trim($phrase['source_text']);
            if ($src === '') continue;

            // Case-insensitive, replace first occurrence only
            if (stripos($protectedText, $src) !== false) {
                $ph = 'NLLBPH' . $i;
                $placeholders[$ph] = trim($phrase['target_text']);
                $protectedText = preg_replace('/' . preg_quote($src, '/') . '/iu', $ph, $protectedText, 1);
            }
        }

        // 3. Call NLLB
        $nllbRaw = $this->nllb->translate($protectedText, $sourceLang, $targetLang);
        if ($nllbRaw === null) {
            return null;   // API failed — fall through to rule-based path
        }

        // 4. Restore placeholders → curated translations
        $correctionsApplied = 0;
        $output = $nllbRaw;
        foreach ($placeholders as $ph => $curatedTarget) {
            if (stripos($output, $ph) !== false) {
                $output = str_ireplace($ph, $curatedTarget, $output);
                $correctionsApplied++;
            }
            // If NLLB mangled a placeholder, skip it — the AI handled that segment.
        }

        // 5. Apply grammar rules and cleanup
        $output = $this->applyRules($output, $sourceLang, $targetLang);
        $output = $this->cleanupOutput($output);

        if ($output === '') {
            return null;
        }

        // Confidence: base 72, +3 per phrase correction (capped at 85)
        $confidence = min(85, 72 + ($correctionsApplied * 3));
        $matchType  = $correctionsApplied > 0 ? 'ai_refined' : 'ai';
        $explanation = $correctionsApplied > 0
            ? "AI translation (NLLB-200) refined with {$correctionsApplied} curated phrase correction(s)."
            : 'AI translation (NLLB-200).';

        return [
            'translated_text'  => $output,
            'literal_meaning'  => '',
            'cultural_meaning' => '',
            'usage_context'    => '',
            'source_table'     => 'nllb',
            'source_id'        => null,
            'category'         => 'ai',
            'explanation'      => $explanation,
            'match_type'       => $matchType,
            'confidence_score' => $confidence,
            'word_results'     => [],
            'missing_tokens'   => [],
        ];
    }

    /**
     * Load all active translation phrases for a given direction,
     * sorted longest-first so greedy phrase matching takes precedence.
     */
    private function loadActivePhrases(string $sourceLang, string $targetLang): array
    {
        $stmt = $this->db->prepare(
            "SELECT source_text, target_text FROM translation_phrases
             WHERE source_language = ? AND target_language = ? AND status = 'active'
             ORDER BY LENGTH(source_text) DESC"
        );
        $stmt->execute([$sourceLang, $targetLang]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // STEP 1.5: Bible Verse Match  (Feature 1)
    // -------------------------------------------------------

    private function matchBibleVerse(string $input, string $sourceLang, string $targetLang): ?array
    {
        $hit = $this->miner->lookupVerse($input, $sourceLang);
        if (!$hit) return null;

        $translated = $sourceLang === 'tiv' ? $hit['english_web'] : $hit['tiv'];
        if (empty($translated)) return null;

        return [
            'translated_text'  => $translated,
            'literal_meaning'  => $translated,
            'cultural_meaning' => '',
            'usage_context'    => '',
            'source_table'     => 'bible_verses',
            'source_id'        => null,
            'category'         => 'bible',
            'explanation'      => 'Bible — ' . $hit['ref'],
            'match_type'       => 'bible',
            'confidence_score' => $hit['confidence'],
            'word_results'     => [],
            'missing_tokens'   => [],
        ];
    }

    // -------------------------------------------------------
    // STEP 1: Proverb Match
    // -------------------------------------------------------

    private function matchProverb(string $normalized, string $sourceLang, string $targetLang): ?array
    {
        $col = ($sourceLang === 'tiv') ? 'tiv_text' : 'english_translation';

        // Exact match only.
        // Fuzzy / substring proverb matching was removed because it returned the
        // full proverb translation for inputs that only partially matched, injecting
        // text into the output that was never in the original input.
        $stmt = $this->db->prepare(
            "SELECT * FROM tiv_proverbs WHERE LOWER({$col}) = ? LIMIT 1"
        );
        $stmt->execute([$normalized]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $translated = ($sourceLang === 'tiv') ? $row['english_translation'] : $row['tiv_text'];

        return [
            'translated_text'  => $translated,
            'literal_meaning'  => $translated,
            'cultural_meaning' => $row['deeper_meaning'] ?? '',
            'usage_context'    => $row['usage_context'] ?? '',
            'source_table'     => 'tiv_proverbs',
            'source_id'        => (int) $row['id'],
            'category'         => 'proverb',
            'explanation'      => $row['deeper_meaning'] ?? '',
        ];
    }

    // -------------------------------------------------------
    // STEP 2: Exact Phrase Match
    // -------------------------------------------------------

    private function matchPhrase(string $normalized, string $sourceLang, string $targetLang): ?array
    {
        // Exact match — exclude bible-tagged entries; those are handled by matchBibleVerse
        $stmt = $this->db->prepare(
            "SELECT * FROM translation_phrases
             WHERE source_language = ?
               AND target_language = ?
               AND status = 'active'
               AND context_tag != 'bible'
               AND LOWER(source_text) = ?
             LIMIT 1"
        );
        $stmt->execute([$sourceLang, $targetLang, $normalized]);
        $row = $stmt->fetch();

        // LIKE fallback — only non-bible entries where source_text equals or contains input
        if (!$row) {
            $stmt = $this->db->prepare(
                "SELECT * FROM translation_phrases
                 WHERE source_language = ?
                   AND target_language = ?
                   AND status = 'active'
                   AND context_tag != 'bible'
                   AND (LOWER(source_text) = ? OR LOWER(source_text) LIKE ?)
                 ORDER BY confidence_score DESC, LENGTH(source_text) DESC
                 LIMIT 1"
            );
            $stmt->execute([$sourceLang, $targetLang, $normalized, '%' . $normalized . '%']);
            $row = $stmt->fetch();
        }

        if (!$row) return null;

        return [
            'translated_text'  => $row['target_text'],
            'literal_meaning'  => '',
            'cultural_meaning' => '',
            'usage_context'    => $row['context_tag'] ?? '',
            'source_table'     => 'translation_phrases',
            'source_id'        => (int) $row['id'],
            'category'         => $row['context_tag'] ?? 'phrase',
            'explanation'      => '',
            'confidence_score' => (int) $row['confidence_score'],
        ];
    }

    // -------------------------------------------------------
    // STEP 3: Partial / Sub-phrase Match
    // Useful when input sentence contains a known phrase.
    // -------------------------------------------------------

    private function matchPartialPhrase(string $normalized, string $sourceLang, string $targetLang): ?array
    {
        // Only run for multi-word input
        if (str_word_count($normalized) < 2) return null;

        // Find multi-word phrases where the source_text is contained within the input.
        // Restrict to multi-word source_text (must contain a space) so that single-word
        // phrase entries don't swallow the entire multi-word input.
        // Exclude bible-tagged entries — those are handled by matchBibleVerse.
        $stmt = $this->db->prepare(
            "SELECT * FROM translation_phrases
             WHERE source_language = ?
               AND target_language = ?
               AND status = 'active'
               AND context_tag != 'bible'
               AND source_text LIKE '% %'
               AND ? LIKE CONCAT('%', LOWER(source_text), '%')
             ORDER BY LENGTH(source_text) DESC
             LIMIT 1"
        );
        $stmt->execute([$sourceLang, $targetLang, $normalized]);
        $row = $stmt->fetch();

        if (!$row) return null;

        // Only accept the partial match if the matched phrase covers at least 90% of
        // the input words. This prevents a short phrase entry from becoming the sole
        // translation of a long sentence, which injects content not in the original.
        $inputWords  = str_word_count($normalized);
        $phraseWords = str_word_count(mb_strtolower($row['source_text'], 'UTF-8'));
        if ($inputWords > 0 && $phraseWords > 0 && ($phraseWords / $inputWords) < 0.9) {
            return null;
        }

        return [
            'translated_text'  => $row['target_text'],
            'literal_meaning'  => '',
            'cultural_meaning' => '',
            'usage_context'    => $row['context_tag'] ?? '',
            'source_table'     => 'translation_phrases',
            'source_id'        => (int) $row['id'],
            'category'         => $row['context_tag'] ?? 'phrase',
            'explanation'      => 'Matched as sub-phrase: "' . $row['source_text'] . '"',
            'confidence_score' => max(70, (int) $row['confidence_score'] - 10),
        ];
    }

    // -------------------------------------------------------
    // STEP 4: Dictionary Word Match
    // -------------------------------------------------------

    private function matchWord(string $normalized, string $sourceLang, string $targetLang): ?array
    {
        $prefix  = $normalized . '%';
        $toForm  = 'to ' . $normalized; // e.g. "walk" → "to walk"

        if ($sourceLang === 'tiv') {
            $stmt = $this->db->prepare(
                "SELECT * FROM daily_words
                 WHERE LOWER(tiv_word) = ? OR LOWER(tiv_word) LIKE ?
                 ORDER BY CASE WHEN LOWER(tiv_word) = ? THEN 0 ELSE 1 END
                 LIMIT 1"
            );
            $stmt->execute([$normalized, $prefix, $normalized]);
        } else {
            // Also check alternate_meaning and "to {word}" form common in verb entries
            $stmt = $this->db->prepare(
                "SELECT * FROM daily_words
                 WHERE LOWER(english_meaning) = ?
                    OR LOWER(alternate_meaning) = ?
                    OR LOWER(english_meaning) = ?
                    OR LOWER(alternate_meaning) = ?
                    OR LOWER(english_meaning) LIKE ?
                    OR LOWER(alternate_meaning) LIKE ?
                 ORDER BY CASE
                     WHEN LOWER(english_meaning) = ?     THEN 0
                     WHEN LOWER(alternate_meaning) = ?   THEN 1
                     WHEN LOWER(english_meaning) = ?     THEN 2
                     WHEN LOWER(alternate_meaning) = ?   THEN 3
                     WHEN LOWER(english_meaning) LIKE ?  THEN 4
                     ELSE 5
                 END
                 LIMIT 1"
            );
            $stmt->execute([
                $normalized, $normalized,
                $toForm,     $toForm,
                $prefix,     $prefix,
                $normalized, $normalized,
                $toForm,     $toForm,
                $prefix,
            ]);
        }

        $row = $stmt->fetch();

        // If daily_words has no match, fall through to translation_phrases
        // (single-word entries like "Please", "Sorry", "Welcome", etc.)
        // Exclude bible-tagged entries — those are handled by matchBibleVerse.
        if (!$row) {
            $phraseRow = $this->db->prepare(
                "SELECT target_text, context_tag, confidence_score FROM translation_phrases
                 WHERE source_language = ?
                   AND target_language = ?
                   AND status = 'active'
                   AND context_tag != 'bible'
                   AND LOWER(source_text) = ?
                 ORDER BY confidence_score DESC
                 LIMIT 1"
            );
            $phraseRow->execute([$sourceLang, $targetLang, $normalized]);
            $pr = $phraseRow->fetch();
            if ($pr) {
                return [
                    'translated_text'  => $pr['target_text'],
                    'literal_meaning'  => $pr['target_text'],
                    'cultural_meaning' => '',
                    'usage_context'    => $pr['context_tag'] ?? '',
                    'source_table'     => 'translation_phrases',
                    'source_id'        => null,
                    'category'         => $pr['context_tag'] ?? 'phrase',
                    'explanation'      => '',
                    'confidence_score' => (int) $pr['confidence_score'],
                ];
            }
            return null;
        }

        if ($sourceLang === 'tiv') {
            $translated = $row['english_meaning'];
            if (!empty($row['alternate_meaning'])) {
                $translated .= ' / ' . $row['alternate_meaning'];
            }
        } else {
            $translated = $row['tiv_word'];
        }

        $explanation = '';
        if (!empty($row['example_tiv'])) {
            $explanation = 'Example: ' . $row['example_tiv'];
            if (!empty($row['example_english'])) {
                $explanation .= ' — ' . $row['example_english'];
            }
        }

        return [
            'translated_text'  => $translated,
            'literal_meaning'  => $translated,
            'cultural_meaning' => '',
            'usage_context'    => $row['part_of_speech'] ?? '',
            'source_table'     => 'daily_words',
            'source_id'        => (int) $row['id'],
            'category'         => $row['category'] ?? $row['part_of_speech'] ?? 'word',
            'explanation'      => $explanation,
            'pronunciation'    => $row['pronunciation'] ?? '',
        ];
    }

    // -------------------------------------------------------
    // STEP 5: Category Content Match
    // -------------------------------------------------------

    private function matchCategory(string $normalized, string $sourceLang, string $targetLang): ?array
    {
        $sources = [
            'tiv_names'     => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_meaning', 'label' => 'name'],
            'tiv_plants'    => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_name',    'label' => 'plant'],
            'tiv_foods'     => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_name',    'label' => 'food'],
            'tiv_festivals' => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_name',    'label' => 'festival'],
            'tiv_animals'   => ['tiv_col' => 'tiv_name', 'eng_col' => 'name',            'label' => 'animal'],
        ];

        foreach ($sources as $table => $cols) {
            $searchCol = ($sourceLang === 'tiv') ? $cols['tiv_col'] : $cols['eng_col'];
            $returnCol = ($sourceLang === 'tiv') ? $cols['eng_col'] : $cols['tiv_col'];

            $stmt = $this->db->prepare(
                "SELECT * FROM {$table}
                 WHERE LOWER({$searchCol}) = ? OR LOWER({$searchCol}) LIKE ?
                 ORDER BY CASE WHEN LOWER({$searchCol}) = ? THEN 0 ELSE 1 END
                 LIMIT 1"
            );
            $stmt->execute([$normalized, $normalized . '%', $normalized]);
            $row = $stmt->fetch();

            if ($row) {
                $translated  = $row[$returnCol] ?? '';
                $explanation = $this->buildCategoryExplanation($row, $cols['label']);

                return [
                    'translated_text'  => $translated ?: $this->capitalizeFirst($normalized),
                    'literal_meaning'  => $translated,
                    'cultural_meaning' => $explanation,
                    'usage_context'    => '',
                    'source_table'     => $table,
                    'source_id'        => (int) $row['id'],
                    'category'         => $cols['label'],
                    'explanation'      => $explanation,
                ];
            }
        }

        return null;
    }

    private function buildCategoryExplanation(array $row, string $label): string
    {
        switch ($label) {
            case 'name':
                $parts = [];
                if (!empty($row['description']))  $parts[] = $row['description'];
                if (!empty($row['origin_story']))  $parts[] = 'Origin: ' . $row['origin_story'];
                return implode(' ', $parts);

            case 'plant':
                $parts = [];
                if (!empty($row['description']))    $parts[] = $row['description'];
                if (!empty($row['medicinal_uses'])) $parts[] = 'Medicinal: ' . $row['medicinal_uses'];
                if (!empty($row['food_uses']))       $parts[] = 'Food use: ' . $row['food_uses'];
                return implode(' ', $parts);

            case 'food':
                $parts = [];
                if (!empty($row['description']))           $parts[] = $row['description'];
                if (!empty($row['cultural_significance'])) $parts[] = $row['cultural_significance'];
                return implode(' ', $parts);

            case 'festival':
                $parts = [];
                if (!empty($row['description']))  $parts[] = $row['description'];
                if (!empty($row['significance'])) $parts[] = $row['significance'];
                return implode(' ', $parts);

            case 'animal':
                $parts = [];
                if (!empty($row['description']))      $parts[] = $row['description'];
                if (!empty($row['cultural_use']))     $parts[] = 'Cultural use: ' . $row['cultural_use'];
                if (!empty($row['symbolic_meaning'])) $parts[] = 'Symbol: ' . $row['symbolic_meaning'];
                return implode(' ', $parts);
        }
        return '';
    }

    // -------------------------------------------------------
    // STEP 6: Word-by-Word Fallback
    // -------------------------------------------------------

    private function wordByWord(string $normalized, string $sourceLang, string $targetLang, string $originalLine = ''): array
    {
        $tokens = $this->tokenize($normalized);
        if (empty($tokens)) {
            return $this->noneResult($normalized);
        }

        $translated    = [];
        $found         = 0;
        $wordResults   = [];
        $missingTokens = [];
        $count         = count($tokens);
        $i             = 0;

        while ($i < $count) {
            $chunkMatched = false;

            // Try 3-gram chunk against translation_phrases first
            if ($i + 2 < $count) {
                $trigram = implode(' ', array_slice($tokens, $i, 3));
                $chunkText = $this->lookupPhraseChunk($trigram, $sourceLang, $targetLang);
                if ($chunkText !== null) {
                    $translated[]  = $chunkText;
                    $wordResults[] = ['token' => $trigram, 'result' => $chunkText, 'source' => 'phrase', 'found' => true];
                    $found++;
                    $i += 3;
                    $chunkMatched = true;
                }
            }

            // Try 2-gram chunk
            if (!$chunkMatched && $i + 1 < $count) {
                $bigram = implode(' ', array_slice($tokens, $i, 2));
                $chunkText = $this->lookupPhraseChunk($bigram, $sourceLang, $targetLang);
                if ($chunkText !== null) {
                    $translated[]  = $chunkText;
                    $wordResults[] = ['token' => $bigram, 'result' => $chunkText, 'source' => 'phrase', 'found' => true];
                    $found++;
                    $i += 2;
                    $chunkMatched = true;
                }
            }

            // --- Tiv grammar: article/demonstrative inversion ---
            // English "the/this/that NOUN" → Tiv "NOUN la/ngun"
            // English "a/an NOUN"          → Tiv "NOUN" (no indefinite article in Tiv)
            if (!$chunkMatched && $sourceLang === 'english') {
                $token = $tokens[$i];
                $tivArticleMap = [
                    'the'   => 'la',
                    'that'  => 'la',
                    'those' => 'la',
                    'this'  => 'ngun',
                    'these' => 'ngun',
                ];
                $dropArticles = ['a' => true, 'an' => true];

                if (isset($tivArticleMap[$token]) && isset($tokens[$i + 1])) {
                    // Look up the following noun first
                    $nextToken = $tokens[$i + 1];
                    // Check if the noun + remainder is a phrase chunk we already matched — if so skip
                    $nextMatch = $this->lookupToken($nextToken, $sourceLang, $targetLang);
                    if ($nextMatch !== null) {
                        $tivArticle = $tivArticleMap[$token];
                        // Lowercase noun so cleanupOutput() capitalises only sentence start
                        $nounText = mb_strtolower($nextMatch['text'], 'UTF-8');
                        $combined = $nounText . ' ' . $tivArticle;
                        $translated[]  = $combined;
                        $wordResults[] = [
                            'token'  => $token . ' ' . $nextToken,
                            'result' => $combined,
                            'source' => $nextMatch['source'],
                            'found'  => true,
                        ];
                        $found++;
                        $i += 2;        // consume both the article and the noun
                        $chunkMatched = true;
                    }
                    // If the noun itself isn't found, fall through to single-token handling
                    // so the article and noun are each logged as missing individually
                } elseif (isset($dropArticles[$token])) {
                    // Tiv has no indefinite article — silently skip 'a' / 'an'
                    $wordResults[] = [
                        'token'  => $token,
                        'result' => '',
                        'source' => 'grammar-rule',
                        'found'  => true,
                    ];
                    $i++;
                    $chunkMatched = true;
                }
            }

            // Fall back to single-token lookup
            if (!$chunkMatched) {
                $token = $tokens[$i];
                $match = $this->lookupToken($token, $sourceLang, $targetLang);
                if ($match) {
                    $translated[]  = $match['text'];
                    $wordResults[] = ['token' => $token, 'result' => $match['text'], 'source' => $match['source'], 'found' => true];
                    $found++;
                } else {
                    $translated[]  = $token;
                    $wordResults[] = ['token' => $token, 'result' => null, 'source' => null, 'found' => false];
                    if ($this->isMeaningfulToken($token)) {
                        $missingTokens[] = $token;
                    }
                }
                $i++;
            }
        }

        // Nothing matched at all
        if ($found === 0) {
            $allMissing = array_filter($tokens, [$this, 'isMeaningfulToken']);
            return array_merge($this->noneResult($normalized), [
                'missing_tokens' => array_values($allMissing),
                'word_results'   => $wordResults,
            ]);
        }

        // Confidence: ratio of found tokens, scaled to 78% ceiling.
        // Words from curated sources (daily_words, phrases) get a bonus point each.
        $qualityScore = 0;
        foreach ($wordResults as $wr) {
            if (!$wr['found']) continue;
            $src = $wr['source'] ?? '';
            $qualityScore += in_array($src, ['words', 'phrases'], true) ? 2 : 1;
        }
        $maxQuality  = count($tokens) * 2;
        $qualityRatio = $maxQuality > 0 ? $qualityScore / $maxQuality : 0;
        $confidence  = (int) round($qualityRatio * 78);

        // Build translated text, reinserting punctuation from the original line.
        // We work at the token level here (before implode) so that multi-word
        // translations like "man" → "or imborivungu" don't break the word count.
        if ($originalLine !== '') {
            $parts     = [];
            $remaining = $originalLine;
            foreach ($translated as $idx => $transText) {
                $srcToken = $wordResults[$idx]['token'] ?? '';
                $punct    = '';
                if ($srcToken !== '') {
                    $pos = mb_stripos($remaining, $srcToken, 0, 'UTF-8');
                    if ($pos !== false) {
                        $remaining = mb_substr($remaining, $pos + mb_strlen($srcToken, 'UTF-8'), null, 'UTF-8');
                        preg_match('/^([,;:!?\.]*)/u', ltrim($remaining, " \t"), $m);
                        $punct = $m[1] ?? '';
                    }
                }
                $parts[] = $transText . $punct;
            }
            $assembledText = trim(implode(' ', $parts));
        } else {
            $assembledText = implode(' ', $translated);
        }

        return [
            'translated_text'  => $assembledText,
            'literal_meaning'  => $assembledText,
            'cultural_meaning' => '',
            'usage_context'    => '',
            'source_table'     => 'multiple',
            'source_id'        => null,
            'category'         => 'sentence',
            'explanation'      => '',
            'word_results'     => $wordResults,
            'missing_tokens'   => $missingTokens,
            'match_type'       => 'word_by_word',
            'confidence_score' => $confidence,
        ];
    }

    /**
     * Look up a multi-word chunk (bigram/trigram) in translation_phrases.
     * Returns the primary target text, or null if not found.
     */
    private function lookupPhraseChunk(string $chunk, string $sourceLang, string $targetLang): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT target_text FROM translation_phrases
             WHERE source_language = ?
               AND target_language = ?
               AND status = 'active'
               AND context_tag != 'bible'
               AND LOWER(source_text) = ?
             LIMIT 1"
        );
        $stmt->execute([$sourceLang, $targetLang, $chunk]);
        $row = $stmt->fetch();
        return $row ? $this->primaryMeaning($row['target_text']) : null;
    }

    private function lookupToken(string $token, string $sourceLang, string $targetLang): ?array
    {
        $prefix = $token . '%';
        $toForm = 'to ' . $token;

        // 1. Match in daily_words (also checks alternate_meaning and "to {word}" verb form)
        if ($sourceLang === 'tiv') {
            $stmt = $this->db->prepare(
                "SELECT tiv_word, english_meaning FROM daily_words
                 WHERE LOWER(tiv_word) = ? OR LOWER(tiv_word) LIKE ?
                 ORDER BY CASE WHEN LOWER(tiv_word) = ? THEN 0 ELSE 1 END
                 LIMIT 1"
            );
            $stmt->execute([$token, $prefix, $token]);
            $row = $stmt->fetch();
            if ($row) {
                $meaning = $this->primaryMeaning($row['english_meaning']);
                // Strip "to " prefix from verb forms in sentence context (e.g. "to walk" → "walk")
                if (strncasecmp($meaning, 'to ', 3) === 0) {
                    $meaning = substr($meaning, 3);
                }
                return ['text' => $meaning, 'source' => 'words'];
            }
        } else {
            $stmt = $this->db->prepare(
                "SELECT tiv_word, english_meaning, alternate_meaning FROM daily_words
                 WHERE LOWER(english_meaning) = ?
                    OR LOWER(alternate_meaning) = ?
                    OR LOWER(english_meaning) = ?
                    OR LOWER(alternate_meaning) = ?
                    OR LOWER(english_meaning) LIKE ?
                    OR LOWER(alternate_meaning) LIKE ?
                 ORDER BY CASE
                     WHEN LOWER(english_meaning) = ?     THEN 0
                     WHEN LOWER(alternate_meaning) = ?   THEN 1
                     WHEN LOWER(english_meaning) = ?     THEN 2
                     WHEN LOWER(alternate_meaning) = ?   THEN 3
                     WHEN LOWER(english_meaning) LIKE ?  THEN 4
                     ELSE 5
                 END
                 LIMIT 1"
            );
            $stmt->execute([
                $token, $token,
                $toForm, $toForm,
                $prefix, $prefix,
                $token, $token,
                $toForm, $toForm,
                $prefix,
            ]);
            $row = $stmt->fetch();
            if ($row) return ['text' => $row['tiv_word'], 'source' => 'words'];
        }

        // 2. Check translation_phrases for a curated single-word entry.
        //    lookupPhraseChunk does an exact LOWER(source_text)=? match on translation_phrases,
        //    so re-using it here costs nothing extra and keeps the logic consistent.
        $phraseHit = $this->lookupPhraseChunk($token, $sourceLang, $targetLang);
        if ($phraseHit !== null) {
            return ['text' => $phraseHit, 'source' => 'phrases'];
        }

        // 3. Previously approved missing word (table may not exist yet — degrade gracefully)
        try {
            $stmt = $this->db->prepare(
                "SELECT approved_meaning FROM missing_words
                 WHERE LOWER(word) = ? AND source_language = ? AND status = 'approved'
                 LIMIT 1"
            );
            $stmt->execute([$token, $sourceLang]);
            $row = $stmt->fetch();
            if ($row && !empty($row['approved_meaning'])) {
                return ['text' => $row['approved_meaning'], 'source' => 'community'];
            }
        } catch (\PDOException $e) {
            // Table not yet created — skip community lookup silently
        }

        // 4. Category tables
        $cats = [
            'tiv_names'     => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_meaning'],
            'tiv_plants'    => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_name'],
            'tiv_foods'     => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_name'],
            'tiv_festivals' => ['tiv_col' => 'tiv_name', 'eng_col' => 'english_name'],
            'tiv_animals'   => ['tiv_col' => 'tiv_name', 'eng_col' => 'name'],
        ];

        foreach ($cats as $table => $cols) {
            if ($sourceLang === 'tiv') {
                $stmt = $this->db->prepare(
                    "SELECT {$cols['tiv_col']}, {$cols['eng_col']} FROM {$table}
                     WHERE LOWER({$cols['tiv_col']}) = ? LIMIT 1"
                );
                $stmt->execute([$token]);
                $row = $stmt->fetch();
                if ($row && !empty($row[$cols['eng_col']])) {
                    return ['text' => $row[$cols['eng_col']], 'source' => $table];
                }
            } else {
                $stmt = $this->db->prepare(
                    "SELECT {$cols['tiv_col']}, {$cols['eng_col']} FROM {$table}
                     WHERE LOWER({$cols['eng_col']}) = ? LIMIT 1"
                );
                $stmt->execute([$token]);
                $row = $stmt->fetch();
                if ($row && !empty($row[$cols['tiv_col']])) {
                    return ['text' => $row[$cols['tiv_col']], 'source' => $table];
                }
            }
        }

        return null;
    }

    // -------------------------------------------------------
    // STEP 7: Apply Translation Rules
    // -------------------------------------------------------

    private function applyRules(string $text, string $sourceLang, string $targetLang): string
    {
        $stmt = $this->db->prepare(
            "SELECT pattern_text, replacement_text FROM translation_rules
             WHERE source_language = ?
               AND target_language = ?
               AND status = 'active'
             ORDER BY priority_score DESC"
        );
        $stmt->execute([$sourceLang, $targetLang]);
        $rules = $stmt->fetchAll();

        foreach ($rules as $rule) {
            $pattern     = $rule['pattern_text'];
            $replacement = $rule['replacement_text'];

            // Try word-boundary regex first for clean whole-word replacements
            $escaped = preg_quote($pattern, '/');
            $regex   = '/\b' . $escaped . '\b/ui';

            if (@preg_match($regex, '') !== false) {
                $text = preg_replace($regex, $replacement, $text);
            } else {
                // Fall back to simple case-insensitive replacement
                $text = str_ireplace($pattern, $replacement, $text);
            }
        }

        return $text;
    }

    // -------------------------------------------------------
    // Output Cleanup
    // -------------------------------------------------------

    /**
     * Reinsert commas, semicolons, periods etc. from the original input into
     * the translated output. Called after word-by-word translation so that
     * "apple, banana, mango" → "akpan, mbi, gyande" (not "akpan mbi gyande").
     *
     * Strategy:
     *  1. Walk through the original text and record, for each word, the
     *     punctuation that immediately follows it (before the next word).
     *  2. If the translated word count matches the source word count, attach
     *     the recorded punctuation to each translated word.
     *  3. If counts differ (e.g. a bigram collapsed two words into one), only
     *     restore a trailing sentence-ender (. ! ?) to avoid mis-alignment.
     */
    private function restorePunctuation(string $translated, string $original): string
    {
        if (trim($translated) === '' || trim($original) === '') {
            return $translated;
        }

        // Source word list (normalised, same as what tokenize() produced)
        $origWords  = preg_split('/\s+/', trim($this->normalize($original)), -1, PREG_SPLIT_NO_EMPTY);
        $transWords = preg_split('/\s+/', trim($translated), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($origWords) || empty($transWords)) {
            return $translated;
        }

        // Walk through the original string and collect the punctuation that
        // follows each source word (commas, semicolons, colons, !, ?, .).
        $separators = [];
        $remaining  = $original;
        foreach ($origWords as $word) {
            $pos = mb_stripos($remaining, $word, 0, 'UTF-8');
            if ($pos === false) {
                $separators[] = '';
                continue;
            }
            $remaining = mb_substr($remaining, $pos + mb_strlen($word, 'UTF-8'), null, 'UTF-8');
            // Grab punctuation chars (not spaces) that appear before the next word
            preg_match('/^([,;:!?\.]*)/u', ltrim($remaining, " \t"), $m);
            $separators[] = $m[1] ?? '';
        }

        // Same word count → apply punctuation position-by-position
        if (count($transWords) === count($origWords)) {
            $parts = [];
            foreach ($transWords as $i => $word) {
                $sep = $separators[$i] ?? '';
                $parts[] = $sep !== '' ? $word . $sep : $word;
            }
            return implode(' ', $parts);
        }

        // Word counts differ: only restore a trailing sentence-ender
        $lastSep = end($separators);
        if ($lastSep !== '' && preg_match('/^[.!?]$/', $lastSep)) {
            if (!preg_match('/[.!?]$/u', trim($translated))) {
                return trim($translated) . $lastSep;
            }
        }

        return $translated;
    }

    /**
     * Final pass: capitalize first letter, fix spacing, trim.
     */
    private function cleanupOutput(string $text): string
    {
        // Normalise line endings to \n
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);

        // Process each line independently so paragraph structure is preserved
        $lines = explode("\n", $text);
        $lines = array_map(function (string $line): string {
            // Collapse runs of spaces/tabs within the line (not newlines)
            $line = preg_replace('/[ \t]+/', ' ', $line);
            return trim($line);
        }, $lines);

        $text = implode("\n", $lines);

        // Collapse 3+ consecutive blank lines down to 2 (one blank line between paragraphs)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        // Capitalise the very first character of the whole output
        if ($text !== '') {
            $text = mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8')
                  . mb_substr($text, 1, null, 'UTF-8');
        }
        return $text;
    }

    // -------------------------------------------------------
    // Missing Words Tracking
    // -------------------------------------------------------

    /**
     * Persist all unrecognised tokens to missing_words table.
     * Returns map of token => missing_word_id for use in suggest links.
     */
    private function saveMissingWords(array $tokens, string $sourceLang, string $targetLang): array
    {
        if (empty($tokens)) return [];

        try {
            require_once BASE_PATH . '/models/MissingWord.php';
            $model = new MissingWord();

            $ids = [];
            foreach ($tokens as $token) {
                $normalized = mb_strtolower(trim($token), 'UTF-8');
                if ($normalized === '') continue;
                $id = $model->recordMiss($normalized, $sourceLang, $targetLang);
                if ($id > 0) {
                    $ids[$token] = $id;
                    // Feature 4: try to resolve this unknown word from Bible verses
                    $this->miner->autoResolveMissingWord($id, $normalized, $sourceLang);
                }
            }
            return $ids;
        } catch (\PDOException $e) {
            // Table not yet created — skip silently, don't break translation
            return [];
        }
    }

    /**
     * Decide whether a token is worth tracking as missing.
     * Filters out numbers, single characters, and very common English stop words.
     */
    private function isMeaningfulToken(string $token): bool
    {
        if (mb_strlen($token, 'UTF-8') < 2) return false;
        if (is_numeric($token)) return false;

        // Common English stopwords that don't need to be in the Tiv dictionary
        static $stopwords = [
            'the','a','an','is','are','was','were','be','been','being',
            'have','has','had','do','does','did','will','would','could',
            'should','may','might','shall','to','of','in','on','at','by',
            'for','with','about','into','from','up','out','as','it','its',
            'this','that','these','those','and','or','but','not','no','i',
            'me','my','we','our','you','your','he','his','she','her','they',
            'their','them','us','him',
        ];
        return !in_array(mb_strtolower($token, 'UTF-8'), $stopwords, true);
    }

    // -------------------------------------------------------
    // Save Translation Log
    // -------------------------------------------------------

    private function saveLog(array $result, ?int $userId): int
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        // Only log if we have meaningful input/output
        if (empty($result['input_text'])) return 0;

        $stmt = $this->db->prepare(
            "INSERT INTO translation_logs
                (user_id, source_text, translated_text, source_language, target_language,
                 match_type, engine_used, confidence_score, payment_status, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, 'v3', ?, 'free', ?)"
        );
        $stmt->execute([
            $userId,
            $result['input_text'],
            $result['translated_text'] ?? '',
            $result['source_language'],
            $result['target_language'],
            $result['match_type'],
            $result['confidence_score'],
            $ip,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------

    /**
     * Extract the primary (first) meaning from a field that may contain
     * multiple variants like "in / into / to" or "Heart/Mind".
     * Used in word-by-word mode so sentence output reads cleanly.
     */
    private function primaryMeaning(string $meaning): string
    {
        // Split on " / " (spaced slash) first, then bare "/"
        $parts = preg_split('/\s*\/\s*/', $meaning, 2);
        return trim($parts[0]);
    }

    private function normalize(string $input): string
    {
        $text = mb_strtolower(trim($input), 'UTF-8');
        // Remove punctuation except apostrophes and hyphens (common in Tiv)
        $text = preg_replace("/[^\p{L}\p{N}\s'\-]/u", '', $text);
        // Collapse multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function tokenize(string $text): array
    {
        // Split on whitespace; keep all tokens including short ones (Tiv has many)
        return preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function capitalizeFirst(string $str): string
    {
        return mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_substr($str, 1, null, 'UTF-8');
    }

    private function emptyResult(): array
    {
        return [
            'input_text'       => '',
            'translated_text'  => '',
            'source_language'  => 'tiv',
            'target_language'  => 'english',
            'match_type'       => 'none',
            'confidence_score' => 0,
            'literal_meaning'  => '',
            'cultural_meaning' => '',
            'usage_context'    => '',
            'source_table'     => null,
            'source_id'        => null,
            'category'         => null,
            'explanation'      => '',
            'word_results'     => [],
            'missing_tokens'   => [],
            'missing_word_ids' => [],
            'pronunciation'    => '',
            'log_id'           => null,
        ];
    }

    private function noneResult(string $input): array
    {
        return array_merge($this->emptyResult(), [
            'input_text'       => $input,
            'translated_text'  => '',
            'match_type'       => 'none',
            'confidence_score' => 0,
        ]);
    }

    // -------------------------------------------------------
    // ADMIN / STATS helpers
    // -------------------------------------------------------

    public function topQueries(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT source_text, source_language, COUNT(*) AS search_count,
                    AVG(confidence_score) AS avg_confidence
             FROM translation_logs
             GROUP BY source_text, source_language
             ORDER BY search_count DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function lowConfidenceLogs(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM translation_logs
             WHERE confidence_score < 40
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
