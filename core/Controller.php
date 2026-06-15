<?php
/**
 * Tiv Culture Archive - Base Controller
 * All controllers extend this class
 */

abstract class Controller
{
    protected View $view;
    protected ?array $user = null;

    public function __construct()
    {
        $this->view = new View();
        $this->user = current_user();
    }

    /**
     * Render a view
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data['user'] = $this->user;

        // Release the session lock before rendering.
        // PHP sessions are file-locked for the entire request by default —
        // closing early prevents parallel browser requests from queuing.
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Ensure CSRF token exists in the session FILE before we close it.
            // csrf_field() is called during rendering (after session closes),
            // so without this the token would only be in memory and never persisted.
            Security::generateCSRFToken();

            // Preload flash messages so flash() still works after session closes
            $GLOBALS['_flash_store'] = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            session_write_close();
        }

        $this->view->render($view, $data, $layout);
    }

    /**
     * Render view without layout
     */
    protected function renderPartial(string $view, array $data = []): void
    {
        $data['user'] = $this->user;
        $this->view->renderPartial($view, $data);
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        redirect($url);
    }

    /**
     * Redirect back to previous page
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }

    /**
     * Set flash message
     */
    protected function flash(string $message, string $type = 'info'): void
    {
        set_flash('message', $message, $type);
    }

    /**
     * Store old input in session
     */
    protected function storeOldInput(): void
    {
        $_SESSION['old_input'] = $_POST;
    }

    /**
     * Clear old input from session
     */
    protected function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }

    /**
     * Get POST data
     */
    protected function post(string $key, $default = null)
    {
        return Security::sanitize($_POST[$key] ?? $default);
    }

    /**
     * Get GET data
     */
    protected function get(string $key, $default = null)
    {
        return Security::sanitize($_GET[$key] ?? $default);
    }

    /**
     * Validate CSRF token
     */
    protected function validateCSRF(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!Security::validateCSRFToken($token)) {
            $this->flash('Invalid security token. Please try again.', 'error');
            return false;
        }
        Security::regenerateCSRFToken();
        return true;
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!is_logged_in()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->flash('Please log in to continue.', 'warning');
            $this->redirect(url('login'));
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!has_role($role)) {
            $this->flash('You do not have permission to access this page.', 'error');
            $this->redirect(url('/'));
        }
    }

    /**
     * Require admin role
     */
    protected function requireAdmin(): void
    {
        $this->requireRole('admin');
    }

    /**
     * Require moderator role
     */
    protected function requireModerator(): void
    {
        $this->requireRole('moderator');
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $fields): array
    {
        $errors = [];

        foreach ($fields as $field => $label) {
            if (empty($this->post($field))) {
                $errors[$field] = "{$label} is required.";
            }
        }

        return $errors;
    }

    /**
     * Paginate results
     */
    protected function paginate(int $total, int $perPage = ITEMS_PER_PAGE): array
    {
        $page = max(1, (int) $this->get('page', 1));
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ];
    }
}
