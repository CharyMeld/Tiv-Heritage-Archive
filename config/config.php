<?php
/**
 * Tiv Culture Archive - Main Configuration
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// Auto-detect environment: localhost = development, everything else = production.
// CLI has no HTTP_HOST — fall back to the deploy path so scripts run via SSH on the
// production server (cron jobs, one-off admin scripts) are correctly detected as
// production instead of silently defaulting to "localhost".
if (isset($_SERVER['HTTP_HOST'])) {
    $_env_host = $_SERVER['HTTP_HOST'];
} else {
    $_env_host = (BASE_PATH === '/var/www/tiv') ? 'tivheritage.com' : 'localhost';
}
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

// Google Search Console site-verification (HTML tag method). Paste the code
// Google gives you when you add the tivheritage.com property.
define('GSC_VERIFICATION_CODE', 'yHJzLs4u52K7JIF9Q-Uu35rTf7wEGXE0nlhBp16BYRo');

// Content protection (disables right-click/copy/print on public pages):
// off for now, per request. Flip to true to re-activate.
define('CONTENT_PROTECTION_ENABLED', false);

// Auto-detect site URL based on environment
if (isset($_SERVER['HTTP_HOST'])) {
    $_proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $_host  = $_SERVER['HTTP_HOST'];
} else {
    // CLI (no HTTP_HOST) — see ENVIRONMENT detection above for the same reasoning.
    $_proto = 'https';
    $_host  = (BASE_PATH === '/var/www/tiv') ? 'www.tivheritage.com' : 'localhost';
}
if (strpos($_host, 'localhost') !== false || $_host === '127.0.0.1') {
    $_sub = '/Tiv-Heritage-Archive'; // local LAMPP: localhost/Tiv-Heritage-Archive
} else {
    $_sub = ''; // Production domain: www.tivheritage.com is at root
}
define('SITE_URL', $_proto . '://' . $_host . $_sub);
unset($_proto, $_host, $_sub);

define('SITE_EMAIL', 'contact@tivheritage.com');

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

// ============================================
// AI Content Marketing Engine — local Ollama only, no external AI API
// ============================================
define('OLLAMA_ENABLED',  true);
define('OLLAMA_ENDPOINT', 'http://127.0.0.1:11434');
define('OLLAMA_MODEL',    'qwen2.5:7b-instruct');
define('OLLAMA_TIMEOUT',  240); // seconds — cold model load alone measured ~122s on the production VPS; warm calls are ~2-3s
define('MARKETING_CRON_LOG_PATH', BASE_PATH . '/storage/logs/marketing-cron.log'); // read by the Activity Log admin page

define('WHATSAPP_CHANNEL_URL', 'https://whatsapp.com/channel/0029Vb8LKTP6buMQyVH18v09');

// Key used by Security::encrypt()/decrypt() for at-rest secrets (Facebook
// app secret / page access token). Must be identical across every
// environment that needs to decrypt previously-stored values.
define('APP_ENCRYPTION_KEY', '');

// Maps every content type the Marketing module can generate from to its
// model class, table, and the fields used to build AI prompts. Deliberately
// separate from CONTENT_CATEGORIES above (which is missing several of these
// categories and may have other dependents) rather than extending it.
define('MARKETING_SOURCE_TYPES', [
    'word'              => ['label' => 'Word of the Day',   'model' => 'DailyWord',       'title_field' => 'tiv_word',    'text_fields' => ['english_meaning', 'alternate_meaning'], 'has_views' => false, 'random_where' => 'is_active = 1'],
    'proverb'           => ['label' => 'Proverb',            'model' => 'TivProverb',      'title_field' => 'tiv_text',    'text_fields' => ['english_translation', 'deeper_meaning'], 'has_views' => false, 'random_where' => ''],
    'name'              => ['label' => 'Name',               'model' => 'TivName',         'title_field' => 'tiv_name',    'text_fields' => ['english_meaning', 'origin_story'], 'has_views' => false, 'random_where' => ''],
    'history'           => ['label' => 'Timeline Event',     'model' => 'TimelineEvent',   'title_field' => 'title',       'text_fields' => ['short_summary', 'description'], 'has_views' => true, 'random_where' => ''],
    'historical_figure' => ['label' => 'Historical Figure',  'model' => 'HistoricalFigure','title_field' => 'english_name','text_fields' => ['short_summary', 'biography'], 'has_views' => true, 'random_where' => ''],
    'festival'          => ['label' => 'Festival',           'model' => 'TivFestival',     'title_field' => 'tiv_name',    'text_fields' => ['description', 'significance'], 'has_views' => false, 'random_where' => ''],
    'food'              => ['label' => 'Traditional Food',   'model' => 'TivFood',         'title_field' => 'tiv_name',    'text_fields' => ['description'], 'has_views' => false, 'random_where' => ''],
    'plant'             => ['label' => 'Plant',              'model' => 'TivPlant',        'title_field' => 'tiv_name',    'text_fields' => ['description'], 'has_views' => false, 'random_where' => ''],
    'animal'            => ['label' => 'Animal',             'model' => 'TivAnimal',       'title_field' => 'name',        'text_fields' => ['description'], 'has_views' => true, 'view_column' => 'views', 'random_where' => ''],
    'grammar'           => ['label' => 'Grammar Rule',       'model' => 'TivGrammarRule',  'title_field' => 'title',       'text_fields' => ['summary', 'explanation'], 'has_views' => false, 'random_where' => ''],
    'lesson'            => ['label' => 'Learning Video',     'model' => 'LearningVideo',   'title_field' => 'title',       'text_fields' => ['description'], 'has_views' => true, 'random_where' => ''],
    'reference'         => ['label' => 'Source',             'model' => 'Source',          'title_field' => 'title',       'text_fields' => ['notes'], 'has_views' => false, 'random_where' => ''],
]);

// Extra "follow us" channels offered alongside the email form in the
// homepage welcome popup (views/partials/welcome-popup.php). Neither
// Facebook nor YouTube exposes an API to subscribe/follow a visitor on
// their behalf (same platform restriction that blocks auto-posting to a
// personal timeline) — these are plain links opened in a new tab, which is
// the only thing either platform allows. Add a new entry here (label, url,
// svg path data) to add another channel; no popup markup changes needed.
define('WELCOME_POPUP_CHANNELS', [
    'facebook' => [
        'label' => 'Follow on Facebook',
        'url'   => 'https://www.facebook.com/profile.php?id=1166693896524477',
        'icon'  => 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 8.16 8.16 0 0 0-1.006-.017c-1.978.02-2.815.847-2.815 2.796v1.192h3.99l-.53 3.667h-3.454v7.98h-.001Z',
    ],
    'youtube' => [
        'label' => 'Subscribe on YouTube',
        'url'   => 'https://www.youtube.com/@TivHeritageArchive?sub_confirmation=1',
        'icon'  => 'M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z',
    ],
]);

// Timezone
date_default_timezone_set('Africa/Lagos');

// Character encoding
mb_internal_encoding('UTF-8');
