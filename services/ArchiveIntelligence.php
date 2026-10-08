<?php
/**
 * ArchiveIntelligence — Self-contained RAG engine for the Tiv Heritage Archive.
 *
 * Replaces the external Claude API. All knowledge comes from the archive database.
 * Workflow per message:
 *   1. Parse query intent (word lookup, proverb, cultural question, translation…)
 *   2. Search archive_search_index (FULLTEXT) + targeted table searches
 *   3. Traverse knowledge graph for related records
 *   4. Compose a natural, cited response from retrieved data
 *
 * Automatically knows about every new record because it queries live DB tables.
 */
require_once BASE_PATH . '/services/SemanticSearch.php';
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/EmbeddingSearch.php';
require_once BASE_PATH . '/services/HeritagePublic.php';
require_once BASE_PATH . '/services/HeritageGraph.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

class ArchiveIntelligence
{
    private PDO $db;
    private SemanticSearch $semantic;
    private EmbeddingSearch $embeddings;

    // Intent types
    const INTENT_WORD        = 'word_lookup';
    const INTENT_NAME        = 'name_lookup';
    const INTENT_PROVERB     = 'proverb';
    const INTENT_FOOD        = 'food';
    const INTENT_PLANT       = 'plant';
    const INTENT_FESTIVAL    = 'festival';
    const INTENT_ANIMAL      = 'animal';
    const INTENT_TRANSLATION = 'translation_help';
    const INTENT_GENERAL     = 'general';
    const INTENT_GREETING    = 'greeting';
    const INTENT_LIST        = 'list_request';
    const INTENT_COMPARE     = 'compare';
    const INTENT_SUMMARIZE   = 'summarize';
    const INTENT_FOLLOWUP    = 'follow_up';
    const INTENT_YESNO       = 'yes_no';
    const INTENT_PERSON      = 'person';
    const INTENT_HISTORY     = 'history';
    const INTENT_STATS       = 'archive_stats';
    const INTENT_GRAMMAR     = 'grammar';
    const INTENT_HELP        = 'help_capabilities';

    // ─────────────────────────────────────────────────────────────
    // Conditional archive statistics — entity/property registries.
    // Column names here are taken directly from the live schema (never assumed);
    // an entity/property combination is only listed when the column genuinely
    // exists and can meaningfully be "present" or "missing". These registries
    // are the ONLY source of table/column names ever interpolated into a stats
    // SQL string — user text is matched against them, never concatenated in.
    // ─────────────────────────────────────────────────────────────
    private const STATS_ENTITIES = [
        'plants' => [
            'table' => 'tiv_plants', 'where' => null,
            'singular' => 'Plant', 'plural' => 'Plants',
            'aliases' => ['plants', 'plant', 'herbs', 'herb', 'trees', 'tree'],
        ],
        'animals' => [
            'table' => 'tiv_animals', 'where' => null,
            'singular' => 'Animal', 'plural' => 'Animals',
            'aliases' => ['animals', 'animal', 'birds', 'bird', 'creatures', 'creature'],
        ],
        'foods' => [
            'table' => 'tiv_foods', 'where' => null,
            'singular' => 'Food', 'plural' => 'Foods',
            'aliases' => ['foods', 'food', 'dishes', 'dish', 'meals', 'meal', 'recipes', 'recipe', 'cuisine'],
        ],
        'festivals' => [
            'table' => 'tiv_festivals', 'where' => null,
            'singular' => 'Festival', 'plural' => 'Festivals',
            'aliases' => ['festivals', 'festival', 'celebrations', 'celebration', 'ceremonies', 'ceremony'],
        ],
        'names' => [
            'table' => 'tiv_names', 'where' => null,
            'singular' => 'Name', 'plural' => 'Names',
            'aliases' => ['names', 'name'],
        ],
        'proverbs' => [
            'table' => 'tiv_proverbs', 'where' => null,
            'singular' => 'Proverb', 'plural' => 'Proverbs',
            'aliases' => ['proverbs', 'proverb', 'sayings', 'saying', 'adages', 'adage'],
        ],
        'words' => [
            'table' => 'daily_words', 'where' => 'is_active = 1',
            'singular' => 'Dictionary word', 'plural' => 'Dictionary words',
            'aliases' => ['dictionary words', 'dictionary word', 'words', 'word', 'vocabulary'],
        ],
        'historical_figures' => [
            'table' => 'historical_figures', 'where' => "status = 'published'",
            'singular' => 'Historical figure', 'plural' => 'Historical figures',
            'aliases' => ['historical figures', 'historical figure', 'figures', 'figure'],
        ],
        'timeline_events' => [
            'table' => 'timeline_events', 'where' => null,
            'singular' => 'Timeline event', 'plural' => 'Timeline events',
            'aliases' => ['timeline events', 'timeline event', 'timeline', 'events', 'event'],
        ],
        'grammar' => [
            'table' => 'tiv_grammar_rules', 'where' => null,
            'singular' => 'Grammar rule', 'plural' => 'Grammar rules',
            'aliases' => ['grammar rules', 'grammar rule', 'grammar'],
        ],
        'bible' => [
            'table' => 'bible_verses', 'where' => null,
            'singular' => 'Bible verse', 'plural' => 'Bible verses',
            'aliases' => ['bible verses', 'bible verse', 'verses', 'verse'],
        ],
    ];

    // entity key => property key => ['column' => real column, 'mode' => null_empty|json_empty|boolean, 'true' => value]
    private const STATS_PROPERTIES = [
        'plants' => [
            'image'       => ['column' => 'image', 'mode' => 'null_empty'],
            'audio'       => ['column' => 'audio_file', 'mode' => 'null_empty'],
            'description' => ['column' => 'description', 'mode' => 'null_empty'],
        ],
        'animals' => [
            'image'       => ['column' => 'image', 'mode' => 'null_empty'],
            'description' => ['column' => 'description', 'mode' => 'null_empty'],
        ],
        'foods' => [
            'image'       => ['column' => 'image', 'mode' => 'null_empty'],
            'description' => ['column' => 'description', 'mode' => 'null_empty'],
        ],
        'festivals' => [
            'image'       => ['column' => 'image', 'mode' => 'null_empty'],
            'description' => ['column' => 'description', 'mode' => 'null_empty'],
        ],
        'names' => [
            'audio'       => ['column' => 'audio_file', 'mode' => 'null_empty'],
            'description' => ['column' => 'description', 'mode' => 'null_empty'],
        ],
        'proverbs' => [
            'translation' => ['column' => 'english_translation', 'mode' => 'null_empty'],
            'meaning'     => ['column' => 'deeper_meaning', 'mode' => 'null_empty'],
        ],
        'words' => [
            'audio'             => ['column' => 'audio_file', 'mode' => 'null_empty'],
            'example'           => ['column' => 'example_tiv', 'mode' => 'null_empty'],
            'alternate_meaning' => ['column' => 'alternate_meaning', 'mode' => 'null_empty'],
            'active'            => ['column' => 'is_active', 'mode' => 'boolean', 'true' => '1'],
        ],
        'historical_figures' => [
            'image'     => ['column' => 'image', 'mode' => 'null_empty'],
            'biography' => ['column' => 'biography', 'mode' => 'null_empty'],
            'published' => ['column' => 'status', 'mode' => 'boolean', 'true' => 'published'],
        ],
        'timeline_events' => [
            'image'       => ['column' => 'image', 'mode' => 'null_empty'],
            'description' => ['column' => 'description', 'mode' => 'null_empty'],
        ],
        'grammar' => [
            'example' => ['column' => 'examples', 'mode' => 'json_empty'],
        ],
        'bible' => [
            'translation' => ['column' => 'tiv', 'mode' => 'null_empty'],
        ],
    ];

    // property key => regex matching natural-language phrasings for it
    private const STATS_PROPERTY_ALIASES = [
        'image'             => '/\b(images?|pictures?|photos?|photographs?|thumbnails?)\b/u',
        'audio'             => '/\b(audio|sounds?|recordings?|pronunciations?)\b/u',
        'description'       => '/\bdescriptions?\b/u',
        'translation'       => '/\b(translations?|translated)\b/u',
        'biography'         => '/\b(biograph(?:y|ies)|bios?)\b/u',
        'example'           => '/\bexamples?\b/u',
        'meaning'           => '/\b(deeper meaning|cultural meaning)\b/u',
        'alternate_meaning' => '/\b(alternate meaning|alternative meaning)\b/u',
        'published'         => '/\b(published|unpublished)\b/u',
        'active'            => '/\b(active|inactive)\b/u',
    ];

    // FETCH pagination — a chat reply can never dump thousands of rows, so every
    // FETCH page is capped and driven by SQL LIMIT/OFFSET, never in-memory slicing.
    private const STATS_FETCH_DEFAULT_PER_PAGE = 25;
    private const STATS_FETCH_MAX_PER_PAGE     = 100;

    // entity key => which columns FETCH is allowed to display for a record.
    // Reuses the same table names as STATS_ENTITIES; never selects columns beyond
    // this allow-list (no SELECT *), and only fields that genuinely exist per the
    // live schema — the same fields already used by the compose*Response() prose
    // composers, so FETCH never shows a field the rest of the app doesn't.
    private const STATS_FETCH_FIELDS = [
        'plants' => [
            'title'  => 'tiv_name',
            'fields' => ['English Name' => 'english_name', 'Scientific Name' => 'scientific_name', 'Description' => 'description'],
            'image_column' => 'image',
        ],
        'animals' => [
            'title'  => 'tiv_name',
            'fields' => ['English Name' => 'name', 'Description' => 'description'],
            'image_column' => 'image',
        ],
        'foods' => [
            'title'  => 'tiv_name',
            'fields' => ['English Name' => 'english_name', 'Description' => 'description'],
            'image_column' => 'image',
        ],
        'festivals' => [
            'title'  => 'tiv_name',
            'fields' => ['English Name' => 'english_name', 'Description' => 'description'],
            'image_column' => 'image',
        ],
        'names' => [
            'title'  => 'tiv_name',
            'fields' => ['English Meaning' => 'english_meaning', 'Gender' => 'gender'],
            'image_column' => null,
        ],
        'proverbs' => [
            'title'  => 'tiv_text',
            'fields' => ['English Translation' => 'english_translation'],
            'image_column' => null,
        ],
        'words' => [
            'title'  => 'tiv_word',
            'fields' => ['English Meaning' => 'english_meaning', 'Part of Speech' => 'part_of_speech'],
            'image_column' => null,
        ],
        'historical_figures' => [
            'title'  => 'english_name',
            'fields' => ['Title' => 'title', 'Period' => 'historical_period'],
            'image_column' => 'image',
        ],
        'timeline_events' => [
            'title'  => 'title',
            'fields' => ['Event Date' => 'event_date', 'Era' => 'era'],
            'image_column' => 'image',
        ],
        'grammar' => [
            'title'  => 'title',
            'fields' => ['Category' => 'category', 'Summary' => 'summary'],
            'image_column' => null,
        ],
        'bible' => [
            'title'  => 'book',
            'fields' => ['English Text' => 'english_web'],
            'image_column' => null,
        ],
    ];

    public function __construct(PDO $db)
    {
        $this->db         = $db;
        $this->semantic   = new SemanticSearch($db);
        $this->embeddings = new EmbeddingSearch($db);
    }

    // ─────────────────────────────────────────────────────────────
    // Main entry point (mirrors CharymeldService::chat signature)
    // ─────────────────────────────────────────────────────────────

    public function chat(string $message, string $context = '', array $history = []): string
    {
        $message = trim($message);
        if ($message === '') {
            return 'I did not receive a message. Please type your question.';
        }

        // Archive-statistics follow-up continuation. Once the previous assistant turn
        // was itself a stats reply (detected via its machine-parseable footer), keep
        // routing here even when THIS message has no "how many" of its own — "What
        // about animals?", "without images?", "that doesn't answer my question" — so
        // it is never mis-classified as a normal record search or dictionary lookup.
        // Bypasses pronoun resolution and the LLM entirely; the count always comes
        // straight from a live COUNT(*) query.
        $priorStats = $this->lastStatsFooterContext($history);
        if ($priorStats !== null && $this->looksLikeStatsFollowUp($message, $priorStats)) {
            return $this->composeStatsResponse($message, $history);
        }

        // Nigeria Heritage graph questions ("Which states have documented Tiv communities?",
        // "What ethnic groups are in Benue State?"). Answered only from published, sourced
        // relations; returns null — and the normal flow continues unchanged — unless the
        // question matches and names a published national record.
        try {
            $graphAnswer = (new HeritageGraph($this->db))->answer($message);
            if ($graphAnswer !== null) return $graphAnswer;
        } catch (\Throwable $e) {
            error_log('ArchiveIntelligence HeritageGraph error: ' . $e->getMessage());
        }

        // Resolve follow-up references ("it", "that", "the same") using conversation history.
        // Also capture the last entity (name + its exact source record, when known) so we
        // can use it as the subject directly on follow-ups.
        $lastEntity = null;
        $entityRef  = null;
        try {
            [$resolvedMessage, $lastEntity, $entityRef] = $this->resolveFollowUpWithEntity($message, $history);
        } catch (\Throwable $e) {
            error_log('ArchiveIntelligence resolveFollowUp error: ' . $e->getMessage());
            $resolvedMessage = $message;
        }
        $isFollowUp = ($resolvedMessage !== $message);

        // Acknowledgement sentinel — user said "okay", "got it", "thanks" etc.
        // Don't repeat the last entry; give a conversational bridge instead.
        if ($resolvedMessage === '__ACKNOWLEDGEMENT__') {
            return $this->acknowledgementResponse($lastEntity ?? '');
        }

        // Detect intent — run on ORIGINAL message first to catch "what is it for?" style
        // patterns that get lost after pronoun resolution ("what is kwahir kwaghalom for?")
        $intent = $this->detectIntent($message);
        // If original gave INTENT_GENERAL, refine using the resolved version
        if ($intent === self::INTENT_GENERAL) {
            $intent = $this->detectIntent($resolvedMessage);
        }

        // Extract how many items the user wants ("give me three" → 3)
        $requestedCount = $this->extractRequestedCount($resolvedMessage);

        // Extract the subject term.
        // For follow-ups, prefer the resolved entity (cleaner than re-extracting from the
        // modified message which can include qualifier words like "male", "female", "example").
        if ($isFollowUp && $lastEntity) {
            $subject = mb_strtolower($lastEntity);
        } else {
            $subject = $this->extractSubject($resolvedMessage, $intent);
        }

        // For compare intent, extract both subjects
        $compareSubjects = [];
        if ($intent === self::INTENT_COMPARE) {
            $compareSubjects = $this->extractCompareSubjects($resolvedMessage);
            $subject = $compareSubjects[0] ?? $subject;
        }

        // Same-entity continuation ("tell me more about that", "what about his
        // achievements?") with no specific content-type keyword of its own — GENERAL and
        // SUMMARIZE both land here for exactly that reason ("tell me more about X" itself
        // matches the SUMMARIZE intent pattern). A content-type request like "proverbs
        // about it" already resolves to a more specific intent and is handled by the
        // normal targeted search below, correctly scoped to $subject. When we know the
        // EXACT record the previous turn was about, fetch it directly by primary key
        // instead of re-searching by text — a fuzzy re-search of "father" or "achievements"
        // style follow-up text can't accidentally pull in an unrelated record if we never
        // search at all.
        $isSameEntityContinuation = in_array($intent, [self::INTENT_GENERAL, self::INTENT_SUMMARIZE], true);

        // A message that re-names the entity we were just discussing by its OWN name
        // ("What did George Akume achieve as governor?") rather than a pronoun never
        // sets $isFollowUp (messageHasNewSubject() correctly treats a proper noun as
        // a self-contained subject) — but extractSubject()'s generic fallback has no
        // pattern for "what did X do/achieve" style questions and would otherwise grab
        // a garbled few-word fragment ("george akume achieve") and search on that
        // instead of the exact record we already know is right. Detect this directly.
        $sameEntityByName = $entityRef !== null && $this->messageNamesEntity($resolvedMessage, $entityRef['name']);

        if (($isFollowUp || $sameEntityByName) && $entityRef && $isSameEntityContinuation) {
            $entityRow = $this->fetchEntityByRef($entityRef);
            $results = $entityRow ? ['primary' => [$entityRow], 'extras' => []] : ['primary' => [], 'extras' => []];
        } else {
            // Search the archive
            $results = $this->searchArchive($resolvedMessage, $subject, $intent, $requestedCount);
        }

        // Enrich with knowledge graph links
        if (!empty($results['primary'])) {
            $results['related'] = $this->fetchRelated($results['primary']);
        } else {
            $results['related'] = [];
        }

        // Compose natural response
        return $this->composeResponse($resolvedMessage, $intent, $subject, $results, $history, $compareSubjects, $requestedCount, $isFollowUp);
    }

    // ─────────────────────────────────────────────────────────────
    // Conversation context: resolve follow-up references
    // ─────────────────────────────────────────────────────────────

    /**
     * Extract the first follow-up suggestion offered by the AI in the last turn
     * and convert it into a targeted search query.
     *
     * Parses: "*Would you like to [ACTION1], or [ACTION2]?*"
     * Returns a query string for ACTION1, or null if no suggestion found.
     */
    private function extractFollowUpSuggestionAsQuery(array $history): ?string
    {
        // Find the last assistant message
        foreach (array_reverse($history) as $turn) {
            if (($turn['role'] ?? '') !== 'assistant') continue;
            $content = $turn['content'] ?? '';

            // Extract entity name from the follow-up (bolded in the question)
            $entity = '';
            if (preg_match('/\*\*([^*\n]{2,40})\*\*/u', $content, $em)) {
                $entity = trim($em[1]);
            }

            // Match the follow-up question pattern
            // "*Would you like to [SUGGESTION1], or [SUGGESTION2]?*"
            if (preg_match('/\*Would you like to (.+?)(?:,? or .+?)?\?\*/isu', $content, $m)) {
                $suggestion = mb_strtolower(trim($m[1]));

                // Map the suggestion phrasing to a concrete query
                if (preg_match('/example\s*sentence|use .+ in a sentence/iu', $suggestion)) {
                    return $entity ? "show me an example using {$entity}" : "give me an example sentence";
                }
                if (preg_match('/related.*word|find.*word|explore.*word/iu', $suggestion)) {
                    return $entity ? "find words related to {$entity}" : "show me related words";
                }
                if (preg_match('/deeper.*meaning|cultural.*meaning/iu', $suggestion)) {
                    return $entity ? "what is the deeper meaning of {$entity}" : "tell me the deeper meaning";
                }
                if (preg_match('/how.*prepared|preparation/iu', $suggestion)) {
                    return $entity ? "how is {$entity} prepared" : "tell me about preparation";
                }
                if (preg_match('/medicinal.*use/iu', $suggestion)) {
                    return $entity ? "what are the medicinal uses of {$entity}" : "tell me about medicinal uses";
                }
                if (preg_match('/when.*celebrat|activities.*involved/iu', $suggestion)) {
                    return $entity ? "when is {$entity} celebrated" : "when is it celebrated";
                }
                if (preg_match('/cultural.*role|cultural.*significance/iu', $suggestion)) {
                    return $entity ? "what is the cultural role of {$entity}" : "tell me about cultural significance";
                }
                if (preg_match('/proverb/iu', $suggestion)) {
                    return $entity ? "show me a proverb about {$entity}" : "give me a proverb";
                }
                if (preg_match('/ritual.*significance|ritual.*use/iu', $suggestion)) {
                    return $entity ? "what is the ritual significance of {$entity}" : "tell me about ritual uses";
                }
                if (preg_match('/other.*festivals|explore.*festivals/iu', $suggestion)) {
                    return "show me some Tiv festivals";
                }
                if (preg_match('/other.*plants|medicinal.*plants/iu', $suggestion)) {
                    return "show me some medicinal plants";
                }
                if (preg_match('/other.*foods|traditional.*food/iu', $suggestion)) {
                    return "show me some traditional Tiv foods";
                }
                if (preg_match('/names.*similar|similar.*meaning/iu', $suggestion)) {
                    return $entity ? "find names similar to {$entity}" : "show me similar names";
                }
                if (preg_match('/origin|where.*name.*from/iu', $suggestion)) {
                    return $entity ? "what is the origin of {$entity}" : "tell me the origin";
                }
                // Default: tell me more about the entity
                return $entity ? "tell me more about {$entity}" : "tell me more";
            }

            // Also handle "*Does X have...? Ask me about it.*" pattern
            if (preg_match('/\*Does .+\? Ask me about it\.\*/isu', $content) && $entity) {
                return "tell me more about {$entity}";
            }

            break; // Only look at the most recent assistant turn
        }
        return null;
    }

    /**
     * Wrapper that returns [resolvedMessage, lastEntity] so callers can
     * use the entity directly as the search subject on follow-ups.
     */
    private function resolveFollowUpWithEntity(string $message, array $history): array
    {
        if (empty($history)) return [$message, null, null];

        $entityRef  = $this->extractEntityRefFromLastAssistantTurn($history);
        $lastEntity = $entityRef['name'] ?? $this->currentEntityNameFallback($history);

        $resolved = $this->resolveFollowUp($message, $history);
        return [$resolved, $lastEntity, $entityRef];
    }

    /**
     * The old, less reliable name-only extraction — bold-markdown scan, then a
     * capitalized-word guess, then a last-resort scan of the user's own prior
     * message. Used only when extractEntityRefFromLastAssistantTurn() finds no
     * citation link to work from (e.g. a greeting or a pure not-found reply).
     */
    private function currentEntityNameFallback(array $history): ?string
    {
        $name = $this->extractEntityFromLastAssistantTurn($history);
        if (!$name) {
            $name = $this->extractLastSubjectFromHistory($history);
        }
        return $name;
    }

    /**
     * True if the ENTIRE message is a bare acknowledgement/decline — "Yes.", "No,
     * thank you", "Okay", "Nah, I'm good" — never a real search query. Single shared
     * pattern for both call sites below; they used to each carry their own near-
     * duplicate regex, which is exactly how "no, thank you" went unrecognized for as
     * long as it did — one copy could be fixed while the other quietly kept drifting.
     * A polite decline is not just a bare word: "no"/"yes"/"nah" are commonly followed
     * by a comma and "thanks"/"thank you" ("no, thank you"), which a pattern requiring
     * the string to be ONLY one bare word (optionally + a single trailing punctuation
     * mark) can never match — that gap is what let a clear conversation-ending decline
     * get treated as a fresh follow-up and answered with another full paragraph.
     */
    private static function isAcknowledgement(string $message): bool
    {
        return (bool) preg_match(
            '/^(?:'
                . '(?:yes|no|yep|nope|yup|nah)\s*,?\s*(?:thanks?|thank\s*you)?'
                . '|thanks?|thank\s*you'
                . '|ok|okay|sure|got\s*it|i\s*see|alright|right'
                . '|cool|nice|great|interesting|indeed|absolutely|of\s*course'
            . ')\s*[.!,]?\s*$/iu',
            trim($message)
        );
    }

    private function resolveFollowUp(string $message, array $history): string
    {
        if (empty($history)) return $message;

        $m = mb_strtolower(trim($message));

        // ── Pure acknowledgements — handle FIRST before any other logic ──────
        // "Yes.", "Okay", "Sure", "No", "Yep", etc. are never new search queries.
        // They either accept the last AI follow-up suggestion or bridge to a new topic.
        // Must be checked BEFORE messageHasNewSubject (which would wrongly flag "Yes" as
        // a capitalised proper noun).
        if (self::isAcknowledgement($message)) {
            // Try to execute the first suggestion from the last AI follow-up question
            $suggestion = $this->extractFollowUpSuggestionAsQuery($history);
            if ($suggestion) {
                return $suggestion;
            }
            return '__ACKNOWLEDGEMENT__';
        }

        // Extract the last entity from the AI's previous response's citation link — far
        // more reliable than guessing from prose, since it's the record's real name and
        // is always present in a substantive reply. Falls back to prose-guessing only
        // when there's no citation link to work from (e.g. a greeting).
        $entityRef  = $this->extractEntityRefFromLastAssistantTurn($history);
        $lastEntity = $entityRef['name'] ?? $this->currentEntityNameFallback($history);

        // ── Detect follow-up signals ──────────────────────────────
        $followUpPatterns = [
            // Explicit continuation
            '/^(tell me more|more about|go on|continue|elaborate|expand|give me more|show me more|what else|any more)/u',
            // Pronouns — it, that, this, its, their, he, she, they
            '/\b(it|its|that|this|he|she|they|their|the same|that word|that name|that proverb)\b/u',
            // Short acknowledgements that request elaboration
            '/^(yes|ok|okay|sure|and|also|so|but|why|when|where|who|whom)\b/u',
            // Clarification / example requests
            '/\b(give me|show me|can you (give|show|explain|describe|tell)|example|more detail|more info|elaborate)\b/u',
            // "How/what/is/are" — only treat as follow-up when history exists
            // (otherwise "is Iveren a female name?" as a first question would wrongly become a follow-up)
            $lastEntity !== null ? '/^(how|what|is|are|was|were|does|did|can|could|would|will)\b/u' : '/(?!x)x/', // never matches if no entity
        ];

        $isFollowUp = false;
        foreach ($followUpPatterns as $pattern) {
            if (preg_match($pattern, $m)) {
                $isFollowUp = true;
                break;
            }
        }

        // Override: if the message has a clear new subject ("what is X?", "tell me about X"),
        // it is NEVER a follow-up — even if the how|what pattern already fired above.
        if ($isFollowUp && $this->messageHasNewSubject($message)) {
            $isFollowUp = false;
        }

        // Short message (≤ 5 words) with no clear new subject → treat as follow-up.
        // "tell me about X" is explicitly excluded — it always introduces a new topic.
        if (!$isFollowUp && $lastEntity) {
            // Never treat "tell me about X" as a follow-up — it's a new topic request
            if (preg_match('/^tell me about .+/u', $m)) {
                $isFollowUp = false;
            } else {
                $tokenCount = count(preg_split('/\s+/u', trim($m), -1, PREG_SPLIT_NO_EMPTY));
                // Use original (non-lowercased) message so proper nouns like Kwagh-hir are detected
                if ($tokenCount <= 5 && !$this->messageHasNewSubject($message)) {
                    $isFollowUp = true;
                }
            }
        }

        if (!$isFollowUp || !$lastEntity) return $message;

        // ── Apply context resolution ──────────────────────────────

        // Pure continuation ("tell me more", "go on", etc.) → expand last topic
        if (preg_match('/^(tell me more|more about|go on|elaborate|expand|give me more|what else|any more)/u', $m)) {
            return "tell me about {$lastEntity}";
        }

        // Short acknowledgements ("okay", "yes", "sure", "I see", "got it", "thanks",
        // "no, thank you") → don't loop back to the same entry. Return a sentinel that
        // triggers a conversational bridge instead of another search.
        if (self::isAcknowledgement($m)) {
            return '__ACKNOWLEDGEMENT__';
        }

        // Replace pronouns with the resolved entity
        $resolved = preg_replace(
            '/\b(it|its|that|this|that word|that name|that proverb|the same)\b/iu',
            $lastEntity,
            $message
        );

        // If pronouns were replaced, return the resolved message
        if ($resolved !== $message) return $resolved;

        // No pronoun replaced but still a follow-up → append entity as context
        return $message . ' about ' . $lastEntity;
    }

    /**
     * Extract the main entity the AI talked about in its last response.
     * Looks for bold-formatted entities (**word**) which the AI always uses for names/words.
     */
    private function extractEntityFromLastAssistantTurn(array $history): ?string
    {
        // Shared by both candidate scans below: a real archive entity is never phrased
        // like "where origin tiv" or "want you help". notFoundResponse() and similar
        // templates bold-wrap the (possibly garbled) extracted subject verbatim in their
        // reply; without this guard, a bad subject gets picked up here as "the entity we
        // were just discussing" and reused as the next turn's subject too — a self-
        // perpetuating loop where one bad answer keeps reproducing itself turn after turn.
        static $garbledSubjectMarkers = ['where','what','who','why','which','when',
            'how','is','are','was','were','does','did','do','can','the','a','an',
            'want','wants','need','looking','trying'];

        // Walk backwards to find the most recent assistant message
        foreach (array_reverse($history) as $turn) {
            if (($turn['role'] ?? '') !== 'assistant') continue;

            $content = $turn['content'] ?? '';
            if (empty($content)) continue;

            // Match **Entity** markdown bold — these are the primary subjects in responses.
            // Skip section headers (emoji prefixes, generic labels, numbered items).
            if (preg_match_all('/\*\*([^*\n]{2,60})\*\*/u', $content, $hits)) {
                foreach ($hits[1] as $candidate) {
                    $clean = trim($candidate, ' .:,;[]');

                    // Strip "Label: entity" prefixes — e.g. "Summary: kwahir kwaghalom" → "kwahir kwaghalom"
                    if (preg_match('/^[A-Z][a-z]+:\s+(.+)$/u', $clean, $labelMatch)) {
                        $clean = trim($labelMatch[1]);
                    }

                    // Skip if starts with emoji (section headers like "🎉 Festivals", "📖 Dictionary")
                    if (preg_match('/^\p{So}|\p{Sm}|\p{Sk}/u', $clean)) continue;
                    // Skip common formatting labels and numbers
                    if (preg_match('/^(meaning|insight|used when|category|example|source|note|dictionary|archive|bible|names?|words?|proverbs?|foods?|plants?|festivals?|animals?|grammar|audio|video|publications?|summary|part of|related|dictionary words?|historical figures?|timeline events?|grammar rules?|bible verses?|\d+\.)$/iu', $clean)) continue;
                    // Skip pure numeric strings ("389", "30,861", "12.5") — a bolded
                    // archive-statistics count is never a valid entity name.
                    if (preg_match('/^\d[\d,]*(?:\.\d+)?$/u', $clean)) continue;
                    // Skip very short
                    if (mb_strlen($clean) < 2) continue;
                    // Skip single common English words that are likely meanings, not entities
                    static $commonMeanings = ['fancy','good','bad','big','small','new','old','free','happy','sad',
                        'true','false','yes','no','one','two','please','thanks'];
                    if (in_array(mb_strtolower($clean), $commonMeanings)) continue;
                    // Skip any candidate containing an English question/function word as a
                    // whole word — see $garbledSubjectMarkers comment above.
                    $cleanWords = preg_split('/\s+/u', mb_strtolower($clean), -1, PREG_SPLIT_NO_EMPTY);
                    if (count(array_intersect($cleanWords, $garbledSubjectMarkers)) > 0) continue;
                    // Return the candidate — Tiv entity names can be lowercase
                    return $clean;
                }
                // No candidate found in the bold-pattern scan
            }

            // Fall back: first capitalised word-group (likely the entity name).
            // Exclude common English words and transition phrases used in bridge/acknowledgement
            // responses so "Noted!", "Happy to help!", "What else..." don't get extracted.
            static $skipWords = [
                'here','from','tiv','the','this','that','there','in','of','and',
                'noted','happy','what','ask','also','these','your','next','would',
                'like','know','tell','more','some','with','about','based','found',
                'here','from','also','please','sorry','great','okay','sure','yes',
                // Greeting/self-referential words that open canned replies (greeting,
                // help, meta-chat) — never real archive entities, but capitalized at
                // the start of a sentence like any other proper noun would be.
                'msugh','nde','ka','i\'m',
            ];
            if (preg_match('/\b([A-ZÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÑ][a-záéíóúàèìòùâêîôûãñ]+(?:[-\s][A-Z][a-z]+)?)\b/u', $content, $m)) {
                $lower = mb_strtolower($m[1]);
                // Same guard as the bold-text scan above: reject a captured phrase whose
                // words include an English question/function word — "The Tiv" isn't a real
                // archive entity either, just the start of an unrelated sentence.
                $lowerWords = preg_split('/\s+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
                if (count(array_intersect($lowerWords, $garbledSubjectMarkers)) > 0) {
                    $m[1] = ''; // fall through to the length/skip-list check below, which rejects empty
                }
                // Must be ≥4 chars, not a common English word, and appear in Tiv-content context
                if (mb_strlen($m[1]) >= 4 && !in_array($lower, $skipWords)) {
                    return $m[1];
                }
            }

            break; // Only look at the most recent assistant turn
        }
        return null;
    }

    /**
     * Maps a citation URL path (as produced by url('word/123'), url('historical-figure/123'),
     * etc.) back to its source table + id. The reverse of urlForTable().
     */
    private function tableFromUrl(string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        $patterns = [
            '#/word/(\d+)#'              => 'daily_words',
            '#/name/(\d+)#'              => 'tiv_names',
            '#/proverb/(\d+)#'           => 'tiv_proverbs',
            '#/food/(\d+)#'              => 'tiv_foods',
            '#/plant/(\d+)#'             => 'tiv_plants',
            '#/festival/(\d+)#'          => 'tiv_festivals',
            '#/animal/(\d+)#'            => 'tiv_animals',
            '#/historical-figure/(\d+)#' => 'historical_figures',
            '#/timeline-event/(\d+)#'    => 'timeline_events',
        ];
        foreach ($patterns as $pattern => $table) {
            if (preg_match($pattern, $path, $m)) {
                return ['table' => $table, 'id' => (int) $m[1]];
            }
        }
        // Slug-based URLs (Nigeria Heritage records, Nigeria-only people/events): look
        // the exact URL up in the search index, which stores every record's URL.
        if (str_contains($url, '/nigeria/')) {
            $rel = str_starts_with($url, SITE_URL . '/') ? substr($url, strlen(SITE_URL)) : $url;
            $stmt = $this->db->prepare("SELECT source_table, source_id FROM archive_search_index WHERE url IN (?, ?) LIMIT 1");
            $stmt->execute([$url, $rel]);
            if ($row = $stmt->fetch()) return ['table' => $row['source_table'], 'id' => (int) $row['source_id']];
        }
        return null;
    }

    /**
     * Identify the specific archive record the last assistant turn was actually about,
     * by parsing its citation link — [Label](url) — rather than guessing from prose.
     * Every composed reply (template or LLM) always ends with a real citation link to
     * the record it's grounded in, so this is far more reliable than scanning for the
     * first capitalized word, which breaks whenever a reply doesn't happen to lead with
     * the entity's name (very common in natural, multi-turn LLM prose — e.g. "As Governor
     * of Benue State, Akume..." leads with "Governor", not the person's name).
     * Returns ['name' => label, 'table' => source table, 'id' => source id] or null.
     */
    private function extractEntityRefFromLastAssistantTurn(array $history): ?array
    {
        foreach (array_reverse($history) as $turn) {
            if (($turn['role'] ?? '') !== 'assistant') continue;
            $content = $turn['content'] ?? '';
            if ($content === '') continue;

            if (preg_match_all('/\[([^\]]{2,80})\]\(([^)\s]+)\)/u', $content, $hits, PREG_SET_ORDER)) {
                $resolved = [];
                foreach ($hits as $hit) {
                    $label = trim($hit[1]);
                    $ref   = $this->tableFromUrl($hit[2]);
                    if ($ref !== null && $label !== '') {
                        $resolved[] = ['name' => $label, 'table' => $ref['table'], 'id' => $ref['id']];
                    }
                }
                if (!empty($resolved)) {
                    // A homonym — the SAME name cited from more than one table, e.g.
                    // "Kwagh-Hir" exists both as a dictionary WORD ("something magical")
                    // and as a FESTIVAL record with real timing/activities data — used to
                    // always resolve to whichever was cited first, which is arbitrary and
                    // was frequently the less useful one (a bare word definition) even
                    // when the reply's actual prose was clearly about the festival.
                    // Deliberately narrow: only overrides "first citation wins" when a
                    // LATER citation shares the exact same name as an earlier
                    // 'daily_words' one — a genuine homonym, not just "any richer record
                    // happened to also be cited." An unrelated second citation (a
                    // different name entirely) must never steal precedence from the word
                    // the reply was actually about.
                    $first = $resolved[0];
                    if ($first['table'] === 'daily_words') {
                        foreach ($resolved as $r) {
                            if ($r['table'] !== 'daily_words' && mb_strtolower($r['name']) === mb_strtolower($first['name'])) {
                                return $r;
                            }
                        }
                    }
                    return $first;
                }
            }
            // This turn had no usable citation — e.g. an honest "I don't have that"
            // bridge reply. Keep walking backwards so the conversation's actual current
            // entity (from an earlier turn) is preserved rather than lost — this is the
            // conversation-state persistence a single "not found" answer shouldn't erase.
        }
        return null;
    }

    /**
     * Whitelist-guarded direct fetch of the exact record a follow-up refers to —
     * precise by construction (a primary-key lookup), so it can't accidentally pull
     * in an unrelated record the way a fuzzy text re-search could.
     */
    private function fetchEntityByRef(array $ref): ?array
    {
        static $allowed = [
            'daily_words', 'tiv_names', 'tiv_proverbs', 'tiv_foods', 'tiv_plants',
            'tiv_festivals', 'tiv_animals', 'historical_figures', 'timeline_events',
            ...['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods'],
        ];
        $table = $ref['table'] ?? '';
        $id    = (int) ($ref['id'] ?? 0);
        if (!in_array($table, $allowed, true) || $id <= 0) return null;

        $stmt = $this->db->prepare("SELECT *, '{$table}' AS _table FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Whether $message explicitly names $entityName — either in full ("George
     * Akume") or by its last word alone ("Akume"), the common way to refer back
     * to someone already introduced. Used to keep a message anchored to the
     * record we already know is right even when it names its subject outright
     * instead of using a pronoun.
     */
    private function messageNamesEntity(string $message, string $entityName): bool
    {
        $name = mb_strtolower(trim($entityName));
        if ($name === '') return false;
        $m = mb_strtolower($message);

        if (mb_strpos($m, $name) !== false) return true;

        $parts    = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        $lastWord = end($parts);
        if ($lastWord !== false && mb_strlen($lastWord) >= 3 && preg_match('/\b' . preg_quote($lastWord, '/') . '\b/u', $m)) {
            return true;
        }
        return false;
    }

    /**
     * Extract a subject from the last meaningful USER message in history.
     * Used as a fallback when the assistant turn gives no entity.
     */
    private function extractLastSubjectFromHistory(array $history): ?string
    {
        foreach (array_reverse($history) as $turn) {
            if (($turn['role'] ?? '') !== 'user') continue;

            $content = $turn['content'] ?? '';
            // Skip very short user turns (they're themselves follow-ups, not subjects)
            if (mb_strlen($content) < 5) continue;

            $words  = preg_split('/[\s\?\.\!\,]+/u', mb_strtolower($content), -1, PREG_SPLIT_NO_EMPTY);
            $useful = array_filter($words, fn($w) => mb_strlen($w) > 2 && !in_array($w, self::subjectStopWords()));

            if (!empty($useful)) {
                return implode(' ', array_slice(array_values($useful), 0, 3));
            }
        }
        return null;
    }

    /**
     * Returns true if the message introduces a clear new named subject of its own,
     * meaning it should NOT inherit context from the previous turn.
     */
    private function messageHasNewSubject(string $m): bool
    {
        // Work on lowercased version for pattern matching — avoids false positives
        // from sentence-starter capitalisation ("What", "Tell", "Give", etc.)
        $lower = mb_strtolower(trim($m));

        // "What is X?", "What does X mean?" — new question UNLESS X is a pronoun
        if (preg_match('/^(what is|what does|what are|what was|what were|who is|who was|tell me about|explain)\b/u', $lower)) {
            // If the next word is a pronoun → it IS a follow-up (e.g. "what is it for?")
            if (!preg_match('/^(?:what is|what does|what are|what was|what were|who is|who was|tell me about|explain)\s+(it|its|that|this|he|she|they|them)\b/u', $lower)) {
                return true; // "What is kwaghalom?" → new subject
            }
            return false; // "What is it for?" → follow-up (pronoun)
        }

        // "give/show me [type] [count]" — new listing query (not a follow-up elaboration).
        // Only when followed by a content-type category word, not generic requests like
        // "give me an example" or "show me more" which are follow-ups about the current topic.
        if (preg_match('/^(give me|show me|list|find me)\b.*(proverbs?|names?|foods?|plants?|festivals?|animals?|words?|dict\w*)\b/u', $lower)) {
            return true;
        }

        // Content-type keywords suggest a new search category — UNLESS the message also
        // anchors back to the previous entity via a pronoun ("proverbs about IT", "names
        // similar to THAT"). That's not a new topic, it's a request for a different kind
        // of evidence about the SAME one — e.g. "Are there proverbs about it?" following
        // "What is Swem?" must stay anchored to Swem, not reset to a topic-less "proverbs"
        // search.
        if (preg_match('/\b(proverbs?|names?|foods?|plants?|festivals?|animals?|words?|translate|meaning of)\b/u', $lower)
            && !preg_match('/\b(it|its|that|this|him|her|he|she|they|them)\b/u', $lower)) {
            return true;
        }

        // General cultural/topical nouns ("culture", "history", "religion", "marriage
        // customs", etc.) introduce a genuinely new topic too, not just the specific
        // archive category words above. Without this, a short reply like "yes the
        // culture" matched none of the triggers above, so messageHasNewSubject()
        // returned false and resolveFollowUp() fell through to its stale-entity
        // branch — gluing whatever narrow entity (e.g. a single dictionary word) was
        // last discussed onto the new message instead of letting it search "culture"
        // on its own merits.
        if (preg_match('/\b(cultures?|cultural|historys?|historical|religions?|religious|traditions?|customs?|customary|marriages?|languages?|migrations?|ancestry|ancestors?|clans?|lineage|heritage|identity|beliefs?|values?|society|societies|communit(?:y|ies))\b/u', $lower)
            && !preg_match('/\b(it|its|that|this|him|her|he|she|they|them)\b/u', $lower)) {
            return true;
        }

        // A quoted word/phrase introduces a new explicit subject
        if (preg_match('/["\']([^"\']+)["\']/', $m)) {
            return true;
        }

        // A capitalised word that is NOT a common question-starter, acknowledgement, or
        // sentence-beginning word indicates an introduced proper noun (e.g. "Kwagh-hir", "Iveren")
        static $sentenceStarters = [
            'what','who','where','when','why','how','is','are','was','were',
            'does','did','can','could','would','tell','show','give','find',
            'yes','no','okay','sure','thanks','great','noted','also','and',
        ];
        if (preg_match_all('/\b([A-Z][a-z]{2,})\b/', $m, $caps)) {
            foreach ($caps[1] as $word) {
                if (!in_array(mb_strtolower($word), $sentenceStarters)) {
                    return true; // found a proper noun like "Kwagh-Hir" or "Iveren"
                }
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────
    // Requested count extraction
    // ─────────────────────────────────────────────────────────────

    private function extractRequestedCount(string $message): int
    {
        $m = mb_strtolower(trim($message));

        // Written numbers
        $wordMap = [
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
            'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
            'a few' => 3, 'few' => 3, 'some' => 3, 'couple' => 2, 'several' => 4,
        ];

        foreach ($wordMap as $word => $n) {
            if (mb_strpos($m, $word) !== false) {
                return min($n, 10);
            }
        }

        // Digit (e.g. "give me 3 proverbs")
        if (preg_match('/\b(\d+)\b/', $m, $hits)) {
            $n = (int) $hits[1];
            if ($n >= 1 && $n <= 10) return $n;
        }

        return 5; // default
    }

    // ─────────────────────────────────────────────────────────────
    // Compare subject extraction
    // ─────────────────────────────────────────────────────────────

    private function extractCompareSubjects(string $message): array
    {
        // "compare X and Y", "difference between X and Y", "X vs Y", "X versus Y"
        $patterns = [
            '/compare\s+["\']?(.+?)["\']?\s+and\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/difference between\s+["\']?(.+?)["\']?\s+and\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/["\']?(.+?)["\']?\s+vs\.?\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/["\']?(.+?)["\']?\s+versus\s+["\']?(.+?)["\']?(?:\?|$)/iu',
        ];

        foreach ($patterns as $pat) {
            if (preg_match($pat, $message, $m)) {
                $a = self::stripTrailingCategoryWord(trim(preg_replace('/[?!.,]+$/', '', $m[1])));
                $b = self::stripTrailingCategoryWord(trim(preg_replace('/[?!.,]+$/', '', $m[2])));
                if ($a && $b) return [$a, $b];
            }
        }

        return [];
    }

    // ─────────────────────────────────────────────────────────────
    // Intent Detection
    // ─────────────────────────────────────────────────────────────

    private function detectIntent(string $message): string
    {
        $m = mb_strtolower(trim($message));

        // Greeting — \b after the alternation matters: without it, "hi" as a bare prefix
        // (no word boundary required) matched the start of completely unrelated words
        // like "history", "hint", "hive", silently swallowing them as greetings before
        // any real intent detection ever ran.
        if (preg_match('/^(hi|hello|good\s*(morning|evening|afternoon|day)|nde\s*er|msugh|how\s*are\s*you|i\s*ngu)\b/u', $m)) {
            return self::INTENT_GREETING;
        }

        // Meta questions about the assistant itself ("what can you do for me?",
        // "how can you help", "what do you do") — must be checked before the
        // generic "\bfor\b" / short-query fallbacks below, which would otherwise
        // treat "for me" or "you" as a search subject and return unrelated
        // archive snippets instead of explaining the assistant's capabilities.
        if (preg_match('/\bwhat can you (do|help)\b|\bwhat do you do\b|\bhow can you help\b|\bwhat can i ask you\b|\b(what|which) (things|topics|questions) can (i|you)\b/u', $m)) {
            return self::INTENT_HELP;
        }

        // Identity questions about the assistant itself ("who are you?", "what are
        // you", "tell me about/more about yourself", "are you an AI/bot/human?") —
        // must be checked before the short-query WORD fallback below (which would
        // otherwise treat "who are you?" as a 3-token dictionary lookup for "you")
        // and before resolveFollowUp's "tell me more" handling ever gets a chance to
        // run on it in a later turn.
        if (preg_match('/^who(?:\'s| is| are)\s+(you|u)\b|^what\s+are\s+you\b|\btell me (more )?about (yourself|you)\b|\bare you (an? )?(ai|bot|robot|human|real|person)\b|\bwhat\'?s your name\b/u', $m)) {
            return self::INTENT_HELP;
        }

        // Archive stats / counts — must be detected early, before "how" or content-type
        // words below get a chance to route this to a generic sample instead of a real count.
        if (preg_match('/\bhow many\b/u', $m) ||
            preg_match('/what(?:\'s| is) in the archive|what (?:resources|content) (?:do you have|are available|is available)/u', $m) ||
            preg_match('/\b(percentages?|percent|proportions?|fractions?)\b/u', $m) ||
            // "do you have any bible verses translated into Tiv?" — an existence/count
            // question about a whole CATEGORY, not a search for specific matching text.
            // Without this, it fell to GENERAL, tried to literally find a record whose
            // content equals "bible verses translated" (nothing does — that's a
            // question ABOUT the archive, not a quote FROM it), and wrongly claimed the
            // archive had no such content even though hundreds of matching rows exist.
            // Deliberately narrow: only fires when a known content-category noun
            // immediately follows "do you have (any)" — NOT for "do you have
            // information about X" or "do you have anything on X", which are genuine
            // topic searches (e.g. "the origin of Tiv") that must stay on the normal
            // search path, not get swallowed into an unrelated stats prompt.
            preg_match('/\bdo (?:you|we) have (?:any )?(?:bible verses?|proverbs?|festivals?|tiv names?|dictionary words?|foods?|plants?|animals?|historical figures?|timeline events?|grammar rules?)\b/u', $m)) {
            return self::INTENT_STATS;
        }

        // Archive record FETCH/LIST/SHOW/GET/FIND/RETRIEVE requests — routed into
        // the same stats subsystem as COUNT/PERCENTAGE so entity/property/state
        // resolution is shared; the operation itself resolves to 'fetch' inside
        // parseStatsQuery(). Must be detected here (before the plant/food/proverb/
        // etc. content-type checks below), or "list plants without images" would
        // be caught by the "\bplants?\b" pattern and mis-routed to INTENT_PLANT.
        if ($this->looksLikeStatsFetchRequest($m)) {
            return self::INTENT_STATS;
        }

        // Compare intent — must be detected early
        if (preg_match('/\b(compare|difference between|vs\.?|versus|contrast|similarities|how .+ differ)\b/u', $m)) {
            return self::INTENT_COMPARE;
        }

        // Summarize / detailed explanation intent
        if (preg_match('/\b(summarize|summary|overview|explain in detail|tell me everything|full (explanation|description|meaning)|what can you tell me about)\b/u', $m)) {
            return self::INTENT_SUMMARIZE;
        }
        // Usage / purpose questions — "what is it for?", "how is it used?", "what does it do?"
        // Matches both pronoun form ("what is it for?") and resolved form ("what is kwahir kwaghalom for?")
        if (preg_match('/\b(what is .+ for|how (is|was|are) .+ used|when (is|was) .+ used|where does .+ come from|what (context|purpose|role)|more (about|detail|info|information))\b/u', $m)) {
            return self::INTENT_SUMMARIZE;
        }
        if (preg_match('/\bfor\s*\??$|what.+\bfor\b\s*\??$/u', $m)) {
            return self::INTENT_SUMMARIZE;
        }

        // Person / entity questions — checked before yes/no and content-type patterns so
        // "Who is Akume?" is never mistaken for a dictionary/word lookup via the short-query
        // fallback below. A dedicated entity record must be tried before anything else.
        if (preg_match('/^who(?:\'s| is| was)\b/u', $m)) {
            return self::INTENT_PERSON;
        }

        // History — historical figures, timeline, "what happened during…" questions.
        // The "figures" branch also covers phrasings without the literal word
        // "historical" ("notable Tiv figures", "famous figures", "Tiv figures") —
        // without it, a short query like "notable Tiv figures" (≤3 tokens) fell
        // through everything below to the generic short-query fallback and got
        // misrouted to a plain dictionary lookup instead of the historical_figures
        // table, even though the archive has dozens of published entries there.
        if (preg_match('/\btiv history\b|\bhistory of\b.*\btiv\b|\btimeline\b|\bwhat happened (?:during|in)\b'
            . '|\b(?:historical|notable|prominent|famous|important|key|major)\s+(?:tiv\s+)?figures?\b'
            . '|\btiv\s+figures?\b/u', $m)) {
            return self::INTENT_HISTORY;
        }

        // Grammar — sentence structure, possession, parts of speech.
        if (preg_match('/\bgrammar\b|\bsentence structure\b|\bpossession in tiv\b|\bhow (?:do|does) tiv (?:pronouns?|verbs?|nouns?|adjectives?)\b|\bquestion formation\b/u', $m)) {
            return self::INTENT_GRAMMAR;
        }

        // Yes/no and factual questions — MUST be checked before content-type patterns
        // because questions like "is Iveren a female NAME?" contain content-type words
        // that would otherwise trigger INTENT_NAME before INTENT_YESNO.
        if (preg_match('/^(is|are|was|were|does|did|can|has|have)\b.+\??\s*$/u', $m)) {
            return self::INTENT_YESNO;
        }

        // Content-type detection with plurals — checked before generic word lookup.
        // "give me three proverbs", "list some proverbs", "show me proverbs and their meanings"
        // all correctly resolve to INTENT_PROVERB instead of falling to INTENT_LIST.
        if (preg_match('/\bproverbs?\b|\bsayings?\b|\badages?\b|\bwise words?\b/u', $m)) {
            return self::INTENT_PROVERB;
        }
        if (preg_match('/\b(names?|naming)\b/u', $m) && !preg_match('/\bword\b|\bdictionary\b/u', $m)) {
            return self::INTENT_NAME;
        }
        if (preg_match('/\bfoods?\b|\bdishes?\b|\bmeals?\b|\brecipes?\b|\bcuisine\b|\beat\b|\bcook\b/u', $m)) {
            return self::INTENT_FOOD;
        }
        if (preg_match('/\bplants?\b|\bherbs?\b|\btrees?\b|\bmedicinal\b|\bleaves?\b/u', $m)) {
            return self::INTENT_PLANT;
        }
        if (preg_match('/\bfestivals?\b|\bcelebrations?\b|\bceremonies?\b|\brituals?\b|\bharvest\b/u', $m)) {
            return self::INTENT_FESTIVAL;
        }
        if (preg_match('/\banimals?\b|\bbirds?\b|\bcreatures?\b|\bwild\s*life\b/u', $m)) {
            return self::INTENT_ANIMAL;
        }

        // Word/translation lookup. "tiv"/"english" must appear in an actual
        // translation-shaped phrase ("mean IN TIV", "TIV WORD for X"), not just
        // anywhere in the message — the old bare co-occurrence check forced ANY
        // "what ... tiv ..." message into a dictionary-only lookup, e.g. "What
        // about Tiv Day?" got misrouted here (correctly finding zero word matches
        // for "Tiv Day" as a whole, since it's a festival, then falling back to a
        // useless "day" lookup) instead of reaching GENERAL, which would have
        // surfaced the actual Tiv Day festival record.
        if (preg_match('/\b(what|how|translate|meaning|mean|definition|define|word for|say)\b/u', $m) &&
            preg_match('/\b(?:in|into|to)\s+(?:the\s+)?(tiv|english)\b|\b(tiv|english)\s+(?:word|translation|meaning|equivalent)\b/u', $m)) {
            return self::INTENT_WORD;
        }
        if (preg_match('/\bword\b.*\bmean|mean.*\bword\b|\bwhat is\b.*\bin tiv\b|\bhow do (you|i) say\b/u', $m)) {
            return self::INTENT_WORD;
        }

        // Generic list requests for any remaining content types
        if (preg_match('/\b(list|give me|show me|examples of)\b.*\b(words?|dictionary)\b/u', $m)) {
            return self::INTENT_WORD;
        }

        // Translation help
        if (preg_match('/\b(translate|translation|how do i translate|can you translate)\b/u', $m)) {
            return self::INTENT_TRANSLATION;
        }

        // Short query (1–3 words, no question structure) — try as word/concept lookup.
        // Covers inputs like "God", "love", "Tiv marriage", "kwagh hir".
        $tokens = preg_split('/\s+/u', trim($m), -1, PREG_SPLIT_NO_EMPTY);
        if (count($tokens) <= 3) {
            return self::INTENT_WORD;
        }

        return self::INTENT_GENERAL;
    }

    // ─────────────────────────────────────────────────────────────
    // Subject Extraction
    // ─────────────────────────────────────────────────────────────

    private function extractSubject(string $message, string $intent): string
    {
        $m = trim($message);

        // Yes/no questions: extract just the primary noun (not the predicate)
        // "is Aondo a male name?" → "Aondo"
        // "does gbande mean drum?" → "gbande"
        // "was Kwagh-hir celebrated before?" → "Kwagh-hir"
        if ($intent === self::INTENT_YESNO) {
            // "is there anything/something about X" / "is there X in the archive" is an
            // existential construction, not "is NAME a ..." — the word right after
            // "is/are" is "there" itself, a filler, never the real subject. Skip past it
            // (and a following "anything/something/nothing about") so the pattern below
            // captures the actual topic instead of literally returning "there".
            if (preg_match('/^(?:is|are|was|were)\s+there\s+(?:anything|something|nothing)?\s*(?:about\s+)?(.+?)\s*(?:in the archive)?\??$/iu', $m, $h)
                && mb_strlen(trim($h[1])) > 1) {
                return trim($h[1]);
            }
            // "can/could/would you tell me/explain/show me (about) X" — one of the most
            // common natural ways to phrase a request to a chatbot — is a request-for-
            // help construction, not "can NAME verb" (e.g. "can Aondo speak Tiv?"). The
            // generic pattern below would otherwise grab "you" as the subject (the word
            // right after "can"), same class of bug as the "is there" case above.
            if (preg_match('/^(?:can|could|would|will)\s+you\s+(?:please\s+)?(?:tell me|explain|show me|help me(?:\s+with)?|find|describe)\s*(?:about\s+)?(.+?)\??$/iu', $m, $h)
                && mb_strlen(trim($h[1])) > 1) {
                return trim($h[1]);
            }
            // Pattern: (is|are|was|were) [article] NAME [rest]
            if (preg_match('/^(?:is|are|was|were)\s+(?:a\s+|an\s+|the\s+)?([A-Za-z\-\']+)/iu', $m, $h)) {
                return trim($h[1]);
            }
            // Pattern: (does|did|can|has|have) NAME [verb] ...
            if (preg_match('/^(?:does|did|can|has|have)\s+([A-Za-z\-\']+)/iu', $m, $h)) {
                return trim($h[1]);
            }
        }

        // Content-type + "about X" patterns — must be checked FIRST to avoid
        // "what are some proverbs about wisdom" → "are some proverbs about wisdom"
        $typeAboutPatterns = [
            '/\bproverbs?\b.*?\babout\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/\bsayings?\b.*?\babout\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/\bnames?\b.*?\bmeaning\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/\bfoods?\b.*?\babout\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/\bfestivals?\b.*?\babout\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/\banimals?\b.*?\babout\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
            '/\bplants?\b.*?\babout\b\s+["\']?(.+?)["\']?(?:\?|$)/iu',
        ];
        foreach ($typeAboutPatterns as $pat) {
            if (preg_match($pat, $m, $hits)) {
                $candidate = trim(preg_replace('/[\?\!\.\,]+$/', '', $hits[1]));
                if (mb_strlen($candidate) > 1 && mb_strlen($candidate) < 60) {
                    return $candidate;
                }
            }
        }

        // Patterns like: "what does X mean", "what is X in Tiv", "tell me about X"
        $patterns = [
            '/what (?:does|is) ["\']?(.+?)["\']? mean/iu',
            '/(?:tell me about|explain|describe) (?:the |a |an )?["\']?(.+?)["\']?(?:\?|$)/iu',
            '/meaning of ["\']?(.+?)["\']?(?:\?|$)/iu',
            '/(?:translate|translation of|how (?:do you|do i) say) ["\']?(.+?)["\']?(?:\?|$|(?:\s+in\s))/iu',
            '/["\']?(.+?)["\']? in (?:tiv|english)/iu',
            '/(?:tiv|english) (?:word|name|proverb|festival|food|plant|animal) (?:for|of|about) ["\']?(.+?)["\']?(?:\?|$)/iu',
            '/(?:proverb|saying) (?:about|on) ["\']?(.+?)["\']?(?:\?|$)/iu',
            '/(?:what is|who is|who was|who\'s) ["\']?(.+?)["\']?(?:\?|$)/iu',
        ];

        foreach ($patterns as $pat) {
            if (preg_match($pat, $m, $hits)) {
                $candidate = trim($hits[1]);
                // Strip trailing punctuation and common filler
                $candidate = preg_replace('/[\?\!\.\,]+$/', '', $candidate);
                // Strip trailing translation-direction qualifiers ("...in Tiv", "...in
                // English") — but NOT a bare "tiv"/"english" anywhere in the phrase, which
                // used to also strip "Tiv" out of a topic like "Tiv migration" or "Tiv
                // history", leaving a mangled "the  migration" that can no longer match
                // the real record's title (which does contain "Tiv") at all.
                $candidate = preg_replace('/\b(please|in tiv|in english)\b/iu', '', $candidate);
                $candidate = preg_replace('/\s+/u', ' ', trim($candidate));
                // "tell me about the Kwagh-hir FESTIVAL" — without this, the trailing
                // category word survives into the subject, and the record (just named
                // "Kwagh-Hir") no longer matches at all, dropping through to the far
                // noisier embedding fallback for a query the direct search should have
                // answered outright.
                $candidate = self::stripTrailingCategoryWord($candidate);
                // Reject a candidate that ITSELF still starts with a question/auxiliary
                // word ("who are some important historical figures", captured whole by
                // the "X in Tiv/English" pattern below matching on "...in Tiv history?")
                // — a real topic phrase never starts this way, so this is a reliable
                // signal the pattern grabbed a whole clause instead of isolating one.
                // Falls through to the stopword-stripping fallback below instead, which
                // handles exactly this shape correctly.
                $startsLikeAClause = preg_match(
                    '/^(who|what|where|when|why|how|which|is|are|was|were|do|does|did|can|could|would|will)\b/iu',
                    $candidate
                );
                if (!$startsLikeAClause && mb_strlen($candidate) > 1 && mb_strlen($candidate) < 100) {
                    return $candidate;
                }
            }
        }

        // Fallback: strip stop words and take the most content-bearing word.
        // Also strip list-request words and quantifiers so "give me three proverbs"
        // → subject="" (triggers generic sample) not "give three proverbs".
        $words = preg_split('/[\s\?\.\!\,]+/u', mb_strtolower($m), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($w) => mb_strlen($w) > 2 && !in_array($w, self::subjectStopWords()));

        return implode(' ', array_values(array_slice($words, 0, 3)));
    }

    /**
     * Canonical stop-word list for stripping a user message down to its content-
     * bearing subject. Shared by extractSubject() and extractLastSubjectFromHistory()
     * — those two methods independently maintaining their own copies is exactly what
     * caused a real production bug: extractLastSubjectFromHistory()'s copy was missing
     * 'where' and 'tiv' entirely, so "WHERE IS THE ORIGIN OF TIV?" as a PREVIOUS user
     * turn re-derived the subject "where origin tiv" on a later turn even after this
     * list (used by extractSubject() for the CURRENT turn) had already been fixed to
     * handle that exact phrasing correctly. One shared source of truth prevents that
     * class of drift from recurring.
     */
    /**
     * Strip a trailing generic category word from an extracted subject — "Tiv Day
     * FESTIVAL" or "Kwagh-hir FESTIVAL" don't match any record's own name (the record
     * is just named "Tiv Day" / "Kwagh-Hir"); same for a trailing "proverb(s)",
     * "word(s)", etc. left over from how people naturally say "tell me about the
     * Kwagh-hir festival" or "compare X festival and Y festival". Shared by
     * extractCompareSubjects() and the main extractSubject() patterns loop — those
     * used to each carry an independent copy (or, for the patterns loop, none at
     * all), which is exactly the kind of drift that already caused more than one real
     * bug earlier in this file's history.
     */
    /**
     * True if $text contains a non-trivial amount of CJK/Korean/Arabic/Cyrillic
     * script — a real, observed local-LLM failure mode where the model drifts into a
     * completely different language mid-reply with no prompting toward it at all
     * (seen live: a "why do the Tiv celebrate Kwagh-hir?" answer that started in
     * English and switched to Chinese partway through). This archive only ever has
     * English and Tiv (Latin script with diacritics) content, so any such text can
     * never be a valid, evidence-grounded answer. A handful of stray characters isn't
     * flagged (threshold > 3) since a single misrendered glyph shouldn't discard an
     * otherwise-good reply — this is for catching a genuine script-level derailment.
     */
    private static function hasUnexpectedNonLatinScript(string $text): bool
    {
        $count = preg_match_all(
            '/[\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}\x{0600}-\x{06FF}\x{0400}-\x{04FF}]/u',
            $text
        );
        return $count !== false && $count > 3;
    }

    private static function stripTrailingCategoryWord(string $s): string
    {
        return trim(preg_replace(
            '/\s+(festivals?|proverbs?|sayings?|names?|words?|foods?|dishes?|plants?|animals?)$/iu', '', $s
        ));
    }

    private static function subjectStopWords(): array
    {
        return ['what','is','are','the','a','an','of','in','on','at','how','does','do','can',
                'tell','me','about','tiv','word','mean','means','meaning','please','who','when',
                'where','why','which','with','for','to','from','and','or','its','their',
                'has','have','had','was','were','be','been','translate','english','say',
                // list-request fillers and quantifiers
                'give','show','list','some','few','couple','several','any',
                'one','two','three','four','five','six','seven','eight','nine','ten',
                // content-type words (category, not topic)
                'proverb','proverbs','saying','sayings',
                'name','names','food','foods','plant','plants',
                'festival','festivals','animal','animals','word','words',
                // possessive and trailing filler words
                'their','them','its','your','my',
                'meaning','meanings','definition','definitions','example','examples',
                'entry','entries','list','listing',
                // gender qualifiers (used in "is X a male/female name?" and "names for
                // girls/boys" — plural forms were missing, so "girls" alone used to
                // survive as the literal search subject, matching no name at all since
                // no name's own text contains the word "girls")
                'male','female','unisex','boy','girl','boys','girls',
                // yes/no question starters, including modal verbs ("could you show me
                // about festivals?" — without these, "could" itself survives as the
                // extracted subject once none of the more specific patterns match)
                'is','are','was','were','did','has','have',
                'can','could','would','will','should','may','might','shall',
                // request/intent filler verbs — "I am LOOKING FOR the origin of
                // tiv", "I WANT TO KNOW about X" — these express that the user is
                // searching, they are never themselves the topic. Left in, they
                // pollute the extracted subject (e.g. "looking origin" instead of
                // "origin"), which then fails the headword-match guard in
                // isGenuineHeadwordMatch() even when the real record is found.
                'want','wants','wanted','looking','look','need','needs','needed',
                'trying','try','tries','wondering','wonder','wonders',
                'interested','curious','searching','search','seeking','seek','seeks',
                'find','finding','finds','asking','ask','asks','information','info',
                // acknowledgement/filler words that can appear alongside a real
                // topic in casual phrasing ("ok so where did tiv come from") —
                // never themselves the subject.
                'ok','okay','yes','no','thanks','great','you',
                // existential/indefinite placeholders — "do you have ANYTHING on X",
                // "is there SOMETHING about X" — never themselves the subject.
                'anything','something','nothing',
                // Pronouns — "what do THEY mean", "tell me about HER" — most of these
                // were missing (only 'them'/'their' were covered), so a pronoun-bearing
                // clause anywhere in an otherwise-fine question could survive as the
                // literal extracted subject ("they"), matching no real record and — for
                // the NAME intent specifically — bypassing the gender filter entirely by
                // falling through to the ungoverned embedding fallback instead of the
                // gender-aware sample mode. 'it'/'he' are 2 chars and already excluded
                // by the length filter below; the rest are 3+ chars and need to be listed.
                'they','this','that','she','his','her','him','himself','herself','themselves'];
    }

    // ─────────────────────────────────────────────────────────────
    // Archive Search
    // ─────────────────────────────────────────────────────────────

    /**
     * When the fast, precise article search (searchContentItems(), keyed on
     * title/subcategory/excerpt LIKE + a 6-char stemming fallback for words 5+
     * characters) finds nothing, a genuinely relevant article may still exist
     * but share no matching vocabulary with the stripped $subject — e.g. subject
     * "come" (from "where did the Tiv come from?") is only 4 characters, too
     * short for the stemming fallback, and shares no substring with "The Tiv
     * Migration into the Benue Valley" at all, even though the full $message is
     * clearly asking exactly what that article answers. This is the same
     * zero-vocabulary-overlap problem EmbeddingSearch already exists to solve
     * (see its own header comment) for the LAST-RESORT tier — this reuses it one
     * tier earlier, but ONLY as an article supplement, and only when the fast
     * path came up completely empty, so it never overrides a genuine direct
     * article match.
     *
     * Deliberately embeds $message (full sentence, needed for the embedding
     * model to work at all) not $subject (lexically-stripped keywords) — same
     * reasoning already established for the existing last-resort tier below.
     */
    private function supplementArticlesViaEmbedding(string $message, array $articles): array
    {
        if (!empty($articles) || trim($message) === '') {
            return $articles;
        }
        try {
            return array_slice($this->embeddings->search($message, 2), 0, 2);
        } catch (\Throwable $e) {
            error_log('EmbeddingSearch (article supplement) error: ' . $e->getMessage());
            return [];
        }
    }

    private function searchArchive(string $message, string $subject, string $intent, int $count = 5): array
    {
        $results = ['primary' => [], 'extras' => []];

        // For specific content-type intents, an empty subject means "give me a sample"
        // — don't bail early; let the handler return a generic set.
        $needsSubject = in_array($intent, [self::INTENT_GENERAL, self::INTENT_WORD, self::INTENT_TRANSLATION]);
        if ($subject === '' && $needsSubject) {
            return $results;
        }

        // Intent-targeted table search (fast and precise)
        switch ($intent) {
            case self::INTENT_WORD:
                // For word/concept lookups, search all primary content tables
                // so "God" finds both the dictionary entry (Ter) and related names.
                // content_items (long-form articles) goes first for the same reason as
                // the GENERAL/default case below — "what is the Tiv migration?" is a WORD-
                // intent question (contains "what"+"tiv") just as easily as a GENERAL one,
                // and deserves the dedicated article over an incidental dictionary hit.
                $articles  = $this->supplementArticlesViaEmbedding($message, $this->searchContentItems($subject));
                $words     = $this->searchWords($subject);
                $names     = $this->searchNames($subject);
                // Curated translation phrases AFTER the dictionary/names, not before:
                // for an ordinary word lookup ("what is msugh") the exact dictionary
                // word or name match (e.g. the name "Msughter") is the right answer and
                // must keep priority. Phrases only need to lead for a query that words/
                // names genuinely can't answer, like "hello" (not itself a Tiv word) —
                // and merge order only matters when something EARLIER already matched,
                // since buildEvidence() keeps just the first few items either way.
                $phrases   = $this->searchPhrases($subject);
                $proverbs  = $this->searchProverbs($subject);
                $festivals = $this->searchFestivals($subject);
                $foods     = $this->searchFoods($subject);
                $plants    = $this->searchPlants($subject);
                $animals   = $this->searchAnimals($subject);
                $verses    = $this->searchBibleVerses($subject);
                $alphabet  = $this->searchAlphabetEntries($subject);
                $videos    = $this->searchLearningVideos($subject);
                $results['primary'] = array_merge(
                    $articles, $words, $names, $phrases, $proverbs, $festivals, $foods, $plants, $animals,
                    $verses, $alphabet, $videos
                );
                break;
            case self::INTENT_NAME:
                // "names for girls/boys" — subject extraction strips "girls"/"boys" as
                // gender-qualifier stopwords (correctly — they're never themselves a
                // name to search for), but that used to lose the gender request
                // entirely, silently falling back to an unfiltered random mix instead
                // of actually honoring "for girls" specifically. Detect it here, from
                // the original message before stopword-stripping ran.
                $nameGender = null;
                if (preg_match('/\b(girls?|female)\b/iu', $message)) {
                    $nameGender = 'female';
                } elseif (preg_match('/\b(boys?|male)\b/iu', $message)) {
                    $nameGender = 'male';
                } elseif (preg_match('/\bunisex\b/iu', $message)) {
                    $nameGender = 'unisex';
                }
                $results['primary'] = $this->searchNames($subject, $nameGender);
                break;
            case self::INTENT_PROVERB:
                $results['primary'] = $this->searchProverbs($subject, $count);
                break;
            case self::INTENT_FOOD:
                $results['primary'] = $this->searchFoods($subject);
                break;
            case self::INTENT_PLANT:
                $results['primary'] = $this->searchPlants($subject);
                break;
            case self::INTENT_FESTIVAL:
                $results['primary'] = $this->searchFestivals($subject);
                break;
            case self::INTENT_ANIMAL:
                $results['primary'] = $this->searchAnimals($subject);
                break;
            case self::INTENT_YESNO:
                // Yes/no questions: try dedicated long-form articles first — a topical
                // request like "can you tell me about Tiv marriage customs?" deserves the
                // article written for exactly that, not a weak incidental word match that
                // happens to contain "Tiv" (which would otherwise short-circuit every tier
                // below it, since each tier only runs when the previous one found nothing
                // at all — a real match wins even when it's a poor one). Then words/names,
                // then everything else. Includes proverbs because "are there proverbs
                // about it/X?" — a very common phrasing — is grammatically a yes/no
                // question and gets classified here before the content-type check further
                // down ever runs (see detectIntent()'s ordering).
                $results['primary'] = $this->searchContentItems($subject);
                if (empty($results['primary'])) {
                    $words = $this->searchWords($subject);
                    $names = $this->searchNames($subject);
                    $results['primary'] = array_merge($words, $names);
                }
                if (empty($results['primary'])) {
                    $results['primary'] = $this->searchHistoricalFigures($subject);
                }
                if (empty($results['primary'])) {
                    $results['primary'] = $this->searchProverbs($subject);
                }
                if (empty($results['primary'])) {
                    $results['primary'] = array_merge(
                        $this->searchFestivals($subject),
                        $this->searchFoods($subject),
                        $this->searchAnimals($subject)
                    );
                }
                break;
            case self::INTENT_PERSON:
                // Dedicated entity records only — never fall back to a dictionary example
                // sentence for a "who is X" question (see incidental-match guard below).
                $results['primary'] = $this->searchHistoricalFigures($subject);
                break;
            case self::INTENT_HISTORY:
                // A generic phrase like "tiv history" or "historical figures" isn't a real
                // search term for any record's own name — treat it as "give me a sample".
                // A plain in_array() exact-match here used to miss the very common shape
                // "who are some IMPORTANT historical figures..." (with a descriptive
                // adjective in front) entirely — one un-stripped adjective and the whole
                // phrase silently fell through to being treated as a specific, unmatchable
                // search term instead of triggering the sample fallback.
                $histSubjectNorm = mb_strtolower(trim($subject));
                // "tiv" is stripped as a stopword by the generic extractSubject()
                // fallback before this ever runs, so "notable Tiv figures" arrives
                // here as "notable figures" — both the "historical" and "tiv" groups
                // must be optional, not just the leading adjective, or a phrasing
                // without the literal word "historical" wrongly gets treated as a
                // real (unmatchable) search term instead of triggering the sample.
                $isGenericHistoryPhrase = $histSubjectNorm === ''
                    || preg_match(
                        '/^(the\s+)?tiv\s+history$|^history(\s+of\s+tiv)?$|^timeline$'
                        . '|^(the\s+)?(important|notable|prominent|key|major|famous|some|a\s+few)?'
                        . '\s*(historical\s+)?(tiv\s+)?figures?$/u',
                        $histSubjectNorm
                    );
                $histSubject = $isGenericHistoryPhrase ? '' : $subject;
                // content_items articles are literally section='history' — "history of Tiv"
                // style questions are exactly what searchContentItems() was built to answer.
                $results['primary'] = array_merge(
                    $this->searchContentItems($histSubject, 3),
                    $this->searchHistoricalFigures($histSubject, 3),
                    $this->searchTimelineEvents($histSubject, 3)
                );
                break;
            case self::INTENT_GRAMMAR:
                static $genericGrammarTerms = ['', 'grammar', 'tiv grammar', 'sentence structure',
                    'grammar rules', 'tiv grammar rules'];
                $gramSubject = in_array(mb_strtolower(trim($subject)), $genericGrammarTerms, true) ? '' : $subject;
                $results['primary'] = $this->searchGrammarRules($gramSubject);
                break;
            case self::INTENT_STATS:
                // Handled entirely in composeStatsResponse() via live COUNT() queries.
                break;
            default:
                // General/unclassified queries — including follow-ups like "what did he
                // write?" where intent stays GENERAL but the subject is a specific carried-
                // over entity name. Try dedicated-record matches first (same battery as
                // INTENT_WORD, plus historical figures): these only match on a row's own
                // headword/name, so they can't be confused by an unrelated dictionary entry
                // that merely mentions the subject inside an example sentence. Only fall back
                // to the blunt full-index search — which DOES search that noisier text — when
                // nothing precise was found.
                // content_items (long-form articles) go first: a dedicated article
                // titled for the subject ("The Origins of the Tiv People") is a far
                // more relevant answer than a dictionary word or plant entry that
                // merely happens to contain the same substring, and evidence-building
                // downstream only keeps the first 4 primary results.
                $articles  = $this->supplementArticlesViaEmbedding($message, $this->searchContentItems($subject));
                $words     = $this->searchWords($subject);
                $names     = $this->searchNames($subject);
                $figures   = $this->searchHistoricalFigures($subject);
                $proverbs  = $this->searchProverbs($subject);
                $festivals = $this->searchFestivals($subject);
                $foods     = $this->searchFoods($subject);
                $plants    = $this->searchPlants($subject);
                $animals   = $this->searchAnimals($subject);
                // Content types that had no dedicated search path at all until now —
                // findable only via the last-resort embedding fallback, never the fast/
                // precise path every other content type gets. Bible verses especially:
                // 30,861 rows, the single largest content type in the whole archive.
                $verses    = $this->searchBibleVerses($subject);
                $phrases   = $this->searchPhrases($subject);
                $alphabet  = $this->searchAlphabetEntries($subject);
                $videos    = $this->searchLearningVideos($subject);
                $results['primary'] = array_merge(
                    $articles, $words, $names, $figures, $proverbs, $festivals, $foods, $plants, $animals,
                    $verses, $phrases, $alphabet, $videos
                );
                // Deliberately no direct searchIndex() fallback here when this comes up
                // empty — leave it empty and let the semantic-fallback block below handle
                // it, since that's the one path with the incidental-match safeguard. An
                // inline fallback here would bypass that guard entirely (this array would
                // no longer be "empty" by the time execution reaches that check).
                break;
        }

        // If targeted search found nothing, fall back to semantic index search
        // (expands query bilingually before hitting FULLTEXT)
        if (empty($results['primary']) && $subject !== '' && $intent !== self::INTENT_STATS) {
            try {
                $fallback = $this->semantic->search($subject, [], 6);
            } catch (\Throwable $e) {
                error_log('SemanticSearch error: ' . $e->getMessage());
                $fallback = [];
            }

            // Incidental-match guard: a fallback hit only counts as a real answer if the
            // subject actually appears in the record's OWN defining field (its headword/
            // title), not merely somewhere inside an example sentence or description that
            // was packed into the search index. Otherwise we'd confidently answer a "Who is
            // Akume?" style question with an unrelated dictionary entry just because "Akume"
            // shows up in one of its example sentences.
            $genuine    = [];
            $incidental = [];
            foreach ($fallback as $row) {
                if ($this->isGenuineHeadwordMatch($row, $subject)) {
                    $genuine[] = $row;
                } else {
                    $incidental[] = $row;
                }
            }

            if (!empty($genuine)) {
                $results['primary'] = $genuine;
            } elseif (!empty($incidental)) {
                // Nothing genuinely matches — keep primary empty so notFoundResponse()
                // fires, but pass the incidental hits along so it can honestly mention them.
                $results['incidental'] = array_slice($incidental, 0, 2);
            }
        }

        // Last resort: meaning-based search. Everything above requires the query and
        // the content to share actual words (even the "semantic" fallback above is
        // still lexical — bilingual/concept expansion plus FULLTEXT, still word-based).
        // This is for paraphrases with NO shared vocabulary at all — "which place is
        // Tiv located?" vs. an article titled "The Origins of the Tiv People" — where
        // isGenuineHeadwordMatch()'s substring check would never pass no matter how
        // relevant the record actually is. The safety net here is EmbeddingSearch's own
        // similarity floor (EMBEDDING_MIN_SIMILARITY) rather than a headword match,
        // since by definition a genuine paraphrase may not contain the subject's exact
        // words — that's the whole reason this tier exists.
        if (empty($results['primary']) && $subject !== '' && $intent !== self::INTENT_STATS) {
            try {
                // Deliberately $message here, not $subject: an embedding model needs full
                // sentence context to work at all — $subject is the lexically-stripped
                // keyword form (built for substring/FULLTEXT matching), which for this
                // query is literally "place located" with "Tiv" and all sentence
                // structure removed. Embedding THAT instead of the real question measurably
                // produced worse matches (verified: ranked unrelated short dictionary
                // entries above the correct, highly-relevant article).
                $embedded = $this->embeddings->search($message, 4);
            } catch (\Throwable $e) {
                error_log('EmbeddingSearch error: ' . $e->getMessage());
                $embedded = [];
            }
            if (!empty($embedded)) {
                $results['primary'] = $embedded;
            }
        }

        return $results;
    }

    /**
     * True if $subject actually appears in the record's own defining field
     * (headword/name/title), as opposed to only inside a secondary field like an
     * example sentence or description. Used to stop the semantic-search fallback
     * from confidently answering a named-entity question with an unrelated record.
     */
    private function isGenuineHeadwordMatch(array $row, string $subject): bool
    {
        $subject = mb_strtolower(trim($subject));
        if ($subject === '') return true; // nothing to compare against — don't block generic samples

        $headwordFields = ['tiv_word', 'tiv_name', 'tiv_text', 'title', 'english_name', 'english_meaning', 'name'];
        foreach ($headwordFields as $field) {
            if (!empty($row[$field]) && mb_strpos(mb_strtolower($row[$field]), $subject) !== false) {
                return true;
            }
        }

        // Index rows (from archive_search_index) don't carry the source table's own
        // columns — fetch the actual record so we can check its real headword.
        if (isset($row['source_table'], $row['source_id'])) {
            $fetched = $this->fetchSourceRecords([$row]);
            if (!empty($fetched)) {
                foreach ($headwordFields as $field) {
                    if (!empty($fetched[0][$field]) && mb_strpos(mb_strtolower($fetched[0][$field]), $subject) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────
    // Targeted table searches
    // ─────────────────────────────────────────────────────────────

    private function searchWords(string $q): array
    {
        $like  = '%' . $q . '%';
        $lower = mb_strtolower($q);

        // Fetch ALL POS variants of matching words so composeWordResponse can group them
        $stmt = $this->db->prepare(
            "SELECT *, 'daily_words' AS _table FROM daily_words
             WHERE is_active = 1
               AND tiv_word IS NOT NULL AND tiv_word != ''
               AND english_meaning IS NOT NULL AND english_meaning != ''
               AND (LOWER(tiv_word) = ? OR LOWER(english_meaning) = ?
                    OR LOWER(tiv_word) LIKE ? OR LOWER(english_meaning) LIKE ?
                    OR LOWER(alternate_meaning) LIKE ?)
             ORDER BY
               CASE
                 WHEN LOWER(tiv_word) = ? THEN 0
                 WHEN LOWER(english_meaning) = ? THEN 1
                 ELSE 2
               END,
               FIELD(part_of_speech,'noun','verb','adjective','adverb','pronoun','preposition','conjunction','interjection','phrase')
             LIMIT 12"
        );
        $stmt->execute([$lower, $lower, $like, $like, $like, $lower, $lower]);
        $rows = $stmt->fetchAll();

        if ($q !== '') {
            $filtered = $this->filterByRelevance($rows, $q, 'tiv_word');
            return !empty($filtered) ? $filtered : $rows;
        }
        return $rows;
    }

    private function searchNames(string $q, ?string $gender = null): array
    {
        if (trim($q) === '') {
            // "give me some names" with no gender filter, or "names for girls" where
            // subject stripped down to empty (gender qualifiers are stopwords) — the
            // latter must still filter by gender rather than returning an unfiltered
            // random mix, or a request specifically "for girls" would just as often
            // come back with boys' names.
            if ($gender !== null) {
                $stmt = $this->db->prepare(
                    "SELECT *, 'tiv_names' AS _table FROM tiv_names WHERE gender = ? ORDER BY RAND() LIMIT 5"
                );
                $stmt->execute([$gender]);
                return $stmt->fetchAll();
            }
            $stmt = $this->db->prepare("SELECT *, 'tiv_names' AS _table FROM tiv_names ORDER BY RAND() LIMIT 5");
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $lower = mb_strtolower($q);
        $genderClause = $gender !== null ? 'AND gender = ?' : '';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_names' AS _table FROM tiv_names
             WHERE (LOWER(tiv_name) = ? OR LOWER(tiv_name) LIKE ?
                OR LOWER(english_meaning) LIKE ? OR LOWER(description) LIKE ?)
               AND CHAR_LENGTH(tiv_name) <= 60
               {$genderClause}
             ORDER BY CASE WHEN LOWER(tiv_name) = ? THEN 0 ELSE 1 END,
                      CHAR_LENGTH(tiv_name) ASC
             LIMIT 5"
        );
        $params = [$lower, $like, $like, $like];
        if ($gender !== null) $params[] = $gender;
        $params[] = $lower;
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'tiv_name');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchProverbs(string $q, int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));

        // Generic request ("give me proverbs", "some proverbs") → return a varied sample
        $genericTerms = ['proverb', 'proverbs', 'saying', 'sayings', 'adage', 'wisdom', ''];
        if (in_array(mb_strtolower(trim($q)), $genericTerms, true)) {
            $total  = (int) $this->db->query("SELECT COUNT(*) FROM tiv_proverbs")->fetchColumn();
            $offset = $total > $limit ? (intval(date('i')) * 2) % max(1, $total - $limit) : 0;
            $stmt   = $this->db->query(
                "SELECT *, 'tiv_proverbs' AS _table FROM tiv_proverbs
                 ORDER BY id ASC LIMIT {$limit} OFFSET {$offset}"
            );
            return $stmt->fetchAll();
        }

        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_proverbs' AS _table FROM tiv_proverbs
             WHERE tiv_text LIKE ? OR english_translation LIKE ?
                OR deeper_meaning LIKE ? OR usage_context LIKE ?
             ORDER BY CASE WHEN LOWER(tiv_text) LIKE ? THEN 0 ELSE 1 END LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    private function searchFoods(string $q): array
    {
        if (trim($q) === '') {
            $stmt = $this->db->query("SELECT *, 'tiv_foods' AS _table FROM tiv_foods ORDER BY RAND() LIMIT 5");
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_foods' AS _table FROM tiv_foods
             WHERE tiv_name LIKE ? OR english_name LIKE ? OR description LIKE ?
                OR cultural_significance LIKE ?
             LIMIT 5"
        );
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'tiv_name');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchPlants(string $q): array
    {
        if (trim($q) === '') {
            $stmt = $this->db->query("SELECT *, 'tiv_plants' AS _table FROM tiv_plants ORDER BY RAND() LIMIT 5");
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_plants' AS _table FROM tiv_plants
             WHERE tiv_name LIKE ? OR english_name LIKE ? OR description LIKE ?
                OR medicinal_uses LIKE ? OR scientific_name LIKE ?
             LIMIT 5"
        );
        $stmt->execute([$like, $like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'tiv_name');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchFestivals(string $q): array
    {
        if (trim($q) === '') {
            $stmt = $this->db->query("SELECT *, 'tiv_festivals' AS _table FROM tiv_festivals ORDER BY RAND() LIMIT 5");
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_festivals' AS _table FROM tiv_festivals
             WHERE tiv_name LIKE ? OR english_name LIKE ? OR description LIKE ?
                OR significance LIKE ? OR activities LIKE ?
             LIMIT 5"
        );
        $stmt->execute([$like, $like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'tiv_name');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchAnimals(string $q): array
    {
        if (trim($q) === '') {
            $stmt = $this->db->query("SELECT *, 'tiv_animals' AS _table FROM tiv_animals ORDER BY RAND() LIMIT 5");
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_animals' AS _table FROM tiv_animals
             WHERE tiv_name LIKE ? OR name LIKE ? OR description LIKE ?
                OR cultural_use LIKE ?
             LIMIT 5"
        );
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'tiv_name');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchHistoricalFigures(string $q, int $limit = 3): array
    {
        $limit = max(1, min($limit, 5));
        if (trim($q) === '') {
            $stmt = $this->db->prepare(
                "SELECT *, 'historical_figures' AS _table FROM historical_figures
                 WHERE status = 'published' ORDER BY RAND() LIMIT {$limit}"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like  = '%' . $q . '%';
        $lower = mb_strtolower($q);
        $stmt = $this->db->prepare(
            "SELECT *, 'historical_figures' AS _table FROM historical_figures
             WHERE status = 'published'
               AND (LOWER(english_name) = ? OR LOWER(english_name) LIKE ?
                    OR LOWER(tiv_name) LIKE ? OR LOWER(title) LIKE ?)
             ORDER BY CASE WHEN LOWER(english_name) = ? THEN 0 ELSE 1 END
             LIMIT {$limit}"
        );
        $stmt->execute([$lower, $like, $like, $like, $lower]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'english_name');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchTimelineEvents(string $q, int $limit = 3): array
    {
        $limit = max(1, min($limit, 5));
        if (trim($q) === '') {
            $stmt = $this->db->prepare(
                "SELECT *, 'timeline_events' AS _table FROM timeline_events
                 WHERE status = 'published' ORDER BY RAND() LIMIT {$limit}"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'timeline_events' AS _table FROM timeline_events
             WHERE status = 'published'
               AND (title LIKE ? OR description LIKE ? OR short_summary LIKE ? OR era LIKE ?)
             ORDER BY year ASC
             LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'title');
        return !empty($filtered) ? $filtered : $rows;
    }

    /**
     * Long-form archive articles (content_items) — e.g. "The Origins of the Tiv
     * People", "The Tiv Migration into the Benue Valley". Not covered by any of
     * the targeted searches above, so a general question like "what is the origin
     * of Tiv?" previously found nothing in the initial battery, fell through to
     * notFoundResponse/meta-chat, and never surfaced the article written for
     * exactly that question. Matches on title/subcategory/excerpt only — never
     * on the full body text — so a passing mention deep in an unrelated article
     * can't outrank the piece actually about the subject.
     */
    private function searchContentItems(string $q, int $limit = 3): array
    {
        $limit = max(1, min($limit, 5));
        if (trim($q) === '') {
            // Sample mode, matching every other searchX() method — used by INTENT_HISTORY
            // for generic phrasing ("history of tiv") that resolves to an empty subject.
            $stmt = $this->db->prepare(
                "SELECT *, 'content_items' AS _table FROM content_items
                 WHERE status = 'published' ORDER BY RAND() LIMIT {$limit}"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'content_items' AS _table FROM content_items
             WHERE status = 'published'
               AND (title LIKE ? OR subcategory LIKE ? OR excerpt LIKE ?)
             LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like]);
        $rows = $stmt->fetchAll();

        // Poor-man's stemming fallback: a plain substring match can't relate "migrated"
        // to a title like "The Tiv Migration into the Benue Valley" — different word
        // forms of the same root never literally contain one another. Retry with just
        // the first ~6 characters of each word-form as a wildcard prefix (shared by
        // migrate/migrated/migration/migratory alike). Only worth doing for this table:
        // it's a small, curated set of long-form articles, so the false-positive risk
        // from a looser match is low, unlike broader tables (dictionary, names) where a
        // 6-character prefix could easily collide with an unrelated headword.
        if (empty($rows)) {
            $words = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY);
            $stems = array_unique(array_map(
                fn($w) => mb_substr($w, 0, min(mb_strlen($w), 6)),
                array_filter($words, fn($w) => mb_strlen($w) >= 5)
            ));
            foreach ($stems as $stem) {
                $stemLike = '%' . $stem . '%';
                $stmt->execute([$stemLike, $stemLike, $stemLike]);
                $rows = array_merge($rows, $stmt->fetchAll());
                if (!empty($rows)) break;
            }
        }

        $filtered = $this->filterByRelevance($rows, $q, 'title');
        return !empty($filtered) ? $filtered : $rows;
    }

    private function searchGrammarRules(string $q, int $limit = 3): array
    {
        $limit = max(1, min($limit, 5));
        if (trim($q) === '') {
            $stmt = $this->db->prepare(
                "SELECT *, 'tiv_grammar_rules' AS _table FROM tiv_grammar_rules
                 ORDER BY sort_order ASC LIMIT {$limit}"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_grammar_rules' AS _table FROM tiv_grammar_rules
             WHERE title LIKE ? OR summary LIKE ? OR explanation LIKE ? OR category LIKE ?
             ORDER BY sort_order ASC
             LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'title');
        return !empty($filtered) ? $filtered : $rows;
    }

    /**
     * Bible verses (30,861 rows — by far the largest single content type in the
     * archive) — indexed by ContentIndexer into archive_search_index, but until now
     * had no dedicated lexical search method here, so a direct verse/passage question
     * could only ever be found via the last-resort embedding fallback, never the fast/
     * precise path every other content type gets.
     */
    private function searchBibleVerses(string $q, int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));
        if (trim($q) === '') return [];
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'bible_verses' AS _table FROM bible_verses
             WHERE (book LIKE ? OR english_web LIKE ? OR tiv LIKE ?)
               AND tiv IS NOT NULL AND tiv != ''
             LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }

    /** Curated Tiv↔English translation phrases (distinct from single dictionary words). */
    private function searchPhrases(string $q, int $limit = 3): array
    {
        $limit = max(1, min($limit, 5));
        if (trim($q) === '') return [];
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'translation_phrases' AS _table FROM translation_phrases
             WHERE status = 'active' AND (source_text LIKE ? OR target_text LIKE ? OR context_tag LIKE ?)
             LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like]);
        $rows = $stmt->fetchAll();

        $filtered = $this->filterByRelevance($rows, $q, 'source_text');
        return !empty($filtered) ? $filtered : $rows;
    }

    /** Tiv alphabet letters — pronunciation, IPA, example words. */
    private function searchAlphabetEntries(string $q, int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));
        if (trim($q) === '') {
            $stmt = $this->db->prepare(
                "SELECT *, 'tiv_alphabet' AS _table FROM tiv_alphabet ORDER BY sort_order LIMIT {$limit}"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_alphabet' AS _table FROM tiv_alphabet
             WHERE letter LIKE ? OR english_letter LIKE ? OR tiv_example LIKE ? OR english_meaning LIKE ?
             ORDER BY sort_order LIMIT {$limit}"
        );
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    /** Learning videos (category/title/description) — table is empty today but kept
     *  consistent with every other content type for when content is added. */
    private function searchLearningVideos(string $q, int $limit = 3): array
    {
        $limit = max(1, min($limit, 5));
        if (trim($q) === '') return [];
        $like = '%' . $q . '%';
        try {
            $stmt = $this->db->prepare(
                "SELECT *, 'learning_videos' AS _table FROM learning_videos
                 WHERE is_active = 1 AND (title LIKE ? OR description LIKE ? OR category LIKE ?)
                 LIMIT {$limit}"
            );
            $stmt->execute([$like, $like, $like]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Unified FULLTEXT index search
    // ─────────────────────────────────────────────────────────────

    private function searchIndex(string $q, int $limit = 5, array $exclude = []): array
    {
        if (mb_strlen(trim($q)) < 2) return [];

        $excludeIds = array_column($exclude, 'id');

        $limitInt = (int) $limit; // MySQL LIMIT won't accept string-bound params

        // Try FULLTEXT first
        try {
            $sql = "SELECT * FROM archive_search_index
                    WHERE MATCH(primary_text, secondary_text, excerpt) AGAINST(? IN NATURAL LANGUAGE MODE)
                    LIMIT {$limitInt}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$q]);
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                return $rows;
            }
        } catch (\PDOException $e) {
            // FULLTEXT not available — fall through to LIKE
        }

        // LIKE fallback
        try {
            $like = '%' . $q . '%';
            $stmt = $this->db->prepare(
                "SELECT * FROM archive_search_index
                 WHERE primary_text LIKE ? OR secondary_text LIKE ? OR excerpt LIKE ?
                 LIMIT {$limitInt}"
            );
            $stmt->execute([$like, $like, $like]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // archive_search_index not yet created — return empty
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Knowledge graph: fetch related records
    // ─────────────────────────────────────────────────────────────

    private function fetchRelated(array $primaryResults): array
    {
        if (empty($primaryResults)) return [];

        $related = [];
        $first = $primaryResults[0];

        // Determine source table/id for the top result
        $sourceTable = $first['_table'] ?? $first['source_table'] ?? null;
        $sourceId    = (int) ($first['id'] ?? $first['source_id'] ?? 0);

        if (!$sourceTable || !$sourceId) return [];

        try {
            // Check archive_knowledge_links table
            $stmt = $this->db->prepare(
                "SELECT l.*, i.content_type, i.primary_text, i.secondary_text, i.excerpt, i.url
                 FROM archive_knowledge_links l
                 JOIN archive_search_index i ON i.source_table = l.to_table AND i.source_id = l.to_id
                 WHERE l.from_table = ? AND l.from_id = ?
                 ORDER BY l.weight DESC LIMIT 3"
            );
            $stmt->execute([$sourceTable, $sourceId]);
            $related = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table may not exist yet
        }

        return $related;
    }

    // ─────────────────────────────────────────────────────────────
    // Response Composition
    // ─────────────────────────────────────────────────────────────

    private function composeResponse(
        string $message,
        string $intent,
        string $subject,
        array  $results,
        array  $history,
        array  $compareSubjects = [],
        int    $requestedCount  = 5,
        bool   $isFollowUp      = false
    ): string {
        $primary = $results['primary'] ?? [];
        $related = $results['related'] ?? [];

        // Greeting
        if ($intent === self::INTENT_GREETING) {
            return $this->greetingResponse();
        }

        // "What can you do for me?" style meta questions about the assistant itself
        if ($intent === self::INTENT_HELP) {
            return $this->helpResponse();
        }

        // List request
        if ($intent === self::INTENT_LIST) {
            return $this->listResponse($message, $primary);
        }

        // Archive stats — runs live COUNT() queries directly, no search results needed.
        if ($intent === self::INTENT_STATS) {
            return $this->composeStatsResponse($message, $history);
        }

        // No results. For the catch-all GENERAL intent this usually means the message
        // was never really an archive search to begin with — small talk, a question
        // about the assistant itself, an off-topic request ("can you download this for
        // me?") — so a canned "not in the archive yet" reply reads as broken. Try a
        // bounded meta-chat completion first: it may discuss itself/its limits freely,
        // but is explicitly forbidden from stating any Tiv word/name/proverb/festival/
        // food/plant/animal/historical/grammar fact, since no evidence was retrieved.
        // Falls back to the deterministic "not found" path (unchanged) whenever the
        // model is disabled, unavailable, or times out, or for every other intent —
        // those already have a specific named subject and deserve the honest, fuzzy-
        // matched "not in the archive yet" reply rather than vague chat.
        if (empty($primary)) {
            // GENERAL/YESNO/SUMMARIZE are the catch-all intents with no clean, explicitly
            // named subject (unlike "what does gbande mean?" → WORD, subject "gbande") —
            // e.g. "can you write me a poem?" matches YESNO's broad "^can\b" pattern despite
            // having nothing to do with the archive. Meta-chat is the better fit for these.
            // But only when the archive search truly found NOTHING — an incidental match
            // means the fallback search actually turned up something plausibly relevant
            // (just not confidently enough to name as the answer), so the honest, still-
            // deterministic notFoundResponse() (which discloses that incidental hit) is
            // the safer reply, not an ungrounded LLM guess.
            if (in_array($intent, [self::INTENT_GENERAL, self::INTENT_YESNO, self::INTENT_SUMMARIZE], true)
                && empty($results['incidental'])) {
                $metaReply = $this->metaChatResponse($message, $history);
                if ($metaReply !== null) {
                    return $metaReply;
                }
            }
            return $this->notFoundResponse($subject, $message, $results['incidental'] ?? []);
        }

        // Try an LLM-composed conversational reply first, grounded strictly in the
        // evidence retrieval already found. Falls back to the deterministic template
        // composers below whenever the model is disabled, unavailable, or times out —
        // those composers are the safety net, not dead code.
        if ($this->llmComposeAllowed($intent)) {
            $llmReply = $this->composeWithLLM($message, $intent, $subject, $primary, $related, $history);
            if ($llmReply !== null) {
                return $llmReply;
            }
        }

        // Route to intent-specific composer
        switch ($intent) {
            case self::INTENT_WORD:
                // Separate dictionary rows from other content types.
                $wordRows  = array_values(array_filter($primary, fn($r) => ($r['_table'] ?? '') === 'daily_words'));
                $otherRows = array_values(array_filter($primary, fn($r) => ($r['_table'] ?? '') !== 'daily_words'));
                if (!empty($wordRows)) {
                    // On follow-ups about the same entity, show a full summary instead of
                    // repeating the same compact dictionary line.
                    if ($isFollowUp && !empty($wordRows)) {
                        return $this->composeSummaryResponse($wordRows, $subject, $related);
                    }
                    return $this->composeWordResponse($primary, $subject, $related);
                }
                return $this->composeGeneralResponse($otherRows, [], $related, $subject);
            case self::INTENT_NAME:
                return $this->composeNameResponse($primary, $subject, $related);
            case self::INTENT_PROVERB:
                return $this->composeProverbResponse($primary, $subject, $requestedCount);
            case self::INTENT_FOOD:
                return $this->composeFoodResponse($primary, $subject, $related);
            case self::INTENT_PLANT:
                return $this->composePlantResponse($primary, $subject, $related);
            case self::INTENT_FESTIVAL:
                return $this->composeFestivalResponse($primary, $subject, $related);
            case self::INTENT_ANIMAL:
                return $this->composeAnimalResponse($primary, $subject, $related);
            case self::INTENT_TRANSLATION:
                return $this->composeTranslationHelp($message, $primary);
            case self::INTENT_COMPARE:
                return $this->composeCompareResponse($compareSubjects, $subject);
            case self::INTENT_SUMMARIZE:
                if (isset($primary[0]['_table']) && ($this->guessTypeFromTable($primary[0]['_table']) === 'national')) {
                    return $this->composeNationalResponse($primary);
                }
                return $this->composeSummaryResponse($primary, $subject, $related);
            case self::INTENT_YESNO:
                return $this->composeYesNoResponse($message, $primary, $subject, $related);
            case self::INTENT_PERSON:
                return $this->composePersonResponse($primary, $subject, $related);
            case self::INTENT_HISTORY:
                return $this->composeHistoryResponse($primary, $subject);
            case self::INTENT_GRAMMAR:
                return $this->composeGrammarResponse($primary, $subject);
            default:
                return $this->composeGeneralResponse($primary, [], $related, $subject);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // LLM-composed conversational replies (Ollama) — grounded strictly in
    // the evidence the retrieval layer already found. The deterministic
    // composers further down remain the fallback whenever this is
    // unavailable, so nothing about the existing behavior is removed.
    // ─────────────────────────────────────────────────────────────

    private function llmComposeAllowed(string $intent): bool
    {
        if (!CHARYMELD_LLM_ENABLED) return false;
        // Compare needs two clearly-labelled evidence sets kept strictly separate —
        // not worth the added risk of the model conflating them yet. Everything else
        // that reaches this point already has non-empty evidence to ground a reply in.
        return $intent !== self::INTENT_COMPARE;
    }

    private function composeWithLLM(string $message, string $intent, string $subject, array $primary, array $related, array $history): ?string
    {
        $evidence = $this->buildEvidence($message, $primary, $related);
        if (empty($evidence['items'])) {
            return null; // nothing usable to ground a reply in — fall back to templates
        }

        $messages = [['role' => 'system', 'content' => $this->llmSystemPrompt()]];
        foreach ($this->recentHistoryTurns($history) as $turn) {
            $messages[] = $turn;
        }

        $userContent = "Archive evidence for this turn (the ONLY facts you may state — "
            . "do not add anything not present here):\n" . $evidence['text']
            . "\n\nUser's question: {$message}";
        $messages[] = ['role' => 'user', 'content' => $userContent];

        $logs  = new MarketingGenerationLog();
        $start = microtime(true);
        try {
            $raw = OllamaClient::chat($messages, CHARYMELD_LLM_TEMPERATURE, CHARYMELD_LLM_NUM_PREDICT);
        } catch (\Throwable $e) {
            error_log('Charymeld LLM error: ' . $e->getMessage());
            $raw = null;
        }
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        $logs->log([
            'purpose'        => 'charymeld_chat',
            'model_name'     => OLLAMA_MODEL,
            'request_prompt' => $userContent,
            'response_raw'   => $raw,
            'success'        => $raw !== null ? 1 : 0,
            'error_message'  => $raw === null ? 'Ollama did not respond (unreachable, disabled, or timed out).' : null,
            'duration_ms'    => $durationMs,
        ]);

        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $reply = trim($raw);

        // Safety net for a real, observed failure mode — see hasUnexpectedNonLatinScript().
        if (self::hasUnexpectedNonLatinScript($reply)) {
            error_log('Charymeld LLM produced unexpected non-Latin script — discarding and falling back to templates. Raw: ' . mb_substr($reply, 0, 300));
            return null;
        }

        // Deterministic backstop for the one grounding failure the system prompt can
        // only discourage, not guarantee, against sampling variance: the model
        // correctly admits the evidence doesn't establish a fact, then immediately
        // walks that back with "However/But/Although ... it suggests/likely/implies
        // ...". Strip that clause outright rather than trust every sample to omit it.
        $reply = $this->stripSpeculativeWalkback($reply);

        // Never trust the model to produce its own citation URLs — append the real
        // ones deterministically, filtered to the evidence actually reflected in the reply.
        $sources = $this->formatEvidenceSources($evidence['items'], $reply);
        if ($sources !== '') {
            $reply .= "\n\n" . $sources;
        }

        return $reply;
    }

    /**
     * Removes a sentence that starts with "However/But/Although" and speculates
     * about a fact ("it suggests he likely...", "it implies...", "probably...")
     * immediately after the model has already correctly admitted the evidence
     * doesn't establish that fact. Narrowly scoped to that exact hedge-then-guess
     * shape so it never touches ordinary sentences that happen to contain words
     * like "suggests" in a legitimate, already-grounded observation.
     */
    private function stripSpeculativeWalkback(string $reply): string
    {
        $stripped = preg_replace(
            '/(?:^|(?<=[.?!]\s))(?:However|But|Although)\b[^.?!]*\b(?:suggests?|implies?|likely|probably|indicat\w+|may\s+(?:be|have)|might\s+(?:be|have)|could\s+be|possibly)\b[^.?!]*[.?!]\s*/iu',
            '',
            $reply
        );
        $stripped = preg_replace('/\s{2,}/u', ' ', trim((string) $stripped));
        return $stripped !== '' ? $stripped : $reply;
    }

    /**
     * Bounded, ungrounded conversational fallback for messages that reach the
     * catch-all GENERAL intent with no archive results — small talk, questions
     * about the assistant itself, or off-topic requests. Unlike composeWithLLM(),
     * there is no retrieved evidence to ground a reply in, so the system prompt
     * forbids the model from stating any actual Tiv/archive fact; it may only
     * talk about its own capabilities and limits, or invite a specific question.
     * Same "return null, never throw" contract as composeWithLLM() — the caller
     * falls back to the deterministic notFoundResponse() on null.
     */
    private function metaChatResponse(string $message, array $history): ?string
    {
        if (!CHARYMELD_LLM_ENABLED) return null;

        $messages = [['role' => 'system', 'content' => $this->metaChatSystemPrompt()]];
        foreach ($this->recentHistoryTurns($history) as $turn) {
            $messages[] = $turn;
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $logs  = new MarketingGenerationLog();
        $start = microtime(true);
        try {
            $raw = OllamaClient::chat($messages, CHARYMELD_LLM_TEMPERATURE, CHARYMELD_LLM_NUM_PREDICT);
        } catch (\Throwable $e) {
            error_log('Charymeld meta-chat LLM error: ' . $e->getMessage());
            $raw = null;
        }
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        $logs->log([
            'purpose'        => 'charymeld_meta_chat',
            'model_name'     => OLLAMA_MODEL,
            'request_prompt' => $message,
            'response_raw'   => $raw,
            'success'        => $raw !== null ? 1 : 0,
            'error_message'  => $raw === null ? 'Ollama did not respond (unreachable, disabled, or timed out).' : null,
            'duration_ms'    => $durationMs,
        ]);

        if ($raw === null || trim($raw) === '') {
            return null;
        }
        if (self::hasUnexpectedNonLatinScript($raw)) {
            error_log('Charymeld meta-chat LLM produced unexpected non-Latin script — discarding. Raw: ' . mb_substr($raw, 0, 300));
            return null;
        }

        // Strip any markdown bold the model used anyway — bolded phrases in an
        // assistant reply are treated as "the entity we were just discussing" by
        // extractEntityFromLastAssistantTurn() on the NEXT turn, so a bolded phrase
        // here (which is never a real archive entity, since no evidence was used)
        // would wrongly hijack follow-up resolution the same way helpResponse()'s
        // bold category labels once did.
        return trim(preg_replace('/\*\*([^*\n]+)\*\*/u', '$1', trim($raw)));
    }

    private function metaChatSystemPrompt(): string
    {
        return "You are Tiv AI, the assistant on the Tiv Heritage Archive website, having a natural, "
            . "friendly conversation with a visitor. You have NO retrieved archive evidence for this "
            . "specific message, so you must NOT state, translate, define, or explain any actual Tiv "
            . "word, name, proverb, festival, food, plant, animal, person, historical event, or grammar "
            . "rule — you have no evidenced basis for any of that right now and must never invent one. "
            . "Instead: explain what you can help with (looking up Tiv words, names, proverbs, festivals, "
            . "foods, plants, animals, translations, history, and grammar from the archive), be honest "
            . "about your limits (you cannot download files, browse the web, access external accounts, "
            . "or take actions outside answering questions from this archive), answer questions about "
            . "yourself, or ask a short clarifying question to help the visitor ask something you CAN "
            . "answer. Keep the reply to 1-3 short, warm sentences. Do not use markdown bold or links.";
    }

    private function llmSystemPrompt(): string
    {
        return "You are the Tiv AI assistant on the Tiv Heritage Archive website — a knowledgeable, "
            . "warm conversational guide to Tiv language, culture, and history. "
            . "Answer using ONLY the archive evidence given to you in the user's message — never invent "
            . "Tiv words, meanings, names, dates, people, or cultural facts that aren't explicitly present "
            . "in that evidence. The evidence list may include items that are only loosely related or that "
            . "merely mention a name in passing (e.g. inside an unrelated example sentence) — read each item "
            . "and use only the ones that actually answer the question; ignore the rest rather than forcing "
            . "a connection. If the evidence doesn't fully answer the question, say so honestly rather than "
            . "guessing. This includes plausible-sounding INFERENCES, not just outright invention — e.g. do "
            . "not deduce a person's place of origin from a region they represented in office, or their "
            . "opinions from their job title, unless the evidence states the fact directly. A reasonable "
            . "guess presented as fact is still wrong; if the archive doesn't explicitly establish something, "
            . "say plainly that it doesn't, even when a nearby fact makes a guess tempting — and stop there. "
            . "Do NOT follow that admission with a 'however/but it suggests/implies/likely' clause that offers "
            . "the inference anyway; once you've said the evidence doesn't establish something, do not then "
            . "estimate it by another route in the same breath. Pivot instead to what the evidence DOES say, "
            . "or invite the user to ask about that instead. Do not write "
            . "markdown links or URLs yourself — those are added automatically "
            . "afterward. Reply in 2-5 sentences of natural conversational prose, not a bulleted search-"
            . "results list. You may end with one short, genuinely useful follow-up question, but skip it "
            . "if the answer is already complete.";
    }

    /**
     * Turn retrieved rows into compact, cited evidence: a text block for the prompt
     * plus the {title,url} pairs used to build the deterministic sources footer.
     * Deliberately short per item — the model's context window is 4096 tokens total.
     */
    private function buildEvidence(string $message, array $primary, array $related): array
    {
        $normalized = $this->fetchSourceRecords(array_slice($primary, 0, 4));
        $normalized = $this->filterEvidenceByAnswerRelevance($message, $normalized);

        $items = [];
        $lines = [];
        $n = 1;
        foreach ($normalized as $r) {
            $type = $r['_content_type'] ?? $this->guessTypeFromTable($r['_table'] ?? '');
            $snippet = $this->evidenceSnippet($r, $type);
            if ($snippet === null) continue;
            $items[] = $snippet;
            $lines[] = "{$n}. [{$snippet['type']}] {$snippet['title']} — {$snippet['text']}";
            $n++;
        }

        // Related (knowledge-graph) items: resolve through the same source-record +
        // evidenceSnippet() pipeline as primary items, rather than truncating the raw
        // archive_search_index.primary_text blob directly. ContentIndexer builds that
        // blob as title + tiv_title + full content + extracted_text all concatenated
        // with no delimiter (for full-text search), so a naive substr() of it produced
        // a title that was really "clean title + run-on start of the article body"
        // rather than the title alone.
        foreach (array_slice($related, 0, 2) as $r) {
            $mapped = $this->fetchSourceRecords([[
                'content_type' => $r['content_type'] ?? '',
                'source_table' => $r['to_table'] ?? '',
                'source_id'    => (int) ($r['to_id'] ?? 0),
                'url'          => $r['url'] ?? '',
            ]]);
            $resolved = $mapped[0] ?? null;
            if ($resolved === null) continue;
            $type = $resolved['_content_type'] ?? $this->guessTypeFromTable($resolved['_table'] ?? '');
            $snippet = $this->evidenceSnippet($resolved, $type);
            if ($snippet === null) continue;
            $items[] = $snippet;
            $lines[] = "{$n}. [related] {$snippet['title']} — {$snippet['text']}";
            $n++;
        }

        return ['items' => $items, 'text' => implode("\n", $lines)];
    }

    /**
     * Reject evidence that is merely RELATED to the question from evidence that
     * actually ANSWERS it — e.g. the dictionary word "Tar" (land/place) is
     * genuinely related to "where did the Tiv migrate from?" but doesn't answer
     * it; an article with the real migration history does. filterByRelevance()
     * upstream only checks whether a record's own headword matches the search
     * subject (noise reduction on LIKE hits) — nothing before this point checks
     * answer-relevance.
     *
     * Rows resolved from a direct, already-precision-matched search{Type}() call
     * (no _search_index_id — see fetchSourceRecords()) are exempt: they're exact/
     * targeted-table hits, not the lexical-expansion or embedding fallback tiers
     * this filter targets, and the pipeline already has deliberate type-ordering
     * for those (e.g. preferring articles over incidental word hits) that a blanket
     * similarity filter could regress while embedding coverage is still sparse.
     * Everything else is scored via the archive's existing embeddings (reusing
     * EmbeddingSearch — no second embedding system); a candidate with no stored
     * embedding yet (score === null) can't be penalized for that, so it passes
     * through unfiltered until the backfill catches up.
     */
    private function filterEvidenceByAnswerRelevance(string $message, array $normalized): array
    {
        $candidateIndexIds = [];
        foreach ($normalized as $i => $r) {
            if (!empty($r['_search_index_id'])) {
                $candidateIndexIds[$i] = (int) $r['_search_index_id'];
            }
        }
        if (empty($candidateIndexIds)) {
            return $normalized;
        }

        $scores = $this->embeddings->scoreCandidates($message, $candidateIndexIds);

        $kept = [];
        foreach ($normalized as $i => $r) {
            if (!isset($candidateIndexIds[$i])) {
                $kept[] = $r;
                continue;
            }
            $score = $scores[$i] ?? null;
            if ($score === null || $score >= EMBEDDING_MIN_SIMILARITY) {
                $kept[] = $r;
            }
        }

        // Every scoreable candidate failed the threshold and there were no exempt
        // rows to fall back on — an overly aggressive filter that starves the reply
        // of evidence entirely is worse than the noise it was meant to remove.
        return !empty($kept) ? $kept : $normalized;
    }

    private function evidenceSnippet(array $r, string $type): ?array
    {
        $trim = fn($s, $len) => mb_strlen((string) $s) > $len ? mb_substr((string) $s, 0, $len) . '…' : (string) $s;

        switch ($type) {
            case 'word':
                $title = $r['tiv_word'] ?? '';
                if ($title === '') return null;
                $text = 'means "' . ($r['english_meaning'] ?? '') . '"' . (!empty($r['part_of_speech']) ? " ({$r['part_of_speech']})" : '')
                    . (!empty($r['example_tiv']) ? '. Example: "' . $trim($r['example_tiv'], 100) . '" — ' . $trim($r['example_english'] ?? '', 100) : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['english_meaning'] ?? '', 'url' => isset($r['id']) ? url('word/' . $r['id']) : '', 'type' => 'Tiv word'];

            case 'name':
                $title = $r['tiv_name'] ?? '';
                if ($title === '') return null;
                $text = 'a Tiv ' . ($r['gender'] ?? '') . ' name meaning "' . ($r['english_meaning'] ?? '') . '"'
                    . (!empty($r['description']) ? '. ' . $trim($r['description'], 150) : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['english_meaning'] ?? '', 'url' => isset($r['id']) ? url('name/' . $r['id']) : '', 'type' => 'Tiv name'];

            case 'proverb':
                $title = $trim($r['tiv_text'] ?? '', 60);
                if ($title === '') return null;
                $text = 'translates as "' . $trim($r['english_translation'] ?? '', 100) . '"'
                    . (!empty($r['deeper_meaning']) ? '. Meaning: ' . $trim($r['deeper_meaning'], 150) : '');
                // A proverb's title is its Tiv-language text — a composed reply almost
                // always translates it into English prose rather than quoting the Tiv
                // sentence verbatim, so title-only matching in formatEvidenceSources()
                // would (and did) systematically fail for every proverb, falling back to
                // citing the WHOLE retrieved evidence set instead of just the one proverb
                // actually discussed. The English translation is the string a reply is
                // actually likely to contain.
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['english_translation'] ?? '', 'url' => isset($r['id']) ? url('proverb/' . $r['id']) : '', 'type' => 'Tiv proverb'];

            case 'food':
                $title = $r['tiv_name'] ?? '';
                if ($title === '') return null;
                $text = (!empty($r['english_name']) ? "({$r['english_name']}) " : '') . $trim($r['description'] ?? '', 150)
                    . (!empty($r['cultural_significance']) ? '. ' . $trim($r['cultural_significance'], 100) : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['english_name'] ?? '', 'url' => isset($r['id']) ? url('food/' . $r['id']) : '', 'type' => 'Tiv food'];

            case 'plant':
                $title = $r['tiv_name'] ?? '';
                if ($title === '') return null;
                $text = (!empty($r['english_name']) ? "({$r['english_name']}) " : '') . $trim($r['description'] ?? '', 150)
                    . (!empty($r['medicinal_uses']) ? '. Medicinal uses: ' . $trim($r['medicinal_uses'], 100) : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['english_name'] ?? '', 'url' => isset($r['id']) ? url('plant/' . $r['id']) : '', 'type' => 'Tiv plant'];

            case 'festival':
                $title = $r['tiv_name'] ?? '';
                if ($title === '') return null;
                // 'activities' wasn't included here at all, and 'significance' was cut
                // to 100 chars — for the Tiv Day record, that truncation lands BEFORE
                // "Swange dance" ever appears, so a genuinely correct record match still
                // left the model with no way to answer a "what is Swange dance?" style
                // question about a specific detail buried in the longer fields.
                $text = (!empty($r['english_name']) ? "({$r['english_name']}) " : '') . $trim($r['description'] ?? '', 150)
                    . (!empty($r['timing']) ? '. Timing: ' . $trim($r['timing'], 60) : '')
                    . (!empty($r['duration']) ? ' (' . $trim($r['duration'], 40) . ')' : '')
                    . (!empty($r['significance']) ? '. Significance: ' . $trim($r['significance'], 150) : '')
                    . (!empty($r['activities']) ? '. Activities: ' . $trim($r['activities'], 150) : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['english_name'] ?? '', 'url' => isset($r['id']) ? url('festival/' . $r['id']) : '', 'type' => 'Tiv festival'];

            case 'animal':
                $title = $r['tiv_name'] ?? '';
                if ($title === '') return null;
                $text = (!empty($r['name']) ? "({$r['name']}) " : '') . $trim($r['description'] ?? '', 150)
                    . (!empty($r['cultural_use']) ? '. Cultural role: ' . $trim($r['cultural_use'], 100) : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['name'] ?? '', 'url' => isset($r['id']) ? url('animal/' . $r['id']) : '', 'type' => 'Tiv animal'];

            case 'historical_figure':
                $title = $r['english_name'] ?? '';
                if ($title === '') return null;
                $text = trim(($r['title'] ?? '') . ' ' . ($r['historical_period'] ?? '')) . '. '
                    . $trim($r['short_summary'] ?? $r['biography'] ?? '', 200)
                    . (!empty($r['achievements']) ? '. Achievements: ' . $trim($r['achievements'], 100) : '');
                return ['title' => $title, 'text' => $text, 'url' => isset($r['id']) ? url('historical-figure/' . $r['id']) : '', 'type' => 'Historical figure'];

            case 'timeline_event':
                $title = $r['title'] ?? '';
                if ($title === '') return null;
                $text = (!empty($r['year']) ? "({$r['year']}) " : '') . $trim($r['short_summary'] ?? $r['description'] ?? '', 200);
                return ['title' => $title, 'text' => $text, 'url' => isset($r['id']) ? url('timeline-event/' . $r['id']) : '', 'type' => 'Timeline event'];

            case 'grammar':
                $title = $r['title'] ?? '';
                if ($title === '') return null;
                $text = $trim($r['summary'] ?? $r['explanation'] ?? '', 200);
                return ['title' => $title, 'text' => $text, 'url' => url('language/grammar'), 'type' => 'Grammar rule'];

            case 'alphabet':
                $title = $r['letter'] ?? '';
                if ($title === '') return null;
                $text = trim(($r['sound_desc'] ?? '') . (!empty($r['tiv_example']) ? '. Example: "' . $trim($r['tiv_example'], 60) . '"' : ''));
                return ['title' => $title, 'text' => $text, 'url' => url('language/grammar'), 'type' => 'Tiv alphabet letter'];

            case 'phrase':
                $title = $r['source_text'] ?? '';
                if ($title === '') return null;
                $text = 'translates as "' . $trim($r['target_text'] ?? '', 150) . '"'
                    . (!empty($r['context_tag']) ? " (context: {$r['context_tag']})" : '');
                return ['title' => $title, 'text' => $text, 'match_hint' => $r['target_text'] ?? '', 'url' => url('translate'), 'type' => 'Translation phrase'];

            case 'video':
                $title = $r['title'] ?? '';
                if ($title === '') return null;
                $text = $trim($r['description'] ?? '', 200);
                return ['title' => $title, 'text' => $text, 'url' => isset($r['id']) ? url('learn/' . $r['id']) : '', 'type' => 'Learning video'];

            case 'bible':
                $ref = trim(($r['book'] ?? '') . ' ' . ($r['chapter'] ?? '') . ':' . ($r['verse'] ?? ''));
                if ($ref === '') return null;
                $text = $trim($r['tiv'] ?? '', 100) . ' — ' . $trim($r['english_web'] ?? '', 100);
                $url  = url('bible/' . ($r['book'] ?? '') . '/' . ($r['chapter'] ?? '') . '#v' . ($r['verse'] ?? ''));
                return ['title' => $ref, 'text' => $text, 'url' => $url, 'type' => 'Bible verse'];

            default:
                $title = $r['title'] ?? $r['tiv_name'] ?? $r['primary_text'] ?? '';
                if ($title === '') return null;
                $text = $trim($r['excerpt'] ?? $r['content'] ?? $r['description'] ?? '', 200);
                $url  = $r['_url'] ?? (isset($r['id'], $r['_table']) ? $this->urlForTable($r['_table'], (int) $r['id']) : '');
                return ['title' => $title, 'text' => $text, 'url' => $url, 'type' => 'Archive entry'];
        }
    }

    /**
     * Evidence item URLs are inconsistently absolute (built via the url() helper,
     * e.g. most of evidenceSnippet()'s cases) or relative (taken straight from
     * archive_search_index.url, e.g. content_items' stored "/content-item/9") —
     * displaying whichever form a given item happened to carry made citations in
     * the same footer look inconsistent ("https://.../content-item/9" next to
     * "/content-item/8"). Normalizes to absolute for display; already-absolute
     * URLs pass through unchanged (url() is NOT safely idempotent on those — it
     * would prefix SITE_URL onto an already-full URL — hence the explicit check).
     */
    private static function absoluteUrl(string $url): string
    {
        if ($url === '') return '';
        return preg_match('#^https?://#i', $url) ? $url : url($url);
    }

    /**
     * Every source cited must actually support something the reply says. With a
     * single piece of evidence there's nothing to filter — buildEvidence() already
     * excludes empty results, so it's necessarily what grounded the answer. With
     * multiple items, only cite the ones whose title actually shows up in the
     * generated text — dumping every retrieved-but-unused record (e.g. tangential
     * historical figures or Bible verses pulled in by a broad search) is exactly
     * the "irrelevant sources" failure this guards against.
     */
    private function formatEvidenceSources(array $items, string $replyText = ''): string
    {
        if (empty($items)) return '';

        $replyLower = mb_strtolower($replyText);

        // With a SINGLE piece of evidence, always keep citing it even when the model
        // says it doesn't cover the specific detail asked about (e.g. "George Akume" is
        // still the right, relevant source for "the archive doesn't mention his father" —
        // it's the record that was actually checked). The ambiguity this guards against —
        // several retrieved-but-irrelevant records getting cited alongside an honest "I
        // don't know" — can only arise when there's more than one candidate item.
        $candidates = $items;
        $used = [];
        if (count($items) > 1 && $replyText !== '') {
            $used = array_values(array_filter($items, function ($it) use ($replyLower) {
                // Check the title (the record's own headword — Tiv-language for words/
                // names/proverbs) AND match_hint (its English meaning/translation, where
                // available). A proverb's title IS its Tiv sentence, but a composed reply
                // almost always translates it into English prose rather than quoting the
                // Tiv text verbatim — title-only matching used to fail for essentially
                // every proverb, so a "here's ONE proverb" reply that clearly translated
                // and discussed only one still cited the entire retrieved batch instead
                // of narrowing to the one actually used.
                foreach ([$it['title'] ?? '', $it['match_hint'] ?? ''] as $candidate) {
                    if ($candidate === '') continue;
                    $probe = mb_strtolower(mb_substr($candidate, 0, min(mb_strlen($candidate), 20)));
                    if (mb_strlen($probe) >= 3 && mb_strpos($replyLower, $probe) !== false) {
                        return true;
                    }
                }
                return false;
            }));
            // Only narrow down when the filter actually matched something — no matches
            // means the model paraphrased rather than quoted verbatim, not that nothing
            // was used, so fall back to the full (already retrieval-filtered) set rather
            // than citing nothing for a genuinely grounded answer.
            if (!empty($used)) {
                $candidates = $used;
            }
        }

        if (count($items) > 1 && empty($used)) {
            // The model explicitly said the evidence doesn't answer the question, AND no
            // evidence item's own title was ever referenced by name — never cite anything
            // in that case. This is what stops an off-target retrieval (e.g. the wrong
            // entity got searched) from surfacing irrelevant "Sources" under an honest "I
            // don't have that" answer. But when a title WAS referenced (the $used check
            // above), the model demonstrably engaged with specific evidence even while
            // honestly hedging about some OTHER detail it doesn't have ("the record covers
            // X but doesn't specify Y") — that combination deserves its citation kept, not
            // wiped out just because the reply also contains a hedging phrase.
            //
            // Only phrases that read as a WHOLE-ANSWER disclaimer belong here. Narrower
            // hedges like "does not specify [some detail]" or "does not mention [some
            // detail]" are exactly how a model phrases a PARTIAL, still-grounded answer
            // ("the record covers the ancestry but doesn't specify the geography") — those
            // used to wipe out a citation for a genuinely evidence-based reply just because
            // the model was honest about one gap in it. If the reply's real problem is that
            // it drew on nothing at all, $used being empty (checked above) plus one of these
            // strong phrases is still enough signal to suppress citations correctly.
            static $noEvidencePhrases = [
                'does not include', "doesn't include", 'do not include',
                'not documented', 'no information', 'cannot provide', "can't provide",
                "doesn't contain", 'does not contain', 'not currently provide',
                'not currently have', "don't have that", 'not in the archive',
            ];
            foreach ($noEvidencePhrases as $phrase) {
                if (mb_strpos($replyLower, $phrase) !== false) {
                    return '';
                }
            }
        }

        $seen  = [];
        $links = [];
        foreach ($candidates as $it) {
            $url   = self::absoluteUrl($it['url'] ?? '');
            $title = $it['title'] ?? '';
            if ($url === '' || $title === '') continue;
            // Dedup key is the URL's PATH, not the raw string — buildEvidence() builds
            // "primary" item URLs as absolute (via the url() helper) but "related"
            // (knowledge-graph) item URLs straight from archive_search_index.url, which
            // is stored relative (e.g. "/word/1397"). The same underlying record then
            // produces two different url() strings for what is the same page, so a
            // raw-string dedup let it slip through twice — the observed bug was the
            // same dictionary entry cited 2-3x in a row under a "Sources:" footer.
            $key = parse_url($url, PHP_URL_PATH) ?: $url;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $links[] = "[{$title}]({$url})";
        }
        if (empty($links)) return '';
        return (count($links) === 1 ? '*Source:* ' : '*Sources:* ') . implode(', ', $links);
    }

    /**
     * The last few user/assistant turns, trimmed for the model's 4096-token context
     * window. Strips the deterministic sources footer so the model isn't re-fed its
     * own citation formatting as if it were conversation content.
     */
    private function recentHistoryTurns(array $history): array
    {
        $turns = [];
        foreach ($history as $turn) {
            $role    = $turn['role'] ?? '';
            $content = trim($turn['content'] ?? '');
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') continue;
            $content = preg_replace('/\n\n\*Sources?:\*.+$/su', '', $content);
            $turns[] = ['role' => $role, 'content' => mb_substr($content, 0, 500)];
        }
        return array_slice($turns, -1 * CHARYMELD_LLM_HISTORY_TURNS * 2);
    }

    // ─────────────────────────────────────────────────────────────
    // Intent-specific response composers
    // ─────────────────────────────────────────────────────────────

    private function composeWordResponse(array $rows, string $subject, array $related): string
    {
        if (empty($rows)) return $this->notFoundResponse($subject, '');

        // Drop rows with blank tiv_word (bad data entries) to avoid "****" output
        $rows = array_values(array_filter($rows, fn($r) => !empty(trim($r['tiv_word'] ?? ''))));
        if (empty($rows)) return $this->notFoundResponse($subject, '');

        // Group rows by tiv_word so homonyms are shown as one entry with multiple POS meanings
        $grouped = [];
        foreach ($rows as $r) {
            $key = mb_strtolower($r['tiv_word'] ?? $r['primary_text'] ?? 'unknown');
            $grouped[$key][] = $r;
        }

        $parts = [];
        foreach (array_slice($grouped, 0, 3) as $wordKey => $variants) {
            $first = $variants[0];
            $tiv   = $first['tiv_word'] ?? $first['primary_text'] ?? '';
            $pron  = $first['pronunciation'] ?? '';
            $url   = isset($first['id']) ? url('word/' . $first['id']) : ($first['url'] ?? '');

            $pronNote = $pron ? " (/{$pron}/)" : '';

            if (count($variants) > 1) {
                // HOMONYM: multiple POS — prose list format
                $n    = count($variants);
                $line = "**{$tiv}**{$pronNote} has {$n} meanings in Tiv depending on its use:\n";
                foreach ($variants as $v) {
                    $pos    = $v['part_of_speech'] ?? '';
                    $eng    = $v['english_meaning'] ?? '';
                    $alt    = $v['alternate_meaning'] ?? '';
                    $exTiv  = $v['example_tiv'] ?? '';
                    $exEng  = $v['example_english'] ?? '';
                    $vUrl   = isset($v['id']) ? url('word/' . $v['id']) : '';

                    $posLabel = $pos ? "**{$pos}**" : 'this use';
                    $entry    = "\n- As a {$posLabel}: *{$eng}*";
                    if ($alt) $entry .= ". It can also mean *{$alt}*";
                    if ($exTiv) {
                        $entry .= ".\n\n  *Example:* \"{$exTiv}\"";
                        if ($exEng) $entry .= " — {$exEng}";
                    }
                    if ($vUrl) $entry .= "\n\n  [View full entry →]({$vUrl})";
                    $line .= $entry;
                }
            } else {
                // Single POS — natural prose sentence
                $pos   = $first['part_of_speech'] ?? '';
                $eng   = $first['english_meaning'] ?? $first['secondary_text'] ?? '';
                $alt   = $first['alternate_meaning'] ?? '';
                $exTiv = $first['example_tiv'] ?? '';
                $exEng = $first['example_english'] ?? '';

                $posLabel = $pos ?: 'word';
                $line     = "**{$tiv}**{$pronNote} is a Tiv {$posLabel} meaning *{$eng}*";
                if ($alt) $line .= ". It can also mean *{$alt}*";
                if ($exTiv) {
                    $line .= ".\n\n*Example:* \"{$exTiv}\"";
                    if ($exEng) $line .= " — {$exEng}";
                }
                if ($url) $line .= "\n\n[View full entry →]({$url})";
            }

            $parts[] = $line;
        }

        $response = count($parts) === 1
            ? "From the Tiv Dictionary:\n\n" . $parts[0]
            : "Tiv dictionary results for **{$subject}**:\n\n" . implode("\n\n", $parts);

        if (!empty($related)) {
            $response .= "\n\n**Related entries:** " . $this->formatRelatedLinks($related);
        }

        // Cross-source enrichment
        $enrichment = $this->enrichWithMentions($subject, 'word');
        if ($enrichment) $response .= "\n\n" . $enrichment;

        $response .= $this->generateFollowUpQuestion(array_values(reset($grouped) ?: []), 'word', $subject);
        return $response;
    }

    private function composeNameResponse(array $rows, string $subject, array $related): string
    {
        $parts = [];
        foreach (array_slice($rows, 0, 3) as $r) {
            $name   = $r['tiv_name'] ?? $r['primary_text'] ?? '';
            $eng    = $r['english_meaning'] ?? $r['secondary_text'] ?? '';
            $gender = $r['gender'] ?? '';
            $desc   = trim($r['description'] ?? '');
            $origin = $r['origin_story'] ?? '';
            $url    = isset($r['id']) ? url('name/' . $r['id']) : ($r['url'] ?? '');

            // Build natural prose sentence
            $genderLabel = match(mb_strtolower($gender)) {
                'male'   => 'male',
                'female' => 'female',
                'unisex' => 'unisex',
                default  => '',
            };

            $line = "**{$name}** is a Tiv";
            if ($genderLabel) $line .= " {$genderLabel}";
            $line .= " name meaning *{$eng}*";
            if ($desc) $line .= ". {$desc}";
            if ($origin) $line .= "\n\n*Origin:* {$origin}";
            if ($url) $line .= "\n\n[View in archive →]({$url})";
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject, '');

        $isGeneric = empty(trim($subject));
        $response = $isGeneric
            ? "Here are some Tiv names from the archive:\n\n" . implode("\n\n", $parts)
            : (count($parts) === 1
                ? "From the Tiv Names archive:\n\n" . $parts[0]
                : "Tiv names matching **{$subject}**:\n\n" . implode("\n\n", $parts));

        // Cross-source enrichment
        $enrichment = $this->enrichWithMentions($subject, 'name');
        if ($enrichment) $response .= "\n\n" . $enrichment;

        if (!$isGeneric) {
            $response .= $this->generateFollowUpQuestion($rows, 'name', $subject);
        }
        return $response;
    }

    private function composeProverbResponse(array $rows, string $subject, int $count = 5): string
    {
        if (empty($rows)) {
            return $this->notFoundResponse($subject ?: 'proverbs', '');
        }

        // Honour the requested count
        $rows = array_slice($rows, 0, max(1, min($count, 10)));

        $isGeneric = empty($subject) || in_array(mb_strtolower($subject), ['proverb','proverbs','saying','sayings'], true);
        $n         = count($rows);
        $isSingle  = ($n === 1);

        $parts = [];
        foreach ($rows as $i => $r) {
            $tiv    = $r['tiv_text'] ?? $r['primary_text'] ?? '';
            $eng    = $r['english_translation'] ?? $r['secondary_text'] ?? '';
            $deeper = $r['deeper_meaning'] ?? '';
            $usage  = $r['usage_context'] ?? '';
            $url    = isset($r['id']) ? url('proverb/' . $r['id']) : ($r['url'] ?? '');

            // Trim the translation to the first sentence if it runs long
            if (mb_strlen($eng) > 80) {
                $firstSentence = preg_split('/(?<=[.!?])\s+(?=[A-Z])/u', $eng, 2);
                $eng = trim($firstSentence[0]);
            }

            if ($isSingle) {
                // Single proverb — full prose treatment
                $line = "The Tiv proverb **\"{$tiv}\"** translates as:\n\n*\"{$eng}\"*";
                if ($deeper && mb_strtolower($deeper) !== mb_strtolower($eng)) {
                    $line .= "\n\nThis teaches: " . mb_substr($deeper, 0, 200) . (mb_strlen($deeper) > 200 ? '…' : '');
                }
                if ($usage) {
                    $line .= "\n\nUsed when: " . mb_substr($usage, 0, 150) . (mb_strlen($usage) > 150 ? '…' : '');
                }
                if ($url) $line .= "\n\n[Read more →]({$url})";
            } else {
                // Multiple proverbs — numbered prose blocks separated by dividers
                $num  = $i + 1;
                $line = "**{$num}.** The Tiv proverb **\"{$tiv}\"** translates as:\n\n*\"{$eng}\"*";
                if ($deeper && mb_strtolower($deeper) !== mb_strtolower($eng)) {
                    $line .= "\n\nThis teaches: " . mb_substr($deeper, 0, 120) . (mb_strlen($deeper) > 120 ? '…' : '');
                }
                if ($usage) {
                    $line .= "\n\nUsed when: " . mb_substr($usage, 0, 100) . (mb_strlen($usage) > 100 ? '…' : '');
                }
                if ($url) $line .= "\n\n[Read more →]({$url})";
            }

            $parts[] = $line;
        }

        $intro = $isGeneric
            ? "Here " . ($n === 1 ? 'is' : 'are') . " {$n} Tiv proverb" . ($n !== 1 ? 's' : '') . " from the archive:\n\n"
            : "Here " . ($n === 1 ? 'is a' : "are {$n}") . " Tiv proverb" . ($n !== 1 ? 's' : '') . " related to **{$subject}**:\n\n";

        return $intro . implode("\n\n---\n\n", $parts) . $this->generateFollowUpQuestion($rows, 'proverb', $subject);
    }

    private function composeFoodResponse(array $rows, string $subject, array $related): string
    {
        $parts = [];
        foreach (array_slice($rows, 0, 2) as $r) {
            $tiv  = $r['tiv_name'] ?? $r['primary_text'] ?? '';
            $eng  = $r['english_name'] ?? $r['secondary_text'] ?? '';
            $desc = trim($r['description'] ?? $r['excerpt'] ?? '');
            $sig  = $r['cultural_significance'] ?? '';
            $prep = $r['preparation_method'] ?? '';
            $url  = isset($r['id']) ? url('food/' . $r['id']) : ($r['url'] ?? '');

            // Natural prose opening sentence
            $engPart = $eng ? " ({$eng})" : '';
            if (mb_strlen($sig) > 20) {
                $line = "**{$tiv}**{$engPart} is a traditional Tiv dish with deep cultural significance";
            } else {
                $line = "**{$tiv}**{$engPart} is a traditional Tiv dish";
            }
            if ($desc) $line .= ". {$desc}";
            else $line .= ".";
            if ($prep) $line .= "\n\n*Preparation:* " . mb_substr($prep, 0, 200) . (mb_strlen($prep) > 200 ? '…' : '');
            if ($sig)  $line .= "\n\n*Cultural significance:* {$sig}";
            if ($url)  $line .= "\n\n[View in archive →]({$url})";
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject, '');

        $response = "From the Tiv Foods archive:\n\n" . implode("\n\n", $parts);
        if (!empty($related)) {
            $response .= "\n\n**Related:** " . $this->formatRelatedLinks($related);
        }

        // Cross-source enrichment
        $enrichment = $this->enrichWithMentions($subject, 'food');
        if ($enrichment) $response .= "\n\n" . $enrichment;

        $response .= $this->generateFollowUpQuestion($rows, 'food', $subject);
        return $response;
    }

    private function composePlantResponse(array $rows, string $subject, array $related): string
    {
        $parts = [];
        foreach (array_slice($rows, 0, 2) as $r) {
            $tiv  = $r['tiv_name'] ?? $r['primary_text'] ?? '';
            $eng  = $r['english_name'] ?? $r['secondary_text'] ?? '';
            $sci  = $r['scientific_name'] ?? '';
            $desc = trim($r['description'] ?? $r['excerpt'] ?? '');
            $med  = $r['medicinal_uses'] ?? '';
            $rit  = $r['ritual_uses'] ?? '';
            $url  = isset($r['id']) ? url('plant/' . $r['id']) : ($r['url'] ?? '');

            $engPart = $eng ? " ({$eng}" . ($sci ? ", *{$sci}*" : '') . ")" : ($sci ? " (*{$sci}*)" : '');
            $line = "**{$tiv}**{$engPart} is a plant used in Tiv culture.";
            if ($desc) $line .= " {$desc}";
            if ($med)  $line .= "\n\n*Medicinal uses:* {$med}";
            if ($rit)  $line .= "\n\n*Ritual significance:* {$rit}";
            if ($url)  $line .= "\n\n[View in archive →]({$url})";
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject, '');

        $response = "From the Tiv Plants archive:\n\n" . implode("\n\n", $parts);

        // Cross-source enrichment
        $enrichment = $this->enrichWithMentions($subject, 'plant');
        if ($enrichment) $response .= "\n\n" . $enrichment;

        return $response . $this->generateFollowUpQuestion($rows, 'plant', $subject);
    }

    private function composeFestivalResponse(array $rows, string $subject, array $related): string
    {
        $parts = [];
        foreach (array_slice($rows, 0, 2) as $r) {
            $tiv  = $r['tiv_name'] ?? $r['primary_text'] ?? '';
            $eng  = $r['english_name'] ?? $r['secondary_text'] ?? '';
            $desc = trim($r['description'] ?? $r['excerpt'] ?? '');
            $sig  = $r['significance'] ?? '';
            $acts = $r['activities'] ?? '';
            $time = $r['timing'] ?? '';
            $url  = isset($r['id']) ? url('festival/' . $r['id']) : ($r['url'] ?? '');

            // Use only the first word of tiv_name to avoid leaking long descriptions
            $tivShort = explode(' ', $tiv)[0];

            // Build the opening prose sentence
            $displayName = $eng ?: $tiv;
            $tivNote     = ($tivShort && $tivShort !== $displayName) ? " (Tiv: *{$tivShort}*)" : '';
            $timingNote  = ($time && mb_strlen($time) < 40) ? " celebrated {$time}" : '';

            // Truncate description to first 2 sentences or 280 chars
            $descShort = '';
            if ($desc) {
                $sentences = preg_split('/(?<=[.!?])\s+/u', $desc, 3);
                $descShort = implode(' ', array_slice($sentences, 0, 2));
                if (mb_strlen($descShort) > 280) {
                    $descShort = mb_substr($descShort, 0, 278) . '…';
                }
            }

            $line = "**{$displayName}**{$tivNote} is a traditional Tiv festival{$timingNote}.";
            if ($descShort) $line .= " {$descShort}";
            if ($sig && mb_strlen($sig) < 300) $line .= "\n\n*Significance:* {$sig}";
            if ($acts) $line .= "\n\n*Activities:* " . mb_substr($acts, 0, 200) . (mb_strlen($acts) > 200 ? '…' : '');
            if ($url)  $line .= "\n\n[View in archive →]({$url})";
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject, '');
        $response = "From the Tiv Festivals archive:\n\n" . implode("\n\n", $parts);
        if (!empty($related)) {
            $response .= "\n\n**Related foods & traditions:** " . $this->formatRelatedLinks($related);
        }

        // Cross-source enrichment
        $enrichment = $this->enrichWithMentions($subject, 'festival');
        if ($enrichment) $response .= "\n\n" . $enrichment;

        $response .= $this->generateFollowUpQuestion($rows, 'festival', $subject);
        return $response;
    }

    private function composeAnimalResponse(array $rows, string $subject, array $related): string
    {
        $parts = [];
        foreach (array_slice($rows, 0, 2) as $r) {
            $tiv  = $r['tiv_name'] ?? $r['primary_text'] ?? '';
            $eng  = $r['name'] ?? $r['secondary_text'] ?? '';
            $desc = trim($r['description'] ?? $r['excerpt'] ?? '');
            $cult = $r['cultural_use'] ?? '';
            $type = $r['animal_type'] ?? '';
            $url  = isset($r['id']) ? url('animal/' . $r['id']) : ($r['url'] ?? '');

            $engPart  = $eng ? " ({$eng})" : '';
            $typePart = $type ? ", a {$type} animal," : '';
            $line = "**{$tiv}**{$engPart}{$typePart} is an animal in Tiv culture.";
            if ($desc) $line .= " {$desc}";
            if ($cult) $line .= "\n\n*Cultural role:* {$cult}";
            if ($url)  $line .= "\n\n[View in archive →]({$url})";
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject, '');

        $response = "From the Tiv Animals archive:\n\n" . implode("\n\n", $parts);

        // Cross-source enrichment
        $enrichment = $this->enrichWithMentions($subject, 'animal');
        if ($enrichment) $response .= "\n\n" . $enrichment;

        return $response . $this->generateFollowUpQuestion($rows, 'animal', $subject);
    }

    private function composePersonResponse(array $rows, string $subject, array $related): string
    {
        // $rows may be search-index rows, whose 'id' is the index row's own id and whose
        // record may not be a person at all (e.g. a dictionary entry matching "Tor Tiv").
        // Resolve them to real historical_figures rows; anything else goes to the general
        // composer, which presents each record as what it actually is.
        $resolved = $this->fetchSourceRecords($rows);
        $people = array_values(array_filter($resolved, fn($r) => ($r['_content_type'] ?? '') === 'historical_figure'));
        if (empty($people)) {
            return $this->composeGeneralResponse($rows, [], $related, $subject);
        }
        $rows = $people;

        $parts = [];
        foreach (array_slice($rows, 0, 2) as $r) {
            $name    = $r['english_name'] ?? $r['primary_text'] ?? '';
            $tivName = $r['tiv_name'] ?? '';
            $title   = $r['title'] ?? '';
            $period  = $r['historical_period'] ?? '';
            $summary = trim($r['short_summary'] ?? '');
            $bio     = trim($r['biography'] ?? '');
            $achieve = trim($r['achievements'] ?? '');
            $legacy  = trim($r['legacy'] ?? '');
            $url     = HeritagePublic::recordUrl('historical_figures', $r);
            $archive = !empty($r['national_slug']) ? 'Nigeria Heritage Archive' : 'Tiv Heritage Archive';

            $titlePart  = $title ? ", {$title}," : '';
            $tivPart    = ($tivName && $tivName !== $name) ? " (Tiv: *{$tivName}*)" : '';
            $periodPart = $period ? " from the {$period}" : '';

            if ($summary) {
                $body = $summary; // already a complete sentence — never append an ellipsis
            } else {
                $body = mb_substr($bio, 0, 280);
                if (mb_strlen($bio) > 280) $body .= '…';
            }

            $line = "**{$name}**{$tivPart}{$titlePart} is a historical figure in the {$archive}{$periodPart}.";
            if ($body)    $line .= " {$body}";
            if ($achieve) $line .= "\n\n*Achievements:* " . mb_substr($achieve, 0, 220) . (mb_strlen($achieve) > 220 ? '…' : '');
            if ($legacy)  $line .= "\n\n*Legacy:* " . mb_substr($legacy, 0, 220) . (mb_strlen($legacy) > 220 ? '…' : '');
            if ($url)     $line .= "\n\n[View full profile →]({$url})";
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject, '');

        $response = (count($parts) === 1
            ? "From the Tiv Heritage Archive:\n\n" . $parts[0]
            : "Historical figures matching **{$subject}**:\n\n" . implode("\n\n", $parts));

        if (!empty($related)) {
            $response .= "\n\n**Related:** " . $this->formatRelatedLinks($related);
        }

        $response .= $this->generateFollowUpQuestion($rows, 'historical_figure', $subject);
        return $response;
    }

    private function composeHistoryResponse(array $rows, string $subject): string
    {
        $figures = array_values(array_filter($rows, fn($r) => ($r['_table'] ?? '') === 'historical_figures'));
        $events  = array_values(array_filter($rows, fn($r) => ($r['_table'] ?? '') === 'timeline_events'));

        if (empty($figures) && empty($events)) {
            return $this->notFoundResponse($subject ?: 'Tiv history', '');
        }

        $sections = [];

        if (!empty($figures)) {
            $lines = [];
            foreach (array_slice($figures, 0, 3) as $r) {
                $name = $r['english_name'] ?? '';
                $sum  = mb_substr(trim($r['short_summary'] ?? $r['biography'] ?? ''), 0, 100);
                $url  = url('historical-figure/' . $r['id']);
                $lines[] = "- [**{$name}**]({$url})" . ($sum ? " — {$sum}" : '');
            }
            $sections[] = "**👤 Historical Figures**\n" . implode("\n", $lines);
        }

        if (!empty($events)) {
            $lines = [];
            foreach (array_slice($events, 0, 3) as $r) {
                $title = $r['title'] ?? '';
                $year  = $r['year'] ?? '';
                $sum   = mb_substr(trim($r['short_summary'] ?? $r['description'] ?? ''), 0, 100);
                $url   = url('timeline-event/' . $r['id']);
                $yearPart = $year ? " ({$year})" : '';
                $lines[] = "- [**{$title}**]({$url}){$yearPart}" . ($sum ? " — {$sum}" : '');
            }
            $sections[] = "**🕰️ Timeline**\n" . implode("\n", $lines);
        }

        $intro = $subject
            ? "Here's what the archive documents about **{$subject}**:\n\n"
            : "Here's a look at Tiv history documented in the archive:\n\n";

        return $intro . implode("\n\n", $sections)
            . "\n\n*Would you like to see the full [Historical Figures](" . url('historical-figures') . ") gallery, or the [Timeline](" . url('timeline') . ")?*";
    }

    private function composeGrammarResponse(array $rows, string $subject): string
    {
        $parts = [];
        foreach (array_slice($rows, 0, 3) as $r) {
            $title   = $r['title'] ?? '';
            $summary = trim($r['summary'] ?? '');
            $expl    = trim($r['explanation'] ?? '');
            $examples = json_decode($r['examples'] ?? '', true) ?: [];

            $line = "**{$title}**" . ($summary ? " — {$summary}" : '');
            if ($expl) $line .= "\n\n" . mb_substr($expl, 0, 300) . (mb_strlen($expl) > 300 ? '…' : '');
            if (!empty($examples)) {
                $ex = $examples[0];
                $tiv = $ex['tiv'] ?? '';
                $eng = $ex['english'] ?? '';
                if ($tiv) {
                    $line .= "\n\n*Example:* \"{$tiv}\"" . ($eng ? " — {$eng}" : '');
                }
            }
            $parts[] = $line;
        }

        if (empty($parts)) return $this->notFoundResponse($subject ?: 'Tiv grammar', '');

        $response = "From the Tiv Grammar guide:\n\n" . implode("\n\n---\n\n", $parts);
        $response .= "\n\n[See the full grammar guide →](" . url('language/grammar') . ")";
        return $response;
    }

    // ─────────────────────────────────────────────────────────────
    // Conditional archive statistics
    //
    //   User → statistics/query interpretation → validated DB query →
    //   COUNT(*) → natural-language response
    //
    // The number in the reply always comes straight from a live COUNT(*) against
    // the real schema (STATS_ENTITIES/STATS_PROPERTIES above) — never a sample,
    // never the unconditional total when a condition was asked for, and never
    // something the LLM invented (the LLM is never called on this path at all).
    // ─────────────────────────────────────────────────────────────

    private function composeStatsResponse(string $message, array $history = []): string
    {
        $m = mb_strtolower(trim($message));

        // "What's in the archive?" / "What resources are available?" — the full,
        // unconditional breakdown across every category. Distinct from a targeted
        // "how many X (without Y)?" count, which the generic entity+condition path
        // below handles.
        if (preg_match('/what(?:\'s| is) in the archive|what (?:resources|content) (?:do you have|are available|is available)/u', $m)
            && $this->detectStatsEntity($m) === null) {
            return $this->fullStatsBreakdown();
        }

        $parsed = $this->parseStatsQuery($message, $history);

        if (!empty($parsed['clarify'])) {
            return $this->statsClarificationResponse($parsed);
        }

        $entityKey = $parsed['entity'];
        $entityDef = self::STATS_ENTITIES[$entityKey];
        $table     = $entityDef['table'];
        $baseWhere = $entityDef['where'];

        $totalSql = "SELECT COUNT(*) FROM {$table}" . ($baseWhere ? " WHERE {$baseWhere}" : '');
        try {
            $total = (int) $this->db->query($totalSql)->fetchColumn();
        } catch (\Throwable $e) {
            error_log('ArchiveIntelligence stats total query failed: ' . $e->getMessage());
            return "I couldn't reach the archive database just now — please try again in a moment.";
        }

        $propertyKey = $parsed['property'];
        $state       = $parsed['state'];
        $operation   = $parsed['operation'] ?? 'count';

        // A real, named field the user asked about, but one the archive doesn't
        // track at all (e.g. "video" — no table has a video column). Honest,
        // never fabricated.
        if (!empty($parsed['notTracked'])) {
            return "I don't currently track a **{$propertyKey}** field for {$entityDef['plural']}, so I can't give you that count. "
                . "I can tell you how many {$entityDef['plural']} have (or are missing) an image, though — would that help?"
                . "\n\n" . $this->statsFooter($entityKey, null, null);
        }

        $propertyDef = ($propertyKey !== null) ? (self::STATS_PROPERTIES[$entityKey][$propertyKey] ?? null) : null;

        // A property was named but this entity has no such column — never invent
        // a count/percentage/fetch for a field that doesn't exist.
        if ($propertyKey !== null && $propertyDef === null) {
            return "{$entityDef['plural']} don't have a **{$propertyKey}** field in the database, so that doesn't apply here. "
                . "The total is **" . number_format($total) . "** {$entityDef['plural']}."
                . "\n\n" . $this->statsFooter($entityKey, null, null);
        }

        // FETCH — retrieves the real, paginated database records (unfiltered or
        // filtered by property/state); never a COUNT/PERCENTAGE sentence.
        if ($operation === 'fetch') {
            $page = max(1, (int) ($parsed['page'] ?? 1));
            return $this->composeFetchResponse($entityKey, $entityDef, $table, $baseWhere, $propertyKey, $propertyDef, $state, $page, $total);
        }

        // Unconditional count — a percentage was already forced back to 'count'
        // in parseStatsQuery() when there's no property/state to compute it against.
        if ($propertyKey === null || $state === null) {
            $response = "The Tiv Heritage Archive currently documents **" . number_format($total) . "** {$entityDef['plural']}.";
            return $response . "\n\n" . $this->statsFooter($entityKey, null, null);
        }

        $condSql = $this->statsCountQuery($table, $baseWhere, $propertyDef, $state);
        try {
            $condCount = (int) $this->db->query($condSql)->fetchColumn();
        } catch (\Throwable $e) {
            error_log('ArchiveIntelligence stats condition query failed: ' . $e->getMessage());
            return "I couldn't reach the archive database just now — please try again in a moment.";
        }

        // PERCENTAGE — the calculation is plain deterministic arithmetic on the two
        // live COUNT(*) results above; the LLM never sees or computes this number.
        if ($operation === 'percentage') {
            $pct = $total > 0 ? round(($condCount / $total) * 100, 1) : 0.0;
            $verbPhrase = $this->statsVerbPhrase($propertyKey, $state);
            $response = "About **" . number_format($pct, 1) . "%** of the {$entityDef['plural']} in the Tiv Heritage Archive {$verbPhrase} — "
                . "**" . number_format($condCount) . "** of **" . number_format($total) . "** documented {$entityDef['plural']}.";

            return $response . "\n\n" . $this->statsFooter($entityKey, $propertyKey, $state, 'percentage');
        }

        $phrase   = $this->statsConditionPhrase($propertyKey, $state);
        $response = "The Tiv Heritage Archive currently has **" . number_format($condCount) . "** {$entityDef['plural']} {$phrase}, "
            . "out of **" . number_format($total) . "** documented {$entityDef['plural']}.";

        return $response . "\n\n" . $this->statsFooter($entityKey, $propertyKey, $state, 'count');
    }

    private function fullStatsBreakdown(): string
    {
        $lines = [];
        foreach (self::STATS_ENTITIES as $def) {
            $sql = "SELECT COUNT(*) FROM {$def['table']}" . ($def['where'] ? " WHERE {$def['where']}" : '');
            try {
                $count = (int) $this->db->query($sql)->fetchColumn();
            } catch (\Throwable $e) {
                continue;
            }
            $lines[] = "- **{$def['plural']}:** " . number_format($count);
        }

        return "Here's what the Tiv Heritage Archive currently documents:\n\n" . implode("\n", $lines)
            . "\n\nAsk me about any of these, or ask a specific count like \"how many plants don't have images?\"";
    }

    /**
     * Parses a stats question into {entity, property, state} using only the
     * current message plus (when the message omits something) the previous
     * stats footer for carry-over. Never guesses a number — that always comes
     * from a live query in composeStatsResponse().
     */
    private function parseStatsQuery(string $message, array $history, bool $isCorrectionRetry = false): array
    {
        $m = mb_strtolower(trim($message));

        // "That doesn't answer my question" etc. has no entity/property of its own —
        // re-derive from the user's OWN previous question instead of parsing the
        // correction phrase itself (which would otherwise resolve to nothing).
        if (!$isCorrectionRetry && $this->isStatsCorrectionPhrase($m)) {
            $prevUserIdx = null;
            for ($i = count($history) - 1; $i >= 0; $i--) {
                if (($history[$i]['role'] ?? '') === 'user') { $prevUserIdx = $i; break; }
            }
            if ($prevUserIdx !== null) {
                $prevMessage = trim($history[$prevUserIdx]['content'] ?? '');
                if ($prevMessage !== '') {
                    $priorHistory = array_slice($history, 0, $prevUserIdx);
                    return $this->parseStatsQuery($prevMessage, $priorHistory, true);
                }
            }
        }

        $priorStats    = $this->lastStatsFooterContext($history);
        $entity        = $this->detectStatsEntity($m);
        $explicitTotal = $this->isExplicitTotalPhrase($m);

        $property   = null;
        $state      = null;
        $notTracked = false;

        // Detection always runs against the message itself — even an "explicit
        // total" phrase like "are there" can appear alongside a real condition in
        // a combined question ("how many plants are there, how many don't have
        // images..."), and that condition must still be picked up.
        $property = $this->detectStatsProperty($m);
        if ($property !== null) {
            $state = $this->detectStatsState($m);
        } elseif (preg_match('/\bvideos?\b/u', $m)) {
            $notTracked = true;
            $property   = 'video';
        }

        // Property/state carry-over — only when THIS message names no property of
        // its own AND isn't an explicit "just the total" request (which must always
        // reset to unconditional rather than silently inheriting a stale condition
        // from an earlier turn, e.g. "How many plants are there?" after a percentage
        // conversation about something else entirely).
        if ($property === null && !$explicitTotal && $priorStats && ($priorStats['property'] ?? null) !== null) {
            $property = $priorStats['property'];
            $state    = $priorStats['state'];
        }

        // Entity carry-over — only when this message names no entity of its own.
        if ($entity === null && $priorStats && ($priorStats['entity'] ?? null) !== null) {
            $entity = $priorStats['entity'];
        }

        // Operation resolution — an explicit ask in THIS message always wins
        // ("percentage" vs "how many" vs "fetch/list/show/get/find"); otherwise
        // carry over from the footer (e.g. "What about animals?" after a
        // percentage question stays a percentage question); default to a plain
        // count. FETCH is checked via isFetchOperationPhrase() rather than
        // detectStatsOperation() (kept percentage-only) so the broader fetch verb
        // set here can't leak into looksLikeStatsFollowUp()'s unrelated use of
        // detectStatsOperation() and start hijacking topic changes mid-conversation.
        if ($this->detectStatsOperation($m) === 'percentage') {
            $operation = 'percentage';
        } elseif ($this->isFetchOperationPhrase($m)) {
            $operation = 'fetch';
        } elseif (preg_match('/\bhow many\b/u', $m)) {
            $operation = 'count';
        } else {
            $operation = $priorStats['operation'] ?? 'count';
        }
        // A percentage of an unconditional total isn't a meaningful request —
        // never surface it unless a real property/state condition is resolved.
        // FETCH, unlike percentage, is meaningful even with no condition ("fetch
        // the plants" — just paginate the whole table), so it's exempt here.
        if (($property === null || $state === null) && $operation === 'percentage') {
            $operation = 'count';
        }

        $clarify = false;
        if ($entity === null) {
            $clarify = true;
        } elseif ($property !== null && $state === null && !$notTracked) {
            // A property was named but present-vs-missing genuinely can't be told.
            $clarify = true;
        }

        // FETCH pagination — an explicit "page N" always wins; otherwise "next
        // page"/"more" advances one page past whatever FETCH page we were last
        // on (only meaningful mid a FETCH conversation); anything else starts
        // back at page 1. Never derived from anything but the message/footer —
        // same "no guessing" rule as entity/property/state.
        $page = 1;
        if (preg_match('/\bpage\s*(\d+)\b/u', $m, $pageMatch)) {
            $page = max(1, (int) $pageMatch[1]);
        } elseif (preg_match('/\b(next page|next\s+\d*\s*(?:results?|records?)?|more results|see more|show more)\b/u', $m)
            && $priorStats && ($priorStats['operation'] ?? null) === 'fetch') {
            $page = (int) ($priorStats['page'] ?? 1) + 1;
        }

        return [
            'entity'     => $entity,
            'property'   => $property,
            'state'      => $state,
            'operation'  => $operation,
            'notTracked' => $notTracked,
            'clarify'    => $clarify,
            'page'       => $page,
        ];
    }

    private function detectStatsOperation(string $m): ?string
    {
        if (preg_match('/\b(percentages?|percent|%|proportions?|fractions?)\b/u', $m)) {
            return 'percentage';
        }
        return null;
    }

    /**
     * "fetch"/"retrieve" are unambiguous archive-fetch verbs — always FETCH once
     * a recognized entity is named. "list"/"show"/"get"/"find" are heavily
     * overloaded elsewhere (e.g. "show me a proverb about X" record browsing),
     * so they only resolve to FETCH here when paired with an explicit
     * present/missing condition — the documented FETCH use case — leaving their
     * existing unfiltered behavior (INTENT_PLANT, INTENT_PROVERB, etc.) intact.
     */
    private function isFetchOperationPhrase(string $m): bool
    {
        if (preg_match('/\b(fetch|retrieve)\b/u', $m)) {
            return true;
        }
        return (bool) preg_match('/\b(list|show|get|find)\b/u', $m)
            && $this->detectStatsProperty($m) !== null
            && $this->detectStatsState($m) !== null;
    }

    /**
     * Gate used by detectIntent() to decide whether a message should enter the
     * stats subsystem at all for a FETCH-style request (mirrors isFetchOperationPhrase()
     * but also requires a recognized entity, since detectIntent() runs before any
     * entity/property resolution has happened).
     */
    private function looksLikeStatsFetchRequest(string $m): bool
    {
        return $this->detectStatsEntity($m) !== null && $this->isFetchOperationPhrase($m);
    }

    private function detectStatsEntity(string $m): ?string
    {
        foreach (self::STATS_ENTITIES as $key => $def) {
            foreach ($def['aliases'] as $alias) {
                if (preg_match('/\b' . preg_quote($alias, '/') . '\b/u', $m)) {
                    return $key;
                }
            }
        }
        return null;
    }

    private function detectStatsProperty(string $m): ?string
    {
        foreach (self::STATS_PROPERTY_ALIASES as $key => $pattern) {
            if (preg_match($pattern, $m)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Negative markers are checked BEFORE positive ones — "have" is a substring
     * of "don't have", so checking positive first would misread "do not have
     * images" as a request for the present-count.
     */
    private function detectStatsState(string $m): ?string
    {
        if (preg_match('/\b(don\'?t|do not|doesn\'?t|does not|haven\'?t|has not|without|no|missing|lack(?:ing)?|aren\'?t|isn\'?t|not)\b/u', $m)) {
            return 'missing';
        }
        if (preg_match('/\b(have|has|with|attached|containing|contain|got|include|includes)\b/u', $m)) {
            return 'present';
        }
        return null;
    }

    private function isStatsCorrectionPhrase(string $m): bool
    {
        return (bool) preg_match(
            '/doesn\'?t answer|does not answer|not what i (?:asked|meant)|that\'?s not (?:right|correct)|that isn\'?t (?:right|correct)|wrong answer|\bincorrect\b|not (?:the |my )?(?:question|answer)/u',
            $m
        );
    }

    /**
     * "How many plants are there?" / "...in the archive?" / "...in total?" — an
     * explicit request for the plain total that must override any conditional
     * property/state carried over from an earlier turn in the same conversation.
     */
    private function isExplicitTotalPhrase(string $m): bool
    {
        return (bool) preg_match('/\b(are there|are in the archive|are documented|do (?:you|we) have|in total|altogether|in all|are archived)\b/u', $m);
    }

    /**
     * Whether a message with no "how many" of its own should still be routed into
     * stats handling, because the previous assistant turn was itself a stats reply.
     */
    private function looksLikeStatsFollowUp(string $message, array $priorStats): bool
    {
        $m = mb_strtolower(trim($message));

        // A clear new-topic request always wins — never trap a genuine topic
        // change inside a stale stats conversation.
        if (preg_match('/^(tell me about|explain|who is|who was|who\'s|what is the meaning of)\b/u', $m)) {
            return false;
        }

        if ($this->isStatsCorrectionPhrase($m)) return true;
        if (preg_match('/\bhow many\b/u', $m)) return true;
        if ($this->detectStatsProperty($m) !== null) return true;
        if ($this->detectStatsOperation($m) !== null) return true;
        if ($this->isFetchOperationPhrase($m)) return true;
        // "next page" / "page 2" only means something mid a FETCH conversation.
        if (($priorStats['operation'] ?? null) === 'fetch'
            && preg_match('/\bpage\s*\d+\b|\b(next page|more results|see more|show more)\b/u', $m)) {
            return true;
        }
        if (preg_match('/\bvideos?\b/u', $m)) return true;
        if ($this->isExplicitTotalPhrase($m)) return true;
        if (preg_match('/^(what about|and what about|how about|what of)\b/u', $m) && $this->detectStatsEntity($m) !== null) return true;

        // Bare/short entity mention — "animals?", "just animals", etc.
        $tokenCount = count(preg_split('/\s+/u', trim($m), -1, PREG_SPLIT_NO_EMPTY));
        if ($tokenCount <= 4 && $this->detectStatsEntity($m) !== null) return true;

        return false;
    }

    /**
     * Walks back to the most recent assistant turn ONLY (not further) so a topic
     * change that ends the stats conversation isn't reopened by an old footer.
     */
    private function lastStatsFooterContext(array $history): ?array
    {
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if (($history[$i]['role'] ?? '') !== 'assistant') continue;
            return $this->parseStatsFooter($history[$i]['content'] ?? '');
        }
        return null;
    }

    private function parseStatsFooter(string $content): ?array
    {
        if (!preg_match('/Archive statistics\s*—\s*([a-z_]+)(?:,\s*property:\s*([a-z_]+),\s*state:\s*(present|missing))?(?:,\s*operation:\s*([a-z_]+))?(?:,\s*page:\s*(\d+))?/iu', $content, $m)) {
            return null;
        }
        $entity = mb_strtolower($m[1]);
        if (!isset(self::STATS_ENTITIES[$entity])) return null;

        return [
            'entity'    => $entity,
            'property'  => (isset($m[2]) && $m[2] !== '') ? mb_strtolower($m[2]) : null,
            'state'     => (isset($m[3]) && $m[3] !== '') ? mb_strtolower($m[3]) : null,
            'operation' => (isset($m[4]) && $m[4] !== '') ? mb_strtolower($m[4]) : 'count',
            'page'      => (isset($m[5]) && $m[5] !== '') ? (int) $m[5] : 1,
        ];
    }

    private function statsFooter(string $entityKey, ?string $propertyKey, ?string $state, string $operation = 'count', int $page = 1): string
    {
        $suffix     = ($propertyKey && $state) ? ", property: {$propertyKey}, state: {$state}" : '';
        $opSuffix   = ($operation !== 'count') ? ", operation: {$operation}" : '';
        $pageSuffix = ($operation === 'fetch') ? ", page: {$page}" : '';
        return "*Source: Archive statistics — {$entityKey}{$suffix}{$opSuffix}{$pageSuffix}*";
    }

    private function statsConditionPhrase(string $propertyKey, string $state): string
    {
        if ($propertyKey === 'published') {
            return $state === 'present' ? 'that are published' : 'that are not yet published';
        }
        if ($propertyKey === 'active') {
            return $state === 'present' ? 'that are active' : 'that are inactive';
        }

        $labels = [
            'image'             => 'an attached image',
            'audio'             => 'an attached audio recording',
            'description'       => 'a description',
            'translation'       => 'a translation',
            'biography'         => 'a biography',
            'example'           => 'an example',
            'meaning'           => 'a recorded deeper meaning',
            'alternate_meaning' => 'a recorded alternate meaning',
        ];
        $label = $labels[$propertyKey] ?? "a recorded {$propertyKey}";
        return $state === 'missing' ? "without {$label}" : "with {$label}";
    }

    /**
     * Verb-clause form of the same condition ("do not have an attached image",
     * "are published") — used for percentage phrasing, where statsConditionPhrase()'s
     * noun-phrase form ("without an attached image") wouldn't fit grammatically.
     */
    private function statsVerbPhrase(string $propertyKey, string $state): string
    {
        if ($propertyKey === 'published') {
            return $state === 'present' ? 'are published' : 'are not yet published';
        }
        if ($propertyKey === 'active') {
            return $state === 'present' ? 'are active' : 'are inactive';
        }

        $labels = [
            'image'             => 'an attached image',
            'audio'             => 'an attached audio recording',
            'description'       => 'a description',
            'translation'       => 'a translation',
            'biography'         => 'a biography',
            'example'           => 'an example',
            'meaning'           => 'a recorded deeper meaning',
            'alternate_meaning' => 'a recorded alternate meaning',
        ];
        $label = $labels[$propertyKey] ?? "a recorded {$propertyKey}";
        return $state === 'missing' ? "do not have {$label}" : "have {$label}";
    }

    private function statsClarificationResponse(array $parsed): string
    {
        if (($parsed['entity'] ?? null) === null) {
            return 'Which part of the archive would you like me to count — plants, animals, foods, festivals, names, '
                . 'proverbs, dictionary words, historical figures, timeline events, grammar rules, or Bible verses?';
        }
        $entityDef = self::STATS_ENTITIES[$parsed['entity']];
        $propLabel = $parsed['property'] ?? 'that';
        return "Do you mean {$entityDef['plural']} that have no {$propLabel} attached to their database record, or {$entityDef['plural']} that do?";
    }

    /**
     * Table/column names are ALWAYS taken from STATS_ENTITIES/STATS_PROPERTIES
     * (never from user input), so this is safe to interpolate directly.
     */
    private function statsCountQuery(string $table, ?string $baseWhere, array $propertyDef, string $state): string
    {
        $cond  = $this->statsStateCondition($propertyDef, $state);
        $where = $baseWhere ? [$baseWhere, $cond] : [$cond];
        return "SELECT COUNT(*) FROM {$table} WHERE " . implode(' AND ', $where);
    }

    /**
     * The present/missing SQL fragment for one property — the single source of
     * truth for "present" vs "missing" shared by COUNT, PERCENTAGE, and FETCH so
     * the three operations can never disagree on what "missing" means.
     */
    private function statsStateCondition(array $propertyDef, string $state): string
    {
        $col  = $propertyDef['column'];
        $mode = $propertyDef['mode'] ?? 'null_empty';

        if ($mode === 'boolean') {
            $trueVal = (string) $propertyDef['true'];
            $trueLit = is_numeric($trueVal) ? $trueVal : ("'" . addslashes($trueVal) . "'");
            return $state === 'present' ? "{$col} = {$trueLit}" : "{$col} != {$trueLit}";
        }
        if ($mode === 'json_empty') {
            return $state === 'missing'
                ? "({$col} IS NULL OR {$col} = '' OR {$col} = '[]')"
                : "({$col} IS NOT NULL AND {$col} != '' AND {$col} != '[]')";
        }
        // null_empty
        return $state === 'missing'
            ? "({$col} IS NULL OR {$col} = '')"
            : "({$col} IS NOT NULL AND {$col} != '')";
    }

    /**
     * FETCH — retrieves the actual paginated records for entity[/property/state],
     * reusing the exact same table/where/condition resolution as COUNT/PERCENTAGE.
     * Every record comes straight from a LIMIT/OFFSET query; nothing is invented
     * and nothing is paginated in PHP.
     */
    private function composeFetchResponse(
        string  $entityKey,
        array   $entityDef,
        string  $table,
        ?string $baseWhere,
        ?string $propertyKey,
        ?array  $propertyDef,
        ?string $state,
        int     $page,
        int     $unconditionalTotal
    ): string {
        $whereSql = $baseWhere;
        if ($propertyDef !== null && $state !== null) {
            $cond     = $this->statsStateCondition($propertyDef, $state);
            $whereSql = $whereSql ? "{$whereSql} AND {$cond}" : $cond;
        }

        if ($whereSql === $baseWhere) {
            $total = $unconditionalTotal;
        } else {
            $countSql = "SELECT COUNT(*) FROM {$table}" . ($whereSql ? " WHERE {$whereSql}" : '');
            try {
                $total = (int) $this->db->query($countSql)->fetchColumn();
            } catch (\Throwable $e) {
                error_log('ArchiveIntelligence fetch count query failed: ' . $e->getMessage());
                return "I couldn't reach the archive database just now — please try again in a moment.";
            }
        }

        $conditionPhrase = ($propertyKey && $state) ? ' ' . $this->statsConditionPhrase($propertyKey, $state) : '';

        if ($total === 0) {
            return "I couldn't find any {$entityDef['plural']}{$conditionPhrase} matching that condition."
                . "\n\n" . $this->statsFooter($entityKey, $propertyKey, $state, 'fetch', 1);
        }

        $perPage    = self::STATS_FETCH_DEFAULT_PER_PAGE;
        $totalPages = (int) ceil($total / $perPage);
        if ($page > $totalPages) $page = $totalPages;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $perPage;

        $columns = $this->fetchColumnsFor($entityKey);
        $colSql  = implode(', ', $columns);
        $sql     = "SELECT {$colSql} FROM {$table}" . ($whereSql ? " WHERE {$whereSql}" : '')
            . " ORDER BY id ASC LIMIT {$perPage} OFFSET {$offset}";
        try {
            $rows = $this->db->query($sql)->fetchAll();
        } catch (\Throwable $e) {
            error_log('ArchiveIntelligence fetch records query failed: ' . $e->getMessage());
            return "I couldn't reach the archive database just now — please try again in a moment.";
        }

        $lines   = ["I found **" . number_format($total) . "** {$entityDef['plural']}{$conditionPhrase}.", ''];
        $start   = $offset + 1;
        $end     = $offset + count($rows);
        $lines[] = "Showing {$start}–{$end} of " . number_format($total) . ".";
        $lines[] = '';

        $n = $start;
        foreach ($rows as $row) {
            $lines[] = $this->formatFetchRecord($n, $entityKey, $row);
            $lines[] = '';
            $n++;
        }

        $lines[] = "Page {$page} of {$totalPages}.";
        if ($page < $totalPages) {
            $lines[] = "*Say \"next page\" to see more.*";
        }

        return implode("\n", $lines) . "\n\n" . $this->statsFooter($entityKey, $propertyKey, $state, 'fetch', $page);
    }

    /** Allow-listed SELECT columns for FETCH — never SELECT *, only known real columns. */
    private function fetchColumnsFor(string $entityKey): array
    {
        $def = self::STATS_FETCH_FIELDS[$entityKey] ?? null;
        if ($def === null) return ['id'];

        $cols = ['id', $def['title']];
        foreach ($def['fields'] as $col) {
            if ($col) $cols[] = $col;
        }
        if (!empty($def['image_column'])) $cols[] = $def['image_column'];
        if ($entityKey === 'bible') { $cols[] = 'chapter'; $cols[] = 'verse'; }

        return array_values(array_unique($cols));
    }

    /** Renders one FETCH record as a numbered, labelled block — never a fabricated field. */
    private function formatFetchRecord(int $n, string $entityKey, array $row): string
    {
        $id = (int) ($row['id'] ?? 0);

        $def = self::STATS_FETCH_FIELDS[$entityKey] ?? null;
        if ($def === null) {
            return "**{$n}.** Record #" . ($id ?: '?');
        }

        $title = (string) ($row[$def['title']] ?? '');
        if ($entityKey === 'bible') {
            $title = trim(($row['book'] ?? '') . ' ' . ($row['chapter'] ?? '') . ':' . ($row['verse'] ?? ''));
        }

        // The leading number is bolded ("**N.**"), never a bare "N. " at line
        // start — the chat widget's markdown renderer (views/partials/charymeld.php
        // formatText()) turns a bare "N. text" line into an orphan <li> with a
        // CSS list-style:decimal counter that resets to 1 on every record once
        // separated by paragraph breaks (which FETCH always does, one blank line
        // per record). Bolding it renders as plain text instead, sidestepping
        // that bug entirely — the same convention composeProverbResponse() already
        // relies on for its own numbered list.
        $lines = ["**{$n}.** **{$title}**"];
        if ($id) $lines[] = "   ID: {$id}";
        foreach ($def['fields'] as $label => $col) {
            $val = trim((string) ($row[$col] ?? ''));
            if ($val === '') continue;
            if (mb_strlen($val) > 160) $val = mb_substr($val, 0, 160) . '…';
            $lines[] = "   {$label}: {$val}";
        }

        if (!empty($def['image_column'])) {
            $hasImage = trim((string) ($row[$def['image_column']] ?? '')) !== '';
            $lines[]  = '   Image: ' . ($hasImage ? 'Available' : 'Missing');
        }

        $url = $id ? $this->urlForTable(self::STATS_ENTITIES[$entityKey]['table'], $id) : '';
        if ($url) $lines[] = "   [View →]({$url})";

        return implode("\n", $lines);
    }

    private function composeTranslationHelp(string $message, array $rows): string
    {
        $reply = "The Tiv translation tool is available at the [Translate page](" . url('translate') . "). ";
        $reply .= "It searches the dictionary, curated phrases, grammar rules, and the Bible for the most accurate Tiv translations.\n\n";

        if (!empty($rows)) {
            $r = $rows[0];
            $tiv  = $r['tiv_word'] ?? $r['tiv_name'] ?? $r['primary_text'] ?? '';
            $eng  = $r['english_meaning'] ?? $r['secondary_text'] ?? '';
            if ($tiv && $eng) {
                $reply .= "I also found a relevant entry: **{$tiv}** — {$eng}";
            }
        }

        return $reply;
    }

    private function composeGeneralResponse(array $primary, array $extras, array $related, string $subject): string
    {
        if (empty($primary)) return $this->notFoundResponse($subject, '');

        // Fetch actual source records so we have clean structured fields,
        // not raw concatenated index text.
        $fetched = $this->fetchSourceRecords($primary);

        if (empty($fetched)) return $this->notFoundResponse($subject, '');

        // Group by content type
        $groups = [];
        foreach ($fetched as $r) {
            $type = $r['_content_type'] ?? 'general';
            $groups[$type][] = $r;
        }

        // If a single content type dominates, route to its specialized composer
        if (count($groups) === 1) {
            $type = array_key_first($groups);
            $rows = $groups[$type];
            return match($type) {
                'word'      => $this->composeWordResponse($rows, $subject, $related),
                'name'      => $this->composeNameResponse($rows, $subject, $related),
                'proverb'   => $this->composeProverbResponse($rows, $subject),
                'food'      => $this->composeFoodResponse($rows, $subject, $related),
                'plant'     => $this->composePlantResponse($rows, $subject, $related),
                'festival'  => $this->composeFestivalResponse($rows, $subject, $related),
                'animal'    => $this->composeAnimalResponse($rows, $subject, $related),
                'historical_figure' => $this->composePersonResponse($rows, $subject, $related),
                'grammar'   => $this->composeGrammarResponse($rows, $subject),
                'national'  => $this->composeNationalResponse($rows),
                default     => $this->composeMixedResponse($groups, $subject),
            };
        }

        return $this->composeMixedResponse($groups, $subject);
    }

    /**
     * Fetch the actual source rows for a list of archive_search_index results.
     * Returns rows enriched with '_content_type' for routing.
     */
    private function fetchSourceRecords(array $indexRows): array
    {
        $fetched = [];
        $tableMap = [
            'word'       => ['table' => 'daily_words',   'type' => 'word'],
            'name'       => ['table' => 'tiv_names',     'type' => 'name'],
            'proverb'    => ['table' => 'tiv_proverbs',  'type' => 'proverb'],
            'food'       => ['table' => 'tiv_foods',     'type' => 'food'],
            'plant'      => ['table' => 'tiv_plants',    'type' => 'plant'],
            'festival'   => ['table' => 'tiv_festivals', 'type' => 'festival'],
            'animal'     => ['table' => 'tiv_animals',   'type' => 'animal'],
            'phrase'     => ['table' => 'translation_phrases', 'type' => 'phrase'],
            'historical_figure' => ['table' => 'historical_figures', 'type' => 'historical_figure'],
            'timeline_event'    => ['table' => 'timeline_events',    'type' => 'timeline_event'],
            'grammar'           => ['table' => 'tiv_grammar_rules',  'type' => 'grammar'],
        ];

        foreach ($indexRows as $r) {
            // If the row already came from a direct table search (has _table), use it as-is
            if (!empty($r['_table'])) {
                $type = $this->guessTypeFromTable($r['_table']);
                $r['_content_type'] = $type;
                $fetched[] = $r;
                continue;
            }

            // Otherwise it's an index row — fetch the source record
            $contentType = $r['content_type'] ?? '';
            $sourceTable = $r['source_table'] ?? null;
            $sourceId    = (int) ($r['source_id'] ?? 0);

            if (!$sourceTable || !$sourceId) continue;

            // Bible verses have their own formatting
            if ($contentType === 'bible') {
                $stmt = $this->db->prepare(
                    "SELECT *, 'bible_verses' AS _table FROM bible_verses WHERE id = ? LIMIT 1"
                );
                $stmt->execute([$sourceId]);
                $row = $stmt->fetch();
                if ($row) {
                    $row['_content_type'] = 'bible';
                    $row['_search_index_id'] = (int) ($r['id'] ?? 0) ?: null;
                    $fetched[] = $row;
                }
                continue;
            }

            // Content items (documents, audio, publications, etc.)
            if ($sourceTable === 'content_items') {
                $stmt = $this->db->prepare(
                    "SELECT *, 'content_items' AS _table FROM content_items WHERE id = ? AND status = 'published' LIMIT 1"
                );
                $stmt->execute([$sourceId]);
                $row = $stmt->fetch();
                if ($row) {
                    $row['_content_type'] = $contentType;
                    $row['_url'] = $r['url'] ?? '/content-item/' . $sourceId;
                    $row['_search_index_id'] = (int) ($r['id'] ?? 0) ?: null;
                    $fetched[] = $row;
                }
                continue;
            }

            // Nigeria Heritage records: published only, presented by composeNationalResponse().
            if (in_array($sourceTable, ['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods'], true)) {
                $stmt = $this->db->prepare(
                    "SELECT *, '{$sourceTable}' AS _table FROM {$sourceTable} WHERE id = ? AND review_status = 'published' LIMIT 1"
                );
                $stmt->execute([$sourceId]);
                if ($row = $stmt->fetch()) {
                    $row['_content_type'] = 'national';
                    $row['_search_index_id'] = (int) ($r['id'] ?? 0) ?: null;
                    $fetched[] = $row;
                }
                continue;
            }

            // Standard tables via tableMap
            $mapped = $tableMap[$contentType] ?? null;
            if (!$mapped) continue;

            $tbl = $mapped['table'];
            $stmt = $this->db->prepare(
                "SELECT *, '{$tbl}' AS _table FROM {$tbl} WHERE id = ? LIMIT 1"
            );
            $stmt->execute([$sourceId]);
            $row = $stmt->fetch();
            if ($row) {
                $row['_content_type'] = $contentType;
                // Tags this row as having arrived via the lexical-expansion/embedding
                // fallback tiers (resolved from an archive_search_index row) rather than
                // a direct, already-precision-matched search{Type}() call — the signal
                // filterEvidenceByAnswerRelevance() uses to decide what's even eligible
                // for an answer-relevance check.
                $row['_search_index_id'] = (int) ($r['id'] ?? 0) ?: null;
                $fetched[] = $row;
            }
        }

        return $fetched;
    }

    /**
     * The route segment for a source table's detail page — NOT the same as
     * guessTypeFromTable()'s content-type label (e.g. 'historical_figures' is
     * type 'historical_figure' but the route is 'historical-figure/{id}').
     */
    private function urlForTable(string $table, int $id): string
    {
        return match($table) {
            'daily_words'         => url('word/' . $id),
            'tiv_names'           => url('name/' . $id),
            'tiv_proverbs'        => url('proverb/' . $id),
            'tiv_foods'           => url('food/' . $id),
            'tiv_plants'          => url('plant/' . $id),
            'tiv_festivals'       => url('festival/' . $id),
            'tiv_animals'         => url('animal/' . $id),
            'bible_verses'        => url('bible'),
            'historical_figures'  => url('historical-figure/' . $id),
            'timeline_events'     => url('timeline-event/' . $id),
            'tiv_grammar_rules'   => url('language/grammar'),
            'content_items'       => url('content-item/' . $id),
            'translation_phrases' => url('translate'),
            'tiv_alphabet'        => url('language/grammar'), // no dedicated public alphabet page exists yet
            'learning_videos'     => url('learn/' . $id),
            default               => $this->nationalUrl($table, $id),
        };
    }

    private function nationalUrl(string $table, int $id): string
    {
        if (!isset(HeritagePublic::SECTIONS[$table])) return '';
        $rec = HeritagePublic::linkedRecord($table, $id);
        return $rec['url'] ?? '';
    }

    private function guessTypeFromTable(string $table): string
    {
        return match($table) {
            'daily_words'   => 'word',
            'tiv_names'     => 'name',
            'tiv_proverbs'  => 'proverb',
            'tiv_foods'     => 'food',
            'tiv_plants'    => 'plant',
            'tiv_festivals' => 'festival',
            'tiv_animals'   => 'animal',
            'bible_verses'  => 'bible',
            'historical_figures' => 'historical_figure',
            'timeline_events'    => 'timeline_event',
            'tiv_grammar_rules'  => 'grammar',
            'content_items'      => 'article',
            'translation_phrases' => 'phrase',
            'tiv_alphabet'        => 'alphabet', // own type: has 'letter', not 'title' — reusing 'grammar' would fail evidenceSnippet()'s title lookup
            'learning_videos'     => 'video',
            'admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods' => 'national',
            default         => 'general',
        };
    }

    /**
     * Format a mixed multi-type result set, grouped by content type with section headers.
     */
    /** Nigeria Heritage records: short cited descriptions (see HeritageGraph::describe). */
    private function composeNationalResponse(array $rows): string
    {
        $graph = new HeritageGraph($this->db);
        return implode("\n\n", array_map(fn($r) => $graph->describe($r['_table'], $r), array_slice($rows, 0, 2)));
    }

    private function composeMixedResponse(array $groups, string $subject): string
    {
        $sections = [];

        foreach ($groups as $type => $rows) {
            $header = match($type) {
                'word'      => '📖 Dictionary',
                'name'      => '👤 Names',
                'proverb'   => '📜 Proverbs',
                'food'      => '🍽️ Foods',
                'plant'     => '🌿 Plants',
                'festival'  => '🎉 Festivals',
                'animal'    => '🐾 Animals',
                'bible'     => '📖 Bible',
                'document'  => '📄 Documents',
                'audio'     => '🎵 Audio',
                'publication' => '📚 Publications',
                'historical_figure' => '👤 Historical Figures',
                'timeline_event'    => '🕰️ Timeline',
                'grammar'           => '📐 Grammar',
                'article'           => '📰 Articles',
                'alphabet'          => '🔤 Alphabet',
                'phrase'            => '💬 Phrases',
                'video'             => '🎬 Learning Videos',
                'national'          => '🇳🇬 Nigeria Heritage',
                default     => '📌 Archive',
            };

            $lines = [];
            foreach (array_slice($rows, 0, 2) as $r) {
                $line = $this->formatRowAsLine($r, $type);
                if ($line) $lines[] = $line;
            }

            if (!empty($lines)) {
                $sections[] = "**{$header}**\n" . implode("\n", $lines);
            }
        }

        if (empty($sections)) return $this->notFoundResponse($subject, '');

        return implode("\n\n", $sections);
    }

    /**
     * Format a single source record as a compact response line.
     */
    private function formatRowAsLine(array $r, string $type): string
    {
        // Skip rows with no usable display content
        if ($type === 'national') {
            return '• [' . $r['name'] . '](' . HeritagePublic::recordUrl($r['_table'], $r) . ')'
                . (!empty($r['summary']) ? ' — ' . mb_substr($r['summary'], 0, 160) : '');
        }
        $primaryDisplay = $r['english_name'] ?? $r['tiv_word'] ?? $r['tiv_name'] ?? $r['tiv_text']
            ?? $r['title'] ?? $r['letter'] ?? $r['source_text'] ?? $r['primary_text'] ?? '';
        if (trim($primaryDisplay) === '') return '';

        // extractEntityRefFromLastAssistantTurn() requires a 2-80 char link label to
        // recover which record a reply was about on the next turn — a bare "→" (used
        // below until this fix) never matched, silently breaking follow-up entity
        // tracking after every mixed-category response. Reuse $primaryDisplay (already
        // guaranteed non-empty above) as the label; only 'alphabet' rows (a single
        // letter) can be too short, hence the fallback.
        $linkLabel = mb_strlen(trim($primaryDisplay)) >= 2
            ? mb_substr(trim($primaryDisplay), 0, 60)
            : 'View entry';

        $url = isset($r['id']) ? match($type) {
            'word'     => url('word/'     . $r['id']),
            'name'     => url('name/'     . $r['id']),
            'proverb'  => url('proverb/'  . $r['id']),
            'food'     => url('food/'     . $r['id']),
            'plant'    => url('plant/'    . $r['id']),
            'festival' => url('festival/' . $r['id']),
            'animal'   => url('animal/'   . $r['id']),
            'historical_figure' => url('historical-figure/' . $r['id']),
            'timeline_event'    => url('timeline-event/'    . $r['id']),
            'grammar'           => url('language/grammar'),
            'article'           => url('content-item/' . $r['id']),
            'alphabet'          => url('language/grammar'),
            'phrase'            => url('translate'),
            'video'             => url('learn/' . $r['id']),
            default    => $r['_url'] ?? '',
        } : ($r['_url'] ?? '');

        return match($type) {
            'word' => sprintf(
                "• **%s**%s — %s%s%s",
                $r['tiv_word'] ?? '',
                !empty($r['pronunciation']) ? ' (/' . $r['pronunciation'] . '/)' : '',
                $r['english_meaning'] ?? '',
                !empty($r['part_of_speech']) ? ' [' . $r['part_of_speech'] . ']' : '',
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'name' => sprintf(
                "• **%s**%s — *%s*%s",
                $r['tiv_name'] ?? '',
                !empty($r['gender']) ? ' (' . $r['gender'] . ')' : '',
                $r['english_meaning'] ?? '',
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'proverb' => sprintf(
                "• *\"%s\"* — %s%s",
                mb_substr($r['tiv_text'] ?? '', 0, 70),
                mb_substr($r['english_translation'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'food', 'plant', 'festival', 'animal' => sprintf(
                "• **%s** (%s) — %s%s",
                $r['tiv_name'] ?? $r['name'] ?? '',
                $r['english_name'] ?? $r['name'] ?? '',
                mb_substr($r['description'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'bible' => sprintf(
                "• *%s %s:%s* — \"%s\"%s",
                $r['book'] ?? '', $r['chapter'] ?? '', $r['verse'] ?? '',
                mb_substr($r['english_web'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'historical_figure' => sprintf(
                "• **%s** — %s%s",
                $r['english_name'] ?? '',
                mb_substr($r['short_summary'] ?? $r['biography'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'timeline_event' => sprintf(
                "• **%s**%s — %s%s",
                $r['title'] ?? '',
                !empty($r['year']) ? ' (' . $r['year'] . ')' : '',
                mb_substr($r['short_summary'] ?? $r['description'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'grammar' => sprintf(
                "• **%s** — %s%s",
                $r['title'] ?? '',
                mb_substr($r['summary'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'alphabet' => sprintf(
                "• **%s** — %s%s",
                $r['letter'] ?? '',
                mb_substr($r['sound_desc'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'phrase' => sprintf(
                "• **%s** — *%s*%s",
                $r['source_text'] ?? '',
                mb_substr($r['target_text'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            'video' => sprintf(
                "• **%s** — %s%s",
                $r['title'] ?? '',
                mb_substr($r['description'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
            default => sprintf(
                "• **%s** — %s%s",
                mb_substr($r['title'] ?? $r['tiv_name'] ?? '', 0, 60),
                mb_substr($r['excerpt'] ?? $r['description'] ?? '', 0, 80),
                $url ? "  [{$linkLabel}]({$url})" : ''
            ),
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Compare response
    // ─────────────────────────────────────────────────────────────

    private function composeCompareResponse(array $subjects, string $fallbackSubject): string
    {
        if (count($subjects) < 2) {
            return "To compare two things, try: *\"Compare Ter and Aondo\"* or *\"Difference between Kwagh-hir and Icongo.\"*";
        }

        [$subjectA, $subjectB] = $subjects;

        // Search for each subject across all tables
        $resultsA = $this->searchAllTables($subjectA, 2);
        $resultsB = $this->searchAllTables($subjectB, 2);

        if (empty($resultsA) && empty($resultsB)) {
            return "I couldn't find archive entries for **{$subjectA}** or **{$subjectB}**. Try contributing these to the archive at [Contribute](" . url('contribute') . ").";
        }

        $response = "**Comparing {$subjectA} and {$subjectB}**\n\n";

        if (!empty($resultsA)) {
            $response .= "**{$subjectA}**\n";
            foreach (array_slice($this->fetchSourceRecords($resultsA), 0, 1) as $r) {
                $response .= $this->formatRowAsLine($r, $r['_content_type'] ?? 'general') . "\n";
                if (!empty($r['description'])) $response .= "  " . mb_substr($r['description'], 0, 150) . "\n";
            }
            $response .= "\n";
        } else {
            $response .= "**{$subjectA}** — not yet in the archive.\n\n";
        }

        if (!empty($resultsB)) {
            $response .= "**{$subjectB}**\n";
            foreach (array_slice($this->fetchSourceRecords($resultsB), 0, 1) as $r) {
                $response .= $this->formatRowAsLine($r, $r['_content_type'] ?? 'general') . "\n";
                if (!empty($r['description'])) $response .= "  " . mb_substr($r['description'], 0, 150) . "\n";
            }
        } else {
            $response .= "**{$subjectB}** — not yet in the archive.\n";
        }

        $response .= "\n" . $this->buildSourceCitations(array_merge($resultsA, $resultsB));
        return $response;
    }

    // ─────────────────────────────────────────────────────────────
    // Summarize response (full detail)
    // ─────────────────────────────────────────────────────────────

    private function composeSummaryResponse(array $primary, string $subject, array $related): string
    {
        if (empty($primary)) return $this->notFoundResponse($subject, '');

        $fetched = $this->fetchSourceRecords($primary);
        if (empty($fetched)) return $this->notFoundResponse($subject, '');

        $r    = $fetched[0];
        $type = $r['_content_type'] ?? 'general';

        $response = "**Summary: {$subject}**\n\n";

        // Build a full detailed narrative by pulling all available fields
        $sections = [];

        // Title / name
        $primaryLabel = $r['tiv_name'] ?? $r['tiv_word'] ?? $r['tiv_text'] ?? $r['title'] ?? $subject;
        $englishLabel = $r['english_meaning'] ?? $r['english_name'] ?? $r['english_translation'] ?? $r['name'] ?? '';

        if ($primaryLabel) {
            $header = "**{$primaryLabel}**";
            if ($englishLabel) $header .= " ({$englishLabel})";
            $sections[] = $header;
        }

        // Core content fields
        foreach (['description','content','deeper_meaning','cultural_significance','significance',
                  'medicinal_uses','ritual_uses','cultural_use','activities','preparation_method',
                  'ingredients','origin_story','usage_context'] as $field) {
            if (!empty($r[$field])) {
                $label = match($field) {
                    'description'          => 'Description',
                    'content'              => 'Details',
                    'deeper_meaning'       => 'Deeper Meaning',
                    'cultural_significance','significance' => 'Cultural Significance',
                    'medicinal_uses'       => 'Medicinal Uses',
                    'ritual_uses'          => 'Ritual Uses',
                    'cultural_use'         => 'Cultural Role',
                    'activities'           => 'Activities',
                    'preparation_method'   => 'Preparation',
                    'ingredients'          => 'Ingredients',
                    'origin_story'         => 'Origin',
                    'usage_context'        => 'Usage',
                    default                => ucfirst(str_replace('_', ' ', $field)),
                };
                $sections[] = "**{$label}:** " . mb_substr($r[$field], 0, 300) . (mb_strlen($r[$field]) > 300 ? '…' : '');
            }
        }

        // Dictionary-specific fields
        if (!empty($r['pronunciation']))   $sections[] = "**Pronunciation:** /{$r['pronunciation']}/";
        if (!empty($r['ipa']))             $sections[] = "**IPA:** [{$r['ipa']}]";
        if (!empty($r['part_of_speech'])) $sections[] = "**Part of Speech:** {$r['part_of_speech']}";
        if (!empty($r['alternate_meaning'])) $sections[] = "**Also means:** {$r['alternate_meaning']}";
        if (!empty($r['category']) && $r['category'] !== $r['part_of_speech'] ?? '')
            $sections[] = "**Category:** {$r['category']}";
        if (!empty($r['example_tiv']))    $sections[] = "**Example:** *{$r['example_tiv']}*"
            . (!empty($r['example_english']) ? " — {$r['example_english']}" : '');
        if (!empty($r['related_words']))  $sections[] = "**Related words:** {$r['related_words']}";

        // Other content type fields
        if (!empty($r['timing']))          $sections[] = "**When:** {$r['timing']}";
        if (!empty($r['scientific_name'])) $sections[] = "**Scientific name:** *{$r['scientific_name']}*";
        if (!empty($r['gender']))          $sections[] = "**Gender:** {$r['gender']}";
        if (!empty($r['animal_type']))     $sections[] = "**Type:** {$r['animal_type']}";

        // If only very basic data is available, say so honestly
        if (count($sections) <= 2) {
            $sections[] = "*The archive entry for this word is currently minimal. "
                . "Additional details such as usage examples, cultural context, and related words "
                . "can be added by community members at the [Archive](" . url('contribute') . ").*";
        }

        $response .= implode("\n\n", $sections);

        // Related entries
        if (!empty($related)) {
            $response .= "\n\n**Related in the archive:**\n";
            foreach (array_slice($related, 0, 4) as $rel) {
                $label = mb_substr($rel['primary_text'] ?? '', 0, 50);
                $url   = $rel['url'] ?? '#';
                $rtype = $this->typeLabel($rel['content_type'] ?? '');
                $response .= "• [{$label}]({$url}) ({$rtype})\n";
            }
        }

        $response .= "\n" . $this->buildSourceCitations($primary);
        return $response;
    }

    // ─────────────────────────────────────────────────────────────
    // Source citations (appended to every response)
    // ─────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────
    // Contextual follow-up question generator
    // ─────────────────────────────────────────────────────────────

    /**
     * Generate a contextual follow-up question that invites the user to
     * keep exploring. The question is specific to the content type and
     * based on what fields are present vs missing in the top result.
     */
    private function generateFollowUpQuestion(array $rows, string $type, string $subject = ''): string
    {
        if (empty($rows)) return '';

        $r = $rows[0];

        // Build a clean short display name.
        // For multi-word entries where tiv_name is a full sentence (festival/plant descriptions),
        // prefer the english_name or fall back to the subject term.
        $tivName = trim($r['tiv_word'] ?? $r['tiv_name'] ?? $r['tiv_text'] ?? $r['title'] ?? '');
        $engName = trim($r['english_name'] ?? $r['english_meaning'] ?? $r['english_translation'] ?? '');

        if ($type === 'historical_figure' && !empty($r['english_name'])) {
            $name = $r['english_name']; // 'title' is an honorific, not the person's name — never use it here
        } elseif ($tivName !== '' && mb_strlen($tivName) <= 35 && mb_substr_count($tivName, ' ') <= 3) {
            $name = $tivName; // short tiv name — use as-is
        } elseif ($engName !== '' && mb_strlen($engName) <= 40) {
            $name = $engName; // english name is clean
        } else {
            $name = mb_substr($tivName ?: $subject, 0, 35); // truncate as last resort
        }

        switch ($type) {
            case 'word':
                $hasExample = !empty($r['example_tiv']);
                $hasAlt     = !empty($r['alternate_meaning']);
                if (!$hasExample && !$hasAlt) {
                    return "\n\n*Would you like to know how to use **{$name}** in a sentence, or find related Tiv words?*";
                }
                if (!$hasExample) {
                    return "\n\n*Would you like to hear **{$name}** used in an example sentence, or explore its alternate meanings?*";
                }
                return "\n\n*Would you like to explore words related to **{$name}**, or hear a proverb that uses this concept?*";

            case 'name':
                $hasGender = !empty($r['gender']);
                $hasOrigin = !empty($r['origin_story']);
                if (!$hasOrigin) {
                    return "\n\n*Would you like to find other Tiv names with similar meanings"
                        . ($hasGender ? '' : ', or know whether this is a male or female name')
                        . "?*";
                }
                return "\n\n*Would you like to explore other Tiv names related to **{$name}**, or hear a proverb connected to this theme?*";

            case 'proverb':
                $hasDeeperMeaning = !empty($r['deeper_meaning'] ?? $r['secondary_text'] ?? '');
                if (!$hasDeeperMeaning) {
                    return "\n\n*Would you like to know the deeper cultural meaning of this proverb, or hear another proverb on a related theme?*";
                }
                return "\n\n*Would you like to explore more proverbs on this theme, or see how this wisdom connects to Tiv traditions?*";

            case 'food':
                $hasPrep = !empty($r['preparation_method']);
                $hasSig  = !empty($r['cultural_significance']);
                if (!$hasPrep && !$hasSig) {
                    return "\n\n*Would you like to know how **{$name}** is prepared, or its cultural significance in Tiv society?*";
                }
                if (!$hasPrep) {
                    return "\n\n*Would you like to know how **{$name}** is traditionally prepared?*";
                }
                return "\n\n*Would you like to explore other traditional Tiv foods, or learn about the festivals where **{$name}** is served?*";

            case 'plant':
                $hasMed = !empty($r['medicinal_uses']);
                $hasRit = !empty($r['ritual_uses']);
                if (!$hasMed) {
                    return "\n\n*Would you like to know about the medicinal uses of **{$name}**, or its ritual significance?*";
                }
                if (!$hasRit) {
                    return "\n\n*Does **{$name}** have any ritual or spiritual significance in Tiv culture? Ask me about it.*";
                }
                return "\n\n*Would you like to explore other medicinal plants in the Tiv archive, or learn how **{$name}** is used in traditional medicine?*";

            case 'festival':
                $hasTiming = !empty($r['timing']);
                $hasActs   = !empty($r['activities']);
                if (!$hasTiming) {
                    return "\n\n*Would you like to know when **{$name}** is celebrated, or what activities are involved?*";
                }
                if (!$hasActs) {
                    return "\n\n*Would you like to know what happens during **{$name}**, or which communities celebrate it?*";
                }
                return "\n\n*Would you like to explore other Tiv festivals, or learn about the foods traditionally served at **{$name}**?*";

            case 'animal':
                $hasCult = !empty($r['cultural_use']);
                if (!$hasCult) {
                    return "\n\n*Would you like to know the cultural role of **{$name}** in Tiv society, or explore other animals in the archive?*";
                }
                return "\n\n*Would you like to hear a proverb featuring **{$name}**, or explore other animals in the Tiv archive?*";

            case 'bible':
                return "\n\n*Would you like to read the full passage, or find Tiv words that appear in this verse?*";

            case 'historical_figure':
                $hasLegacy = !empty($r['legacy']);
                if (!$hasLegacy) {
                    return "\n\n*Would you like to know more about **{$name}**'s achievements, or see other historical figures in the archive?*";
                }
                return "\n\n*Would you like to know about **{$name}**'s legacy, or explore other historical figures in the archive?*";

            default:
                return "\n\n*Would you like to know more about this, or explore something else in the Tiv Heritage Archive?*";
        }
    }

    private function buildSourceCitations(array $rows): string
    {
        // Inline [View in archive] links are already present in each response item.
        // A separate Sources footer is redundant — return empty.
        return '';

        if (empty($rows)) return '';

        $seen  = [];
        $lines = [];

        foreach ($rows as $r) {
            // Handle both direct source rows and index rows
            $table = $r['_table'] ?? $r['source_table'] ?? null;
            $id    = (int) ($r['id'] ?? $r['source_id'] ?? 0);
            $type  = $r['_content_type'] ?? $r['content_type'] ?? $this->guessTypeFromTable($table ?? '');
            $url   = $r['_url'] ?? $r['url'] ?? null;

            if (!$table || !$id) continue;
            $key = $table . ':' . $id;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            // Build URL from table and id if not already set
            if (!$url) {
                $url = match($table) {
                    'daily_words'   => url('word/'     . $id),
                    'tiv_names'     => url('name/'     . $id),
                    'tiv_proverbs'  => url('proverb/'  . $id),
                    'tiv_foods'     => url('food/'     . $id),
                    'tiv_plants'    => url('plant/'    . $id),
                    'tiv_festivals' => url('festival/' . $id),
                    'tiv_animals'   => url('animal/'   . $id),
                    'content_items' => url('content-item/' . $id),
                    'learning_videos' => url('learn/' . $id),
                    default         => null,
                };
            }

            $label = match($table) {
                'daily_words'        => 'Dictionary',
                'tiv_names'          => 'Names Archive',
                'tiv_proverbs'       => 'Proverbs Archive',
                'tiv_foods'          => 'Foods Archive',
                'tiv_plants'         => 'Plants Archive',
                'tiv_festivals'      => 'Festivals Archive',
                'tiv_animals'        => 'Animals Archive',
                'bible_verses'       => 'Bible (Icighan Bibilo)',
                'content_items'      => ucfirst($type) . ' Archive',
                'learning_videos'    => 'Learning Videos',
                'translation_phrases'=> 'Translation Phrases',
                'tiv_alphabet'       => 'Tiv Alphabet',
                default              => 'Archive',
            };

            $lines[] = $url ? "[{$label}]({$url})" : $label;
        }

        if (empty($lines)) return '';

        return "\n---\n*Sources: " . implode(' · ', array_unique($lines)) . "*";
    }

    // ─────────────────────────────────────────────────────────────
    // Multi-table search (used by compare)
    // ─────────────────────────────────────────────────────────────

    private function searchAllTables(string $subject, int $limit = 2): array
    {
        // A dedicated record (festival/article/historical figure) is a far more useful
        // side of a comparison than a bare dictionary definition that merely contains
        // the same substring — same reasoning as everywhere else content_items was
        // given priority today. This ordering actually matters here, unlike most other
        // batteries: the final array_slice($limit) keeps only the first $limit items
        // total across ALL types, so whichever type is checked first effectively wins
        // outright — a dictionary word used to always crowd out a real festival match
        // for something like "Kwagh-hir" (which is both a word AND a festival).
        $all = array_merge(
            $this->searchContentItems($subject),
            $this->searchFestivals($subject),
            $this->searchHistoricalFigures($subject),
            $this->searchNames($subject),
            $this->searchProverbs($subject),
            $this->searchFoods($subject),
            $this->searchPlants($subject),
            $this->searchAnimals($subject),
            $this->searchWords($subject)
        );
        return array_slice($all, 0, $limit);
    }

    // ─────────────────────────────────────────────────────────────
    // Special responses
    // ─────────────────────────────────────────────────────────────

    private function acknowledgementResponse(string $lastEntity): string
    {
        $suggestions = [
            "word"     => ["try asking about another Tiv word", "explore a proverb", "look up a Tiv name"],
            "proverb"  => ["hear another proverb", "explore words in this proverb", "look up a related festival or tradition"],
            "name"     => ["explore names with similar meanings", "hear a proverb related to this concept", "look up another Tiv name"],
            "food"     => ["explore other traditional Tiv foods", "look up plants used in Tiv cooking", "learn about a Tiv festival where food is central"],
            "plant"    => ["explore other medicinal plants", "learn about traditional Tiv foods that use this plant", "look up a Tiv festival"],
            "festival" => ["explore other Tiv festivals", "look up foods served at this festival", "hear a proverb related to this tradition"],
            "animal"   => ["explore other Tiv animals", "hear a proverb featuring animals", "look up a Tiv word related to this animal"],
        ];

        // Pick a contextually relevant suggestion set, default to general
        $set = [
            "ask about another Tiv word",
            "explore a proverb or name",
            "look up a traditional food, plant, or festival",
        ];

        $entity = $lastEntity ? "**{$lastEntity}**" : "that";
        $s1 = $set[0];
        $s2 = $set[1];
        $s3 = $set[2];

        $responses = [
            "What else would you like to explore? You can {$s1}, {$s2}, or {$s3}.",
            "Noted! What would you like to know next? Ask me about any Tiv word, name, proverb, food, plant, festival, or animal.",
            "Happy to help further! Is there something specific about Tiv culture or language you'd like to explore next?",
        ];

        return $responses[array_rand($responses)];
    }

    private function greetingResponse(): string
    {
        $greetings = [
            "Msugh u za van! I'm Tiv AI, your guide to the Tiv Heritage Archive. Ask me about Tiv words, names, proverbs, foods, plants, festivals, or anything in our growing archive.",
            "Msugh u za van! I can help you explore Tiv words, cultural traditions, names, proverbs, and more. What would you like to learn today?",
            "Msugh u za van! I'm here to help you discover the richness of Tiv culture and language.",
        ];
        return $greetings[array_rand($greetings)];
    }

    private function helpResponse(): string
    {
        // No **bold** markdown here — extractEntityFromLastAssistantTurn() scans the
        // assistant's last reply for bold text to guess "the entity we were just
        // discussing" on follow-up turns. Bolding a category label (e.g. "Words &
        // translations") made it look like a real archive entity and caused the NEXT,
        // unrelated question to be wrongly treated as a follow-up about that phrase.
        return "I'm Tiv AI. I search the Tiv Heritage Archive and answer directly from what's stored there — I don't chat freely or make things up. Here's what I can do:\n\n"
            . "- Words & translations — Tiv-to-English or English-to-Tiv lookups (\"what does 'msugh' mean?\")\n"
            . "- Names — meanings and origins of Tiv names (\"is Iveren a female name?\")\n"
            . "- Proverbs & sayings — with their meanings (\"proverbs about wisdom\")\n"
            . "- Festivals & ceremonies — like Kwagh-hir (\"tell me about the Kwagh-hir festival\")\n"
            . "- Foods, plants & animals — from the archive's cultural entries\n"
            . "- People & history — notable Tiv figures and historical events\n"
            . "- Grammar — sentence structure, pronouns, possession in Tiv\n"
            . "- Comparisons & archive stats — \"compare X and Y\", \"how many proverbs do you have?\"\n\n"
            . "Just ask naturally, e.g. \"what is 'gbande'?\" or \"tell me about Tiv marriage customs.\"\n\n"
            . "I can't download data or files for you, though — I only answer questions from what's already in the archive.";
    }

    private function listResponse(string $message, array $rows): string
    {
        if (empty($rows)) {
            return "The archive is still growing in this area. You can [contribute content](" . url('contribute') . ") to help build it up!";
        }

        $lines = [];
        foreach (array_slice($rows, 0, 8) as $r) {
            $pText = mb_substr($r['primary_text'] ?? $r['tiv_word'] ?? $r['tiv_name'] ?? '', 0, 60);
            $sText = mb_substr($r['secondary_text'] ?? $r['english_meaning'] ?? $r['english_name'] ?? '', 0, 60);
            $url   = $r['url'] ?? '';
            $lines[] = $url ? "- [{$pText}]({$url})" . ($sText ? " — {$sText}" : '') : "- {$pText}" . ($sText ? " — {$sText}" : '');
        }

        return "Here are some entries from the archive:\n\n" . implode("\n", $lines) . "\n\nSee more in the [Archive](" . url('archive') . ").";
    }

    private function notFoundResponse(string $subject, string $message, array $incidental = []): string
    {
        if (!$subject) {
            return "I couldn't find that. Try asking about a specific Tiv word, name, proverb, food, plant, festival, or animal.";
        }

        // An incidental match means the subject only appears buried inside another
        // record's example sentence or description — never claim it as the answer,
        // but it's honest to mention where the name showed up.
        if (!empty($incidental)) {
            $fetched = $this->fetchSourceRecords([$incidental[0]]);
            if (!empty($fetched)) {
                $other = $fetched[0];
                $otherName = $other['tiv_word'] ?? $other['tiv_name'] ?? $other['english_name'] ?? $other['title'] ?? '';
                $otherType = $this->typeLabel($other['_content_type'] ?? '');
                if ($otherName !== '') {
                    return "I don't have a dedicated record for **{$subject}** in the archive yet. "
                        . "The name does appear inside an example or description in the {$otherType} entry for **{$otherName}**, "
                        . "but that entry is about {$otherName}, not about {$subject}.";
                }
            }
        }

        // Try to find phonetically similar entries (same first 3 chars)
        $similar = $this->findSimilarEntries($subject);
        if (!empty($similar)) {
            $suggestions = implode(', ', array_map(fn($s) => "**{$s}**", array_slice($similar, 0, 3)));
            return "**{$subject}** is not in the archive yet. Did you mean: {$suggestions}?";
        }

        return "**{$subject}** is not in the archive yet.";
    }

    private function findSimilarEntries(string $subject): array
    {
        if (mb_strlen($subject) < 3) return [];
        $prefix = mb_substr(mb_strtolower($subject), 0, 3) . '%';
        try {
            $stmt = $this->db->prepare(
                "SELECT tiv_word FROM daily_words WHERE LOWER(tiv_word) LIKE ? AND is_active=1
                 AND tiv_word != '' LIMIT 4"
            );
            $stmt->execute([$prefix]);
            $words = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt2 = $this->db->prepare(
                "SELECT tiv_name FROM tiv_names WHERE LOWER(tiv_name) LIKE ? LIMIT 2"
            );
            $stmt2->execute([$prefix]);
            $names = $stmt2->fetchAll(PDO::FETCH_COLUMN);

            $stmt3 = $this->db->prepare(
                "SELECT english_name FROM historical_figures
                 WHERE LOWER(english_name) LIKE ? AND status = 'published' LIMIT 2"
            );
            $stmt3->execute([$prefix]);
            $figures = $stmt3->fetchAll(PDO::FETCH_COLUMN);

            $all = array_merge($words, $names, $figures);
            // Filter to only those actually different from the subject
            return array_values(array_filter($all, fn($s) => mb_strtolower($s) !== mb_strtolower($subject)));
        } catch (\PDOException $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Cross-source enrichment
    // ─────────────────────────────────────────────────────────────

    /**
     * Enrich a response by finding mentions of the subject in other content types.
     * Returns an enrichment string or empty string if nothing useful is found.
     */
    private function enrichWithMentions(string $subject, string $primaryType): string
    {
        if (empty(trim($subject))) return '';

        try {
            if ($primaryType === 'word') {
                // Look for proverbs that use this word
                $like = '%' . $subject . '%';
                $stmt = $this->db->prepare(
                    "SELECT tiv_text, english_translation, id FROM tiv_proverbs
                     WHERE tiv_text LIKE ? LIMIT 2"
                );
                $stmt->execute([$like]);
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $lines = [];
                    foreach ($rows as $r) {
                        $tiv = mb_substr($r['tiv_text'] ?? '', 0, 80);
                        $url = isset($r['id']) ? url('proverb/' . $r['id']) : '';
                        $lines[] = $url ? "*\"{$tiv}\"* [→]({$url})" : "*\"{$tiv}\"*";
                    }
                    return "This word also appears in Tiv proverbs:\n" . implode("\n", $lines);
                }
            } elseif ($primaryType === 'name') {
                // Look for dictionary words related to the name's meaning
                // (we don't have the meaning here, so search by the subject itself)
                $like = '%' . $subject . '%';
                $stmt = $this->db->prepare(
                    "SELECT tiv_word, english_meaning, id FROM daily_words
                     WHERE LOWER(english_meaning) LIKE ? AND is_active=1 LIMIT 2"
                );
                $stmt->execute([$like]);
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $words = array_map(fn($r) => "**{$r['tiv_word']}**", $rows);
                    return "Related Tiv words: " . implode(', ', $words) . ".";
                }
            } elseif ($primaryType === 'festival') {
                // Look for foods mentioned in relation to this festival
                $like = '%' . $subject . '%';
                $stmt = $this->db->prepare(
                    "SELECT tiv_name, english_name, id FROM tiv_foods
                     WHERE tiv_name LIKE ? OR english_name LIKE ? LIMIT 2"
                );
                $stmt->execute([$like, $like]);
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $foods = [];
                    foreach ($rows as $r) {
                        $name = $r['tiv_name'] ?? $r['english_name'] ?? '';
                        $url  = isset($r['id']) ? url('food/' . $r['id']) : '';
                        $foods[] = $url ? "[{$name}]({$url})" : $name;
                    }
                    return "Traditional foods associated with this festival include: " . implode(', ', $foods) . ".";
                }
            }
        } catch (\PDOException $e) {
            // Don't let enrichment errors break the main response
        }

        return '';
    }

    // ─────────────────────────────────────────────────────────────
    // Yes/No response composer
    // ─────────────────────────────────────────────────────────────

    private function composeYesNoResponse(string $question, array $rows, string $subject, array $related): string
    {
        if (empty($rows)) {
            return $this->notFoundResponse($subject, $question);
        }

        $q = mb_strtolower(trim($question));

        // For gender questions, prefer rows that actually have the gender field set —
        // but ONLY among rows that are genuinely about the subject asked about. Without
        // that check, "is Aondo a male name?" (Aondo is a dictionary WORD — "God" — with
        // no dedicated name-table entry of its own) would grab the first ANY gendered
        // row from the whole merged word+name result set, even one from an unrelated
        // fuzzy name match ("Teryima") that has nothing to do with Aondo — answering a
        // completely different question than the one asked while sounding confident
        // about it. Same "genuine match, not incidental" discipline as
        // isGenuineHeadwordMatch() elsewhere in this file.
        $r = $rows[0];
        if (preg_match('/\b(male|female|unisex|boy|girl)\b/u', $q)) {
            $subjectLower = mb_strtolower(trim($subject));
            foreach ($rows as $candidate) {
                if (empty($candidate['gender'])) continue;
                $ownName = mb_strtolower($candidate['tiv_name'] ?? $candidate['english_name'] ?? '');
                if ($subjectLower !== '' && $ownName !== '' && mb_strpos($ownName, $subjectLower) === false) {
                    continue; // has a gender, but isn't actually a match for the subject
                }
                $r = $candidate;
                break;
            }
        }

        $name = $r['english_name'] ?? $r['tiv_name'] ?? $r['tiv_word'] ?? $r['tiv_text'] ?? $subject;
        $url  = (isset($r['id'], $r['_table'])) ? $this->urlForTable($r['_table'], (int) $r['id']) : '';

        // Historical figures need person-appropriate phrasing — the generic
        // "X means Y" template below doesn't make sense for a biography record, and
        // the archive doesn't have structured data to verify most yes/no claims about
        // a person, so this stays neutral rather than asserting "yes" or "no".
        if (($r['_table'] ?? '') === 'historical_figures') {
            $summary = $r['short_summary'] ?? mb_substr($r['biography'] ?? '', 0, 150);
            $urlNote = $url ? " [View profile →]({$url})" : '';
            return "**{$name}** is documented in the archive." . ($summary ? " {$summary}" : '') . "{$urlNote}"
                 . $this->generateFollowUpQuestion($rows, 'historical_figure', $subject);
        }

        // Gender question: "is X a male/female name?"
        if (preg_match('/\b(male|female|unisex|boy|girl)\b/u', $q) && !empty($r['gender'])) {
            $gender       = mb_strtolower($r['gender']);
            $askedGender  = '';
            if (preg_match('/\b(male|boy)\b/u', $q))   $askedGender = 'male';
            if (preg_match('/\b(female|girl)\b/u', $q)) $askedGender = 'female';
            if (preg_match('/\bunisex\b/u', $q))        $askedGender = 'unisex';
            $meaning = $r['english_meaning'] ?? $r['english_name'] ?? '';
            if ($askedGender && $gender === $askedGender) {
                return "Yes, **{$name}** is a {$gender} Tiv name" . ($meaning ? " meaning *{$meaning}*" : '') . ".";
            } elseif ($askedGender && $gender !== $askedGender) {
                return "No, **{$name}** is actually a {$gender} Tiv name" . ($meaning ? " meaning *{$meaning}*" : '') . ".";
            }
        }

        // "Does X mean Y?" question
        if (preg_match('/\bdoes\b.+\bmean\b\s+(.+?)\??$/u', $q, $m)) {
            $askedMeaning = trim($m[1], ' "\'?');
            $actualMeaning = mb_strtolower($r['english_meaning'] ?? $r['english_translation'] ?? '');
            if (!empty($actualMeaning)) {
                $lowerAsked = mb_strtolower($askedMeaning);
                if (mb_strpos($actualMeaning, $lowerAsked) !== false) {
                    return "Yes, **{$name}** means *{$askedMeaning}* in Tiv.";
                } else {
                    $actual = $r['english_meaning'] ?? $r['english_translation'] ?? '';
                    return "No, **{$name}** actually means *{$actual}* in Tiv.";
                }
            }
        }

        // Default: frame the most relevant result as a yes/no answer
        $type    = $r['_table'] ?? '';
        $meaning = $r['english_meaning'] ?? $r['english_name'] ?? $r['english_translation'] ?? '';

        if ($meaning) {
            $urlNote = $url ? " [Learn more →]({$url})" : '';
            return "Based on the archive, **{$name}** means *{$meaning}* in Tiv.{$urlNote} Would you like more details?"
                 . $this->generateFollowUpQuestion($rows, $this->guessTypeFromTable($type), $subject);
        }

        return "Based on the archive, **{$name}** is documented in the Tiv Heritage Archive."
             . ($url ? " [View entry →]({$url})" : '')
             . " Would you like more details?";
    }

    // ─────────────────────────────────────────────────────────────
    // Relevance filtering
    // ─────────────────────────────────────────────────────────────

    /**
     * Filter search results to only those that actually contain the subject term.
     * Falls back to unfiltered results if all are filtered out.
     */
    private function filterByRelevance(array $rows, string $subject, string $primaryField): array
    {
        if (empty($subject) || empty($rows)) return $rows;
        $lower = mb_strtolower($subject);
        $filtered = array_values(array_filter($rows, function($r) use ($lower, $primaryField) {
            $val = mb_strtolower(
                $r[$primaryField] ?? $r['tiv_name'] ?? $r['tiv_word'] ?? $r['tiv_text'] ?? ''
            );
            return mb_strpos($val, $lower) !== false || mb_strpos($lower, $val) !== false;
        }));
        return !empty($filtered) ? $filtered : $rows;
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function formatRelatedLinks(array $related): string
    {
        $links = [];
        foreach (array_slice($related, 0, 3) as $r) {
            $label = mb_substr($r['primary_text'] ?? '', 0, 40);
            $url   = $r['url'] ?? '#';
            $type  = $this->typeLabel($r['content_type'] ?? '');
            $links[] = "[{$label}]({$url}) ({$type})";
        }
        return implode(', ', $links);
    }

    private function typeLabel(string $type): string
    {
        return match($type) {
            'word'         => 'word',
            'name'         => 'name',
            'proverb'      => 'proverb',
            'food'         => 'food',
            'plant'        => 'plant',
            'festival'     => 'festival',
            'animal'       => 'animal',
            'bible'        => 'Bible',
            'document'     => 'document',
            'audio'        => 'audio',
            'publication'  => 'publication',
            'folktale'     => 'folktale',
            'story'        => 'story',
            'poem'         => 'poem',
            'phrase'       => 'phrase',
            'historical_figure' => 'historical figure',
            'timeline_event'    => 'timeline event',
            'grammar'           => 'grammar',
            default        => 'archive entry',
        };
    }
}
