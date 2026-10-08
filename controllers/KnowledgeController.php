<?php
/**
 * Knowledge Graph Admin Controller
 * Manages Sources (References) and Knowledge Links
 */

require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/KnowledgeLink.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivAnimal.php';

class KnowledgeController extends Controller
{
    private Source $sourceModel;
    private KnowledgeLink $linkModel;

    public function __construct()
    {
        parent::__construct();
        $this->sourceModel = new Source();
        $this->linkModel   = new KnowledgeLink();
    }

    /* ──────────────────────────────────────────
       SOURCES
    ────────────────────────────────────────── */

    /** List all sources */
    public function sources(): void
    {
        $this->requireModerator();
        $grouped = $this->sourceModel->getGroupedByType();
        $total   = $this->sourceModel->count();

        $this->render('admin/knowledge/sources', [
            'title'      => 'Manage Sources & Contributors',
            'grouped'    => $grouped,
            'total'      => $total,
            'typeLabels' => Source::$typeLabels,
            'typeIcons'  => Source::$typeIcons,
            'currentPage'=> 'sources',
        ], 'admin');
    }

    /** Create source form */
    public function createSource(): void
    {
        $this->requireModerator();
        $this->render('admin/knowledge/source-form', [
            'title'      => 'Add Source / Contributor',
            'source'     => null,
            'typeLabels' => Source::$typeLabels,
            'currentPage'=> 'sources',
        ], 'admin');
    }

    /** Store new source */
    public function storeSource(): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $id = $this->sourceModel->create([
            'source_type'      => $this->post('source_type'),
            'title'            => $this->post('title'),
            'author'           => $this->post('author'),
            'contributor_name' => $this->post('contributor_name'),
            'location'         => $this->post('location'),
            'year_recorded'    => $this->post('year_recorded') ?: null,
            'notes'            => $this->post('notes'),
        ] + $this->researchSourceFields());

        $this->flash('Source added successfully.', 'success');
        $this->redirect(url('admin/sources'));
    }

    /** Edit source form */
    public function editSource(string $id): void
    {
        $this->requireModerator();
        $source = $this->sourceModel->find((int) $id);
        if (!$source) { $this->flash('Source not found.', 'error'); $this->redirect(url('admin/sources')); return; }

        $this->render('admin/knowledge/source-form', [
            'title'      => 'Edit Source',
            'source'     => $source,
            'typeLabels' => Source::$typeLabels,
            'currentPage'=> 'sources',
        ], 'admin');
    }

    /** Update source */
    public function updateSource(string $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->sourceModel->update((int) $id, [
            'source_type'      => $this->post('source_type'),
            'title'            => $this->post('title'),
            'author'           => $this->post('author'),
            'contributor_name' => $this->post('contributor_name'),
            'location'         => $this->post('location'),
            'year_recorded'    => $this->post('year_recorded') ?: null,
            'notes'            => $this->post('notes'),
        ] + $this->researchSourceFields());

        $this->flash('Source updated.', 'success');
        $this->redirect(url('admin/sources'));
    }

    /**
     * Citation fields used by national research (publisher, URL, access date, …).
     * The form always posts them pre-filled, so saving never blanks existing values.
     */
    private function researchSourceFields(): array
    {
        $out = [];
        foreach (['publisher', 'organisation', 'url', 'publication_date', 'publication_details', 'archive_reference', 'isbn', 'doi'] as $f) {
            $v = trim((string) $this->post($f, ''));
            $out[$f] = $v !== '' ? $v : null;
        }
        $date = trim((string) $this->post('access_date', ''));
        $out['access_date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
        $status = $this->post('verification_status');
        $out['verification_status'] = in_array($status, ['verified', 'needs_corroboration', 'disputed'], true) ? $status : 'needs_corroboration';
        // Source type and tier (NIGERIA_STEP5 §4); sources that copy each other share a copy group.
        require_once BASE_PATH . '/services/HeritageRegistry.php';
        $kind = $this->post('source_kind');
        $out['source_kind'] = isset(HeritageRegistry::SOURCE_KIND[$kind]) ? $kind : null;
        $tier = (int) $this->post('source_tier', 0);
        $out['source_tier'] = $tier >= 1 && $tier <= 5 ? $tier : null;
        $lineage = strtoupper(trim((string) $this->post('lineage_group', '')));
        $out['lineage_group'] = $lineage !== '' ? substr(preg_replace('/[^A-Z0-9-]+/', '-', $lineage), 0, 40) : null;
        $rights = trim((string) $this->post('rights_notes', ''));
        $out['rights_notes'] = $rights !== '' ? $rights : null;
        return $out;
    }

    /** Delete source */
    public function deleteSource(string $id): void
    {
        $this->requireAdmin();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->sourceModel->delete((int) $id);
        $this->flash('Source deleted.', 'info');
        $this->redirect(url('admin/sources'));
    }

    /* ──────────────────────────────────────────
       KNOWLEDGE LINKS
    ────────────────────────────────────────── */

    /** List all links */
    public function links(): void
    {
        $this->requireModerator();

        $db   = Database::getInstance();
        $stmt = $db->query("SELECT * FROM knowledge_links ORDER BY created_at DESC LIMIT 200");
        $links = $stmt->fetchAll();

        // Enrich each link with human-readable labels
        $linkModel = new KnowledgeLink();
        foreach ($links as &$link) {
            $link['source_label'] = $linkModel->getItemLabel($link['source_table'], (int)$link['source_id']);
            $link['target_label'] = $linkModel->getItemLabel($link['target_table'], (int)$link['target_id']);
        }
        unset($link);

        $this->render('admin/knowledge/links', [
            'title'      => 'Knowledge Graph Links',
            'links'      => $links,
            'tableConfig'=> KnowledgeLink::$tableConfig,
            'currentPage'=> 'links',
        ], 'admin');
    }

    /** Create link form */
    public function createLink(): void
    {
        $this->requireModerator();

        // Load all items per table for dropdowns
        $items = $this->getAllContentItems();

        $this->render('admin/knowledge/link-create', [
            'title'      => 'Add Knowledge Graph Link',
            'items'      => $items,
            'tableConfig'=> KnowledgeLink::$tableConfig,
            'currentPage'=> 'links',
        ], 'admin');
    }

    /** Store new link */
    public function storeLink(): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $sourceTable  = $this->post('source_table');
        $sourceId     = (int) $this->post('source_id');
        $targetTable  = $this->post('target_table');
        $targetId     = (int) $this->post('target_id');
        $relationType = trim($this->post('relation_type'));

        if (!$sourceTable || !$sourceId || !$targetTable || !$targetId || !$relationType) {
            $this->flash('All fields are required.', 'error');
            $this->back();
            return;
        }

        if ($sourceTable === $targetTable && $sourceId === $targetId) {
            $this->flash('An item cannot link to itself.', 'error');
            $this->back();
            return;
        }

        $this->linkModel->addLink($sourceTable, $sourceId, $targetTable, $targetId, $relationType);

        $this->flash('Knowledge link added successfully.', 'success');
        $this->redirect(url('admin/links'));
    }

    /** Delete link */
    public function deleteLink(string $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->linkModel->delete((int) $id);
        $this->flash('Link removed.', 'info');
        $this->redirect(url('admin/links'));
    }

    /* ──────────────────────────────────────────
       HELPERS
    ────────────────────────────────────────── */

    /** Load top 300 items from each content table for dropdowns */
    private function getAllContentItems(): array
    {
        $db = Database::getInstance();
        $result = [];

        $queries = [
            'tiv_names'    => "SELECT id, tiv_name AS label FROM tiv_names ORDER BY tiv_name LIMIT 300",
            'tiv_proverbs' => "SELECT id, LEFT(tiv_text,70) AS label FROM tiv_proverbs ORDER BY id LIMIT 300",
            'tiv_plants'   => "SELECT id, tiv_name AS label FROM tiv_plants ORDER BY tiv_name LIMIT 300",
            'tiv_festivals'=> "SELECT id, tiv_name AS label FROM tiv_festivals ORDER BY tiv_name LIMIT 300",
            'tiv_foods'    => "SELECT id, tiv_name AS label FROM tiv_foods ORDER BY tiv_name LIMIT 300",
            'daily_words'  => "SELECT id, tiv_word AS label FROM daily_words ORDER BY tiv_word LIMIT 300",
            'tiv_animals'  => "SELECT id, tiv_name AS label FROM tiv_animals ORDER BY tiv_name LIMIT 300",
            'historical_figures' => "SELECT id, english_name AS label FROM historical_figures ORDER BY english_name LIMIT 300",
        ];

        foreach ($queries as $table => $sql) {
            $stmt = $db->query($sql);
            $result[$table] = $stmt->fetchAll();
        }

        return $result;
    }
}
