<?php
require_once BASE_PATH . '/models/MarketingFacebookSetting.php';
require_once BASE_PATH . '/models/MarketingSchedule.php';
require_once BASE_PATH . '/models/MarketingPublishAttempt.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/models/MarketingImage.php';
require_once BASE_PATH . '/services/FacebookGraphClient.php';
require_once BASE_PATH . '/services/InstagramGraphClient.php';
require_once BASE_PATH . '/services/SocialPublisher.php';

class AdminMarketingFacebookController extends Controller
{
    private MarketingFacebookSetting $settings;
    private MarketingSchedule $schedules;
    private MarketingPublishAttempt $attempts;
    private MarketingPost $posts;

    public function __construct()
    {
        parent::__construct();
        $this->settings  = new MarketingFacebookSetting();
        $this->schedules = new MarketingSchedule();
        $this->attempts  = new MarketingPublishAttempt();
        $this->posts     = new MarketingPost();
    }

    public function settings(): void
    {
        $this->requireAdmin();

        $current = $this->settings->get();
        // Never send the decrypted secret/token to the browser — the form
        // shows a masked placeholder and only overwrites on new input.
        $current['has_app_secret'] = !empty($current['app_secret_encrypted']);
        $current['has_page_token'] = !empty($current['page_access_token_encrypted']);

        $this->render('admin/marketing/facebook/settings', [
            'title'       => 'Facebook Settings',
            'currentPage' => 'marketing_settings',
            'settings'    => $current,
        ], 'admin');
    }

    public function saveSettings(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $data = [
            'app_id' => trim($this->post('app_id', '')) ?: null,
            'page_id' => trim($this->post('page_id', '')) ?: null,
            'instagram_business_account_id' => trim($this->post('instagram_business_account_id', '')) ?: null,
            'updated_by' => $this->user['id'],
        ];

        $appSecret = trim($this->post('app_secret', ''));
        if ($appSecret !== '') {
            $data['app_secret_encrypted'] = Security::encrypt($appSecret);
        }

        $pageToken = trim($this->post('page_access_token', ''));
        if ($pageToken !== '') {
            $data['page_access_token_encrypted'] = Security::encrypt($pageToken);
            // Any credential change invalidates the previous connections —
            // both Facebook and Instagram publish through this same token —
            // require an explicit re-test rather than assuming either still works.
            $data['connection_status'] = 'disconnected';
            $data['instagram_connection_status'] = 'disconnected';
        } elseif ($data['instagram_business_account_id'] !== ($this->settings->get()['instagram_business_account_id'] ?? null)) {
            // The IG account id changed but the token didn't — only the
            // Instagram side needs re-verifying.
            $data['instagram_connection_status'] = 'disconnected';
        }

        $this->settings->save($data);
        $this->flash('Settings saved. Click "Test Connection" to verify.', 'success');
        $this->redirect(url('admin/marketing/facebook/settings'));
    }

    public function testConnection(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $client = new FacebookGraphClient();
        $result = $client->testConnection();

        $this->flash($result['message'], $result['success'] ? 'success' : 'error');
        $this->redirect(url('admin/marketing/facebook/settings'));
    }

    public function testInstagramConnection(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $client = new InstagramGraphClient();
        $result = $client->testConnection();

        $this->flash($result['message'], $result['success'] ? 'success' : 'error');
        $this->redirect(url('admin/marketing/facebook/settings'));
    }

    public function postPreview(string $postId): void
    {
        $this->requireAdmin();

        $post = $this->posts->find((int) $postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/generator'));
            return;
        }

        $captions = new MarketingPostCaption();
        $fbCaption = $captions->getForPost((int) $postId)['facebook'] ?? null;

        $this->render('admin/marketing/facebook/preview', [
            'title'       => 'Facebook Preview: ' . $post['headline'],
            'currentPage' => 'marketing_settings',
            'post'        => $post,
            'fbCaption'   => $fbCaption,
        ], 'admin');
    }

    public function queue(): void
    {
        $this->requireAdmin();

        $pending = $this->schedules->getAll(['status' => 'pending'], 50, 0);
        $due = $this->schedules->getAll(['status' => 'due'], 50, 0);
        $failed = $this->schedules->getAll(['status' => 'failed'], 50, 0);
        $published = $this->schedules->getAll(['status' => 'published'], 20, 0, 's.published_at', 'DESC');

        // Keyed by post_id so the WhatsApp share button can pull the
        // already-generated WhatsApp caption for each published post
        // instead of falling back to just the headline.
        $captions = new MarketingPostCaption();
        $whatsappCaptions = [];
        foreach ($published as $s) {
            $postId = (int) $s['post_id'];
            if (!isset($whatsappCaptions[$postId])) {
                $whatsappCaptions[$postId] = $captions->getForPost($postId)['whatsapp'] ?? null;
            }
        }

        // Keyed by schedule id — the branded image (if any) each published
        // post went out with on Facebook, so it can be attached client-side
        // when sharing to WhatsApp (wa.me links have no way to carry media).
        $images = new MarketingImage();
        $imageUrls = [];
        foreach ($published as $s) {
            if (!empty($s['image_id'])) {
                $image = $images->find((int) $s['image_id']);
                if ($image) {
                    $imageUrls[(int) $s['id']] = UPLOADS_URL . '/' . $image['file_path'];
                }
            }
        }

        $this->render('admin/marketing/facebook/queue', [
            'title'             => 'Publishing Queue',
            'currentPage'       => 'marketing_queue',
            'pending'           => $pending,
            'due'               => $due,
            'failed'            => $failed,
            'published'         => $published,
            'whatsappCaptions'  => $whatsappCaptions,
            'imageUrls'         => $imageUrls,
            'connected'         => $this->settings->isConnected(),
            'igConnected'       => $this->settings->isInstagramConnected(),
        ], 'admin');
    }

    public function retry(string $scheduleId): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $schedule = $this->schedules->find((int) $scheduleId);
        if (!$schedule) {
            $this->flash('Schedule not found.', 'error');
            $this->redirect(url('admin/marketing/facebook/queue'));
            return;
        }

        $post = $this->posts->find((int) $schedule['post_id']);
        $images = new MarketingImage();
        $image = $schedule['image_id'] ? $images->find((int) $schedule['image_id']) : null;
        $result = SocialPublisher::publish($schedule, $post, $image);

        $this->attempts->logAttempt((int) $scheduleId, [
            'http_status' => $result['http_status'] ?? null,
            'response_body' => $result['response_body'] ?? null,
            'error_message' => $result['error'] ?? null,
            'success' => $result['success'] ? 1 : 0,
        ]);

        if ($result['success']) {
            $this->schedules->markPublished((int) $scheduleId, $result['permalink'] ?? null);
            $this->flash('Published successfully.', 'success');
        } else {
            $this->schedules->markFailed((int) $scheduleId, $result['error'] ?? 'Unknown error');
            $this->flash('Publish failed: ' . ($result['error'] ?? 'Unknown error'), 'error');
        }

        $this->redirect(url('admin/marketing/facebook/queue'));
    }
}
