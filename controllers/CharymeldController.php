<?php
/**
 * CharymeldController — Handles the Charymeld AI assistant chat endpoint.
 *
 * POST /charymeld/chat
 *   body: { message, history[], csrf_token }
 *   response: { reply } | { error }
 */

require_once BASE_PATH . '/services/CharymeldService.php';

class CharymeldController extends Controller
{
    /**
     * GET /charymeld/token
     * Returns a fresh charymeld CSRF token. Called by the widget JS after a 403
     * so the user can retry without a page reload.
     */
    public function token(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name(SESSION_NAME);
            session_start();
        }
        // Generate (or retrieve) the token and return it
        if (empty($_SESSION[self::CYM_TOKEN_KEY])) {
            $_SESSION[self::CYM_TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        $this->json(['csrf_token' => $_SESSION[self::CYM_TOKEN_KEY]]);
    }

    /** POST /charymeld/chat */
    public function chat(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // CSRF check using a dedicated Charymeld session token.
        // A separate token is used so that other form submissions on the page
        // (which call Security::regenerateCSRFToken() on the main token) never
        // invalidate the chat widget's token.
        $token = $this->post('csrf_token', '');
        if (empty($token) || !$this->validateCharymeldToken($token)) {
            $this->json(['error' => 'Invalid request token.'], 403);
            return;
        }

        // Rate limit: 25 requests per hour per session
        if (!$this->checkRateLimit()) {
            $this->json(['error' => 'You\'ve sent too many messages. Please wait a few minutes.'], 429);
            return;
        }

        $message = trim($this->post('message', ''));
        if ($message === '') {
            $this->json(['error' => 'Please type a message.'], 400);
            return;
        }
        if (mb_strlen($message) > 800) {
            $this->json(['error' => 'Message is too long (max 800 characters).'], 400);
            return;
        }

        // Decode conversation history sent by the client. Read $_POST directly
        // (not via $this->post()) — that helper runs Security::sanitize(), which
        // calls stripslashes() and corrupts any escaped quote inside the JSON
        // (e.g. a previous reply quoting a proverb or book title), silently
        // breaking json_decode() and dropping history to []. This is a structured
        // payload, not free text, so it's decoded directly instead; every string
        // pulled out of it is only ever used as archive search input (never
        // rendered as HTML or used in raw SQL), so skipping the free-text
        // sanitizer here is safe.
        $rawHistory = $_POST['history'] ?? '[]';
        $history    = is_string($rawHistory) && mb_strlen($rawHistory) <= 20000
            ? (json_decode($rawHistory, true) ?? [])
            : [];
        if (!is_array($history)) {
            $history = [];
        }

        // ArchiveIntelligence handles its own DB search internally
        $service  = new CharymeldService();
        $reply    = $service->chat($message, '', $history);

        if ($reply === null) {
            $this->json(['error' => 'Charymeld is temporarily unavailable. Please try again shortly.'], 503);
            return;
        }

        // Issue a fresh Charymeld token with each reply so the JS always has
        // a current token even across multiple messages in one session.
        $newToken = $this->issueCharymeldToken();

        $this->json(['reply' => $reply, 'csrf_token' => $newToken]);
    }

    // ─────────────────────────────────────────────────────────────
    // Charymeld-specific CSRF token (never rotated by other forms)
    // ─────────────────────────────────────────────────────────────

    // Session key for the Charymeld-specific CSRF token.
    // Must match the key used in views/partials/charymeld.php.
    private const CYM_TOKEN_KEY = 'charymeld_csrf';

    /** Validate the submitted token against the session. */
    private function validateCharymeldToken(string $token): bool
    {
        $stored = $_SESSION[self::CYM_TOKEN_KEY] ?? '';
        return !empty($stored) && hash_equals($stored, $token);
    }

    /** Rotate and return a new Charymeld token (sent back with each reply). */
    private function issueCharymeldToken(): string
    {
        $_SESSION[self::CYM_TOKEN_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::CYM_TOKEN_KEY];
    }

    /**
     * Session-based rate limiter: 25 messages per rolling 60-minute window.
     */
    private function checkRateLimit(): bool
    {
        $key    = 'charymeld_rate';
        $window = 3600; // 1 hour
        $limit  = 25;
        $now    = time();

        $timestamps = $_SESSION[$key] ?? [];
        // Drop entries outside the window
        $timestamps = array_values(array_filter($timestamps, fn($t) => ($now - $t) < $window));

        if (count($timestamps) >= $limit) {
            return false;
        }

        $timestamps[]    = $now;
        $_SESSION[$key]  = $timestamps;
        return true;
    }
}
