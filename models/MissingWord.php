<?php
/**
 * Missing Word Model
 * Tracks words/phrases not found during translation.
 * Serves as the hub for community-contributed meanings.
 */

class MissingWord extends Model
{
    protected string $table = 'missing_words';
    protected array $fillable = [
        'word', 'source_language', 'target_language',
        'search_count', 'last_searched_at', 'status',
        'approved_meaning', 'approved_by', 'approved_at',
    ];

    /**
     * Record a missing word hit.
     * If word already exists, increment search_count.
     * Returns the missing_word id.
     */
    public function recordMiss(string $word, string $sourceLang, string $targetLang): int
    {
        $word = mb_strtolower(trim($word), 'UTF-8');
        if ($word === '') return 0;

        // Try to update existing row first
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET search_count = search_count + 1, last_searched_at = NOW()
             WHERE word = ? AND source_language = ? AND status NOT IN ('approved','rejected')"
        );
        $stmt->execute([$word, $sourceLang]);

        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare(
                "SELECT id FROM {$this->table} WHERE word = ? AND source_language = ? LIMIT 1"
            );
            $stmt->execute([$word, $sourceLang]);
            return (int) $stmt->fetchColumn();
        }

        // Insert new
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
                (word, source_language, target_language, search_count, status)
             VALUES (?, ?, ?, 1, 'missing')
             ON DUPLICATE KEY UPDATE
                search_count = search_count + 1, last_searched_at = NOW()"
        );
        $stmt->execute([$word, $sourceLang, $targetLang]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * List for admin with filters and pagination.
     */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'mw.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['source_language'])) {
            $where[]  = 'mw.source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['q'])) {
            $where[]  = 'mw.word LIKE ?';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT mw.*,
                    (SELECT COUNT(*) FROM missing_word_suggestions s
                     WHERE s.missing_word_id = mw.id AND s.admin_review_status = 'pending') AS pending_suggestions,
                    (SELECT COUNT(*) FROM missing_word_suggestions s
                     WHERE s.missing_word_id = mw.id) AS total_suggestions
             FROM {$this->table} mw
             {$whereClause}
             ORDER BY mw.search_count DESC, mw.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function adminCount(array $filters): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['source_language'])) {
            $where[]  = 'source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['q'])) {
            $where[]  = 'word LIKE ?';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        return $this->countWhere('status', $status);
    }

    /**
     * Mark a missing word as having at least one suggestion pending.
     */
    public function markHasSuggestions(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'has_suggestions' WHERE id = ? AND status = 'missing'"
        );
        $stmt->execute([$id]);
    }

    /**
     * Approve a missing word: store the final meaning and mark approved.
     */
    public function approve(int $id, string $meaning, int $reviewerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET status = 'approved', approved_meaning = ?, approved_by = ?, approved_at = NOW()
             WHERE id = ?"
        );
        return $stmt->execute([$meaning, $reviewerId, $id]);
    }

    public function reject(int $id, int $reviewerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $id]);
    }

    /**
     * Top missing words (most searched, not yet resolved).
     */
    public function topMissing(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE status IN ('missing', 'has_suggestions')
             ORDER BY search_count DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
