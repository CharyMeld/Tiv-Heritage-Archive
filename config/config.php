<?php
/**
 * Tiv Culture Archive - Main Configuration
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// Auto-detect environment: localhost = development, everything else = production
$_env_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('ENVIRONMENT', (strpos($_env_host, 'localhost') !== false || $_env_host === '127.0.0.1') ? 'development' : 'production');
unset($_env_host);

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Site Configuration
define('SITE_NAME', 'Tiv Culture Archive');
define('SITE_TAGLINE', 'Preserving Language, History & Identity');

// AdSense: off until Google approves the account. Flip to true once approved.
define('ADSENSE_ENABLED', false);

// Content protection (disables right-click/copy/print on public pages):
// off for now, per request. Flip to true to re-activate.
define('CONTENT_PROTECTION_ENABLED', false);

// Auto-detect site URL based on environment
$_proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($_host, 'localhost') !== false || $_host === '127.0.0.1') {
    $_sub = '/Tiv-Heritage-Archive'; // local LAMPP: localhost/Tiv-Heritage-Archive
} else {
    $_sub = ''; // Production domain: www.tivheritage.com is at root
}
define('SITE_URL', $_proto . '://' . $_host . $_sub);
unset($_proto, $_host, $_sub);

define('SITE_EMAIL', 'contact@tivarchive.com');

// Paths
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Session Configuration
define('SESSION_NAME', 'tiv_archive_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Pagination
define('ITEMS_PER_PAGE', 12);

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Audio Upload Configuration
define('MAX_AUDIO_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_AUDIO_TYPES', ['audio/webm', 'audio/mp3', 'audio/mpeg', 'audio/ogg', 'audio/wav']);
define('ALLOWED_AUDIO_EXTENSIONS', ['webm', 'mp3', 'ogg', 'wav']);
define('AUDIO_UPLOADS_PATH', BASE_PATH . '/uploads/audio');
define('AUDIO_UPLOADS_URL', SITE_URL . '/uploads/audio');

// Security Configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 30); // minutes
define('IP_BLOCK_THRESHOLD', 10); // auto-block after this many failed attempts

// Categories
define('CONTENT_CATEGORIES', [
    'name' => ['label' => 'Names', 'table' => 'tiv_names', 'icon' => 'user'],
    'proverb' => ['label' => 'Proverbs', 'table' => 'tiv_proverbs', 'icon' => 'quote'],
    'plant' => ['label' => 'Plants', 'table' => 'tiv_plants', 'icon' => 'leaf'],
    'festival' => ['label' => 'Festivals', 'table' => 'tiv_festivals', 'icon' => 'calendar'],
    'food' => ['label' => 'Foods', 'table' => 'tiv_foods', 'icon' => 'utensils'],
    'word' => ['label' => 'Words', 'table' => 'daily_words', 'icon' => 'book'],
    'animal' => ['label' => 'Animals', 'table' => 'tiv_animals', 'icon' => 'paw']
]);

// User Roles
define('USER_ROLES', [
    'user' => ['label' => 'User', 'level' => 1],
    'contributor' => ['label' => 'Contributor', 'level' => 2],
    'moderator' => ['label' => 'Moderator', 'level' => 3],
    'admin' => ['label' => 'Administrator', 'level' => 4]
]);

// ============================================
// External AI APIs — disabled (self-hosted)
// Translation and assistant now use Archive Intelligence (local RAG).
// These constants are kept for backward compatibility only.
// ============================================
define('NLLB_ENABLED',   false);
define('NLLB_API_KEY',   '');
define('NLLB_ENDPOINT',  '');
define('NLLB_TIMEOUT',   10);

define('CHARYMELD_ENABLED',    true);  // always on — no API key needed
define('ANTHROPIC_API_KEY',    '');    // unused — local RAG handles responses
define('CHARYMELD_MODEL',      '');
define('CHARYMELD_MAX_TOKENS', 0);
define('CHARYMELD_TIMEOUT',    30);

// ============================================
// Archive Intelligence (local RAG engine)
// ============================================
define('AI_SEARCH_LIMIT',      5);    // max results per table search
define('AI_RESPONSE_MAX_ITEMS', 4);   // max items shown per response
define('AI_INDEX_AUTO_REBUILD', false); // set true to rebuild index on every admin save (slow on large DBs)

// Timezone
date_default_timezone_set('Africa/Lagos');

// Character encoding
mb_internal_encoding('UTF-8');
