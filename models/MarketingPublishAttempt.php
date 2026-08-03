<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingPublishAttempt extends Model
{
    protected string $table = 'marketing_publish_attempts';

    protected array $fillable = [
        'schedule_id', 'attempt_number', 'http_status', 'response_body',
        'error_message', 'success',
    ];

    public function logAttempt(int $scheduleId, array $data): int
    {
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE schedule_id = ?");
        $countStmt->execute([$scheduleId]);
        $attemptNumber = ((int) $countStmt->fetchColumn()) + 1;

        return $this->create(array_merge($data, [
            'schedule_id' => $scheduleId,
            'attempt_number' => $attemptNumber,
        ]));
    }

    public function getForSchedule(int $scheduleId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE schedule_id = ? ORDER BY attempted_at DESC"
        );
        $stmt->execute([$scheduleId]);
        return $stmt->fetchAll();
    }
}
