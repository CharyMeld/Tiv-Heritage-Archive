<?php
/**
 * Public Nigeria Heritage section (/nigeria/…).
 *
 * Only published records are shown (moderators may preview drafts, always noindex).
 * Every page goes through HeritagePublic's indexability gate: thin, unsourced or
 * unverified pages are served with noindex and kept out of the sitemap.
 * All links are built with nigeria_url() so the section can later move to a subdomain.
 */

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/HeritagePublic.php';

class NigeriaController extends Controller
{
    private PDO $db;
    /** True while an editor previews an unpublished record: unpublished links show as plain, marked names. */
    private bool $previewMode = false;
    private ?array $zoneLinksCache = null;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /* ── Home ──────────────────────────────────────────────────── */

    public function home(): void
    {
        $sections = [];
        $indexable = 0;
        foreach (HeritagePublic::SECTIONS as $table => [$seg, $plural]) {
            $rows = HeritagePublic::published($table, $table === 'admin_units' ? "t.unit_type <> 'ward'" : '1 = 1', [], 't.id');
            $indexable += count(array_filter($rows, fn($r) => HeritagePublic::isIndexable($table, $r, (int) $r['source_count'])));
            $sections[$table] = ['path' => $seg, 'label' => $plural, 'count' => count($rows)];
        }
        $crumbs = [['label' => 'Home', 'url' => url('/')], ['label' => 'Nigeria Heritage', 'url' => null]];
        $this->page('nigeria/home', [
            'title'       => 'Nigeria Heritage Archive — The Historical, Cultural & Living Memory of Nigeria',
            'description' => 'A source-aware archive of Nigeria’s states, peoples, languages, places, events and cultural heritage, built from cited research and connected to the Tiv Heritage Archive.',
            'sections'    => $sections,
            'recent'      => $this->recentRecords(),
        ], $crumbs, nigeria_url(), $indexable < HeritagePublic::MIN_HOME_ITEMS);
    }

    /* ── States, LGAs and other administrative units ───────────── */

    public function states(): void
    {
        $states = HeritagePublic::published('admin_units', HeritagePublic::LISTINGS['states'][1]);
        $historical = HeritagePublic::published('admin_units', HeritagePublic::LISTINGS['states-history'][1], [], 't.created_on IS NULL, t.created_on, t.name');
        $groups = [];
        foreach ($states as $s) $groups[$s['geopolitical_zone'] ?: 'Zone not recorded'][] = $s;
        ksort($groups);
        $groupsWithHistory = $groups + ($historical ? ['Historical administrative units' => $historical] : []);
        $this->listing('admin_units', 'States & the Federal Capital Territory',
            'Nigeria’s 36 states and the Federal Capital Territory, with their creation, predecessor units, LGAs, peoples and history — each entry backed by cited sources.',
            $groupsWithHistory, nigeria_url('states'), [['label' => 'States & FCT', 'url' => null]], $this->zoneLinks());
    }

    /** Published geopolitical zone records: zone name => URL. */
    private function zoneLinks(): array
    {
        if ($this->zoneLinksCache !== null) return $this->zoneLinksCache;
        $out = [];
        foreach (HeritagePublic::published('admin_units', HeritagePublic::LISTINGS['zones'][1]) as $z) {
            $out[$z['name']] = HeritagePublic::recordUrl('admin_units', $z);
        }
        return $this->zoneLinksCache = $out;
    }

    public function state(string $slug): void
    {
        $row = $this->findOne('admin_units', "unit_type IN ('state', 'federal_capital_territory') AND slug = ?", [$slug]);
        if (!$row) { $this->notFound('states/' . $slug); return; }
        $this->record('admin_units', $row, [['label' => 'States & FCT', 'url' => nigeria_url('states')]]);
    }

    public function lga(string $state, string $slug): void
    {
        $parent = $this->findOne('admin_units', "unit_type IN ('state', 'federal_capital_territory') AND slug = ?", [$state], false);
        if (!$parent) {
            // The state was renamed: follow its redirect, keeping the LGA part of the path.
            $stmt = $this->db->prepare("SELECT a.slug FROM slug_redirects r JOIN admin_units a ON a.id = r.entity_id
                                        WHERE r.entity_table = 'admin_units' AND r.old_path = ? AND a.review_status = 'published' LIMIT 1");
            $stmt->execute(['states/' . $state]);
            if ($newState = $stmt->fetchColumn()) {
                redirect301(nigeria_url("states/{$newState}/lgas/{$slug}"));
                return;
            }
        }
        $row = $parent ? $this->findOne('admin_units', "unit_type = 'lga' AND parent_id = ? AND slug = ?", [$parent['id'], $slug]) : null;
        if (!$row) { $this->notFound("states/{$state}/lgas/{$slug}"); return; }
        $this->record('admin_units', $row, [
            ['label' => 'States & FCT', 'url' => nigeria_url('states')],
            ['label' => $parent['name'], 'url' => nigeria_url('states/' . $parent['slug'])],
        ]);
    }

    public function unit(string $slug): void
    {
        // Wards have no page of their own: they are listed on their LGA's page.
        $row = $this->findOne('admin_units', "unit_type NOT IN ('state', 'federal_capital_territory', 'lga', 'ward') AND slug = ?", [$slug]);
        if (!$row) { $this->notFound('units/' . $slug); return; }
        $this->record('admin_units', $row, [['label' => 'States & FCT', 'url' => nigeria_url('states')]]);
    }

    /* ── Section listings and records ──────────────────────────── */

    /** GET nigeria/{section} for places, ethnic-groups, languages, kingdoms, periods, people, events. */
    public function section(string $section): void
    {
        $table = $this->tableForSection($section);
        [, $plural] = HeritagePublic::SECTIONS[$table];
        $intro = [
            'places'             => 'Towns, cities, heritage sites, archaeological sites, museums and archives across Nigeria.',
            'ethnic_groups'      => 'Nigeria’s peoples: where they live, the languages they speak, their history and their cultural institutions.',
            'languages'          => 'Nigeria’s languages and dialects, where they are spoken and how they are written, taught and preserved.',
            'polities'           => 'Kingdoms, emirates, chiefdoms and traditional institutions, past and present.',
            'historical_periods' => 'The periods historians use to describe Nigeria’s past.',
            'historical_figures' => 'Nigerian historical and cultural figures, each linked to the sources and places of their lives.',
            'timeline_events'    => 'Events in Nigeria’s history, in chronological order — with announced and planned events kept apart from what has happened.',
        ][$table];

        switch ($table) {
            case 'places':
                $opts = HeritageRegistry::get('places')['fields']['place_type']['options'];
                $groups = [];
                foreach (HeritagePublic::published($table) as $r) $groups[$opts[$r['place_type']] ?? 'Other'][] = $r;
                ksort($groups);
                break;
            case 'languages':
                $groups = ['Languages' => HeritagePublic::published($table, "t.lang_type = 'language'"),
                           'Language families' => HeritagePublic::published($table, "t.lang_type IN ('family', 'branch')")];
                break;
            case 'historical_figures':
                $groups = ['' => HeritagePublic::published($table, '1 = 1', [], 't.english_name')];
                break;
            case 'timeline_events':
                $groups = ['' => HeritagePublic::published($table, '1 = 1', [], 't.year IS NULL, t.year, t.id')];
                break;
            case 'historical_periods':
                $groups = ['' => HeritagePublic::published($table, '1 = 1', [], 't.start_year IS NULL, t.start_year')];
                break;
            case 'ethnic_groups':
                $groups = ['' => HeritagePublic::published($table, HeritagePublic::LISTINGS['ethnic-groups'][1])];
                break;
            default:
                $groups = ['' => HeritagePublic::published($table)];
        }
        $groups = array_filter($groups);
        $this->listing($table, $plural, $intro, $groups, nigeria_url($section), [['label' => $plural, 'url' => null]]);
    }

    /** GET nigeria/{section}/{slug} */
    public function show(string $section, string $slug): void
    {
        $table = $this->tableForSection($section);
        $column = in_array($table, ['historical_figures', 'timeline_events'], true) ? 'national_slug' : 'slug';
        $row = $this->findOne($table, "{$column} = ?", [$slug]);
        if (!$row) { $this->notFound("{$section}/{$slug}"); return; }
        $this->record($table, $row, [['label' => HeritagePublic::SECTIONS[$table][1], 'url' => nigeria_url($section)]]);
    }

    public function culture(): void
    {
        $opts = HeritageRegistry::get('cultural-records')['fields']['record_type']['options'];
        $groups = [];
        foreach (HeritagePublic::published('cultural_records') as $r) $groups[$opts[$r['record_type']] ?? 'Other'][] = $r;
        ksort($groups);
        $this->listing('cultural_records', 'Culture & Heritage',
            'Festivals, food, clothing, music, dance, crafts, ceremonies and traditional knowledge — with oral traditions clearly marked as such.',
            $groups, nigeria_url('culture'), [['label' => 'Culture & Heritage', 'url' => null]]);
    }

    public function cultureType(string $type): void
    {
        $key = str_replace('-', '_', $type);
        $opts = HeritageRegistry::get('cultural-records')['fields']['record_type']['options'];
        if (!isset($opts[$key])) { $this->notFound('culture/' . $type); return; }
        $this->listing('cultural_records', $opts[$key], null,
            ['' => HeritagePublic::published('cultural_records', 't.record_type = ?', [$key])], nigeria_url('culture/' . $type),
            [['label' => 'Culture & Heritage', 'url' => nigeria_url('culture')], ['label' => $opts[$key], 'url' => null]]);
    }

    public function cultureRecord(string $type, string $slug): void
    {
        $key = str_replace('-', '_', $type);
        $row = $this->findOne('cultural_records', 'record_type = ? AND slug = ?', [$key, $slug]);
        if (!$row) { $this->notFound("culture/{$type}/{$slug}"); return; }
        $opts = HeritageRegistry::get('cultural-records')['fields']['record_type']['options'];
        $this->record('cultural_records', $row, [
            ['label' => 'Culture & Heritage', 'url' => nigeria_url('culture')],
            ['label' => $opts[$key], 'url' => nigeria_url('culture/' . $type)],
        ]);
    }

    /* ── Rendering ─────────────────────────────────────────────── */

    private function listing(string $table, string $heading, ?string $intro, array $groups, string $canonical, array $trail, array $groupLinks = []): void
    {
        $count = 0;
        $indexable = 0;
        foreach ($groups as $rows) {
            foreach ($rows as $r) {
                $count++;
                if (HeritagePublic::isIndexable($table, $r, (int) $r['source_count'])) $indexable++;
            }
        }
        $crumbs = array_merge([['label' => 'Home', 'url' => url('/')], ['label' => 'Nigeria Heritage', 'url' => nigeria_url()]], $trail);
        $this->page('nigeria/list', [
            'title'       => $heading . ' | Nigeria Heritage Archive',
            'description' => SeoHelper::truncate($intro ?? "{$heading} documented in the Nigeria Heritage Archive, with cited sources."),
            'heading'     => $heading,
            'intro'       => $intro,
            'table'       => $table,
            'groups'      => $groups,
            'groupLinks'  => $groupLinks,
            'count'       => $count,
        ], $crumbs, $canonical, $indexable < HeritagePublic::MIN_LISTING_ITEMS);
    }

    private function record(string $table, array $row, array $trail): void
    {
        $id = (int) $row['id'];
        $published = HeritagePublic::isPublished($table, $row);
        $this->previewMode = !$published;
        $knowledge = HeritagePublic::knowledge($table, $id);
        $name = $row[HeritageRegistry::NAME_COLUMN[$table]];
        $canonical = HeritagePublic::recordUrl($table, $row);
        $indexable = HeritagePublic::isIndexable($table, $row, count($knowledge['sources']));
        $summary = $row[HeritagePublic::SUMMARY[$table] ?? 'summary'] ?? null;
        $label = $this->typeLabel($table, $row);

        $crumbs = array_merge([['label' => 'Home', 'url' => url('/')], ['label' => 'Nigeria Heritage', 'url' => nigeria_url()]],
            $trail, [['label' => $name, 'url' => null]]);
        $description = SeoHelper::describe([$summary, $row['description'] ?? $row['biography'] ?? null],
            "{$name} — {$label} in the Nigeria Heritage Archive, with cited sources.");

        $this->page('nigeria/record', [
            'title'       => "{$name} — {$label} | Nigeria Heritage Archive",
            'description' => $description,
            'table'       => $table,
            'item'        => $row,
            'name'        => $name,
            'typeLabel'   => $label,
            'summary'     => $summary,
            'facts'       => $this->facts($table, $row),
            'prose'       => array_filter(HeritagePublic::PROSE[$table] ?? [], fn($col) => !empty($row[$col]), ARRAY_FILTER_USE_KEY),
            'children'    => $this->children($table, $row),
            'tivLinks'    => HeritagePublic::tivCollectionLinks($table, $row),
            'history'     => $table === 'admin_units' ? $this->unitHistory($id) : [],
            'preview'     => !$published,
            'ogImage'     => !empty($row['image']) ? SeoHelper::ogImage($row['image']) : null,
            'imageCredit' => HeritagePublic::imageCredit($table, $id, $row['image'] ?? null),
            'jsonLd'      => SeoHelper::jsonLd([$this->schema($table, $row, $name, $description, $canonical), SeoHelper::breadcrumbListSchema($crumbs)]),
        ] + $knowledge, $crumbs, $canonical, !$indexable);
    }

    private function page(string $view, array $data, array $crumbs, string $canonical, bool $noindex): void
    {
        $this->render($view, $data + [
            'breadcrumb' => $crumbs,
            'ogUrl'      => $canonical,
            'noindex'    => $noindex,
            'bodyClass'  => 'section-nigeria',
            'extraCss'   => '<link rel="stylesheet" href="' . asset('css/nigeria.css') . '">',
            'jsonLd'     => $data['jsonLd'] ?? SeoHelper::jsonLd([SeoHelper::breadcrumbListSchema($crumbs)]),
        ]);
    }

    /* ── Data helpers ──────────────────────────────────────────── */

    /** One row; unpublished rows only for moderators (preview). */
    private function findOne(string $table, string $where, array $params, bool $allowPreview = true): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$where} LIMIT 1");
        $stmt->execute($params);
        $row = $stmt->fetch();
        if (!$row) return null;
        if (!HeritagePublic::isPublished($table, $row) && !($allowPreview && is_moderator())) return null;
        return $row;
    }

    private function tableForSection(string $section): string
    {
        foreach (HeritagePublic::SECTIONS as $table => [$seg]) {
            if ($seg === $section) return $table;
        }
        $this->notFound($section);
        exit;
    }

    /** 404, or a 301 when the path belonged to a record whose slug changed. */
    private function notFound(string $path): void
    {
        $stmt = $this->db->prepare('SELECT entity_table, entity_id FROM slug_redirects WHERE old_path = ? LIMIT 1');
        $stmt->execute([$path]);
        if ($r = $stmt->fetch()) {
            $row = $this->db->query("SELECT * FROM {$r['entity_table']} WHERE id = " . (int) $r['entity_id'])->fetch();
            if ($row && HeritagePublic::isPublished($r['entity_table'], $row)) {
                redirect301(HeritagePublic::recordUrl($r['entity_table'], $row));
                return;
            }
        }
        http_response_code(404);
        $this->render('errors/404', ['title' => 'Not Found', 'noindex' => true]);
    }

    private function typeLabel(string $table, array $row): string
    {
        $col = ['admin_units' => 'unit_type', 'places' => 'place_type', 'languages' => 'lang_type',
                'polities' => 'polity_type', 'cultural_records' => 'record_type'][$table] ?? null;
        $key = HeritageRegistry::keyForTable($table);
        if ($col && $key) return HeritageRegistry::get($key)['fields'][$col]['options'][$row[$col]] ?? HeritagePublic::SECTIONS[$table][2];
        return HeritagePublic::SECTIONS[$table][2];
    }

    private function link(string $table, $id): ?array
    {
        if (!$id) return null;
        $rec = HeritagePublic::linkedRecord($table, (int) $id);
        if ($rec || !$this->previewMode || !isset(HeritageRegistry::NAME_COLUMN[$table])) return $rec;
        // Editor preview: name the unpublished record and link to its admin page.
        $stmt = $this->db->prepare('SELECT ' . HeritageRegistry::NAME_COLUMN[$table] . " FROM {$table} WHERE id = ?");
        $stmt->execute([(int) $id]);
        $name = $stmt->fetchColumn();
        $key = HeritageRegistry::keyForTable($table);
        return $name && $key ? ['name' => $name . ' (unpublished)', 'url' => url("admin/heritage/{$key}/" . (int) $id . '/edit'),
                                'type' => HeritageRegistry::LINKABLE[$table] ?? $table] : null;
    }

    /** Label => value (string or ['name','url']) key facts per record type. */
    private function facts(string $table, array $r): array
    {
        $p = fn($y, $t, $pr = null, $d = null) => HeritagePublic::dateLabel($y, $t, $pr, $d);
        $opt = fn(string $key, string $field, $v) => $v === null ? null : (HeritageRegistry::get($key)['fields'][$field]['options'][$v] ?? $v);
        $f = match ($table) {
            'admin_units' => [
                'Type' => $opt('admin-units', 'unit_type', $r['unit_type']),
                'Official name' => $r['official_name'],
                'Part of' => $this->link('admin_units', $r['parent_id']),
                'Capital' => $this->link('places', $r['capital_place_id']),
                'Headquarters' => $this->link('places', $r['headquarters_place_id'] ?? null),
                'Geopolitical zone' => $r['unit_type'] === 'geopolitical_zone' ? null
                    : ($r['geopolitical_zone'] ? ($this->zoneLinks()[$r['geopolitical_zone']] ?? null
                        ? ['name' => $r['geopolitical_zone'], 'url' => $this->zoneLinks()[$r['geopolitical_zone']]] : $r['geopolitical_zone']) : null),
                'Created' => $p(null, $r['created_on_text'], $r['created_precision'], $r['created_on']),
                'Ended' => $p(null, $r['ended_on_text'], $r['ended_precision'], $r['ended_on']),
                'Status' => $r['status'] !== 'current' ? $opt('admin-units', 'status', $r['status']) : null,
                'ISO code' => $r['iso_code'],
                'Official code' => $r['official_code'] ?? null,
                'Coordinates' => ($r['latitude'] ?? null) !== null && !empty($r['coords_source_id']) ? round((float) $r['latitude'], 4) . ', ' . round((float) $r['longitude'], 4) : null,
            ],
            'places' => [
                'Type' => $opt('places', 'place_type', $r['place_type']),
                'Located in' => $this->link('admin_units', $r['admin_unit_id']),
                'Status' => $r['status'] !== 'existing' ? $opt('places', 'status', $r['status']) : null,
                'Coordinates' => $r['latitude'] !== null && $r['coords_source_id'] ? round((float) $r['latitude'], 4) . ', ' . round((float) $r['longitude'], 4) : null,
                'Protection' => $opt('places', 'protection_status', $r['protection_status'] ?? null),
                'Condition' => ($c = $opt('places', 'condition_status', $r['condition_status'] ?? null))
                    ? $c . (!empty($r['condition_as_of']) ? ' (as of ' . date('F Y', strtotime($r['condition_as_of'])) . ')' : '') : null,
            ],
            'ethnic_groups' => ['Self-designation' => $r['endonym'], 'Sub-group of' => $this->link('ethnic_groups', $r['parent_id']),
                                'Level' => ($r['group_level'] ?? 'ethnic_group') !== 'ethnic_group' ? $opt('ethnic-groups', 'group_level', $r['group_level']) : null],
            'languages' => [
                'Type' => $opt('languages', 'lang_type', $r['lang_type']),
                'Belongs to' => $this->link('languages', $r['parent_id']),
                'ISO 639-3' => $r['iso639_3'], 'Glottocode' => $r['glottocode'],
                'Writing system' => $r['writing_system'],
                'Vitality' => $r['vitality'] && $r['vitality_source_id'] ? $r['vitality']
                    : (!empty($r['vitality_status']) && $r['vitality_source_id'] ? $opt('languages', 'vitality_status', $r['vitality_status']) : null),
            ],
            'polities' => [
                'Type' => $opt('polities', 'polity_type', $r['polity_type']),
                'Seat' => $this->link('places', $r['seat_place_id']),
                'Founded' => $p($r['founded_year'], $r['founded_text'], $r['founded_precision']),
                'Ended' => $p($r['ended_year'], $r['ended_text'], $r['ended_precision']),
                'Still exists' => $r['is_extant'] === null ? null : ((int) $r['is_extant'] ? 'Yes' : 'No'),
            ],
            'cultural_records' => [
                'Type' => $opt('cultural-records', 'record_type', $r['record_type']),
                'Nature of the record' => $opt('cultural-records', 'nature', $r['nature']),
                'Local name' => $r['local_name'] ? $r['local_name'] . (($l = $this->link('languages', $r['language_id'])) ? ' (' . $l['name'] . ')' : '') : null,
                'Category' => $opt('cultural-records', 'cultural_category', $r['cultural_category'] ?? null),
                'When observed' => $r['timing'],
                'Season' => $r['season'] ?? null,
                'Usual months' => !empty($r['month_from']) ? date('F', mktime(0, 0, 0, (int) $r['month_from'], 1))
                    . (!empty($r['month_to']) && $r['month_to'] != $r['month_from'] ? '–' . date('F', mktime(0, 0, 0, (int) $r['month_to'], 1)) : '') : null,
                'Current status' => $opt('cultural-records', 'current_status', $r['current_status'] ?? null),
                'Scope' => $opt('cultural-records', 'scope_level', $r['scope_level'] ?? null),
            ],
            'historical_periods' => [
                'Scope' => $opt('historical-periods', 'scope', $r['scope']),
                'From' => $p($r['start_year'], null, $r['date_precision']),
                'Until' => $p($r['end_year'], null, $r['date_precision']),
            ],
            'historical_figures' => [
                'Known as' => $r['title'], 'Born' => $r['date_of_birth'], 'Place of birth' => $r['place_of_birth'],
                'Died' => $r['date_of_death'], 'Occupation' => $r['occupation'], 'Field' => $r['category'],
            ],
            'timeline_events' => [
                'Date' => $r['event_date'] ?: $p($r['year'], null, $r['date_precision']),
                'Status' => HeritageRegistry::TEMPORAL[$r['temporal_status']] ?? null,
                'Location' => $r['location'],
                'Period' => $this->link('historical_periods', $r['period_id']),
            ],
            default => [],
        };
        return array_filter($f, fn($v) => $v !== null && $v !== '');
    }

    /** Published child records shown on a page: [heading => [[name, url, note]]]. */
    private function children(string $table, array $r): array
    {
        $id = (int) $r['id'];
        $map = fn(string $t, array $rows, ?callable $note = null) => array_map(fn($row) => [
            'name' => $row[HeritageRegistry::NAME_COLUMN[$t]], 'url' => HeritagePublic::recordUrl($t, $row), 'note' => $note ? $note($row) : null,
        ], $rows);
        $out = [];
        if ($table === 'admin_units' && $r['unit_type'] === 'geopolitical_zone') {
            $out['States in this zone'] = $map('admin_units', HeritagePublic::published('admin_units',
                "t.unit_type IN ('state', 'federal_capital_territory') AND t.status = 'current' AND t.geopolitical_zone = ?", [$r['name']]));
        } elseif ($table === 'admin_units') {
            $out['Local Government Areas'] = $map('admin_units', HeritagePublic::published('admin_units', "t.unit_type = 'lga' AND t.parent_id = ?", [$id]));
            $out['Units within it'] = $map('admin_units', HeritagePublic::published('admin_units', "t.unit_type NOT IN ('lga', 'ward') AND t.parent_id = ?", [$id]));
            if ($r['unit_type'] === 'lga') {
                // Wards (INEC registration areas) are listed here, without pages of their own.
                $stmt = $this->db->prepare("SELECT u.name, u.official_code,
                        (SELECT st.value_low FROM entity_statistics st WHERE st.entity_table = 'admin_units' AND st.entity_id = u.id
                           AND st.metric = 'other' AND st.notes LIKE 'Polling units%' ORDER BY st.reference_year DESC LIMIT 1) AS pus
                    FROM admin_units u WHERE u.unit_type = 'ward' AND u.parent_id = ? AND u.review_status = 'published'
                    ORDER BY u.official_code, u.name");
                $stmt->execute([$id]);
                $out['Wards (INEC registration areas)'] = array_map(fn($w) => ['name' => $w['name'], 'url' => null,
                    'note' => trim(($w['official_code'] ? 'code ' . $w['official_code'] : '') . ($w['pus'] ? ' · ' . $w['pus'] . ' polling units' : ''), ' ·')],
                    $stmt->fetchAll());
            }
            $out['Places'] = $map('places', HeritagePublic::published('places', 't.admin_unit_id = ?', [$id]));
        } elseif ($table === 'languages') {
            $out['Dialects & varieties'] = $map('languages', HeritagePublic::published('languages', 't.parent_id = ?', [$id]));
        } elseif ($table === 'ethnic_groups') {
            $out['Sub-groups'] = $map('ethnic_groups', HeritagePublic::published('ethnic_groups', 't.parent_id = ?', [$id]));
        } elseif ($table === 'historical_periods') {
            $out['Events in this period'] = $map('timeline_events', HeritagePublic::published('timeline_events', 't.period_id = ?', [$id], 't.year'),
                fn($e) => HeritageRegistry::TEMPORAL[$e['temporal_status']] !== 'Historical' ? HeritageRegistry::TEMPORAL[$e['temporal_status']] : $e['event_date']);
        }
        return array_filter($out);
    }

    /** Sourced administrative changes involving a unit, oldest first. */
    private function unitHistory(int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, f.name AS from_name, f.id AS from_id, t.name AS to_name, t.id AS to_id FROM admin_unit_changes c
             LEFT JOIN admin_units f ON f.id = c.from_unit_id LEFT JOIN admin_units t ON t.id = c.to_unit_id
             WHERE (c.from_unit_id = ? OR c.to_unit_id = ?) AND c.source_id IS NOT NULL
             ORDER BY COALESCE(c.effective_date, IF(c.effective_text REGEXP '^[0-9]{4}', CONCAT(LEFT(c.effective_text, 4), '-07-01'), NULL)) IS NULL,
                      COALESCE(c.effective_date, IF(c.effective_text REGEXP '^[0-9]{4}', CONCAT(LEFT(c.effective_text, 4), '-07-01'), NULL)), c.id");
        $stmt->execute([$id, $id]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$c) {
            $c['from'] = $this->link('admin_units', $c['from_id']);
            $c['to'] = $this->link('admin_units', $c['to_id']);
        }
        return $rows;
    }

    private function recentRecords(): array
    {
        $out = [];
        foreach (array_keys(HeritagePublic::SECTIONS) as $table) {
            foreach (HeritagePublic::published($table, $table === 'admin_units' ? "t.unit_type <> 'ward'" : '1 = 1', [], 't.updated_at DESC LIMIT 4') as $row) {
                $out[] = ['name' => $row[HeritageRegistry::NAME_COLUMN[$table]], 'url' => HeritagePublic::recordUrl($table, $row),
                          'type' => $this->typeLabel($table, $row), 'updated' => $row['updated_at'],
                          'summary' => $row[HeritagePublic::SUMMARY[$table] ?? 'summary'] ?? null];
            }
        }
        usort($out, fn($a, $b) => strcmp($b['updated'], $a['updated']));
        return array_slice($out, 0, 8);
    }

    private function schema(string $table, array $r, string $name, string $description, string $url): array
    {
        $type = match ($table) {
            'admin_units' => 'AdministrativeArea', 'places' => 'Place', 'languages' => 'Language',
            'historical_figures' => 'Person', 'timeline_events' => 'Event',
            'polities' => 'Organization', default => 'Thing',
        };
        $s = ['@context' => 'https://schema.org', '@type' => $type, 'name' => $name, 'description' => $description, 'url' => $url];
        if (!empty($r['stable_id'])) $s['identifier'] = $r['stable_id'];
        if ($table === 'places' && $r['latitude'] !== null && $r['coords_source_id']) {
            $s['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $r['latitude'], 'longitude' => (float) $r['longitude']];
        }
        if ($table === 'languages' && $r['iso639_3']) $s['alternateName'] = $r['iso639_3'];
        if ($table === 'timeline_events') {
            if ($r['start_date']) $s['startDate'] = $r['start_date'];
            // Planned/announced events are marked as scheduled, never as having happened.
            if (in_array($r['temporal_status'], ['announced', 'planned', 'proposed', 'projected', 'forecast'], true)) {
                $s['eventStatus'] = 'https://schema.org/EventScheduled';
            }
        }
        return $s;
    }
}
