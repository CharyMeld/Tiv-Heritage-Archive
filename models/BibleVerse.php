<?php

require_once BASE_PATH . '/core/Model.php';

class BibleVerse extends Model
{
    protected string $table = 'bible_verses';

    protected array $fillable = [
        'testament', 'book', 'book_key', 'chapter', 'verse', 'english_web', 'tiv'
    ];

    /** Single verse lookup */
    public function getVerse(string $bookKey, int $chapter, int $verse): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE book_key = ? AND chapter = ? AND verse = ? LIMIT 1"
        );
        $stmt->execute([$bookKey, $chapter, $verse]);
        return $stmt->fetch() ?: null;
    }

    /** All verses for one chapter */
    public function getChapter(string $bookKey, int $chapter): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE book_key = ? AND chapter = ?
             ORDER BY verse ASC"
        );
        $stmt->execute([$bookKey, $chapter]);
        return $stmt->fetchAll();
    }

    /** All books with chapter count (for navigation) */
    public function getBookList(): array
    {
        $stmt = $this->db->query(
            "SELECT testament, book, book_key,
                    MAX(chapter) AS chapters,
                    COUNT(*)     AS total_verses
             FROM {$this->table}
             GROUP BY testament, book, book_key
             ORDER BY
               CASE testament WHEN 'OT' THEN 1 ELSE 2 END,
               MIN(id)"
        );
        return $stmt->fetchAll();
    }

    /**
     * Full-text search across English and Tiv text.
     * Falls back to LIKE if FULLTEXT index is not ready.
     */
    public function searchVerses(string $query, int $limit = 50): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT *,
                        MATCH(english_web, tiv) AGAINST(? IN NATURAL LANGUAGE MODE) AS score
                 FROM {$this->table}
                 WHERE MATCH(english_web, tiv) AGAINST(? IN NATURAL LANGUAGE MODE)
                 ORDER BY score DESC
                 LIMIT ?"
            );
            $stmt->execute([$query, $query, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // FULLTEXT not available yet — use LIKE
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->table}
                 WHERE english_web LIKE ? OR tiv LIKE ?
                 LIMIT ?"
            );
            $like = '%' . $query . '%';
            $stmt->execute([$like, $like, $limit]);
            return $stmt->fetchAll();
        }
    }

    /**
     * NT verses that have Tiv text — used for translation memory lookups.
     * Returns an array keyed by "book_key C:V" for fast in-memory access.
     */
    public function getAlignedNT(): array
    {
        $stmt = $this->db->query(
            "SELECT book_key, chapter, verse, english_web, tiv
             FROM {$this->table}
             WHERE testament = 'NT' AND tiv IS NOT NULL AND tiv != ''"
        );
        $out = [];
        while ($row = $stmt->fetch()) {
            $key = "{$row['book_key']} {$row['chapter']}:{$row['verse']}";
            $out[$key] = $row;
        }
        return $out;
    }

    /** Random verse that has both English and Tiv text (for homepage widget) */
    public function randomAligned(): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE tiv IS NOT NULL AND tiv != ''
             ORDER BY RAND() LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    /**
     * Fetch a batch of N verses starting at a global offset (Genesis→Revelation order).
     * Wraps around when reaching the end.
     * Returns ['verses'=>[...], 'total'=>int, 'next_offset'=>int]
     */
    public function getVersesBatch(int $offset, int $limit = 20): array
    {
        $total  = $this->count();
        $offset = (($offset % $total) + $total) % $total; // normalise

        $remaining = $total - $offset;
        if ($remaining >= $limit) {
            $stmt = $this->db->prepare(
                "SELECT id, testament, book, book_key, chapter, verse, english_web, tiv
                 FROM {$this->table} ORDER BY id LIMIT ? OFFSET ?"
            );
            $stmt->execute([$limit, $offset]);
            $verses = $stmt->fetchAll();
        } else {
            $stmt = $this->db->prepare(
                "SELECT id, testament, book, book_key, chapter, verse, english_web, tiv
                 FROM {$this->table} ORDER BY id LIMIT ? OFFSET ?"
            );
            $stmt->execute([$remaining, $offset]);
            $part1 = $stmt->fetchAll();

            $stmt->execute([$limit - $remaining, 0]);
            $part2  = $stmt->fetchAll();
            $verses = array_merge($part1, $part2);
        }

        return [
            'verses'      => $verses,
            'total'       => $total,
            'next_offset' => ($offset + $limit) % $total,
        ];
    }

    /**
     * Daily verse — cycles through ALL verses Genesis→Revelation, one per day.
     * Same verse all day, advances at midnight.
     */
    public function getDailyVerse(): ?array
    {
        $stmt  = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $total = (int) $stmt->fetchColumn();
        if (!$total) return null;

        // Use absolute day number so it never resets within a year
        $dayNumber = (int) floor(time() / 86400);
        $offset    = $dayNumber % $total;

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY id LIMIT 1 OFFSET ?"
        );
        $stmt->execute([$offset]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get a rolling window of N verses around today's daily verse position,
     * cycling Genesis → Revelation continuously.
     */
    public function getDailyWindow(int $count = 8): array
    {
        $stmt  = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        $total = (int) $stmt->fetchColumn();
        if (!$total) return [];

        $dayNumber = (int) floor(time() / 86400);
        $offset    = $dayNumber % $total;

        // Fetch $count verses starting from today's position, wrapping around
        $remaining = $total - $offset;
        if ($remaining >= $count) {
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->table} ORDER BY id LIMIT ? OFFSET ?"
            );
            $stmt->execute([$count, $offset]);
            return $stmt->fetchAll();
        }

        // Wrap around: take remaining + start from beginning
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY id LIMIT ? OFFSET ?"
        );
        $stmt->execute([$remaining, $offset]);
        $part1 = $stmt->fetchAll();

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY id LIMIT ?"
        );
        $stmt->execute([$count - $remaining]);
        $part2 = $stmt->fetchAll();

        return array_merge($part1, $part2);
    }

    /**
     * Fetch a list of specific verses by reference array.
     * $refs = [['book_key'=>'JOH','chapter'=>3,'verse'=>16], ...]
     */
    public function getKeyVerses(array $refs): array
    {
        if (empty($refs)) return [];
        $out  = [];
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE book_key = ? AND chapter = ? AND verse = ? LIMIT 1"
        );
        foreach ($refs as $r) {
            $stmt->execute([$r['book_key'], $r['chapter'], $r['verse']]);
            $row = $stmt->fetch();
            if ($row) $out[] = $row;
        }
        return $out;
    }

    /**
     * Get chapter metadata (Tiv book name + chapter title).
     */
    public function getChapterMeta(string $bookKey, int $chapter): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM bible_chapters WHERE book_key = ? AND chapter = ? LIMIT 1"
        );
        $stmt->execute([$bookKey, $chapter]);
        return $stmt->fetch() ?: ['tiv_book_name' => '', 'tiv_chapter_title' => ''];
    }

    /**
     * Get the Tiv book name for a book (from any chapter that has it set).
     */
    public function getTivBookName(string $bookKey): string
    {
        $stmt = $this->db->prepare(
            "SELECT tiv_book_name FROM bible_chapters
             WHERE book_key = ? AND tiv_book_name IS NOT NULL AND tiv_book_name != ''
             LIMIT 1"
        );
        $stmt->execute([$bookKey]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    /**
     * Save chapter metadata (Tiv book name + chapter title).
     * Also propagates tiv_book_name to all chapters of the same book
     * so you only need to set it once.
     */
    public function saveChapterMeta(string $bookKey, int $chapter, string $tivBookName, string $tivChapterTitle): void
    {
        // Upsert this chapter's metadata
        $stmt = $this->db->prepare(
            "INSERT INTO bible_chapters (book_key, chapter, tiv_book_name, tiv_chapter_title)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               tiv_book_name     = IF(? != '', ?, tiv_book_name),
               tiv_chapter_title = ?"
        );
        $stmt->execute([
            $bookKey, $chapter, $tivBookName ?: null, $tivChapterTitle ?: null,
            $tivBookName, $tivBookName ?: null,
            $tivChapterTitle ?: null,
        ]);

        // Propagate tiv_book_name to all other chapters of this book that don't have it yet
        if ($tivBookName !== '') {
            $stmt = $this->db->prepare(
                "UPDATE bible_chapters
                 SET tiv_book_name = ?
                 WHERE book_key = ? AND (tiv_book_name IS NULL OR tiv_book_name = '')"
            );
            $stmt->execute([$tivBookName, $bookKey]);
        }
    }

    /** Max chapter number for a book */
    public function maxChapter(string $bookKey): int
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(chapter) FROM {$this->table} WHERE book_key = ?"
        );
        $stmt->execute([$bookKey]);
        return (int) $stmt->fetchColumn();
    }

    /** Tiv completion stats per book */
    public function tivStatsByBook(): array
    {
        $stmt = $this->db->query(
            "SELECT book_key, book, testament,
                    COUNT(*)                                     AS total,
                    SUM(tiv IS NOT NULL AND tiv != '')           AS tiv_done,
                    MAX(chapter)                                 AS chapters
             FROM {$this->table}
             GROUP BY book_key, book, testament
             ORDER BY MIN(id)"
        );
        return $stmt->fetchAll();
    }

    /**
     * Save Tiv text from bulk paste (one line per verse, in order).
     * Lines are matched positionally to verse numbers in the chapter.
     */
    /**
     * Strip a leading verse number from a Tiv line.
     * Handles formats: "1 text", "1. text", "1: text", "1) text", "(1) text"
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
     * Save Tiv text from bulk paste (one line per verse, in order).
     * Automatically strips any leading verse numbers from each line.
     */
    public function saveBulkTiv(string $bookKey, int $chapter, array $lines): int
    {
        $verses = $this->getChapter($bookKey, $chapter);
        $saved  = 0;
        $stmt   = $this->db->prepare(
            "UPDATE {$this->table} SET tiv = ?
             WHERE book_key = ? AND chapter = ? AND verse = ?"
        );
        foreach ($verses as $i => $verse) {
            $raw = $lines[$i] ?? null;
            if ($raw === null || $raw === '') continue;
            $tiv = $this->stripVerseNumber($raw);
            if ($tiv === '') continue;
            $stmt->execute([$tiv, $bookKey, $chapter, $verse['verse']]);
            $saved++;
        }
        return $saved;
    }

    /**
     * Save Tiv text from individual verse fields.
     * $data is [verse_number => tiv_text]
     * Automatically strips any leading verse numbers.
     */
    public function saveIndividualTiv(string $bookKey, int $chapter, array $data): int
    {
        $saved = 0;
        $stmt  = $this->db->prepare(
            "UPDATE {$this->table} SET tiv = ?
             WHERE book_key = ? AND chapter = ? AND verse = ?"
        );
        foreach ($data as $verseNum => $raw) {
            $tiv = $this->stripVerseNumber((string) $raw);
            if ($tiv === '') continue;
            $stmt->execute([$tiv, $bookKey, $chapter, $verseNum]);
            $saved++;
        }
        return $saved;
    }

    /** Stats for admin dashboard */
    public function stats(): array
    {
        $stmt = $this->db->query(
            "SELECT
               COUNT(*)                                    AS total,
               SUM(testament = 'NT')                      AS nt_total,
               SUM(testament = 'OT')                      AS ot_total,
               SUM(tiv IS NOT NULL AND tiv != '')          AS tiv_count,
               ROUND(SUM(tiv IS NOT NULL AND tiv != '')/
                     NULLIF(SUM(testament='NT'),0)*100, 1) AS tiv_coverage_pct
             FROM {$this->table}"
        );
        return $stmt->fetch();
    }
}
