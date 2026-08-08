<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/Suggestion.php';
require_once BASE_PATH . '/services/OutreachMailer.php';

class AdminSuggestionsController extends Controller
{
    private Suggestion $model;

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->model = new Suggestion();
    }

    public function index(): void
    {
        $status   = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        $q        = trim($_GET['q'] ?? '');

        $total = $q
            ? count($this->model->searchSuggestions($q))
            : $this->model->countAll($status, $category);

        $pagination  = $this->paginate($total, 20);
        $suggestions = $q
            ? $this->model->search($q, 20)
            : $this->model->getAll($status, $category, 20, $pagination['offset']);

        $stats = $this->model->getStats();

        $this->render('admin/suggestions/index', [
            'title'           => 'Suggestions & Feedback | Admin',
            'currentPage'     => 'suggestions',
            'suggestions'     => $suggestions,
            'stats'           => $stats,
            'categories'      => Suggestion::categories(),
            'statuses'        => Suggestion::statuses(),
            'pagination'      => $pagination,
            'filter_status'   => $status,
            'filter_category' => $category,
            'q'               => $q,
        ], 'admin');
    }

    public function view(string $id): void
    {
        $suggestion = $this->model->find((int) $id);
        if (!$suggestion) {
            $this->redirect(url('admin/suggestions'));
            return;
        }

        // Auto-mark as read when opened
        if ($suggestion['status'] === 'new') {
            $this->model->update((int) $id, ['status' => 'read']);
            $suggestion['status'] = 'read';
        }

        $this->render('admin/suggestions/view', [
            'title'       => 'Suggestion: ' . $suggestion['subject'] . ' | Admin',
            'currentPage' => 'suggestions',
            'suggestion'  => $suggestion,
            'categories'  => Suggestion::categories(),
            'statuses'    => Suggestion::statuses(),
        ], 'admin');
    }

    public function updateStatus(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $suggestion = $this->model->find((int) $id);
        if (!$suggestion) {
            $this->flash('Suggestion not found.', 'error');
            $this->redirect(url('admin/suggestions'));
            return;
        }

        $status = $_POST['status'] ?? '';
        if (!array_key_exists($status, Suggestion::statuses())) {
            $this->flash('Invalid status.', 'error');
            $this->back();
            return;
        }

        $this->model->update((int) $id, [
            'status'      => $status,
            'admin_notes' => trim($_POST['admin_notes'] ?? '') ?: null,
            'handled_by'  => $this->user['id'],
        ]);

        $this->flash('Suggestion status updated.', 'success');
        $this->redirect(url('admin/suggestions/' . $id));
    }

    public function reply(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $suggestion = $this->model->find((int) $id);
        if (!$suggestion) {
            $this->flash('Suggestion not found.', 'error');
            $this->redirect(url('admin/suggestions'));
            return;
        }

        $reply = trim($_POST['admin_reply'] ?? '');
        if ($reply === '') {
            $this->flash('Reply message cannot be empty.', 'error');
            $this->redirect(url('admin/suggestions/' . $id));
            return;
        }

        $subject = 'Re: ' . $suggestion['subject'];
        $body = OutreachMailer::buildBody(
            '<p>Dear {{name}},</p><p>Thank you for reaching out to us regarding "' .
            htmlspecialchars($suggestion['subject'], ENT_QUOTES, 'UTF-8') . '". Here is our response:</p>' .
            '<blockquote style="margin:16px 0;padding:12px 16px;background:#f7f4ee;border-left:3px solid #C8A951;white-space:pre-line;">' .
            nl2br(htmlspecialchars($reply, ENT_QUOTES, 'UTF-8')) . '</blockquote>' .
            '<p>Warm regards,<br>{{site_name}} Team</p>',
            ['name' => $suggestion['full_name']]
        );

        $sent = OutreachMailer::send($suggestion['email'], $suggestion['full_name'], $subject, $body);

        $this->model->update((int) $id, [
            'admin_reply' => $reply,
            'replied_at'  => date('Y-m-d H:i:s'),
            'replied_by'  => $this->user['id'],
            'status'      => $suggestion['status'] === 'new' || $suggestion['status'] === 'read' ? 'in_progress' : $suggestion['status'],
        ]);

        $this->flash($sent ? 'Reply sent to ' . $suggestion['email'] . '.' : 'Reply saved, but the email failed to send.', $sent ? 'success' : 'error');
        $this->redirect(url('admin/suggestions/' . $id));
    }

    public function export(): void
    {
        $suggestions = $this->model->getAll('', '', 9999, 0);
        $categories  = Suggestion::categories();
        $statuses    = Suggestion::statuses();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="suggestions_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Name', 'Email', 'Subject', 'Category', 'Status', 'Submitted']);
        foreach ($suggestions as $s) {
            fputcsv($out, [
                $s['id'], $s['full_name'], $s['email'], $s['subject'],
                $categories[$s['category']] ?? $s['category'],
                $statuses[$s['status']] ?? $s['status'],
                $s['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
