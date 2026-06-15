<?php
/**
 * Translation Controller
 * Handles public-facing translation pages and AJAX translate endpoint.
 */

class TranslationController extends Controller
{
    private TranslationEngine $engine;
    private TranslationLog $logModel;
    private TranslationFeedback $feedbackModel;

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

        $this->engine        = new TranslationEngine(Database::getInstance());
        $this->logModel      = new TranslationLog();
        $this->feedbackModel = new TranslationFeedback();
    }

    // -------------------------------------------------------
    // GET /translate  — Translation page
    // -------------------------------------------------------

    public function index(): void
    {
        $userId    = $this->user ? (int) $this->user['id'] : null;
        $remaining = $this->engine->remainingFree($userId);
        $isPaid    = $userId ? $this->engine->hasPaidAccess($userId) : false;

        $result = null;

        $this->render('translate/index', [
            'title'       => 'Tiv Translator — Tiv ↔ English',
            'description' => 'Translate between Tiv and English using the Tiv Translation Engine.',
            'remaining'   => $remaining,
            'isPaid'      => $isPaid,
            'isAdmin'     => is_admin(),
            'result'      => $result,
            'lastInput'   => '',
            'lastSource'  => 'tiv',
            'lastTarget'  => 'english',
        ]);
    }

    // -------------------------------------------------------
    // POST /translate  — Perform translation
    // -------------------------------------------------------

    public function translate(): void
    {
        // Allow enough time for NLLB cold-start (HF model loading can take ~40s)
        @set_time_limit(120);

        $userId     = $this->user ? (int) $this->user['id'] : null;
        $sourceLang = $this->post('source_language', 'tiv');
        $targetLang = $this->post('target_language', 'english');
        $inputText  = trim($_POST['input_text'] ?? '');

        $isAdmin = is_admin();

        // Validate
        if ($inputText === '') {
            $this->flash('Please enter text to translate.', 'warning');
            $this->redirect(url('translate'));
            return;
        }
        if (!$isAdmin && mb_strlen($inputText) > 1000) {
            $this->flash('Input text is too long. Maximum 1000 characters.', 'error');
            $this->redirect(url('translate'));
            return;
        }
        if (!in_array($sourceLang, ['tiv', 'english'])) $sourceLang = 'tiv';
        if (!in_array($targetLang, ['tiv', 'english'])) $targetLang = 'english';
        if ($sourceLang === $targetLang) $targetLang = ($sourceLang === 'tiv') ? 'english' : 'tiv';

        // Check daily limit (admins are exempt)
        if (!$isAdmin && !$this->engine->canTranslate($userId)) {
            $limit = $userId ? TranslationEngine::FREE_DAILY_LIMIT_USER : TranslationEngine::FREE_DAILY_LIMIT_GUEST;
            $this->flash(
                "You have reached your free daily translation limit ({$limit}/day). "
                . ($userId ? 'Upgrade to a paid plan for unlimited access.' : 'Register or log in for more translations.'),
                'warning'
            );
            $this->redirect(url('translate'));
            return;
        }

        // Run engine
        $result = $this->engine->translate($inputText, $sourceLang, $targetLang, $userId, true);

        $remaining = $this->engine->remainingFree($userId);
        $isPaid    = $userId ? $this->engine->hasPaidAccess($userId) : false;

        // AJAX request — return JSON
        if ($this->isAjax()) {
            $this->json([
                'success'   => true,
                'result'    => $result,
                'remaining' => $remaining,
            ]);
            return;
        }

        // Full page render with result
        $this->render('translate/index', [
            'title'       => 'Tiv Translator — Tiv ↔ English',
            'description' => 'Translate between Tiv and English using the Tiv Translation Engine.',
            'remaining'   => $remaining,
            'isPaid'      => $isPaid,
            'isAdmin'     => $isAdmin,
            'result'      => $result,
            'lastInput'   => $inputText,
            'lastSource'  => $sourceLang,
            'lastTarget'  => $targetLang,
        ]);
    }

    // -------------------------------------------------------
    // GET /translate/history  — User's translation history
    // -------------------------------------------------------

    public function history(): void
    {
        $this->requireAuth();

        $userId = (int) $this->user['id'];
        $total  = $this->logModel->countForUser($userId);
        $paging = $this->paginate($total, 20);
        $logs   = $this->logModel->forUser($userId, $paging['per_page'], $paging['offset']);
        $isPaid = $this->engine->hasPaidAccess($userId);

        $this->render('translate/history', [
            'title'   => 'Translation History',
            'logs'    => $logs,
            'paging'  => $paging,
            'isPaid'  => $isPaid,
        ]);
    }

    // -------------------------------------------------------
    // POST /translate/feedback  — Submit feedback on a translation
    // -------------------------------------------------------

    public function feedback(): void
    {
        $this->requireAuth();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $logId       = (int) ($this->post('log_id') ?? 0);
        $rating      = (int) ($this->post('rating') ?? 3);
        $correction  = trim($_POST['suggested_correction'] ?? '');

        if ($logId <= 0) {
            $this->flash('Invalid translation reference.', 'error');
            $this->back();
            return;
        }

        $rating = max(1, min(5, $rating));
        $userId = (int) $this->user['id'];

        $this->feedbackModel->create([
            'translation_log_id'  => $logId,
            'user_id'             => $userId,
            'rating'              => $rating,
            'suggested_correction' => $correction ?: null,
            'admin_review_status' => 'pending',
        ]);

        $this->flash('Thank you for your feedback! It helps improve the translator.', 'success');
        $this->redirect(url('translate/history'));
    }

    // -------------------------------------------------------
    // GET /translate/plans  — Pricing / upgrade page (scaffold)
    // -------------------------------------------------------

    public function plans(): void
    {
        $userId = $this->user ? (int) $this->user['id'] : null;
        $isPaid = $userId ? $this->engine->hasPaidAccess($userId) : false;

        $this->render('translate/plans', [
            'title'  => 'Translation Plans',
            'isPaid' => $isPaid,
        ]);
    }

    // -------------------------------------------------------
    // POST /translate/suggest-multi  — Bulk suggest meanings for missing words
    // -------------------------------------------------------

    public function storeMultiSuggestions(): void
    {
        $this->requireAuth();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $meanings = $_POST['meanings'] ?? [];
        if (!is_array($meanings)) {
            $this->flash('Invalid submission.', 'error');
            $this->redirect(url('translate'));
            return;
        }

        $missingWordModel = new MissingWord();
        $suggestionModel  = new MissingWordSuggestion();
        $userId           = (int) $this->user['id'];
        $isAdmin          = is_admin();

        $saved   = 0;
        $skipped = 0;

        foreach ($meanings as $wordId => $meaning) {
            $wordId  = (int) $wordId;
            $meaning = trim((string) $meaning);

            if ($meaning === '' || $wordId <= 0) {
                $skipped++;
                continue;
            }

            $word = $missingWordModel->find($wordId);
            if (!$word || in_array($word['status'], ['approved', 'rejected'])) {
                continue;
            }

            if ($isAdmin) {
                // Admin: approve directly so the next translation uses it immediately
                $missingWordModel->approve($wordId, $meaning, $userId);
            } else {
                // Regular user: save as pending suggestion (skip if already suggested)
                if ($suggestionModel->userAlreadySuggested($wordId, $userId)) {
                    continue;
                }
                $suggestionModel->create([
                    'missing_word_id'     => $wordId,
                    'user_id'             => $userId,
                    'suggested_meaning'   => $meaning,
                    'admin_review_status' => 'pending',
                ]);
                $missingWordModel->markHasSuggestions($wordId);
            }

            $saved++;
        }

        if ($saved === 0) {
            $this->flash('No meanings were entered. Fill in at least one word to submit.', 'warning');
        } elseif ($isAdmin) {
            $this->flash("$saved word" . ($saved > 1 ? 's' : '') . " approved and added to the dictionary.", 'success');
        } else {
            $this->flash("$saved meaning" . ($saved > 1 ? 's' : '') . " submitted for review. Thank you!", 'success');
        }

        $this->redirect(url('translate'));
    }

    // -------------------------------------------------------
    // GET /translate/suggest/{id}  — Suggest meaning for a missing word
    // -------------------------------------------------------

    public function suggestWord(int $id): void
    {
        $this->requireAuth();

        $missingWordModel = new MissingWord();
        $word = $missingWordModel->find($id);

        if (!$word || in_array($word['status'], ['approved', 'rejected'])) {
            $this->flash('This word is no longer open for suggestions.', 'warning');
            $this->redirect(url('translate'));
            return;
        }

        $suggestionModel = new MissingWordSuggestion();
        $userId = (int) $this->user['id'];

        // Existing suggestions for this word (to show context)
        $existing = $suggestionModel->forWord($id);
        $alreadySuggested = $suggestionModel->userAlreadySuggested($id, $userId);

        $this->render('translate/suggest-word', [
            'title'           => 'Suggest Meaning — ' . htmlspecialchars($word['word']),
            'word'            => $word,
            'existing'        => $existing,
            'alreadySuggested' => $alreadySuggested,
        ]);
    }

    // -------------------------------------------------------
    // POST /translate/suggest/{id}  — Store word suggestion
    // -------------------------------------------------------

    public function storeSuggestion(int $id): void
    {
        $this->requireAuth();

        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $missingWordModel = new MissingWord();
        $word = $missingWordModel->find($id);

        if (!$word || in_array($word['status'], ['approved', 'rejected'])) {
            $this->flash('This word is no longer open for suggestions.', 'warning');
            $this->redirect(url('translate'));
            return;
        }

        $userId  = (int) $this->user['id'];
        $meaning = trim($_POST['suggested_meaning'] ?? '');

        if ($meaning === '') {
            $this->flash('Please provide a meaning.', 'error');
            $this->back();
            return;
        }

        $suggestionModel = new MissingWordSuggestion();

        if ($suggestionModel->userAlreadySuggested($id, $userId)) {
            $this->flash('You have already submitted a suggestion for this word.', 'warning');
            $this->redirect(url('translate/suggest/' . $id));
            return;
        }

        $suggestionModel->create([
            'missing_word_id'    => $id,
            'user_id'            => $userId,
            'suggested_meaning'  => $meaning,
            'part_of_speech'     => $this->post('part_of_speech') ?: null,
            'example_sentence'   => trim($_POST['example_sentence'] ?? '') ?: null,
            'notes'              => trim($_POST['notes'] ?? '') ?: null,
            'admin_review_status' => 'pending',
        ]);

        // Update missing_word status to has_suggestions
        $missingWordModel->markHasSuggestions($id);

        $this->flash('Thank you! Your suggestion has been submitted for admin review.', 'success');
        $this->redirect(url('translate'));
    }
}
