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

class ArchiveIntelligence
{
    private PDO $db;
    private SemanticSearch $semantic;

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

    public function __construct(PDO $db)
    {
        $this->db       = $db;
        $this->semantic = new SemanticSearch($db);
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

        // Resolve follow-up references ("it", "that", "the same") using conversation history.
        // Also capture the last entity so we can use it as the subject directly on follow-ups.
        $lastEntity = null;
        try {
            [$resolvedMessage, $lastEntity] = $this->resolveFollowUpWithEntity($message, $history);
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

        // Search the archive
        $results = $this->searchArchive($resolvedMessage, $subject, $intent, $requestedCount);

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
        if (empty($history)) return [$message, null];

        $lastEntity = $this->extractEntityFromLastAssistantTurn($history);
        if (!$lastEntity) {
            $lastEntity = $this->extractLastSubjectFromHistory($history);
        }

        $resolved = $this->resolveFollowUp($message, $history);
        return [$resolved, $lastEntity];
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
        if (preg_match('/^(yes|no|yep|nope|yup|ok|okay|sure|got\s*it|i\s*see|alright|right|thanks|thank\s*you|cool|nice|great|interesting|indeed|absolutely|of\s*course)\s*[.!,]?\s*$/iu', $message)) {
            // Try to execute the first suggestion from the last AI follow-up question
            $suggestion = $this->extractFollowUpSuggestionAsQuery($history);
            if ($suggestion) {
                return $suggestion;
            }
            return '__ACKNOWLEDGEMENT__';
        }

        // Extract the last entity from the AI's previous response — more reliable
        // than parsing user messages, since the AI names the entity prominently.
        $lastEntity = $this->extractEntityFromLastAssistantTurn($history);

        // Fall back to extracting from user messages if assistant turn gives nothing
        if (!$lastEntity) {
            $lastEntity = $this->extractLastSubjectFromHistory($history);
        }

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

        // Short acknowledgements ("okay", "yes", "sure", "I see", "got it", "thanks") →
        // don't loop back to the same entry. Return a sentinel that triggers a
        // conversational bridge instead of another search.
        if (preg_match('/^(yes|ok|okay|sure|got it|i see|alright|right|thanks|thank you|cool|nice|great|interesting)\s*[.!]?$/u', $m)) {
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
                    if (preg_match('/^(meaning|insight|used when|category|example|source|note|dictionary|archive|bible|names?|words?|proverbs?|foods?|plants?|festivals?|animals?|grammar|audio|video|publications?|summary|part of|related|\d+\.)$/iu', $clean)) continue;
                    // Skip very short
                    if (mb_strlen($clean) < 2) continue;
                    // Skip single common English words that are likely meanings, not entities
                    static $commonMeanings = ['fancy','good','bad','big','small','new','old','free','happy','sad',
                        'true','false','yes','no','one','two','please','thanks'];
                    if (in_array(mb_strtolower($clean), $commonMeanings)) continue;
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
            ];
            if (preg_match('/\b([A-ZÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÃÑ][a-záéíóúàèìòùâêîôûãñ]+(?:[-\s][A-Z][a-z]+)?)\b/u', $content, $m)) {
                $lower = mb_strtolower($m[1]);
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
     * Extract a subject from the last meaningful USER message in history.
     * Used as a fallback when the assistant turn gives no entity.
     */
    private function extractLastSubjectFromHistory(array $history): ?string
    {
        static $stopWords = ['what','is','are','the','a','an','of','in','tell','me','about',
                             'mean','means','ok','yes','no','thanks','great','how','does','do',
                             'that','this','give','show','can','you','please','more','some'];

        foreach (array_reverse($history) as $turn) {
            if (($turn['role'] ?? '') !== 'user') continue;

            $content = $turn['content'] ?? '';
            // Skip very short user turns (they're themselves follow-ups, not subjects)
            if (mb_strlen($content) < 5) continue;

            $words  = preg_split('/[\s\?\.\!\,]+/u', mb_strtolower($content), -1, PREG_SPLIT_NO_EMPTY);
            $useful = array_filter($words, fn($w) => mb_strlen($w) > 2 && !in_array($w, $stopWords));

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

        // Content-type keywords suggest a new search category
        if (preg_match('/\b(proverbs?|names?|foods?|plants?|festivals?|animals?|words?|translate|meaning of)\b/u', $lower)) {
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
                $a = trim(preg_replace('/[?!.,]+$/', '', $m[1]));
                $b = trim(preg_replace('/[?!.,]+$/', '', $m[2]));
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

        // Greeting
        if (preg_match('/^(hi|hello|good\s*(morning|evening|afternoon|day)|nde\s*er|msugh|how\s*are\s*you|i\s*ngu)/u', $m)) {
            return self::INTENT_GREETING;
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

        // Word/translation lookup
        if (preg_match('/\b(what|how|translate|meaning|mean|definition|define|word for|say)\b/u', $m) &&
            preg_match('/\b(tiv|english)\b/u', $m)) {
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
            '/(?:what is|who is) ["\']?(.+?)["\']?(?:\?|$)/iu',
        ];

        foreach ($patterns as $pat) {
            if (preg_match($pat, $m, $hits)) {
                $candidate = trim($hits[1]);
                // Strip trailing punctuation and common filler
                $candidate = preg_replace('/[\?\!\.\,]+$/', '', $candidate);
                $candidate = preg_replace('/\b(please|tiv|english|in tiv|in english)\b/iu', '', $candidate);
                $candidate = trim($candidate);
                if (mb_strlen($candidate) > 1 && mb_strlen($candidate) < 100) {
                    return $candidate;
                }
            }
        }

        // Fallback: strip stop words and take the most content-bearing word.
        // Also strip list-request words and quantifiers so "give me three proverbs"
        // → subject="" (triggers generic sample) not "give three proverbs".
        $stopWords = ['what','is','are','the','a','an','of','in','on','at','how','does','do','can',
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
                      // gender qualifiers (used in "is X a male/female name?")
                      'male','female','unisex','boy','girl',
                      // yes/no question starters
                      'is','are','was','were','did','has','have'];

        $words = preg_split('/[\s\?\.\!\,]+/u', mb_strtolower($m), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($w) => mb_strlen($w) > 2 && !in_array($w, $stopWords));

        return implode(' ', array_values(array_slice($words, 0, 3)));
    }

    // ─────────────────────────────────────────────────────────────
    // Archive Search
    // ─────────────────────────────────────────────────────────────

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
                $words     = $this->searchWords($subject);
                $names     = $this->searchNames($subject);
                $proverbs  = $this->searchProverbs($subject);
                $festivals = $this->searchFestivals($subject);
                $foods     = $this->searchFoods($subject);
                $plants    = $this->searchPlants($subject);
                $animals   = $this->searchAnimals($subject);
                $results['primary'] = array_merge($words, $names, $proverbs, $festivals, $foods, $plants, $animals);
                break;
            case self::INTENT_NAME:
                $results['primary'] = $this->searchNames($subject);
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
                // Yes/no questions: search words and names first, then everything else
                $words = $this->searchWords($subject);
                $names = $this->searchNames($subject);
                $results['primary'] = array_merge($words, $names);
                if (empty($results['primary'])) {
                    $results['primary'] = array_merge(
                        $this->searchFestivals($subject),
                        $this->searchFoods($subject),
                        $this->searchAnimals($subject)
                    );
                }
                break;
            default:
                // General search across full index
                $results['primary'] = $this->searchIndex($subject, 5);
                break;
        }

        // If targeted search found nothing, fall back to semantic index search
        // (expands query bilingually before hitting FULLTEXT)
        if (empty($results['primary']) && $subject !== '') {
            try {
                $results['primary'] = $this->semantic->search($subject, [], 6);
            } catch (\Throwable $e) {
                error_log('SemanticSearch error: ' . $e->getMessage());
                $results['primary'] = [];
            }
        }

        return $results;
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

    private function searchNames(string $q): array
    {
        if (trim($q) === '') {
            $stmt = $this->db->prepare("SELECT *, 'tiv_names' AS _table FROM tiv_names ORDER BY RAND() LIMIT 5");
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = '%' . $q . '%';
        $lower = mb_strtolower($q);
        $stmt = $this->db->prepare(
            "SELECT *, 'tiv_names' AS _table FROM tiv_names
             WHERE (LOWER(tiv_name) = ? OR LOWER(tiv_name) LIKE ?
                OR LOWER(english_meaning) LIKE ? OR LOWER(description) LIKE ?)
               AND CHAR_LENGTH(tiv_name) <= 60
             ORDER BY CASE WHEN LOWER(tiv_name) = ? THEN 0 ELSE 1 END,
                      CHAR_LENGTH(tiv_name) ASC
             LIMIT 5"
        );
        $stmt->execute([$lower, $like, $like, $like, $lower]);
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

        // List request
        if ($intent === self::INTENT_LIST) {
            return $this->listResponse($message, $primary);
        }

        // No results
        if (empty($primary)) {
            return $this->notFoundResponse($subject, $message);
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
                return $this->composeSummaryResponse($primary, $subject, $related);
            case self::INTENT_YESNO:
                return $this->composeYesNoResponse($message, $primary, $subject, $related);
            default:
                return $this->composeGeneralResponse($primary, [], $related, $subject);
        }
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
                $fetched[] = $row;
            }
        }

        return $fetched;
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
            default         => 'general',
        };
    }

    /**
     * Format a mixed multi-type result set, grouped by content type with section headers.
     */
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
        $primaryDisplay = $r['tiv_word'] ?? $r['tiv_name'] ?? $r['tiv_text'] ?? $r['title'] ?? $r['primary_text'] ?? '';
        if (trim($primaryDisplay) === '') return '';

        $url = isset($r['id']) ? match($type) {
            'word'     => url('word/'     . $r['id']),
            'name'     => url('name/'     . $r['id']),
            'proverb'  => url('proverb/'  . $r['id']),
            'food'     => url('food/'     . $r['id']),
            'plant'    => url('plant/'    . $r['id']),
            'festival' => url('festival/' . $r['id']),
            'animal'   => url('animal/'   . $r['id']),
            default    => $r['_url'] ?? '',
        } : ($r['_url'] ?? '');

        return match($type) {
            'word' => sprintf(
                "• **%s**%s — %s%s%s",
                $r['tiv_word'] ?? '',
                !empty($r['pronunciation']) ? ' (/' . $r['pronunciation'] . '/)' : '',
                $r['english_meaning'] ?? '',
                !empty($r['part_of_speech']) ? ' [' . $r['part_of_speech'] . ']' : '',
                $url ? "  [→]({$url})" : ''
            ),
            'name' => sprintf(
                "• **%s**%s — *%s*%s",
                $r['tiv_name'] ?? '',
                !empty($r['gender']) ? ' (' . $r['gender'] . ')' : '',
                $r['english_meaning'] ?? '',
                $url ? "  [→]({$url})" : ''
            ),
            'proverb' => sprintf(
                "• *\"%s\"* — %s%s",
                mb_substr($r['tiv_text'] ?? '', 0, 70),
                mb_substr($r['english_translation'] ?? '', 0, 80),
                $url ? "  [→]({$url})" : ''
            ),
            'food', 'plant', 'festival', 'animal' => sprintf(
                "• **%s** (%s) — %s%s",
                $r['tiv_name'] ?? $r['name'] ?? '',
                $r['english_name'] ?? $r['name'] ?? '',
                mb_substr($r['description'] ?? '', 0, 80),
                $url ? "  [→]({$url})" : ''
            ),
            'bible' => sprintf(
                "• *%s %s:%s* — \"%s\"%s",
                $r['book'] ?? '', $r['chapter'] ?? '', $r['verse'] ?? '',
                mb_substr($r['english_web'] ?? '', 0, 80),
                $url ? "  [→]({$url})" : ''
            ),
            default => sprintf(
                "• **%s** — %s%s",
                mb_substr($r['title'] ?? $r['tiv_name'] ?? '', 0, 60),
                mb_substr($r['excerpt'] ?? $r['description'] ?? '', 0, 80),
                $url ? "  [→]({$url})" : ''
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

        if ($tivName !== '' && mb_strlen($tivName) <= 35 && mb_substr_count($tivName, ' ') <= 3) {
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
                $hasDeeperMeaning = !empty($r['deeper_meaning'] ?? $r['secondary_text']);
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
        $all = array_merge(
            $this->searchWords($subject),
            $this->searchNames($subject),
            $this->searchProverbs($subject),
            $this->searchFoods($subject),
            $this->searchPlants($subject),
            $this->searchFestivals($subject),
            $this->searchAnimals($subject)
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

    private function notFoundResponse(string $subject, string $message): string
    {
        if (!$subject) {
            return "I couldn't find that. Try asking about a specific Tiv word, name, proverb, food, plant, festival, or animal.";
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

            $all = array_merge($words, $names);
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

        // For gender questions, prefer rows that actually have the gender field set
        $r = $rows[0];
        if (preg_match('/\b(male|female|unisex|boy|girl)\b/u', $q)) {
            foreach ($rows as $candidate) {
                if (!empty($candidate['gender'])) { $r = $candidate; break; }
            }
        }

        $name = $r['tiv_name'] ?? $r['tiv_word'] ?? $r['tiv_text'] ?? $subject;
        $url  = isset($r['id'])
            ? (isset($r['_table']) ? url($this->guessTypeFromTable($r['_table']) . '/' . $r['id']) : '')
            : '';

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
            default        => 'archive entry',
        };
    }
}
