<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingGenerationLog extends Model
{
    protected string $table = 'marketing_generation_log';

    protected array $fillable = [
        'post_id', 'prompt_template_id', 'purpose', 'model_name', 'request_prompt',
        'response_raw', 'success', 'error_message', 'duration_ms',
    ];

    public function log(array $data): int
    {
        return $this->create($data);
    }

    public function recentFailures(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE success = 0 ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function successRate(int $days = 7): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total, SUM(success = 1) AS succeeded
             FROM {$this->table}
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        $row = $stmt->fetch();
        $total = (int) ($row['total'] ?? 0);
        $succeeded = (int) ($row['succeeded'] ?? 0);
        return [
            'total' => $total,
            'succeeded' => $succeeded,
            'rate' => $total > 0 ? round($succeeded / $total * 100, 1) : null,
        ];
    }
}
