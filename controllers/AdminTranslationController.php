<?php
/**
 * Admin Translation Controller
 * Manage phrases, rules, logs, and feedback for the Translation Engine.
 */

class AdminTranslationController extends Controller
{
    private TranslationLog $logModel;
    private TranslationPhrase $phraseModel;
    private TranslationRule $ruleModel;
    private TranslationFeedback $feedbackModel;
    private TranslationPayment $paymentModel;
    private TranslationEngine $engine;
    private MissingWord $missingWordModel;
    private MissingWordSuggestion $suggestionModel;

    public function __construct()
    {
        parent::__construct();
        require_once BASE_PATH . '/services/TranslationEngine.php';
        require_once BASE_PATH . '/models/TranslationLog.php';
        require_once BASE_PATH . '/models/TranslationPhrase.php';
        require_once BASE_PATH . '/models/TranslationRule.php';
        require_once BASE_PATH . '/models/TranslationFeedback.php';
        require_once BASE_PATH . '/models/TranslationPayment.php';
        require_once BASE_PATH . '/models/MissingWord.php';
        require_once BASE_PATH . '/models/MissingWordSuggestion.php';

        $this->logModel        = new TranslationLog();
        $this->phraseModel     = new TranslationPhrase();
        $this->ruleModel       = new TranslationRule();
        $this->feedbackModel   = new TranslationFeedback();
        $this->paymentModel    = new TranslationPayment();
        $this->engine          = new TranslationEngine(Database::getInstance());
        $this->missingWordModel  = new MissingWord();
        $this->suggestionModel   = new MissingWordSuggestion();
    }

    // -------------------------------------------------------
    // Dashboard / Logs
    // -------------------------------------------------------

    public function index(): void
    {
        $this->requireModerator();

        $filters = [
            'match_type'      => $this->get('match_type', ''),
            'source_language' => $this->get('source_language', ''),
            'q'               => $this->get('q', ''),
        ];

        $total  = $this->logModel->adminCount($filters);
        $paging = $this->paginate($total, 20);
        $logs   = $this->logModel->adminList($filters, $paging['per_page'], $paging['offset']);
        $stats  = $this->logModel->stats();

        $topQueries = $this->engine->topQueries(10);
        $pendingFeedback = $this->feedbackModel->countPending();

        $this->render('admin/translation/index', [
            'title'           => 'Translation Management',
            'currentPage'     => 'translation',
            'logs'            => $logs,
            'paging'          => $paging,
            'stats'           => $stats,
            'filters'         => $filters,
            'topQueries'      => $topQueries,
            'pendingFeedback' => $pendingFeedback,
        ], 'admin');
    }

    // -------------------------------------------------------
    // Phrases CRUD
    // -------------------------------------------------------

    public function phrases(): void
    {
        $this->requireModerator();

        $filters = [
            'source_language' => $this->get('source_language', ''),
            'context_tag'     => $this->get('context_tag', ''),
            'q'               => $this->get('q', ''),
        ];

        $total        = $this->phraseModel->adminCount($filters);
        $paging       = $this->paginate($total, 25);
        $phrases      = $this->phraseModel->adminList($filters, $paging['per_page'], $paging['offset']);
        $tags         = $this->phraseModel->allContextTags();
        $phraseStats  = $this->phraseModel->stats();

        $this->render('admin/translation/phrases', [
            'title'        => 'Translation Phrases',
            'currentPage'  => 'translation',
            'phrases'      => $phrases,
            'paging'       => $paging,
            'filters'      => $filters,
            'tags'         => $tags,
            'phraseStats'  => $phraseStats,
        ], 'admin');
    }

    public function createPhrase(): void
    {
        $this->requireModerator();

        $this->render('admin/translation/phrase-form', [
            'title'       => 'Add Translation Phrase',
            'currentPage' => 'translation',
            'phrase'      => null,
        ], 'admin');
    }

    public function storePhrase(): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $errors = $this->validateRequired([
            'source_text'     => 'Source text',
            'target_text'     => 'Target text',
            'source_language' => 'Source language',
            'target_language' => 'Target language',
        ]);

        if ($errors) {
            $this->flash(implode(' ', $errors), 'error');
            $this->back();
            return;
        }

        $this->phraseModel->create([
            'source_text'      => trim($_POST['source_text']),
            'source_language'  => $this->post('source_language'),
            'target_text'      => trim($_POST['target_text']),
            'target_language'  => $this->post('target_language'),
            'context_tag'      => $this->post('context_tag') ?: null,
            'confidence_score' => max(1, min(100, (int)($this->post('confidence_score') ?? 90))),
            'status'           => $this->post('status') === 'inactive' ? 'inactive' : 'active',
            'created_by'       => (int) $this->user['id'],
        ]);

        $this->flash('Phrase added successfully.', 'success');
        $this->redirect(url('admin/translation/phrases'));
    }

    public function editPhrase(int $id): void
    {
        $this->requireModerator();

        $phrase = $this->phraseModel->find($id);
        if (!$phrase) {
            $this->flash('Phrase not found.', 'error');
            $this->redirect(url('admin/translation/phrases'));
            return;
        }

        $this->render('admin/translation/phrase-form', [
            'title'       => 'Edit Translation Phrase',
            'currentPage' => 'translation',
            'phrase'      => $phrase,
        ], 'admin');
    }

    public function updatePhrase(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $phrase = $this->phraseModel->find($id);
        if (!$phrase) {
            $this->flash('Phrase not found.', 'error');
            $this->redirect(url('admin/translation/phrases'));
            return;
        }

        $this->phraseModel->update($id, [
            'source_text'      => trim($_POST['source_text'] ?? ''),
            'source_language'  => $this->post('source_language'),
            'target_text'      => trim($_POST['target_text'] ?? ''),
            'target_language'  => $this->post('target_language'),
            'context_tag'      => $this->post('context_tag') ?: null,
            'confidence_score' => max(1, min(100, (int)($this->post('confidence_score') ?? 90))),
            'status'           => $this->post('status') === 'inactive' ? 'inactive' : 'active',
        ]);

        $this->flash('Phrase updated.', 'success');
        $this->redirect(url('admin/translation/phrases'));
    }

    public function deletePhrase(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->phraseModel->delete($id);
        $this->flash('Phrase deleted.', 'success');
        $this->redirect(url('admin/translation/phrases'));
    }

    // -------------------------------------------------------
    // Rules CRUD
    // -------------------------------------------------------

    public function rules(): void
    {
        $this->requireModerator();

        $filters = [
            'source_language' => $this->get('source_language', ''),
            'rule_type'       => $this->get('rule_type', ''),
            'q'               => $this->get('q', ''),
        ];

        $total  = $this->ruleModel->adminCount($filters);
        $paging = $this->paginate($total, 25);
        $rules  = $this->ruleModel->adminList($filters, $paging['per_page'], $paging['offset']);

        $this->render('admin/translation/rules', [
            'title'       => 'Translation Rules',
            'currentPage' => 'translation',
            'rules'       => $rules,
            'paging'      => $paging,
            'filters'     => $filters,
        ], 'admin');
    }

    public function createRule(): void
    {
        $this->requireModerator();

        $this->render('admin/translation/rule-form', [
            'title'       => 'Add Translation Rule',
            'currentPage' => 'translation',
            'rule'        => null,
        ], 'admin');
    }

    public function storeRule(): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $errors = $this->validateRequired([
            'rule_name'        => 'Rule name',
            'pattern_text'     => 'Pattern text',
            'replacement_text' => 'Replacement text',
        ]);

        if ($errors) {
            $this->flash(implode(' ', $errors), 'error');
            $this->back();
            return;
        }

        $this->ruleModel->create([
            'rule_name'        => $this->post('rule_name'),
            'source_language'  => $this->post('source_language') === 'english' ? 'english' : 'tiv',
            'target_language'  => $this->post('target_language') === 'tiv' ? 'tiv' : 'english',
            'pattern_text'     => trim($_POST['pattern_text'] ?? ''),
            'replacement_text' => trim($_POST['replacement_text'] ?? ''),
            'rule_type'        => $this->post('rule_type') ?: 'phrase_fix',
            'priority_score'   => max(1, min(100, (int)($this->post('priority_score') ?? 50))),
            'status'           => $this->post('status') === 'inactive' ? 'inactive' : 'active',
            'created_by'       => (int) $this->user['id'],
        ]);

        $this->flash('Rule added.', 'success');
        $this->redirect(url('admin/translation/rules'));
    }

    public function editRule(int $id): void
    {
        $this->requireModerator();

        $rule = $this->ruleModel->find($id);
        if (!$rule) {
            $this->flash('Rule not found.', 'error');
            $this->redirect(url('admin/translation/rules'));
            return;
        }

        $this->render('admin/translation/rule-form', [
            'title'       => 'Edit Translation Rule',
            'currentPage' => 'translation',
            'rule'        => $rule,
        ], 'admin');
    }

    public function updateRule(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $rule = $this->ruleModel->find($id);
        if (!$rule) {
            $this->flash('Rule not found.', 'error');
            $this->redirect(url('admin/translation/rules'));
            return;
        }

        $this->ruleModel->update($id, [
            'rule_name'        => $this->post('rule_name'),
            'source_language'  => $this->post('source_language') === 'english' ? 'english' : 'tiv',
            'target_language'  => $this->post('target_language') === 'tiv' ? 'tiv' : 'english',
            'pattern_text'     => trim($_POST['pattern_text'] ?? ''),
            'replacement_text' => trim($_POST['replacement_text'] ?? ''),
            'rule_type'        => $this->post('rule_type') ?: 'phrase_fix',
            'priority_score'   => max(1, min(100, (int)($this->post('priority_score') ?? 50))),
            'status'           => $this->post('status') === 'inactive' ? 'inactive' : 'active',
        ]);

        $this->flash('Rule updated.', 'success');
        $this->redirect(url('admin/translation/rules'));
    }

    public function deleteRule(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->ruleModel->delete($id);
        $this->flash('Rule deleted.', 'success');
        $this->redirect(url('admin/translation/rules'));
    }

    // -------------------------------------------------------
    // Feedback review
    // -------------------------------------------------------

    public function feedback(): void
    {
        $this->requireModerator();

        $filters = [
            'admin_review_status' => $this->get('status', ''),
        ];

        $total    = $this->feedbackModel->adminCount($filters);
        $paging   = $this->paginate($total, 20);
        $feedback = $this->feedbackModel->adminList($filters, $paging['per_page'], $paging['offset']);

        $this->render('admin/translation/feedback', [
            'title'       => 'Translation Feedback',
            'currentPage' => 'translation',
            'feedback'    => $feedback,
            'paging'      => $paging,
            'filters'     => $filters,
        ], 'admin');
    }

    public function approveFeedback(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->feedbackModel->approve($id, (int) $this->user['id']);
        $this->flash('Feedback approved.', 'success');
        $this->redirect(url('admin/translation/feedback'));
    }

    public function rejectFeedback(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $notes = $this->post('notes', '');
        $this->feedbackModel->reject($id, (int) $this->user['id'], $notes);
        $this->flash('Feedback rejected.', 'success');
        $this->redirect(url('admin/translation/feedback'));
    }

    // -------------------------------------------------------
    // Missing Words — list & admin-direct add
    // -------------------------------------------------------

    public function missingWords(): void
    {
        $this->requireModerator();

        $filters = [
            'status'          => $this->get('status', ''),
            'source_language' => $this->get('source_language', ''),
            'q'               => $this->get('q', ''),
        ];

        $total  = $this->missingWordModel->adminCount($filters);
        $paging = $this->paginate($total, 25);
        $words  = $this->missingWordModel->adminList($filters, $paging['per_page'], $paging['offset']);

        $pendingSuggestions = $this->suggestionModel->countPending();

        $this->render('admin/translation/missing-words', [
            'title'              => 'Missing Words',
            'currentPage'        => 'translation',
            'words'              => $words,
            'paging'             => $paging,
            'filters'            => $filters,
            'pendingSuggestions' => $pendingSuggestions,
        ], 'admin');
    }

    /**
     * GET — form for admin to add a missing word directly to daily_words.
     */
    public function addWord(int $id): void
    {
        $this->requireModerator();

        $word = $this->missingWordModel->find($id);
        if (!$word) {
            $this->flash('Missing word not found.', 'error');
            $this->redirect(url('admin/translation/missing-words'));
            return;
        }

        $suggestions = $this->suggestionModel->forWord($id);

        $this->render('admin/translation/word-form', [
            'title'       => 'Add Word to Dictionary',
            'currentPage' => 'translation',
            'word'        => $word,
            'suggestions' => $suggestions,
        ], 'admin');
    }

    /**
     * POST — admin saves a missing word directly into daily_words and marks it approved.
     */
    public function storeWord(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $word = $this->missingWordModel->find($id);
        if (!$word) {
            $this->flash('Missing word not found.', 'error');
            $this->redirect(url('admin/translation/missing-words'));
            return;
        }

        $tivWord    = trim($_POST['tiv_word'] ?? '');
        $engMeaning = trim($_POST['english_meaning'] ?? '');

        if ($tivWord === '' || $engMeaning === '') {
            $this->flash('Both Tiv word and English meaning are required.', 'error');
            $this->back();
            return;
        }

        // Insert into daily_words
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO daily_words
                (tiv_word, english_meaning, alternate_meaning, part_of_speech,
                 category, example_tiv, example_english, pronunciation)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tivWord,
            $engMeaning,
            trim($_POST['alternate_meaning'] ?? '') ?: null,
            $this->post('part_of_speech') ?: null,
            $this->post('category') ?: null,
            trim($_POST['example_tiv'] ?? '') ?: null,
            trim($_POST['example_english'] ?? '') ?: null,
            trim($_POST['pronunciation'] ?? '') ?: null,
        ]);

        // Mark missing word as approved
        $this->missingWordModel->approve($id, $engMeaning, (int) $this->user['id']);

        $this->flash('"' . $tivWord . '" has been added to the dictionary.', 'success');
        $this->redirect(url('admin/translation/missing-words'));
    }

    /**
     * POST — admin rejects a missing word (closes it without adding).
     */
    public function rejectWord(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $this->missingWordModel->reject($id, (int) $this->user['id']);
        $this->flash('Word marked as rejected.', 'success');
        $this->redirect(url('admin/translation/missing-words'));
    }

    // -------------------------------------------------------
    // Missing Word Suggestions — review queue
    // -------------------------------------------------------

    public function wordSuggestions(): void
    {
        $this->requireModerator();

        $filters = [
            'admin_review_status' => $this->get('status', ''),
            'source_language'     => $this->get('source_language', ''),
        ];

        $total       = $this->suggestionModel->adminCount($filters);
        $paging      = $this->paginate($total, 20);
        $suggestions = $this->suggestionModel->adminList($filters, $paging['per_page'], $paging['offset']);

        $this->render('admin/translation/missing-word-suggestions', [
            'title'       => 'Word Suggestions',
            'currentPage' => 'translation',
            'suggestions' => $suggestions,
            'paging'      => $paging,
            'filters'     => $filters,
        ], 'admin');
    }

    /**
     * POST — approve a user suggestion and add word to daily_words.
     */
    public function approveSuggestion(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $suggestion = $this->suggestionModel->find($id);
        if (!$suggestion) {
            $this->flash('Suggestion not found.', 'error');
            $this->redirect(url('admin/translation/word-suggestions'));
            return;
        }

        $missingWord = $this->missingWordModel->find((int) $suggestion['missing_word_id']);
        if (!$missingWord) {
            $this->flash('Associated missing word not found.', 'error');
            $this->redirect(url('admin/translation/word-suggestions'));
            return;
        }

        // Determine tiv_word and english_meaning based on direction
        if ($missingWord['source_language'] === 'tiv') {
            $tivWord    = $missingWord['word'];
            $engMeaning = $suggestion['suggested_meaning'];
        } else {
            $tivWord    = $suggestion['suggested_meaning'];
            $engMeaning = $missingWord['word'];
        }

        // Add to daily_words
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO daily_words
                (tiv_word, english_meaning, part_of_speech, example_tiv)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $tivWord,
            $engMeaning,
            $suggestion['part_of_speech'] ?? null,
            $suggestion['example_sentence'] ?? null,
        ]);

        // Approve the suggestion
        $this->suggestionModel->approve($id, (int) $this->user['id']);

        // Mark the parent missing_word as approved
        $this->missingWordModel->approve(
            (int) $suggestion['missing_word_id'],
            $engMeaning,
            (int) $this->user['id']
        );

        $this->flash('Suggestion approved. "' . $tivWord . '" has been added to the dictionary.', 'success');
        $this->redirect(url('admin/translation/word-suggestions'));
    }

    /**
     * POST — reject a user suggestion.
     */
    public function rejectSuggestion(int $id): void
    {
        $this->requireModerator();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $notes = $this->post('notes', '');
        $this->suggestionModel->reject($id, (int) $this->user['id'], $notes);
        $this->flash('Suggestion rejected.', 'success');
        $this->redirect(url('admin/translation/word-suggestions'));
    }

    /**
     * Convert an approved correction into a new translation phrase.
     */
    public function convertFeedback(int $id): void
    {
        $this->requireAdmin();
        if (!$this->validateCSRF()) { $this->back(); return; }

        $fb = $this->feedbackModel->find($id);
        if (!$fb || empty($fb['suggested_correction'])) {
            $this->flash('Cannot convert: no correction text.', 'error');
            $this->redirect(url('admin/translation/feedback'));
            return;
        }

        // Load the original log
        $logModel = new TranslationLog();
        $log = $logModel->find((int) $fb['translation_log_id']);
        if (!$log) {
            $this->flash('Original log not found.', 'error');
            $this->redirect(url('admin/translation/feedback'));
            return;
        }

        // Add as a new phrase
        $this->phraseModel->create([
            'source_text'      => $log['source_text'],
            'source_language'  => $log['source_language'],
            'target_text'      => $fb['suggested_correction'],
            'target_language'  => $log['target_language'],
            'context_tag'      => 'user_correction',
            'confidence_score' => 85,
            'status'           => 'active',
            'created_by'       => (int) $this->user['id'],
        ]);

        $this->feedbackModel->markConverted($id, (int) $this->user['id']);
        $this->flash('Feedback converted to phrase successfully.', 'success');
        $this->redirect(url('admin/translation/feedback'));
    }
}
