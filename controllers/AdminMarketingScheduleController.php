<?php
require_once BASE_PATH . '/models/MarketingSchedule.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingImage.php';
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/models/MarketingNewsletterIssue.php';
require_once BASE_PATH . '/models/NewsletterSubscriber.php';
require_once BASE_PATH . '/services/NewsletterGeneratorService.php';

class AdminMarketingScheduleController extends Controller
{
    private MarketingSchedule $schedules;
    private MarketingPost $posts;
    private MarketingImage $images;
    private MarketingNewsletterIssue $newsletters;
    private NewsletterSubscriber $subscribers;

    public function __construct()
    {
        parent::__construct();
        $this->schedules   = new MarketingSchedule();
        $this->posts       = new MarketingPost();
        $this->images      = new MarketingImage();
        $this->newsletters = new MarketingNewsletterIssue();
        $this->subscribers = new NewsletterSubscriber();
    }

    // ── Content Calendar ─────────────────────────────────────────────────────

    public function calendar(): void
    {
        $this->requireAdmin();

        $year = (int) $this->get('year', date('Y'));
        $month = (int) $this->get('month', date('n'));
        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }

        $entries = $this->schedules->getForCalendar($year, $month);
        $byDay = [];
        foreach ($entries as $e) {
            $day = (int) date('j', strtotime($e['scheduled_at']));
            $byDay[$day][] = $e;
        }

        $firstOfMonth = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = (int) date('t', $firstOfMonth);
        $startWeekday = (int) date('w', $firstOfMonth); // 0=Sun

        $this->render('admin/marketing/calendar/index', [
            'title'         => 'Content Calendar',
            'currentPage'   => 'marketing_scheduled',
            'year'          => $year,
            'month'         => $month,
            'monthLabel'    => date('F Y', $firstOfMonth),
            'daysInMonth'   => $daysInMonth,
            'startWeekday'  => $startWeekday,
            'byDay'         => $byDay,
            'today'         => date('Y-m-d'),
        ], 'admin');
    }

    // ── Scheduled Posts (list view) ──────────────────────────────────────────

    public function scheduled(): void
    {
        $this->requireAdmin();

        $filters = ['status' => $this->get('status', '')];
        $total = $this->schedules->countAll($filters);
        $pagination = $this->paginate($total, 25);
        $items = $this->schedules->getAll($filters, $pagination['per_page'], $pagination['offset']);

        $this->render('admin/marketing/schedule/index', [
            'title'       => 'Scheduled Posts',
            'currentPage' => 'marketing_scheduled',
            'items'       => $items,
            'filters'     => $filters,
            'pagination'  => $pagination,
            'statuses'    => MarketingSchedule::STATUSES,
        ], 'admin');
    }

    public function createSchedule(string $postId): void
    {
        $this->requireAdmin();

        $post = $this->posts->find((int) $postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/scheduled'));
            return;
        }

        $this->render('admin/marketing/schedule/create', [
            'title'       => 'Schedule Post',
            'currentPage' => 'marketing_scheduled',
            'post'        => $post,
            'platforms'   => MarketingPostCaption::PLATFORMS,
            'images'      => $this->images->getForPost((int) $postId),
        ], 'admin');
    }

    public function storeSchedule(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $postId = (int) $this->post('post_id', 0);
        $post = $this->posts->find($postId);
        if (!$post) {
            $this->flash('Post not found.', 'error');
            $this->redirect(url('admin/marketing/scheduled'));
            return;
        }

        $scheduledAt = $this->post('scheduled_at', '');
        if (!$scheduledAt) {
            $this->flash('A date and time are required.', 'error');
            $this->back();
            return;
        }

        $this->schedules->create([
            'post_id' => $postId,
            'platform' => $this->post('platform', 'facebook'),
            'image_id' => $this->post('image_id') ?: null,
            'scheduled_at' => str_replace('T', ' ', $scheduledAt) . ':00',
            'status' => 'pending',
            'created_by' => $this->user['id'],
        ]);

        $this->posts->update($postId, ['status' => 'scheduled']);

        $this->flash('Post scheduled.', 'success');
        $this->redirect(url('admin/marketing/scheduled'));
    }

    public function editSchedule(string $id): void
    {
        $this->requireAdmin();

        $schedule = $this->schedules->find((int) $id);
        if (!$schedule) {
            $this->flash('Schedule not found.', 'error');
            $this->redirect(url('admin/marketing/scheduled'));
            return;
        }

        $post = $this->posts->find((int) $schedule['post_id']);

        $this->render('admin/marketing/schedule/edit', [
            'title'       => 'Edit Schedule',
            'currentPage' => 'marketing_scheduled',
            'schedule'    => $schedule,
            'post'        => $post,
            'platforms'   => MarketingPostCaption::PLATFORMS,
            'images'      => $this->images->getForPost((int) $schedule['post_id']),
        ], 'admin');
    }

    public function updateSchedule(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $scheduledAt = $this->post('scheduled_at', '');
        if (!$scheduledAt) {
            $this->flash('A date and time are required.', 'error');
            $this->back();
            return;
        }

        $this->schedules->update((int) $id, [
            'platform' => $this->post('platform', 'facebook'),
            'image_id' => $this->post('image_id') ?: null,
            'scheduled_at' => str_replace('T', ' ', $scheduledAt) . ':00',
            'status' => 'pending',
        ]);

        $this->flash('Schedule updated.', 'success');
        $this->redirect(url('admin/marketing/scheduled'));
    }

    public function cancelSchedule(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->schedules->cancel((int) $id);
        $this->flash('Schedule cancelled.', 'success');
        $this->redirect(url('admin/marketing/scheduled'));
    }

    // ── Newsletter ────────────────────────────────────────────────────────────

    public function newsletter(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/newsletter/index', [
            'title'       => 'Newsletter',
            'currentPage' => 'marketing_newsletter',
            'items'       => $this->newsletters->getAll(20, 0),
            'issueTypes'  => MarketingNewsletterIssue::ISSUE_TYPES,
            'subscriberCounts' => $this->subscribers->countByStatus(),
        ], 'admin');
    }

    // ── Newsletter Subscribers (from the homepage welcome popup) ────────────

    public function subscribers(): void
    {
        $this->requireAdmin();

        $filters = [
            'status' => $this->get('status', ''),
            'search' => trim($this->get('search', '')),
        ];
        $total = $this->subscribers->countAll($filters);
        $pagination = $this->paginate($total, 25);

        $this->render('admin/marketing/newsletter/subscribers', [
            'title'       => 'Newsletter Subscribers',
            'currentPage' => 'marketing_newsletter',
            'items'       => $this->subscribers->getAll($filters, $pagination['per_page'], $pagination['offset']),
            'filters'     => $filters,
            'pagination'  => $pagination,
            'counts'      => $this->subscribers->countByStatus(),
            'statuses'    => NewsletterSubscriber::STATUSES,
        ], 'admin');
    }

    public function generateNewsletter(): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $issueType = $this->post('issue_type', 'weekly_summary');

        try {
            $content = NewsletterGeneratorService::generate($issueType);
            $id = $this->newsletters->create([
                'issue_type' => $issueType,
                'issue_date' => date('Y-m-d'),
                'subject' => $content['subject'],
                'html_body' => $content['html_body'],
                'text_body' => $content['text_body'],
                'status' => 'draft',
                'created_by' => $this->user['id'],
            ]);
            $this->flash('Newsletter generated.', 'success');
            $this->redirect(url('admin/marketing/newsletter/' . $id));
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), 'error');
            $this->redirect(url('admin/marketing/newsletter'));
        }
    }

    public function viewNewsletter(string $id): void
    {
        $this->requireAdmin();

        $issue = $this->newsletters->find((int) $id);
        if (!$issue) {
            $this->flash('Newsletter issue not found.', 'error');
            $this->redirect(url('admin/marketing/newsletter'));
            return;
        }

        $this->render('admin/marketing/newsletter/view', [
            'title'       => $issue['subject'],
            'currentPage' => 'marketing_newsletter',
            'issue'       => $issue,
        ], 'admin');
    }

    public function approveNewsletter(string $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $this->newsletters->approve((int) $id);
        $this->flash('Newsletter approved.', 'success');
        $this->redirect(url('admin/marketing/newsletter/' . $id));
    }

    public function downloadNewsletterHtml(string $id): void
    {
        $this->requireAdmin();

        $issue = $this->newsletters->find((int) $id);
        if (!$issue) {
            $this->flash('Newsletter issue not found.', 'error');
            $this->redirect(url('admin/marketing/newsletter'));
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter-' . $issue['id'] . '.html"');
        echo $issue['html_body'];
        exit;
    }

    public function downloadNewsletterText(string $id): void
    {
        $this->requireAdmin();

        $issue = $this->newsletters->find((int) $id);
        if (!$issue) {
            $this->flash('Newsletter issue not found.', 'error');
            $this->redirect(url('admin/marketing/newsletter'));
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter-' . $issue['id'] . '.txt"');
        echo $issue['text_body'];
        exit;
    }
}
