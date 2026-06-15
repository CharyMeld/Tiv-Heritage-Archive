<?php
/**
 * Bible Controller — admin chapter-by-chapter Tiv entry
 */

require_once BASE_PATH . '/models/BibleVerse.php';
require_once BASE_PATH . '/services/BibleTranslationMiner.php';

class BibleController extends Controller
{
    private BibleVerse $model;

    // NT books in canonical order (OT can be added once full Tiv Bible is available)
    private const BOOKS = [
        'MAT' => 'Matthew',      'MAR' => 'Mark',           'LUK' => 'Luke',
        'JOH' => 'John',         'ACT' => 'Acts',           'ROM' => 'Romans',
        '1CO' => '1 Corinthians','2CO' => '2 Corinthians',  'GAL' => 'Galatians',
        'EPH' => 'Ephesians',    'PHP' => 'Philippians',    'COL' => 'Colossians',
        '1TH' => '1 Thessalonians','2TH' => '2 Thessalonians',
        '1TI' => '1 Timothy',   '2TI' => '2 Timothy',      'TIT' => 'Titus',
        'PHM' => 'Philemon',     'HEB' => 'Hebrews',        'JAS' => 'James',
        '1PE' => '1 Peter',      '2PE' => '2 Peter',        '1JO' => '1 John',
        '2JO' => '2 John',       '3JO' => '3 John',         'JUD' => 'Jude',
        'REV' => 'Revelation',
        // OT
        'GEN' => 'Genesis',      'EXO' => 'Exodus',         'LEV' => 'Leviticus',
        'NUM' => 'Numbers',      'DEU' => 'Deuteronomy',    'JOS' => 'Joshua',
        'JDG' => 'Judges',       'RUT' => 'Ruth',           '1SA' => '1 Samuel',
        '2SA' => '2 Samuel',     '1KI' => '1 Kings',        '2KI' => '2 Kings',
        '1CH' => '1 Chronicles', '2CH' => '2 Chronicles',   'EZR' => 'Ezra',
        'NEH' => 'Nehemiah',     'EST' => 'Esther',         'JOB' => 'Job',
        'PSA' => 'Psalms',       'PRO' => 'Proverbs',       'ECC' => 'Ecclesiastes',
        'SNG' => 'Song of Solomon','ISA' => 'Isaiah',       'JER' => 'Jeremiah',
        'LAM' => 'Lamentations', 'EZE' => 'Ezekiel',        'DAN' => 'Daniel',
        'HOS' => 'Hosea',        'JOL' => 'Joel',           'AMO' => 'Amos',
        'OBA' => 'Obadiah',      'JON' => 'Jonah',          'MIC' => 'Micah',
        'NAM' => 'Nahum',        'HAB' => 'Habakkuk',       'ZEP' => 'Zephaniah',
        'HAG' => 'Haggai',       'ZEC' => 'Zechariah',      'MAL' => 'Malachi',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->model = new BibleVerse();
    }

    /**
     * Strip leading verse number from a Tiv line.
     * Handles: "1 text", "1. text", "1: text", "1) text", "(1) text", "1,text"
     */
    private function stripVerseNumber(string $text): string
    {
        $t = trim($text);
        // Whole line is just a number (standalone verse marker) → discard
        if (preg_match('/^\(?\d+\)?$/', $t)) return '';
        // Number at start followed by separator → strip it
        return trim(preg_replace('/^\s*\(?\d+[\)\.\:\,\s]+/', '', $text));
    }

    /**
     * Admin batch: re-mine all aligned verses into the translation engine.
     * POST admin/bible/mine
     */
    public function mine(): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $db    = \Database::getInstance();
        $miner = new BibleTranslationMiner($db);

        $phrases = $miner->syncAllPhrases();   // Feature 2 — full phrase sync only

        $this->flash(
            "Translation engine updated — {$phrases} verse phrase(s) synced to translation memory.",
            'success'
        );
        $this->redirect(url('admin/bible'));
    }

    /**
     * Dashboard — book list with Tiv completion stats
     */
    public function index(): void
    {
        $books = $this->model->getBookList();

        // Build completion stats per book
        $stats = $this->model->tivStatsByBook();
        $statsMap = [];
        foreach ($stats as $s) {
            $statsMap[$s['book_key']] = $s;
        }

        $this->render('admin/bible/index', [
            'title'       => 'Bible — Tiv Entry',
            'books'       => $books,
            'statsMap'    => $statsMap,
            'currentPage' => 'bible',
        ]);
    }

    /**
     * Show chapter entry form
     * GET admin/bible/{bookKey}/{chapter}
     */
    public function chapter(string $book, string $chapter): void
    {
        $book = strtoupper($book);
        $chapter = (int) $chapter;

        if (!isset(self::BOOKS[$book]) || $chapter < 1) {
            $this->redirect(url('admin/bible'));
            return;
        }

        $verses = $this->model->getChapter($book, $chapter);
        if (empty($verses)) {
            $this->flash('Chapter not found in database.', 'error');
            $this->redirect(url('admin/bible'));
            return;
        }

        $maxChapter = $this->model->maxChapter($book);
        try {
            $chapterMeta = $this->model->getChapterMeta($book, $chapter);
            if (empty($chapterMeta['tiv_book_name'])) {
                $chapterMeta['tiv_book_name'] = $this->model->getTivBookName($book);
            }
        } catch (Exception $e) {
            $chapterMeta = ['tiv_book_name' => '', 'tiv_chapter_title' => ''];
        }

        $this->render('admin/bible/chapter', [
            'title'       => self::BOOKS[$book] . ' ' . $chapter . ' — Tiv Entry',
            'bookKey'     => $book,
            'bookName'    => self::BOOKS[$book],
            'chapter'     => $chapter,
            'maxChapter'  => $maxChapter,
            'verses'      => $verses,
            'chapterMeta' => $chapterMeta,
            'currentPage' => 'bible',
        ]);
    }

    /**
     * Save pasted Tiv text for a chapter
     * POST admin/bible/{bookKey}/{chapter}
     */
    public function saveChapter(string $book, string $chapter): void
    {
        if (!$this->validateCSRF()) {
            $this->back();
            return;
        }

        $book = strtoupper($book);
        $chapter = (int) $chapter;

        if (!isset(self::BOOKS[$book])) {
            $this->flash('Invalid book.', 'error');
            $this->redirect(url('admin/bible'));
            return;
        }

        $mode = $this->post('input_mode', 'bulk');

        if ($mode === 'bulk') {
            // Bulk paste: one Tiv verse per line
            $raw   = $this->post('tiv_bulk', '');
            $lines = array_values(array_filter(
                array_map(
                    fn($l) => $this->stripVerseNumber(trim($l)),
                    explode("\n", str_replace("\r", "", $raw))
                ),
                fn($l) => $l !== ''
            ));
            $saved = $this->model->saveBulkTiv($book, $chapter, $lines);
        } else {
            // Individual fields (verse_N inputs)
            $data = [];
            foreach ($_POST as $key => $val) {
                if (preg_match('/^verse_(\d+)$/', $key, $m)) {
                    $data[(int)$m[1]] = $this->stripVerseNumber(trim($val));
                }
            }
            $saved = $this->model->saveIndividualTiv($book, $chapter, $data);
        }

        // Save chapter metadata (Tiv book name + chapter title)
        $tivBookName     = trim($this->post('tiv_book_name', ''));
        $tivChapterTitle = trim($this->post('tiv_chapter_title', ''));
        try {
            $this->model->saveChapterMeta($book, $chapter, $tivBookName, $tivChapterTitle);
        } catch (Exception $e) {
            // bible_chapters table not yet created — ignore silently
        }

        // Feature 2: auto-sync newly saved verses to translation_phrases
        if ($saved > 0) {
            $db     = \Database::getInstance();
            $miner  = new BibleTranslationMiner($db);
            $verses = $this->model->getChapter($book, $chapter);
            $miner->syncPhrasesFromVerses($verses);   // Feature 2 — phrase sync only
            // Feature 3 (word mining) is manual-only via the Sync button
            // to avoid low-confidence auto-insertions
        }

        $bookName = self::BOOKS[$book];
        $this->flash("$bookName $chapter saved — $saved verse(s) updated.", 'success');

        // Advance to next chapter if it exists
        $maxChapter = $this->model->maxChapter($book);
        $next = $chapter + 1;
        if ($next <= $maxChapter) {
            $this->redirect(url("admin/bible/{$book}/{$next}"));
        } else {
            $this->redirect(url('admin/bible'));
        }
    }
}
