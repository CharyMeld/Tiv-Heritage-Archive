<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/CommunityApplication.php';
require_once BASE_PATH . '/models/CommunityMember.php';

class CommunityController extends Controller
{
    private CommunityApplication $appModel;
    private CommunityMember $memberModel;

    public function __construct()
    {
        parent::__construct();
        $this->appModel    = new CommunityApplication();
        $this->memberModel = new CommunityMember();
    }

    public function index(): void
    {
        $contributors = $this->memberModel->getDirectory('contributor');
        $researchers  = $this->memberModel->getDirectory('researcher');
        $stats        = $this->memberModel->getStats();

        $this->render('community/index', [
            'title'        => 'Community Directory | Tiv Heritage Archive',
            'description'  => 'Meet the contributors and researchers preserving Tiv heritage.',
            'contributors' => $contributors,
            'researchers'  => $researchers,
            'stats'        => $stats,
            'currentPage'  => 'community',
        ]);
    }

    public function joinForm(): void
    {
        $this->render('community/join', [
            'title'       => 'Join the Community | Tiv Heritage Archive',
            'description' => 'Apply to become a contributor or researcher in the Tiv Heritage Archive community.',
            'currentPage' => 'community_join',
        ]);
    }

    public function submitApplication(): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $errors = [];

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $reason   = trim($_POST['reason_for_joining'] ?? '');
        $type     = $_POST['member_type'] ?? 'contributor';

        if (!$fullName) {
            $errors['full_name'] = 'Full name is required.';
        }
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if (!$reason) {
            $errors['reason_for_joining'] = 'Please tell us why you want to join.';
        }
        if (!in_array($type, ['contributor', 'researcher'])) {
            $errors['member_type'] = 'Please select a membership type.';
        }

        if ($errors) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect(url('community/join'));
            return;
        }

        $data = [
            'full_name'          => $fullName,
            'email'              => $email,
            'phone'              => trim($_POST['phone'] ?? '') ?: null,
            'country'            => trim($_POST['country'] ?? '') ?: null,
            'state_region'       => trim($_POST['state_region'] ?? '') ?: null,
            'occupation'         => trim($_POST['occupation'] ?? '') ?: null,
            'area_of_interest'   => trim($_POST['area_of_interest'] ?? '') ?: null,
            'member_type'        => $type,
            'short_bio'          => trim($_POST['short_bio'] ?? '') ?: null,
            'skills'             => trim($_POST['skills'] ?? '') ?: null,
            'reason_for_joining' => $reason,
            'status'             => 'pending',
        ];

        $data['profile_photo']      = $this->handleUpload('profile_photo', 'community/photos');
        $data['supporting_document']= $this->handleUpload('supporting_document', 'community/documents', false);

        $this->appModel->create($data);

        $this->redirect(url('community/join/success'));
    }

    public function joinSuccess(): void
    {
        $this->render('community/success', [
            'title'       => 'Application Submitted | Tiv Heritage Archive',
            'currentPage' => 'community_join',
        ]);
    }

    private function handleUpload(string $field, string $subDir, bool $imageOnly = true): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return null;
        }

        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        if ($imageOnly) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
                return null;
            }
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
