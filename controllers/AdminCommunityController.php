<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/CommunityApplication.php';
require_once BASE_PATH . '/models/CommunityMember.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/services/RegistrationMailer.php';

class AdminCommunityController extends Controller
{
    private CommunityApplication $appModel;
    private CommunityMember $memberModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->appModel    = new CommunityApplication();
        $this->memberModel = new CommunityMember();
        $this->userModel   = new User();
    }

    public function applications(): void
    {
        $status = $_GET['status'] ?? '';
        $type   = $_GET['type'] ?? '';
        $q      = trim($_GET['q'] ?? '');

        $total = $q
            ? count($this->appModel->searchApplicants($q))
            : $this->appModel->countAll($status, $type);

        $pagination    = $this->paginate($total, 20);
        $applications  = $q
            ? $this->appModel->search($q, 20)
            : $this->appModel->getAll($status, $type, 20, $pagination['offset']);

        $stats = $this->appModel->getStats();

        $this->render('admin/community/applications', [
            'title'        => 'Community Applications | Admin',
            'currentPage'  => 'community_applications',
            'applications' => $applications,
            'stats'        => $stats,
            'pagination'   => $pagination,
            'filter_status'=> $status,
            'filter_type'  => $type,
            'q'            => $q,
        ], 'admin');
    }

    public function viewApplication(string $id): void
    {
        $app = $this->appModel->find((int) $id);
        if (!$app) {
            $this->redirect(url('admin/community/applications'));
            return;
        }

        $member = $this->memberModel->getByApplicationId((int) $id);

        $this->render('admin/community/view-application', [
            'title'       => 'Application: ' . $app['full_name'] . ' | Admin',
            'currentPage' => 'community_applications',
            'app'         => $app,
            'member'      => $member,
        ], 'admin');
    }

    public function approve(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $app = $this->appModel->find((int) $id);
        if (!$app || $app['status'] === 'approved') {
            $this->flash('Application not found or already approved.', 'error');
            $this->redirect(url('admin/community/applications'));
            return;
        }

        $notes = trim($_POST['admin_notes'] ?? '');

        $this->appModel->update((int) $id, [
            'status'      => 'approved',
            'admin_notes' => $notes ?: null,
            'reviewed_by' => $this->user['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        // Create public member record if not already exists
        $existing = $this->memberModel->getByApplicationId((int) $id);
        if (!$existing) {
            $this->memberModel->createFromApplication($app);
        } else {
            $this->memberModel->update($existing['id'], ['is_active' => 1]);
        }

        // Approval is the point where "applied" becomes real contributor
        // portal access — only ever raises role from the base 'user'
        // level, never touches moderator/admin accounts.
        if (!empty($app['user_id'])) {
            $linkedUser = $this->userModel->find((int) $app['user_id']);
            if ($linkedUser && $linkedUser['role'] === 'user') {
                $this->userModel->updateRole((int) $app['user_id'], 'contributor');
            }
        }

        // Email is additive to the approval above and must never block it —
        // RegistrationMailer logs and swallows any delivery failure itself.
        $updatedApp = $this->appModel->find((int) $id);
        if ($updatedApp) {
            RegistrationMailer::approved($updatedApp);
        }

        $this->flash('Application approved and member added to the directory.', 'success');
        $this->redirect(url('admin/community/applications'));
    }

    public function reject(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $app = $this->appModel->find((int) $id);
        if (!$app || $app['status'] === 'rejected') {
            $this->flash('Application not found or already rejected.', 'error');
            $this->redirect(url('admin/community/applications'));
            return;
        }

        $notes = trim($_POST['admin_notes'] ?? '');

        $this->appModel->update((int) $id, [
            'status'      => 'rejected',
            'admin_notes' => $notes ?: null,
            'reviewed_by' => $this->user['id'],
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        // Deactivate member record if one exists
        $existing = $this->memberModel->getByApplicationId((int) $id);
        if ($existing) {
            $this->memberModel->update($existing['id'], ['is_active' => 0]);
        }

        // Email is additive to the rejection above and must never block it —
        // RegistrationMailer logs and swallows any delivery failure itself.
        $updatedApp = $this->appModel->find((int) $id);
        if ($updatedApp) {
            RegistrationMailer::declined($updatedApp);
        }

        $this->flash('Application rejected.', 'info');
        $this->redirect(url('admin/community/applications'));
    }

    public function members(): void
    {
        $type = $_GET['type'] ?? '';
        $total = $this->memberModel->countAll($type);
        $pagination = $this->paginate($total, 20);
        $members = $this->memberModel->getAll($type, 20, $pagination['offset']);
        $stats   = $this->memberModel->getStats();

        $this->render('admin/community/members', [
            'title'       => 'Community Members | Admin',
            'currentPage' => 'community_members',
            'members'     => $members,
            'stats'       => $stats,
            'pagination'  => $pagination,
            'filter_type' => $type,
        ], 'admin');
    }

    public function editMember(string $id): void
    {
        $member = $this->memberModel->find((int) $id);
        if (!$member) {
            $this->redirect(url('admin/community/members'));
            return;
        }

        $this->render('admin/community/edit-member', [
            'title'       => 'Edit Member: ' . $member['full_name'] . ' | Admin',
            'currentPage' => 'community_members',
            'member'      => $member,
        ], 'admin');
    }

    public function updateMember(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $member = $this->memberModel->find((int) $id);
        if (!$member) {
            $this->redirect(url('admin/community/members'));
            return;
        }

        $photo = $member['profile_photo'];
        if (!empty($_FILES['profile_photo']['name']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $new = $this->handleImageUpload('profile_photo', 'community/photos');
            if ($new) {
                $photo = $new;
            }
        }

        $this->memberModel->update((int) $id, [
            'full_name'       => trim($_POST['full_name'] ?? $member['full_name']),
            'member_type'     => $_POST['member_type'] ?? $member['member_type'],
            'short_bio'       => trim($_POST['short_bio'] ?? '') ?: null,
            'area_of_interest'=> trim($_POST['area_of_interest'] ?? '') ?: null,
            'location'        => trim($_POST['location'] ?? '') ?: null,
            'profile_photo'   => $photo,
            'is_featured'     => isset($_POST['is_featured']) ? 1 : 0,
            'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        ]);

        $this->flash('Member profile updated.', 'success');
        $this->redirect(url('admin/community/members'));
    }

    public function featureMember(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $member = $this->memberModel->find((int) $id);
        if ($member) {
            $this->memberModel->update((int) $id, ['is_featured' => $member['is_featured'] ? 0 : 1]);
            $this->flash('Member featured status updated.', 'success');
        }

        $this->redirect(url('admin/community/members'));
    }

    public function toggleMember(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $member = $this->memberModel->find((int) $id);
        if ($member) {
            $this->memberModel->update((int) $id, ['is_active' => $member['is_active'] ? 0 : 1]);
            $this->flash('Member status updated.', 'success');
        }

        $this->redirect(url('admin/community/members'));
    }

    public function removeMember(string $id): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->memberModel->delete((int) $id);
        $this->flash('Member removed from public directory.', 'info');
        $this->redirect(url('admin/community/members'));
    }

    public function exportMembers(): void
    {
        $members = $this->memberModel->getAll('', 9999, 0);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="community_members_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Name', 'Email', 'Type', 'Area of Interest', 'Location', 'Active', 'Featured', 'Date Joined']);
        foreach ($members as $m) {
            fputcsv($out, [
                $m['id'], $m['full_name'], $m['email'], $m['member_type'],
                $m['area_of_interest'], $m['location'],
                $m['is_active'] ? 'Yes' : 'No',
                $m['is_featured'] ? 'Yes' : 'No',
                $m['date_joined'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportApplications(): void
    {
        $apps = $this->appModel->getAll('', '', 9999, 0);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="community_applications_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Name', 'Email', 'Type', 'Status', 'Country', 'Occupation', 'Applied']);
        foreach ($apps as $a) {
            fputcsv($out, [
                $a['id'], $a['full_name'], $a['email'], $a['member_type'],
                $a['status'], $a['country'], $a['occupation'], $a['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    private function handleImageUpload(string $field, string $subDir): ?string
    {
        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
            return null;
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('cm_', true) . '.' . $ext;
        $dir      = UPLOADS_PATH . '/' . $subDir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return $subDir . '/' . $filename;
    }
}
