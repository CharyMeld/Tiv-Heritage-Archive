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
    private ?string $_currentDomain = null; // semantic domain of the current sentence

    // Free tier: max daily translations per guest/user
    const FREE_DAILY_LIMIT_GUEST = 5;
    const FREE_DAILY_LIMIT_USER  = 15;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        require_once BASE_PATH . '/services/BibleTranslationMiner.php';
        $this->miner = new BibleTranslationMiner($db);

        require_once BASE_PATH . '/services/PhonologyEngine.php';
        $this->phonology = new PhonologyEngine($db);

        require_once BASE_PATH . '/services/GrammarEngine.php';
        $this->grammar = new GrammarEngine($db);
    }

    private BibleTranslationMiner $miner;
    private PhonologyEngine       $phonology;
    private GrammarEngine         $grammar;

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

        // Populate alternatives and why-explanation
        if (!empty($result['translated_text'])) {
            $posVariants = $result['_pos_variants'] ?? [];
            unset($result['_pos_variants']); // don't leak internal field
            $wordId = ($result['source_table'] ?? null) === 'daily_words' ? (int) $result['source_id'] : null;
            $result['alternatives']    = $this->findAlternatives($input, $sourceLang, $targetLang, $result['translated_text'], $posVariants, $wordId);
            $result['why_explanation'] = $this->buildWhyExplanation($result);
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

        // Detect the semantic domain of this sentence so word-selection can prefer
        // domain-appropriate vocabulary when multiple dictionary entries exist.
        $this->_currentDomain = $this->detectSemanticDomain($line);

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

        // Step 9 — Local statistical refinement
        // For word-by-word and "none" results, attempt to improve the output
        // using n-gram statistics learned from high-confidence past translations
        // and additional Bible verse mining for unknown tokens.
        $needsRefinement = in_array($result['match_type'] ?? '', ['word_by_word', 'none'], true);
        if ($needsRefinement) {
            $refined = $this->refineLocally($line, $sourceLang, $targetLang, $result);
            if ($refined !== null) {
                $result = $refined;
            }
        }

        // Step 10 — Citations: package which archive record grounded this
        // translation, so the UI can show "why". word_by_word() builds its
        // own richer per-word + per-grammar-rule citation list internally
        // (source_table === 'multiple') — don't overwrite that.
        if ($result && empty($result['citations'])
            && !empty($result['source_table']) && $result['source_table'] !== 'multiple') {
            $citations   = $result['_extra_citations'] ?? [];
            $matchType   = $result['match_type'] ?? '';
            $citations[] = [
                'type'  => self::CITATION_TYPE_BY_MATCH[$matchType] ?? 'other',
                'table' => $result['source_table'],
                'id'    => $result['source_id'],
                'label' => $this->citationLabel($result['source_table']),
            ];
            $result['citations'] = $citations;
        }
        unset($result['_extra_citations']);

        return $result ?? $this->emptyResult();
    }

    private const CITATION_TYPE_BY_MATCH = [
        'proverb'       => 'proverb',
        'phrase'        => 'phrase',
        'word'          => 'dictionary',
        'category'      => 'culture',
        'bible'         => 'bible',
        'learned'       => 'learned',
        'bible_refined' => 'bible',
    ];

    /** Human-readable source label for the citations UI card. */
    private function citationLabel(string $table): string
    {
        return match ($table) {
            'tiv_proverbs'        => 'Tiv Proverbs Archive',
            'translation_phrases' => 'Curated Translation Phrases',
            'daily_words'         => 'Tiv Dictionary',
            'tiv_names'           => 'Tiv Names Archive',
            'tiv_plants'          => 'Tiv Plants Archive',
            'tiv_foods'           => 'Tiv Foods Archive',
            'tiv_festivals'       => 'Tiv Festivals Archive',
            'tiv_animals'         => 'Tiv Animals Archive',
            'bible_verses'        => 'Tiv Bible',
            'translation_ngrams'  => 'Learned Translation History',
            default               => ucfirst(str_replace('_', ' ', $table)),
        };
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
    // STEP 9: Local Statistical Refinement
    // Replaces the former NLLB external API call.
    // Improves word-by-word and "none" results using:
    //   a) N-gram statistics from approved past translations
    //   b) Additional Bible verse mining for unknown tokens
    //   c) Grammar rule re-application after Bible mining
    // -------------------------------------------------------

    private function refineLocally(string $input, string $sourceLang, string $targetLang, array $currentResult): ?array
    {
        $normalized = $this->normalize($input);
        $improvementsApplied = 0;

        // a) Check translation_ngrams for a learned single-token mapping
        if (str_word_count($normalized) === 1) {
            $hit = $this->lookupNgram($normalized, $sourceLang, $targetLang);
            if ($hit !== null) {
                $output = $this->applyRules($hit['target'], $sourceLang, $targetLang);
                $output = $this->cleanupOutput($output);
                if ($output !== '') {
                    return [
                        'translated_text'  => $output,
                        'literal_meaning'  => $output,
                        'cultural_meaning' => '',
                        'usage_context'    => '',
                        'source_table'     => 'translation_ngrams',
                        'source_id'        => null,
                        'category'         => 'learned',
                        'explanation'      => 'Learned from archive translation history.',
                        'match_type'       => 'learned',
                        'confidence_score' => min(78, (int) $hit['confidence']),
                        'word_results'     => $currentResult['word_results'] ?? [],
                        'missing_tokens'   => [],
                    ];
                }
            }
        }

        // b) Re-attempt Bible mining for any missing tokens
        $missingTokens = $currentResult['missing_tokens'] ?? [];
        $wordResults   = $currentResult['word_results'] ?? [];

        if (!empty($missingTokens)) {
            $resolved = [];
            foreach ($missingTokens as $token) {
                $mineHit = $this->miner->lookupWordInBible($token, $sourceLang);
                if ($mineHit !== null) {
                    $resolved[$token] = $mineHit;
                    $improvementsApplied++;
                }
            }

            if (!empty($resolved)) {
                // Rebuild translated text replacing missing tokens with Bible-mined values
                $rebuilt = $currentResult['translated_text'] ?? '';
                foreach ($resolved as $orig => $replacement) {
                    $rebuilt = preg_replace('/\b' . preg_quote($orig, '/') . '\b/iu', $replacement, $rebuilt);
                }

                $rebuilt = $this->applyRules($rebuilt, $sourceLang, $targetLang);
                $rebuilt = $this->cleanupOutput($rebuilt);

                if ($rebuilt !== '' && $rebuilt !== $currentResult['translated_text']) {
                    $stillMissing = array_diff($missingTokens, array_keys($resolved));
                    $newConfidence = min(82, ($currentResult['confidence_score'] ?? 40) + ($improvementsApplied * 5));
                    return array_merge($currentResult, [
                        'translated_text'  => $rebuilt,
                        'match_type'       => 'bible_refined',
                        'confidence_score' => $newConfidence,
                        'missing_tokens'   => array_values($stillMissing),
                        'explanation'      => "Refined using Bible bilingual data ({$improvementsApplied} token(s) resolved).",
                        'source_table'     => 'bible_verses',
                    ]);
                }
            }
        }

        // c) If word_by_word produced some output, apply one more round of grammar rules
        $current = $currentResult['translated_text'] ?? '';
        if ($current !== '' && ($currentResult['match_type'] ?? '') === 'word_by_word') {
            $refined = $this->applyRules($current, $sourceLang, $targetLang);
            $refined = $this->cleanupOutput($refined);
            if ($refined !== $current) {
                return array_merge($currentResult, [
                    'translated_text'  => $refined,
                    'match_type'       => 'word_by_word',
                    'explanation'      => 'Grammar rules applied.',
                ]);
            }
        }

        return null;
    }

    /**
     * Look up a single token in the learned n-gram statistics table.
     */
    private function lookupNgram(string $token, string $sourceLang, string $targetLang): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT target_ngram, confidence FROM translation_ngrams
                 WHERE source_ngram = ? AND source_lang = ? AND target_lang = ?
                 ORDER BY frequency DESC, confidence DESC
                 LIMIT 1"
            );
            $stmt->execute([$token, $sourceLang, $targetLang]);
            $row = $stmt->fetch();
            if ($row) {
                return ['target' => $row['target_ngram'], 'confidence' => $row['confidence']];
            }
        } catch (\PDOException $e) {
            // Table may not exist yet — degrade gracefully
        }
        return null;
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
        // Fetch ALL POS variants so we can disambiguate and show alternatives
        $variants = $this->fetchAllPosVariants($normalized, $sourceLang);

        if (empty($variants)) {
            // Fall through to translation_phrases
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

        // Pick the best POS variant, passing the word itself for function-word POS detection
        $row = $this->resolvePosByContext($variants, null, null, $sourceLang, $normalized);

        if ($sourceLang === 'tiv') {
            $translated = $row['english_meaning'];
            if (!empty($row['alternate_meaning'])) {
                $translated .= ' / ' . $row['alternate_meaning'];
            }
        } else {
            $translated = $row['tiv_word'];
        }

        // Build POS disambiguation note when homonyms exist
        $posNote   = '';
        $posAlts   = [];
        if (count($variants) > 1) {
            foreach ($variants as $v) {
                if ($v['id'] === $row['id']) continue;
                $altMeaning = $sourceLang === 'tiv' ? $v['english_meaning'] : $v['tiv_word'];
                $posAlts[]  = '[' . ($v['part_of_speech'] ?: 'other') . '] ' . $altMeaning;
            }
            $posNote = 'This word has ' . count($variants) . ' meanings depending on part of speech. '
                     . 'Showing: [' . ($row['part_of_speech'] ?: 'noun') . '] '
                     . ($sourceLang === 'tiv' ? $row['english_meaning'] : $row['tiv_word']) . '.';
        }

        $explanation = $posNote;
        if (!empty($row['example_tiv'])) {
            $ex = 'Example: ' . $row['example_tiv'];
            if (!empty($row['example_english'])) {
                $ex .= ' — ' . $row['example_english'];
            }
            $explanation = $explanation ? $explanation . ' ' . $ex : $ex;
        }
        if (!empty($row['usage_notes'])) {
            $explanation = $explanation ? $explanation . ' ' . $row['usage_notes'] : $row['usage_notes'];
        }

        // Dictionary enrichment: prefer curated literal/figurative meaning
        // when an admin has filled them in; otherwise keep the established
        // behaviour (literal_meaning = the translated text itself).
        $literalMeaning  = !empty($row['literal_meaning']) ? $row['literal_meaning'] : $translated;
        $culturalMeaning = !empty($row['figurative_meaning']) ? $row['figurative_meaning'] : '';

        $pronunciation = $this->wordPronunciation($row);

        // Root word: surfaced as an extra citation, never used for fallback
        // matching (Tiv has no fixed derivation/pluralisation rule to guess).
        $extraCitations = [];
        if (!empty($row['root_word_id'])) {
            $extraCitations[] = [
                'type'  => 'dictionary',
                'table' => 'daily_words',
                'id'    => (int) $row['root_word_id'],
                'label' => 'Tiv Dictionary — root word',
            ];
        }

        return [
            'translated_text'   => $translated,
            'literal_meaning'   => $literalMeaning,
            'cultural_meaning'  => $culturalMeaning,
            'usage_context'     => $row['part_of_speech'] ?? '',
            'source_table'      => 'daily_words',
            'source_id'         => (int) $row['id'],
            'category'          => $row['category'] ?? $row['part_of_speech'] ?? 'word',
            'explanation'       => $explanation,
            'pronunciation'     => $pronunciation,
            '_pos_variants'     => $variants,  // passed to findAlternatives
            '_extra_citations'  => $extraCitations,
        ];
    }

    /**
     * Join several already-bracketed per-word pronunciation strings
     * ("/m/", "/ya/", …) into one clean phrase-level "/m ya .../" guide.
     */
    private function joinPronunciationParts(array $parts): string
    {
        $clean = array_filter(array_map(fn($p) => trim($p, '/ '), $parts), fn($p) => $p !== '');
        return empty($clean) ? '' : '/' . implode(' ', $clean) . '/';
    }

    /**
     * Pronunciation for a daily_words row: curated IPA/tone > free-text
     * pronunciation column > Alphabet-generated fallback (only when
     * nothing else is available — never overrides curated data).
     */
    private function wordPronunciation(array $row): string
    {
        $pronunciation = $row['pronunciation'] ?? '';
        if (!empty($row['ipa'])) {
            return $row['ipa'] . (!empty($row['tone']) ? ' (' . $row['tone'] . ' tone)' : '');
        }
        if (!empty($row['tone'])) {
            return trim($pronunciation . ' (' . $row['tone'] . ' tone)');
        }
        if ($pronunciation === '' && !empty($row['tiv_word'])) {
            return $this->phonology->generatePronunciation($row['tiv_word']);
        }
        return $pronunciation;
    }

    // -------------------------------------------------------
    // POS DISAMBIGUATION HELPERS
    // -------------------------------------------------------

    /**
     * Fetch all POS variants of a word from daily_words.
     * Returns rows ordered by match quality so variants[0] is always the best match.
     *
     * Priority (for both directions):
     *   0 — exact primary-field match
     *   1 — exact alternate-field match
     *   2 — exact "to {word}" match (verb forms)
     *   3 — exact alternate "to {word}" match
     *   4 — single-word prefix match (only if query ≥ 4 chars to avoid "we"→"weather")
     *   5 — everything else
     *
     * Within each priority tier, shorter meanings come first (more specific).
     */
    private function fetchAllPosVariants(string $normalized, string $sourceLang): array
    {
        $toForm = 'to ' . $normalized;
        // Only use prefix LIKE for queries of 4+ chars to avoid false positives
        // (e.g. "we" matching "weather", "and" matching "android")
        $usePrefixLike = mb_strlen($normalized) >= 4;
        $prefix = $normalized . '%';

        if ($sourceLang === 'tiv') {
            $stmt = $this->db->prepare(
                "SELECT * FROM daily_words
                 WHERE is_active = 1
                   AND (LOWER(tiv_word) = ?"
                   . ($usePrefixLike ? " OR LOWER(tiv_word) LIKE ?" : "")
                   . ")
                 ORDER BY
                   CASE WHEN LOWER(tiv_word) = ? THEN 0 ELSE 1 END,
                   CHAR_LENGTH(tiv_word) ASC
                 LIMIT 8"
            );
            $params = $usePrefixLike
                ? [$normalized, $prefix, $normalized]
                : [$normalized, $normalized];
            $stmt->execute($params);
        } else {
            // English → Tiv: match against english_meaning and alternate_meaning.
            // Uses the same priority order as the original lookupToken so the
            // best-matching row always ends up at variants[0].
            $likeClause = $usePrefixLike
                ? " OR (LOWER(english_meaning) LIKE ? AND english_meaning NOT LIKE '% %' AND CHAR_LENGTH(english_meaning) <= ?)"
                  . " OR (LOWER(alternate_meaning) LIKE ? AND alternate_meaning NOT LIKE '% %' AND CHAR_LENGTH(alternate_meaning) <= ?)"
                : "";

            // Domain boost: if a semantic domain is active, entries whose category
            // matches the domain get a lower sort rank (= preferred).
            $domainCase = '';
            if ($this->_currentDomain !== null) {
                $domain = $this->db->quote($this->_currentDomain);
                $domainCase = ", CASE WHEN LOWER(category) = {$domain} THEN 0 ELSE 1 END";
            }

            $sql = "SELECT * FROM daily_words
                    WHERE is_active = 1
                      AND (LOWER(english_meaning) = ?
                           OR LOWER(alternate_meaning) = ?
                           OR LOWER(english_meaning) = ?
                           OR LOWER(alternate_meaning) = ?
                           {$likeClause})
                    ORDER BY
                      CASE
                        WHEN LOWER(english_meaning) = ?   THEN 0
                        WHEN LOWER(alternate_meaning) = ? THEN 1
                        WHEN LOWER(english_meaning) = ?   THEN 2
                        WHEN LOWER(alternate_meaning) = ? THEN 3
                        ELSE 4
                      END
                      {$domainCase},
                      CHAR_LENGTH(english_meaning) ASC
                    LIMIT 8";

            $maxLen = mb_strlen($normalized) + 3; // single-word matches only
            $params = [$normalized, $normalized, $toForm, $toForm];
            if ($usePrefixLike) {
                $params = array_merge($params, [$prefix, $maxLen, $prefix, $maxLen]);
            }
            $params = array_merge($params, [$normalized, $normalized, $toForm, $toForm]);

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        return $stmt->fetchAll();
    }

    /**
     * Given multiple POS variants of the same word, pick the best one based on context.
     *
     * Resolution priority:
     *   1. Context signals from surrounding tokens (prev/next word)
     *   2. Intrinsic English POS of the current token (function words: pronouns,
     *      conjunctions, prepositions, articles)
     *   3. Trust SQL ordering — variants[0] is already the best match by exact/prefix rank
     *
     * The old frequency-based "noun > verb > ..." fallback was removed because it
     * caused "we"→"weather" (noun>pronoun), "my"→"yam/food" (noun>pronoun), and
     * "and"→"have learned driving" (bad noun entry over correct conjunction entry).
     */
    private function resolvePosByContext(
        array   $variants,
        ?string $prevToken,
        ?string $nextToken,
        string  $sourceLang,
        ?string $currentToken = null
    ): array {
        if (count($variants) === 1) return $variants[0];

        $prev = $prevToken    ? mb_strtolower($prevToken)    : null;
        $next = $nextToken    ? mb_strtolower($nextToken)    : null;
        $curr = $currentToken ? mb_strtolower($currentToken) : null;

        $wantedPos = null;

        if ($sourceLang === 'english') {
            // 1. Signals from surrounding tokens
            if ($prev === 'to') {
                $wantedPos = 'verb';
            } elseif (in_array($prev, ['the','a','an','this','that','these','those','my','your','his','her','our','their'])) {
                $wantedPos = 'noun';
            } elseif (in_array($prev, ['is','are','was','were','be','been','being','am'])) {
                $wantedPos = 'adjective';
            } elseif (in_array($prev, ['very','quite','rather','too','so'])) {
                $wantedPos = 'adjective';
            } elseif (in_array($next, ['the','a','an'])) {
                $wantedPos = 'adjective';
            }

            // 2. Intrinsic POS for English function words when no surrounding signal
            if ($wantedPos === null && $curr !== null) {
                $wantedPos = $this->guessEnglishFunctionPos($curr);
            }
        }

        // Try to find a variant that matches the wanted POS
        if ($wantedPos !== null) {
            foreach ($variants as $v) {
                if (($v['part_of_speech'] ?? '') === $wantedPos) {
                    return $v;
                }
            }
        }

        // 3. No match for wantedPos — return SQL-ordered best match (variants[0]).
        return $variants[0];
    }

    /**
     * Identify the expected POS for common English function words.
     * These words have stable POS regardless of sentence context.
     */
    /**
     * Detect the semantic domain of a sentence so we can prefer domain-appropriate
     * vocabulary when multiple translations exist.
     *
     * Returns a category tag or null (no specific domain detected).
     */
    public function detectSemanticDomain(string $text): ?string
    {
        $t = mb_strtolower($text);

        $domains = [
            'religion'   => ['pray','prayer','god','lord','church','jesus','bible','holy',
                             'worship','pastor','faith','spirit','salvation','aondo','ter'],
            'farming'    => ['farm','crop','harvest','plant','soil','seed','rain','field',
                             'yam','cassava','hoe','plough','cultivate','sowing'],
            'family'     => ['family','father','mother','son','daughter','brother','sister',
                             'husband','wife','child','children','home','house','parent'],
            'greeting'   => ['morning','afternoon','evening','hello','welcome','greet',
                             'how are you','good day','farewell','goodbye'],
            'health'     => ['sick','disease','medicine','hospital','doctor','pain','fever',
                             'treatment','heal','health','ill','injury'],
            'education'  => ['school','learn','teach','study','book','class','student',
                             'teacher','lesson','read','write','knowledge'],
            'food'       => ['eat','food','meal','cook','drink','rice','soup','meat',
                             'fish','breakfast','lunch','dinner','hunger'],
            'travel'     => ['journey','travel','road','walk','run','car','market',
                             'city','village','destination','arrive'],
        ];

        $scores = [];
        foreach ($domains as $domain => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (mb_strpos($t, $kw) !== false) $score++;
            }
            if ($score > 0) $scores[$domain] = $score;
        }

        if (empty($scores)) return null;
        arsort($scores);
        return array_key_first($scores);
    }

    private function guessEnglishFunctionPos(string $token): ?string
    {
        static $pronouns = [
            'i','me','my','mine','myself',
            'we','us','our','ours','ourselves',
            'you','your','yours','yourself','yourselves',
            'he','him','his','himself',
            'she','her','hers','herself',
            'it','its','itself',
            'they','them','their','theirs','themselves',
            'who','whom','whose','which','that',
        ];
        static $conjunctions = [
            'and','or','but','nor','for','yet','so',
            'both','either','neither','whether',
            'although','though','even though',
            'because','since','unless','until',
            'while','whereas','if','although',
        ];
        static $prepositions = [
            'in','on','at','by','to','of','up','as',
            'for','from','into','onto','upon',
            'with','about','above','across','after',
            'against','along','among','around',
            'before','behind','below','beneath',
            'beside','between','beyond','during',
            'except','inside','near','off','out',
            'outside','over','past','since',
            'through','till','under','until',
            'via','within','without',
        ];

        if (in_array($token, $pronouns, true))    return 'pronoun';
        if (in_array($token, $conjunctions, true)) return 'conjunction';
        if (in_array($token, $prepositions, true)) return 'preposition';

        return null;
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
        // Pre-scan: protect known multi-word phrases (4–10 words) before tokenising.
        // The main loop handles bigrams and trigrams; pre-scanning catches longer units
        // like "thank you very much", "have a nice day", "I don't speak Tiv", etc.
        [$normalized, $phraseMap] = $this->preScanPhrases($normalized, $sourceLang, $targetLang);

        $tokens = $this->tokenize($normalized);
        if (empty($tokens)) {
            return $this->noneResult($normalized);
        }

        // Grammar: a bare recognised English question word ("where", "who", …)
        // as the WHOLE input maps directly via the Grammar module's data — the
        // one safe, unambiguous case (see GrammarEngine docblock for why full
        // question-sentence restructuring is deliberately not attempted).
        if ($sourceLang === 'english' && count($tokens) === 1) {
            $qMatch = $this->grammar->mapQuestionWord($tokens[0]);
            if ($qMatch !== null) {
                return [
                    'translated_text'  => $this->capitalizeFirst($qMatch['tiv']),
                    'literal_meaning'  => $qMatch['tiv'],
                    'cultural_meaning' => '',
                    'usage_context'    => '',
                    'source_table'     => 'multiple',
                    'source_id'        => null,
                    'category'         => 'sentence',
                    'explanation'      => 'Tiv question word, from the Grammar module.',
                    'pronunciation'    => $this->phonology->generatePronunciation($qMatch['tiv']),
                    'word_results'     => [['token' => $tokens[0], 'result' => $qMatch['tiv'], 'source' => 'grammar-rule', 'pos' => '', 'found' => true]],
                    'missing_tokens'   => [],
                    'match_type'       => 'word_by_word',
                    'confidence_score' => 70,
                    'citations'        => [$qMatch['citation']],
                ];
            }
        }

        // Grammar: strip English negation markers before translating the rest
        // of the sentence; the Tiv clause-final particle is appended after
        // assembly below (see GrammarEngine::negationParticle()).
        $negated = false;
        if ($sourceLang === 'english') {
            [$tokens, $negated] = $this->grammar->stripNegation($tokens);
        }

        $translated         = [];
        $found              = 0;
        $wordResults        = [];
        $missingTokens      = [];
        $pronunciationParts = [];
        $citedWordIds       = [];
        $count              = count($tokens);
        $i                  = 0;

        while ($i < $count) {
            $chunkMatched = false;

            // Restore pre-scanned phrase placeholders (e.g. KPHR0, KPHR1 …)
            $token = $tokens[$i];
            if (isset($phraseMap[$token])) {
                $translated[]  = $phraseMap[$token];
                $wordResults[] = ['token' => $token, 'result' => $phraseMap[$token],
                                  'source' => 'phrase', 'pos' => '', 'found' => true];
                $found++;
                $i++;
                continue;
            }

            // Try 5-gram chunk (extended window)
            if (!$chunkMatched && $i + 4 < $count) {
                $fivegram = implode(' ', array_slice($tokens, $i, 5));
                $chunkText = $this->lookupPhraseChunk($fivegram, $sourceLang, $targetLang);
                if ($chunkText !== null) {
                    $translated[]  = $chunkText;
                    $wordResults[] = ['token' => $fivegram, 'result' => $chunkText, 'source' => 'phrase', 'pos' => '', 'found' => true];
                    $found++;
                    $i += 5;
                    $chunkMatched = true;
                }
            }

            // Try 4-gram chunk (extended window)
            if (!$chunkMatched && $i + 3 < $count) {
                $fourgram = implode(' ', array_slice($tokens, $i, 4));
                $chunkText = $this->lookupPhraseChunk($fourgram, $sourceLang, $targetLang);
                if ($chunkText !== null) {
                    $translated[]  = $chunkText;
                    $wordResults[] = ['token' => $fourgram, 'result' => $chunkText, 'source' => 'phrase', 'pos' => '', 'found' => true];
                    $found++;
                    $i += 4;
                    $chunkMatched = true;
                }
            }

            // Try 3-gram chunk against translation_phrases first
            if (!$chunkMatched && $i + 2 < $count) {
                $trigram = implode(' ', array_slice($tokens, $i, 3));
                $chunkText = $this->lookupPhraseChunk($trigram, $sourceLang, $targetLang);
                if ($chunkText !== null) {
                    $translated[]  = $chunkText;
                    $wordResults[] = ['token' => $trigram, 'result' => $chunkText, 'source' => 'phrase', 'pos' => '', 'found' => true];
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
                    $wordResults[] = ['token' => $bigram, 'result' => $chunkText, 'source' => 'phrase', 'pos' => '', 'found' => true];
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
                    // Look up the following noun first — pass article as prevToken so it resolves as noun
                    $nextToken = $tokens[$i + 1];
                    $nextMatch = $this->lookupToken($nextToken, $sourceLang, $targetLang, $token, $tokens[$i + 2] ?? null);
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
                        if (!empty($nextMatch['pronunciation'])) $pronunciationParts[] = $nextMatch['pronunciation'];
                        if (!empty($nextMatch['word_id'])) $citedWordIds[$nextMatch['word_id']] = true;
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

            // Fall back to single-token lookup — pass surrounding tokens for POS context
            if (!$chunkMatched) {
                $token     = $tokens[$i];
                $prevTok   = $i > 0           ? $tokens[$i - 1] : null;
                $nextTok   = $i + 1 < $count  ? $tokens[$i + 1] : null;
                $match = $this->lookupToken($token, $sourceLang, $targetLang, $prevTok, $nextTok);
                if ($match) {
                    $translated[]  = $match['text'];
                    $pos           = $match['pos'] ?? '';
                    $wordResults[] = ['token' => $token, 'result' => $match['text'], 'source' => $match['source'], 'pos' => $pos, 'found' => true];
                    $found++;
                    if (!empty($match['pronunciation'])) $pronunciationParts[] = $match['pronunciation'];
                    if (!empty($match['word_id'])) $citedWordIds[$match['word_id']] = true;
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

        // Grammar: append Tiv's clause-final negation particle (see
        // GrammarEngine::stripNegation() above, which removed the English
        // negation markers before word-by-word translation began).
        $citations = [];
        if ($negated) {
            $assembledText .= ' ' . $this->grammar->negationParticle();
            $wordResults[]  = ['token' => '(negation)', 'result' => $this->grammar->negationParticle(), 'source' => 'grammar-rule', 'pos' => '', 'found' => true];
            $negCitation = $this->grammar->negationCitation();
            if ($negCitation !== null) $citations[] = $negCitation;
        }

        // Citations: one entry per distinct dictionary word actually used
        // (capped to keep the "Sources" card readable on longer sentences).
        foreach (array_slice(array_keys($citedWordIds), 0, 6) as $wid) {
            $citations[] = ['type' => 'dictionary', 'table' => 'daily_words', 'id' => (int) $wid, 'label' => 'Tiv Dictionary'];
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
            'pronunciation'    => $this->joinPronunciationParts($pronunciationParts),
            'citations'        => $citations,
            'word_results'     => $wordResults,
            'missing_tokens'   => $missingTokens,
            'match_type'       => 'word_by_word',
            'confidence_score' => $confidence,
        ];
    }

    /**
     * Pre-scan a sentence for known multi-word phrases (6–20 words) and replace them
     * with unique placeholders before tokenisation.
     *
     * This catches phrases the bigram/trigram loop would never reach, such as:
     *   "thank you very much"  →  "Nande kpishi"
     *   "have a nice day"      →  "Iyange i doo"
     *   "I don't speak Tiv"    →  "M fa u lamen dzwa Tiv ga"
     *
     * Returns [prescanned_text, [placeholder => target_text]].
     * Placeholders (KPHR0, KPHR1 …) are restored inside wordByWord after tokenisation.
     */
    private function preScanPhrases(string $text, string $sourceLang, string $targetLang): array
    {
        // Load 6–20 word phrases (bigrams/trigrams/4/5-grams handled in main loop)
        $stmt = $this->db->prepare(
            "SELECT source_text, target_text FROM translation_phrases
             WHERE source_language = ? AND target_language = ?
               AND status = 'active' AND context_tag != 'bible'
               AND source_text LIKE '% % % % % %'
               AND CHAR_LENGTH(source_text) <= 120
             ORDER BY CHAR_LENGTH(source_text) DESC
             LIMIT 400"
        );
        $stmt->execute([$sourceLang, $targetLang]);
        $phrases = $stmt->fetchAll();

        $phraseMap = [];
        $counter   = 0;

        foreach ($phrases as $p) {
            $src = trim($p['source_text']);
            if ($src === '') continue;
            // Case-insensitive search in the text
            if (stripos($text, $src) !== false) {
                $ph = 'KPHR' . $counter++;
                $phraseMap[$ph] = $this->primaryMeaning(trim($p['target_text']));
                // Replace first occurrence only (greedy, longest-first due to ORDER BY)
                $text = preg_replace('/' . preg_quote($src, '/') . '/iu', $ph, $text, 1);
            }
        }

        return [$text, $phraseMap];
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

    private function lookupToken(
        string  $token,
        string  $sourceLang,
        string  $targetLang,
        ?string $prevToken = null,
        ?string $nextToken = null
    ): ?array {
        // Fetch all POS variants and pick the contextually best one
        $variants = $this->fetchAllPosVariants($token, $sourceLang);

        if (!empty($variants)) {
            $row = $this->resolvePosByContext($variants, $prevToken, $nextToken, $sourceLang, $token);

            $pronunciation = $this->wordPronunciation($row);

            if ($sourceLang === 'tiv') {
                $meaning = $this->primaryMeaning($row['english_meaning']);
                // Strip "to " prefix from verb forms in sentence context (e.g. "to walk" → "walk")
                if (strncasecmp($meaning, 'to ', 3) === 0) {
                    $meaning = substr($meaning, 3);
                }
                return [
                    'text'          => $meaning,
                    'source'        => 'words',
                    'pos'           => $row['part_of_speech'] ?? '',
                    'word_id'       => (int) $row['id'],
                    'pronunciation' => $pronunciation,
                ];
            } else {
                return [
                    'text'          => $row['tiv_word'],
                    'source'        => 'words',
                    'pos'           => $row['part_of_speech'] ?? '',
                    'word_id'       => (int) $row['id'],
                    'pronunciation' => $pronunciation,
                ];
            }
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
            'alternatives'     => [],
            'why_explanation'  => '',
            'citations'        => [],
        ];
    }

    // -------------------------------------------------------
    // ALTERNATIVE SUGGESTIONS
    // -------------------------------------------------------

    /**
     * Find alternative translations for the primary result.
     * Called after translateSegment() completes.
     */
    /**
     * Curated synonyms for a daily_words row via the knowledge_links graph
     * (relation_type='synonym', either direction). Degrades gracefully to
     * an empty list — this is enrichment, not a required data source.
     */
    private function fetchSynonymAlternatives(int $wordId, string $sourceLang, string $primaryLower): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT dw.tiv_word, dw.english_meaning, dw.part_of_speech
                 FROM knowledge_links kl
                 JOIN daily_words dw
                   ON (kl.source_table = 'daily_words' AND kl.target_table = 'daily_words'
                       AND ((kl.source_id = ? AND dw.id = kl.target_id)
                         OR (kl.target_id = ? AND dw.id = kl.source_id)))
                 WHERE kl.relation_type = 'synonym'
                   AND dw.is_active = 1
                 LIMIT 5"
            );
            $stmt->execute([$wordId, $wordId]);
            $rows = $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $text = $sourceLang === 'tiv' ? $row['english_meaning'] : $row['tiv_word'];
            if (mb_strtolower($text) === $primaryLower) continue;
            $out[] = [
                'text'   => $text,
                'note'   => 'synonym' . (!empty($row['part_of_speech']) ? " [{$row['part_of_speech']}]" : ''),
                'source' => 'synonym',
                'pos'    => $row['part_of_speech'] ?? '',
            ];
        }
        return $out;
    }

    public function findAlternatives(
        string $input,
        string $sourceLang,
        string $targetLang,
        string $primaryResult,
        array  $posVariants = [],  // pre-fetched from matchWord()
        ?int   $wordId = null      // daily_words.id of the matched word, when known
    ): array {
        $normalized   = $this->normalize($input);
        $alternatives = [];
        $primaryLower = mb_strtolower($primaryResult);

        // 1.5. Curated synonyms (knowledge_links) for the matched word — real
        // editorial data, so these come before the fuzzy LIKE-based tier below.
        if ($wordId !== null) {
            $alternatives = array_merge($alternatives, $this->fetchSynonymAlternatives($wordId, $sourceLang, $primaryLower));
        }

        // 1. OTHER POS VARIANTS of the same word (most important for disambiguation)
        if (!empty($posVariants) && count($posVariants) > 1) {
            foreach ($posVariants as $v) {
                $altText = $sourceLang === 'tiv' ? $v['english_meaning'] : $v['tiv_word'];
                if (mb_strtolower($altText) === $primaryLower) continue;
                $pos = $v['part_of_speech'] ?? '';
                $note = $pos ? "[{$pos}]" : '';
                if (!empty($v['example_tiv'])) {
                    $note .= ' — e.g. "' . mb_substr($v['example_tiv'], 0, 50) . '"';
                }
                $alternatives[] = [
                    'text'   => $altText,
                    'note'   => $note ?: 'alternate POS',
                    'source' => 'dictionary',
                    'pos'    => $pos,
                ];
            }
        }

        // 2. If no pre-fetched variants, query for same-word different POS
        if (empty($alternatives)) {
            $variantRows = $this->fetchAllPosVariants($normalized, $sourceLang);
            foreach ($variantRows as $v) {
                $altText = $sourceLang === 'tiv' ? $v['english_meaning'] : $v['tiv_word'];
                if (mb_strtolower($altText) === $primaryLower) continue;
                $pos  = $v['part_of_speech'] ?? '';
                $alternatives[] = [
                    'text'   => $altText,
                    'note'   => $pos ? "[{$pos}]" : 'alternate meaning',
                    'source' => 'dictionary',
                    'pos'    => $pos,
                ];
            }
        }

        // 3. Other dictionary entries with similar meanings (different words, same concept)
        if ($sourceLang === 'english') {
            $like = '%' . $normalized . '%';
            $stmt = $this->db->prepare(
                "SELECT tiv_word, english_meaning, part_of_speech
                 FROM daily_words
                 WHERE (LOWER(english_meaning) LIKE ? OR LOWER(alternate_meaning) LIKE ?)
                   AND LOWER(tiv_word) != ?
                   AND is_active = 1
                 LIMIT 3"
            );
            $stmt->execute([$like, $like, $primaryLower]);
            foreach ($stmt->fetchAll() as $row) {
                if (!in_array($row['tiv_word'], array_column($alternatives, 'text'))) {
                    $pos = $row['part_of_speech'] ?? '';
                    $alternatives[] = [
                        'text'   => $row['tiv_word'],
                        'note'   => ($pos ? "[{$pos}] " : '') . $row['english_meaning'],
                        'source' => 'dictionary',
                        'pos'    => $pos,
                    ];
                }
            }
        }

        // 4. Related phrases
        $stmt2 = $this->db->prepare(
            "SELECT target_text, context_tag
             FROM translation_phrases
             WHERE source_language = ? AND target_language = ?
               AND LOWER(source_text) LIKE ?
               AND LOWER(target_text) != ?
               AND status = 'active'
             LIMIT 2"
        );
        $stmt2->execute([$sourceLang, $targetLang, '%' . $normalized . '%', $primaryLower]);
        foreach ($stmt2->fetchAll() as $row) {
            if (!in_array($row['target_text'], array_column($alternatives, 'text'))) {
                $alternatives[] = [
                    'text'   => $row['target_text'],
                    'note'   => $row['context_tag'] ? 'phrase (' . $row['context_tag'] . ')' : 'phrase',
                    'source' => 'phrases',
                    'pos'    => '',
                ];
            }
        }

        return array_slice($alternatives, 0, 5);
    }

    /**
     * Build a human-readable explanation of why this translation was chosen.
     */
    public function buildWhyExplanation(array $result): string
    {
        $matchType = $result['match_type'] ?? 'none';
        $source    = $result['source_table'] ?? '';
        $conf      = (int) ($result['confidence_score'] ?? 0);

        return match(true) {
            $matchType === 'proverb' =>
                "This is an exact match from the Tiv Proverbs archive. Proverbs are preserved verbatim with their verified English translations.",
            $matchType === 'phrase' && $source === 'translation_phrases' =>
                "Matched a curated phrase in the translation database (confidence: {$conf}%). Curated phrases are reviewed and approved by the archive team.",
            $matchType === 'word' && $source === 'daily_words' =>
                "Found in the Tiv Dictionary with a direct word match (confidence: {$conf}%). The dictionary contains verified Tiv ↔ English vocabulary.",
            $matchType === 'category' =>
                "Matched a named entity in the Tiv culture archive (name, food, plant, festival, or animal). These have verified bilingual entries.",
            $matchType === 'bible' =>
                "Found in the Tiv Bible (Icighan Bibilo). The Bible is the largest verified bilingual Tiv text in the archive.",
            $matchType === 'learned' =>
                "This translation was learned from high-confidence past translations recorded in the archive (confidence: {$conf}%).",
            $matchType === 'bible_refined' =>
                "Initial word-by-word translation was refined using the Tiv Bible corpus to resolve unknown tokens.",
            $matchType === 'word_by_word' =>
                "Translated word-by-word using the dictionary and grammar rules. Some words may not have been found — see 'Words Not in Dictionary' below.",
            $matchType === 'none' =>
                "No match found in the archive. Consider contributing this translation to help improve the engine.",
            default =>
                "Matched via archive search (confidence: {$conf}%).",
        };
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
