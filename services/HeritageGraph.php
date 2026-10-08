<?php
/**
 * HeritageGraph — answers questions that are really graph traversals over the
 * Nigeria Heritage knowledge network ("Which states have documented Tiv
 * communities?", "What ethnic groups are in Benue State?", "Where is Tiv spoken?").
 *
 * Rules (NIGERIA_EXPANSION_STAGE_1_AUDIT.md §10):
 *   - answers only from published records and published, sourced relations;
 *   - every listed fact carries its evidence status and source;
 *   - when the archive has the entity but not the relation, it says so — it never
 *     infers or guesses a relationship;
 *   - when the question does not match, or the entity is not a published national
 *     record, answer() returns null and ArchiveIntelligence carries on unchanged.
 */

require_once BASE_PATH . '/services/HeritagePublic.php';

class HeritageGraph
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Question patterns: regex => [handler, entity table the captured name must resolve in]. */
    private const PATTERNS = [
        // Where a people lives
        '/\b(?:which|what)\s+states?\s+(?:have|has|with|contain)\s+(?:documented\s+)?(?:the\s+)?(?<name>.+?)\s+(?:communities|community|people|populations?|settlements?|presence)\b/iu' => ['groupLocations', 'ethnic_groups'],
        '/\bwhere\s+(?:do|does|are|is)\s+(?:the\s+)?(?<name>.+?)\s+(?:people\s+)?(?:live|lives|found|located|settled)\b/iu' => ['groupLocations', 'ethnic_groups'],
        '/\bin\s+which\s+states?\s+(?:are|is|do|does)\s+(?:the\s+)?(?<name>.+?)\s+(?:people\s+)?(?:found|live|located)\b/iu' => ['groupLocations', 'ethnic_groups'],
        // Where a language is spoken
        '/\bwhere\s+is\s+(?:the\s+)?(?<name>.+?)(?:\s+language)?\s+spoken\b/iu' => ['languagePlaces', 'languages'],
        '/\b(?:which|what)\s+states?\s+(?:speak|speaks)\s+(?:the\s+)?(?<name>.+?)(?:\s+language)?\s*\??$/iu' => ['languagePlaces', 'languages'],
        // What language a people speaks
        '/\bwhat\s+languages?\s+(?:do|does)\s+(?:the\s+)?(?<name>.+?)\s+(?:people\s+)?speak\b/iu' => ['groupLanguages', 'ethnic_groups'],
        // Peoples / languages of a state
        '/\b(?:what|which)\s+(?:ethnic\s+groups|peoples|tribes)\s+(?:are|live|are\s+found|exist)?\s*(?:in|of)\s+(?<name>.+?)\s*\??$/iu' => ['unitGroups', 'admin_units'],
        '/\b(?:ethnic\s+groups|peoples)\s+(?:in|of)\s+(?<name>.+?)\s*\??$/iu' => ['unitGroups', 'admin_units'],
        '/\b(?:what|which)\s+languages\s+(?:are\s+)?(?:spoken\s+)?(?:in|of)\s+(?<name>.+?)\s*\??$/iu' => ['unitLanguages', 'admin_units'],
        // Neighbouring units
        '/\b(?:which|what)\s+states?\s+(?:border|borders|neighbour|neighbours|neighbor|neighbors|share\s+a\s+border\s+with)\s+(?<name>.+?)\s*\??$/iu' => ['unitNeighbours', 'admin_units'],
        '/\b(?:neighbou?ring\s+states|neighbou?rs|borders)\s+of\s+(?<name>.+?)\s*\??$/iu' => ['unitNeighbours', 'admin_units'],
        '/\bwhat\s+(?:does|do)\s+(?<name>.+?)\s+border\s*\??$/iu' => ['unitNeighbours', 'admin_units'],
        // Structure of a state
        '/\b(?:what|which|list)\s+(?:are\s+)?(?:the\s+)?(?:lgas|local\s+government\s+areas|local\s+governments)\s+(?:in|of)\s+(?<name>.+?)\s*\??$/iu' => ['unitLgas', 'admin_units'],
        '/\bwhat\s+is\s+the\s+capital\s+(?:city\s+)?of\s+(?<name>.+?)\s*\??$/iu' => ['unitCapital', 'admin_units'],
        // Events at a place
        '/\bwhat\s+(?:happened|events\s+(?:happened|took\s+place))\s+(?:in|at)\s+(?<name>.+?)\s*\??$/iu' => ['placeEvents', '*place'],
        '/\bhistor(?:y|ical\s+events)\s+(?:of|in)\s+(?<name>.+?)\s*\??$/iu' => ['placeEvents', '*place'],
        // General question naming a national record exactly (checked last)
        '/^(?:please\s+)?(?:tell\s+me\s+about|what\s+(?:is|are|was|were)|who\s+(?:are|were|is|was)|describe|information\s+(?:on|about))\s+(?:the\s+)?(?<name>.+?)(?:\s+people)?\s*\??$/iu' => ['describeEntity', '*any'],
    ];

    /** Tables tried, in order, when a general question names a national record. */
    private const DESCRIBE_TABLES = ['admin_units', 'ethnic_groups', 'languages', 'polities', 'places', 'cultural_records', 'historical_periods'];

    /**
     * Subjects the Tiv Heritage Archive itself answers in depth; general questions about
     * them keep going to the Tiv archive answer instead of the national record.
     */
    private const TIV_COLLECTION_SUBJECTS = ['tiv', 'tiv language', 'the tiv', 'tivs'];

    public function answer(string $message): ?string
    {
        $message = trim($message);
        foreach (self::PATTERNS as $regex => [$handler, $table]) {
            if (!preg_match($regex, $message, $m)) continue;
            $name = $this->cleanName($m['name']);
            if ($name === '') continue;
            if ($table === '*any' && in_array(mb_strtolower($name), self::TIV_COLLECTION_SUBJECTS, true)) continue;
            $entity = match ($table) {
                '*any'      => $this->resolveAny($name),
                '*place'    => $this->resolve('admin_units', $name) ?? $this->resolve('places', $name),
                // "Where is Tiv language spoken?": the word "language" may belong to the name.
                'languages' => $this->resolve('languages', $name) ?? $this->resolve('languages', $name . ' language'),
                default     => $this->resolve($table, $name),
            };
            if (!$entity) continue; // not a published national record: let the normal AI answer
            $answer = $this->$handler($entity);
            if ($answer === null) continue;
            return $answer;
        }
        return null;
    }

    /* ── Handlers ──────────────────────────────────────────────── */

    private function groupLocations(array $g): string
    {
        $rows = $this->related($g, ['present_in', 'historical_territory'], 'out', ['admin_units', 'places']);
        return $this->reply($g, $rows,
            "Places with documented **{$g['_name']}** communities",
            "The Nigeria Heritage Archive does not yet record where **{$g['_name']}** communities are found. I won't guess — that research hasn't been added yet.");
    }

    private function languagePlaces(array $l): string
    {
        $rows = $this->related($l, ['spoken_in'], 'out', ['admin_units', 'places']);
        return $this->reply($l, $rows,
            "Where **{$l['_name']}** is documented as spoken",
            "The archive does not yet record where **{$l['_name']}** is spoken.");
    }

    private function groupLanguages(array $g): string
    {
        $rows = $this->related($g, ['speaks'], 'out', ['languages']);
        return $this->reply($g, $rows,
            "Languages documented for the **{$g['_name']}**",
            "The archive does not yet record which language the **{$g['_name']}** speak.");
    }

    private function unitGroups(array $u): string
    {
        $rows = $this->related($u, ['present_in', 'historical_territory'], 'in', ['ethnic_groups']);
        return $this->reply($u, $rows,
            "Ethnic groups documented in **{$u['_name']}**",
            "The archive does not yet record the ethnic groups of **{$u['_name']}**.");
    }

    private function unitLanguages(array $u): string
    {
        $rows = $this->related($u, ['spoken_in'], 'in', ['languages']);
        return $this->reply($u, $rows,
            "Languages documented as spoken in **{$u['_name']}**",
            "The archive does not yet record the languages spoken in **{$u['_name']}**.");
    }

    private function unitNeighbours(array $u): string
    {
        // A border runs both ways: the link may be stored from either side.
        $rows = [];
        foreach (array_merge($this->related($u, ['neighbours'], 'out', ['admin_units']), $this->related($u, ['neighbours'], 'in', ['admin_units'])) as $r) {
            $rows[$r['url']] = $r;
        }
        return $this->reply($u, array_values($rows),
            "States documented as bordering **{$u['_name']}**",
            "The archive does not yet record which states border **{$u['_name']}**.");
    }

    private function unitLgas(array $u): string
    {
        $stmt = $this->db->prepare("SELECT * FROM admin_units WHERE parent_id = ? AND unit_type = 'lga' AND review_status = 'published' ORDER BY name");
        $stmt->execute([$u['id']]);
        $rows = array_map(fn($r) => ['name' => $r['name'], 'url' => HeritagePublic::recordUrl('admin_units', $r),
                                     'evidence' => HeritagePublic::evidenceWords($r['evidence_status'], $r['evidence_level'] ?? null), 'source' => null, 'when' => null], $stmt->fetchAll());
        return $this->reply($u, $rows,
            "Local Government Areas of **{$u['_name']}** recorded in the archive",
            "The archive does not yet list the Local Government Areas of **{$u['_name']}**.",
            '*Only published LGA records are listed, so this list may be incomplete while research is in progress.*');
    }

    private function unitCapital(array $u): string
    {
        $cap = $u['capital_place_id'] ? HeritagePublic::linkedRecord('places', (int) $u['capital_place_id']) : null;
        if (!$cap) {
            return "The archive does not yet record the capital of **{$u['_name']}**. [View {$u['_name']} →]({$u['_url']})";
        }
        return "According to the Nigeria Heritage Archive, the capital of **{$u['_name']}** is **[{$cap['name']}]({$cap['url']})**."
            . $this->recordEvidence($u) . "\n\n[View {$u['_name']} →]({$u['_url']})";
    }

    private function placeEvents(array $p): string
    {
        $rows = $this->related($p, ['occurred_in', 'affected'], 'in', ['timeline_events']);
        return $this->reply($p, $rows,
            "Events recorded for **{$p['_name']}**",
            "The archive does not yet record events linked to **{$p['_name']}**.");
    }

    private function describeEntity(array $e): ?string
    {
        // Only substantial national records answer a general "tell me about X" directly;
        // for a short record (e.g. a one-line LGA entry such as Gboko) the normal archive
        // answer — which can draw on Tiv content and still cite the national record — is better.
        $alias = $e['_alias'] ?? null;
        if (!$alias && !HeritagePublic::isIndexable($e['_table'], $e)) return null;
        $intro = "From the Nigeria Heritage Archive:\n\n";
        if ($alias) {
            // Asked by another name (e.g. "Munshi"): say whose name it is, with its recorded note.
            $intro .= "**{$alias['name']}** is " . ($alias['name_type'] === 'spelling_variant' ? 'a variant spelling of ' : 'another name recorded for ')
                    . "**{$e['_name']}**" . ($alias['usage_notes'] ? " — {$alias['usage_notes']}" : '.') . "\n\n";
        }
        return $intro . $this->describe($e['_table'], $e);
    }

    private function resolveAny(string $name): ?array
    {
        foreach (self::DESCRIBE_TABLES as $t) {
            if ($row = $this->resolve($t, $name)) return $row;
        }
        return null;
    }

    /* ── Graph + formatting ────────────────────────────────────── */

    /**
     * Published, sourced relations of $e of the given types. 'out' = $e is the subject,
     * 'in' = $e is the object. Records visitors cannot see are skipped.
     */
    private function related(array $e, array $types, string $dir, array $otherTables): array
    {
        $ph = implode(',', array_fill(0, count($types), '?'));
        $tp = implode(',', array_fill(0, count($otherTables), '?'));
        [$self, $other] = $dir === 'out' ? ['from', 'to'] : ['to', 'from'];
        $stmt = $this->db->prepare(
            "SELECT r.*, s.title AS source_title, s.id AS source_ref FROM entity_relations r
             JOIN sources s ON s.id = r.source_id
             WHERE r.{$self}_table = ? AND r.{$self}_id = ? AND r.relation_type IN ({$ph})
               AND r.{$other}_table IN ({$tp}) AND r.review_status = 'published'
             ORDER BY r.relation_type = 'historical_territory', r.valid_from_year IS NULL, r.valid_from_year, r.id");
        $stmt->execute([$e['_table'], $e['id'], ...$types, ...$otherTables]);
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $rec = HeritagePublic::linkedRecord($r[$other . '_table'], (int) $r[$other . '_id']);
            if (!$rec) continue;
            $from = HeritagePublic::dateLabel($r['valid_from_year'], $r['valid_from_text'], $r['date_precision']);
            $to = HeritagePublic::dateLabel($r['valid_to_year'], $r['valid_to_text'], $r['date_precision']);
            $out[] = $rec + ['evidence' => HeritagePublic::evidenceWords($r['evidence_status'], $r['evidence_level'] ?? null), 'when' => ($from || $to) ? trim(($from ?? '?') . '–' . ($to ?? '')) : null,
                             'role' => $r['role'], 'historical' => $r['relation_type'] === 'historical_territory',
                             'source' => ['title' => $r['source_title'], 'url' => url('references/' . (int) $r['source_ref'])]];
        }
        return $out;
    }

    private function reply(array $e, array $rows, string $heading, string $none,
                           string $note = '*Only relationships recorded with a source are listed; the archive does not infer others, so this list may be incomplete.*'): string
    {
        if (!$rows) {
            return $none . "\n\n[View {$e['_name']} in the Nigeria Heritage Archive →]({$e['_url']})";
        }
        $lines = [];
        foreach ($rows as $r) {
            $bits = array_filter([
                $r['when'] ?? null,
                !empty($r['historical']) ? 'historical territory' : null,
                $r['role'] ?? null,
                $r['evidence'] ?: null,
            ]);
            $line = "• [{$r['name']}]({$r['url']})" . ($bits ? ' — ' . implode(', ', $bits) : '');
            if (!empty($r['source'])) $line .= " · source: [{$r['source']['title']}]({$r['source']['url']})";
            $lines[] = $line;
        }
        return "{$heading}, from the Nigeria Heritage Archive:\n\n" . implode("\n", $lines)
            . "\n\n{$note}"
            . "\n\n[View {$e['_name']} →]({$e['_url']})";
    }

    private function recordEvidence(array $e): string
    {
        $words = HeritagePublic::evidenceWords($e['evidence_status'] ?? null, $e['evidence_level'] ?? null);
        return $words ? ' *(Evidence: ' . $words . '.)*' : '';
    }

    /** Short cited description of a national record (for "tell me about …" answers). */
    public function describe(string $table, array $r): string
    {
        $name = $r[HeritageRegistry::NAME_COLUMN[$table]];
        $url = HeritagePublic::recordUrl($table, $r);
        $summary = trim((string) ($r['summary'] ?? ''));
        if ($summary === '') {
            $first = '';
            foreach (array_keys(HeritagePublic::PROSE[$table] ?? []) as $col) {
                if (!empty($r[$col])) { $first = (string) $r[$col]; break; }
            }
            $summary = mb_substr(trim($first), 0, 300) . (mb_strlen(trim($first)) > 300 ? '…' : '');
        }
        $k = HeritagePublic::knowledge($table, (int) $r['id']);
        // The record's own link comes first: follow-up questions ("tell me more about it")
        // take the first link of the previous answer as its subject.
        $out = "**[{$name}]({$url})**" . ($summary ? " — {$summary}" : '') . $this->recordEvidence($r);
        if (($r['nature'] ?? '') === 'oral_tradition' || ($r['evidence_status'] ?? '') === 'oral_tradition') {
            $out .= "\n\n*This is preserved as oral tradition — a cultural record, not a proven historical fact.*";
        }
        foreach (array_slice($k['relations'], 0, 3, true) as $reads => $rels) {
            $out .= "\n\n{$name} {$reads}: " . implode(', ', array_map(fn($x) => "[{$x['other']['name']}]({$x['other']['url']})", $rels));
        }
        if ($k['sources']) {
            $out .= "\n\nSources: " . implode('; ', array_map(fn($s) => '[' . ($s['title'] ?: 'Source') . '](' . url('references/' . (int) $s['id']) . ')', array_slice($k['sources'], 0, 3)));
        }
        return $out . "\n\n[View in the Nigeria Heritage Archive →]({$url})";
    }

    /* ── Entity resolution ─────────────────────────────────────── */

    private function cleanName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name), " \t?.!,'\"");
        return preg_replace('/^(?:the|a|an)\s+/iu', '', $name);
    }

    /**
     * Published record of $table whose name, self-designation or recorded other name
     * matches (case-insensitive). For administrative units "Benue State" also matches
     * "Benue", and current states are preferred.
     */
    private function resolve(string $table, string $name): ?array
    {
        $candidates = [$name];
        if ($table === 'admin_units') {
            $stripped = preg_replace('/\s+(?:state|lga|local\s+government(?:\s+area)?)$/iu', '', $name);
            if ($stripped !== $name) $candidates[] = $stripped;
            if (preg_match('/^(?:fct|federal\s+capital\s+territory|abuja)$/iu', $name)) $candidates[] = 'Federal Capital Territory';
            // States are stored as "Kano State": a bare "Kano" means the state before any LGA of that name.
            if (!preg_match('/\bstate$/iu', $name)) array_unshift($candidates, $name . ' State');
        }
        $extraCols = ['ethnic_groups' => ' OR LOWER(t.endonym) = ?', 'cultural_records' => ' OR LOWER(t.local_name) = ?',
                      'admin_units' => ' OR LOWER(t.official_name) = ?'][$table] ?? '';
        $order = $table === 'admin_units'
            ? "ORDER BY (t.unit_type IN ('state','federal_capital_territory') AND t.status = 'current') DESC, t.id"
            : 'ORDER BY t.id';
        foreach ($candidates as $c) {
            $lc = mb_strtolower($c);
            $params = [$lc];
            if ($extraCols) $params[] = $lc;
            $params = [...$params, $table, $lc];
            $stmt = $this->db->prepare(
                "SELECT t.* FROM {$table} t WHERE t.review_status = 'published'" . ($table === 'admin_units' ? " AND t.unit_type <> 'ward'" : '') . " AND (LOWER(t.name) = ?{$extraCols}
                   OR t.id IN (SELECT entity_id FROM entity_names WHERE entity_table = ? AND LOWER(name) = ?)) {$order} LIMIT 1");
            $stmt->execute($params);
            if ($row = $stmt->fetch()) {
                $out = $row + ['_table' => $table, '_name' => $row['name'], '_url' => HeritagePublic::recordUrl($table, $row)];
                if (mb_strtolower($row['name']) !== $lc && mb_strtolower((string) ($row['endonym'] ?? '')) !== $lc) {
                    $n = $this->db->prepare('SELECT name, name_type, usage_notes FROM entity_names WHERE entity_table = ? AND entity_id = ? AND LOWER(name) = ? LIMIT 1');
                    $n->execute([$table, $row['id'], $lc]);
                    if ($alias = $n->fetch()) $out['_alias'] = $alias;
                }
                return $out;
            }
        }
        return null;
    }
}
