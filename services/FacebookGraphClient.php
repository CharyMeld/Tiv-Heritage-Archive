<?php
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/models/MarketingFacebookSetting.php';

/**
 * Thin wrapper around the Meta Graph API for a single Facebook Page.
 *
 * Phase 1 architecture: everything here is fully implemented against the
 * real Graph API, but publishPost()/uploadPhoto() are gated behind
 * isConnected() — which stays false until testConnection() succeeds against
 * a real App ID/Secret/Page Token. The intent is that once real credentials
 * are entered in Settings and "Connect" is clicked, publishing works
 * immediately with no further development needed.
 */
class FacebookGraphClient
{
    private const GRAPH_VERSION = 'v19.0';
    private const GRAPH_BASE = 'https://graph.facebook.com/' . self::GRAPH_VERSION;

    private array $settings;
    private ?string $pageAccessToken;

    public function __construct()
    {
        $model = new MarketingFacebookSetting();
        $this->settings = $model->get();
        $this->pageAccessToken = Security::decrypt($this->settings['page_access_token_encrypted'] ?? null);
    }

    public function isConnected(): bool
    {
        return $this->settings['connection_status'] === 'connected' && !empty($this->pageAccessToken);
    }

    /**
     * The one genuinely live call in Phase 1: verifies the stored Page
     * token actually works against the real Graph API, and updates the
     * stored connection status accordingly.
     */
    public function testConnection(): array
    {
        if (empty($this->pageAccessToken) || empty($this->settings['page_id'])) {
            return ['success' => false, 'message' => 'Enter a Page ID and Page Access Token first.'];
        }

        // Note: a Page's id/name are public and return successfully with
        // *any* valid token (even a User Access Token pasted in by
        // mistake), so this only proves the token+page_id pair is valid —
        // not that the token can actually post. Facebook's "tasks" field
        // (which does reveal CREATE_CONTENT posting rights) is only
        // populated in the /me/accounts listing, not queryable directly on
        // the Page node itself, so it can't be checked here. The first real
        // publish attempt is the actual proof.
        $url = self::GRAPH_BASE . '/' . rawurlencode($this->settings['page_id'])
            . '?fields=id,name&access_token=' . rawurlencode($this->pageAccessToken);

        [$httpCode, $body, $curlError] = $this->request($url, 'GET', null, 8);

        $model = new MarketingFacebookSetting();

        if ($curlError) {
            $model->markError('Network error: ' . $curlError);
            return ['success' => false, 'message' => 'Could not reach Facebook: ' . $curlError];
        }

        $decoded = json_decode($body, true);

        if ($httpCode === 200 && isset($decoded['id'])) {
            $model->markConnected();
            return ['success' => true, 'message' => 'Connected to Page "' . ($decoded['name'] ?? $decoded['id']) . '". (This confirms the token is valid, not that it can post — make sure it\'s the Page Access Token from GET /me/accounts, not a User Access Token.)'];
        }

        $errorMessage = $decoded['error']['message'] ?? "Unexpected response (HTTP {$httpCode}).";
        $model->markError($errorMessage);
        return ['success' => false, 'message' => $errorMessage];
    }

    /**
     * Publish a text post to the connected Page. Fully implemented against
     * the real endpoint, but returns immediately with a clear error if not
     * connected — this is the dead-until-connected code path.
     */
    public function publishPost(array $data): array
    {
        if (!$this->isConnected()) {
            return ['success' => false, 'error' => 'Facebook is not connected.'];
        }

        $url = self::GRAPH_BASE . '/' . rawurlencode($this->settings['page_id']) . '/feed';
        $payload = [
            'message' => $data['message'] ?? '',
            'link' => $data['link'] ?? null,
            'access_token' => $this->pageAccessToken,
        ];
        if (!empty($data['scheduled_publish_time'])) {
            $payload['published'] = false;
            $payload['scheduled_publish_time'] = $data['scheduled_publish_time'];
        }

        [$httpCode, $body] = $this->request($url, 'POST', array_filter($payload, fn($v) => $v !== null));
        $decoded = json_decode($body, true);

        if ($httpCode === 200 && isset($decoded['id'])) {
            return [
                'success' => true,
                'post_id' => $decoded['id'],
                'permalink' => $this->permalinkFor($decoded['id']),
                'http_status' => $httpCode,
                'response_body' => $body,
            ];
        }

        return [
            'success' => false,
            'error' => $decoded['error']['message'] ?? "Unexpected response (HTTP {$httpCode}).",
            'http_status' => $httpCode,
            'response_body' => $body,
        ];
    }

    /**
     * Publish a photo post to the connected Page. Same gated pattern as
     * publishPost().
     */
    public function uploadPhoto(array $data): array
    {
        if (!$this->isConnected()) {
            return ['success' => false, 'error' => 'Facebook is not connected.'];
        }

        if (empty($data['image_url'])) {
            return ['success' => false, 'error' => 'No image URL provided.'];
        }

        $url = self::GRAPH_BASE . '/' . rawurlencode($this->settings['page_id']) . '/photos';
        $payload = array_filter([
            'url' => $data['image_url'],
            'caption' => $data['caption'] ?? '',
            'access_token' => $this->pageAccessToken,
        ]);

        [$httpCode, $body] = $this->request($url, 'POST', $payload);
        $decoded = json_decode($body, true);

        if ($httpCode === 200 && isset($decoded['id'])) {
            return [
                'success' => true,
                'post_id' => $decoded['id'],
                'permalink' => $this->permalinkFor($decoded['id']),
                'http_status' => $httpCode,
                'response_body' => $body,
            ];
        }

        return [
            'success' => false,
            'error' => $decoded['error']['message'] ?? "Unexpected response (HTTP {$httpCode}).",
            'http_status' => $httpCode,
            'response_body' => $body,
        ];
    }

    /**
     * Resolves the real, human-viewable permalink for a just-created post so
     * the admin UI can offer a "Share to my profile" link (Facebook's own
     * share dialog, opened with this URL — the closest thing to cross-
     * posting to a personal timeline, since the Graph API itself cannot post
     * to a personal profile on anyone's behalf). Tries the "link" field
     * first (confirmed to return the real photo-viewer permalink for photo
     * posts); falls back to constructing the standard /{page}/posts/{id}
     * pattern if that field is empty (e.g. for plain text feed posts).
     */
    private function permalinkFor(string $postId): ?string
    {
        $url = self::GRAPH_BASE . '/' . rawurlencode($postId) . '?fields=link&access_token=' . rawurlencode($this->pageAccessToken);
        [$httpCode, $body] = $this->request($url, 'GET', null, 8);
        $decoded = json_decode($body, true);

        if ($httpCode === 200 && !empty($decoded['link'])) {
            return $decoded['link'];
        }

        $postPart = str_contains($postId, '_') ? substr($postId, strpos($postId, '_') + 1) : $postId;
        return 'https://www.facebook.com/' . rawurlencode($this->settings['page_id']) . '/posts/' . rawurlencode($postPart);
    }

    /**
     * @return array{0:int,1:string,2:?string} [httpCode, body, curlErrorOrNull]
     */
    private function request(string $url, string $method, ?array $payload = null, int $timeout = 15): array
    {
        $ch = curl_init();
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($payload ?? []);
        }
        curl_setopt_array($ch, $opts);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = $errno !== 0 ? curl_error($ch) : null;
        curl_close($ch);

        return [$httpCode, $body === false ? '' : $body, $error];
    }
}
