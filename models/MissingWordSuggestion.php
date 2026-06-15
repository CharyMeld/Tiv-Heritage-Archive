<?php
/**
 * Missing Word Suggestion Model
 * Community-submitted meanings for words not in the dictionary.
 * Approved suggestions are added to daily_words by admin.
 */

class MissingWordSuggestion extends Model
{
    protected string $table = 'missing_word_suggestions';
    protected array $fillable = [
        'missing_word_id', 'user_id', 'suggested_meaning',
        'part_of_speech', 'example_sentence', 'notes',
        'admin_review_status', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    public function countPending(): int
    {
        return $this->countWhere('admin_review_status', 'pending');
    }

    /**
     * All suggestions for a specific missing word.
     */
    public function forWord(int $missingWordId): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, u.name AS user_name
             FROM {$this->table} s
             LEFT JOIN users u ON s.user_id = u.id
             WHERE s.missing_word_id = ?
             ORDER BY s.created_at DESC"
        );
        $stmt->execute([$missingWordId]);
        return $stmt->fetchAll();
    }

    /**
     * Admin list with joined missing word and user info.
     */
    public function adminList(array $filters, int $limit, int $offset): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['admin_review_status'])) {
            $where[]  = 's.admin_review_status = ?';
            $params[] = $filters['admin_review_status'];
        }
        if (!empty($filters['source_language'])) {
            $where[]  = 'mw.source_language = ?';
            $params[] = $filters['source_language'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT s.*, mw.word, mw.source_language, mw.target_language,
                    u.name AS user_name
             FROM {$this->table} s
             JOIN missing_words mw ON s.missing_word_id = mw.id
             LEFT JOIN users u ON s.user_id = u.id
             {$whereClause}
             ORDER BY s.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function adminCount(array $filters): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['admin_review_status'])) {
            $where[]  = 's.admin_review_status = ?';
            $params[] = $filters['admin_review_status'];
        }
        if (!empty($filters['source_language'])) {
            $where[]  = 'mw.source_language = ?';
            $params[] = $filters['source_language'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} s
             JOIN missing_words mw ON s.missing_word_id = mw.id
             {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function approve(int $id, int $reviewerId, string $notes = ''): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET admin_review_status = 'approved',
                 reviewed_by = ?, reviewed_at = NOW(), admin_notes = ?
             WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes ?: null, $id]);
    }

    public function reject(int $id, int $reviewerId, string $notes = ''): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET admin_review_status = 'rejected',
                 reviewed_by = ?, reviewed_at = NOW(), admin_notes = ?
             WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes ?: null, $id]);
    }

    /**
     * Check if a user has already suggested a meaning for this word.
     */
    public function userAlreadySuggested(int $missingWordId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE missing_word_id = ? AND user_id = ?"
        );
        $stmt->execute([$missingWordId, $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
