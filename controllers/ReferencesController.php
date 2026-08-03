<?php
/**
 * References & Contributors Controller
 */

require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/SavedItem.php';
require_once BASE_PATH . '/services/CitationFormatter.php';

class ReferencesController extends Controller
{
    private const EXPORT_FORMATS = [
        'apa'     => ['ext' => 'txt', 'mime' => 'text/plain'],
        'mla'     => ['ext' => 'txt', 'mime' => 'text/plain'],
        'chicago' => ['ext' => 'txt', 'mime' => 'text/plain'],
        'harvard' => ['ext' => 'txt', 'mime' => 'text/plain'],
        'bibtex'  => ['ext' => 'bib', 'mime' => 'application/x-bibtex'],
        'ris'     => ['ext' => 'ris', 'mime' => 'application/x-research-info-systems'],
    ];

    public function index(): void
    {
        $model   = new Source();
        $grouped = $model->getGroupedByType();
        $total   = $model->count();

        $this->render('references/index', [
            'title'       => 'References & Contributors',
            'description' => 'All sources, references, and community contributors to the Tiv Heritage Archive',
            'grouped'     => $grouped,
            'total'       => $total,
            'typeLabels'  => Source::$typeLabels,
            'typeIcons'   => Source::$typeIcons,
            'currentPage' => 'references',
        ]);
    }

    /** GET references/{id} — Reference Details page */
    public function show(string $id): void
    {
        $model  = new Source();
        $source = $model->find((int) $id);

        if (!$source) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $canonicalUrl = SITE_URL . '/references/' . $source['id'];
        $formatter    = CitationFormatter::make($source, $canonicalUrl);

        $saved = false;
        if ($this->user) {
            $savedModel = new SavedItem();
            $saved = $savedModel->isSaved((int) $this->user['id'], 'sources', (int) $source['id']);
        }

        $this->render('references/show', [
            'title'        => $source['title'] ?: 'Reference',
            'description'  => 'Citation and details for this reference in the Tiv Heritage Archive.',
            'source'       => $source,
            'canonicalUrl' => $canonicalUrl,
            'defaultCitation' => $formatter->apa(),
            'saved'        => $saved,
            'breadcrumb'   => [
                ['label' => 'References', 'url' => url('references')],
                ['label' => mb_strimwidth($source['title'] ?: 'Reference', 0, 60, '…'), 'url' => null],
            ],
            'currentPage'  => 'references',
        ]);
    }

    /** GET references/{id}/export/{format} — downloads a formatted citation file */
    public function export(string $id, string $format): void
    {
        if (!isset(self::EXPORT_FORMATS[$format])) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $model  = new Source();
        $source = $model->find((int) $id);
        if (!$source) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $canonicalUrl = SITE_URL . '/references/' . $source['id'];
        $formatter = CitationFormatter::make($source, $canonicalUrl);
        $text = $formatter->{$format}();
        $meta = self::EXPORT_FORMATS[$format];

        $filename = 'citation-' . $source['id'] . '-' . $format . '.' . $meta['ext'];

        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        header('Content-Type: ' . $meta['mime'] . '; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($text));
        echo $text;
        exit;
    }

    /** GET references/{id}/print — standalone, layout-free print view */
    public function print(string $id): void
    {
        $model  = new Source();
        $source = $model->find((int) $id);

        if (!$source) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $canonicalUrl = SITE_URL . '/references/' . $source['id'];
        $formatter    = CitationFormatter::make($source, $canonicalUrl);

        // No layout: this view is intentionally self-contained (no styles.css/script.js)
        // so it never picks up the site's content-protection behavior.
        $this->render('references/print', [
            'title'          => ($source['title'] ?: 'Reference') . ' — Print',
            'source'         => $source,
            'canonicalUrl'   => $canonicalUrl,
            'defaultCitation' => $formatter->apa(),
        ], '');
    }

    /** POST references/{id}/save — AJAX toggle for Save to Collection */
    public function save(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $input['_token'] ?? '';

        if (!Security::validateCSRFToken($token)) {
            $this->json(['ok' => false, 'error' => 'invalid_token'], 403);
            return;
        }

        if (!$this->user) {
            $this->json(['ok' => false, 'error' => 'auth_required', 'loginUrl' => url('login')], 401);
            return;
        }

        $model  = new Source();
        $source = $model->find((int) $id);
        if (!$source) {
            $this->json(['ok' => false, 'error' => 'not_found'], 404);
            return;
        }

        $savedModel = new SavedItem();
        $saved = $savedModel->toggle((int) $this->user['id'], 'sources', (int) $id);

        $this->json(['ok' => true, 'saved' => $saved]);
    }
}
