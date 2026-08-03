<?php
/**
 * Admin Contributors — roster, per-contributor profile, and payment generation.
 */

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/CommunityMember.php';
require_once BASE_PATH . '/models/CommunityApplication.php';
require_once BASE_PATH . '/models/Submission.php';
require_once BASE_PATH . '/models/ContributorPayment.php';
require_once BASE_PATH . '/models/User.php';

class AdminContributorController extends Controller
{
    private CommunityMember $memberModel;
    private CommunityApplication $appModel;
    private Submission $submissionModel;
    private ContributorPayment $paymentModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->memberModel     = new CommunityMember();
        $this->appModel        = new CommunityApplication();
        $this->submissionModel = new Submission();
        $this->paymentModel    = new ContributorPayment();
        $this->userModel       = new User();
    }

    public function index(): void
    {
        $members  = $this->memberModel->getContributorRoster(100, 0);
        $earnings = $this->submissionModel->earningsSummaryByUser();

        $roster = array_map(function ($member) use ($earnings) {
            $e = $member['user_id'] ? ($earnings[$member['user_id']] ?? null) : null;
            $member['total_contributions']    = $e['total_contributions'] ?? 0;
            $member['approved_contributions'] = $e['approved_contributions'] ?? 0;
            $member['lifetime_earnings']      = $e['lifetime_earnings'] ?? 0;
            $member['total_paid']             = $e['total_paid'] ?? 0;
            $member['outstanding_balance']    = $e['outstanding_balance'] ?? 0;
            return $member;
        }, $members);

        $this->render('admin/contributors/index', [
            'title'       => 'Contributors | Admin',
            'currentPage' => 'contributors',
            'roster'      => $roster,
        ], 'admin');
    }

    public function show(string $id): void
    {
        $member = $this->memberModel->find((int) $id);
        if (!$member) {
            $this->flash('Contributor not found.', 'error');
            $this->redirect(url('admin/contributors'));
            return;
        }

        $userId = $member['user_id'] ? (int) $member['user_id'] : null;
        $profileUser = $userId ? $this->userModel->find($userId) : null;
        $application = $userId ? $this->appModel->findBy('user_id', $userId) : null;

        $this->render('admin/contributors/show', [
            'title'              => $member['full_name'] . ' | Contributors | Admin',
            'currentPage'        => 'contributors',
            'member'             => $member,
            // NOT 'user' — Controller::render() always overwrites that key
            // with the logged-in admin, for nav/session purposes. Using a
            // distinct key is what makes this the *profile subject's*
            // role, not the viewing admin's.
            'profileUser'        => $profileUser,
            'application'        => $application,
            'history'            => $userId ? $this->submissionModel->historyForUser($userId, 200, 0) : [],
            'payments'           => $userId ? $this->paymentModel->historyForUser($userId) : [],
            'lifetimeEarnings'   => $userId ? $this->submissionModel->lifetimeEarnings($userId) : 0,
            'totalPaid'          => $userId ? $this->submissionModel->totalPaid($userId) : 0,
            'outstandingBalance' => $userId ? $this->submissionModel->outstandingBalance($userId) : 0,
        ], 'admin');
    }

    /**
     * Generates one payment covering every approved-and-unpaid submission
     * for this contributor. Bookkeeping only — the admin has already paid
     * the contributor externally; this just records it and marks the
     * covered contributions Paid, atomically, so the audit trail (which
     * payment covers which submissions) can never end up half-applied.
     */
    public function generatePayment(string $id): void
    {
        $this->requireAdmin(); // financial action — admin only, not just moderator

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $member = $this->memberModel->find((int) $id);
        if (!$member || !$member['user_id']) {
            $this->flash('Contributor not found or not linked to a user account.', 'error');
            $this->redirect(url('admin/contributors'));
            return;
        }

        $userId = (int) $member['user_id'];
        $unpaid = $this->submissionModel->getUnpaidApproved($userId);

        if (empty($unpaid)) {
            $this->flash('This contributor has no approved, unpaid contributions to pay out.', 'info');
            $this->redirect(url('admin/contributors/' . $id));
            return;
        }

        $totalAmount = array_sum(array_map(fn($s) => (float) ($s['amount'] ?? 0), $unpaid));
        $paymentMethod = trim($this->post('payment_method')) ?: null;
        $reference     = trim($this->post('reference')) ?: null;
        $notes         = trim($this->post('notes')) ?: null;

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            $paymentId = $this->paymentModel->create([
                'user_id'            => $userId,
                'total_amount'       => $totalAmount,
                'currency'           => 'NGN',
                'contribution_count' => count($unpaid),
                'payment_method'     => $paymentMethod,
                'reference'          => $reference,
                'notes'              => $notes,
                'paid_by'            => $this->user['id'],
            ]);

            $ids = array_column($unpaid, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare(
                "UPDATE submissions SET payment_status = 'paid', payment_id = ? WHERE id IN ({$placeholders})"
            );
            $stmt->execute(array_merge([$paymentId], $ids));

            $db->commit();

            Security::logActivity($this->user['id'], 'contributor_payment_generated', 'contributor_payment', $paymentId);
            $this->flash('Payment of ₦' . number_format($totalAmount, 2) . " generated for {$member['full_name']}, covering " . count($unpaid) . ' contribution(s).', 'success');
        } catch (Throwable $e) {
            $db->rollBack();
            $this->flash('Payment generation failed — no changes were made. ' . $e->getMessage(), 'error');
        }

        $this->redirect(url('admin/contributors/' . $id));
    }
}
