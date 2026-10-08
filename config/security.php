<?php
/**
 * Tiv Culture Archive - Security Functions
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

/**
 * Security Helper Class
 */
class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken(): string
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Regenerate CSRF token (after form submission)
     */
    public static function regenerateCSRFToken(): string
    {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Get CSRF token input field
     */
    public static function csrfField(): string
    {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Escape output for HTML
     */
    public static function escape(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Alias for escape
     */
    public static function e(?string $string): string
    {
        return self::escape($string);
    }

    /**
     * Sanitize input string
     */
    public static function sanitize(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        $string = trim($string);
        $string = stripslashes($string);
        return $string;
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail(?string $email): string
    {
        return filter_var(trim($email ?? ''), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validate email
     */
    public static function validateEmail(?string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get client IP address
     */
    public static function getClientIP(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * Generate random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Encrypt a secret for at-rest storage (e.g. a Facebook Page access
     * token). AES-256-GCM: authenticated encryption, so decrypt() can
     * detect tampering/corruption rather than silently returning garbage.
     * Returns a single base64 string (IV + auth tag + ciphertext) suitable
     * for one database column.
     */
    public static function encrypt(string $plaintext): string
    {
        $key = hex2bin(APP_ENCRYPTION_KEY);
        $iv = random_bytes(12);
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Reverses encrypt(). Returns null (never throws) if the value is
     * missing, malformed, or fails authentication — callers should treat
     * null as "credentials unusable, re-enter them".
     */
    public static function decrypt(?string $encoded): ?string
    {
        if (empty($encoded)) {
            return null;
        }

        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 12 + 16) {
            return null;
        }

        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $ciphertext = substr($raw, 28);

        $key = hex2bin(APP_ENCRYPTION_KEY);
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return $plaintext === false ? null : $plaintext;
    }

    /**
     * Check login attempts and rate limiting
     */
    public static function checkLoginAttempts(string $email): array
    {
        $db = Database::getInstance();
        $ip = self::getClientIP();

        // Check if IP is blocked
        $stmt = $db->prepare("
            SELECT * FROM ip_blocks
            WHERE ip_address = ?
            AND (permanent = 1 OR blocked_until > NOW())
        ");
        $stmt->execute([$ip]);

        if ($stmt->fetch()) {
            return [
                'allowed' => false,
                'message' => 'Your IP address has been temporarily blocked. Please try again later.'
            ];
        }

        // Count recent failed attempts
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempts
            FROM login_attempts
            WHERE (email = ? OR ip_address = ?)
            AND success = 0
            AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$email, $ip, LOCKOUT_DURATION]);
        $result = $stmt->fetch();

        if ($result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            // Auto-block IP if threshold reached
            if ($result['attempts'] >= IP_BLOCK_THRESHOLD) {
                $stmt = $db->prepare("
                    INSERT INTO ip_blocks (ip_address, reason, blocked_until)
                    VALUES (?, 'Too many failed login attempts', DATE_ADD(NOW(), INTERVAL 1 HOUR))
                    ON DUPLICATE KEY UPDATE blocked_until = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                ");
                $stmt->execute([$ip]);
            }

            return [
                'allowed' => false,
                'message' => 'Too many failed login attempts. Please try again in ' . LOCKOUT_DURATION . ' minutes.',
                'remaining_attempts' => 0
            ];
        }

        return [
            'allowed' => true,
            'remaining_attempts' => MAX_LOGIN_ATTEMPTS - $result['attempts']
        ];
    }

    /**
     * Log login attempt
     */
    public static function logLoginAttempt(string $email, bool $success): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO login_attempts (email, ip_address, user_agent, success)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $email,
            self::getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $success ? 1 : 0
        ]);
    }

    /**
     * Log activity
     */
    public static function logActivity(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO activity_log
            (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            self::getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /**
     * Validate file upload
     */
    public static function validateImageUpload(array $file): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed.';
            return ['valid' => false, 'errors' => $errors];
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds maximum allowed (' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB).';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_IMAGE_EXTENSIONS);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            $errors[] = 'Invalid file extension.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType,
            'extension' => $extension
        ];
    }

    /**
     * Generate safe filename for upload
     */
    public static function generateSafeFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return uniqid('img_', true) . '.' . $extension;
    }
}

/**
 * Helper functions for views
 */

function e(?string $string): string
{
    return Security::escape($string);
}

function csrf_field(): string
{
    return Security::csrfField();
}

function csrf_token(): string
{
    return Security::generateCSRFToken();
}

function old(string $key, string $default = ''): string
{
    return Security::escape($_SESSION['old_input'][$key] ?? $default);
}

function flash(string $key): ?array
{
    // After session_write_close(), flash data is in $GLOBALS['_flash_store']
    if (isset($GLOBALS['_flash_store'][$key])) {
        $message = $GLOBALS['_flash_store'][$key];
        unset($GLOBALS['_flash_store'][$key]);
        return $message;
    }
    // Fallback: session is still open (e.g. during a POST redirect flow)
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function set_flash(string $key, string $message, string $type = 'info'): void
{
    // Re-open session if it was closed early so the flash persists across redirect
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start(['name' => SESSION_NAME]);
    }
    $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
    session_write_close();
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function redirect301(string $url): void
{
    header('Location: ' . $url, true, 301);
    exit;
}

function url(string $path = ''): string
{
    return SITE_URL . '/' . ltrim($path, '/');
}

/** Public URL inside the Nigeria Heritage section (see NIGERIA_BASE_URL). */
function nigeria_url(string $path = ''): string
{
    $path = trim($path, '/');
    return NIGERIA_BASE_URL . ($path !== '' ? '/' . $path : '');
}

/**
 * Main page of an archive section. Dictionary, names, proverbs, plants, animals, foods
 * and festivals are read on their full-text pages (CollectionController); the old
 * card listings at archive/{section} only serve search results (?q=) and redirect
 * otherwise. Other sections keep archive/{section}.
 */
function section_path(string $section): string
{
    return [
        'words'     => 'collections/dictionary/a',
        'names'     => 'collections/names',
        'proverbs'  => 'collections/proverbs',
        'plants'    => 'collections/plants',
        'animals'   => 'collections/animals',
        'foods'     => 'collections/foods',
        'festivals' => 'collections/festivals',
    ][$section] ?? 'archive/' . $section;
}

/**
 * Whether the page being rendered may carry ads. Set by Controller::render() from the
 * page's data: only real content pages qualify — a 200 response, not noindex (thin,
 * unverified or duplicate pages are noindex), and not an account, form or admin page.
 * Ads on low-value or no-content pages are what AdSense rejects sites for.
 */
function ads_eligible(): bool
{
    return !empty($GLOBALS['_ads_page_eligible']);
}

/** Auto ads loader script in <head>. */
function ads_script_on(): bool
{
    return ADSENSE_SCRIPT_ENABLED && ads_eligible();
}

/** Manual in-page ad units (only after the site is approved; see ADSENSE_ENABLED). */
function ads_on(): bool
{
    return ADSENSE_ENABLED && ads_eligible();
}

function asset(string $path): string
{
    $filePath = BASE_PATH . '/assets/' . ltrim($path, '/');
    $version  = file_exists($filePath) ? filemtime($filePath) : time();
    return ASSETS_URL . '/' . ltrim($path, '/') . '?v=' . $version;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function has_role(string $role): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }

    $userLevel = USER_ROLES[$user['role']]['level'] ?? 0;
    $requiredLevel = USER_ROLES[$role]['level'] ?? PHP_INT_MAX;

    return $userLevel >= $requiredLevel;
}

function is_admin(): bool
{
    return has_role('admin');
}

function is_moderator(): bool
{
    return has_role('moderator');
}
