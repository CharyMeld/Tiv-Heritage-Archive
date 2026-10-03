<?php
/**
 * HeritagePublic — shared logic for the public Nigeria Heritage section:
 * public paths (also used by the admin for slug redirects), the indexability
 * gate that keeps thin pages out of Google (NIGERIA_EXPANSION_STAGE_1_AUDIT.md §15),
 * and the per-record knowledge (sources, names, statistics, relations) shown publicly.
 */

require_once BASE_PATH . '/services/HeritageRegistry.php';
require_once BASE_PATH . '/services/SeoHelper.php';

class HeritagePublic
{
    /** Indexability gate: minimum original prose, in words, for a record page. */
    public const MIN_WORDS = 300;
    /** A listing page is indexable once it links to this many indexable records. */
    public const MIN_LISTING_ITEMS = 3;
    /** Evidence statuses that keep a page out of the index even when published. */
    public const NOINDEX_EVIDENCE = ['unverified'];

    /** Long-form prose columns per table, with public section headings (display order). */
    public const PROSE = [
        'admin_units'        => ['description' => 'Overview', 'geography_notes' => 'Geography'],
        'places'             => ['description' => 'Overview', 'history' => 'History'],
        'ethnic_groups'      => ['description' => 'Overview', 'history' => 'History', 'origins_and_migration' => 'Origins & Migration',
                                 'traditional_governance' => 'Traditional Governance', 'social_organisation' => 'Social Organisation',
                                 'economy_and_occupations' => 'Economy & Occupations', 'preservation_notes' => 'Preservation'],
        'languages'          => ['description' => 'Overview', 'orthography_notes' => 'Writing & Orthography', 'literature_notes' => 'Literature',
                                 'educational_use' => 'Use in Education', 'digital_resources' => 'Digital Resources', 'preservation_notes' => 'Preservation'],
        'polities'           => ['description' => 'Overview', 'governance' => 'Governance'],
        'cultural_records'   => ['description' => 'Overview', 'significance' => 'Significance', 'status_notes' => 'Continuity & Change'],
        'historical_periods' => ['description' => 'Overview'],
        'historical_figures' => ['biography' => 'Biography', 'early_life' => 'Early Life', 'education' => 'Education', 'career' => 'Career',
                                 'leadership_service' => 'Leadership & Service', 'achievements' => 'Achievements',
                                 'historical_significance' => 'Historical Significance', 'legacy' => 'Legacy'],
        'timeline_events'    => ['description' => 'What Happened', 'causes' => 'Causes', 'consequences' => 'Consequences',
                                 'historical_significance' => 'Historical Significance'],
    ];

    /** Summary column per table. */
    public const SUMMARY = ['historical_figures' => 'short_summary', 'timeline_events' => 'short_summary'];

    /** Public section per table: [path segment, plural label, singular label]. */
    public const SECTIONS = [
        'admin_units'        => ['states', 'States & FCT', 'Administrative unit'],
        'places'             => ['places', 'Places & Heritage Sites', 'Place'],
        'ethnic_groups'      => ['ethnic-groups', 'Ethnic Groups', 'Ethnic group'],
        'languages'          => ['languages', 'Languages', 'Language'],
        'polities'           => ['kingdoms', 'Kingdoms & Traditional Institutions', 'Kingdom / institution'],
        'cultural_records'   => ['culture', 'Culture & Heritage', 'Cultural record'],
        'historical_periods' => ['periods', 'Historical Periods', 'Historical period'],
        'historical_figures' => ['people', 'People', 'Person'],
        'timeline_events'    => ['events', 'Events', 'Event'],
    ];

    private static ?PDO $db = null;

    private static function db(): PDO
    {
        return self::$db ??= Database::getInstance();
    }

    /* ── Paths ─────────────────────────────────────────────────── */

    /** Path under /nigeria/ for a record; $parentSlug is needed for LGAs. */
    public static function path(string $table, array $row, ?string $parentSlug = null): string
    {
        switch ($table) {
            case 'admin_units':
                if (in_array($row['unit_type'], ['state', 'federal_capital_territory'], true)) return 'states/' . $row['slug'];
                if ($row['unit_type'] === 'lga' && $row['parent_id']) {
                    $parentSlug ??= self::db()->query('SELECT slug FROM admin_units WHERE id = ' . (int) $row['parent_id'])->fetchColumn() ?: 'unknown';
                    return 'states/' . $parentSlug . '/lgas/' . $row['slug'];
                }
                return 'units/' . $row['slug'];
            case 'cultural_records':   return 'culture/' . str_replace('_', '-', $row['record_type']) . '/' . $row['slug'];
            case 'historical_figures': return 'people/' . $row['national_slug'];
            case 'timeline_events':    return 'events/' . $row['national_slug'];
        }
        return self::SECTIONS[$table][0] . '/' . $row['slug'];
    }

    /** Canonical URL of any linkable record (Tiv records keep their existing URLs). */
    public static function recordUrl(string $table, array $row): ?string
    {
        if ($table === 'admin_units' && ($row['unit_type'] ?? '') === 'ward') return null; // listed on the LGA page only
        switch ($table) {
            case 'historical_figures':
                return !empty($row['national_slug']) ? nigeria_url(self::path($table, $row))
                    : url(SeoHelper::canonicalSlugPath('historical-figure', (int) $row['id'], $row['english_name']));
            case 'timeline_events':
                return !empty($row['national_slug']) ? nigeria_url(self::path($table, $row))
                    : url(SeoHelper::canonicalSlugPath('timeline-event', (int) $row['id'], $row['title']));
            case 'content_items':  return url('content-item/' . (int) $row['id']);
            case 'tiv_festivals':  return url(SeoHelper::canonicalSlugPath('festival', (int) $row['id'], $row['tiv_name']));
            case 'tiv_foods':      return url(SeoHelper::canonicalSlugPath('food', (int) $row['id'], $row['tiv_name']));
        }
        return isset(self::SECTIONS[$table]) ? nigeria_url(self::path($table, $row)) : null;
    }

    /* ── Visibility & the indexability gate ────────────────────── */

    /** Published for visitors? (national people/events use their own status column) */
    public static function isPublished(string $table, array $row): bool
    {
        return in_array($table, ['historical_figures', 'timeline_events'], true)
            ? ($row['status'] ?? '') === 'published'
            : ($row['review_status'] ?? '') === 'published';
    }

    public static function proseWords(string $table, array $row): int
    {
        $text = (string) ($row[self::SUMMARY[$table] ?? 'summary'] ?? '');
        foreach (array_keys(self::PROSE[$table] ?? []) as $col) {
            $text .= ' ' . ($row[$col] ?? '');
        }
        // Unicode-aware (str_word_count splits words at letters like ọ, ẹ, ǹ).
        return count(preg_split('/[^\p{L}\p{M}\p{N}\'’-]+/u', strip_tags($text), -1, PREG_SPLIT_NO_EMPTY));
    }

    public static function sourceCount(string $table, int $id): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(DISTINCT source_id) FROM entity_sources WHERE entity_table = ? AND entity_id = ?');
        $stmt->execute([$table, $id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Google may index a record page only when it is published, has at least
     * MIN_WORDS of prose, at least one attached source and an assessed evidence
     * status. Everything else is served with noindex and left out of the sitemap.
     */
    public static function isIndexable(string $table, array $row, ?int $sources = null): bool
    {
        $evidence = $row['evidence_status'] ?? null;
        return self::isPublished($table, $row)
            && $evidence !== null && !in_array($evidence, self::NOINDEX_EVIDENCE, true)
            && self::proseWords($table, $row) >= self::MIN_WORDS
            && ($sources ?? self::sourceCount($table, (int) $row['id'])) > 0;
    }

    /** SQL fragment: published rows of a table (people/events: national records only). */
    public static function publishedSql(string $table, string $alias = 't'): string
    {
        if ($table === 'historical_figures' || $table === 'timeline_events') {
            return "{$alias}.status = 'published' AND {$alias}.national_slug IS NOT NULL";
        }
        return "{$alias}.review_status = 'published'";
    }

    /** Published rows of a table with their source counts, optionally filtered. */
    public static function published(string $table, string $where = '1 = 1', array $params = [], ?string $order = null): array
    {
        $order ??= 't.' . HeritageRegistry::NAME_COLUMN[$table];
        $stmt = self::db()->prepare(
            "SELECT t.*, (SELECT COUNT(DISTINCT es.source_id) FROM entity_sources es
                          WHERE es.entity_table = '{$table}' AND es.entity_id = t.id) AS source_count
             FROM {$table} t WHERE " . self::publishedSql($table) . " AND ({$where}) ORDER BY {$order}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * What each listing page shows: [table, extra WHERE]. Shared by the listing pages
     * and the sitemap so a sitemap URL is never a page the page itself marks noindex.
     */
    public const LISTINGS = [
        'states'         => ['admin_units', "t.unit_type IN ('state', 'federal_capital_territory') AND t.status = 'current'"],
        'states-history' => ['admin_units', "t.unit_type NOT IN ('lga', 'country', 'geopolitical_zone', 'ward') AND NOT (t.unit_type IN ('state', 'federal_capital_territory') AND t.status = 'current')"],
        'zones'          => ['admin_units', "t.unit_type = 'geopolitical_zone'"],
        'places'         => ['places', '1 = 1'],
        'ethnic-groups'  => ['ethnic_groups', 't.parent_id IS NULL'],
        'languages'      => ['languages', "t.lang_type IN ('language', 'family', 'branch')"],
        'kingdoms'       => ['polities', '1 = 1'],
        'culture'        => ['cultural_records', '1 = 1'],
        'periods'        => ['historical_periods', '1 = 1'],
        'people'         => ['historical_figures', '1 = 1'],
        'events'         => ['timeline_events', '1 = 1'],
    ];

    /** Home is indexable once this many national records are indexable. */
    public const MIN_HOME_ITEMS = 5;

    /** Number of indexable rows among $rows (rows from published(), with source_count). */
    public static function countIndexable(string $table, array $rows): int
    {
        return count(array_filter($rows, fn($r) => self::isIndexable($table, $r, (int) $r['source_count'])));
    }

    /**
     * Sitemap entries for the national section: [url, priority] for indexable record
     * pages, listing pages that pass MIN_LISTING_ITEMS, and the home page.
     */
    public static function sitemapEntries(): array
    {
        $out = [];
        $total = 0;
        foreach (array_keys(self::SECTIONS) as $table) {
            foreach (self::published($table, '1 = 1', [], 't.id') as $row) {
                if (!self::isIndexable($table, $row, (int) $row['source_count'])) continue;
                $total++;
                $out[] = [self::recordUrl($table, $row), '0.7'];
            }
        }
        foreach (['states', 'places', 'ethnic-groups', 'languages', 'kingdoms', 'culture', 'periods', 'people', 'events'] as $key) {
            [$table, $where] = self::LISTINGS[$key];
            $n = self::countIndexable($table, self::published($table, $where));
            if ($key === 'states') {
                [, $histWhere] = self::LISTINGS['states-history'];
                $n += self::countIndexable($table, self::published($table, $histWhere));
            }
            if ($n >= self::MIN_LISTING_ITEMS) $out[] = [nigeria_url($key), '0.6'];
        }
        $types = HeritageRegistry::get('cultural-records')['fields']['record_type']['options'];
        foreach (array_keys($types) as $type) {
            if (self::countIndexable('cultural_records', self::published('cultural_records', 't.record_type = ?', [$type])) >= self::MIN_LISTING_ITEMS) {
                $out[] = [nigeria_url('culture/' . str_replace('_', '-', $type)), '0.5'];
            }
        }
        if ($total >= self::MIN_HOME_ITEMS) array_unshift($out, [nigeria_url(), '0.8']);
        return $out;
    }

    /**
     * SQL condition: may visitors see this source (References pages, sitemap)?
     * A source stays public as before unless it is cited only by national research
     * that is not yet published — then it waits until something citing it is published,
     * so importing a research batch never puts pages online ahead of review.
     */
    public static function sourceIsPublicSql(string $s = 's'): string
    {
        [$national, $tivUse, $visible] = self::sourceConditions($s);
        return "(NOT ({$national}) OR {$tivUse} OR {$visible})";
    }

    /**
     * May search engines index this source's reference page? Sources the Tiv archive
     * uses, or not cited by national research, stay as before. A source cited only by
     * national records follows those records: indexable once one of them passes the
     * indexability gate (so thin national work never adds thin reference pages).
     */
    public static function sourceIsIndexable(int $id): bool
    {
        // Reference pages of sources created by research batches stay visible to readers
        // but are never submitted to search engines: they are citation records, and only
        // real content pages should grow the sitemap (AdSense thin-content rule, audit §15).
        // Sources that existed before keep their previous behaviour.
        return !in_array($id, self::batchCreatedSourceIds(), true);
    }

    /** Ids of sources created by research batches (recorded in each batch's summary by the importer). */
    private static function batchCreatedSourceIds(): array
    {
        static $ids = null;
        if ($ids !== null) return $ids;
        $ids = [];
        try {
            foreach (self::db()->query('SELECT summary FROM research_batches')->fetchAll(PDO::FETCH_COLUMN) as $sum) {
                if (preg_match('/Sources created by this batch \(ids\): ([\d,]+)/', (string) $sum, $m)) {
                    $ids = array_merge($ids, array_map('intval', explode(',', $m[1])));
                }
            }
        } catch (PDOException $e) {
            // research tables not migrated: nothing is held back
        }
        return $ids = array_values(array_unique($ids));
    }

    /** [cited by national research, used by Tiv content, cited by a visible record] SQL conditions. */
    private static function sourceConditions(string $s): array
    {
        $tivUse = implode(' OR ', array_map(fn($t) => "EXISTS (SELECT 1 FROM {$t} WHERE {$t}.source_id = {$s}.id)", [
            'daily_words', 'historical_figures', 'timeline_events', 'timeline_event_sources', 'tiv_animals',
            'tiv_festivals', 'tiv_foods', 'tiv_names', 'tiv_plants', 'tiv_proverbs']));

        $poly = ['entity_sources', 'entity_names', 'entity_statistics'];
        $national = implode(' OR ', array_merge(
            array_map(fn($t) => "EXISTS (SELECT 1 FROM {$t} x WHERE x.source_id = {$s}.id)", [...$poly, 'entity_relations', 'admin_unit_changes']),
            ["EXISTS (SELECT 1 FROM places x WHERE x.coords_source_id = {$s}.id)", "EXISTS (SELECT 1 FROM languages x WHERE x.vitality_source_id = {$s}.id)"]));
        // Only sources that a research batch created are ever held back; sources that
        // existed before (or were reused by a batch) keep their public status unchanged.
        $created = self::batchCreatedSourceIds();
        $national = $created ? "({$s}.id IN (" . implode(',', $created) . ") AND ({$national}))" : '0';

        // Cited by a record visitors can already see.
        $entityVisible = [];
        foreach (array_keys(self::SECTIONS) as $t) {
            $entityVisible[] = "(x.entity_table = '{$t}' AND EXISTS (SELECT 1 FROM {$t} e WHERE e.id = x.entity_id AND "
                . str_replace('t.', 'e.', in_array($t, ['historical_figures', 'timeline_events'], true) ? "t.status = 'published'" : "t.review_status = 'published'") . '))';
        }
        $visible = implode(' OR ', array_merge(
            array_map(fn($t) => "EXISTS (SELECT 1 FROM {$t} x WHERE x.source_id = {$s}.id AND (" . implode(' OR ', $entityVisible) . '))', $poly),
            ["EXISTS (SELECT 1 FROM entity_relations x WHERE x.source_id = {$s}.id AND x.review_status = 'published')",
             "EXISTS (SELECT 1 FROM admin_unit_changes x JOIN admin_units u ON u.id = x.to_unit_id WHERE x.source_id = {$s}.id AND u.review_status = 'published')"]));

        return [$national, $tivUse, $visible];
    }

    /** Anything published in the national section? (drives the nav item; cached 10 min) */
    public static function hasPublishedContent(): bool
    {
        return (bool) Cache::remember('nigeria_has_published', 600, function () {
            foreach (array_keys(self::SECTIONS) as $table) {
                $sql = 'SELECT 1 FROM ' . $table . ' t WHERE ' . self::publishedSql($table) . ' LIMIT 1';
                try {
                    if (self::db()->query($sql)->fetchColumn()) return 1;
                } catch (PDOException $e) {
                    return 0; // national tables not migrated yet: behave as "no content"
                }
            }
            return 0;
        });
    }

    /* ── Knowledge shown on a record page ──────────────────────── */

    public static function knowledge(string $table, int $id): array
    {
        $q = function (string $sql, array $p) { $s = self::db()->prepare($sql); $s->execute($p); return $s->fetchAll(); };

        $sources = $q("SELECT es.claim, es.page_section, es.stance, s.* FROM entity_sources es JOIN sources s ON s.id = es.source_id
                       WHERE es.entity_table = ? AND es.entity_id = ? ORDER BY es.id", [$table, $id]);
        // One entry per source, keeping every claim it supports.
        $bySource = [];
        foreach ($sources as $s) {
            $bySource[$s['id']] ??= $s + ['claims' => []];
            if ($s['claim']) $bySource[$s['id']]['claims'][] = $s['claim'] . ($s['stance'] === 'contradicts' ? ' (contradicts)' : '');
        }

        $relations = $q("SELECT r.*, rt.label, rt.inverse_label FROM entity_relations r JOIN relation_types rt ON rt.code = r.relation_type
                         WHERE r.review_status = 'published' AND ((r.from_table = ? AND r.from_id = ?) OR (r.to_table = ? AND r.to_id = ?))
                         ORDER BY rt.label, r.valid_from_year", [$table, $id, $table, $id]);
        $grouped = [];
        foreach ($relations as $r) {
            $outgoing = $r['from_table'] === $table && (int) $r['from_id'] === $id;
            $otherTable = $outgoing ? $r['to_table'] : $r['from_table'];
            $other = self::linkedRecord($otherTable, (int) ($outgoing ? $r['to_id'] : $r['from_id']));
            if (!$other) continue; // unpublished or missing: never shown
            $grouped[$outgoing ? $r['label'] : $r['inverse_label']][] = $r + ['other' => $other];
        }

        return [
            'sources'    => array_values($bySource),
            'names'      => $q("SELECT n.*, l.name AS language_name FROM entity_names n LEFT JOIN languages l ON l.id = n.language_id
                                WHERE n.entity_table = ? AND n.entity_id = ? ORDER BY n.valid_from_year IS NULL, n.valid_from_year, n.name", [$table, $id]),
            'statistics' => $q("SELECT st.*, s.title AS source_title, s.id AS source_ref FROM entity_statistics st JOIN sources s ON s.id = st.source_id
                                WHERE st.entity_table = ? AND st.entity_id = ? ORDER BY st.metric, st.reference_year DESC", [$table, $id]),
            'relations'  => $grouped,
            'attributes' => $table === 'cultural_records'
                ? $q("SELECT a.*, s.title AS source_title, s.id AS source_ref FROM cultural_record_attributes a LEFT JOIN sources s ON s.id = a.source_id
                      WHERE a.cultural_record_id = ? ORDER BY a.attribute, a.sort_order, a.id", [$id]) : [],
        ];
    }

    /** A related record as [name, url, type label] if visitors may see it, else null. */
    public static function linkedRecord(string $table, int $id): ?array
    {
        if (!isset(HeritageRegistry::NAME_COLUMN[$table])) return null;
        $stmt = self::db()->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $visible = match ($table) {
            'historical_figures', 'timeline_events', 'content_items' => ($row['status'] ?? '') === 'published',
            'tiv_festivals', 'tiv_foods' => true,
            default => ($row['review_status'] ?? '') === 'published',
        };
        // Records marked sensitive are never linked publicly; types without public pages yet
        // (communities, oral histories) are skipped until they have one.
        if (!$visible || (($row['sensitivity'] ?? 'public') !== 'public')) return null;
        $url = self::recordUrl($table, $row);
        if ($url === null) return null;
        return ['name' => $row[HeritageRegistry::NAME_COLUMN[$table]], 'url' => $url,
                'type' => HeritageRegistry::LINKABLE[$table] ?? $table];
    }

    /* ── Tiv Heritage collection ↔ national Tiv records ────────── */

    /**
     * The published national Tiv people and Tiv language records, as linkedRecord() entries,
     * for the "Tiv in Nigeria Heritage" link on Tiv collection pages. Empty when neither is published.
     */
    public static function tivNationalRecords(): array
    {
        $out = [];
        foreach (['ethnic_groups', 'languages'] as $table) {
            $stmt = self::db()->prepare("SELECT id FROM {$table} WHERE slug = 'tiv' LIMIT 1");
            try { $stmt->execute(); } catch (PDOException $e) { return []; }
            if (($id = $stmt->fetchColumn()) && ($rec = self::linkedRecord($table, (int) $id))) $out[$table] = $rec;
        }
        return $out;
    }

    /**
     * Collection-level links from a national Tiv record (people or language) to the matching
     * sections of the Tiv Heritage collection, with item counts. These are links between our
     * own collections, not sourced claims, so they are not stored as relations.
     */
    public static function tivCollectionLinks(string $table, array $row): array
    {
        if (($row['slug'] ?? '') !== 'tiv' || !in_array($table, ['ethnic_groups', 'languages'], true)) return [];
        foreach (['ContentItem', 'HistoricalFigure', 'TimelineEvent'] as $m) require_once BASE_PATH . "/models/{$m}.php";
        $count = fn(string $sql) => (int) self::db()->query($sql)->fetchColumn();
        $articles = fn(string $section) => $count("SELECT COUNT(*) FROM content_items t WHERE t.status = 'published' AND t.section = '{$section}' AND "
            . (new ContentItem())->publicScopeSql('t'));
        $links = $table === 'ethnic_groups' ? [
            ['History articles', 'history', $articles('history')],
            ['Culture articles', 'culture', $articles('culture')],
            ['Festivals', 'archive/festivals', $count('SELECT COUNT(*) FROM tiv_festivals')],
            ['Foods', 'archive/foods', $count('SELECT COUNT(*) FROM tiv_foods')],
            ['Proverbs', 'archive/proverbs', $count('SELECT COUNT(*) FROM tiv_proverbs')],
            ['Personal names', 'archive/names', $count('SELECT COUNT(*) FROM tiv_names')],
            ['Historical figures', 'historical-figures', $count("SELECT COUNT(*) FROM historical_figures t WHERE t.status = 'published' AND "
                . (new HistoricalFigure())->publicScopeSql('t'))],
            ['Timeline of Tiv history', 'timeline', $count("SELECT COUNT(*) FROM timeline_events t WHERE t.status = 'published' AND "
                . (new TimelineEvent())->publicScopeSql('t'))],
        ] : [
            ['Tiv–English dictionary', 'archive/words', null],
            ['Language section (alphabet, grammar, lessons)', 'language', null],
            ['Proverbs', 'archive/proverbs', $count('SELECT COUNT(*) FROM tiv_proverbs')],
            ['Personal names', 'archive/names', $count('SELECT COUNT(*) FROM tiv_names')],
        ];
        $out = [];
        foreach ($links as [$name, $path, $n]) {
            if ($n === 0) continue; // an empty section is not worth a link
            $out[] = ['name' => $name, 'url' => url($path), 'note' => $n ? number_format($n) . ($n === 1 ? ' item' : ' items') : null];
        }
        return $out;
    }

    /** Public explanation of each of the six evidence levels (NIGERIA_STEP5 §1.1). */
    public const EVIDENCE_LEVEL_EXPLAIN = [
        'verified' => 'Supported by an official source, or by two or more independent sources that agree.',
        'well_documented' => 'Supported by credible sources, but not yet independently confirmed.',
        'reported' => 'Found in a credible source; further corroboration is welcome.',
        'needs_corroboration' => 'The evidence is thin or incomplete; more sources are being sought.',
        'disputed' => 'Credible sources or community accounts disagree; the differing accounts are noted.',
        'uncertain' => 'There is not yet enough information to classify this responsibly.',
    ];

    /** Plain public wording for settlement status (how a group is present in a place). */
    public const SETTLEMENT_PUBLIC = [
        'indigenous_core' => 'indigenous homeland', 'indigenous_shared' => 'indigenous, shared area',
        'historically_present' => 'historically present', 'significant_contemporary' => 'significant present-day presence',
        'migrant_community' => 'migrant community', 'mixed_community' => 'mixed community', 'disputed' => 'disputed',
    ];

    /** Plain public label for a source's reliability tier (NIGERIA_STEP5 §4.1). */
    public const SOURCE_TIER_LABEL = [
        1 => 'Official source', 2 => 'Academic source', 3 => 'Reference source', 4 => 'Community source', 5 => 'General web source',
    ];

    /**
     * Evidence badge with a plain-language explanation as its tooltip. The six-level
     * evidence_level is shown when assessed; otherwise the earlier evidence_status.
     */
    public static function evidenceBadge(?string $status, ?string $level = null): string
    {
        if ($level && isset(self::EVIDENCE_LEVEL_EXPLAIN[$level])) {
            $class = match ($level) {
                'verified', 'well_documented' => 'ng-ev-strong',
                'disputed', 'uncertain' => 'ng-ev-weak',
                default => 'ng-ev-medium',
            };
            return '<span class="ng-evidence ' . $class . '" title="' . e(self::EVIDENCE_LEVEL_EXPLAIN[$level]) . '">Evidence: '
                . e(HeritageRegistry::EVIDENCE_LEVEL[$level]) . '</span>';
        }
        if (!$status) return '';
        $explain = [
            'verified' => 'Confirmed by authoritative sources.',
            'well_documented' => 'Documented consistently across reliable sources.',
            'multiple_sources' => 'Supported by more than one independent source.',
            'single_reliable_source' => 'Supported by one reliable source; further corroboration welcome.',
            'community_source' => 'Provided by community members; not yet independently confirmed.',
            'oral_tradition' => 'Preserved as oral tradition — a cultural record, not a proven historical fact.',
            'scholarly_interpretation' => 'A scholarly interpretation; other scholars may differ.',
            'disputed' => 'Sources disagree on this record; the differing accounts are noted.',
            'needs_corroboration' => 'Needs further sources before it can be relied on.',
            'unverified' => 'Not yet verified.',
            'outdated' => 'May be out of date; newer information is being sought.',
        ];
        $class = match ($status) {
            'verified', 'well_documented', 'multiple_sources', 'single_reliable_source' => 'ng-ev-strong',
            'disputed', 'outdated', 'unverified' => 'ng-ev-weak',
            default => 'ng-ev-medium',
        };
        return '<span class="ng-evidence ' . $class . '" title="' . e($explain[$status] ?? '') . '">Evidence: '
            . e(HeritageRegistry::EVIDENCE[$status] ?? $status) . '</span>';
    }

    /** Evidence label in words (level when assessed, else the earlier status), for text such as AI answers. */
    public static function evidenceWords(?string $status, ?string $level = null): ?string
    {
        if ($level && isset(HeritageRegistry::EVIDENCE_LEVEL[$level])) return mb_strtolower(HeritageRegistry::EVIDENCE_LEVEL[$level]);
        return $status ? mb_strtolower(HeritageRegistry::EVIDENCE[$status] ?? $status) : null;
    }

    /** Plain text (blank-line separated) as escaped paragraphs. */
    public static function paragraphs(?string $text): string
    {
        $parts = preg_split('/\R\s*\R/', trim((string) $text));
        return implode('', array_map(fn($p) => '<p>' . nl2br(e(trim($p))) . '</p>', array_filter($parts, fn($p) => trim($p) !== '')));
    }

    /** Creative Commons deeds for the licences the image refill accepts (owner decision 3 Oct 2026: free licences only). */
    public const LICENCE_URL = [
        'CC0'           => 'https://creativecommons.org/publicdomain/zero/1.0/',
        'Public domain' => null,
        'CC BY 2.0'     => 'https://creativecommons.org/licenses/by/2.0/',
        'CC BY 3.0'     => 'https://creativecommons.org/licenses/by/3.0/',
        'CC BY 4.0'     => 'https://creativecommons.org/licenses/by/4.0/',
        'CC BY-SA 2.0'  => 'https://creativecommons.org/licenses/by-sa/2.0/',
        'CC BY-SA 3.0'  => 'https://creativecommons.org/licenses/by-sa/3.0/',
        'CC BY-SA 4.0'  => 'https://creativecommons.org/licenses/by-sa/4.0/',
    ];

    /**
     * Credit for a record's hero image: the published media_assets row that both depicts this record
     * (media_links) and is the file in its `image` column. Null when the image has no media row.
     *
     * @return array{title:string, creator:?string, licence:?string, licence_url:?string, source_url:?string, source_label:string}|null
     */
    public static function imageCredit(string $table, int $id, ?string $image): ?array
    {
        if (!$image) return null;
        $s = self::db()->prepare("SELECT m.title, m.creator, m.licence, m.external_url FROM media_assets m
            JOIN media_links l ON l.media_id = m.id AND l.entity_table = ? AND l.entity_id = ? AND l.role = 'depicts'
            WHERE m.file_path = ? AND m.review_status = 'published' LIMIT 1");
        $s->execute([$table, $id, $image]);
        $m = $s->fetch();
        if (!$m) return null;
        $host = (string) parse_url((string) $m['external_url'], PHP_URL_HOST);
        return ['title' => $m['title'], 'creator' => $m['creator'], 'licence' => $m['licence'],
                'licence_url' => self::LICENCE_URL[$m['licence']] ?? null, 'source_url' => $m['external_url'],
                'source_label' => str_ends_with($host, 'wikimedia.org') ? 'Wikimedia Commons' : 'source'];
    }

    /** Human-readable date from a year/text/precision triple, e.g. "c. 1850", "3 February 1976". */
    public static function dateLabel($year, ?string $text, ?string $precision = null, ?string $date = null): ?string
    {
        if ($text) return $text;
        if ($date) return date('j F Y', strtotime($date));
        if ($year === null || $year === '') return null;
        $y = (int) $year < 0 ? abs((int) $year) . ' BCE' : (string) (int) $year;
        return match ($precision) { 'circa' => "c. {$y}", 'decade' => "{$y}s", default => $y };
    }
}
