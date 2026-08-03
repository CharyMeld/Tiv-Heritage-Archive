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
        'reviewed_at',
        'amount',
        'payment_status',
        'payment_id',
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
     * Approve submission. $amount is the payout earned for this specific
     * contribution, set by the admin at approval time (per-submission,
     * not a fixed rate table).
     */
    public function approve(int $id, int $reviewerId, ?string $notes = null, ?float $amount = null): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET status = 'approved', reviewer_id = ?, reviewer_notes = ?, reviewed_at = NOW(), amount = ?
            WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes, $amount, $id]);
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
     * Mark submission as needing revision (distinct from outright rejection).
     */
    public function needsRevision(int $id, int $reviewerId, ?string $notes = null): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET status = 'needs_revision', reviewer_id = ?, reviewer_notes = ?, reviewed_at = NOW()
            WHERE id = ?"
        );
        return $stmt->execute([$reviewerId, $notes, $id]);
    }

    /* ── Earnings (contributor + admin dashboards) ───────────────────── */

    public function lifetimeEarnings(int $userId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} WHERE user_id = ? AND status = 'approved'"
        );
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }

    public function totalPaid(int $userId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} WHERE user_id = ? AND payment_status = 'paid'"
        );
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }

    public function outstandingBalance(int $userId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table}
             WHERE user_id = ? AND status = 'approved' AND payment_status = 'unpaid'"
        );
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }

    /** ['approved' => n, 'pending' => n, 'needs_revision' => n, 'rejected' => n] */
    public function countsByStatus(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT status, COUNT(*) c FROM {$this->table} WHERE user_id = ? GROUP BY status"
        );
        $stmt->execute([$userId]);
        $raw = array_column($stmt->fetchAll(), 'c', 'status');
        return [
            'approved' => (int) ($raw['approved'] ?? 0),
            'pending' => (int) ($raw['pending'] ?? 0),
            'needs_revision' => (int) ($raw['needs_revision'] ?? 0),
            'rejected' => (int) ($raw['rejected'] ?? 0),
        ];
    }

    /** Full earnings-history rows for a contributor's dashboard. */
    public function historyForUser(int $userId, int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, category, tiv_term, english_meaning, status, amount, payment_status,
                    created_at, reviewed_at
             FROM {$this->table}
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /** All approved-and-unpaid rows for a contributor, used to generate a payment. */
    public function getUnpaidApproved(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = ? AND status = 'approved' AND payment_status = 'unpaid'"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Aggregate earnings across ALL contributors, for the admin roster. */
    public function earningsSummaryByUser(): array
    {
        $stmt = $this->db->query(
            "SELECT
                user_id,
                COUNT(*) AS total_contributions,
                SUM(status = 'approved') AS approved_contributions,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) AS lifetime_earnings,
                COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN status = 'approved' AND payment_status = 'unpaid' THEN amount ELSE 0 END), 0) AS outstanding_balance
             FROM {$this->table}
             WHERE user_id IS NOT NULL
             GROUP BY user_id"
        );
        $rows = $stmt->fetchAll();
        return array_column($rows, null, 'user_id');
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
