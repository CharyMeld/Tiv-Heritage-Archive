<?php
/**
 * Admin — API Key Management Controller
 */

class AdminApiKeyController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function index(): void
    {
        $db   = Database::getInstance();
        $keys = $db->query(
            "SELECT * FROM api_keys ORDER BY created_at DESC"
        )->fetchAll();

        $this->render('admin/api-keys', [
            'title'       => 'API Keys',
            'keys'        => $keys,
            'currentPage' => 'api_keys',
        ], 'admin');
    }

    public function revoke(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare(
            "UPDATE api_keys SET status = 'revoked' WHERE id = ?"
        )->execute([$id]);

        $this->flash('API key revoked successfully.', 'success');
        $this->redirect(url('admin/api-keys'));
    }

    public function restore(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare(
            "UPDATE api_keys SET status = 'active' WHERE id = ?"
        )->execute([$id]);

        $this->flash('API key restored successfully.', 'success');
        $this->redirect(url('admin/api-keys'));
    }

    public function updateLimit(int $id): void
    {
        $limit = max(1, (int) ($_POST['daily_limit'] ?? 1000));
        $db    = Database::getInstance();
        $db->prepare(
            "UPDATE api_keys SET daily_limit = ? WHERE id = ?"
        )->execute([$limit, $id]);

        $this->flash('Daily limit updated.', 'success');
        $this->redirect(url('admin/api-keys'));
    }
}
