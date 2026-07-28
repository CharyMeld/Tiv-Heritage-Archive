<?php
/**
 * Admin Controller
 */

require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Submission.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/LearningVideo.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/CommunityApplication.php';
require_once BASE_PATH . '/models/CommunityMember.php';
require_once BASE_PATH . '/models/Suggestion.php';

class AdminController extends Controller
{
    private User $userModel;
    private Submission $submissionModel;
    private Source $sourceModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->submissionModel = new Submission();
        $this->sourceModel = new Source();
    }

    /**
     * Admin dashboard
     */
    public function index(): void
    {
        $this->requireModerator();

        $communityStats = [];
        $suggestionStats = [];
        try {
            $communityStats  = (new CommunityApplication())->getStats();
            $memberStats     = (new CommunityMember())->getStats();
            $suggestionStats = (new Suggestion())->getStats();
        } catch (\Exception $e) { /* tables may not exist yet */ }

        $stats = [
            'pending'  => $this->submissionModel->countPending(),
            'names'    => (new TivName())->count(),
            'proverbs' => (new TivProverb())->count(),
            'plants'   => (new TivPlant())->count(),
            'festivals'=> (new TivFestival())->count(),
            'foods'    => (new TivFood())->count(),
            'words'    => (new DailyWord())->countActive(),
            'animals'  => (new TivAnimal())->count(),
            'videos'   => (new LearningVideo())->count(),
            'users'    => $this->userModel->count(),
            'community_pending'     => $communityStats['pending'] ?? 0,
            'community_members'     => $memberStats['active'] ?? 0,
            'suggestions_new'       => $suggestionStats['new_count'] ?? 0,
        ];

        $recentSubmissions = $this->submissionModel->getPending(5);
        $recentUsers = $this->userModel->getRecent(5);

        $this->render('admin/index', [
            'title'             => 'Admin Dashboard',
            'stats'             => $stats,
            'recentSubmissions' => $recentSubmissions,
            'recentUsers'       => $recentUsers,
            'currentPage'       => 'dashboard'
        ], 'admin');
    }

    /**
     * Pending submissions
     */
    public function pending(): void
    {
        $this->requireModerator();

        $total = $this->submissionModel->countPending();
        $pagination = $this->paginate($total);
        $submissions = $this->submissionModel->getPending($pagination['per_page'], $pagination['offset']);

        $this->render('admin/pending', [
            'title' => 'Pending Submissions',
            'submissions' => $submissions,
            'pagination' => $pagination,
            'currentPage' => 'pending'
        ], 'admin');
    }

    /**
     * Approve submission
     */
    public function approve(string $id): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $submission = $this->submissionModel->find((int) $id);
        if (!$submission) {
            $this->flash('Submission not found.', 'error');
            $this->redirect(url('admin/pending'));
            return;
        }

        // Approve and optionally add to main content
        $this->submissionModel->approve((int) $id, $this->user['id'], $this->post('notes'));

        // Add to main content table if requested
        if ($this->post('add_to_content')) {
            $this->addToContent($submission);
        }

        Security::logActivity($this->user['id'], 'submission_approved', 'submission', (int) $id);

        $this->flash('Submission approved successfully.', 'success');
        $this->redirect(url('admin/pending'));
    }

    /**
     * Reject submission
     */
    public function reject(string $id): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->submissionModel->reject((int) $id, $this->user['id'], $this->post('notes'));

        Security::logActivity($this->user['id'], 'submission_rejected', 'submission', (int) $id);

        $this->flash('Submission rejected.', 'info');
        $this->redirect(url('admin/pending'));
    }

    /**
     * Global search across all content categories
     */
    public function search(): void
    {
        $this->requireModerator();

        $q = trim($_GET['q'] ?? '');
        $results = [];

        if ($q !== '') {
            $categories = [
                'names'     => ['model' => new TivName(),      'fields' => ['tiv_name', 'english_meaning'],    'label' => 'Tiv Names'],
                'proverbs'  => ['model' => new TivProverb(),    'fields' => ['tiv_text', 'english_translation'],'label' => 'Proverbs'],
                'plants'    => ['model' => new TivPlant(),      'fields' => ['tiv_name', 'english_name'],       'label' => 'Plants'],
                'festivals' => ['model' => new TivFestival(),   'fields' => ['tiv_name', 'english_name'],       'label' => 'Festivals'],
                'foods'     => ['model' => new TivFood(),       'fields' => ['tiv_name', 'english_name'],       'label' => 'Foods'],
                'words'     => ['model' => new DailyWord(),     'fields' => ['tiv_word', 'english_meaning'],    'label' => 'Dictionary'],
                'animals'   => ['model' => new TivAnimal(),     'fields' => ['tiv_name', 'name'],               'label' => 'Animals'],
                'videos'    => ['model' => new LearningVideo(), 'fields' => ['title', 'description'],           'label' => 'Videos'],
            ];

            foreach ($categories as $category => $cfg) {
                $items = $cfg['model']->searchLike($q, $cfg['fields'], 50);
                if (!empty($items)) {
                    $results[$category] = [
                        'label' => $cfg['label'],
                        'items' => $items,
                    ];
                }
            }
        }

        $this->render('admin/search', [
            'title'       => 'Search Content',
            'q'           => $q,
            'results'     => $results,
            'currentPage' => 'search',
        ], 'admin');
    }

    /**
     * Content management
     */
    public function content(string $category): void
    {
        $this->requireModerator();

        $model = $this->getModelForCategory($category);
        if (!$model) {
            $this->redirect(url('admin'));
            return;
        }

        $q = trim($_GET['q'] ?? '');

        if ($q !== '') {
            $items      = $model->searchLike($q, $this->getSearchFields($category), 200);
            $pagination = null;
        } else {
            $total      = $model->count();
            $pagination = $this->paginate($total);
            $items      = $model->paginate($pagination['per_page'], $pagination['offset']);
        }

        $this->render('admin/content', [
            'title'       => 'Manage ' . ucfirst($category),
            'category'    => $category,
            'items'       => $items,
            'pagination'  => $pagination,
            'q'           => $q,
            'currentPage' => $category
        ], 'admin');
    }

    /**
     * Create content form
     */
    public function create(string $category): void
    {
        $this->requireModerator();

        $this->render('admin/create', [
            'title' => 'Add New ' . rtrim(ucfirst($category), 's'),
            'category' => $category,
            'currentPage' => $category,
            'sources' => $this->sourceModel->getAllOrdered()
        ], 'admin');
    }

    /**
     * Store new content
     */
    public function store(string $category): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $model = $this->getModelForCategory($category);
        if (!$model) {
            $this->redirect(url('admin'));
            return;
        }

        $data = $this->getContentData($category);
        $data['created_by'] = $this->user['id'];

        // Check for duplicates before saving
        $dupCheck = $this->getDuplicateField($category);
        if ($dupCheck && !empty($data[$dupCheck['field']])) {
            if ($model->existsByField($dupCheck['field'], $data[$dupCheck['field']])) {
                $this->storeOldInput();
                $this->flash(
                    '"' . htmlspecialchars($data[$dupCheck['field']]) . '" already exists in ' . ucfirst($category) . '.',
                    'error'
                );
                $this->back();
                return;
            }
        }

        // Handle audio recording for names and words
        if (in_array($category, ['names', 'words'])) {
            $audioData = $_POST['audio_data'] ?? '';
            if (!empty($audioData) && strpos($audioData, 'data:audio/') === 0) {
                $audioFile = $this->saveAudioFile($audioData);
                if ($audioFile) {
                    $data['audio_file'] = $audioFile;
                }
            }
        }

        // Handle image upload for plants, animals, foods and festivals
        if (in_array($category, ['plants', 'animals', 'foods', 'festivals']) && !empty($_FILES['image']['name'])) {
            $prefix = match($category) { 'animals' => 'animal', 'foods' => 'food', 'festivals' => 'festival', default => 'plant' };
            $imageFile = $this->saveImageFile($_FILES['image'], $prefix);
            if ($imageFile) {
                $data['image'] = $imageFile;
            } elseif ($imageFile === false) {
                $this->storeOldInput();
                $this->back();
                return;
            }
        }

        try {
            $id = $model->create($data);
            Security::logActivity($this->user['id'], 'content_created', $category, $id);

            Cache::forget('archive_counts');
            Cache::forget('archive_rows');
            if (in_array($category, ['plants', 'animals', 'foods', 'festivals'])) Cache::forget('home_featured');
            $this->clearOldInput();
            $this->flash('Content created successfully.', 'success');
            $this->redirect(url('admin/content/' . $category));

        } catch (Exception $e) {
            $this->storeOldInput();
            $this->flash('Error creating content: ' . $e->getMessage(), 'error');
            $this->back();
        }
    }

    /**
     * Edit content form
     */
    public function edit(string $category, string $id): void
    {
        $this->requireModerator();

        $model = $this->getModelForCategory($category);
        if (!$model) {
            $this->redirect(url('admin'));
            return;
        }

        $item = $model->find((int) $id);
        if (!$item) {
            $this->flash('Item not found.', 'error');
            $this->redirect(url('admin/content/' . $category));
            return;
        }

        $gallery = [];
        if ($category === 'festivals') {
            $gallery = (new TivFestival())->getGallery((int) $id);
        }

        $rootWord      = null;
        $wordRelations = [];
        if ($category === 'words') {
            if (!empty($item['root_word_id'])) {
                $rootWord = (new DailyWord())->find((int) $item['root_word_id']);
            }
            require_once BASE_PATH . '/models/KnowledgeLink.php';
            $wordRelations = array_filter(
                (new KnowledgeLink())->getRelatedItems('daily_words', (int) $id),
                fn($link) => in_array($link['relation'], ['synonym', 'antonym', 'see_also'], true)
                    && $link['table'] === 'daily_words'
            );
        }

        $this->render('admin/edit', [
            'title'         => 'Edit ' . rtrim(ucfirst($category), 's'),
            'category'      => $category,
            'item'          => $item,
            'gallery'       => $gallery,
            'currentPage'   => $category,
            'sources'       => $this->sourceModel->getAllOrdered(),
            'rootWord'      => $rootWord,
            'wordRelations' => $wordRelations
        ], 'admin');
    }

    /**
     * Update content
     */
    public function update(string $category, string $id): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $model = $this->getModelForCategory($category);
        if (!$model) {
            $this->redirect(url('admin'));
            return;
        }

        $data = $this->getContentData($category);

        // Handle audio recording for names and words
        if (in_array($category, ['names', 'words'])) {
            $audioData = $_POST['audio_data'] ?? '';
            if (!empty($audioData) && strpos($audioData, 'data:audio/') === 0) {
                $audioFile = $this->saveAudioFile($audioData);
                if ($audioFile) {
                    $data['audio_file'] = $audioFile;
                }
            }
        }

        // Handle image upload for plants, animals, foods and festivals
        if (in_array($category, ['plants', 'animals', 'foods', 'festivals']) && !empty($_FILES['image']['name'])) {
            $prefix = match($category) { 'animals' => 'animal', 'foods' => 'food', 'festivals' => 'festival', default => 'plant' };
            $imageFile = $this->saveImageFile($_FILES['image'], $prefix);
            if ($imageFile) {
                $data['image'] = $imageFile;
            } elseif ($imageFile === false) {
                $this->storeOldInput();
                $this->back();
                return;
            }
        }

        try {
            $model->update((int) $id, $data);
            Security::logActivity($this->user['id'], 'content_updated', $category, (int) $id);

            Cache::forget('archive_counts');
            Cache::forget('archive_rows');
            if (in_array($category, ['plants', 'animals', 'foods', 'festivals'])) Cache::forget('home_featured');
            $this->clearOldInput();
            $this->flash('Content updated successfully.', 'success');
            $this->redirect(url('admin/content/' . $category));

        } catch (Exception $e) {
            $this->storeOldInput();
            $this->flash('Error updating content: ' . $e->getMessage(), 'error');
            $this->back();
        }
    }

    /**
     * Delete content
     */
    public function delete(string $category, string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $model = $this->getModelForCategory($category);
        if (!$model) {
            $this->redirect(url('admin'));
            return;
        }

        $model->delete((int) $id);
        Security::logActivity($this->user['id'], 'content_deleted', $category, (int) $id);

        Cache::forget('archive_counts');
        Cache::forget('archive_rows');
        $this->flash('Content deleted successfully.', 'success');
        $this->redirect(url('admin/content/' . $category));
    }

    /**
     * Add a synonym/antonym/see-also relation from the word edit page
     */
    public function addWordRelation(string $id): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $targetTivWord = trim((string) $this->post('target_tiv_word'));
        $relationType  = $this->post('relation_type', 'synonym');

        if (!in_array($relationType, ['synonym', 'antonym', 'see_also'], true)) {
            $relationType = 'synonym';
        }

        if ($targetTivWord === '') {
            $this->flash('Enter the related word\'s Tiv spelling.', 'error');
            $this->redirect(url('admin/content/words/' . $id . '/edit'));
            return;
        }

        $targetWord = (new DailyWord())->findBy('tiv_word', $targetTivWord);
        if (!$targetWord) {
            $this->flash('No word found with that exact spelling.', 'error');
            $this->redirect(url('admin/content/words/' . $id . '/edit'));
            return;
        }

        if ((int) $targetWord['id'] === (int) $id) {
            $this->flash('A word cannot be related to itself.', 'error');
            $this->redirect(url('admin/content/words/' . $id . '/edit'));
            return;
        }

        require_once BASE_PATH . '/models/KnowledgeLink.php';
        (new KnowledgeLink())->addLink('daily_words', (int) $id, 'daily_words', (int) $targetWord['id'], $relationType);

        $this->flash('Related word added.', 'success');
        $this->redirect(url('admin/content/words/' . $id . '/edit'));
    }

    /**
     * Remove a word relation from the word edit page
     */
    public function removeWordRelation(string $id, string $linkId): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        require_once BASE_PATH . '/models/KnowledgeLink.php';
        (new KnowledgeLink())->delete((int) $linkId);

        $this->flash('Relation removed.', 'info');
        $this->redirect(url('admin/content/words/' . $id . '/edit'));
    }

    /**
     * User management
     */
    public function users(): void
    {
        $this->requireAdmin();

        $total = $this->userModel->count();
        $pagination = $this->paginate($total);
        $users = $this->userModel->paginate($pagination['per_page'], $pagination['offset'], 'created_at', 'DESC');

        $this->render('admin/users', [
            'title' => 'Manage Users',
            'users' => $users,
            'pagination' => $pagination,
            'roles' => USER_ROLES,
            'currentPage' => 'users'
        ], 'admin');
    }

    /**
     * Update user role
     */
    public function updateRole(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $role = $this->post('role');
        if (!array_key_exists($role, USER_ROLES)) {
            $this->flash('Invalid role.', 'error');
            $this->back();
            return;
        }

        // Prevent admin from demoting themselves
        if ((int) $id === $this->user['id'] && $role !== 'admin') {
            $this->flash('You cannot change your own role.', 'error');
            $this->back();
            return;
        }

        $this->userModel->updateRole((int) $id, $role);
        Security::logActivity($this->user['id'], 'user_role_changed', 'user', (int) $id, null, ['role' => $role]);

        $this->flash('User role updated successfully.', 'success');
        $this->redirect(url('admin/users'));
    }

    /**
     * Create a new admin/staff user directly from the admin panel
     */
    public function storeUser(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $name     = trim($this->post('name'));
        $email    = trim($this->post('email'));
        $password = $this->post('password');
        $role     = $this->post('role', 'moderator');

        // Validate
        if (!$name || !$email || !$password) {
            $this->flash('Name, email and password are all required.', 'error');
            $this->back();
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('Invalid email address.', 'error');
            $this->back();
            return;
        }

        if (strlen($password) < 8) {
            $this->flash('Password must be at least 8 characters.', 'error');
            $this->back();
            return;
        }

        if (!array_key_exists($role, USER_ROLES)) {
            $this->flash('Invalid role selected.', 'error');
            $this->back();
            return;
        }

        // Check for duplicate email
        if ($this->userModel->findByEmail($email)) {
            $this->flash('A user with that email already exists.', 'error');
            $this->back();
            return;
        }

        try {
            $id = $this->userModel->createUser([
                'name'     => $name,
                'email'    => $email,
                'password' => $password,
                'role'     => $role,
            ]);

            Security::logActivity($this->user['id'], 'admin_user_created', 'user', $id, null, ['role' => $role]);
            $this->flash("User \"{$name}\" created successfully with role: " . USER_ROLES[$role]['label'] . '.', 'success');
        } catch (Exception $e) {
            $this->flash('Error creating user: ' . $e->getMessage(), 'error');
        }

        $this->redirect(url('admin/users'));
    }

    /**
     * Delete a user
     */
    public function deleteUser(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        if ((int) $id === $this->user['id']) {
            $this->flash('You cannot delete your own account.', 'error');
            $this->redirect(url('admin/users'));
            return;
        }

        $target = $this->userModel->find((int) $id);
        if (!$target) {
            $this->flash('User not found.', 'error');
            $this->redirect(url('admin/users'));
            return;
        }

        $this->userModel->delete((int) $id);
        Security::logActivity($this->user['id'], 'user_deleted', 'user', (int) $id);
        $this->flash("User \"{$target['name']}\" has been deleted.", 'success');
        $this->redirect(url('admin/users'));
    }

    /**
     * Reset a user's password (admin only)
     */
    public function resetPassword(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $target = $this->userModel->find((int) $id);
        if (!$target) {
            $this->flash('User not found.', 'error');
            $this->redirect(url('admin/users'));
            return;
        }

        $newPassword = $this->post('new_password');
        $confirm     = $this->post('confirm_password');

        if (strlen($newPassword) < 8) {
            $this->flash('Password must be at least 8 characters.', 'error');
            $this->redirect(url('admin/users'));
            return;
        }

        if ($newPassword !== $confirm) {
            $this->flash('Passwords do not match.', 'error');
            $this->redirect(url('admin/users'));
            return;
        }

        $this->userModel->updatePassword((int) $id, $newPassword);
        Security::logActivity($this->user['id'], 'admin_password_reset', 'user', (int) $id);
        $this->flash("Password for \"{$target['name']}\" has been reset.", 'success');
        $this->redirect(url('admin/users'));
    }

    /**
     * Site settings
     */
    public function settings(): void
    {
        $this->requireAdmin();

        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM site_settings ORDER BY setting_key");
        $settings = $stmt->fetchAll();

        $this->render('admin/settings', [
            'title' => 'Site Settings',
            'settings' => $settings,
            'currentPage' => 'settings'
        ], 'admin');
    }

    /**
     * Update site settings
     */
    public function updateSettings(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $db = Database::getInstance();
        $settings = $_POST['settings'] ?? [];

        foreach ($settings as $key => $value) {
            $stmt = $db->prepare(
                "UPDATE site_settings SET setting_value = ? WHERE setting_key = ?"
            );
            $stmt->execute([$value, $key]);
        }

        Security::logActivity($this->user['id'], 'settings_updated');

        $this->flash('Settings updated successfully.', 'success');
        $this->redirect(url('admin/settings'));
    }

    /**
     * Upload a photo to a festival's gallery
     */
    public function uploadGalleryPhoto(string $id): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->flash('Security check failed.', 'error');
            $this->back();
            return;
        }

        $model   = new TivFestival();
        $festival = $model->find((int) $id);
        if (!$festival) {
            $this->flash('Festival not found.', 'error');
            $this->redirect(url('admin/content/festivals'));
            return;
        }

        if (empty($_FILES['gallery_image']['name'])) {
            $this->flash('No image file selected.', 'error');
            $this->redirect(url('admin/content/festivals/' . $id . '/edit'));
            return;
        }

        $filename = $this->saveImageFile($_FILES['gallery_image'], 'festival');
        if ($filename === false) {
            $this->redirect(url('admin/content/festivals/' . $id . '/edit'));
            return;
        }

        $caption  = trim($this->post('caption')  ?? '') ?: null;
        $altText  = trim($this->post('alt_text')  ?? '') ?: null;
        $featured = $this->post('is_featured') ? 1 : 0;

        $model->addGalleryPhoto((int) $id, $filename, $caption, $altText, $featured, $this->user['id']);

        Security::logActivity($this->user['id'], 'gallery_photo_uploaded', 'tiv_festivals', (int) $id);

        $this->flash('Photo added to gallery.', 'success');
        $this->redirect(url('admin/content/festivals/' . $id . '/edit'));
    }

    /**
     * Delete a photo from a festival's gallery
     */
    public function deleteGalleryPhoto(string $id, string $photoId): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $model = new TivFestival();
        $photo = $model->getGalleryPhoto((int) $photoId);

        if ($photo && (int) $photo['festival_id'] === (int) $id) {
            $filePath = UPLOADS_PATH . '/images/' . $photo['image_path'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $model->removeGalleryPhoto((int) $photoId, (int) $id);
            Security::logActivity($this->user['id'], 'gallery_photo_deleted', 'tiv_festivals', (int) $id);
            $this->flash('Photo removed from gallery.', 'success');
        } else {
            $this->flash('Photo not found.', 'error');
        }

        $this->redirect(url('admin/content/festivals/' . $id . '/edit'));
    }

    /**
     * Save uploaded image file
     * @return string|false|null Filename on success, false on error, null if no file
     */
    private function saveImageFile(array $file, string $prefix = 'plant'): string|false|null
    {
        if (empty($file['name'])) {
            return null;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('Image upload failed. Please try again.', 'error');
            return false;
        }

        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $this->flash('Image file is too large. Maximum size is 5MB.', 'error');
            return false;
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $this->flash('Invalid image file type. Allowed: JPG, PNG, GIF, WebP.', 'error');
            return false;
        }

        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            $this->flash('Invalid image file extension.', 'error');
            return false;
        }

        // Ensure upload directory exists
        $uploadPath = UPLOADS_PATH . '/images';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generate unique filename
        $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->flash('Failed to save image file.', 'error');
            return false;
        }

        return $filename;
    }

    /**
     * Save base64 audio data to file
     */
    private function saveAudioFile(string $audioData): ?string
    {
        // Ensure audio uploads directory exists
        if (!is_dir(AUDIO_UPLOADS_PATH)) {
            mkdir(AUDIO_UPLOADS_PATH, 0777, true);
        }

        // Extract MIME type and base64 data
        if (preg_match('/^data:(audio\/[^;]+);base64,(.+)$/', $audioData, $matches)) {
            $mimeType = $matches[1];
            $base64Data = $matches[2];

            // Decode base64 data
            $binaryData = base64_decode($base64Data);
            if ($binaryData === false) {
                return null;
            }

            // Check file size
            if (strlen($binaryData) > MAX_AUDIO_SIZE) {
                return null;
            }

            // Determine file extension based on MIME type
            $extension = 'webm';
            if ($mimeType === 'audio/mp3' || $mimeType === 'audio/mpeg') {
                $extension = 'mp3';
            } elseif ($mimeType === 'audio/ogg') {
                $extension = 'ogg';
            } elseif ($mimeType === 'audio/wav') {
                $extension = 'wav';
            }

            // Generate unique filename
            $filename = 'pronunciation_' . uniqid() . '_' . time() . '.' . $extension;
            $filepath = AUDIO_UPLOADS_PATH . '/' . $filename;

            // Save file
            if (file_put_contents($filepath, $binaryData)) {
                return $filename;
            }
        }

        return null;
    }

    /**
     * Get model for category
     */
    private function getModelForCategory(string $category): ?Model
    {
        $models = [
            'names' => TivName::class,
            'proverbs' => TivProverb::class,
            'plants' => TivPlant::class,
            'festivals' => TivFestival::class,
            'foods' => TivFood::class,
            'words' => DailyWord::class,
            'animals' => TivAnimal::class,
            'videos' => LearningVideo::class
        ];

        if (!isset($models[$category])) {
            return null;
        }

        return new $models[$category]();
    }

    /**
     * Get content data from POST based on category
     */
    private function getContentData(string $category): array
    {
        switch ($category) {
            case 'names':
                return [
                    'tiv_name' => $this->post('tiv_name'),
                    'english_meaning' => $this->post('english_meaning'),
                    'gender' => $this->post('gender', 'unisex'),
                    'description' => $this->post('description'),
                    'pronunciation' => $this->post('pronunciation'),
                    'origin_story' => $this->post('origin_story'),
                    'usage_context' => $this->post('usage_context'),
                    'is_featured' => $this->post('is_featured') ? 1 : 0,
                    'source_id' => $this->post('source_id') ?: null
                ];

            case 'proverbs':
                return [
                    'tiv_text' => $this->post('tiv_text'),
                    'english_translation' => $this->post('english_translation'),
                    'deeper_meaning' => $this->post('deeper_meaning'),
                    'usage_context' => $this->post('usage_context'),
                    'category' => $this->post('category'),
                    'is_featured' => $this->post('is_featured') ? 1 : 0,
                    'source_id' => $this->post('source_id') ?: null
                ];

            case 'plants':
                return [
                    'tiv_name' => $this->post('tiv_name'),
                    'english_name' => $this->post('english_name'),
                    'scientific_name' => $this->post('scientific_name'),
                    'description' => $this->post('description'),
                    'medicinal_uses' => $this->post('medicinal_uses'),
                    'food_uses' => $this->post('food_uses'),
                    'ritual_uses' => $this->post('ritual_uses'),
                    'cultivation' => $this->post('cultivation'),
                    'is_featured' => $this->post('is_featured') ? 1 : 0,
                    'source_id' => $this->post('source_id') ?: null
                ];

            case 'festivals':
                return [
                    'tiv_name'     => $this->post('tiv_name'),
                    'english_name' => $this->post('english_name'),
                    'festival_type'=> $this->post('festival_type'),
                    'description'  => $this->post('description'),
                    'significance' => $this->post('significance'),
                    'timing'       => $this->post('timing'),
                    'duration'     => $this->post('duration'),
                    'activities'   => $this->post('activities'),
                    'location'     => $this->post('location'),
                    'is_featured'  => $this->post('is_featured') ? 1 : 0,
                    'source_id'    => $this->post('source_id') ?: null
                ];

            case 'foods':
                return [
                    'tiv_name'              => $this->post('tiv_name'),
                    'english_name'          => $this->post('english_name'),
                    'category'              => $this->post('category'),
                    'description'           => $this->post('description'),
                    'ingredients'           => $this->post('ingredients'),
                    'preparation_method'    => $this->post('preparation_method'),
                    'serving_suggestions'   => $this->post('serving_suggestions'),
                    'cultural_significance' => $this->post('cultural_significance'),
                    'is_featured'           => $this->post('is_featured') ? 1 : 0,
                    'source_id'             => $this->post('source_id') ?: null
                ];

            case 'words':
                $rootWordTiv = trim((string) $this->post('root_word_tiv'));
                $rootWordId  = null;
                if ($rootWordTiv !== '') {
                    $rootWord   = (new DailyWord())->findBy('tiv_word', $rootWordTiv);
                    $rootWordId = $rootWord['id'] ?? null;
                }

                return [
                    'tiv_word' => $this->post('tiv_word'),
                    'english_meaning' => $this->post('english_meaning'),
                    'alternate_meaning' => $this->post('alternate_meaning') ?: null,
                    'part_of_speech' => $this->post('part_of_speech', 'noun'),
                    'category' => $this->post('category') ?: null,
                    'pronunciation' => $this->post('pronunciation'),
                    'ipa' => $this->post('ipa') ?: null,
                    'tone' => $this->post('tone') ?: null,
                    'root_word_id' => $rootWordId,
                    'example_tiv' => $this->post('example_tiv'),
                    'example_english' => $this->post('example_english'),
                    'literal_meaning' => $this->post('literal_meaning') ?: null,
                    'figurative_meaning' => $this->post('figurative_meaning') ?: null,
                    'usage_notes' => $this->post('usage_notes') ?: null,
                    'dialect_region' => $this->post('dialect_region') ?: null,
                    'frequency' => $this->post('frequency') ?: null,
                    'is_active' => $this->post('is_active') ? 1 : 0,
                    'source_id' => $this->post('source_id') ?: null
                ];

            case 'animals':
                return [
                    'tiv_name'    => $this->post('tiv_name'),
                    'name'        => $this->post('name'),
                    'description' => $this->post('description'),
                    'cultural_use'=> $this->post('cultural_use'),
                    'animal_type' => $this->post('animal_type', 'wild'),
                    'source_id'   => $this->post('source_id') ?: null
                ];

            case 'videos':
                return [
                    'title' => $this->post('title'),
                    'description' => $this->post('description'),
                    'youtube_id' => $this->extractYoutubeId($this->post('youtube_id')),
                    'category' => $this->post('category'),
                    'difficulty' => $this->post('difficulty', 'beginner'),
                    'duration' => $this->post('duration'),
                    'sort_order' => (int) $this->post('sort_order', 0),
                    'is_featured' => $this->post('is_featured') ? 1 : 0,
                    'is_active' => $this->post('is_active') ? 1 : 0
                ];

            default:
                return [];
        }
    }

    private function extractYoutubeId(string $input): string
    {
        $input = trim($input);
        // youtu.be/ID
        if (preg_match('#youtu\.be/([A-Za-z0-9_\-]{11})#', $input, $m)) {
            return $m[1];
        }
        // youtube.com/watch?v=ID
        if (preg_match('#[?&]v=([A-Za-z0-9_\-]{11})#', $input, $m)) {
            return $m[1];
        }
        // youtube.com/embed/ID
        if (preg_match('#/embed/([A-Za-z0-9_\-]{11})#', $input, $m)) {
            return $m[1];
        }
        // Already just an ID
        return $input;
    }

    // -------------------------------------------------------
    // BULK UPLOAD — Dictionary Words
    // -------------------------------------------------------

    /**
     * Show the bulk-upload form for dictionary words
     */
    public function bulkUploadWordsForm(): void
    {
        $this->requireModerator();
        $this->render('admin/bulk_upload_words', [
            'title'       => 'Bulk Upload Dictionary Words',
            'currentPage' => 'words',
        ], 'admin');
    }

    /**
     * Process an uploaded .xlsx or .csv file and insert dictionary words,
     * skipping any row whose (tiv_word, english_meaning) pair already exists.
     *
     * Expected column order (case-insensitive header row):
     *   Tiv_Word | English_Word | Part_Of_Speech | Meaning | Example_Tiv | Example_English
     */
    public function bulkUploadWords(): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->redirect(url('admin/content/words/bulk-upload'));
            return;
        }

        $file = $_FILES['bulk_file'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('No file uploaded or upload error.', 'error');
            $this->redirect(url('admin/content/words/bulk-upload'));
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'csv'], true)) {
            $this->flash('Only .xlsx and .csv files are accepted.', 'error');
            $this->redirect(url('admin/content/words/bulk-upload'));
            return;
        }

        // Parse file into rows
        try {
            $rows = $ext === 'xlsx'
                ? $this->parseXlsx($file['tmp_name'])
                : $this->parseCsv($file['tmp_name']);
        } catch (Exception $e) {
            $this->flash('Could not read file: ' . $e->getMessage(), 'error');
            $this->redirect(url('admin/content/words/bulk-upload'));
            return;
        }

        if (empty($rows)) {
            $this->flash('The file appears to be empty.', 'error');
            $this->redirect(url('admin/content/words/bulk-upload'));
            return;
        }

        // Normalise header row → column index map
        $header = array_map(fn($h) => strtolower(trim(str_replace([' ', '_'], '', $h))), $rows[0]);
        $col = [
            'tiv_word'        => array_search('tivword',      $header, true),
            'english_meaning' => array_search('englishword',  $header, true),
            'part_of_speech'  => array_search('partofspeech', $header, true),
            'alternate_meaning' => array_search('meaning',    $header, true),
            'example_tiv'     => array_search('exampletiv',   $header, true),
            'example_english' => array_search('exampleenglish', $header, true),
        ];

        // Require the two mandatory columns
        if ($col['tiv_word'] === false || $col['english_meaning'] === false) {
            $this->flash(
                'Header row not found or missing required columns (Tiv_Word, English_Word).',
                'error'
            );
            $this->redirect(url('admin/content/words/bulk-upload'));
            return;
        }

        $db          = Database::getInstance();
        $userId      = $this->user['id'];
        $inserted    = 0;
        $skipped     = 0;
        $errors      = 0;
        $details     = [];   // per-row result log shown in the view

        $validPos = ['noun','verb','adjective','adverb','pronoun',
                     'preposition','conjunction','interjection','phrase'];

        $checkStmt = $db->prepare(
            "SELECT id FROM daily_words
             WHERE LOWER(tiv_word) = LOWER(?) AND LOWER(english_meaning) = LOWER(?)
             LIMIT 1"
        );

        $insertStmt = $db->prepare(
            "INSERT INTO daily_words
                (tiv_word, english_meaning, alternate_meaning, part_of_speech,
                 example_tiv, example_english, is_active, created_by)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)"
        );

        foreach (array_slice($rows, 1) as $rowNum => $row) {
            // Skip completely blank rows
            $rowText = implode('', array_map('trim', $row));
            if ($rowText === '') continue;

            $tivWord   = trim($row[$col['tiv_word']]        ?? '');
            $engWord   = trim($row[$col['english_meaning']] ?? '');

            if ($tivWord === '' || $engWord === '') {
                $details[] = [
                    'row'    => $rowNum + 2,
                    'tiv'    => $tivWord ?: '(empty)',
                    'eng'    => $engWord ?: '(empty)',
                    'status' => 'error',
                    'note'   => 'Tiv_Word or English_Word is blank — skipped',
                ];
                $errors++;
                continue;
            }

            // Duplicate check
            $checkStmt->execute([$tivWord, $engWord]);
            if ($checkStmt->fetch()) {
                $details[] = [
                    'row'    => $rowNum + 2,
                    'tiv'    => $tivWord,
                    'eng'    => $engWord,
                    'status' => 'duplicate',
                    'note'   => 'Already exists in dictionary',
                ];
                $skipped++;
                continue;
            }

            // Resolve optional columns
            $pos = $col['part_of_speech'] !== false
                ? strtolower(trim($row[$col['part_of_speech']] ?? ''))
                : 'noun';
            if (!in_array($pos, $validPos, true)) $pos = 'noun';

            $altMeaning = $col['alternate_meaning'] !== false
                ? trim($row[$col['alternate_meaning']] ?? '')
                : null;
            $exTiv = $col['example_tiv'] !== false
                ? trim($row[$col['example_tiv']] ?? '')
                : null;
            $exEng = $col['example_english'] !== false
                ? trim($row[$col['example_english']] ?? '')
                : null;

            try {
                $insertStmt->execute([
                    $tivWord,
                    $engWord,
                    $altMeaning ?: null,
                    $pos,
                    $exTiv  ?: null,
                    $exEng  ?: null,
                    $userId,
                ]);
                $details[] = [
                    'row'    => $rowNum + 2,
                    'tiv'    => $tivWord,
                    'eng'    => $engWord,
                    'status' => 'inserted',
                    'note'   => ucfirst($pos),
                ];
                $inserted++;
            } catch (PDOException $e) {
                $details[] = [
                    'row'    => $rowNum + 2,
                    'tiv'    => $tivWord,
                    'eng'    => $engWord,
                    'status' => 'error',
                    'note'   => 'DB error: ' . $e->getMessage(),
                ];
                $errors++;
            }
        }

        Security::logActivity(
            $userId,
            'bulk_upload_words',
            'daily_words',
            null,
            null,
            ['inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors]
        );

        $this->render('admin/bulk_upload_words', [
            'title'       => 'Bulk Upload Dictionary Words',
            'currentPage' => 'words',
            'result'      => compact('inserted', 'skipped', 'errors', 'details'),
        ], 'admin');
    }

    // -------------------------------------------------------
    // XLSX / CSV parsers — no external library required
    // -------------------------------------------------------

    /**
     * Parse a .xlsx file using ZipArchive + SimpleXML.
     * Returns array of rows, each row an array of cell values (strings).
     */
    private function parseXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive extension is not available on this server.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception('Cannot open .xlsx file — it may be corrupt or not a valid Excel file.');
        }

        // ── Shared strings ────────────────────────────────────
        $sharedStrings = [];
        // Try both common casing variants
        $ssRaw = $zip->getFromName('xl/sharedStrings.xml')
               ?: $zip->getFromName('xl/SharedStrings.xml')
               ?: false;

        if ($ssRaw !== false) {
            $ssXml = $this->stripXmlNs($ssRaw);
            $ss    = simplexml_load_string($ssXml, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($ss) {
                foreach ($ss->si as $si) {
                    // Rich-text cells have multiple <r><t> children; plain cells have one <t>
                    $nodes = $si->xpath('.//t');
                    $text  = '';
                    foreach ($nodes as $t) {
                        $text .= (string) $t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // ── Find first worksheet path via workbook relationships ──
        $sheetPath = null;
        $relsRaw   = $zip->getFromName('xl/_rels/workbook.xml.rels') ?: false;
        if ($relsRaw !== false) {
            $relsXml = $this->stripXmlNs($relsRaw);
            $rels    = simplexml_load_string($relsXml, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($rels) {
                // Pick the first Worksheet relationship
                foreach ($rels->Relationship as $rel) {
                    $type = (string) $rel['Type'];
                    if (stripos($type, 'worksheet') !== false) {
                        $target = (string) $rel['Target'];
                        // Target may be relative ("worksheets/sheet1.xml") or absolute
                        $sheetPath = (strpos($target, '/') === 0)
                            ? ltrim($target, '/')
                            : 'xl/' . $target;
                        break;
                    }
                }
            }
        }
        // Fallback: try common names if rels lookup failed
        if ($sheetPath === null) {
            foreach (['xl/worksheets/sheet1.xml', 'xl/worksheets/Sheet1.xml'] as $try) {
                if ($zip->getFromName($try) !== false) {
                    $sheetPath = $try;
                    break;
                }
            }
        }

        if ($sheetPath === null) {
            $zip->close();
            throw new Exception('Could not locate a worksheet inside the .xlsx file.');
        }

        $sheetRaw = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetRaw === false) {
            throw new Exception("Could not read worksheet at path: {$sheetPath}");
        }

        // ── Parse worksheet ───────────────────────────────────
        $sheetXml = $this->stripXmlNs($sheetRaw);
        $sheet    = simplexml_load_string($sheetXml, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);

        if (!$sheet || !isset($sheet->sheetData)) {
            throw new Exception('Worksheet XML could not be parsed — the file may be corrupt.');
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells     = [];
            $maxColIdx = 0;

            foreach ($row->c as $cell) {
                $ref       = (string) ($cell['r'] ?? '');
                $type      = (string) ($cell['t'] ?? '');
                $colLetter = rtrim(preg_replace('/[0-9]+/', '', $ref), '');
                $colIdx    = ($colLetter !== '') ? $this->xlsxColToIndex($colLetter) : 0;

                if ($type === 's') {
                    // Shared string index
                    $value = $sharedStrings[(int)(string)($cell->v ?? 0)] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } elseif ($type === 'b') {
                    $value = ((string)($cell->v ?? '')) === '1' ? 'TRUE' : 'FALSE';
                } else {
                    // Number, date, formula result, or empty
                    $value = (string) ($cell->v ?? '');
                }

                $cells[$colIdx] = $value;
                if ($colIdx > $maxColIdx) $maxColIdx = $colIdx;
            }

            // Materialise sparse cells as a dense array
            $rowData = [];
            for ($i = 0; $i <= $maxColIdx; $i++) {
                $rowData[] = isset($cells[$i]) ? trim($cells[$i]) : '';
            }
            $rows[] = $rowData;
        }

        return $rows;
    }

    /**
     * Strip XML namespace declarations and namespace prefixes from an XML string
     * so that SimpleXML can access elements without namespace-aware calls.
     * This is safe for the subset of XLSX XML we consume here.
     */
    private function stripXmlNs(string $xml): string
    {
        // Remove namespace declarations: xmlns="..." and xmlns:foo="..."
        $xml = preg_replace('/\sxmlns(?::\w+)?="[^"]*"/i', '', $xml);
        // Remove namespace prefixes on opening and closing tags: <mc:foo → <foo, </mc:foo → </foo
        $xml = preg_replace('/<(\/?)\w+:/', '<$1', $xml);
        return $xml;
    }

    /**
     * Convert an Excel column letter (A, B, … Z, AA, AB …) to a 0-based index.
     */
    private function xlsxColToIndex(string $col): int
    {
        $col = strtoupper(trim($col));
        $idx = 0;
        for ($i = 0, $len = strlen($col); $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $idx - 1;
    }

    /**
     * Parse a .csv file (auto-detects tab or comma delimiter, strips UTF-8 BOM).
     * Returns array of rows, each row an array of cell values.
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new Exception('Cannot open CSV file.');
        }

        // Strip UTF-8 BOM that Excel adds when saving as CSV
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);   // not a BOM — go back to start
        }

        // Sniff delimiter from the first non-empty line
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3); // re-skip BOM

        $delimiter = (substr_count($firstLine, "\t") >= substr_count($firstLine, ',')) ? "\t" : ',';

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Strip BOM from first cell of first row (extra safety)
            if (empty($rows) && isset($row[0])) {
                $row[0] = ltrim($row[0], "\xEF\xBB\xBF");
            }
            $rows[] = array_map('trim', $row);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Show all duplicate entries across all content tables
     */
    public function duplicates(): void
    {
        $this->requireModerator();

        $tables = [
            'names'     => ['table' => 'tiv_names',   'field' => 'tiv_name',  'label' => 'Tiv Names'],
            'proverbs'  => ['table' => 'tiv_proverbs','field' => 'tiv_text',  'label' => 'Proverbs'],
            'plants'    => ['table' => 'tiv_plants',  'field' => 'tiv_name',  'label' => 'Plants'],
            'festivals' => ['table' => 'tiv_festivals','field' => 'tiv_name', 'label' => 'Festivals'],
            'foods'     => ['table' => 'tiv_foods',   'field' => 'tiv_name',  'label' => 'Foods'],
            'words'     => ['table' => 'daily_words', 'field' => 'tiv_word',  'label' => 'Dictionary'],
            'animals'   => ['table' => 'tiv_animals', 'field' => 'tiv_name',  'label' => 'Animals'],
        ];

        $db = Database::getInstance();
        $groups = [];

        foreach ($tables as $category => $cfg) {
            $stmt = $db->query(
                "SELECT {$cfg['field']} AS term, COUNT(*) AS cnt
                 FROM {$cfg['table']}
                 GROUP BY {$cfg['field']}
                 HAVING cnt > 1
                 ORDER BY {$cfg['field']} ASC"
            );
            $dupes = $stmt->fetchAll();

            foreach ($dupes as $dupe) {
                $stmt2 = $db->prepare(
                    "SELECT * FROM {$cfg['table']} WHERE {$cfg['field']} = ? ORDER BY id ASC"
                );
                $stmt2->execute([$dupe['term']]);
                $rows = $stmt2->fetchAll();

                $groups[] = [
                    'category' => $category,
                    'label'    => $cfg['label'],
                    'field'    => $cfg['field'],
                    'term'     => $dupe['term'],
                    'count'    => $dupe['cnt'],
                    'rows'     => $rows,
                ];
            }
        }

        $this->render('admin/duplicates', [
            'title'        => 'Duplicate Entries',
            'groups'       => $groups,
            'totalGroups'  => count($groups),
            'currentPage'  => 'duplicates'
        ], 'admin');
    }

    /**
     * Delete a specific duplicate record
     */
    public function deleteDuplicate(): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $category = $this->post('category');
        $id       = (int) $this->post('id');

        $model = $this->getModelForCategory($category);
        if (!$model || !$id) {
            $this->flash('Invalid request.', 'error');
            $this->redirect(url('admin/duplicates'));
            return;
        }

        $model->delete($id);
        // Bust cached counts/rows so archive page reflects the change
        Cache::forget('archive_counts');
        Cache::forget('archive_rows');

        Security::logActivity($this->user['id'], 'duplicate_deleted', $category, $id);

        $this->flash('Duplicate entry deleted successfully.', 'success');
        $this->redirect(url('admin/duplicates'));
    }

    /**
     * Returns the fields to search by LIKE for a given category.
     */
    private function getSearchFields(string $category): array
    {
        $map = [
            'names'     => ['tiv_name', 'english_meaning'],
            'proverbs'  => ['tiv_text', 'english_translation'],
            'plants'    => ['tiv_name', 'english_name'],
            'festivals' => ['tiv_name', 'english_name'],
            'foods'     => ['tiv_name', 'english_name'],
            'words'     => ['tiv_word', 'english_meaning'],
            'animals'   => ['tiv_name', 'name'],
            'videos'    => ['title', 'description'],
        ];
        return $map[$category] ?? ['id'];
    }

    /**
     * Returns the primary name field to use for duplicate detection per category.
     * Returns null for categories that don't need a duplicate check.
     */
    private function getDuplicateField(string $category): ?array
    {
        $map = [
            'names'     => ['field' => 'tiv_name'],
            'proverbs'  => ['field' => 'tiv_text'],
            'plants'    => ['field' => 'tiv_name'],
            'festivals' => ['field' => 'tiv_name'],
            'foods'     => ['field' => 'tiv_name'],
            'words'     => ['field' => 'tiv_word'],
            'animals'   => ['field' => 'tiv_name'],
        ];
        return $map[$category] ?? null;
    }

    /**
     * Add approved submission to main content
     */
    private function addToContent(array $submission): void
    {
        $additionalData = json_decode($submission['additional_data'] ?? '{}', true) ?: [];

        switch ($submission['category']) {
            case 'name':
                $model = new TivName();
                if ($model->existsByField('tiv_name', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_name' => $submission['tiv_term'],
                    'english_meaning' => $submission['english_meaning'],
                    'description' => $submission['description'],
                    'gender' => $additionalData['gender'] ?? 'unisex',
                    'pronunciation' => $additionalData['pronunciation'] ?? null,
                    'audio_file' => $additionalData['audio_file'] ?? null,
                    'origin_story' => $additionalData['origin_story'] ?? null,
                    'created_by' => $submission['user_id']
                ]);
                break;

            case 'proverb':
                $model = new TivProverb();
                if ($model->existsByField('tiv_text', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_text' => $submission['tiv_term'],
                    'english_translation' => $submission['english_meaning'],
                    'deeper_meaning' => $additionalData['deeper_meaning'] ?? $submission['description'],
                    'usage_context' => $additionalData['usage_context'] ?? null,
                    'created_by' => $submission['user_id']
                ]);
                break;

            case 'plant':
                $model = new TivPlant();
                if ($model->existsByField('tiv_name', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_name' => $submission['tiv_term'],
                    'english_name' => $additionalData['english_name'] ?? $submission['english_meaning'],
                    'description' => $submission['description'],
                    'medicinal_uses' => $additionalData['medicinal_uses'] ?? null,
                    'food_uses' => $additionalData['food_uses'] ?? null,
                    'ritual_uses' => $additionalData['ritual_uses'] ?? null,
                    'image' => $additionalData['image'] ?? null,
                    'created_by' => $submission['user_id']
                ]);
                break;

            case 'festival':
                $model = new TivFestival();
                if ($model->existsByField('tiv_name', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_name' => $submission['tiv_term'],
                    'english_name' => $additionalData['english_name'] ?? $submission['english_meaning'],
                    'description' => $submission['description'],
                    'timing' => $additionalData['timing'] ?? null,
                    'activities' => $additionalData['activities'] ?? null,
                    'location' => $additionalData['location'] ?? null,
                    'image' => $additionalData['image'] ?? null,
                    'created_by' => $submission['user_id']
                ]);
                break;

            case 'food':
                $model = new TivFood();
                if ($model->existsByField('tiv_name', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_name' => $submission['tiv_term'],
                    'english_name' => $additionalData['english_name'] ?? $submission['english_meaning'],
                    'description' => $submission['description'],
                    'ingredients' => $additionalData['ingredients'] ?? null,
                    'preparation_method' => $additionalData['preparation_method'] ?? null,
                    'image' => $additionalData['image'] ?? null,
                    'created_by' => $submission['user_id']
                ]);
                break;

            case 'animal':
                require_once BASE_PATH . '/models/TivAnimal.php';
                $model = new TivAnimal();
                if ($model->existsByField('tiv_name', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_name' => $submission['tiv_term'],
                    'name' => $additionalData['english_name'] ?? $submission['english_meaning'],
                    'description' => $submission['description'],
                    'animal_type' => $additionalData['animal_type'] ?? 'wild',
                    'cultural_use' => $additionalData['cultural_use'] ?? null,
                    'image' => $additionalData['image'] ?? null,
                    'created_by' => $submission['user_id']
                ]);
                break;

            case 'word':
                $model = new DailyWord();
                if ($model->existsByField('tiv_word', $submission['tiv_term'])) break;
                $model->create([
                    'tiv_word' => $submission['tiv_term'],
                    'english_meaning' => $submission['english_meaning'],
                    'part_of_speech' => $additionalData['part_of_speech'] ?? 'noun',
                    'pronunciation' => $additionalData['pronunciation'] ?? null,
                    'audio_file' => $additionalData['audio_file'] ?? null,
                    'example_tiv' => $additionalData['example_tiv'] ?? null,
                    'example_english' => $additionalData['example_english'] ?? null,
                    'created_by' => $submission['user_id'],
                    'source_id' => $additionalData['source_id'] ?? null
                ]);
                break;
        }
    }
}
