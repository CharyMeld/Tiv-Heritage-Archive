<?php
/**
 * Auth Controller
 */

require_once BASE_PATH . '/models/User.php';

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Login form
     */
    public function loginForm(): void
    {
        if (is_logged_in()) {
            $this->redirect(url('profile'));
            return;
        }

        $this->render('auth/login', [
            'title' => 'Login',
            'currentPage' => 'login'
        ]);
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $email = Security::sanitizeEmail($this->post('email'));
        $password = $this->post('password');

        // Check rate limiting
        $rateLimit = Security::checkLoginAttempts($email);
        if (!$rateLimit['allowed']) {
            $this->storeOldInput();
            $this->flash($rateLimit['message'], 'error');
            $this->back();
            return;
        }

        // Validate fields
        if (empty($email) || empty($password)) {
            Security::logLoginAttempt($email, false);
            $this->storeOldInput();
            $this->flash('Please enter your email and password.', 'error');
            $this->back();
            return;
        }

        // Authenticate
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            Security::logLoginAttempt($email, false);
            $this->storeOldInput();

            $remaining = $rateLimit['remaining_attempts'] - 1;
            if ($remaining > 0) {
                $this->flash("Invalid email or password. {$remaining} attempts remaining.", 'error');
            } else {
                $this->flash('Too many failed attempts. Please try again later.', 'error');
            }

            $this->back();
            return;
        }

        // Successful login
        Security::logLoginAttempt($email, true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;

        Security::logActivity($user['id'], 'user_login');

        $this->clearOldInput();

        // Redirect to intended URL or profile
        $intendedUrl = $_SESSION['intended_url'] ?? url('profile');
        unset($_SESSION['intended_url']);

        $this->flash('Welcome back, ' . $user['name'] . '!', 'success');
        $this->redirect($intendedUrl);
    }

    /**
     * Register form
     */
    public function registerForm(): void
    {
        if (is_logged_in()) {
            $this->redirect(url('profile'));
            return;
        }

        $this->render('auth/register', [
            'title' => 'Register',
            'currentPage' => 'register'
        ]);
    }

    /**
     * Handle registration
     */
    public function register(): void
    {
        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $errors = $this->validateRequired([
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirm' => 'Password Confirmation'
        ]);

        $email = Security::sanitizeEmail($this->post('email'));
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');

        // Validate email format
        if (!Security::validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // Check if email exists
        if ($this->userModel->emailExists($email)) {
            $errors['email'] = 'This email is already registered.';
        }

        // Validate password
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $this->storeOldInput();
            $_SESSION['errors'] = $errors;
            $this->back();
            return;
        }

        // Create user
        try {
            $userId = $this->userModel->createUser([
                'name' => $this->post('name'),
                'email' => $email,
                'password' => $password,
                'role' => 'user'
            ]);

            Security::logActivity($userId, 'user_registered');

            // Auto-login
            $user = $this->userModel->find($userId);
            unset($user['password']);

            $_SESSION['user_id'] = $userId;
            $_SESSION['user'] = $user;

            $this->clearOldInput();
            $this->flash('Welcome to ' . SITE_NAME . '! Your account has been created.', 'success');
            $this->redirect(url('profile'));

        } catch (Exception $e) {
            $this->storeOldInput();
            $this->flash('An error occurred. Please try again.', 'error');
            $this->back();
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if (is_logged_in()) {
            Security::logActivity($_SESSION['user_id'], 'user_logout');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->flash('You have been logged out.', 'info');
        $this->redirect(url('/'));
    }

    /**
     * Forgot password form
     */
    public function forgotPasswordForm(): void
    {
        $this->render('auth/forgot-password', [
            'title' => 'Forgot Password',
            'currentPage' => 'login'
        ]);
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword(): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $email = Security::sanitizeEmail($this->post('email'));

        if (!Security::validateEmail($email)) {
            $this->flash('Please enter a valid email address.', 'error');
            $this->back();
            return;
        }

        // Always show success message (security best practice)
        // In production, you would send an actual password reset email

        Security::logActivity(null, 'password_reset_requested', 'user', null, null, ['email' => $email]);

        $this->flash('If an account exists with that email, you will receive password reset instructions.', 'info');
        $this->redirect(url('login'));
    }
}
