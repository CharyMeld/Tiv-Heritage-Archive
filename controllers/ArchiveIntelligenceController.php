<?php
/**
 * ArchiveIntelligenceController — Admin panel for the local AI system.
 *
 * GET  /admin/intelligence       — Status dashboard
 * POST /admin/intelligence/reindex — Rebuild the full search index
 */

require_once BASE_PATH . '/services/ContentIndexer.php';
require_once BASE_PATH . '/services/FileIndexer.php';

class ArchiveIntelligenceController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $db = Database::getInstance();

        // Index stats
        $stats = [];
        try {
            $stats['total_indexed']   = (int) $db->query("SELECT COUNT(*) FROM archive_search_index")->fetchColumn();
            $stats['by_type']         = $db->query(
                "SELECT content_type, COUNT(*) AS cnt FROM archive_search_index GROUP BY content_type ORDER BY cnt DESC"
            )->fetchAll();
            $stats['last_updated']    = $db->query("SELECT MAX(updated_at) FROM archive_search_index")->fetchColumn();
            $stats['knowledge_links'] = (int) $db->query("SELECT COUNT(*) FROM archive_knowledge_links")->fetchColumn();
            $stats['ngrams']          = (int) $db->query("SELECT COUNT(*) FROM translation_ngrams")->fetchColumn();
        } catch (\PDOException $e) {
            $stats['error'] = 'Search index tables not yet created. Run the migration first.';
        }

        // File extraction stats
        try {
            $stats['files_pending'] = (int) $db->query(
                "SELECT COUNT(*) FROM content_items WHERE extraction_status = 'pending'"
            )->fetchColumn();
            $stats['files_done']    = (int) $db->query(
                "SELECT COUNT(*) FROM content_items WHERE extraction_status = 'done'"
            )->fetchColumn();
            $stats['files_failed']  = (int) $db->query(
                "SELECT COUNT(*) FROM content_items WHERE extraction_status = 'failed'"
            )->fetchColumn();
        } catch (\PDOException $e) {
            // extracted_text column not yet added
            $stats['extract_col_missing'] = true;
        }

        $stats['tools'] = FileIndexer::availableTools();

        $this->render('admin/intelligence', [
            'title'       => 'Archive Intelligence',
            'currentPage' => 'admin',
            'stats'       => $stats,
        ]);
    }

    public function reindex(): void
    {
        $this->requireAdmin();
        $this->requirePost();

        set_time_limit(300);

        $db      = Database::getInstance();
        $indexer = new ContentIndexer($db);

        try {
            $total = $indexer->indexAll();
            $this->json([
                'success' => true,
                'message' => "Indexed {$total} records. Knowledge graph and n-gram stats rebuilt.",
                'total'   => $total,
            ]);
        } catch (\Throwable $e) {
            error_log('Reindex error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Reindex failed: ' . $e->getMessage()], 500);
        }
    }

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'] ?? '', ['admin', 'editor'])) {
            $this->redirect(url('login'));
        }
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            exit;
        }
    }
}
