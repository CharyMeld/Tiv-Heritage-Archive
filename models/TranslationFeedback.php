<?php
/**
 * Translation Feedback Model
 * User corrections and quality improvement data.
 */

class TranslationFeedback extends Model
{
    protected string $table = 'translation_feedback';
    protected array $fillable = [
        'translation_log_id', 'user_id', 'rating',
        'suggested_correction', 'admin_review_status',
        'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    /**
     * Pending feedback for admin review.
     */
    public function pending(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            "SELECT tf.*, tl.source_text, tl.translated_text,
                    tl.source_language, tl.target_language,
                    u.name AS user_name
             FROM {$this->table} tf
             JOIN translation_logs tl ON tf.translation_log_id = tl.id
             LEFT JOIN users u ON tf.user_id = u.id
             WHERE tf.admin_review_status = 'pending'
             ORDER BY tf.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countPending(): int
    {
        return $this->countWhere('admin_review_status', 'pending');
    }

    public function adminList(array $filters, int $limit, int $offset): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['admin_review_status'])) {
            $where[]  = 'tf.admin_review_status = ?';
            $params[] = $filters['admin_review_status'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT tf.*, tl.source_text, tl.translated_text,
                    tl.source_language, tl.target_language,
                    u.name AS user_name
             FROM {$this->table} tf
             JOIN translation_logs tl ON tf.translation_log_id = tl.id
             LEFT JOIN users u ON tf.user_id = u.id
             {$whereClause}
             ORDER BY tf.created_at DESC
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
            $where[]  = 'admin_review_status = ?';
            $params[] = $filters['admin_review_status'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function approve(int $id, int $reviewerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET admin_review_status = 'approved', reviewed_by = ?, reviewed_at = NOW()
             WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $id]);
    }

    public function reject(int $id, int $reviewerId, string $notes = ''): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET admin_review_status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), admin_notes = ?
             WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes, $id]);
    }

    public function markConverted(int $id, int $reviewerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET admin_review_status = 'converted', reviewed_by = ?, reviewed_at = NOW()
             WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $id]);
    }
}
