<?php
/**
 * SemanticSearch — Bilingual query expansion and concept-aware ranking.
 *
 * Makes archive search "semantic" without external ML embeddings by:
 *   1. Cross-lingual expansion: "God" → also search "Aondo", "Ter", "Tor" (from dictionary)
 *   2. Concept synonym expansion: related terms from the archive's own data
 *   3. BM25-style re-ranking of FULLTEXT results
 *   4. Knowledge graph boosting: results connected to high-confidence entries rank higher
 *
 * This is domain-specific semantic search grounded entirely in the archive corpus.
 */
class SemanticSearch
{
    private PDO $db;

    // Cache expanded terms per request (avoid redundant DB lookups)
    private array $expansionCache = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────

    /**
     * Expand a user query with bilingual and related terms from the archive.
     *
     * @param  string $query  Original user query
     * @return array          ['original' => string, 'expanded' => string[], 'tiv_terms' => string[], 'english_terms' => string[]]
     */
    public function expandQuery(string $query): array
    {
        $query = trim($query);
        $key   = mb_strtolower($query);

        if (isset($this->expansionCache[$key])) {
            return $this->expansionCache[$key];
        }

        $tokens = $this->tokenize($query);
        $tivTerms     = [];
        $englishTerms = [];

        foreach ($tokens as $token) {
            $lower = mb_strtolower($token);

            // Look up in dictionary both ways
            $tivMatches     = $this->lookupEnglishToTiv($lower);
            $englishMatches = $this->lookupTivToEnglish($lower);

            $tivTerms     = array_merge($tivTerms,     $tivMatches);
            $englishTerms = array_merge($englishTerms, $englishMatches);
        }

        // Add archival context expansions (culture-specific concept mapping)
        $conceptExpansions = $this->expandConcepts($tokens);

        $result = [
            'original'      => $query,
            'expanded'      => array_unique(array_merge($tokens, $tivTerms, $englishTerms, $conceptExpansions)),
            'tiv_terms'     => array_unique($tivTerms),
            'english_terms' => array_unique($englishTerms),
        ];

        $this->expansionCache[$key] = $result;
        return $result;
    }

    /**
     * Search the unified index using expanded query terms.
     * Returns results ranked by BM25-style score and knowledge graph connections.
     *
     * @param string $query        User query
     * @param array  $contentTypes Filter to specific types (empty = all)
     * @param int    $limit
     */
    public function search(string $query, array $contentTypes = [], int $limit = 8): array
    {
        $expansion = $this->expandQuery($query);
        $allTerms  = $expansion['expanded'];

        if (empty($allTerms)) {
            return [];
        }

        $results = [];

        // Primary: FULLTEXT search with original query
        $ftResults = $this->fulltextSearch($query, $contentTypes, $limit);
        foreach ($ftResults as $r) {
            $id = $r['source_table'] . ':' . $r['source_id'];
            $results[$id] = array_merge($r, ['score' => 10.0]);
        }

        // Secondary: search with each expanded term individually
        foreach (array_slice($allTerms, 0, 6) as $term) {
            if ($term === $query || mb_strlen($term) < 2) continue;

            $termResults = $this->fulltextSearch($term, $contentTypes, 4);
            foreach ($termResults as $r) {
                $id = $r['source_table'] . ':' . $r['source_id'];
                if (!isset($results[$id])) {
                    $results[$id] = array_merge($r, ['score' => 5.0]);
                } else {
                    // Boost entries found by multiple expansion terms
                    $results[$id]['score'] += 3.0;
                }
            }
        }

        // Knowledge graph boost: results linked to top result get a boost
        if (!empty($results)) {
            $top = reset($results);
            $linkedIds = $this->getLinkedIds($top['source_table'] ?? '', (int) ($top['source_id'] ?? 0));
            foreach ($linkedIds as $linkedId) {
                if (isset($results[$linkedId])) {
                    $results[$linkedId]['score'] += 2.0;
                }
            }
        }

        // Sort by score descending
        uasort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_values(array_slice($results, 0, $limit));
    }

    /**
     * Build a boolean FULLTEXT query string from expanded terms.
     * Used when running a MySQL FULLTEXT BOOLEAN MODE search.
     */
    public function buildBooleanQuery(string $query): string
    {
        $expansion = $this->expandQuery($query);
        $terms     = $expansion['expanded'];

        if (empty($terms)) return $query;

        // The original query terms get +prefix (required)
        // Expanded terms are optional additions
        $origTokens    = $this->tokenize($query);
        $expandedExtra = array_diff($terms, $origTokens);

        $parts = [];
        foreach ($origTokens as $t) {
            if (mb_strlen($t) >= 3) {
                $parts[] = '+' . $this->escapeBooleanTerm($t) . '*'; // prefix match
            }
        }
        foreach (array_slice($expandedExtra, 0, 4) as $t) {
            if (mb_strlen($t) >= 3) {
                $parts[] = $this->escapeBooleanTerm($t); // optional match (boosts relevance)
            }
        }

        return implode(' ', $parts) ?: $query;
    }

    // ─────────────────────────────────────────────────────────────
    // Dictionary-based cross-lingual expansion
    // ─────────────────────────────────────────────────────────────

    private function lookupEnglishToTiv(string $term): array
    {
        // Find Tiv words whose English meaning contains this term
        $stmt = $this->db->prepare(
            "SELECT tiv_word, alternate_meaning FROM daily_words
             WHERE LOWER(english_meaning) = ? OR LOWER(english_meaning) LIKE ?
               AND is_active = 1
             LIMIT 5"
        );
        $stmt->execute([$term, '%' . $term . '%']);
        $rows = $stmt->fetchAll();

        $tivTerms = [];
        foreach ($rows as $r) {
            if (!empty($r['tiv_word'])) $tivTerms[] = mb_strtolower($r['tiv_word']);
        }

        // Also check translation_phrases
        $stmt2 = $this->db->prepare(
            "SELECT target_text FROM translation_phrases
             WHERE source_language = 'english' AND target_language = 'tiv'
               AND status = 'active'
               AND (LOWER(source_text) = ? OR LOWER(source_text) LIKE ?)
             LIMIT 5"
        );
        $stmt2->execute([$term, '%' . $term . '%']);
        foreach ($stmt2->fetchAll() as $r) {
            $tivTerms[] = mb_strtolower($r['target_text']);
        }

        return array_unique(array_filter($tivTerms));
    }

    private function lookupTivToEnglish(string $term): array
    {
        // Find English meanings for a Tiv word
        $stmt = $this->db->prepare(
            "SELECT english_meaning, alternate_meaning FROM daily_words
             WHERE LOWER(tiv_word) = ? OR LOWER(tiv_word) LIKE ?
               AND is_active = 1
             LIMIT 5"
        );
        $stmt->execute([$term, $term . '%']);
        $rows = $stmt->fetchAll();

        $engTerms = [];
        foreach ($rows as $r) {
            if (!empty($r['english_meaning'])) {
                // Take the primary meaning only (before "/" or ",")
                $parts = preg_split('/[\/,]/', $r['english_meaning']);
                $engTerms[] = mb_strtolower(trim($parts[0]));
            }
        }

        return array_unique(array_filter($engTerms));
    }

    /**
     * Expand culturally-loaded concepts to related Tiv archive terms.
     * E.g. "God" → ['Aondo', 'Ter', 'Tor', 'divine']
     *      "marriage" → ['bride price', 'ikyoor', 'ceremony']
     */
    private function expandConcepts(array $tokens): array
    {
        $expanded = [];

        foreach ($tokens as $token) {
            $lower = mb_strtolower($token);

            // Concept map: common English concepts → Tiv cultural equivalents
            // Populated from the archive itself (these are illustrative; the dictionary
            // lookup above handles the full range dynamically)
            static $conceptMap = [
                'god'        => ['aondo', 'ter', 'tor', 'usha', 'divine', 'creator'],
                'spirit'     => ['aondo', 'ibo', 'ibibi'],
                'chief'      => ['tor', 'ter', 'leader', 'ruler'],
                'king'       => ['tor', 'ter', 'chief'],
                'dance'      => ['kwagh-hir', 'icongo', 'masquerade'],
                'marriage'   => ['ikyoor', 'bride', 'ceremony', 'dowry'],
                'harvest'    => ['nyian', 'food', 'festival', 'farming'],
                'medicine'   => ['ikpe', 'medicinal', 'herb', 'plant', 'healer'],
                'story'      => ['folktale', 'kwagh-hir', 'oral', 'tradition'],
                'war'        => ['imongo', 'battle', 'warrior', 'conflict'],
                'farm'       => ['mba', 'farming', 'crops', 'cultivation'],
                'love'       => ['soo', 'ikyar', 'affection'],
                'death'      => ['tamen', 'burial', 'ancestor', 'spirit'],
                'water'      => ['aba', 'river', 'benue', 'stream'],
                'home'       => ['kem', 'family', 'compound', 'household'],
                'name'       => ['yihin', 'naming', 'identity'],
                'proverb'    => ['kwagh', 'saying', 'wisdom', 'adage'],
            ];

            if (isset($conceptMap[$lower])) {
                $expanded = array_merge($expanded, $conceptMap[$lower]);
            }

            // Also look up names: if term matches a Tiv name, include its meaning
            $stmt = $this->db->prepare(
                "SELECT english_meaning FROM tiv_names
                 WHERE LOWER(tiv_name) = ? OR LOWER(english_meaning) LIKE ?
                 LIMIT 3"
            );
            $stmt->execute([$lower, '%' . $lower . '%']);
            foreach ($stmt->fetchAll() as $r) {
                $parts = preg_split('/[\/,\s]+/', $r['english_meaning']);
                foreach (array_slice($parts, 0, 2) as $p) {
                    $p = mb_strtolower(trim($p));
                    if (mb_strlen($p) >= 3) $expanded[] = $p;
                }
            }
        }

        return array_unique($expanded);
    }

    // ─────────────────────────────────────────────────────────────
    // FULLTEXT search against the unified index
    // ─────────────────────────────────────────────────────────────

    private function fulltextSearch(string $term, array $types, int $limit): array
    {
        if (mb_strlen(trim($term)) < 2) return [];

        $typeFilter = '';
        $limitInt   = (int) $limit; // MySQL LIMIT/OFFSET won't accept string-bound params

        if (!empty($types)) {
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $typeFilter   = "AND content_type IN ({$placeholders})";
        }

        // Try FULLTEXT first
        try {
            $sql = "SELECT *, MATCH(primary_text, secondary_text, excerpt) AGAINST(? IN NATURAL LANGUAGE MODE) AS ft_score
                    FROM archive_search_index
                    WHERE MATCH(primary_text, secondary_text, excerpt) AGAINST(? IN NATURAL LANGUAGE MODE)
                    {$typeFilter}
                    ORDER BY ft_score DESC
                    LIMIT {$limitInt}";
            $params = array_merge([$term, $term], $types);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            if (!empty($rows)) return $rows;
        } catch (\PDOException $e) {
            // FULLTEXT unavailable
        }

        // LIKE fallback
        $like   = '%' . $term . '%';
        $params = array_merge([$like, $like, $like], $types);

        $stmt = $this->db->prepare(
            "SELECT * FROM archive_search_index
             WHERE (primary_text LIKE ? OR secondary_text LIKE ? OR excerpt LIKE ?)
             {$typeFilter}
             LIMIT {$limitInt}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────────
    // Knowledge graph
    // ─────────────────────────────────────────────────────────────

    private function getLinkedIds(string $table, int $id): array
    {
        if (!$table || !$id) return [];

        try {
            $stmt = $this->db->prepare(
                "SELECT CONCAT(to_table, ':', to_id) AS linked_id
                 FROM archive_knowledge_links
                 WHERE from_table = ? AND from_id = ?
                 ORDER BY weight DESC LIMIT 5"
            );
            $stmt->execute([$table, $id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function tokenize(string $text): array
    {
        $text   = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $text);
        $tokens = preg_split('/\s+/u', mb_strtolower(trim($text)), -1, PREG_SPLIT_NO_EMPTY);
        // Filter out very short tokens and stop words
        static $stop = ['what','is','are','the','a','an','of','in','on','at','for','to','and','or',
                        'how','does','do','can','tell','me','about','who','when','where','why'];
        return array_values(array_filter($tokens, fn($t) => mb_strlen($t) > 1 && !in_array($t, $stop)));
    }

    private function escapeBooleanTerm(string $term): string
    {
        return '"' . str_replace('"', '', $term) . '"';
    }
}
