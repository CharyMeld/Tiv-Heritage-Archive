<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/TivGrammarRule.php';

class AdminGrammarController extends Controller
{
    private TivGrammarRule $model;

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->model = new TivGrammarRule();
    }

    public function index(): void
    {
        $grouped = $this->model->getAllGrouped();

        $counts = [];
        foreach (TivGrammarRule::CATEGORIES as $category) {
            $counts[$category] = count($grouped[$category]);
        }

        $this->render('admin/grammar/index', [
            'title'       => 'Manage Grammar | Admin',
            'currentPage' => 'grammar',
            'grouped'     => $grouped,
            'counts'      => $counts,
        ], 'admin');
    }

    public function create(): void
    {
        $this->render('admin/grammar/form', [
            'title'       => 'Add Grammar Rule | Admin',
            'currentPage' => 'grammar',
            'rule'        => null,
        ], 'admin');
    }

    public function store(): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        [$data, $error] = $this->collectInput();
        if ($error) {
            $this->flash($error, 'error');
            $this->back();
            return;
        }

        $this->model->create($data);

        $this->flash('Grammar rule "' . $data['title'] . '" added.', 'success');
        $this->redirect(url('admin/grammar'));
    }

    public function edit(string $id): void
    {
        $rule = $this->model->find((int) $id);
        if (!$rule) { $this->redirect(url('admin/grammar')); return; }
        $rule['examples'] = json_decode($rule['examples'] ?? '', true) ?: [];

        $this->render('admin/grammar/form', [
            'title'       => 'Edit: ' . $rule['title'] . ' | Admin',
            'currentPage' => 'grammar',
            'rule'        => $rule,
        ], 'admin');
    }

    public function update(string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $rule = $this->model->find((int) $id);
        if (!$rule) { $this->redirect(url('admin/grammar')); return; }

        [$data, $error] = $this->collectInput();
        if ($error) {
            $this->flash($error, 'error');
            $this->back();
            return;
        }

        $this->model->update((int) $id, $data);

        $this->flash('"' . $data['title'] . '" updated.', 'success');
        $this->redirect(url('admin/grammar'));
    }

    public function delete(string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }
        $this->model->delete((int) $id);
        $this->flash('Grammar rule deleted.', 'info');
        $this->redirect(url('admin/grammar'));
    }

    /* ── Private helpers ─────────────────────────────────────────────── */

    /**
     * Reads and validates the shared store/update form fields.
     * @return array{0: array, 1: ?string} [data, errorMessage]
     */
    private function collectInput(): array
    {
        $category = trim($_POST['category'] ?? '');
        $title    = trim($_POST['title'] ?? '');

        if (!in_array($category, TivGrammarRule::CATEGORIES, true)) {
            return [[], 'A valid category is required.'];
        }
        if (!$title) {
            return [[], 'Title is required.'];
        }

        $examplesRaw = trim($_POST['examples_json'] ?? '[]');
        $examples    = json_decode($examplesRaw, true);
        if (!is_array($examples)) {
            return [[], 'Examples must be valid JSON (an array of {tiv, english, note?} objects).'];
        }

        return [[
            'category'    => $category,
            'title'       => $title,
            'summary'     => trim($_POST['summary'] ?? ''),
            'explanation' => trim($_POST['explanation'] ?? ''),
            'examples'    => $examples,
            'source_note' => trim($_POST['source_note'] ?? ''),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ], null];
    }
}
