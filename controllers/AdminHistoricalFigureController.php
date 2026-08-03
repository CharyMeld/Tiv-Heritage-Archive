<?php
/**
 * Admin controller for Historical Figures CRUD + gallery management
 */

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/HistoricalFigure.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/services/ContentIndexer.php';
require_once BASE_PATH . '/services/ImageVariantGenerator.php';

class AdminHistoricalFigureController extends Controller
{
    private HistoricalFigure $model;
    private Source $sourceModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->model = new HistoricalFigure();
        $this->sourceModel = new Source();
    }

    public function index(): void
    {
        $total = $this->model->count();
        $pagination = $this->paginate($total, 20);
        $items = $this->model->paginate($pagination['per_page'], $pagination['offset'], 'created_at', 'DESC');

        $this->render('admin/historical-figures/index', [
            'title'       => 'Historical Figures | Admin',
            'currentPage' => 'historical_figures',
            'items'       => $items,
            'pagination'  => $pagination,
        ], 'admin');
    }

    public function create(): void
    {
        $this->render('admin/historical-figures/form', [
            'title'       => 'Add Historical Figure | Admin',
            'currentPage' => 'historical_figures',
            'item'        => null,
            'gallery'     => [],
            'sources'     => $this->sourceModel->getAllOrdered(),
        ], 'admin');
    }

    public function store(): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $errors = $this->validateRequired(['english_name' => 'English name', 'category' => 'Category']);
        if ($errors) {
            $this->storeOldInput();
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        $data = $this->collectFields();
        $data['created_by'] = $this->user['id'];

        $image = $this->saveImageFile($_FILES['image'] ?? [], 'figure');
        if ($image === false) { $this->back(); return; }
        if ($image !== null) { $data['image'] = $image; }

        $id = $this->model->create($data);
        $this->model->update($id, ['slug' => $this->slugify($data['english_name']) . '-' . $id]);
        $this->clearOldInput();

        $this->silentAutoIndex($id);
        $this->bustHistoricalFigureCaches();
        Security::logActivity($this->user['id'], 'historical_figure_created', 'historical_figures', $id);
        $this->flash('"' . $data['english_name'] . '" added.', 'success');
        $this->redirect(url('admin/historical-figures'));
    }

    public function edit(string $id): void
    {
        $item = $this->model->find((int) $id);
        if (!$item) {
            $this->flash('Historical figure not found.', 'error');
            $this->redirect(url('admin/historical-figures'));
            return;
        }

        $this->render('admin/historical-figures/form', [
            'title'       => 'Edit: ' . $item['english_name'] . ' | Admin',
            'currentPage' => 'historical_figures',
            'item'        => $item,
            'gallery'     => $this->model->getGallery((int) $id),
            'sources'     => $this->sourceModel->getAllOrdered(),
        ], 'admin');
    }

    public function update(string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $item = $this->model->find((int) $id);
        if (!$item) {
            $this->flash('Historical figure not found.', 'error');
            $this->redirect(url('admin/historical-figures'));
            return;
        }

        $errors = $this->validateRequired(['english_name' => 'English name', 'category' => 'Category']);
        if ($errors) {
            $this->storeOldInput();
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        $data = $this->collectFields();

        if (!empty($_FILES['image']['name'])) {
            $image = $this->saveImageFile($_FILES['image'], 'figure');
            if ($image === false) { $this->back(); return; }
            if ($image !== null) { $data['image'] = $image; }
        }

        $this->model->update((int) $id, $data);
        $this->silentAutoIndex((int) $id);
        $this->bustHistoricalFigureCaches();
        Security::logActivity($this->user['id'], 'historical_figure_updated', 'historical_figures', (int) $id);
        $this->flash('"' . $data['english_name'] . '" updated.', 'success');
        $this->redirect(url('admin/historical-figures'));
    }

    public function delete(string $id): void
    {
        $this->requireAdmin();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $item = $this->model->find((int) $id);
        if ($item) {
            $this->model->delete((int) $id);
            $this->silentRemoveFromIndex((int) $id);
            $this->bustHistoricalFigureCaches();
            Security::logActivity($this->user['id'], 'historical_figure_deleted', 'historical_figures', (int) $id);
            $this->flash('"' . $item['english_name'] . '" deleted.', 'info');
        }
        $this->redirect(url('admin/historical-figures'));
    }

    /* ── Gallery ──────────────────────────────────────────────── */

    public function uploadGalleryPhoto(string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $figure = $this->model->find((int) $id);
        if (!$figure) {
            $this->flash('Historical figure not found.', 'error');
            $this->redirect(url('admin/historical-figures'));
            return;
        }

        if (empty($_FILES['gallery_image']['name'])) {
            $this->flash('No image file selected.', 'error');
            $this->redirect(url('admin/historical-figures/' . $id . '/edit'));
            return;
        }

        $filename = $this->saveImageFile($_FILES['gallery_image'], 'figure');
        if ($filename === false || $filename === null) {
            $this->redirect(url('admin/historical-figures/' . $id . '/edit'));
            return;
        }

        $caption = trim($this->post('caption') ?? '') ?: null;
        $altText = trim($this->post('alt_text') ?? '') ?: null;
        $featured = $this->post('is_featured') ? 1 : 0;

        $this->model->addGalleryPhoto((int) $id, $filename, $caption, $altText, $featured, $this->user['id']);
        Security::logActivity($this->user['id'], 'gallery_photo_uploaded', 'historical_figures', (int) $id);
        $this->flash('Photo added to gallery.', 'success');
        $this->redirect(url('admin/historical-figures/' . $id . '/edit'));
    }

    public function deleteGalleryPhoto(string $id, string $photoId): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $photo = $this->model->getGalleryPhoto((int) $photoId);
        if ($photo && (int) $photo['figure_id'] === (int) $id) {
            $filePath = UPLOADS_PATH . '/images/' . $photo['image_path'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $this->model->removeGalleryPhoto((int) $photoId, (int) $id);
            Security::logActivity($this->user['id'], 'gallery_photo_deleted', 'historical_figures', (int) $id);
            $this->flash('Photo removed from gallery.', 'success');
        } else {
            $this->flash('Photo not found.', 'error');
        }

        $this->redirect(url('admin/historical-figures/' . $id . '/edit'));
    }

    /* ── Helpers ──────────────────────────────────────────────── */

    private function collectFields(): array
    {
        $category = $this->post('category');
        return [
            'english_name'             => trim($this->post('english_name') ?? ''),
            'tiv_name'                 => trim($this->post('tiv_name') ?? '') ?: null,
            'title'                    => trim($this->post('title') ?? '') ?: null,
            'category'                 => $category,
            'subcategory'              => $category === 'Traditional Leadership' ? ($this->post('subcategory') ?: null) : null,
            'reign_order'              => $category === 'Traditional Leadership' ? ($this->post('reign_order') ?: null) : null,
            'gender'                   => $this->post('gender') ?: null,
            'date_of_birth'            => trim($this->post('date_of_birth') ?? '') ?: null,
            'birth_year'               => $this->post('birth_year') ?: null,
            'place_of_birth'           => trim($this->post('place_of_birth') ?? '') ?: null,
            'date_of_death'            => trim($this->post('date_of_death') ?? '') ?: null,
            'death_year'               => $this->post('death_year') ?: null,
            'burial_place'             => trim($this->post('burial_place') ?? '') ?: null,
            'clan'                     => trim($this->post('clan') ?? '') ?: null,
            'district'                 => trim($this->post('district') ?? '') ?: null,
            'local_government'        => trim($this->post('local_government') ?? '') ?: null,
            'state'                    => trim($this->post('state') ?? '') ?: 'Benue',
            'country'                  => trim($this->post('country') ?? '') ?: 'Nigeria',
            'religion'                 => trim($this->post('religion') ?? '') ?: null,
            'occupation'               => trim($this->post('occupation') ?? '') ?: null,
            'historical_period'        => $this->post('historical_period') ?: null,
            'short_summary'            => trim($this->post('short_summary') ?? '') ?: null,
            'biography'                => $this->post('biography') ?: null,
            'early_life'               => $this->post('early_life') ?: null,
            'education'                => $this->post('education') ?: null,
            'career'                   => $this->post('career') ?: null,
            'leadership_service'       => $this->post('leadership_service') ?: null,
            'achievements'             => $this->post('achievements') ?: null,
            'historical_significance' => $this->post('historical_significance') ?: null,
            'legacy'                   => $this->post('legacy') ?: null,
            'timeline_notes'           => $this->post('timeline_notes') ?: null,
            'references_text'          => $this->post('references_text') ?: null,
            'source_id'                => $this->post('source_id') ?: null,
            'status'                   => in_array($this->post('status'), ['draft', 'published'], true) ? $this->post('status') : 'draft',
            'is_featured'              => $this->post('is_featured') ? 1 : 0,
        ];
    }

    private function slugify(string $text): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
        return $slug ?: 'figure';
    }

    /**
     * Save uploaded image file
     * @return string|false|null Filename on success, false on error, null if no file
     */
    private function saveImageFile(array $file, string $prefix = 'figure'): string|false|null
    {
        if (empty($file['name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('Image upload failed. Please try again.', 'error');
            return false;
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $this->flash('Image file is too large. Maximum size is 5MB.', 'error');
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $this->flash('Invalid image file type. Allowed: JPG, PNG, GIF, WebP.', 'error');
            return false;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            $this->flash('Invalid image file extension.', 'error');
            return false;
        }

        $uploadPath = UPLOADS_PATH . '/images';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath . '/' . $filename)) {
            $this->flash('Failed to save the uploaded image.', 'error');
            return false;
        }

        ImageVariantGenerator::generate($uploadPath . '/' . $filename);

        return $filename;
    }

    /** Clear the public browse UI's cached category/subcategory counts so admin changes show up immediately */
    private function bustHistoricalFigureCaches(): void
    {
        Cache::forget('hf_category_counts');
        Cache::forget('hf_sidebar_tree');
    }

    private function silentAutoIndex(int $id): void
    {
        try {
            (new ContentIndexer(Database::getInstance()))->indexRecord('historical_figures', $id);
        } catch (\Throwable $e) {
            error_log('AutoIndex error [historical_figures#' . $id . ']: ' . $e->getMessage());
        }
    }

    private function silentRemoveFromIndex(int $id): void
    {
        try {
            (new ContentIndexer(Database::getInstance()))->removeRecord('historical_figures', $id);
        } catch (\Throwable $e) {
            error_log('RemoveIndex error [historical_figures#' . $id . ']: ' . $e->getMessage());
        }
    }
}
