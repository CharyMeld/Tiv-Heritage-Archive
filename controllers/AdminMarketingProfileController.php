<?php
require_once BASE_PATH . '/models/MarketingProfilePost.php';
require_once BASE_PATH . '/services/ProfilePackGenerator.php';

/**
 * Profile pack: ready-to-post items for the owner's personal Facebook profile,
 * prepared weekly by bin/marketing-profile-pack.php. The owner copies the
 * caption, downloads the image, posts by hand and marks the item posted.
 */
class AdminMarketingProfileController extends Controller
{
    private MarketingProfilePost $posts;

    public function __construct()
    {
        parent::__construct();
        $this->posts = new MarketingProfilePost();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $this->render('admin/marketing/profile/index', [
            'title'       => 'Profile Pack',
            'currentPage' => 'marketing_profile',
            'upcoming'    => $this->posts->upcoming(),
            'history'     => $this->posts->history(),
            'counts'      => $this->posts->counts(),
        ], 'admin');
    }

    /** Prepare the next empty slot now (one item per request: each caption is a slow model call). */
    public function generate(): void
    {
        $this->requireAdmin();
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        set_time_limit(300);
        $perDay = max(1, min(3, (int) $this->post('per_day', 1)));
        // Next empty slot however far ahead (was capped at 14 days, which blocked scheduling beyond two weeks).
        $lines = ProfilePackGenerator::fill(ProfilePackGenerator::MAX_DAYS_AHEAD, $perDay, false, (int) $this->user['id'], 1);
        $this->flash($lines ? implode(' ', $lines) : 'Nothing to prepare.', str_contains(implode(' ', $lines), 'failed') ? 'error' : 'success');
        $this->redirect(url('admin/marketing/profile'));
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        if (!$this->posts->find((int) $id)) {
            $this->flash('Profile post not found.', 'error');
        } else {
            $this->posts->update((int) $id, [
                'caption' => trim($this->post('caption', '')),
                'hashtags' => trim($this->post('hashtags', '')),
            ]);
            $this->flash('Caption saved.', 'success');
        }
        $this->redirect(url('admin/marketing/profile') . '#pp-' . (int) $id);
    }

    public function status(string $id): void
    {
        $this->requireAdmin();
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $status = $this->post('status', '');
        if (!isset(MarketingProfilePost::STATUSES[$status]) || !$this->posts->find((int) $id)) {
            $this->flash('Invalid request.', 'error');
        } else {
            $this->posts->update((int) $id, [
                'status' => $status,
                'posted_at' => $status === 'posted' ? date('Y-m-d H:i:s') : null,
            ]);
            $this->flash('Marked as ' . strtolower(MarketingProfilePost::STATUSES[$status]) . '.', 'success');
        }
        $this->redirect(url('admin/marketing/profile'));
    }
}
