<?php
/**
 * FileIndexer — Extracts searchable text from uploaded archive files.
 *
 * Supported file types:
 *   PDFs          → pdftotext (poppler-utils, standard on Ubuntu/Debian)
 *   Word .docx    → unzip XML extraction (no LibreOffice needed)
 *   Word .doc     → antiword if available, else skipped
 *   Images        → tesseract OCR (English + basic Tiv Latin)
 *   Audio/Video   → indexes the title/description only (no auto-transcription;
 *                    transcripts manually stored in content field are indexed)
 *
 * All CLI tools are called with a strict timeout; failures degrade gracefully.
 */
class FileIndexer
{
    private PDO $db;

    // CLI tool paths — autodetected on first use
    private static ?string $pdftotext  = null;
    private static ?string $tesseract  = null;
    private static ?string $antiword   = null;

    private const EXTRACT_TIMEOUT = 30; // seconds per file

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────

    /**
     * Extract text from a content_item's uploaded file and store it.
     * Returns the extracted text, or null if extraction is not possible.
     */
    public function extractAndStore(int $contentItemId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT media_file, media_type FROM content_items WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$contentItemId]);
        $row = $stmt->fetch();

        if (!$row || empty($row['media_file'])) {
            return null;
        }

        $filePath = UPLOADS_PATH . '/' . $row['media_file'];

        if (!file_exists($filePath)) {
            $this->updateStatus($contentItemId, 'failed');
            return null;
        }

        $text = $this->extractText($filePath, $row['media_type']);

        $this->db->prepare(
            "UPDATE content_items
             SET extracted_text = ?, extraction_status = ?
             WHERE id = ?"
        )->execute([$text, $text !== null ? 'done' : 'failed', $contentItemId]);

        return $text;
    }

    /**
     * Process all content_items with extraction_status = 'pending'.
     * Called from the admin reindex flow.
     */
    public function processPending(int $limit = 50): int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM content_items
             WHERE extraction_status = 'pending'
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $done = 0;
        foreach ($ids as $id) {
            $this->extractAndStore((int) $id);
            $done++;
        }
        return $done;
    }

    /**
     * Extract text from an arbitrary file path (used when first uploading).
     */
    public function extractFromPath(string $absolutePath, string $mediaType): ?string
    {
        if (!file_exists($absolutePath)) return null;
        return $this->extractText($absolutePath, $mediaType);
    }

    // ─────────────────────────────────────────────────────────────
    // Extraction dispatch
    // ─────────────────────────────────────────────────────────────

    private function extractText(string $path, string $mediaType): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // PDF
        if ($ext === 'pdf' || $mediaType === 'document') {
            if ($ext === 'pdf') return $this->extractPdf($path);
            if (in_array($ext, ['docx', 'odt'])) return $this->extractDocx($path);
            if ($ext === 'doc')  return $this->extractDoc($path);
            if ($ext === 'txt')  return file_get_contents($path) ?: null;
        }

        // Image → OCR
        if ($mediaType === 'image' || in_array($ext, ['jpg', 'jpeg', 'png', 'tiff', 'tif', 'bmp', 'webp'])) {
            return $this->extractOcr($path);
        }

        // Audio/video — only the text transcript stored in content field is indexed
        // No auto-transcription without Whisper
        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // PDF extraction
    // ─────────────────────────────────────────────────────────────

    private function extractPdf(string $path): ?string
    {
        $tool = $this->findTool('pdftotext', ['/usr/bin/pdftotext', '/usr/local/bin/pdftotext']);
        if (!$tool) return null;

        $cmd  = escapeshellarg($tool) . ' -layout -enc UTF-8 ' . escapeshellarg($path) . ' -';
        $text = $this->runCommand($cmd);

        return $this->cleanExtracted($text);
    }

    // ─────────────────────────────────────────────────────────────
    // Word .docx extraction (unzip XML — no LibreOffice needed)
    // ─────────────────────────────────────────────────────────────

    private function extractDocx(string $path): ?string
    {
        if (!class_exists('ZipArchive')) return null;

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return null;

        $text = '';
        // word/document.xml holds the main body text
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) return null;

        // Strip XML tags, decode entities, collapse whitespace
        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", $xml));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return $this->cleanExtracted($text);
    }

    // ─────────────────────────────────────────────────────────────
    // Word .doc extraction via antiword
    // ─────────────────────────────────────────────────────────────

    private function extractDoc(string $path): ?string
    {
        $tool = $this->findTool('antiword', ['/usr/bin/antiword', '/usr/local/bin/antiword']);
        if (!$tool) return null;

        $cmd  = escapeshellarg($tool) . ' ' . escapeshellarg($path);
        $text = $this->runCommand($cmd);

        return $this->cleanExtracted($text);
    }

    // ─────────────────────────────────────────────────────────────
    // OCR via Tesseract
    // ─────────────────────────────────────────────────────────────

    private function extractOcr(string $path): ?string
    {
        $tool = $this->findTool('tesseract', ['/usr/bin/tesseract', '/usr/local/bin/tesseract']);
        if (!$tool) return null;

        // Use English; Tiv uses Latin script so English coverage is reasonable
        $cmd  = escapeshellarg($tool) . ' ' . escapeshellarg($path) . ' stdout -l eng 2>/dev/null';
        $text = $this->runCommand($cmd);

        return $this->cleanExtracted($text);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function runCommand(string $cmd): ?string
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) return null;

        fclose($pipes[0]);

        stream_set_timeout($pipes[1], self::EXTRACT_TIMEOUT);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        return $output !== false && $output !== '' ? $output : null;
    }

    private function findTool(string $name, array $candidates): ?string
    {
        $prop = $name;
        if (self::$$prop !== null) {
            return self::$$prop === '' ? null : self::$$prop;
        }

        foreach ($candidates as $path) {
            if (file_exists($path) && is_executable($path)) {
                self::$$prop = $path;
                return $path;
            }
        }

        // Try PATH
        $which = shell_exec('which ' . escapeshellarg($name) . ' 2>/dev/null');
        if ($which) {
            $path = trim($which);
            self::$$prop = $path;
            return $path;
        }

        self::$$prop = '';
        return null;
    }

    private function cleanExtracted(?string $text): ?string
    {
        if ($text === null || trim($text) === '') return null;

        // Remove non-printable characters (except newlines/tabs)
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x{FFFF}]/u', ' ', $text);
        // Collapse multiple blank lines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        // Collapse runs of spaces
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = trim($text);

        // Truncate to 100KB to avoid bloating the DB
        return mb_substr($text, 0, 100000, 'UTF-8');
    }

    private function updateStatus(int $id, string $status): void
    {
        $this->db->prepare(
            "UPDATE content_items SET extraction_status = ? WHERE id = ?"
        )->execute([$status, $id]);
    }

    // ─────────────────────────────────────────────────────────────
    // Tool availability report (used by admin dashboard)
    // ─────────────────────────────────────────────────────────────

    public static function availableTools(): array
    {
        $tools = [
            'pdftotext' => ['/usr/bin/pdftotext', '/usr/local/bin/pdftotext'],
            'tesseract'  => ['/usr/bin/tesseract',  '/usr/local/bin/tesseract'],
            'antiword'   => ['/usr/bin/antiword',   '/usr/local/bin/antiword'],
        ];

        $result = [];
        foreach ($tools as $name => $candidates) {
            $found = false;
            foreach ($candidates as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $result[$name] = ['available' => true, 'path' => $path];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $which = shell_exec('which ' . escapeshellarg($name) . ' 2>/dev/null');
                $result[$name] = $which
                    ? ['available' => true,  'path' => trim($which)]
                    : ['available' => false, 'path' => null];
            }
        }
        return $result;
    }
}
