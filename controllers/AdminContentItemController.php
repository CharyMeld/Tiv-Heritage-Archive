<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/ContentItem.php';
require_once BASE_PATH . '/models/ContentSubmission.php';
require_once BASE_PATH . '/services/ContentIndexer.php';
require_once BASE_PATH . '/services/FileIndexer.php';

class AdminContentItemController extends Controller
{
    private ContentItem $model;

    private array $validSections = ['language', 'literature', 'culture', 'history', 'archive'];

    private array $validSubs = [
        'language'   => ['alphabet'],
        'literature' => ['folktales', 'stories', 'poems'],
        'culture'    => ['traditions', 'attire', 'marriage-customs'],
        'history'    => ['origins', 'migration', 'timeline'],
        'archive'    => ['documents', 'audio', 'publications'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->model = new ContentItem();
    }

    public function index(string $section, string $sub): void
    {
        if (!$this->validSub($section, $sub)) {
            $this->redirect(url('admin'));
            return;
        }

        $total      = $this->model->adminCount($section, $sub);
        $pagination = $this->paginate($total, 20);
        $items      = $this->model->adminList($section, $sub, 20, $pagination['offset']);

        $this->render('admin/content-items/index', [
            'title'       => ContentItem::subcategoryLabel($sub) . ' | Admin',
            'currentPage' => 'content_items',
            'section'     => $section,
            'sub'         => $sub,
            'items'       => $items,
            'pagination'  => $pagination,
            'label'       => ContentItem::subcategoryLabel($sub),
        ], 'admin');
    }

    public function create(string $section, string $sub): void
    {
        if (!$this->validSub($section, $sub)) {
            $this->redirect(url('admin'));
            return;
        }

        $this->render('admin/content-items/form', [
            'title'       => 'Add ' . ContentItem::subcategoryLabel($sub) . ' | Admin',
            'currentPage' => 'content_items',
            'section'     => $section,
            'sub'         => $sub,
            'label'       => ContentItem::subcategoryLabel($sub),
            'item'        => null,
        ], 'admin');
    }

    public function store(string $section, string $sub): void
    {
        if (!$this->validateCSRF() || !$this->validSub($section, $sub)) {
            $this->back();
            return;
        }

        $title = trim($_POST['title'] ?? '');
        if (!$title) {
            $this->flash('Title is required.', 'error');
            $this->back();
            return;
        }

        $mediaFile = null;
        $mediaType = 'none';

        if (!empty($_FILES['media_file']['name'])) {
            $uploaded = $this->handleUpload('media_file', $section . '/' . $sub);
            if ($uploaded) {
                $mediaFile = $uploaded['path'];
                $mediaType = $uploaded['type'];
            }
        }

        $newId = $this->model->create([
            'section'      => $section,
            'subcategory'  => $sub,
            'title'        => $title,
            'tiv_title'    => trim($_POST['tiv_title']    ?? '') ?: null,
            'excerpt'      => trim($_POST['excerpt']      ?? '') ?: null,
            'tiv_excerpt'  => trim($_POST['tiv_excerpt']  ?? '') ?: null,
            'content'      => trim($_POST['content']      ?? '') ?: null,
            'tiv_content'  => trim($_POST['tiv_content']  ?? '') ?: null,
            'media_file'   => $mediaFile,
            'media_type'   => $mediaType,
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
            'status'       => $_POST['status'] ?? 'published',
            'created_by'   => $this->user['id'],
        ]);

        // Auto-index immediately; extract file text if a document/image was uploaded
        $this->autoIndex('content_items', $newId, $mediaFile, $mediaType);

        $this->flash(ContentItem::subcategoryLabel($sub) . ' item added successfully.', 'success');
        $this->redirect(url("admin/content-items/{$section}/{$sub}"));
    }

    public function edit(string $section, string $sub, string $id): void
    {
        $item = $this->model->find((int) $id);
        if (!$item) {
            $this->redirect(url("admin/content-items/{$section}/{$sub}"));
            return;
        }

        $this->render('admin/content-items/form', [
            'title'       => 'Edit: ' . $item['title'] . ' | Admin',
            'currentPage' => 'content_items',
            'section'     => $section,
            'sub'         => $sub,
            'label'       => ContentItem::subcategoryLabel($sub),
            'item'        => $item,
        ], 'admin');
    }

    public function update(string $section, string $sub, string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $item = $this->model->find((int) $id);
        if (!$item) {
            $this->redirect(url("admin/content-items/{$section}/{$sub}"));
            return;
        }

        $mediaFile = $item['media_file'];
        $mediaType = $item['media_type'];

        if (!empty($_FILES['media_file']['name'])) {
            $uploaded = $this->handleUpload('media_file', $section . '/' . $sub);
            if ($uploaded) {
                $mediaFile = $uploaded['path'];
                $mediaType = $uploaded['type'];
            }
        }

        $this->model->update((int) $id, [
            'title'       => trim($_POST['title']       ?? $item['title']),
            'tiv_title'   => trim($_POST['tiv_title']   ?? '') ?: null,
            'excerpt'     => trim($_POST['excerpt']      ?? '') ?: null,
            'tiv_excerpt' => trim($_POST['tiv_excerpt']  ?? '') ?: null,
            'content'     => trim($_POST['content']      ?? '') ?: null,
            'tiv_content' => trim($_POST['tiv_content']  ?? '') ?: null,
            'media_file'  => $mediaFile,
            'media_type'  => $mediaType,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'status'      => $_POST['status'] ?? 'published',
        ]);

        // Re-index immediately; extract new file text if file changed
        $newFile = ($mediaFile !== $item['media_file']) ? $mediaFile : null;
        $this->autoIndex('content_items', (int) $id, $newFile, $mediaType);

        $this->flash('Item updated successfully.', 'success');
        $this->redirect(url("admin/content-items/{$section}/{$sub}"));
    }

    public function delete(string $section, string $sub, string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->model->delete((int) $id);
        $this->silentRemoveIndex('content_items', (int) $id);
        $this->flash('Item deleted.', 'info');
        $this->redirect(url("admin/content-items/{$section}/{$sub}"));
    }

    public function submissions(string $section, string $sub): void
    {
        if (!$this->validSub($section, $sub)) {
            $this->redirect(url('admin'));
            return;
        }

        $subModel   = new ContentSubmission();
        $status     = $_GET['status'] ?? 'pending';
        $total      = $subModel->countPending($section, $sub);
        $pagination = $this->paginate($total, 20);
        $items      = $subModel->getAll($status, $section, $sub, 20, $pagination['offset']);

        $this->render('admin/content-items/submissions', [
            'title'      => 'Submissions: ' . ContentItem::subcategoryLabel($sub) . ' | Admin',
            'currentPage'=> 'content_items',
            'section'    => $section,
            'sub'        => $sub,
            'label'      => ContentItem::subcategoryLabel($sub),
            'items'      => $items,
            'pagination' => $pagination,
            'status'     => $status,
            'pendingCount' => $subModel->countPending($section, $sub),
        ], 'admin');
    }

    public function approveSubmission(string $section, string $sub, string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $subModel = new ContentSubmission();
        $sub_item = $subModel->find((int) $id);

        if (!$sub_item) {
            $this->flash('Submission not found.', 'error');
            $this->redirect(url("admin/content-items/{$section}/{$sub}/submissions"));
            return;
        }

        // Publish to content_items
        $newId = $this->model->create([
            'section'     => $sub_item['section'],
            'subcategory' => $sub_item['subcategory'],
            'title'       => $sub_item['title'],
            'tiv_title'   => $sub_item['tiv_title'],
            'excerpt'     => $sub_item['excerpt'],
            'tiv_excerpt' => $sub_item['tiv_excerpt'],
            'content'     => $sub_item['content'],
            'tiv_content' => $sub_item['tiv_content'],
            'media_file'  => $sub_item['media_file'],
            'media_type'  => $sub_item['media_type'],
            'is_featured' => 0,
            'status'      => 'published',
            'created_by'  => $this->user['id'],
        ]);

        // Auto-index approved submission immediately
        $this->autoIndex('content_items', $newId, $sub_item['media_file'], $sub_item['media_type']);

        $subModel->update((int) $id, [
            'status'      => 'approved',
            'admin_notes' => trim($_POST['admin_notes'] ?? '') ?: null,
            'reviewed_by' => $this->user['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('Submission approved and published.', 'success');
        $this->redirect(url("admin/content-items/{$section}/{$sub}/submissions"));
    }

    public function rejectSubmission(string $section, string $sub, string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $subModel = new ContentSubmission();
        $subModel->update((int) $id, [
            'status'      => 'rejected',
            'admin_notes' => trim($_POST['admin_notes'] ?? '') ?: null,
            'reviewed_by' => $this->user['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('Submission rejected.', 'info');
        $this->redirect(url("admin/content-items/{$section}/{$sub}/submissions"));
    }

    private function validSub(string $section, string $sub): bool
    {
        return isset($this->validSubs[$section]) && in_array($sub, $this->validSubs[$section]);
    }

    /**
     * Index a record immediately after save and extract file text if applicable.
     * Runs silently — never breaks the admin workflow if it fails.
     */
    private function autoIndex(string $table, int $id, ?string $mediaFile, string $mediaType): void
    {
        try {
            $db      = Database::getInstance();
            $indexer = new ContentIndexer($db);
            $indexer->indexRecord($table, $id);

            // Extract and store text from uploaded files (PDF, docx, images)
            if ($mediaFile && in_array($mediaType, ['document', 'image'])) {
                $fileIndexer = new FileIndexer($db);
                $fileIndexer->extractAndStore($id);
                // Re-index now that extracted_text is populated
                $indexer->indexRecord($table, $id);
            }
        } catch (\Throwable $e) {
            error_log('AutoIndex error [' . $table . '#' . $id . ']: ' . $e->getMessage());
        }
    }

    private function silentRemoveIndex(string $table, int $id): void
    {
        try {
            (new ContentIndexer(Database::getInstance()))->removeRecord($table, $id);
        } catch (\Throwable $e) {
            error_log('RemoveIndex error: ' . $e->getMessage());
        }
    }

    private function handleUpload(string $field, string $subDir): ?array
    {
        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $audioTypes = ['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/mp4', 'audio/webm'];
        $docTypes   = ['application/pdf', 'application/msword',
                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (in_array($mime, ALLOWED_IMAGE_TYPES)) {
            $mediaType = 'image';
        } elseif (in_array($mime, $audioTypes)) {
            $mediaType = 'audio';
        } elseif (in_array($mime, $docTypes)) {
            $mediaType = 'document';
        } else {
            return null;
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('ci_', true) . '.' . $ext;
        $dir      = UPLOADS_PATH . '/content/' . $subDir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return ['path' => 'content/' . $subDir . '/' . $filename, 'type' => $mediaType];
    }
}
