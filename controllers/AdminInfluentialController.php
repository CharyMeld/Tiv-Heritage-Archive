<?php
require_once BASE_PATH . '/models/InfluentialPerson.php';
require_once BASE_PATH . '/models/OutreachNomination.php';
require_once BASE_PATH . '/models/EmailTemplate.php';
require_once BASE_PATH . '/models/EmailCampaign.php';
require_once BASE_PATH . '/models/DirectSend.php';
require_once BASE_PATH . '/services/OutreachMailer.php';

class AdminInfluentialController extends Controller {

    private InfluentialPerson  $people;
    private OutreachNomination $nominations;
    private EmailTemplate      $templates;
    private EmailCampaign      $campaigns;
    private DirectSend         $directSends;

    public function __construct() {
        parent::__construct();
        $this->people      = new InfluentialPerson();
        $this->nominations = new OutreachNomination();
        $this->templates   = new EmailTemplate();
        $this->campaigns   = new EmailCampaign();
        $this->directSends = new DirectSend();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(): void {
        $this->requireAdmin();

        $peopleStats    = $this->people->getStats();
        $nominationStats= $this->nominations->getStats();
        $campaignsTotal = $this->campaigns->count();
        $recentPeople   = $this->people->recent(5);
        $recentNominations = $this->nominations->getAll('pending', 5, 0);

        $this->render('admin/outreach/index', [
            'title'             => 'Outreach Dashboard',
            'currentPage'       => 'outreach',
            'peopleStats'       => $peopleStats,
            'nominationStats'   => $nominationStats,
            'campaignsTotal'    => $campaignsTotal,
            'recentPeople'      => $recentPeople,
            'recentNominations' => $recentNominations,
        ], 'admin');
    }

    // ── People CRUD ───────────────────────────────────────────────────────────

    public function people(): void {
        $this->requireAdmin();

        $filters = [
            'q'              => trim($this->get('q', '')),
            'category'       => $this->get('category', ''),
            'contact_status' => $this->get('contact_status', ''),
            'consent_status' => $this->get('consent_status', ''),
        ];

        $total      = $this->people->countAll($filters);
        $pagination = $this->paginate($total, 25);
        $items      = $this->people->getAll($filters, $pagination['per_page'], $pagination['offset']);

        $this->render('admin/outreach/people/index', [
            'title'       => 'Influential People',
            'currentPage' => 'outreach',
            'items'       => $items,
            'filters'     => $filters,
            'pagination'  => $pagination,
            'total'       => $total,
            'categories'  => InfluentialPerson::CATEGORIES,
            'contactStatuses' => InfluentialPerson::CONTACT_STATUSES,
            'consentStatuses' => InfluentialPerson::CONSENT_STATUSES,
        ], 'admin');
    }

    public function createPerson(): void {
        $this->requireAdmin();

        $prefill = [];
        if ($this->get('from') === 'wiki') {
            $prefill = [
                'name'         => $this->get('name', ''),
                'category'     => $this->get('category', 'other'),
                'notes'        => $this->get('notes', ''),
                'source_url'   => $this->get('source_url', ''),
                'source_notes' => $this->get('source_notes', ''),
            ];
        }

        $this->render('admin/outreach/people/create', [
            'title'           => 'Add Influential Person',
            'currentPage'     => 'outreach',
            'categories'      => InfluentialPerson::CATEGORIES,
            'contactStatuses' => InfluentialPerson::CONTACT_STATUSES,
            'consentStatuses' => InfluentialPerson::CONSENT_STATUSES,
            'responseStatuses'=> InfluentialPerson::RESPONSE_STATUSES,
            'gdprBases'       => InfluentialPerson::GDPR_BASES,
            'prefill'         => $prefill,
        ], 'admin');
    }

    public function discoverySearch(): void {
        $this->requireAdmin();

        header('Content-Type: application/json; charset=utf-8');

        $q = trim($this->get('q', ''));
        if (mb_strlen($q) < 2) {
            echo json_encode(['results' => [], 'error' => null]);
            exit;
        }

        require_once BASE_PATH . '/services/WikipediaService.php';
        $results = (new WikipediaService())->searchWithDetails($q);

        echo json_encode(['results' => $results, 'query' => $q, 'error' => null]);
        exit;
    }

    public function storePerson(): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $name = trim($this->post('name', ''));
        if (empty($name)) {
            $this->storeOldInput();
            $this->flash('Name is required.', 'error');
            $this->back();
            return;
        }

        $email = trim($this->post('email', ''));
        if ($email !== '' && !Security::validateEmail($email)) {
            $this->storeOldInput();
            $this->flash('Invalid email address.', 'error');
            $this->back();
            return;
        }

        if ($email !== '' && $this->people->isEmailUnsubscribed($email)) {
            $this->storeOldInput();
            $this->flash('This email address is on the unsubscribe list and cannot be re-added.', 'error');
            $this->back();
            return;
        }

        $sourceNotes = trim($this->post('source_notes', ''));
        if (empty($sourceNotes)) {
            $this->storeOldInput();
            $this->flash('Source information is required — record where this person\'s details were found.', 'error');
            $this->back();
            return;
        }

        $data = [
            'name'            => $name,
            'title'           => trim($this->post('title', '')),
            'category'        => $this->post('category', 'other'),
            'organization'    => trim($this->post('organization', '')),
            'email'           => $email,
            'phone'           => trim($this->post('phone', '')),
            'website'         => trim($this->post('website', '')),
            'country'         => trim($this->post('country', '')),
            'state_region'    => trim($this->post('state_region', '')),
            'notes'           => trim($this->post('notes', '')),
            'source_url'      => trim($this->post('source_url', '')),
            'source_notes'    => $sourceNotes,
            'contact_status'  => $this->post('contact_status', 'not_contacted'),
            'consent_status'  => $this->post('consent_status', 'unknown'),
            'consent_date'    => $this->post('consent_status', 'unknown') === 'opted_in' ? date('Y-m-d') : null,
            'gdpr_basis'      => $this->post('gdpr_basis', 'not_set'),
            'response_status' => $this->post('response_status', 'none'),
            'last_contact_date' => $this->post('last_contact_date', '') ?: null,
            'is_unsubscribed' => (int) $this->post('is_unsubscribed', 0),
            'unsubscribe_token' => Security::generateToken(32),
            'added_by'        => $this->user['id'],
        ];

        $id = $this->people->create($data);
        Security::logActivity($this->user['id'], 'influential_person_created', 'influential_people', $id, null, $data);

        $this->flash('Person added successfully.', 'success');
        $this->redirect(url('admin/outreach/people'));
    }

    public function editPerson(string $id): void {
        $this->requireAdmin();

        $person = $this->people->find((int) $id);
        if (!$person) {
            $this->flash('Person not found.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }

        $this->render('admin/outreach/people/edit', [
            'title'           => 'Edit: ' . $person['name'],
            'currentPage'     => 'outreach',
            'person'          => $person,
            'categories'      => InfluentialPerson::CATEGORIES,
            'contactStatuses' => InfluentialPerson::CONTACT_STATUSES,
            'consentStatuses' => InfluentialPerson::CONSENT_STATUSES,
            'responseStatuses'=> InfluentialPerson::RESPONSE_STATUSES,
            'gdprBases'       => InfluentialPerson::GDPR_BASES,
        ], 'admin');
    }

    public function updatePerson(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $person = $this->people->find((int) $id);
        if (!$person) {
            $this->flash('Person not found.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }

        $name = trim($this->post('name', ''));
        if (empty($name)) {
            $this->storeOldInput();
            $this->flash('Name is required.', 'error');
            $this->back();
            return;
        }

        $email = trim($this->post('email', ''));
        if ($email !== '' && !Security::validateEmail($email)) {
            $this->storeOldInput();
            $this->flash('Invalid email address.', 'error');
            $this->back();
            return;
        }

        // Block changing to an unsubscribed email (only if email actually changed)
        if ($email !== '' && $email !== ($person['email'] ?? '') && $this->people->isEmailUnsubscribed($email)) {
            $this->storeOldInput();
            $this->flash('That email address is on the unsubscribe list and cannot be used.', 'error');
            $this->back();
            return;
        }

        $sourceNotes = trim($this->post('source_notes', ''));
        if (empty($sourceNotes)) {
            $this->storeOldInput();
            $this->flash('Source information is required — record where this person\'s details were found.', 'error');
            $this->back();
            return;
        }

        $newConsentStatus = $this->post('consent_status', 'unknown');
        $consentDate = $person['consent_date'] ?? null;
        if ($newConsentStatus === 'opted_in' && ($person['consent_status'] ?? '') !== 'opted_in') {
            $consentDate = date('Y-m-d');
        }

        $data = [
            'name'            => $name,
            'title'           => trim($this->post('title', '')),
            'category'        => $this->post('category', 'other'),
            'organization'    => trim($this->post('organization', '')),
            'email'           => $email,
            'phone'           => trim($this->post('phone', '')),
            'website'         => trim($this->post('website', '')),
            'country'         => trim($this->post('country', '')),
            'state_region'    => trim($this->post('state_region', '')),
            'notes'           => trim($this->post('notes', '')),
            'source_url'      => trim($this->post('source_url', '')),
            'source_notes'    => $sourceNotes,
            'consent_status'  => $newConsentStatus,
            'consent_date'    => $consentDate,
            'gdpr_basis'      => $this->post('gdpr_basis', 'not_set'),
            'contact_status'  => $this->post('contact_status', 'not_contacted'),
            'response_status' => $this->post('response_status', 'none'),
            'last_contact_date' => $this->post('last_contact_date', '') ?: null,
            'is_unsubscribed' => (int) $this->post('is_unsubscribed', 0),
        ];

        $this->people->update((int) $id, $data);
        Security::logActivity($this->user['id'], 'influential_person_updated', 'influential_people', (int) $id, $person, $data);

        $this->flash('Person updated successfully.', 'success');
        $this->redirect(url('admin/outreach/people'));
    }

    public function deletePerson(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $person = $this->people->find((int) $id);
        if (!$person) {
            $this->flash('Person not found.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }

        $this->people->delete((int) $id);
        Security::logActivity($this->user['id'], 'influential_person_deleted', 'influential_people', (int) $id, $person, null);

        $this->flash('Person removed.', 'success');
        $this->redirect(url('admin/outreach/people'));
    }

    // ── One-off direct send to a single contact ─────────────────────────────────

    public function sendPersonForm(string $id): void {
        $this->requireAdmin();

        $person = $this->people->find((int) $id);
        if (!$person) {
            $this->flash('Person not found.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }

        $allTemplates = $this->templates->getAll();
        if (empty($allTemplates)) {
            $this->flash('Please create at least one email template before sending.', 'error');
            $this->redirect(url('admin/outreach/templates/create'));
            return;
        }

        if (empty($person['email'])) {
            $this->flash($person['name'] . ' has no email address on file.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }
        if ($person['is_unsubscribed'] || $person['consent_status'] === 'opted_out') {
            $this->flash($person['name'] . ' has opted out / unsubscribed and cannot be emailed.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }

        $selectedId = (int) $this->get('template_id', $allTemplates[0]['id']);
        $selected   = $this->templates->find($selectedId) ?: $allTemplates[0];

        $siteUrl  = defined('SITE_URL') ? SITE_URL : '';
        $unsubUrl = $person['unsubscribe_token'] ? $siteUrl . '/outreach/unsubscribe/' . $person['unsubscribe_token'] : '';
        $previewBody = OutreachMailer::buildBody($selected['body_html'], [
            'name'            => $person['name'],
            'title'           => $person['title'] ?? '',
            'unsubscribe_url' => $unsubUrl,
        ]);

        $this->render('admin/outreach/people/send', [
            'title'       => 'Send Email to ' . $person['name'],
            'currentPage' => 'outreach',
            'person'      => $person,
            'templates'   => $allTemplates,
            'selected'    => $selected,
            'previewBody' => $previewBody,
            'history'     => $this->directSends->getForPerson((int) $id),
        ], 'admin');
    }

    public function sendToPerson(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $person = $this->people->find((int) $id);
        if (!$person) {
            $this->flash('Person not found.', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }
        if (empty($person['email']) || $person['is_unsubscribed'] || $person['consent_status'] === 'opted_out') {
            $this->flash('This contact cannot be emailed (no address, unsubscribed, or opted out).', 'error');
            $this->redirect(url('admin/outreach/people'));
            return;
        }

        $templateId = (int) $this->post('template_id', 0);
        $template   = $this->templates->find($templateId);
        if (!$template) {
            $this->flash('Invalid template selected.', 'error');
            $this->redirect(url('admin/outreach/people/' . $id . '/send'));
            return;
        }

        $siteUrl  = defined('SITE_URL') ? SITE_URL : '';
        $unsubUrl = $person['unsubscribe_token'] ? $siteUrl . '/outreach/unsubscribe/' . $person['unsubscribe_token'] : '';
        $body = OutreachMailer::buildBody($template['body_html'], [
            'name'            => $person['name'],
            'title'           => $person['title'] ?? '',
            'unsubscribe_url' => $unsubUrl,
        ]);

        $ok = OutreachMailer::send($person['email'], $person['name'], $template['subject'], $body);

        $this->directSends->create([
            'person_id'   => (int) $id,
            'template_id' => $templateId,
            'email'       => $person['email'],
            'status'      => $ok ? 'sent' : 'failed',
            'sent_by'     => $this->user['id'],
        ]);

        if ($ok) {
            $this->people->markContacted((int) $id);
            Security::logActivity($this->user['id'], 'direct_email_sent', 'influential_people', (int) $id, null, ['template_id' => $templateId]);
            $this->flash('Email sent to ' . $person['name'] . '.', 'success');
        } else {
            $this->flash('Failed to send email to ' . $person['name'] . '.', 'error');
        }

        $this->redirect(url('admin/outreach/people'));
    }

    // ── Nominations ───────────────────────────────────────────────────────────

    public function nominations(): void {
        $this->requireAdmin();

        $statusFilter = $this->get('status', '');
        $total        = $this->nominations->countAll($statusFilter);
        $pagination   = $this->paginate($total, 20);
        $items        = $this->nominations->getAll($statusFilter, $pagination['per_page'], $pagination['offset']);
        $stats        = $this->nominations->getStats();

        $this->render('admin/outreach/nominations/index', [
            'title'        => 'Outreach Nominations',
            'currentPage'  => 'outreach',
            'items'        => $items,
            'stats'        => $stats,
            'statusFilter' => $statusFilter,
            'pagination'   => $pagination,
        ], 'admin');
    }

    public function viewNomination(string $id): void {
        $this->requireAdmin();

        $nomination = $this->nominations->find((int) $id);
        if (!$nomination) {
            $this->flash('Nomination not found.', 'error');
            $this->redirect(url('admin/outreach/nominations'));
            return;
        }

        $this->render('admin/outreach/nominations/view', [
            'title'       => 'Nomination: ' . $nomination['nominee_name'],
            'currentPage' => 'outreach',
            'nomination'  => $nomination,
            'categories'  => InfluentialPerson::CATEGORIES,
        ], 'admin');
    }

    public function approveNomination(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $nomination = $this->nominations->find((int) $id);
        if (!$nomination || $nomination['status'] !== 'pending') {
            $this->flash('Nomination not found or already reviewed.', 'error');
            $this->redirect(url('admin/outreach/nominations'));
            return;
        }

        $category = $this->post('category', $nomination['nominee_category'] ?? 'other');
        if (!array_key_exists($category, InfluentialPerson::CATEGORIES)) {
            $category = 'other';
        }

        $nomineeEmail = $nomination['nominee_email'] ?? '';
        if ($nomineeEmail !== '' && $this->people->isEmailUnsubscribed($nomineeEmail)) {
            $this->flash('This nominee\'s email is on the unsubscribe list. They cannot be added to the database.', 'error');
            $this->redirect(url('admin/outreach/nominations/' . $id));
            return;
        }

        $personId = $this->people->create([
            'name'             => $nomination['nominee_name'],
            'title'            => $nomination['nominee_title']        ?? '',
            'category'         => $category,
            'organization'     => $nomination['nominee_organization'] ?? '',
            'email'            => $nomineeEmail,
            'website'          => $nomination['nominee_website']      ?? '',
            'country'          => $nomination['nominee_country']      ?? '',
            'state_region'     => $nomination['nominee_state_region'] ?? '',
            'contact_status'   => 'not_contacted',
            'consent_status'   => 'unknown',
            'response_status'  => 'none',
            'source_notes'     => 'Approved from community nomination #' . $id
                                  . ' — nominated by ' . ($nomination['nominator_name'] ?? 'anonymous'),
            'gdpr_basis'       => 'not_set',
            'nomination_id'    => (int) $id,
            'unsubscribe_token'=> Security::generateToken(32),
            'added_by'         => $this->user['id'],
        ]);

        $this->nominations->approve((int) $id, $this->user['id'], $personId, trim($this->post('admin_notes', '')));
        Security::logActivity($this->user['id'], 'nomination_approved', 'outreach_nominations', (int) $id, null, ['person_id' => $personId]);

        $this->flash('Nomination approved and person added to the database.', 'success');
        $this->redirect(url('admin/outreach/nominations'));
    }

    public function rejectNomination(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $nomination = $this->nominations->find((int) $id);
        if (!$nomination || $nomination['status'] !== 'pending') {
            $this->flash('Nomination not found or already reviewed.', 'error');
            $this->redirect(url('admin/outreach/nominations'));
            return;
        }

        $this->nominations->reject((int) $id, $this->user['id'], trim($this->post('admin_notes', '')));
        Security::logActivity($this->user['id'], 'nomination_rejected', 'outreach_nominations', (int) $id);

        $this->flash('Nomination rejected.', 'info');
        $this->redirect(url('admin/outreach/nominations'));
    }

    // ── Email Templates ───────────────────────────────────────────────────────

    public function templates(): void {
        $this->requireAdmin();

        $this->render('admin/outreach/templates/index', [
            'title'       => 'Email Templates',
            'currentPage' => 'outreach',
            'items'       => $this->templates->getAll(),
        ], 'admin');
    }

    public function createTemplate(): void {
        $this->requireAdmin();

        $this->render('admin/outreach/templates/create', [
            'title'       => 'New Email Template',
            'currentPage' => 'outreach',
        ], 'admin');
    }

    public function storeTemplate(): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $name    = trim($this->post('name', ''));
        $subject = trim($this->post('subject', ''));
        $body    = trim($this->post('body_html', ''));

        if (empty($name) || empty($subject) || empty($body)) {
            $this->storeOldInput();
            $this->flash('Name, subject, and body are all required.', 'error');
            $this->back();
            return;
        }

        $id = $this->templates->create([
            'name'       => $name,
            'subject'    => $subject,
            'body_html'  => $body,
            'created_by' => $this->user['id'],
        ]);

        Security::logActivity($this->user['id'], 'email_template_created', 'email_templates', $id);

        $this->flash('Template created.', 'success');
        $this->redirect(url('admin/outreach/templates'));
    }

    public function editTemplate(string $id): void {
        $this->requireAdmin();

        $template = $this->templates->find((int) $id);
        if (!$template) {
            $this->flash('Template not found.', 'error');
            $this->redirect(url('admin/outreach/templates'));
            return;
        }

        $this->render('admin/outreach/templates/edit', [
            'title'       => 'Edit Template: ' . $template['name'],
            'currentPage' => 'outreach',
            'template'    => $template,
        ], 'admin');
    }

    public function updateTemplate(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $template = $this->templates->find((int) $id);
        if (!$template) {
            $this->flash('Template not found.', 'error');
            $this->redirect(url('admin/outreach/templates'));
            return;
        }

        $name    = trim($this->post('name', ''));
        $subject = trim($this->post('subject', ''));
        $body    = trim($this->post('body_html', ''));

        if (empty($name) || empty($subject) || empty($body)) {
            $this->storeOldInput();
            $this->flash('Name, subject, and body are all required.', 'error');
            $this->back();
            return;
        }

        $this->templates->update((int) $id, [
            'name'      => $name,
            'subject'   => $subject,
            'body_html' => $body,
        ]);

        Security::logActivity($this->user['id'], 'email_template_updated', 'email_templates', (int) $id);

        $this->flash('Template updated.', 'success');
        $this->redirect(url('admin/outreach/templates'));
    }

    public function deleteTemplate(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $template = $this->templates->find((int) $id);
        if (!$template) {
            $this->flash('Template not found.', 'error');
            $this->redirect(url('admin/outreach/templates'));
            return;
        }

        $this->templates->delete((int) $id);
        Security::logActivity($this->user['id'], 'email_template_deleted', 'email_templates', (int) $id);

        $this->flash('Template deleted.', 'success');
        $this->redirect(url('admin/outreach/templates'));
    }

    // ── Campaigns ─────────────────────────────────────────────────────────────

    public function campaigns(): void {
        $this->requireAdmin();

        $total      = $this->campaigns->count();
        $pagination = $this->paginate($total, 20);
        $items      = $this->campaigns->getAll($pagination['per_page'], $pagination['offset']);

        foreach ($items as &$item) {
            $cats = $item['target_categories'] ? json_decode($item['target_categories'], true) : [];
            $item['recipient_count'] = $this->people->countForCampaign(is_array($cats) ? $cats : []);
        }
        unset($item);

        $this->render('admin/outreach/campaigns/index', [
            'title'       => 'Email Campaigns',
            'currentPage' => 'outreach',
            'items'       => $items,
            'pagination'  => $pagination,
        ], 'admin');
    }

    public function createCampaign(): void {
        $this->requireAdmin();

        $allTemplates = $this->templates->getAll();
        if (empty($allTemplates)) {
            $this->flash('Please create at least one email template before creating a campaign.', 'error');
            $this->redirect(url('admin/outreach/templates/create'));
            return;
        }

        $this->render('admin/outreach/campaigns/create', [
            'title'             => 'New Campaign',
            'currentPage'       => 'outreach',
            'templates'         => $allTemplates,
            'categories'        => InfluentialPerson::CATEGORIES,
            'preselectTemplate' => (int) $this->get('template_id', 0),
        ], 'admin');
    }

    public function storeCampaign(): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->storeOldInput();
            $this->back();
            return;
        }

        $name       = trim($this->post('name', ''));
        $templateId = (int) $this->post('template_id', 0);

        if (empty($name) || $templateId <= 0) {
            $this->storeOldInput();
            $this->flash('Campaign name and template are required.', 'error');
            $this->back();
            return;
        }

        if (!$this->templates->find($templateId)) {
            $this->storeOldInput();
            $this->flash('Invalid template selected.', 'error');
            $this->back();
            return;
        }

        $selectedCategories = $_POST['target_categories'] ?? [];
        $categoriesJson = empty($selectedCategories) ? null : json_encode(array_values($selectedCategories));

        $id = $this->campaigns->create([
            'name'              => $name,
            'template_id'       => $templateId,
            'target_categories' => $categoriesJson,
            'schedule_type'     => $this->post('schedule_type', 'once'),
            'status'            => 'draft',
            'created_by'        => $this->user['id'],
        ]);

        Security::logActivity($this->user['id'], 'email_campaign_created', 'email_campaigns', $id);

        $this->flash('Campaign created.', 'success');
        $this->redirect(url('admin/outreach/campaigns/' . $id));
    }

    public function viewCampaign(string $id): void {
        $this->requireAdmin();

        $campaign = $this->campaigns->getDetail((int) $id);
        if (!$campaign) {
            $this->flash('Campaign not found.', 'error');
            $this->redirect(url('admin/outreach/campaigns'));
            return;
        }

        $targetCategories = $this->campaigns->getTargetCategories((int) $id);
        $recipients       = $this->people->getForCampaign($targetCategories);
        $sends            = $this->campaigns->getSends((int) $id);

        $this->render('admin/outreach/campaigns/view', [
            'title'            => 'Campaign: ' . $campaign['name'],
            'currentPage'      => 'outreach',
            'campaign'         => $campaign,
            'targetCategories' => $targetCategories,
            'recipients'       => $recipients,
            'sends'            => $sends,
            'categories'       => InfluentialPerson::CATEGORIES,
        ], 'admin');
    }

    public function sendCampaign(string $id): void {
        $this->requireAdmin();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $campaign = $this->campaigns->getDetail((int) $id);
        if (!$campaign) {
            $this->flash('Campaign not found.', 'error');
            $this->redirect(url('admin/outreach/campaigns'));
            return;
        }

        if ($campaign['status'] === 'sent') {
            $this->flash('This campaign has already been sent.', 'error');
            $this->redirect(url('admin/outreach/campaigns/' . $id));
            return;
        }

        $targetCategories = $this->campaigns->getTargetCategories((int) $id);
        $recipients       = $this->people->getForCampaign($targetCategories);

        if (empty($recipients)) {
            $this->flash('No eligible recipients found (check consent and email fields).', 'error');
            $this->redirect(url('admin/outreach/campaigns/' . $id));
            return;
        }

        $siteUrl = defined('SITE_URL') ? SITE_URL : '';
        $sent    = 0;

        foreach ($recipients as $person) {
            if (empty($person['email'])) continue;

            $unsubUrl = $person['unsubscribe_token']
                ? $siteUrl . '/outreach/unsubscribe/' . $person['unsubscribe_token']
                : '';

            $body = OutreachMailer::buildBody($campaign['body_html'], [
                'name'            => $person['name'],
                'title'           => $person['title'] ?? '',
                'unsubscribe_url' => $unsubUrl,
            ]);

            $ok = OutreachMailer::send(
                $person['email'],
                $person['name'],
                $campaign['subject'],
                $body
            );

            $status = $ok ? 'sent' : 'failed';
            $this->campaigns->recordSend((int) $id, (int) $person['id'], $person['email'], $status);

            if ($ok) {
                $this->people->markContacted((int) $person['id']);
                $sent++;
            }
        }

        $this->campaigns->markSent((int) $id, $sent);
        Security::logActivity(
            $this->user['id'],
            'email_campaign_sent',
            'email_campaigns',
            (int) $id,
            null,
            ['total_sent' => $sent]
        );

        $this->flash("Campaign sent. {$sent} email(s) dispatched.", 'success');
        $this->redirect(url('admin/outreach/campaigns/' . $id));
    }

    // ── Discovery Assistant ───────────────────────────────────────────────────

    public function discovery(): void {
        $this->requireAdmin();

        $keywords = trim($this->get('keywords', ''));
        $category = $this->get('category', '');

        $this->render('admin/outreach/discovery', [
            'title'       => 'Discovery Assistant',
            'currentPage' => 'outreach',
            'keywords'    => $keywords,
            'category'    => $category,
            'categories'  => InfluentialPerson::CATEGORIES,
        ], 'admin');
    }
}
