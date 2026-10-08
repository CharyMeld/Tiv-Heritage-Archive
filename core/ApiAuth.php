<?php
/**
 * API Authentication & Bot/Scraper Protection
 */

if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

class ApiAuth
{
    // User-agent substrings that identify headless scrapers / automation tools
    private static array $scraperAgents = [
        'curl/', 'wget/', 'python-requests', 'python-urllib',
        'scrapy', 'httpie', 'go-http-client', 'java/',
        'libwww-perl', 'lwp-trivial', 'pycurl', 'mechanize',
        'okhttp', 'node-fetch', 'got/', 'undici', 'axios/',
        'aiohttp', 'httpx/', 'urllib/', 'http.rb', 'rest-client',
        'postmanruntime', 'insomnia/', 'httrack', 'webcopier',
        'offline explorer', 'teleport', 'webzip', 'winhttrack',
        'harvester', 'extractor', 'mass downloader',
        'archive.org_bot', 'ia_archiver',
    ];

    // Well-known crawlers that are never blocked or rate-limited. Google runs separate
    // crawlers for AdSense review/targeting (Mediapartners-Google), ad landing-page
    // checks (AdsBot-Google, incl. -Mobile), Search Console's live test
    // (Google-InspectionTool), site verification and others; before this list named them
    // all, only Googlebot was exempt and the AdSense crawler shared the 60/min limit.
    private static array $allowedBots = [
        'googlebot', 'mediapartners-google', 'adsbot-google', 'google-inspectiontool',
        'googleother', 'storebot-google', 'google-site-verification', 'google-read-aloud',
        'feedfetcher-google', 'apis-google', 'google-safety', 'duplexweb-google',
        'google-adwords', 'googleproducer',
        'bingbot', 'adidxbot', 'slurp', 'duckduckbot',
        'baiduspider', 'yandexbot', 'applebot', 'facebookexternalhit',
        'twitterbot', 'linkedinbot', 'whatsapp',
    ];

    // ----------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------

    /**
     * Require a valid API key for programmatic/API endpoints.
     * Sends a JSON error and exits if the key is missing or invalid.
     * Returns the key record on success.
     */
    public static function requireApiKey(): array
    {
        $key = self::extractKey();

        if (!$key) {
            self::denyApi(401, 'API key required. Request one at ' . url('api/request-key'));
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM api_keys WHERE api_key = ? AND status = 'active'"
        );
        $stmt->execute([$key]);
        $record = $stmt->fetch();

        if (!$record) {
            self::denyApi(403, 'Invalid or revoked API key.');
        }

        self::enforceDaily($record);

        $db->prepare(
            "UPDATE api_keys
             SET requests_today = requests_today + 1,
                 requests_total = requests_total + 1,
                 last_used_at   = NOW()
             WHERE id = ?"
        )->execute([$record['id']]);

        return $record;
    }

    /**
     * Protect HTML content pages from bots and scrapers.
     * Call this at the start of any controller that serves archive data.
     */
    public static function protectPage(): void
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        // Block requests with no user-agent (always automated)
        if ($ua === '') {
            self::denyHtml();
        }

        // Never block trusted search-engine crawlers
        foreach (self::$allowedBots as $bot) {
            if (strpos($ua, $bot) !== false) {
                return;
            }
        }

        // Block known scraper signatures
        foreach (self::$scraperAgents as $sig) {
            if (strpos($ua, $sig) !== false) {
                self::denyHtml();
            }
        }

        // IP rate limit: max 60 page requests per minute
        self::enforceIpRateLimit();
    }

    /**
     * Generate a new unique API key prefixed with "tiv_".
     */
    public static function generateKey(): string
    {
        return 'tiv_' . bin2hex(random_bytes(22));
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private static function extractKey(): ?string
    {
        // Accept X-API-Key header, Authorization: Bearer <key>, or ?api_key= param
        $key = $_SERVER['HTTP_X_API_KEY']
            ?? $_GET['api_key']
            ?? null;

        if (!$key && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (strpos($auth, 'Bearer ') === 0) {
                $key = substr($auth, 7);
            }
        }

        return $key ? trim($key) : null;
    }

    private static function enforceDaily(array $record): void
    {
        $today = date('Y-m-d');
        $db    = Database::getInstance();

        // Reset counter when the calendar day rolls over
        if ($record['last_reset_at'] !== $today) {
            $db->prepare(
                "UPDATE api_keys
                 SET requests_today = 0, last_reset_at = ?
                 WHERE id = ?"
            )->execute([$today, $record['id']]);
            return; // Fresh day — always allow
        }

        if ($record['requests_today'] >= $record['daily_limit']) {
            self::denyApi(
                429,
                'Daily limit of ' . number_format($record['daily_limit']) .
                ' requests exceeded. Counter resets at midnight.'
            );
        }
    }

    private static function enforceIpRateLimit(): void
    {
        $ip       = Security::getClientIP();
        $cacheKey = 'rl_' . md5($ip);
        $now      = time();

        $data = Cache::get($cacheKey) ?? ['count' => 0, 'since' => $now];

        // Slide the window every 60 seconds
        if ($now - $data['since'] > 60) {
            $data = ['count' => 1, 'since' => $now];
        } else {
            $data['count']++;
        }

        Cache::set($cacheKey, $data, 90);

        if ($data['count'] > 60) {
            self::denyHtml();
        }
    }

    private static function denyApi(int $code, string $message): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error'   => $message,
            'docs'    => url('api/request-key'),
        ]);
        exit;
    }

    private static function denyHtml(): never
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        $link = url('api/request-key');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Access Denied</title>
<style>
  body{margin:0;background:#f4f4f5;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif}
  .box{background:#fff;border-radius:10px;padding:3rem 2.5rem;text-align:center;max-width:440px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
  h1{color:#c0392b;margin:0 0 .75rem}
  p{color:#555;line-height:1.6;margin:.5rem 0}
  a{color:#2980b9;font-weight:600}
</style>
</head>
<body>
<div class="box">
  <h1>Access Denied</h1>
  <p>Automated access to this site is not permitted.</p>
  <p>For programmatic access, please <a href="{$link}">request an API key</a>.</p>
</div>
</body>
</html>
HTML;
        exit;
    }
}
