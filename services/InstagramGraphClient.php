<?php
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/models/MarketingFacebookSetting.php';

/**
 * Thin wrapper around the Meta Graph API for a single Instagram Business
 * Account. Reuses the same Page Access Token already entered on the
 * Facebook settings page — Instagram Graph API publishing authenticates
 * through the Facebook Page the Instagram account is linked to, there's no
 * separate Instagram app secret/token. Same gated-until-connected pattern
 * as FacebookGraphClient: publishPhoto() returns a clear error until
 * isConnected() is true, which requires both a saved Instagram Business
 * Account ID and a successful testConnection().
 */
class InstagramGraphClient
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
        return $this->settings['instagram_connection_status'] === 'connected'
            && !empty($this->settings['instagram_business_account_id'])
            && !empty($this->pageAccessToken);
    }

    /**
     * Verifies the stored Instagram Business Account ID is real and the
     * Page Access Token can actually read it (proves the IG account is
     * linked to the connected Page and the token has instagram_basic).
     */
    public function testConnection(): array
    {
        $igUserId = $this->settings['instagram_business_account_id'] ?? '';

        if (empty($this->pageAccessToken)) {
            return ['success' => false, 'message' => 'Save a Facebook Page Access Token first (Instagram publishing reuses it).'];
        }
        if (empty($igUserId)) {
            return ['success' => false, 'message' => 'Enter an Instagram Business Account ID first.'];
        }

        $url = self::GRAPH_BASE . '/' . rawurlencode($igUserId)
            . '?fields=id,username&access_token=' . rawurlencode($this->pageAccessToken);

        [$httpCode, $body, $curlError] = $this->request($url, 'GET', null, 8);

        $model = new MarketingFacebookSetting();

        if ($curlError) {
            $model->markInstagramError('Network error: ' . $curlError);
            return ['success' => false, 'message' => 'Could not reach Instagram: ' . $curlError];
        }

        $decoded = json_decode($body, true);

        if ($httpCode === 200 && isset($decoded['id'])) {
            $model->markInstagramConnected();
            return ['success' => true, 'message' => 'Connected to Instagram account "@' . ($decoded['username'] ?? $decoded['id']) . '".'];
        }

        $errorMessage = $decoded['error']['message'] ?? "Unexpected response (HTTP {$httpCode}).";
        $model->markInstagramError($errorMessage);
        return ['success' => false, 'message' => $errorMessage];
    }

    /**
     * Publishes a single image post. Instagram has no text-only post type,
     * so an image is mandatory (callers must check for this before calling
     * in). Two-step Graph API flow: create a media container, then publish
     * it — the container briefly needs to finish processing server-side
     * before it can be published, so this polls its status_code a few
     * times rather than assuming it's instantly ready.
     */
    public function publishPhoto(array $data): array
    {
        if (!$this->isConnected()) {
            return ['success' => false, 'error' => 'Instagram is not connected.'];
        }

        if (empty($data['image_url'])) {
            return ['success' => false, 'error' => 'No image URL provided — Instagram requires an image for every post.'];
        }

        $igUserId = $this->settings['instagram_business_account_id'];

        // Step 1: create the media container.
        $containerUrl = self::GRAPH_BASE . '/' . rawurlencode($igUserId) . '/media';
        $containerPayload = array_filter([
            'image_url' => $data['image_url'],
            'caption' => $data['caption'] ?? '',
            'access_token' => $this->pageAccessToken,
        ]);

        [$httpCode, $body] = $this->request($containerUrl, 'POST', $containerPayload);
        $decoded = json_decode($body, true);

        if ($httpCode !== 200 || empty($decoded['id'])) {
            return [
                'success' => false,
                'error' => $decoded['error']['message'] ?? "Could not create media container (HTTP {$httpCode}).",
                'http_status' => $httpCode,
                'response_body' => $body,
            ];
        }

        $containerId = $decoded['id'];

        // Step 2: wait for the container to finish processing (usually
        // near-instant for images, but not guaranteed) before publishing.
        if (!$this->waitForContainerReady($containerId)) {
            return [
                'success' => false,
                'error' => 'Media container did not finish processing in time.',
                'http_status' => $httpCode,
                'response_body' => $body,
            ];
        }

        // Step 3: publish the container. Even after status_code reports
        // FINISHED, Meta occasionally isn't quite ready yet and returns
        // error code 9007 ("Media ID is not available" / "The media is not
        // ready to be published. Please wait a moment.") — a known,
        // self-resolving race condition, not a real failure. Retry the
        // publish call itself (not container creation) a few times with
        // backoff before giving up.
        $publishUrl = self::GRAPH_BASE . '/' . rawurlencode($igUserId) . '/media_publish';
        $publishPayload = [
            'creation_id' => $containerId,
            'access_token' => $this->pageAccessToken,
        ];

        for ($attempt = 0; $attempt < 4; $attempt++) {
            [$publishHttpCode, $publishBody] = $this->request($publishUrl, 'POST', $publishPayload);
            $publishDecoded = json_decode($publishBody, true);

            if ($publishHttpCode === 200 && isset($publishDecoded['id'])) {
                return [
                    'success' => true,
                    'post_id' => $publishDecoded['id'],
                    'permalink' => $this->permalinkFor($publishDecoded['id']),
                    'http_status' => $publishHttpCode,
                    'response_body' => $publishBody,
                ];
            }

            $errorCode = $publishDecoded['error']['code'] ?? null;
            if ($errorCode !== 9007 || $attempt === 3) {
                return [
                    'success' => false,
                    'error' => $publishDecoded['error']['message'] ?? "Unexpected response publishing container (HTTP {$publishHttpCode}).",
                    'http_status' => $publishHttpCode,
                    'response_body' => $publishBody,
                ];
            }

            usleep(1500000 * ($attempt + 1)); // 1.5s, 3s, 4.5s backoff
        }
    }

    /**
     * Polls the container's status_code until FINISHED (or a terminal
     * failure/timeout). Images are typically ready within a second or two;
     * this allows up to ~15s total before giving up.
     */
    private function waitForContainerReady(string $containerId): bool
    {
        $url = self::GRAPH_BASE . '/' . rawurlencode($containerId) . '?fields=status_code&access_token=' . rawurlencode($this->pageAccessToken);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            [$httpCode, $body] = $this->request($url, 'GET', null, 8);
            $decoded = json_decode($body, true);
            $status = $decoded['status_code'] ?? null;

            if ($status === 'FINISHED') {
                return true;
            }
            if ($status === 'ERROR' || $status === 'EXPIRED') {
                return false;
            }

            usleep(500000 * ($attempt + 1)); // 0.5s, 1s, 1.5s, ... backoff
        }

        return false;
    }

    /**
     * Resolves the real, human-viewable permalink for a just-published
     * post, mirroring FacebookGraphClient::permalinkFor().
     */
    private function permalinkFor(string $mediaId): ?string
    {
        $url = self::GRAPH_BASE . '/' . rawurlencode($mediaId) . '?fields=permalink&access_token=' . rawurlencode($this->pageAccessToken);
        [$httpCode, $body] = $this->request($url, 'GET', null, 8);
        $decoded = json_decode($body, true);

        if ($httpCode === 200 && !empty($decoded['permalink'])) {
            return $decoded['permalink'];
        }

        return null;
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
