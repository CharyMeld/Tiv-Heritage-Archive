<?php
require_once BASE_PATH . '/models/MarketingUtmLink.php';
require_once BASE_PATH . '/models/MarketingLinkClick.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

class AdminMarketingAnalyticsController extends Controller
{
    private MarketingUtmLink $links;
    private MarketingLinkClick $clicks;
    private MarketingPost $posts;
    private MarketingGenerationLog $logs;

    public function __construct()
    {
        parent::__construct();
        $this->links  = new MarketingUtmLink();
        $this->clicks = new MarketingLinkClick();
        $this->posts  = new MarketingPost();
        $this->logs   = new MarketingGenerationLog();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/analytics/index', [
            'title'             => 'Analytics',
            'currentPage'       => 'marketing_analytics',
            'statusCounts'      => $this->posts->countByStatus(),
            'trafficByPlatform' => $this->links->trafficByPlatform(),
            'totalClicks'       => $this->clicks->totalAll(),
            'totalLinks'        => $this->links->countAll(),
            'generationRate7'   => $this->logs->successRate(7),
            'generationRate30'  => $this->logs->successRate(30),
            'recentFailures'    => $this->logs->recentFailures(10),
        ], 'admin');
    }

    public function traffic(): void
    {
        $this->requireAdmin();

        $daily = $this->clicks->dailyCounts(30);
        $maxDaily = 0;
        foreach ($daily as $d) {
            $maxDaily = max($maxDaily, (int) $d['clicks']);
        }

        $this->render('admin/marketing/analytics/traffic', [
            'title'             => 'Traffic Reports',
            'currentPage'       => 'marketing_traffic',
            'trafficByPlatform' => $this->links->trafficByPlatform(),
            'dailyCounts'       => $daily,
            'maxDaily'          => $maxDaily ?: 1,
            'totalClicks'       => $this->clicks->totalAll(),
        ], 'admin');
    }

    public function links(): void
    {
        $this->requireAdmin();

        $total = $this->links->countAll();
        $pagination = $this->paginate($total, 25);

        $this->render('admin/marketing/analytics/links', [
            'title'       => 'UTM Links',
            'currentPage' => 'marketing_traffic',
            'items'       => $this->links->getAll($pagination['per_page'], $pagination['offset']),
            'pagination'  => $pagination,
            'platforms'   => MarketingUtmLink::PLATFORMS,
        ], 'admin');
    }

    public function createLink(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/analytics/create-link', [
            'title'       => 'New Tracked Link',
            'currentPage' => 'marketing_traffic',
            'platforms'   => MarketingUtmLink::PLATFORMS,
        ], 'admin');
    }

    public function storeLink(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $destinationUrl = trim($this->post('destination_url', ''));
        if (!$destinationUrl || !filter_var($destinationUrl, FILTER_VALIDATE_URL)) {
            $this->storeOldInput();
            $this->flash('A valid destination URL is required.', 'error');
            $this->back();
            return;
        }

        $code = $this->links->generateUniqueCode();
        $this->links->create([
            'platform' => $this->post('platform', 'other'),
            'destination_url' => $destinationUrl,
            'utm_source' => trim($this->post('utm_source', '')) ?: $this->post('platform', 'other'),
            'utm_medium' => trim($this->post('utm_medium', '')) ?: 'social',
            'utm_campaign' => trim($this->post('utm_campaign', '')) ?: 'general',
            'utm_content' => trim($this->post('utm_content', '')) ?: null,
            'short_code' => $code,
            'created_by' => $this->user['id'],
        ]);

        $this->flash('Tracked link created: ' . url('go/' . $code), 'success');
        $this->redirect(url('admin/marketing/links'));
    }

    public function deleteLink(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->links->delete((int) $id);
        $this->flash('Link deleted.', 'success');
        $this->redirect(url('admin/marketing/links'));
    }
}
