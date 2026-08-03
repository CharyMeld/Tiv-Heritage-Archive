<?php
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/services/SocialCaptionFormatter.php';

class AdminMarketingSocialController extends Controller
{
    private MarketingPost $posts;
    private MarketingPostCaption $captions;

    public function __construct()
    {
        parent::__construct();
        $this->posts = new MarketingPost();
        $this->captions = new MarketingPostCaption();
    }

    public function index(): void
    {
        $this->requireAdmin();

        // Any post that's been through review is eligible for social captions.
        $eligible = array_merge(
            $this->posts->getAll(['status' => 'approved'], 50, 0),
            $this->posts->getAll(['status' => 'scheduled'], 50, 0),
            $this->posts->getAll(['status' => 'published'], 50, 0)
        );

        $this->render('admin/marketing/social/index', [
            'title'       => 'Social Media Captions',
            'currentPage' => 'marketing_social',
            'posts'       => $eligible,
        ], 'admin');
    }

    public function view(string $postId): void
    {
        $this->requireAdmin();

        $post = $this->posts->find((int) $postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/social'));
            return;
        }

        $this->render('admin/marketing/social/view', [
            'title'       => 'Captions: ' . $post['headline'],
            'currentPage' => 'marketing_social',
            'post'        => $post,
            'platforms'   => MarketingPostCaption::PLATFORMS,
            'captions'    => $this->captions->getForPost((int) $postId),
        ], 'admin');
    }

    public function generateCaption(string $postId): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $post = $this->posts->find((int) $postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/social'));
            return;
        }

        $platform = $this->post('platform', '');

        try {
            SocialCaptionFormatter::generateForPlatform($post, $platform, null, $this->user['id']);
            $this->flash(ucfirst($platform) . ' caption generated.', 'success');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), 'error');
        }

        $this->redirect(url('admin/marketing/social/' . $postId));
    }

    public function regenerateCaption(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $caption = $this->captions->find((int) $id);
        if (!$caption) {
            $this->flash('Caption not found.', 'error');
            $this->back();
            return;
        }

        $post = $this->posts->find((int) $caption['post_id']);

        try {
            SocialCaptionFormatter::generateForPlatform($post, $caption['platform'], null, $this->user['id']);
            $this->flash('Caption regenerated.', 'success');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), 'error');
        }

        $this->redirect(url('admin/marketing/social/' . $caption['post_id']));
    }

    public function updateCaption(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $caption = $this->captions->find((int) $id);
        if (!$caption) {
            $this->flash('Caption not found.', 'error');
            $this->back();
            return;
        }

        $text = trim($this->post('caption_text', ''));
        $this->captions->update((int) $id, [
            'caption_text' => $text,
            'hashtags' => trim($this->post('hashtags', '')),
            'char_count' => mb_strlen($text),
        ]);

        $this->flash('Caption updated.', 'success');
        $this->redirect(url('admin/marketing/social/' . $caption['post_id']));
    }
}
