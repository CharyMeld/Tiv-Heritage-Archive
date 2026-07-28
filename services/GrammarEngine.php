<?php
/**
 * Grammar Engine — Grammar module wired into the Translation Engine.
 *
 * Deliberately conservative: only two English->Tiv transformations are
 * applied, each traceable to a specific `tiv_grammar_rules` row so the
 * translation can cite exactly which linguistic rule it used.
 *
 *  - Negation: strip English negation markers before word-by-word
 *    translation, then the caller appends Tiv's trailing "ga" particle.
 *  - Question words: data-driven single-word lookup built from the
 *    "Question Words" rule's own example data (not hardcoded pairs
 *    disconnected from the source). Only fires for a bare recognised
 *    question word as the whole input — no sentence restructuring.
 *
 * No general word-order/reordering heuristics — a confidently wrong
 * rewrite is worse than an honest lower-confidence word-by-word result
 * on a public site.
 */

class GrammarEngine
{
    private PDO $db;

    /** lowercase english question word => tiv equivalent */
    private array $questionWordMap = [];
    private ?int $questionWordsRuleId = null;
    private ?string $questionWordsRuleTitle = null;

    private ?int $negationRuleId = null;
    private ?string $negationRuleTitle = null;

    private const NEGATION_TOKENS = [
        'not', 'never', 'cannot',
        "can't", "don't", "doesn't", "didn't", "won't", "wouldn't",
        "couldn't", "shouldn't", "isn't", "aren't", "wasn't", "weren't",
        "haven't", "hasn't", "hadn't",
    ];

    /** Auxiliary verbs stripped only when immediately followed by "not" */
    private const NEGATION_AUX_STRIP = ['do', 'does', 'did'];

    /**
     * Object-case pronoun overrides. Tiv marks some pronouns differently
     * when they're the object of a verb rather than its subject (e.g. "we
     * saw you" takes "ven", not the subject form "u"). Confirmed directly
     * by Charles against the "we saw you yesterday..." mistranslation —
     * "us" is unambiguous in English (always object-case), "you" is only
     * swapped in when context marks it as the object (see objectPronoun()).
     */
    private const OBJECT_PRONOUNS = [
        'us'  => 'vese',
        'you' => 'ven',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->load();
    }

    private function load(): void
    {
        require_once BASE_PATH . '/models/TivGrammarRule.php';
        $model = new TivGrammarRule();

        foreach ($model->getByCategory('question_formation') as $rule) {
            if ($rule['title'] !== 'Question Words') continue;
            $this->questionWordsRuleId    = (int) $rule['id'];
            $this->questionWordsRuleTitle = $rule['title'];
            foreach ($rule['examples'] as $ex) {
                $english = mb_strtolower(trim($ex['english'] ?? ''), 'UTF-8');
                $tiv     = trim($ex['tiv'] ?? '');
                if ($english === '' || $tiv === '') continue;
                // "ana, an" -> use the first form as the primary answer
                $firstTiv = trim(explode(',', $tiv)[0]);
                if (!isset($this->questionWordMap[$english])) {
                    $this->questionWordMap[$english] = $firstTiv;
                }
            }
        }

        foreach ($model->getByCategory('sentence_structure') as $rule) {
            if ($rule['title'] === 'Word Order & Pronoun-Object Substitution') {
                $this->negationRuleId    = (int) $rule['id'];
                $this->negationRuleTitle = $rule['title'];
            }
        }
    }

    /**
     * Strip English negation markers from an already-tokenized input
     * (english source only). Returns [strippedTokens, wasNegated].
     */
    public function stripNegation(array $tokens): array
    {
        $negated = false;
        $out     = [];
        $i       = 0;
        $n       = count($tokens);

        while ($i < $n) {
            $t = mb_strtolower($tokens[$i], 'UTF-8');

            if (in_array($t, self::NEGATION_TOKENS, true)) {
                $negated = true;
                $i++;
                continue;
            }

            $next = isset($tokens[$i + 1]) ? mb_strtolower($tokens[$i + 1], 'UTF-8') : null;
            if (in_array($t, self::NEGATION_AUX_STRIP, true) && $next === 'not') {
                $negated = true;
                $i += 2; // drop both the auxiliary and "not"
                continue;
            }

            $out[] = $tokens[$i];
            $i++;
        }

        return [$out, $negated];
    }

    /** The Tiv clause-final negation particle documented in the grammar data */
    public function negationParticle(): string
    {
        return 'ga';
    }

    public function negationCitation(): ?array
    {
        if ($this->negationRuleId === null) return null;
        return [
            'type'  => 'grammar',
            'table' => 'tiv_grammar_rules',
            'id'    => $this->negationRuleId,
            'label' => $this->negationRuleTitle,
        ];
    }

    /**
     * If the input is exactly one recognised bare English question word,
     * return its Tiv equivalent + citation. Deliberately does not attempt
     * to handle full question sentences ("where is the market") — only
     * the safe, unambiguous single-word case.
     */
    /**
     * "us" always maps to its object form; "you" only does when the caller
     * has determined (from sentence context) that it's in object position —
     * pass $isObjectPosition=false for the ambiguous subject case and the
     * normal subject-pronoun dictionary lookup is used instead.
     */
    public function objectPronoun(string $englishWord, bool $isObjectPosition): ?string
    {
        $word = mb_strtolower(trim($englishWord), 'UTF-8');
        if ($word === 'us') return self::OBJECT_PRONOUNS['us'];
        if ($word === 'you' && $isObjectPosition) return self::OBJECT_PRONOUNS['you'];
        return null;
    }

    /** Linking particle required immediately before an object-case pronoun (confirmed by Charles). */
    public function objectPronounParticle(): string
    {
        return 'a';
    }

    /**
     * Tiv time adverbs that front the sentence rather than staying where the
     * English word fell. Confirmed by Charles for "yesterday" (nyen) against
     * the "we saw you yesterday..." correction, then explicitly extended by
     * him to the other daily_words time-adverb entries (today/tomorrow/now).
     */
    private const FRONTING_TIME_ADVERBS = ['nyen', 'nyian', 'kper', 'hegen'];

    public function frontingTimeAdverbs(): array
    {
        return self::FRONTING_TIME_ADVERBS;
    }

    public function timeAdverbFrontingCitation(): array
    {
        return [
            'type'  => 'grammar',
            'table' => 'daily_words',
            'id'    => null,
            'label' => 'Time-adverb word order, confirmed by Charles',
        ];
    }

    public function objectPronounCitation(): ?array
    {
        if ($this->negationRuleId === null) return null;
        return [
            'type'  => 'grammar',
            'table' => 'tiv_grammar_rules',
            'id'    => $this->negationRuleId,
            'label' => $this->negationRuleTitle,
        ];
    }

    public function mapQuestionWord(string $normalizedInput): ?array
    {
        $key = mb_strtolower(trim($normalizedInput), 'UTF-8');
        if (!isset($this->questionWordMap[$key])) return null;

        return [
            'tiv'      => $this->questionWordMap[$key],
            'citation' => [
                'type'  => 'grammar',
                'table' => 'tiv_grammar_rules',
                'id'    => $this->questionWordsRuleId,
                'label' => $this->questionWordsRuleTitle,
            ],
        ];
    }
}
