<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/CommunityApplication.php';
require_once BASE_PATH . '/models/CommunityMember.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/services/RegistrationMailer.php';

class CommunityController extends Controller
{
    private CommunityApplication $appModel;
    private CommunityMember $memberModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->appModel    = new CommunityApplication();
        $this->memberModel = new CommunityMember();
        $this->userModel   = new User();
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

    public function show(string $id): void
    {
        $member = $this->memberModel->find((int) $id);

        if (!$member || !$member['is_active']) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $this->render('community/show', [
            'title'       => $member['full_name'] . ' | Tiv Heritage Archive',
            'description' => $member['short_bio'] ?: 'Meet ' . $member['full_name'] . ', a ' . $member['member_type'] . ' at the Tiv Heritage Archive.',
            'member'      => $member,
            'currentPage' => 'community',
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
        $reason   = trim($_POST['reason_for_joining'] ?? '');
        $type     = $_POST['member_type'] ?? 'contributor';

        // Logged-in users apply under their own account's email — the
        // posted email field (if any) is ignored so they can't submit
        // an application against a different account than their own.
        $isLoggedIn = !empty($this->user);
        $email = $isLoggedIn ? $this->user['email'] : Security::sanitizeEmail($_POST['email'] ?? '');

        if (!$fullName) {
            $errors['full_name'] = 'Full name is required.';
        }
        if (!$isLoggedIn && !Security::validateEmail($email)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if (!$reason) {
            $errors['reason_for_joining'] = 'Please tell us why you want to join.';
        }
        if (!in_array($type, ['contributor', 'researcher'])) {
            $errors['member_type'] = 'Please select a membership type.';
        }

        // Resolve which user account this application links to.
        $userId = null;
        $existingUser = null;
        if ($isLoggedIn) {
            $userId = (int) $this->user['id'];
        } elseif (!isset($errors['email'])) {
            $existingUser = $this->userModel->findByEmail($email);
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

            if (!$password) {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            }

            if ($existingUser) {
                // This email already has an account — require its real
                // password before linking, so an application can't be
                // silently attached to someone else's account just by
                // typing their email address.
                if (!isset($errors['password']) && !Security::verifyPassword($password, $existingUser['password'])) {
                    $errors['password'] = 'This email is already registered. Enter that account\'s correct password to link your application, or log in first.';
                }
                $userId = $existingUser['id'];
            } else {
                if (!isset($errors['password']) && $password !== $passwordConfirm) {
                    $errors['password_confirm'] = 'Passwords do not match.';
                }
            }
        }

        if ($errors) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect(url('community/join'));
            return;
        }

        // New account only when the email genuinely didn't exist yet.
        if (!$isLoggedIn && !$existingUser) {
            $userId = $this->userModel->createUser([
                'name'     => $fullName,
                'email'    => $email,
                'password' => $password,
                'role'     => 'user', // upgraded to 'contributor' on application approval
            ]);
        }

        $data = [
            'user_id'            => $userId,
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

        $newId = $this->appModel->create($data);

        // Email notifications are additive to the application record just
        // created above and must never block a successful registration —
        // RegistrationMailer logs and swallows any delivery failure itself.
        $newApp = $this->appModel->find($newId);
        if ($newApp) {
            RegistrationMailer::newRegistration($newApp);
        }

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
