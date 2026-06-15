<?php
/**
 * Public Bible Reader Controller
 */
require_once BASE_PATH . '/models/BibleVerse.php';

class BibleReaderController extends Controller
{
    private BibleVerse $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new BibleVerse();
    }

    /** Book list landing page */
    public function index(): void
    {
        $books = $this->model->getBookList();
        $nt    = array_filter($books, fn($b) => $b['testament'] === 'NT');
        $ot    = array_filter($books, fn($b) => $b['testament'] === 'OT');

        $this->render('bible/index', [
            'title'       => 'Icighan Bibilo — Bible',
            'description' => 'Read the Bible in English (WEB) with Tiv translation',
            'nt'          => array_values($nt),
            'ot'          => array_values($ot),
            'currentPage' => 'bible',
        ]);
    }

    /** Read a chapter */
    public function chapter(string $book, string $chapter): void
    {
        $book    = strtoupper($book);
        $chapter = (int) $chapter;

        if ($chapter < 1) {
            $this->redirect(url('bible'));
            return;
        }

        $verses = $this->model->getChapter($book, $chapter);
        if (empty($verses)) {
            $this->redirect(url('bible'));
            return;
        }

        $maxChapter = $this->model->maxChapter($book);
        $bookName   = $verses[0]['book'];
        $hasTiv     = !empty(array_filter($verses, fn($v) => !empty($v['tiv'])));

        // bible_chapters table may not exist yet on all environments
        try {
            $chapterMeta = $this->model->getChapterMeta($book, $chapter);
            $tivBookName = $chapterMeta['tiv_book_name'] ?: $this->model->getTivBookName($book);
        } catch (Exception $e) {
            $chapterMeta = ['tiv_book_name' => '', 'tiv_chapter_title' => ''];
            $tivBookName = '';
        }

        $this->render('bible/chapter', [
            'title'            => "$bookName $chapter — Bible",
            'description'      => "$bookName chapter $chapter in English and Tiv",
            'bookKey'          => $book,
            'bookName'         => $bookName,
            'tivBookName'      => $tivBookName,
            'tivChapterTitle'  => $chapterMeta['tiv_chapter_title'] ?? '',
            'chapter'          => $chapter,
            'maxChapter'       => $maxChapter,
            'verses'           => $verses,
            'hasTiv'           => $hasTiv,
            'currentPage'      => 'bible',
        ]);
    }

    /**
     * API: return a batch of verses as JSON starting at a given offset.
     * GET /api/bible/verses?offset=N&limit=N
     */
    public function versesApi(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=300');

        $offset = max(0, (int) $this->get('offset', '0'));
        $limit  = min(30, max(1, (int) $this->get('limit', '20')));

        $data = $this->model->getVersesBatch($offset, $limit);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Search Bible */
    public function search(): void
    {
        $q       = trim($this->get('q', ''));
        $results = $q ? $this->model->searchVerses($q, 30) : [];

        $this->render('bible/search', [
            'title'       => $q ? "Bible Search: $q" : 'Search the Bible',
            'query'       => $q,
            'results'     => $results,
            'currentPage' => 'bible',
        ]);
    }
}
