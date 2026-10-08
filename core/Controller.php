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
        // Every "not found" call site across the app (18 occurrences as of
        // this fix — DetailController, HistoricalFigureController,
        // TimelineController, CommunityController,
        // MarketingRedirectController, ReferencesController) renders this
        // exact view but never sent a real HTTP status — every one of
        // those pages was a soft 404 (200 OK with "Not Found" text),
        // which Google Search Console flagged directly. Setting the
        // status here, once, fixes every existing and future call site
        // without needing to touch each one individually.
        if ($view === 'errors/404' && ! headers_sent()) {
            http_response_code(404);
        }

        $data['user'] = $this->user;

        $path = trim((string) ($_GET['url'] ?? ''), '/');

        // Pages that are not content for search engines: internal search results
        // (?q=...) and forms/account pages (join, contribute, suggestions, nominate,
        // login...). They stay usable but are noindex and carry no ads.
        if (trim((string) ($_GET['q'] ?? '')) !== ''
            || preg_match('#^(contribute|suggestions|community/join|nominate-influential|login|register|forgot-password|reset-password|profile)(/|$)#', $path)) {
            $data['noindex'] = true;
        }

        // Ad eligibility for this page (read by ads_eligible() in config/security.php):
        // only indexable content pages served with 200 and the main layout, never
        // account/form/admin pages or the Bible reader (text not original to this site).
        $GLOBALS['_ads_page_eligible'] = $layout === 'main'
            && http_response_code() === 200
            && empty($data['noindex'])
            && empty($data['noAds'])
            && !preg_match('#^(admin|login|register|logout|profile|forgot-password|reset-password|contribute|suggestions|community/join|nominate-influential|newsletter|bible|translate/history|api|go|outreach)(/|$)#', $path);

        // Release the session lock before rendering.
        // PHP sessions are file-locked for the entire request by default —
        // closing early prevents parallel browser requests from queuing.
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Ensure CSRF token exists in the session FILE before we close it.
            // csrf_field() is called during rendering (after session closes),
            // so without this the token would only be in memory and never persisted.
            Security::generateCSRFToken();

            // Ensure the Charymeld-specific CSRF token is also persisted before
            // session close. The charymeld partial reads this during rendering
            // (after session_write_close), so it must already exist on disk.
            if (empty($_SESSION['charymeld_csrf'])) {
                $_SESSION['charymeld_csrf'] = bin2hex(random_bytes(32));
            }

            // Preload flash messages so flash() still works after session closes
            $GLOBALS['_flash_store'] = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);

            // Form errors and old input are shown once. Views unset them only after the
            // session is closed (which would not persist), so remove them from the stored
            // session here and keep them in memory for this render only. Otherwise a refused
            // save would pre-fill later forms — even another record's — with stale values.
            $once = array_intersect_key($_SESSION, ['errors' => 1, 'old_input' => 1]);
            unset($_SESSION['errors'], $_SESSION['old_input']);
            session_write_close();
            $_SESSION = $once + $_SESSION;
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
