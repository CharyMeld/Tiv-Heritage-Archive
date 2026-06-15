<?php
/**
 * Translation Log Model
 * Records every translation performed by users/guests.
 */

class TranslationLog extends Model
{
    protected string $table = 'translation_logs';
    protected array $fillable = [
        'user_id', 'source_text', 'translated_text',
        'source_language', 'target_language', 'match_type',
        'engine_used', 'confidence_score', 'payment_status', 'ip_address',
    ];

    /**
     * Get logs for a specific user with pagination.
     */
    public function forUser(int $userId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Admin: paginated list with optional filters.
     */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['match_type'])) {
            $where[]  = 'match_type = ?';
            $params[] = $filters['match_type'];
        }
        if (!empty($filters['source_language'])) {
            $where[]  = 'source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(source_text LIKE ? OR translated_text LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT tl.*, u.name AS user_name
             FROM {$this->table} tl
             LEFT JOIN users u ON tl.user_id = u.id
             {$whereClause}
             ORDER BY tl.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function adminCount(array $filters): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['match_type'])) {
            $where[]  = 'match_type = ?';
            $params[] = $filters['match_type'];
        }
        if (!empty($filters['source_language'])) {
            $where[]  = 'source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(source_text LIKE ? OR translated_text LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Stats summary for admin dashboard.
     */
    public function stats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
                AVG(confidence_score) AS avg_confidence,
                SUM(CASE WHEN match_type = 'none' THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN match_type = 'proverb' THEN 1 ELSE 0 END) AS proverb_hits,
                SUM(CASE WHEN match_type = 'word' THEN 1 ELSE 0 END) AS word_hits,
                SUM(CASE WHEN match_type = 'phrase' THEN 1 ELSE 0 END) AS phrase_hits,
                SUM(CASE WHEN match_type = 'category' THEN 1 ELSE 0 END) AS category_hits,
                SUM(CASE WHEN match_type = 'word_by_word' THEN 1 ELSE 0 END) AS partial_hits
             FROM {$this->table}"
        );
        return $stmt->fetch() ?: [];
    }
}
