<?php
/**
 * Profile Controller
 */

require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Submission.php';
require_once BASE_PATH . '/models/SavedItem.php';
require_once BASE_PATH . '/models/Source.php';

class ProfileController extends Controller
{
    private User $userModel;
    private Submission $submissionModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->submissionModel = new Submission();
    }

    /**
     * Profile page
     */
    public function index(): void
    {
        $this->requireAuth();

        $totalSubmissions = $this->submissionModel->countUserSubmissions($this->user['id']);
        $approvedSubmissions = $this->userModel->getApprovedSubmissionCount($this->user['id']);
        $recentSubmissions = $this->submissionModel->getUserSubmissions($this->user['id'], 5);

        $this->render('profile/index', [
            'title' => 'My Profile',
            'totalSubmissions' => $totalSubmissions,
            'approvedSubmissions' => $approvedSubmissions,
            'recentSubmissions' => $recentSubmissions,
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Edit profile form
     */
    public function edit(): void
    {
        $this->requireAuth();

        $this->render('profile/edit', [
            'title' => 'Edit Profile',
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Update profile
     */
    public function update(): void
    {
        $this->requireAuth();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $errors = $this->validateRequired([
            'name' => 'Name',
            'email' => 'Email'
        ]);

        $email = Security::sanitizeEmail($this->post('email'));

        if (!Security::validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // Check if email is taken by another user
        if ($this->userModel->emailExists($email, $this->user['id'])) {
            $errors['email'] = 'This email is already in use.';
        }

        if (!empty($errors)) {
            $this->storeOldInput();
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        // Update user
        $this->userModel->update($this->user['id'], [
            'name' => $this->post('name'),
            'email' => $email,
            'bio' => $this->post('bio')
        ]);

        // Update session
        $_SESSION['user']['name'] = $this->post('name');
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['bio'] = $this->post('bio');

        Security::logActivity($this->user['id'], 'profile_updated');

        $this->clearOldInput();
        $this->flash('Profile updated successfully.', 'success');
        $this->redirect(url('profile'));
    }

    /**
     * View all submissions
     */
    public function submissions(): void
    {
        $this->requireAuth();

        $total = $this->submissionModel->countUserSubmissions($this->user['id']);
        $pagination = $this->paginate($total);
        $submissions = $this->submissionModel->getUserSubmissions(
            $this->user['id'],
            $pagination['per_page'],
            $pagination['offset']
        );

        $this->render('profile/submissions', [
            'title' => 'My Submissions',
            'submissions' => $submissions,
            'pagination' => $pagination,
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Change password form
     */
    public function passwordForm(): void
    {
        $this->requireAuth();

        $this->render('profile/password', [
            'title' => 'Change Password',
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(): void
    {
        $this->requireAuth();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $errors = $this->validateRequired([
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password'
        ]);

        $currentPassword = $this->post('current_password');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        // Verify current password
        $user = $this->userModel->find($this->user['id']);
        if (!Security::verifyPassword($currentPassword, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        // Update password
        $this->userModel->updatePassword($this->user['id'], $newPassword);

        Security::logActivity($this->user['id'], 'password_changed');

        $this->flash('Password updated successfully.', 'success');
        $this->redirect(url('profile'));
    }

    /** GET profile/collection — references the user has saved via "Save to Collection" */
    public function collection(): void
    {
        $this->requireAuth();

        $savedModel = new SavedItem();
        $sources = $savedModel->getForUserByTable((int) $this->user['id'], 'sources');

        $this->render('profile/collection', [
            'title'   => 'My Collection',
            'sources' => $sources,
        ]);
    }
}
