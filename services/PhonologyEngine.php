<?php
/**
 * Phonology Engine — Alphabet module wired into the Translation Engine.
 *
 * Loads the structured letter/digraph/tone inventory from `tiv_alphabet`
 * and uses it to segment a Tiv word into graphemes (treating digraphs as
 * single units and doubled vowels as long vowels), generate an IPA-based
 * pronunciation guide, and give a soft "does this look like valid Tiv
 * spelling" signal. Never blocks or alters a translation — purely
 * informational output consumed by TranslationEngine.
 */

class PhonologyEngine
{
    private PDO $db;

    /** lowercase digraph string => ipa, e.g. 'gb' => '/ɡ͡b/' */
    private array $digraphIpa = [];

    /** lowercase single letter => ipa, e.g. 'a' => '/a/' */
    private array $letterIpa = [];

    /** lowercase vowel letters recognised for long-vowel detection */
    private array $vowels = [];

    /** digraphs sorted longest-first (all currently 2 chars, kept generic) */
    private array $digraphs = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->load();
    }

    private function load(): void
    {
        require_once BASE_PATH . '/models/TivAlphabetEntry.php';
        $grouped = (new TivAlphabetEntry())->getAllGrouped();

        foreach ($grouped['vowel'] ?? [] as $row) {
            $small = $this->smallForm($row['letter']);
            if ($small === '') continue;
            $this->vowels[] = $small;
            if (!empty($row['ipa'])) {
                $this->letterIpa[$small] = $row['ipa'];
            }
        }

        foreach ($grouped['consonant'] ?? [] as $row) {
            $small = $this->smallForm($row['letter']);
            if ($small === '' || !empty($row['ipa']) === false) continue;
            $this->letterIpa[$small] = $row['ipa'];
        }

        foreach ($grouped['digraph'] ?? [] as $row) {
            $small = $this->smallForm($row['letter']);
            if ($small === '') continue;
            $this->digraphs[] = $small;
            if (!empty($row['ipa'])) {
                $this->digraphIpa[$small] = $row['ipa'];
            }
        }

        // Common Tiv vowel diacritics not itemised as separate "vowel" rows
        // in tiv_alphabet (which only lists the 5 plain forms) but that are
        // genuinely part of Tiv orthography (tone marks, ô). Union, not
        // replace — real DB data always wins for IPA lookups above.
        foreach (['â','ê','î','ô','û','á','à','é','è','í','ì','ó','ò','ú','ù'] as $v) {
            if (!in_array($v, $this->vowels, true)) {
                $this->vowels[] = $v;
            }
        }

        // Longest-first (future-proof if a 3+ char digraph is ever added)
        usort($this->digraphs, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
    }

    /** "GB gb" -> "gb", "A a" -> "a" */
    private function smallForm(string $letterField): string
    {
        $parts = preg_split('/\s+/', trim($letterField), 2);
        $small = $parts[1] ?? ($parts[0] ?? '');
        return mb_strtolower(trim($small), 'UTF-8');
    }

    /**
     * Split a Tiv word into grapheme units: digraphs and doubled (long)
     * vowels count as one unit each; everything else is a single character.
     */
    public function segmentGraphemes(string $word): array
    {
        $word  = mb_strtolower(trim($word), 'UTF-8');
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
        $n     = count($chars);
        $graphemes = [];
        $i = 0;

        while ($i < $n) {
            if ($i + 1 < $n) {
                $pair = $chars[$i] . $chars[$i + 1];
                if (in_array($pair, $this->digraphs, true)) {
                    $graphemes[] = $pair;
                    $i += 2;
                    continue;
                }
                if ($chars[$i] === $chars[$i + 1] && in_array($chars[$i], $this->vowels, true)) {
                    $graphemes[] = $pair; // long vowel, e.g. "aa"
                    $i += 2;
                    continue;
                }
            }
            $graphemes[] = $chars[$i];
            $i++;
        }

        return $graphemes;
    }

    /**
     * Soft signal only: true if the word decomposes entirely into known
     * Tiv graphemes (letters, digraphs, long vowels) plus apostrophe/hyphen.
     * Never used to block or reject a translation.
     */
    public function isValidTivSpelling(string $word): bool
    {
        $graphemes = $this->segmentGraphemes($word);
        if (empty($graphemes)) return false;

        foreach ($graphemes as $g) {
            if (in_array($g, ["'", '-'], true)) continue;
            if (mb_strlen($g, 'UTF-8') === 2) {
                $halves = preg_split('//u', $g, -1, PREG_SPLIT_NO_EMPTY);
                if (($halves[0] ?? '') === ($halves[1] ?? '') && in_array($halves[0], $this->vowels, true)) {
                    continue; // long vowel
                }
            }
            if (in_array($g, $this->digraphs, true)) continue;
            if (in_array($g, $this->vowels, true)) continue;
            if (isset($this->letterIpa[$g])) continue;
            if (preg_match('/^\p{L}$/u', $g)) continue; // unknown-but-plausible letter — don't over-reject
            return false;
        }
        return true;
    }

    /**
     * Build an IPA-based pronunciation guide from grapheme segmentation,
     * making digraphs and long vowels visually distinct as single phonemes.
     */
    /**
     * Build an IPA-based pronunciation guide. Accepts a single word or a
     * short multi-word phrase — each word is segmented independently
     * (punctuation stripped) and joined with a space, the whole thing
     * wrapped in a single pair of slashes.
     */
    public function generatePronunciation(string $word): string
    {
        $words = preg_split('/\s+/', trim($word), -1, PREG_SPLIT_NO_EMPTY);
        $wordParts = [];

        foreach ($words as $w) {
            // Strip anything that isn't a letter/apostrophe/hyphen (punctuation, digits)
            $clean = preg_replace("/[^\p{L}'\-]/u", '', $w);
            if ($clean === '') continue;
            $pronounced = $this->pronounceWord($clean);
            if ($pronounced !== '') $wordParts[] = $pronounced;
        }

        return empty($wordParts) ? '' : '/' . implode(' ', $wordParts) . '/';
    }

    /** Grapheme-by-grapheme IPA for a single clean word, dot-joined, no slashes. */
    private function pronounceWord(string $word): string
    {
        $graphemes = $this->segmentGraphemes($word);
        if (empty($graphemes)) return '';

        $parts = [];
        foreach ($graphemes as $g) {
            if (in_array($g, ["'", '-'], true)) continue;

            if (isset($this->digraphIpa[$g])) {
                $parts[] = trim($this->digraphIpa[$g], '/');
                continue;
            }
            if (mb_strlen($g, 'UTF-8') === 2) {
                $halves = preg_split('//u', $g, -1, PREG_SPLIT_NO_EMPTY);
                $base = $halves[0] ?? $g;
                if (isset($this->letterIpa[$base])) {
                    $parts[] = trim($this->letterIpa[$base], '/') . 'ː'; // long vowel marker
                    continue;
                }
            }
            if (isset($this->letterIpa[$g])) {
                $parts[] = trim($this->letterIpa[$g], '/');
                continue;
            }
            $parts[] = $g;
        }

        return implode('.', $parts);
    }
}
