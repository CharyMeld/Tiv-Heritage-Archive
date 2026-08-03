<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingSchedule extends Model
{
    protected string $table = 'marketing_schedules';

    protected array $fillable = [
        'post_id', 'platform', 'image_id', 'scheduled_at', 'status',
        'attempts', 'last_error', 'published_at', 'created_by',
    ];

    public const STATUSES = [
        'pending'   => 'Pending',
        'due'       => 'Due',
        'published' => 'Published',
        'failed'    => 'Failed',
        'cancelled' => 'Cancelled',
    ];

    public function getAll(array $filters = [], int $limit = 25, int $offset = 0, string $orderBy = 's.scheduled_at', string $direction = 'ASC'): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 's.status = ?';
            $params[] = $filters['status'];
        }

        // $orderBy/$direction are only ever set from the fixed whitelist of
        // literals below (never user input), so string-building them
        // directly into the SQL is safe — PDO placeholders can't parameterize
        // ORDER BY column/direction names.
        $orderBy = in_array($orderBy, ['s.scheduled_at', 's.published_at'], true) ? $orderBy : 's.scheduled_at';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT s.*, p.headline, p.source_type
                FROM {$this->table} s
                LEFT JOIN marketing_posts p ON p.id = s.post_id";
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= " ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        $where = [];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getForCalendar(int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, p.headline
             FROM {$this->table} s
             LEFT JOIN marketing_posts p ON p.id = s.post_id
             WHERE YEAR(s.scheduled_at) = ? AND MONTH(s.scheduled_at) = ?
             ORDER BY s.scheduled_at ASC"
        );
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll();
    }

    /**
     * Due schedules, as of now. Deliberately compares against a PHP-computed
     * timestamp rather than SQL's NOW() — this app runs PHP in Africa/Lagos
     * time (see config/config.php) while MySQL's NOW()/CURRENT_TIMESTAMP use
     * the server's SYSTEM timezone (UTC here), an hour apart. scheduled_at
     * values are always written from PHP, so comparisons must use PHP's
     * clock too, or "due" schedules would silently look like they're still
     * an hour in the future.
     */
    public function getDue(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE status IN ('pending','due') AND scheduled_at <= ? ORDER BY scheduled_at ASC"
        );
        $stmt->execute([date('Y-m-d H:i:s')]);
        return $stmt->fetchAll();
    }

    public function markPublished(int $id, ?string $publishedUrl = null): void
    {
        $this->db->prepare(
            "UPDATE {$this->table} SET status = 'published', published_at = NOW(), published_url = ? WHERE id = ?"
        )->execute([$publishedUrl, $id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $this->db->prepare(
            "UPDATE {$this->table} SET status = 'failed', attempts = attempts + 1, last_error = ? WHERE id = ?"
        )->execute([$error, $id]);
    }

    public function cancel(int $id): void
    {
        $this->db->prepare("UPDATE {$this->table} SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    }
}
