<?php
/**
 * Contributor Portal — earnings dashboard for logged-in contributors.
 */

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/Submission.php';
require_once BASE_PATH . '/models/ContributorPayment.php';

class ContributorController extends Controller
{
    private Submission $submissionModel;
    private ContributorPayment $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole('contributor'); // moderators/admins pass too (higher role level)
        $this->submissionModel = new Submission();
        $this->paymentModel    = new ContributorPayment();
    }

    public function dashboard(): void
    {
        $userId = (int) $this->user['id'];

        $this->render('contributor/dashboard', [
            'title'              => 'My Contributor Dashboard',
            'lifetimeEarnings'   => $this->submissionModel->lifetimeEarnings($userId),
            'totalPaid'          => $this->submissionModel->totalPaid($userId),
            'outstandingBalance' => $this->submissionModel->outstandingBalance($userId),
            'counts'             => $this->submissionModel->countsByStatus($userId),
            'history'            => $this->submissionModel->historyForUser($userId),
            'payments'           => $this->paymentModel->historyForUser($userId),
            'currentPage'        => 'contributor',
        ]);
    }
}
