<?php
/**
 * Team Member Admin Controller
 */

require_once BASE_PATH . '/models/TeamMember.php';

class TeamController extends Controller
{
    private TeamMember $teamModel;

    public function __construct()
    {
        parent::__construct();
        $this->teamModel = new TeamMember();
    }

    /** List all team members */
    public function index(): void
    {
        $this->requireModerator();

        $members = $this->teamModel->getAll();

        $this->render('admin/team/index', [
            'title'       => 'Manage Team',
            'members'     => $members,
            'currentPage' => 'team',
        ], 'admin');
    }

    /** Show create form */
    public function create(): void
    {
        $this->requireModerator();

        $this->render('admin/team/form', [
            'title'       => 'Add Team Member',
            'member'      => null,
            'platforms'   => TeamMember::socialPlatforms(),
            'currentPage' => 'team',
        ], 'admin');
    }

    /** Store new team member */
    public function store(): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $data = $this->buildData();

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $imgFile = $this->saveTeamImage($_FILES['image']);
            if ($imgFile === false) {
                $this->storeOldInput();
                $this->back();
                return;
            }
            if ($imgFile) $data['image'] = $imgFile;
        }

        try {
            $id = $this->teamModel->create($data);
            Security::logActivity($this->user['id'], 'team_member_created', 'team_members', $id);
            $this->clearOldInput();
            $this->flash('Team member added successfully.', 'success');
            $this->redirect(url('admin/team'));
        } catch (Exception $e) {
            $this->storeOldInput();
            $this->flash('Error saving team member: ' . $e->getMessage(), 'error');
            $this->back();
        }
    }

    /** Show edit form */
    public function edit(string $id): void
    {
        $this->requireModerator();

        $member = $this->teamModel->find((int) $id);
        if (!$member) {
            $this->flash('Team member not found.', 'error');
            $this->redirect(url('admin/team'));
            return;
        }

        $this->render('admin/team/form', [
            'title'       => 'Edit Team Member',
            'member'      => $member,
            'platforms'   => TeamMember::socialPlatforms(),
            'currentPage' => 'team',
        ], 'admin');
    }

    /** Update team member */
    public function update(string $id): void
    {
        $this->requireModerator();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $member = $this->teamModel->find((int) $id);
        if (!$member) {
            $this->flash('Team member not found.', 'error');
            $this->redirect(url('admin/team'));
            return;
        }

        $data = $this->buildData();

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $imgFile = $this->saveTeamImage($_FILES['image']);
            if ($imgFile === false) {
                $this->storeOldInput();
                $this->back();
                return;
            }
            if ($imgFile) {
                // Delete old image
                if (!empty($member['image'])) {
                    $oldPath = UPLOADS_PATH . '/team/' . $member['image'];
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $data['image'] = $imgFile;
            }
        }

        try {
            $this->teamModel->update((int) $id, $data);
            Security::logActivity($this->user['id'], 'team_member_updated', 'team_members', (int) $id);
            $this->clearOldInput();
            $this->flash('Team member updated successfully.', 'success');
            $this->redirect(url('admin/team'));
        } catch (Exception $e) {
            $this->storeOldInput();
            $this->flash('Error updating team member: ' . $e->getMessage(), 'error');
            $this->back();
        }
    }

    /** Delete team member */
    public function delete(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $member = $this->teamModel->find((int) $id);
        if ($member && !empty($member['image'])) {
            $imgPath = UPLOADS_PATH . '/team/' . $member['image'];
            if (file_exists($imgPath)) @unlink($imgPath);
        }

        $this->teamModel->delete((int) $id);
        Security::logActivity($this->user['id'], 'team_member_deleted', 'team_members', (int) $id);
        $this->flash('Team member deleted.', 'success');
        $this->redirect(url('admin/team'));
    }

    // ── Helpers ────────────────────────────────────────────────

    private function buildData(): array
    {
        // Collect social URLs — only store platforms that have a value
        $platforms = array_keys(TeamMember::socialPlatforms());
        $socials = [];
        foreach ($platforms as $platform) {
            $url = trim($this->post('social_' . $platform, ''));
            if ($url !== '') {
                $socials[$platform] = $url;
            }
        }

        // Contributions: one per line → JSON array
        $rawContribs = trim($this->post('contributions', ''));
        $contributions = [];
        if ($rawContribs !== '') {
            $contributions = array_values(array_filter(
                array_map('trim', explode("\n", $rawContribs))
            ));
        }

        return [
            'user_id'       => $this->post('user_id') ?: null,
            'name'          => trim($this->post('name')),
            'role'          => trim($this->post('role')),
            'category'      => $this->post('category', 'contributor'),
            'short_bio'     => trim($this->post('short_bio', '')),
            'full_bio'      => trim($this->post('full_bio', '')),
            'contributions' => !empty($contributions) ? json_encode($contributions) : null,
            'socials'       => !empty($socials) ? json_encode($socials) : null,
            'sort_order'    => (int) $this->post('sort_order', 0),
            'is_active'     => $this->post('is_active') ? 1 : 0,
        ];
    }

    private function saveTeamImage(array $file): string|false|null
    {
        if (empty($file['name'])) return null;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('Image upload failed.', 'error');
            return false;
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $this->flash('Image too large. Maximum 5MB.', 'error');
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
            $this->flash('Invalid image type. Use JPG, PNG, GIF or WebP.', 'error');
            return false;
        }

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dir  = UPLOADS_PATH . '/team';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $filename = 'team_' . uniqid() . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            $this->flash('Failed to save image.', 'error');
            return false;
        }

        return $filename;
    }
}
