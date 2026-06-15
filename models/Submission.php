<?php
/**
 * Submission Model
 */

require_once BASE_PATH . '/core/Model.php';

class Submission extends Model
{
    protected string $table = 'submissions';

    protected array $fillable = [
        'user_id',
        'category',
        'tiv_term',
        'english_meaning',
        'description',
        'additional_data',
        'status',
        'reviewer_id',
        'reviewer_notes',
        'reviewed_at'
    ];

    /**
     * Get submissions with user info
     */
    public function getWithUser(string $status = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT s.*, u.name as user_name, u.email as user_email
                FROM {$this->table} s
                LEFT JOIN users u ON s.user_id = u.id";

        $params = [];

        if ($status) {
            $sql .= " WHERE s.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get pending submissions
     */
    public function getPending(int $limit = 50, int $offset = 0): array
    {
        return $this->getWithUser('pending', $limit, $offset);
    }

    /**
     * Count pending submissions
     */
    public function countPending(): int
    {
        return $this->countWhere('status', 'pending');
    }

    /**
     * Get user submissions
     */
    public function getUserSubmissions(int $userId, int $limit = 50, int $offset = 0): array
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

    /**
     * Count user submissions
     */
    public function countUserSubmissions(int $userId): int
    {
        return $this->countWhere('user_id', $userId);
    }

    /**
     * Approve submission
     */
    public function approve(int $id, int $reviewerId, ?string $notes = null): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET status = 'approved', reviewer_id = ?, reviewer_notes = ?, reviewed_at = NOW()
            WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes, $id]);
    }

    /**
     * Reject submission
     */
    public function reject(int $id, int $reviewerId, ?string $notes = null): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET status = 'rejected', reviewer_id = ?, reviewer_notes = ?, reviewed_at = NOW()
            WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes, $id]);
    }

    /**
     * Get submission with full details
     */
    public function getWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*,
                u.name as user_name,
                u.email as user_email,
                r.name as reviewer_name
            FROM {$this->table} s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN users r ON s.reviewer_id = r.id
            WHERE s.id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get recent submissions by category
     */
    public function getRecentByCategory(string $category, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE category = ? AND status = 'approved'
            ORDER BY created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$category, $limit]);
        return $stmt->fetchAll();
    }
}
