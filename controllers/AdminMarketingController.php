<?php
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';
require_once BASE_PATH . '/models/MarketingUtmLink.php';
require_once BASE_PATH . '/models/MarketingLinkClick.php';
require_once BASE_PATH . '/models/MarketingImage.php';
require_once BASE_PATH . '/services/ContentGeneratorService.php';
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/SocialGraphicGenerator.php';
require_once BASE_PATH . '/services/MarketingLinkBuilder.php';

class AdminMarketingController extends Controller
{
    private MarketingPost $posts;
    private MarketingPromptTemplate $templates;
    private MarketingGenerationLog $logs;
    private MarketingImage $images;

    public function __construct()
    {
        parent::__construct();
        $this->posts     = new MarketingPost();
        $this->templates = new MarketingPromptTemplate();
        $this->logs      = new MarketingGenerationLog();
        $this->images    = new MarketingImage();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(): void
    {
        $this->requireAdmin();

        $statusCounts = $this->posts->countByStatus();
        $recentPosts = $this->posts->getAll([], 8, 0);
        $generationRate = $this->logs->successRate(7);

        $links = new MarketingUtmLink();
        $clicks = new MarketingLinkClick();
        $trafficByPlatform = $links->trafficByPlatform();

        $today = date('Y-m-d');
        $todaysPosts = 0;
        foreach ($recentPosts as $p) {
            if (substr($p['created_at'], 0, 10) === $today) $todaysPosts++;
        }

        $this->render('admin/marketing/dashboard/index', [
            'title'             => 'Marketing Dashboard',
            'currentPage'       => 'marketing_dashboard',
            'statusCounts'      => $statusCounts,
            'recentPosts'       => $recentPosts,
            'generationRate'    => $generationRate,
            'trafficByPlatform' => $trafficByPlatform,
            'totalClicks'       => $clicks->totalAll(),
            'todaysPosts'       => $todaysPosts,
            'ollamaAvailable'   => OllamaClient::isAvailable(),
        ], 'admin');
    }

    // ── Activity Log ─────────────────────────────────────────────────────────

    /**
     * Live tail of storage/logs/marketing-cron.log — the exact step-by-step
     * output of bin/marketing-autopilot.php and bin/marketing-process-schedule.php
     * as they run on cron, so an admin can watch generation/caption/image/publish
     * steps happen without SSH access. Auto-refreshes client-side.
     */
    public function activity(): void
    {
        $this->requireAdmin();

        $lines = [];
        $unreadable = false;

        if (is_readable(MARKETING_CRON_LOG_PATH)) {
            // Tail the last ~4000 lines rather than reading the whole file —
            // this log grows forever and is never rotated.
            $all = file(MARKETING_CRON_LOG_PATH, FILE_IGNORE_NEW_LINES);
            $tail = array_slice($all, -4000);

            foreach (array_reverse($tail) as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s*(.*)$/', $line, $m)) {
                    $lines[] = ['time' => $m[1], 'text' => $m[2]];
                } elseif (trim($line) !== '') {
                    // Unparsed line (e.g. a PHP warning/stack trace) — still
                    // show it, just without a timestamp column.
                    $lines[] = ['time' => null, 'text' => $line];
                }
            }
        } else {
            $unreadable = true;
        }

        $this->render('admin/marketing/activity/index', [
            'title'       => 'Activity Log',
            'currentPage' => 'marketing_activity',
            'lines'       => array_slice($lines, 0, 500),
            'unreadable'  => $unreadable,
        ], 'admin');
    }

    // ── All Posts ─────────────────────────────────────────────────────────────

    public function posts(): void
    {
        $this->requireAdmin();

        $filters = array_filter([
            'status'      => $this->get('status', ''),
            'source_type' => $this->get('category', ''),
        ]);

        $pagination = $this->paginate($this->posts->countAll($filters));

        $this->render('admin/marketing/posts/index', [
            'title'       => 'All Generated Content',
            'currentPage' => 'marketing_generator',
            'posts'       => $this->posts->getAll($filters, $pagination['per_page'], $pagination['offset']),
            'pagination'  => $pagination,
            'statuses'    => MarketingPost::STATUSES,
            'categories'  => MarketingPost::SOURCE_TYPES,
            'filters'     => ['status' => $filters['status'] ?? '', 'category' => $filters['source_type'] ?? ''],
        ], 'admin');
    }

    // ── Content Generator ────────────────────────────────────────────────────

    public function generator(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/generator/index', [
            'title'       => 'Content Generator',
            'currentPage' => 'marketing_generator',
            'categories'  => MARKETING_SOURCE_TYPES,
            'templates'   => $this->templates->getAll(),
            'recentPosts' => $this->posts->getAll(['status' => 'draft'], 10, 0),
            'ollamaAvailable' => OllamaClient::isAvailable(),
        ], 'admin');
    }

    public function generate(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $mode = $this->post('mode', 'random');
        $category = $this->post('category') ?: null;
        $itemId = $this->post('item_id') ? (int) $this->post('item_id') : null;
        $templateId = $this->post('template_id') ? (int) $this->post('template_id') : null;

        try {
            $postId = ContentGeneratorService::generate($mode, $category, $itemId, $templateId, $this->user['id']);
            $this->flash('Content generated — review it below before approving.', 'success');
            $this->redirect(url('admin/marketing/posts/' . $postId));
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), 'error');
            $this->redirect(url('admin/marketing/generator'));
        }
    }

    public function preview(string $id): void
    {
        $this->requireAdmin();

        $post = $this->posts->find((int) $id);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/generator'));
            return;
        }

        $sourceLabel = MARKETING_SOURCE_TYPES[$post['source_type']]['label'] ?? $post['source_type'];

        $this->render('admin/marketing/generator/preview', [
            'title'       => 'Review: ' . $post['headline'],
            'currentPage' => 'marketing_generator',
            'post'        => $post,
            'sourceLabel' => $sourceLabel,
        ], 'admin');
    }

    public function approve(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $post = $this->posts->find((int) $id);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/generator'));
            return;
        }

        $this->posts->approve((int) $id, $this->user['id']);
        Security::logActivity($this->user['id'], 'marketing_post_approved', 'marketing_posts', (int) $id);
        $this->flash('Post approved.', 'success');
        $this->redirect(url('admin/marketing/posts/' . $id));
    }

    public function reject(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $reason = $this->post('reason', '');
        $this->posts->reject((int) $id, $this->user['id'], $reason);
        Security::logActivity($this->user['id'], 'marketing_post_rejected', 'marketing_posts', (int) $id);
        $this->flash('Post rejected.', 'success');
        $this->redirect(url('admin/marketing/posts/' . $id));
    }

    public function deletePost(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->posts->delete((int) $id);
        $this->flash('Post deleted.', 'success');
        $this->redirect(url('admin/marketing/generator'));
    }

    // ── Prompt Templates ─────────────────────────────────────────────────────

    public function templates(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/templates/index', [
            'title'       => 'Prompt Templates',
            'currentPage' => 'marketing_templates',
            'items'       => $this->templates->getAll(),
            'categories'  => MarketingPromptTemplate::CATEGORIES,
            'platforms'   => MarketingPromptTemplate::PLATFORMS,
        ], 'admin');
    }

    public function createTemplate(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/templates/create', [
            'title'       => 'New Prompt Template',
            'currentPage' => 'marketing_templates',
            'categories'  => MarketingPromptTemplate::CATEGORIES,
            'platforms'   => MarketingPromptTemplate::PLATFORMS,
        ], 'admin');
    }

    public function storeTemplate(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $name = trim($this->post('name', ''));
        $userPrompt = trim($this->post('user_prompt_template', ''));

        if (!$name || !$userPrompt) {
            $this->storeOldInput();
            $this->flash('Name and prompt text are required.', 'error');
            $this->back();
            return;
        }

        $id = $this->templates->create([
            'name' => $name,
            'category' => $this->post('category', 'general'),
            'platform' => $this->post('platform', 'any'),
            'system_prompt' => trim($this->post('system_prompt', '')) ?: null,
            'user_prompt_template' => $userPrompt,
            'is_default' => $this->post('is_default') ? 1 : 0,
            'is_active' => 1,
            'created_by' => $this->user['id'],
        ]);

        if ($this->post('is_default')) {
            $this->templates->setDefault($id);
        }

        $this->flash('Template created.', 'success');
        $this->redirect(url('admin/marketing/templates'));
    }

    public function editTemplate(string $id): void
    {
        $this->requireAdmin();

        $template = $this->templates->find((int) $id);
        if (!$template) {
            $this->flash('Template not found.', 'error');
            $this->redirect(url('admin/marketing/templates'));
            return;
        }

        $this->render('admin/marketing/templates/edit', [
            'title'       => 'Edit Template',
            'currentPage' => 'marketing_templates',
            'item'        => $template,
            'categories'  => MarketingPromptTemplate::CATEGORIES,
            'platforms'   => MarketingPromptTemplate::PLATFORMS,
        ], 'admin');
    }

    public function updateTemplate(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $name = trim($this->post('name', ''));
        $userPrompt = trim($this->post('user_prompt_template', ''));

        if (!$name || !$userPrompt) {
            $this->flash('Name and prompt text are required.', 'error');
            $this->back();
            return;
        }

        $this->templates->update((int) $id, [
            'name' => $name,
            'category' => $this->post('category', 'general'),
            'platform' => $this->post('platform', 'any'),
            'system_prompt' => trim($this->post('system_prompt', '')) ?: null,
            'user_prompt_template' => $userPrompt,
            'is_active' => $this->post('is_active') ? 1 : 0,
        ]);

        if ($this->post('is_default')) {
            $this->templates->setDefault((int) $id);
        }

        $this->flash('Template updated.', 'success');
        $this->redirect(url('admin/marketing/templates'));
    }

    public function deleteTemplate(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->templates->delete((int) $id);
        $this->flash('Template deleted.', 'success');
        $this->redirect(url('admin/marketing/templates'));
    }

    public function setDefaultTemplate(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->templates->setDefault((int) $id);
        $this->flash('Default template updated.', 'success');
        $this->back();
    }

    // ── Image Generator ──────────────────────────────────────────────────────

    public function images(): void
    {
        $this->requireAdmin();

        $eligiblePosts = array_merge(
            $this->posts->getAll(['status' => 'approved'], 50, 0),
            $this->posts->getAll(['status' => 'scheduled'], 50, 0),
            $this->posts->getAll(['status' => 'published'], 50, 0)
        );

        $this->render('admin/marketing/images/index', [
            'title'       => 'Image Generator',
            'currentPage' => 'marketing_images',
            'posts'       => $eligiblePosts,
            'gallery'     => $this->images->getAll(24, 0),
        ], 'admin');
    }

    public function imagePicker(string $postId): void
    {
        $this->requireAdmin();

        $post = $this->posts->find((int) $postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/images'));
            return;
        }

        $this->render('admin/marketing/images/picker', [
            'title'        => 'Generate Image: ' . $post['headline'],
            'currentPage'  => 'marketing_images',
            'post'         => $post,
            'templates'    => MarketingImage::TEMPLATES,
            'orientations' => MarketingImage::ORIENTATIONS,
            'existing'     => $this->images->getForPost((int) $postId),
        ], 'admin');
    }

    public function generateImage(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $postId = (int) $this->post('post_id', 0);
        $templateType = $this->post('template_type', '');
        $orientation = $this->post('orientation', 'square');

        $post = $this->posts->find($postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/images'));
            return;
        }

        $item = $post['source_snapshot'] ? (json_decode($post['source_snapshot'], true) ?: []) : [];
        $item['id'] = $item['id'] ?? $post['source_id'] ?? null;
        // The "quote" template (and the blank-field fallback inside
        // SocialGraphicGenerator) pulls from the generated post's own
        // headline/caption, not the original source item — make both
        // available regardless of which template was picked.
        $item['headline'] = $post['headline'];
        $item['caption'] = $post['caption'];
        if (!isset(MarketingImage::AUTO_TEMPLATE_FOR_SOURCE[$post['source_type']])
            || MarketingImage::AUTO_TEMPLATE_FOR_SOURCE[$post['source_type']] !== $templateType) {
            // A repurposed/generic template was picked for this source
            // type — swap the eyebrow for the real content type name
            // instead of showing a mismatched label like "WORD OF THE DAY"
            // on a Name or Grammar Rule card.
            $item['eyebrow_override'] = strtoupper(MarketingPost::SOURCE_TYPES[$post['source_type']] ?? '');
        }

        $link = MarketingLinkBuilder::linkFor($postId, (string) $post['source_type'], $item, 'facebook', $this->user['id']);
        $ctaUrl = url('go/' . $link['short_code']);

        try {
            $path = SocialGraphicGenerator::generate($templateType, $orientation, $item, $ctaUrl);
            $dims = MarketingImage::ORIENTATIONS[$orientation];

            $this->images->create([
                'post_id' => $postId,
                'template_type' => $templateType,
                'orientation' => $orientation,
                'file_path' => 'marketing_images/' . basename($path),
                'width' => $dims['width'],
                'height' => $dims['height'],
                'created_by' => $this->user['id'],
            ]);

            $this->flash('Image generated.', 'success');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), 'error');
        }

        $this->redirect(url('admin/marketing/posts/' . $postId . '/images'));
    }

    public function deleteImage(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $image = $this->images->find((int) $id);
        if ($image) {
            $filePath = UPLOADS_PATH . '/' . $image['file_path'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            $this->images->delete((int) $id);
        }

        $this->flash('Image deleted.', 'success');
        $this->back();
    }

    // ── Email Campaigns (stub — full subscriber/campaign management is a later phase) ──

    public function emailCampaignsStub(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/campaigns-stub', [
            'title'       => 'Email Campaigns',
            'currentPage' => 'marketing_campaigns',
        ], 'admin');
    }
}
