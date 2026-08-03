<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingPost extends Model
{
    protected string $table = 'marketing_posts';

    protected array $fillable = [
        'source_type', 'source_id', 'source_snapshot', 'selection_mode', 'prompt_template_id',
        'headline', 'caption', 'cta', 'hashtags', 'seo_title', 'meta_description',
        'og_description', 'twitter_description', 'status', 'rejection_reason',
        'model_name', 'reviewed_by', 'reviewed_at', 'created_by',
    ];

    public const STATUSES = [
        'draft'           => 'Draft',
        'pending_review'  => 'Pending Review',
        'approved'        => 'Approved',
        'rejected'        => 'Rejected',
        'scheduled'       => 'Scheduled',
        'published'       => 'Published',
    ];

    public const SOURCE_TYPES = [
        'word'              => 'Word of the Day',
        'proverb'           => 'Proverb',
        'name'              => 'Name',
        'plant'             => 'Plant',
        'festival'          => 'Festival',
        'food'              => 'Traditional Food',
        'animal'            => 'Animal',
        'grammar'           => 'Grammar Rule',
        'history'           => 'Timeline Event',
        'historical_figure' => 'Historical Figure',
        'lesson'            => 'Learning Video',
        'reference'         => 'Source',
    ];

    public function getAll(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sql = "SELECT p.*, t.name AS template_name
                FROM {$this->table} p
                LEFT JOIN marketing_prompt_templates t ON t.id = p.prompt_template_id";
        if ($where) $sql .= ' WHERE ' . $where;
        $sql .= ' ORDER BY p.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        [$where, $params] = $this->buildFilters($filters);
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= ' WHERE ' . $where;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildFilters(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['source_type'])) {
            $where[] = 'source_type = ?';
            $params[] = $filters['source_type'];
        }

        return [implode(' AND ', $where), $params];
    }

    public function getForCalendar(int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, s.scheduled_at, s.platform AS schedule_platform, s.status AS schedule_status
             FROM {$this->table} p
             INNER JOIN marketing_schedules s ON s.post_id = p.id
             WHERE YEAR(s.scheduled_at) = ? AND MONTH(s.scheduled_at) = ?
             ORDER BY s.scheduled_at ASC"
        );
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll();
    }

    public function countByStatus(): array
    {
        $stmt = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM {$this->table} GROUP BY status"
        );
        $rows = $stmt->fetchAll();
        $counts = array_fill_keys(array_keys(self::STATUSES), 0);
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }
        return $counts;
    }

    public function approve(int $id, int $userId): void
    {
        $this->db->prepare(
            "UPDATE {$this->table} SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
        )->execute([$userId, $id]);
    }

    public function reject(int $id, int $userId, string $reason): void
    {
        $this->db->prepare(
            "UPDATE {$this->table} SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?"
        )->execute([$reason, $userId, $id]);
    }
}
