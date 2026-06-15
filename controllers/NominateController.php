<?php
require_once BASE_PATH . '/models/OutreachNomination.php';
require_once BASE_PATH . '/models/InfluentialPerson.php';

class NominateController extends Controller {

    private OutreachNomination $nominations;

    public function __construct() {
        parent::__construct();
        $this->nominations = new OutreachNomination();
    }

    public function index(): void {
        $this->render('outreach/nominate', [
            'title'      => 'Nominate an Influential Tiv Person',
            'categories' => InfluentialPerson::CATEGORIES,
        ]);
    }

    public function submit(): void {
        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->flash('Form validation failed. Please try again.', 'error');
            $this->redirect(url('nominate-influential'));
            return;
        }

        $nomineeName = trim($this->post('nominee_name', ''));
        $reason      = trim($this->post('reason_for_nomination', ''));

        if (empty($nomineeName)) {
            $this->storeOldInput();
            $this->flash('The nominee\'s name is required.', 'error');
            $this->redirect(url('nominate-influential'));
            return;
        }

        if (empty($reason)) {
            $this->storeOldInput();
            $this->flash('Please provide a reason for the nomination.', 'error');
            $this->redirect(url('nominate-influential'));
            return;
        }

        $nominatorEmail = trim($this->post('nominator_email', ''));
        if ($nominatorEmail !== '' && !Security::validateEmail($nominatorEmail)) {
            $this->storeOldInput();
            $this->flash('Your email address appears to be invalid.', 'error');
            $this->redirect(url('nominate-influential'));
            return;
        }

        $nomineeEmail = trim($this->post('nominee_email', ''));
        if ($nomineeEmail !== '' && !Security::validateEmail($nomineeEmail)) {
            $this->storeOldInput();
            $this->flash('The nominee\'s email address appears to be invalid.', 'error');
            $this->redirect(url('nominate-influential'));
            return;
        }

        $this->nominations->create([
            'nominee_name'         => $nomineeName,
            'nominee_title'        => trim($this->post('nominee_title', '')),
            'nominee_category'     => $this->post('nominee_category', ''),
            'nominee_organization' => trim($this->post('nominee_organization', '')),
            'nominee_email'        => $nomineeEmail,
            'nominee_website'      => trim($this->post('nominee_website', '')),
            'nominee_country'      => trim($this->post('nominee_country', '')),
            'nominee_state_region' => trim($this->post('nominee_state_region', '')),
            'reason_for_nomination'=> $reason,
            'nominator_name'       => trim($this->post('nominator_name', '')),
            'nominator_email'      => $nominatorEmail,
            'status'               => 'pending',
        ]);

        $this->redirect(url('nominate-influential/success'));
    }

    public function success(): void {
        $this->render('outreach/success', [
            'title' => 'Nomination Submitted',
        ]);
    }
}
