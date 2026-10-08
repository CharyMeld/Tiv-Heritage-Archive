<?php
/**
 * Admin module for the Nigeria Heritage knowledge tables: generic CRUD driven by
 * HeritageRegistry, a knowledge panel per record (sources, names, statistics,
 * relations, administrative changes) and the research dashboard.
 *
 * Evidence rules enforced here:
 *   - review_status 'published' needs at least one attached source;
 *   - evidence_status can only be raised to a sourced status when sources exist
 *     (multiple_sources needs two);
 *   - every evidence/review status change is written to activity_log with old/new values;
 *   - the six-level evidence_level (migration 002): Verified / Well documented / Reported need an
 *     attached source, and Verified needs a Tier 1 source or two independent sources;
 *   - only records whose sensitivity is Public can be published;
 *   - new records get a permanent stable_id (StableId), never changed afterwards.
 */

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/services/HeritageRegistry.php';
require_once BASE_PATH . '/services/HeritagePublic.php';
require_once BASE_PATH . '/services/ContentIndexer.php';
require_once BASE_PATH . '/services/StableId.php';

class AdminHeritageController extends Controller
{
    private PDO $db;

    /** Minimum attached sources for an evidence status (others need none). */
    private const EVIDENCE_MIN_SOURCES = [
        'verified' => 1, 'well_documented' => 1, 'single_reliable_source' => 1, 'multiple_sources' => 2,
    ];
    /** Six-level evidence levels that need at least one source (Verified has a stricter rule too). */
    private const LEVEL_NEEDS_SOURCE = ['verified', 'well_documented', 'reported'];

    /** Child rows removed with their parent record. */
    private const CHILDREN = [
        'claims' => [['claim_sources', 'claim_id']],
        'cultural_records' => [['cultural_record_attributes', 'cultural_record_id']],
        'media_assets' => [['media_links', 'media_id']],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->db = Database::getInstance();
    }

    /* ── Dashboard ─────────────────────────────────────────────── */

    public function dashboard(): void
    {
        $counts = [];
        foreach (HeritageRegistry::entities() as $key => $e) {
            if (!empty($e['no_knowledge']) || !empty($e['readonly']) || !empty($e['no_dashboard'])) continue;
            $row = $this->db->query(
                "SELECT COUNT(*) total,
                        COALESCE(SUM(review_status = 'published'), 0) published,
                        COALESCE(SUM(review_status = 'in_review'), 0) in_review,
                        COALESCE(SUM(evidence_status IN ('" . implode("','", HeritageRegistry::EVIDENCE_ATTENTION) . "')), 0) attention,
                        COALESCE(SUM(evidence_status = 'disputed'), 0) disputed
                 FROM {$e['table']}"
            )->fetch();
            $counts[$key] = $row + ['label' => $e['plural'], 'icon' => $e['icon']];
        }

        $states = $this->db->query(
            "SELECT u.id, u.name, u.unit_type, u.review_status, u.evidence_status,
                    (SELECT COUNT(*) FROM entity_sources es WHERE es.entity_table = 'admin_units' AND es.entity_id = u.id) sources,
                    (SELECT COUNT(*) FROM admin_units l WHERE l.parent_id = u.id AND l.unit_type = 'lga') lgas,
                    rp.id progress_id, rp.status progress_status, rp.lgas_researched, rp.lgas_verified
             FROM admin_units u LEFT JOIN research_progress rp ON rp.admin_unit_id = u.id WHERE u.unit_type IN ('state', 'federal_capital_territory') AND u.status = 'current'
             ORDER BY u.name"
        )->fetchAll();

        $this->render('admin/heritage/dashboard', [
            'title'       => 'Nigeria Heritage — Research Dashboard | Admin',
            'currentPage' => 'heritage',
            'counts'      => $counts,
            'states'      => $states,
            'sourceCount' => (int) $this->db->query("SELECT COUNT(*) FROM sources")->fetchColumn(),
            'linkedSourceCount' => (int) $this->db->query("SELECT COUNT(DISTINCT source_id) FROM entity_sources")->fetchColumn(),
            'relationCount' => (int) $this->db->query("SELECT COUNT(*) FROM entity_relations")->fetchColumn(),
            'batches'     => $this->db->query("SELECT * FROM research_batches ORDER BY id DESC LIMIT 10")->fetchAll(),
            'batchStatus' => array_column($this->db->query("SELECT status, COUNT(*) c FROM research_batches GROUP BY status")->fetchAll(), 'c', 'status'),
            'gaps'        => $this->db->query("SELECT * FROM research_gaps WHERE status IN ('open','in_progress') ORDER BY id DESC LIMIT 15")->fetchAll(),
            'gapCount'    => (int) $this->db->query("SELECT COUNT(*) FROM research_gaps WHERE status IN ('open','in_progress')")->fetchColumn(),
            'attention'   => $this->attentionList(),
            'duplicates'  => $this->duplicateCandidates(),
            'unsourcedPublished' => $this->unsourcedPublished(),
            'newCounts'   => $this->newTableCounts(),
        ], 'admin');
    }

    /** Record counts for the migration-002 tables (0 when a table is missing). */
    private function newTableCounts(): array
    {
        $out = [];
        foreach (['communities', 'claims', 'claim-disputes', 'oral-histories', 'contributors', 'field-records', 'media', 'research-progress'] as $key) {
            $e = HeritageRegistry::get($key);
            try {
                $out[$key] = ['label' => $e['plural'], 'icon' => $e['icon'], 'total' => (int) $this->db->query("SELECT COUNT(*) FROM {$e['table']}")->fetchColumn()];
            } catch (PDOException $ex) {
                // table not migrated yet
            }
        }
        if (isset($out['claim-disputes'])) {
            $out['claim-disputes']['open'] = (int) $this->db->query("SELECT COUNT(*) FROM claim_disputes WHERE status = 'open'")->fetchColumn();
        }
        return $out;
    }

    /** Records needing corroboration, disputed, unverified, outdated or awaiting review. */
    private function attentionList(): array
    {
        $parts = [];
        foreach (HeritageRegistry::entities() as $key => $e) {
            if (!empty($e['no_knowledge']) || !empty($e['readonly']) || !empty($e['no_dashboard'])) continue;
            $parts[] = "SELECT '{$key}' AS type_key, id, name, evidence_status, review_status, updated_at FROM {$e['table']}
                        WHERE review_status = 'in_review'
                           OR evidence_status IN ('" . implode("','", HeritageRegistry::EVIDENCE_ATTENTION) . "')";
        }
        return $this->db->query(implode(' UNION ALL ', $parts) . ' ORDER BY updated_at DESC LIMIT 25')->fetchAll();
    }

    /** Same normalised name within a table (admin units: same type and parent). Never merged automatically. */
    private function duplicateCandidates(): array
    {
        $parts = [];
        foreach (HeritageRegistry::entities() as $key => $e) {
            if (!empty($e['no_knowledge']) || !empty($e['readonly']) || !empty($e['no_dashboard'])) continue;
            $group = $key === 'admin-units' ? ', unit_type, parent_id' : '';
            $parts[] = "SELECT '{$key}' AS type_key, LOWER(TRIM(name)) AS norm, COUNT(*) c, GROUP_CONCAT(id ORDER BY id) ids
                        FROM {$e['table']} GROUP BY LOWER(TRIM(name)){$group} HAVING c > 1";
        }
        $parts[] = "SELECT CONCAT('alt:', n.entity_table) AS type_key, LOWER(TRIM(n.name)) AS norm, COUNT(DISTINCT n.entity_id) c,
                           GROUP_CONCAT(DISTINCT n.entity_id ORDER BY n.entity_id) ids
                    FROM entity_names n GROUP BY n.entity_table, LOWER(TRIM(n.name)) HAVING c > 1";
        return $this->db->query(implode(' UNION ALL ', $parts) . ' LIMIT 50')->fetchAll();
    }

    private function unsourcedPublished(): array
    {
        $parts = [];
        foreach (HeritageRegistry::entities() as $key => $e) {
            if (!empty($e['no_knowledge']) || !empty($e['readonly']) || !empty($e['no_dashboard'])) continue;
            $parts[] = "SELECT '{$key}' AS type_key, t.id, t.name FROM {$e['table']} t
                        WHERE t.review_status = 'published' AND NOT EXISTS
                        (SELECT 1 FROM entity_sources es WHERE es.entity_table = '{$e['table']}' AND es.entity_id = t.id)";
        }
        return $this->db->query(implode(' UNION ALL ', $parts) . ' LIMIT 50')->fetchAll();
    }

    /* ── List / create / edit / delete ─────────────────────────── */

    public function index(string $type): void
    {
        $e = $this->entity($type);
        $where = [];
        $params = [];
        $filterValue = $this->get('filter', '');
        if (!empty($e['filter']) && $filterValue !== '' && isset($e['fields'][$e['filter']]['options'][$filterValue])) {
            $where[] = "{$e['filter']} = ?";
            $params[] = $filterValue;
        }
        $q = trim((string) $this->get('q', ''));
        if ($q !== '') {
            $where[] = "{$e['title_field']} LIKE ?";
            $params[] = '%' . $q . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$e['table']} {$whereSql}");
        $stmt->execute($params);
        $pagination = $this->paginate((int) $stmt->fetchColumn(), 30);

        $stmt = $this->db->prepare("SELECT * FROM {$e['table']} {$whereSql} ORDER BY {$e['title_field']} ASC LIMIT ? OFFSET ?");
        $stmt->execute([...$params, $pagination['per_page'], $pagination['offset']]);

        $this->render('admin/heritage/index', [
            'title'       => $e['plural'] . ' | Nigeria Heritage | Admin',
            'currentPage' => 'heritage',
            'e'           => $e,
            'items'       => $stmt->fetchAll(),
            'pagination'  => $pagination,
            'fkLabels'    => $this->fkLabels($e),
            'filterValue' => $filterValue,
            'q'           => $q,
        ], 'admin');
    }

    public function create(string $type): void
    {
        $e = $this->entity($type);
        if (!empty($e['readonly'])) { $this->readonly($e); return; }
        $this->renderForm($e, null);
    }

    public function store(string $type): void
    {
        $e = $this->entity($type);
        if (!empty($e['readonly'])) { $this->readonly($e); return; }
        if (!$this->validateCSRF()) { $this->back(); return; }

        [$data, $errors] = $this->collect($e, null);
        if ($errors) { $this->failForm($errors); return; }
        if (!isset($e['no_slug'])) {
            $data['slug'] = $this->uniqueSlug($e, $this->post('slug') ?: $data[$e['title_field']], $data, null);
        }
        if (isset($e['fields']['review_status']) && $data['review_status'] === 'published') {
            $this->failForm(['Save the record first and attach a source; it can be published after that.']);
            return;
        }
        if (($min = self::EVIDENCE_MIN_SOURCES[$data['evidence_status'] ?? ''] ?? 0) > 0) {
            $this->failForm(["Evidence status \"" . HeritageRegistry::EVIDENCE[$data['evidence_status']] . "\" needs {$min} attached source(s). Save as Unverified first, then attach sources."]);
            return;
        }
        if (in_array($data['evidence_level'] ?? null, self::LEVEL_NEEDS_SOURCE, true)) {
            $this->failForm(['Evidence level "' . HeritageRegistry::EVIDENCE_LEVEL[$data['evidence_level']] . '" needs attached sources. Save with a lower level first, then attach sources.']);
            return;
        }
        if ($this->hasColumn($e['table'], 'created_by')) {
            $data['created_by'] = $this->user['id'];
        }
        if (!empty($e['set_user'])) {
            $data[$e['set_user']] = $this->user['id'];
        }

        $cols = array_keys($data);
        $stmt = $this->db->prepare(sprintf("INSERT INTO %s (%s) VALUES (%s)",
            $e['table'], implode(', ', $cols), implode(', ', array_fill(0, count($cols), '?'))));
        try {
            $stmt->execute(array_values($data));
        } catch (PDOException $ex) {
            $this->failForm([$this->friendlyDbError($ex)]);
            return;
        }
        $id = (int) $this->db->lastInsertId();
        try {
            StableId::assign($this->db, $e['table'], $id);
        } catch (\Throwable $ex) {
            error_log("StableId [{$e['table']}#{$id}]: " . $ex->getMessage()); // never blocks the save
        }
        $this->clearOldInput();
        Security::logActivity($this->user['id'], 'heritage_created', $e['table'], $id, null, $this->statusValues($data));
        $this->reindex($e['table'], $id);
        Cache::forget('nigeria_has_published');
        $this->flashWithDuplicates($e, $data, $id,
            "{$e['label']} \"{$data[$e['title_field']]}\" saved." . (empty($e['no_knowledge']) ? ' Attach its sources below.' : ''));
        $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit"));
    }

    public function edit(string $type, string $id): void
    {
        $e = $this->entity($type);
        $item = $this->find($e, (int) $id);
        if (!$item) { $this->notFound($e); return; }
        $this->renderForm($e, $item);
    }

    public function update(string $type, string $id): void
    {
        $e = $this->entity($type);
        if (!empty($e['readonly'])) { $this->readonly($e); return; }
        if (!$this->validateCSRF()) { $this->back(); return; }
        $item = $this->find($e, (int) $id);
        if (!$item) { $this->notFound($e); return; }

        [$data, $errors] = $this->collect($e, $item);
        if ($errors) { $this->failForm($errors); return; }

        if (isset($e['fields']['evidence_status'])) {
            $sources = $this->sourceCount($e['table'], (int) $id);
            if ($data['review_status'] === 'published' && $sources === 0) {
                $errors[] = 'A record needs at least one attached source before it can be published.';
            }
            $min = self::EVIDENCE_MIN_SOURCES[$data['evidence_status']] ?? 0;
            if ($sources < $min) {
                $errors[] = "Evidence status \"" . HeritageRegistry::EVIDENCE[$data['evidence_status']] . "\" needs {$min} attached source(s); this record has {$sources}.";
            }
        }
        $errors = array_merge($errors, $this->ruleErrors002($e, (int) $id, $data));
        if ($errors) { $this->failForm($errors); return; }
        if (!isset($e['no_slug'])) {
            $data['slug'] = $this->uniqueSlug($e, $this->post('slug') ?: $data[$e['title_field']], $data, (int) $id);
        }

        $sets = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($data)));
        try {
            $this->db->beginTransaction();
            $this->db->prepare("UPDATE {$e['table']} SET {$sets} WHERE id = ?")->execute([...array_values($data), (int) $id]);
            if (!isset($e['no_slug']) && $data['slug'] !== $item['slug'] && ($item['review_status'] ?? '') === 'published') {
                // The old URL may be indexed: keep it for a 301 (served from Step 6's public routes).
                $this->db->prepare("INSERT IGNORE INTO slug_redirects (entity_table, entity_id, old_path) VALUES (?, ?, ?)")
                    ->execute([$e['table'], (int) $id, HeritagePublic::path($e['table'], $item)]);
            }
            $this->db->commit();
        } catch (PDOException $ex) {
            $this->db->rollBack();
            $this->failForm([$this->friendlyDbError($ex)]);
            return;
        }

        $old = $this->statusValues($item);
        $new = $this->statusValues($data);
        if ($old !== $new) {
            Security::logActivity($this->user['id'], 'heritage_status_changed', $e['table'], (int) $id, $old, $new);
        }
        Security::logActivity($this->user['id'], 'heritage_updated', $e['table'], (int) $id);
        $this->reindex($e['table'], (int) $id, true);
        Cache::forget('nigeria_has_published');
        $this->clearOldInput();
        $this->flashWithDuplicates($e, $data, (int) $id, "{$e['label']} \"{$data[$e['title_field']]}\" updated.");
        $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit"));
    }

    public function delete(string $type, string $id): void
    {
        $this->requireAdmin();
        $e = $this->entity($type);
        if (!empty($e['readonly'])) { $this->readonly($e); return; }
        if (!$this->validateCSRF()) { $this->back(); return; }
        $item = $this->find($e, (int) $id);
        if (!$item) { $this->notFound($e); return; }

        try {
            $this->db->beginTransaction();
            if (empty($e['no_knowledge'])) {
                foreach (['entity_sources', 'entity_names', 'entity_statistics', 'collection_items', 'media_links'] as $t) {
                    $this->db->prepare("DELETE FROM {$t} WHERE entity_table = ? AND entity_id = ?")->execute([$e['table'], (int) $id]);
                }
                $this->db->prepare("DELETE FROM entity_relations WHERE (from_table = ? AND from_id = ?) OR (to_table = ? AND to_id = ?)")
                    ->execute([$e['table'], (int) $id, $e['table'], (int) $id]);
            }
            foreach (self::CHILDREN[$e['table']] ?? [] as [$childTable, $fk]) {
                $this->db->prepare("DELETE FROM {$childTable} WHERE {$fk} = ?")->execute([(int) $id]);
            }
            $this->db->prepare("DELETE FROM {$e['table']} WHERE id = ?")->execute([(int) $id]);
            $this->db->commit();
        } catch (PDOException $ex) {
            $this->db->rollBack();
            $this->flash('Not deleted: ' . $this->friendlyDbError($ex), 'error');
            $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit"));
            return;
        }
        Security::logActivity($this->user['id'], 'heritage_deleted', $e['table'], (int) $id, [$e['title_field'] => $item[$e['title_field']]]);
        $this->reindex($e['table'], (int) $id);
        Cache::forget('nigeria_has_published');
        $this->flash("{$e['label']} \"{$item[$e['title_field']]}\" deleted.", 'info');
        $this->redirect(url("admin/heritage/{$e['key']}"));
    }

    /* ── Knowledge panel: sources, names, statistics, relations, unit changes ── */

    public function addSource(string $type, string $id): void
    {
        [$e, $item] = $this->knowledgeTarget($type, $id);
        if (!$item) return;
        $sourceId = (int) $this->post('source_id');
        if (!$this->exists('sources', $sourceId)) { $this->flash('Choose a source.', 'error'); $this->backToEdit($e, $id); return; }
        $this->db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, page_section, quote, stance, research_batch_id, created_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$e['table'], (int) $id, $sourceId, $this->nullIfEmpty('claim'), $this->nullIfEmpty('page_section'),
                       $this->nullIfEmpty('quote'), $this->enum('stance', ['supports', 'contradicts', 'mentions'], 'supports'),
                       $this->nullIfEmpty('research_batch_id'), $this->user['id']]);
        $this->flash('Source attached.', 'success');
        $this->backToEdit($e, $id);
    }

    public function addName(string $type, string $id): void
    {
        [$e, $item] = $this->knowledgeTarget($type, $id);
        if (!$item) return;
        $name = trim((string) $this->post('name'));
        if ($name === '') { $this->flash('Enter the name.', 'error'); $this->backToEdit($e, $id); return; }
        $this->db->prepare("INSERT INTO entity_names (entity_table, entity_id, name, name_type, language_id, valid_from_year, valid_to_year, usage_notes, source_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$e['table'], (int) $id, $name,
                       $this->enum('name_type', ['alternative', 'historical', 'endonym', 'exonym', 'colonial', 'spelling_variant', 'abbreviation', 'official', 'other'], 'alternative'),
                       $this->nullIfEmpty('language_id'), $this->nullIfEmpty('valid_from_year'), $this->nullIfEmpty('valid_to_year'),
                       $this->nullIfEmpty('usage_notes'), $this->nullIfEmpty('source_id')]);
        $this->reindex($e['table'], (int) $id);
        $this->flash("Name \"{$name}\" added.", 'success');
        $this->backToEdit($e, $id);
    }

    public function addStatistic(string $type, string $id): void
    {
        [$e, $item] = $this->knowledgeTarget($type, $id);
        if (!$item) return;
        $sourceId = (int) $this->post('source_id');
        $low = $this->post('value_low');
        if (!$this->exists('sources', $sourceId) || $low === null || $low === '' || !ctype_digit((string) $low)) {
            $this->flash('A statistic needs a whole-number value and a cited source.', 'error');
            $this->backToEdit($e, $id);
            return;
        }
        $high = $this->nullIfEmpty('value_high');
        if ($high !== null && (!ctype_digit((string) $high) || (int) $high < (int) $low)) {
            $this->flash('The upper value must be a whole number no smaller than the lower value.', 'error');
            $this->backToEdit($e, $id);
            return;
        }
        $this->db->prepare("INSERT INTO entity_statistics (entity_table, entity_id, metric, value_low, value_high, reference_year, method, notes, source_id, evidence_status, evidence_level)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$e['table'], (int) $id,
                       $this->enum('metric', ['population', 'speakers', 'first_language_speakers', 'second_language_speakers', 'area_km2', 'households', 'other'], 'population'),
                       (int) $low, $high, $this->nullIfEmpty('reference_year'),
                       $this->enum('method', ['census', 'projection', 'estimate', 'survey', 'other'], null),
                       $this->nullIfEmpty('notes'), $sourceId,
                       $this->enum('evidence_status', array_keys(HeritageRegistry::EVIDENCE), 'single_reliable_source'),
                       $this->enum('evidence_level', array_keys(HeritageRegistry::EVIDENCE_LEVEL), null)]);
        $this->flash('Statistic recorded with its source.', 'success');
        $this->backToEdit($e, $id);
    }

    public function addRelation(string $type, string $id): void
    {
        [$e, $item] = $this->knowledgeTarget($type, $id);
        if (!$item) return;
        [$toTable, $toId] = array_pad(explode(':', (string) $this->post('target'), 2), 2, '');
        $relType = (string) $this->post('relation_type');
        if (!isset(HeritageRegistry::LINKABLE[$toTable]) || !$this->exists($toTable, (int) $toId)) {
            $this->flash('Choose the related record.', 'error'); $this->backToEdit($e, $id); return;
        }
        if (!$this->exists('relation_types', $relType, 'code')) {
            $this->flash('Choose a relationship type.', 'error'); $this->backToEdit($e, $id); return;
        }
        if ($toTable === $e['table'] && (int) $toId === (int) $id) {
            $this->flash('A record cannot be related to itself.', 'error'); $this->backToEdit($e, $id); return;
        }
        $evidence = $this->enum('evidence_status', array_keys(HeritageRegistry::EVIDENCE), 'unverified');
        $sourceId = $this->nullIfEmpty('source_id');
        if ((self::EVIDENCE_MIN_SOURCES[$evidence] ?? 0) > 0 && $sourceId === null) {
            $this->flash('A relationship marked "' . HeritageRegistry::EVIDENCE[$evidence] . '" needs its source.', 'error');
            $this->backToEdit($e, $id);
            return;
        }
        $level = $this->enum('evidence_level', array_keys(HeritageRegistry::EVIDENCE_LEVEL), null);
        if (in_array($level, self::LEVEL_NEEDS_SOURCE, true) && $sourceId === null) {
            $this->flash('A relationship marked "' . HeritageRegistry::EVIDENCE_LEVEL[$level] . '" needs its source.', 'error');
            $this->backToEdit($e, $id);
            return;
        }
        $sensitivity = $this->enum('sensitivity', array_keys(HeritageRegistry::SENSITIVITY), 'public');
        $direction = $this->post('direction') === 'incoming';
        $from = $direction ? [$toTable, (int) $toId] : [$e['table'], (int) $id];
        $to   = $direction ? [$e['table'], (int) $id] : [$toTable, (int) $toId];
        $this->db->prepare("INSERT INTO entity_relations (from_table, from_id, relation_type, settlement_status, location_type, speaker_role, to_table, to_id,
                                valid_from_year, valid_from_text, valid_to_year, valid_to_text, date_precision, role, notes, source_id, evidence_status,
                                evidence_level, sensitivity, review_status, research_batch_id, created_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$from[0], $from[1], $relType,
                       $this->enum('settlement_status', array_keys(HeritageRegistry::SETTLEMENT), null),
                       $this->enum('location_type', array_keys(HeritageRegistry::LOCATION_TYPE), null),
                       $this->enum('speaker_role', array_keys(HeritageRegistry::SPEAKER_ROLE), null),
                       $to[0], $to[1],
                       $this->nullIfEmpty('valid_from_year'), $this->nullIfEmpty('valid_from_text'),
                       $this->nullIfEmpty('valid_to_year'), $this->nullIfEmpty('valid_to_text'),
                       $this->enum('date_precision', array_keys(HeritageRegistry::PRECISION), 'unknown'),
                       $this->nullIfEmpty('role'), $this->nullIfEmpty('notes'), $sourceId, $evidence, $level, $sensitivity,
                       // Only public, sourced relationships can be published.
                       $sourceId !== null && $sensitivity === 'public' && $this->post('publish') ? 'published' : 'draft',
                       $this->nullIfEmpty('research_batch_id'), $this->user['id']]);
        $this->reindex($from[0], $from[1]);
        $this->reindex($to[0], $to[1]);
        $this->flash('Relationship added.', 'success');
        $this->backToEdit($e, $id);
    }

    public function addChange(string $type, string $id): void
    {
        [$e, $item] = $this->knowledgeTarget($type, $id);
        if (!$item) return;
        if ($e['table'] !== 'admin_units') { $this->notFound($e); return; }
        $other = $this->nullIfEmpty('other_unit_id');
        if ($other !== null && !$this->exists('admin_units', (int) $other)) {
            $this->flash('Choose an existing unit.', 'error'); $this->backToEdit($e, $id); return;
        }
        // "This unit" is the successor for created_from / renamed / upgraded, the predecessor otherwise.
        $changeType = $this->enum('change_type', ['created', 'created_from', 'split_into', 'merged_into', 'renamed', 'boundary_change', 'capital_change', 'upgraded', 'abolished', 'other'], 'other');
        $thisIsSuccessor = in_array($changeType, ['created', 'created_from', 'renamed', 'upgraded', 'boundary_change', 'capital_change'], true);
        $evidence = $this->enum('evidence_status', array_keys(HeritageRegistry::EVIDENCE), 'unverified');
        $sourceId = $this->nullIfEmpty('source_id');
        if ((self::EVIDENCE_MIN_SOURCES[$evidence] ?? 0) > 0 && $sourceId === null) {
            $this->flash('A change marked "' . HeritageRegistry::EVIDENCE[$evidence] . '" needs its source.', 'error');
            $this->backToEdit($e, $id);
            return;
        }
        $this->db->prepare("INSERT INTO admin_unit_changes (change_type, from_unit_id, to_unit_id, old_value, new_value, effective_date, effective_text,
                                date_precision, legal_instrument, notes, source_id, evidence_status, research_batch_id, created_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$changeType, $thisIsSuccessor ? $other : (int) $id, $thisIsSuccessor ? (int) $id : $other,
                       $this->nullIfEmpty('old_value'), $this->nullIfEmpty('new_value'), $this->nullIfEmpty('effective_date'),
                       $this->nullIfEmpty('effective_text'), $this->enum('date_precision', array_keys(HeritageRegistry::PRECISION), 'unknown'),
                       $this->nullIfEmpty('legal_instrument'), $this->nullIfEmpty('notes'), $sourceId, $evidence,
                       $this->nullIfEmpty('research_batch_id'), $this->user['id']]);
        $this->flash('Administrative change recorded.', 'success');
        $this->backToEdit($e, $id);
    }

    /** Child-row panels of migration 002: [table, parent fk, registry flag]. */
    private const PANELS = [
        'claim-sources' => ['claim_sources', 'claim_id', 'claim_sources'],
        'attributes'    => ['cultural_record_attributes', 'cultural_record_id', 'attributes'],
        'media-links'   => ['media_links', 'media_id', 'media_links'],
    ];

    /** POST admin/heritage/{type}/{id}/panel/{kind} — adds a claim source, cultural attribute or media link. */
    public function addPanelRow(string $type, string $id, string $kind): void
    {
        $e = $this->entity($type);
        [$table, $fk, $flag] = self::PANELS[$kind] ?? [null, null, null];
        if (!$table || !in_array($flag, $e['panels'] ?? [], true) || !$this->validateCSRF()) { $this->back(); return; }
        $item = $this->find($e, (int) $id);
        if (!$item) { $this->notFound($e); return; }
        $back = fn() => $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit") . '#panels');

        if ($kind === 'claim-sources') {
            $sourceId = (int) $this->post('source_id');
            if (!$this->exists('sources', $sourceId)) { $this->flash('Choose a source.', 'error'); $back(); return; }
            $this->db->prepare("INSERT IGNORE INTO claim_sources (claim_id, source_id, stance, page_section, quote, accessed_on, research_batch_id) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([(int) $id, $sourceId, $this->enum('stance', ['supports', 'contradicts', 'mentions'], 'supports'),
                           $this->nullIfEmpty('page_section'), $this->nullIfEmpty('quote'),
                           preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->post('accessed_on')) ? $this->post('accessed_on') : null,
                           $this->nullIfEmpty('research_batch_id')]);
            $this->flash('Source attached to the claim.', 'success');
        } elseif ($kind === 'attributes') {
            $value = $this->nullIfEmpty('value');
            if ($value === null) { $this->flash('Enter the value.', 'error'); $back(); return; }
            $this->db->prepare("INSERT INTO cultural_record_attributes (cultural_record_id, attribute, value, local_value, sort_order, source_id, research_batch_id) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([(int) $id, $this->enum('attribute', array_keys(self::ATTRIBUTES), 'local_term'), $value, $this->nullIfEmpty('local_value'),
                           (int) $this->post('sort_order', 0), $this->nullIfEmpty('source_id'), $this->nullIfEmpty('research_batch_id')]);
            $this->flash('Detail added.', 'success');
        } else {
            [$toTable, $toId] = array_pad(explode(':', (string) $this->post('target'), 2), 2, '');
            if (!isset(HeritageRegistry::LINKABLE[$toTable]) || !$this->exists($toTable, (int) $toId)) { $this->flash('Choose the record.', 'error'); $back(); return; }
            $this->db->prepare("INSERT IGNORE INTO media_links (media_id, entity_table, entity_id, role) VALUES (?, ?, ?, ?)")
                ->execute([(int) $id, $toTable, (int) $toId, $this->enum('role', ['depicts', 'records', 'documents'], 'depicts')]);
            $this->flash('Linked.', 'success');
        }
        Security::logActivity($this->user['id'], "heritage_{$kind}_added", $e['table'], (int) $id);
        $back();
    }

    /** Cultural record attribute kinds: HeritageRegistry::CULTURAL_ATTRIBUTES. */
    public const ATTRIBUTES = HeritageRegistry::CULTURAL_ATTRIBUTES;

    /** POST admin/heritage/{type}/{id}/panel/{kind}/{rowId}/delete */
    public function deletePanelRow(string $type, string $id, string $kind, string $rowId): void
    {
        $e = $this->entity($type);
        [$table, $fk, $flag] = self::PANELS[$kind] ?? [null, null, null];
        if (!$table || !in_array($flag, $e['panels'] ?? [], true) || !$this->validateCSRF()) { $this->back(); return; }
        $this->db->prepare("DELETE FROM {$table} WHERE id = ? AND {$fk} = ?")->execute([(int) $rowId, (int) $id]);
        Security::logActivity($this->user['id'], "heritage_{$kind}_removed", $e['table'], (int) $id, ['row_id' => (int) $rowId]);
        $this->flash('Removed.', 'info');
        $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit") . '#panels');
    }

    /** POST admin/heritage/{type}/{id}/{kind}/{rowId}/delete — removes one knowledge-panel row. */
    public function deleteKnowledge(string $type, string $id, string $kind, string $rowId): void
    {
        [$e, $item] = $this->knowledgeTarget($type, $id);
        if (!$item) return;
        $sql = [
            'sources'    => "DELETE FROM entity_sources WHERE id = ? AND entity_table = ? AND entity_id = ?",
            'names'      => "DELETE FROM entity_names WHERE id = ? AND entity_table = ? AND entity_id = ?",
            'statistics' => "DELETE FROM entity_statistics WHERE id = ? AND entity_table = ? AND entity_id = ?",
            'relations'  => "DELETE FROM entity_relations WHERE id = ? AND ((from_table = ? AND from_id = ?) OR (to_table = ? AND to_id = ?))",
            'changes'    => "DELETE FROM admin_unit_changes WHERE id = ? AND (from_unit_id = ? OR to_unit_id = ?)",
        ][$kind] ?? null;
        if ($sql === null) { $this->notFound($e); return; }

        $publiclyIndexed = empty($e['readonly']) ? HeritagePublic::isPublished($e['table'], $item)
            : ($item['status'] === 'published' && !empty($item['national_slug']));
        if ($kind === 'sources' && $publiclyIndexed && $this->sourceCount($e['table'], (int) $id) <= 1) {
            $this->flash('This is the only source of a published record. Unpublish it first, or attach another source.', 'error');
            $this->backToEdit($e, $id);
            return;
        }
        $ends = [[$e['table'], (int) $id]];
        if ($kind === 'relations') {
            $r = $this->db->prepare("SELECT from_table, from_id, to_table, to_id FROM entity_relations WHERE id = ?");
            $r->execute([(int) $rowId]);
            if ($rel = $r->fetch()) $ends = [[$rel['from_table'], (int) $rel['from_id']], [$rel['to_table'], (int) $rel['to_id']]];
        }
        $params = match ($kind) {
            'relations' => [(int) $rowId, $e['table'], (int) $id, $e['table'], (int) $id],
            'changes'   => [(int) $rowId, (int) $id, (int) $id],
            default     => [(int) $rowId, $e['table'], (int) $id],
        };
        $this->db->prepare($sql)->execute($params);
        Security::logActivity($this->user['id'], "heritage_{$kind}_removed", $e['table'], (int) $id, ['row_id' => (int) $rowId]);
        foreach ($ends as [$t, $i]) $this->reindex($t, $i);
        $this->flash('Removed.', 'info');
        $this->backToEdit($e, $id);
    }

    /**
     * POST admin/heritage/{people|events}/{id}/collections — collection membership,
     * national URL and evidence status of an existing person/event.
     * Nigeria-only records get /nigeria/… URLs; anything in the Tiv collection keeps
     * its existing Tiv URL (national_slug NULL).
     */
    public function setCollections(string $type, string $id): void
    {
        $e = $this->entity($type);
        if (empty($e['readonly']) || !$this->validateCSRF()) { $this->back(); return; }
        $item = $this->find($e, (int) $id);
        if (!$item) { $this->notFound($e); return; }

        $inTiv = !empty($_POST['in_tiv']);
        $inNigeria = !empty($_POST['in_nigeria']);
        $evidence = $this->enum('evidence_status', array_keys(HeritageRegistry::EVIDENCE), null);
        $errors = [];
        if (!$inTiv && !$inNigeria) $errors[] = 'A record must belong to at least one collection.';
        if (($min = self::EVIDENCE_MIN_SOURCES[$evidence] ?? 0) > ($n = $this->sourceCount($e['table'], (int) $id))) {
            $errors[] = 'Evidence status "' . HeritageRegistry::EVIDENCE[$evidence] . "\" needs {$min} attached source(s); this record has {$n}.";
        }
        if ($errors) { $this->failForm($errors); return; }

        $slug = null;
        if ($inNigeria && !$inTiv) {
            $slug = $this->uniqueNationalSlug($e['table'], $this->post('national_slug') ?: $item[$e['title_field']], (int) $id);
        }
        $collections = array_column($this->db->query("SELECT code, id FROM collections")->fetchAll(), 'id', 'code');
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM collection_items WHERE entity_table = ? AND entity_id = ?")->execute([$e['table'], (int) $id]);
            $ins = $this->db->prepare("INSERT INTO collection_items (collection_id, entity_table, entity_id) VALUES (?, ?, ?)");
            if ($inTiv) $ins->execute([$collections['tiv'], $e['table'], (int) $id]);
            if ($inNigeria) $ins->execute([$collections['nigeria'], $e['table'], (int) $id]);
            if ($item['national_slug'] && $item['national_slug'] !== $slug && $item['status'] === 'published') {
                // Its /nigeria/ address may be indexed: keep it as a redirect to the new canonical URL.
                $this->db->prepare("INSERT IGNORE INTO slug_redirects (entity_table, entity_id, old_path) VALUES (?, ?, ?)")
                    ->execute([$e['table'], (int) $id, HeritagePublic::path($e['table'], $item)]);
            }
            $this->db->prepare("UPDATE {$e['table']} SET national_slug = ?, evidence_status = ? WHERE id = ?")->execute([$slug, $evidence, (int) $id]);
            $this->db->commit();
        } catch (PDOException $ex) {
            $this->db->rollBack();
            $this->failForm([$this->friendlyDbError($ex)]);
            return;
        }
        Security::logActivity($this->user['id'], 'heritage_collections_changed', $e['table'], (int) $id,
            ['national_slug' => $item['national_slug'], 'evidence_status' => $item['evidence_status']],
            ['national_slug' => $slug, 'evidence_status' => $evidence, 'tiv' => $inTiv, 'nigeria' => $inNigeria]);
        Cache::forget('nigeria_has_published');
        $this->reindex($e['table'], (int) $id);
        $this->flash($slug ? "Saved. Public address: " . nigeria_url(HeritagePublic::path($e['table'], ['national_slug' => $slug] + $item))
                           : 'Saved. It keeps its Tiv Heritage address.', 'success');
        $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit"));
    }

    private function uniqueNationalSlug(string $table, string $text, int $selfId): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text)), '-') ?: 'record';
        for ($n = 1; ; $n++) {
            $slug = $n === 1 ? $base : "{$base}-{$n}";
            $stmt = $this->db->prepare("SELECT 1 FROM {$table} WHERE national_slug = ? AND id != ? LIMIT 1");
            $stmt->execute([$slug, $selfId]);
            if (!$stmt->fetchColumn()) return $slug;
        }
    }

    /**
     * Keep the AI/search index in step with the record (published national records only;
     * ContentIndexer removes unpublished ones). $related also refreshes records linked to
     * it, whose indexed relation text mentions its name. Never blocks the save.
     */
    private function reindex(string $table, int $id, bool $related = false): void
    {
        try {
            $indexer = new ContentIndexer($this->db);
            $indexer->indexRecord($table, $id);
            if ($related) {
                $stmt = $this->db->prepare("SELECT to_table t, to_id i FROM entity_relations WHERE from_table = ? AND from_id = ?
                                            UNION SELECT from_table, from_id FROM entity_relations WHERE to_table = ? AND to_id = ?");
                $stmt->execute([$table, $id, $table, $id]);
                foreach ($stmt->fetchAll() as $r) $indexer->indexRecord($r['t'], (int) $r['i']);
            }
        } catch (\Throwable $ex) {
            error_log("AdminHeritage reindex [{$table}#{$id}]: " . $ex->getMessage());
        }
    }

    private function readonly(array $e): void
    {
        $this->flash("{$e['plural']}: create and edit these in their own admin screen; here you set collections, evidence and sources.", 'info');
        $this->redirect(url("admin/heritage/{$e['key']}"));
    }

    /* ── Helpers ───────────────────────────────────────────────── */

    private function entity(string $type): array
    {
        $e = HeritageRegistry::get($type);
        if (!$e) {
            $this->flash('Unknown record type.', 'error');
            $this->redirect(url('admin/heritage'));
            exit;
        }
        return $e;
    }

    private function find(array $e, int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$e['table']} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function knowledgeTarget(string $type, string $id): array
    {
        $e = $this->entity($type);
        if (!empty($e['no_knowledge']) || !$this->validateCSRF()) { $this->back(); return [$e, null]; }
        $item = $this->find($e, (int) $id);
        if (!$item) { $this->notFound($e); return [$e, null]; }
        return [$e, $item];
    }

    private function exists(string $table, $value, string $column = 'id'): bool
    {
        if ($value === '' || $value === null || $value === 0) return false;
        $stmt = $this->db->prepare("SELECT 1 FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        return (bool) $stmt->fetchColumn();
    }

    private function sourceCount(string $table, int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT source_id) FROM entity_sources WHERE entity_table = ? AND entity_id = ?");
        $stmt->execute([$table, $id]);
        return (int) $stmt->fetchColumn();
    }

    /** Validates the posted form against the registry. Returns [data, errors]. */
    private function collect(array $e, ?array $item): array
    {
        $data = [];
        $errors = [];
        foreach ($e['fields'] as $name => $f) {
            $raw = $_POST[$name] ?? null;
            $value = is_string($raw) ? trim($raw) : null;
            if ($value === '' || $value === null) {
                $value = null;
            }
            switch ($f['type']) {
                case 'select':
                    if ($value !== null && !array_key_exists($value, $f['options'])) {
                        $errors[] = "{$f['label']}: invalid choice.";
                    }
                    break;
                case 'number':
                    if ($value !== null && !is_numeric($value)) $errors[] = "{$f['label']} must be a number.";
                    elseif ($value !== null && ((isset($f['min']) && $value < $f['min']) || (isset($f['max']) && $value > $f['max']))) {
                        $errors[] = "{$f['label']} must be between {$f['min']} and {$f['max']}.";
                    }
                    break;
                case 'date':
                    if ($value !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) $errors[] = "{$f['label']} must be a date.";
                    break;
                case 'fk':
                    if ($value !== null) {
                        $fkTable = $f['fk'] === 'sources' ? 'sources' : HeritageRegistry::get($f['fk'])['table'];
                        if (!$this->exists($fkTable, (int) $value)) $errors[] = "{$f['label']}: record not found.";
                        if ($item && $fkTable === $e['table'] && (int) $value === (int) $item['id']) $errors[] = "{$f['label']} cannot be the record itself.";
                    }
                    break;
            }
            if (!empty($f['maxlength']) && $value !== null && mb_strlen($value) > $f['maxlength']) {
                $errors[] = "{$f['label']} is at most {$f['maxlength']} characters.";
            }
            if ($value === null && isset($f['default']) && !empty($f['required'])) $value = $f['default'];
            if (!empty($f['required']) && $value === null) $errors[] = "{$f['label']} is required.";
            $data[$name] = $value;
        }
        if (isset($data['summary']) && $data['summary'] !== null && mb_strlen($data['summary']) > 500) {
            $errors[] = 'Summary is at most 500 characters.';
        }
        foreach ($e['rules'] ?? [] as $rule) {
            $triggered = isset($rule['when']) ? ($data[$rule['when'][0]] ?? null) === $rule['when'][1]
                                              : (bool) array_filter($rule['if'], fn($f) => $data[$f] !== null);
            if (!$triggered) continue;
            if (isset($rule['require']) && $data[$rule['require']] === null) $errors[] = $rule['message'];
            if (isset($rule['equal']) && ((int) $data[$rule['equal'][0]] !== (int) $data[$rule['equal'][1]] || (int) $data[$rule['equal'][1]] === 0)) {
                $errors[] = $rule['message'];
            }
        }
        // Record references stored as (table, id) pairs, e.g. a claim's subject.
        foreach ($e['refs'] ?? [] as [$tableCol, $idCol, $required]) {
            $t = $data[$tableCol] ?? null;
            if ($t === null && !$required) continue;
            if ($t !== null && isset(HeritageRegistry::LINKABLE[$t]) && !$this->exists($t, (int) ($data[$idCol] ?? 0))) {
                $errors[] = "{$e['fields'][$idCol]['label']}: no " . strtolower(HeritageRegistry::LINKABLE[$t]) . " with ID " . (int) ($data[$idCol] ?? 0) . '.';
            }
        }
        return [$data, $errors];
    }

    /**
     * Migration-002 rules checked on every update: sensitivity and permission before publishing,
     * a source before publishing records that have no older evidence rule, and the six-level
     * evidence rules (NIGERIA_STEP5 §1.1).
     */
    private function ruleErrors002(array $e, int $id, array $data): array
    {
        $errors = [];
        $publishing = ($data['review_status'] ?? null) === 'published';
        if ($publishing && isset($data['sensitivity']) && $data['sensitivity'] !== 'public') {
            $errors[] = 'Only records marked Public can be published (this one is "' . (HeritageRegistry::SENSITIVITY[$data['sensitivity']] ?? $data['sensitivity']) . '").';
        }
        if ($publishing && !empty($e['publish_requires'])) {
            foreach ($e['publish_requires'] as $field => $allowed) {
                if ($field !== 'message' && !in_array($data[$field] ?? null, $allowed, true)) $errors[] = $e['publish_requires']['message'];
            }
        }
        $hasSourcing = empty($e['no_knowledge']) || !empty($e['source_count']);
        [$count, $tier1, $lineages] = $hasSourcing ? $this->evidenceSources($e, $id) : [0, false, 0];
        if ($publishing && $hasSourcing && !isset($e['fields']['evidence_status']) && $count === 0) {
            $errors[] = 'A record needs at least one attached source before it can be published.';
        }
        $level = $data['evidence_level'] ?? null;
        if (in_array($level, self::LEVEL_NEEDS_SOURCE, true)) {
            $label = HeritageRegistry::EVIDENCE_LEVEL[$level];
            if (!$hasSourcing || $count === 0) {
                $errors[] = "Evidence level \"{$label}\" needs at least one attached source.";
            } elseif ($level === 'verified' && !$tier1 && $lineages < 2) {
                $errors[] = 'Evidence level "Verified" needs a Tier 1 source, or two independent sources (sources that copy each other count as one).';
            }
        }
        return $errors;
    }

    /** [sources attached, any Tier 1 among them, independent lineages] for a record. */
    private function evidenceSources(array $e, int $id): array
    {
        [$table, $fk] = $e['source_count'] ?? ['entity_sources', null];
        $stmt = $fk
            ? $this->db->prepare("SELECT s.id, s.source_tier, s.lineage_group FROM {$table} x JOIN sources s ON s.id = x.source_id WHERE x.{$fk} = ? AND x.stance <> 'contradicts'")
            : $this->db->prepare("SELECT s.id, s.source_tier, s.lineage_group FROM entity_sources x JOIN sources s ON s.id = x.source_id
                                  WHERE x.entity_table = ? AND x.entity_id = ? AND x.stance <> 'contradicts'");
        $stmt->execute($fk ? [$id] : [$e['table'], $id]);
        $rows = $stmt->fetchAll();
        $lineages = array_unique(array_map(fn($r) => $r['lineage_group'] ?: 'S' . $r['id'], $rows));
        return [count(array_unique(array_column($rows, 'id'))), in_array(1, array_map('intval', array_column($rows, 'source_tier')), true), count($lineages)];
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        return $cache["{$table}.{$column}"] ??= (bool) $this->db->query(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = " . $this->db->quote($table)
            . " AND column_name = " . $this->db->quote($column))->fetchColumn();
    }

    private function uniqueSlug(array $e, string $text, array $data, ?int $selfId): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text)), '-') ?: 'record';
        $scope = '';
        $params = [];
        foreach ($e['slug_scope'] ?? [] as $col) {
            $scope .= $data[$col] === null ? " AND {$col} IS NULL" : " AND {$col} = ?";
            if ($data[$col] !== null) $params[] = $data[$col];
        }
        for ($n = 1; ; $n++) {
            $slug = $n === 1 ? $base : "{$base}-{$n}";
            $stmt = $this->db->prepare("SELECT 1 FROM {$e['table']} WHERE slug = ?{$scope}" . ($selfId ? ' AND id != ?' : '') . ' LIMIT 1');
            $stmt->execute(array_merge([$slug], $params, $selfId ? [$selfId] : []));
            if (!$stmt->fetchColumn()) return $slug;
        }
    }

    private function statusValues(array $row): array
    {
        return array_intersect_key($row, array_flip(['evidence_status', 'evidence_level', 'sensitivity', 'review_status', 'status']));
    }

    /** Success message, turned into a warning when the name matches another record (never merged). */
    private function flashWithDuplicates(array $e, array $data, int $id, string $message): void
    {
        if (!empty($e['no_knowledge'])) { $this->flash($message, 'success'); return; }
        $name = $data[$e['title_field']];
        $col = $e['title_field'];
        $stmt = $this->db->prepare(
            "SELECT id, {$col} AS name FROM {$e['table']} WHERE id != ? AND LOWER(TRIM({$col})) = LOWER(TRIM(?))
             UNION SELECT entity_id, name FROM entity_names WHERE entity_table = ? AND entity_id != ? AND LOWER(TRIM(name)) = LOWER(TRIM(?))
             LIMIT 5");
        $stmt->execute([$id, $name, $e['table'], $id, $name]);
        $matches = $stmt->fetchAll();
        if (!$matches) { $this->flash($message, 'success'); return; }
        $this->flash($message . ' Possible duplicate, please check (nothing was merged): ' .
            implode(', ', array_map(fn($m) => "#{$m['id']} {$m['name']}", $matches)), 'warning');
    }

    private function fkLabels(array $e): array
    {
        $out = [];
        foreach ($e['fields'] as $name => $f) {
            if ($f['type'] === 'fk') $out[$name] = $this->fkOptions($f['fk']);
        }
        return $out;
    }

    /** id => label for a foreign-key dropdown. */
    private function fkOptions(string $fk): array
    {
        if ($fk === 'sources') {
            $rows = $this->db->query("SELECT id, COALESCE(NULLIF(title,''), NULLIF(contributor_name,''), CONCAT('Source #', id)) label, author FROM sources ORDER BY label")->fetchAll();
            return array_column(array_map(fn($r) => ['id' => $r['id'], 'label' => $r['label'] . ($r['author'] ? ' — ' . $r['author'] : '')], $rows), 'label', 'id');
        }
        $t = HeritageRegistry::get($fk);
        $extra = $fk === 'admin-units' ? ", CONCAT(name, ' (', REPLACE(unit_type, '_', ' '), ')')" : ", {$t['title_field']}";
        return array_column($this->db->query("SELECT id{$extra} AS label FROM {$t['table']} ORDER BY label")->fetchAll(), 'label', 'id');
    }

    private function renderForm(array $e, ?array $item): void
    {
        $data = [
            'title'       => ($item ? 'Edit: ' . $item[$e['title_field']] : 'Add ' . $e['label']) . ' | Nigeria Heritage | Admin',
            'currentPage' => 'heritage',
            'e'           => $e,
            'item'        => $item,
            'fkOptions'   => $this->fkLabels($e),
        ];
        if ($item && empty($e['no_knowledge'])) {
            $data += $this->knowledgePanel($e, $item);
        }
        if ($item && !empty($e['panels'])) {
            $data += $this->panelData($e, $item);
        }
        if ($item && $this->hasColumn($e['table'], 'stable_id')) {
            $data['stableId'] = $item['stable_id'] ?? null;
        }
        if ($item && !empty($e['readonly'])) {
            $stmt = $this->db->prepare("SELECT c.code FROM collection_items ci JOIN collections c ON c.id = ci.collection_id WHERE ci.entity_table = ? AND ci.entity_id = ?");
            $stmt->execute([$e['table'], (int) $item['id']]);
            $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $data['collections'] = $codes ?: ['tiv'];   // untagged rows are Tiv (see Model::collectionScope)
            $data['publicUrl'] = HeritagePublic::recordUrl($e['table'], $item);
        }
        $this->render('admin/heritage/form', $data, 'admin');
    }

    /** Rows and choices for the migration-002 panels shown on this record's form. */
    private function panelData(array $e, array $item): array
    {
        $id = (int) $item['id'];
        $q = function (string $sql, array $p) { $s = $this->db->prepare($sql); $s->execute($p); return $s->fetchAll(); };
        $out = ['panelSources' => $this->fkOptions('sources'), 'panelBatches' => $this->fkOptions('research-batches'), 'attributeKinds' => self::ATTRIBUTES];
        if (in_array('claim_sources', $e['panels'], true)) {
            $out['claimSources'] = $q("SELECT cs.*, s.title, s.author, s.source_tier, s.lineage_group FROM claim_sources cs JOIN sources s ON s.id = cs.source_id
                                       WHERE cs.claim_id = ? ORDER BY cs.id", [$id]);
            $out['claimSubject'] = $this->recordName($item['subject_table'], (int) $item['subject_id']);
            $out['claimObject'] = $item['object_table'] ? $this->recordName($item['object_table'], (int) $item['object_id']) : null;
        }
        if (in_array('attributes', $e['panels'], true)) {
            $out['attributes'] = $q("SELECT a.*, s.title source_title FROM cultural_record_attributes a LEFT JOIN sources s ON s.id = a.source_id
                                     WHERE a.cultural_record_id = ? ORDER BY a.attribute, a.sort_order, a.id", [$id]);
        }
        if (in_array('media_links', $e['panels'], true)) {
            $links = $q("SELECT * FROM media_links WHERE media_id = ? ORDER BY id", [$id]);
            foreach ($links as &$l) $l['name'] = $this->recordName($l['entity_table'], (int) $l['entity_id']);
            unset($l);
            $out['mediaLinks'] = $links;
            $out['mediaTargets'] = $this->linkTargets();
        }
        return $out;
    }

    /** Every linkable record, grouped by type, for "related record" dropdowns. */
    private function linkTargets(): array
    {
        $targets = [];
        foreach (HeritageRegistry::LINKABLE as $table => $label) {
            $col = HeritageRegistry::NAME_COLUMN[$table];
            foreach ($this->db->query("SELECT id, {$col} AS name FROM {$table} ORDER BY {$col} LIMIT 2000")->fetchAll() as $row) {
                $targets[$label][$table . ':' . $row['id']] = $row['name'];
            }
        }
        return $targets;
    }

    private function knowledgePanel(array $e, array $item): array
    {
        $t = $e['table'];
        $id = (int) $item['id'];
        $q = function (string $sql, array $p) { $s = $this->db->prepare($sql); $s->execute($p); return $s->fetchAll(); };

        $relations = $q("SELECT r.*, rt.label, rt.inverse_label FROM entity_relations r JOIN relation_types rt ON rt.code = r.relation_type
                         WHERE (r.from_table = ? AND r.from_id = ?) OR (r.to_table = ? AND r.to_id = ?) ORDER BY r.id", [$t, $id, $t, $id]);
        foreach ($relations as &$r) {
            $outgoing = $r['from_table'] === $t && (int) $r['from_id'] === $id;
            $r['reads'] = $outgoing ? $r['label'] : $r['inverse_label'];
            $r['other_table'] = $outgoing ? $r['to_table'] : $r['from_table'];
            $r['other_id'] = $outgoing ? $r['to_id'] : $r['from_id'];
            $r['other_name'] = $this->recordName($r['other_table'], (int) $r['other_id']);
        }
        unset($r);

        $targets = $this->linkTargets();

        return [
            'sources'     => $q("SELECT es.*, s.title, s.author, s.contributor_name, s.source_type, s.source_kind, s.source_tier, s.lineage_group
                                 FROM entity_sources es JOIN sources s ON s.id = es.source_id
                                 WHERE es.entity_table = ? AND es.entity_id = ? ORDER BY es.id", [$t, $id]),
            'names'       => $q("SELECT * FROM entity_names WHERE entity_table = ? AND entity_id = ? ORDER BY name", [$t, $id]),
            'statistics'  => $q("SELECT st.*, s.title source_title FROM entity_statistics st JOIN sources s ON s.id = st.source_id
                                 WHERE st.entity_table = ? AND st.entity_id = ? ORDER BY st.metric, st.reference_year", [$t, $id]),
            'relations'   => $relations,
            'changes'     => $t === 'admin_units' ? $q(
                                "SELECT c.*, f.name from_name, tu.name to_name FROM admin_unit_changes c
                                 LEFT JOIN admin_units f ON f.id = c.from_unit_id LEFT JOIN admin_units tu ON tu.id = c.to_unit_id
                                 WHERE c.from_unit_id = ? OR c.to_unit_id = ? ORDER BY c.effective_date, c.id", [$id, $id]) : [],
            'allSources'  => $this->fkOptions('sources'),
            'allBatches'  => $this->fkOptions('research-batches'),
            'allLanguages'=> $this->fkOptions('languages'),
            'allUnits'    => $this->fkOptions('admin-units'),
            'relationTypes' => $this->db->query("SELECT * FROM relation_types ORDER BY label")->fetchAll(),
            'targets'     => $targets,
        ];
    }

    private function recordName(string $table, int $id): string
    {
        if (!isset(HeritageRegistry::NAME_COLUMN[$table])) return "#{$id}";
        $stmt = $this->db->prepare("SELECT " . HeritageRegistry::NAME_COLUMN[$table] . " FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        return (string) ($stmt->fetchColumn() ?: "#{$id} (missing)");
    }

    private function nullIfEmpty(string $key)
    {
        $v = $this->post($key);
        return (is_string($v) && trim($v) !== '') ? trim($v) : null;
    }

    private function enum(string $key, array $allowed, ?string $default)
    {
        $v = $this->post($key);
        return in_array($v, $allowed, true) ? $v : $default;
    }

    private function friendlyDbError(PDOException $ex): string
    {
        $msg = $ex->getMessage();
        if (str_contains($msg, '1451')) return 'other records still refer to this one (e.g. units inside it, or a place used as a capital). Remove those links first.';
        if (str_contains($msg, '1062')) return 'a record with the same unique value (slug or code) already exists.';
        if (str_contains($msg, '1452')) return 'a linked record does not exist.';
        error_log('AdminHeritageController: ' . $msg);
        return 'the database rejected the change.';
    }

    private function failForm(array $errors): void
    {
        $this->storeOldInput();
        $_SESSION['errors'] = $errors;
        $this->back();
    }

    private function backToEdit(array $e, string $id): void
    {
        $this->redirect(url("admin/heritage/{$e['key']}/{$id}/edit") . '#knowledge');
    }

    private function notFound(array $e): void
    {
        $this->flash("{$e['label']} not found.", 'error');
        $this->redirect(url("admin/heritage/{$e['key']}"));
    }
}
