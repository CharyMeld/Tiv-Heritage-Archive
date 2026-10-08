<?php

require_once BASE_PATH . '/core/Model.php';

class RegistrationEmailLog extends Model
{
    protected string $table = 'registration_emails';

    protected array $fillable = [
        'application_id', 'user_id', 'event_type', 'recipient_email',
        'subject', 'status', 'error_message', 'sent_at',
    ];

    public function alreadySent(int $applicationId, string $eventType): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->table} WHERE application_id = ? AND event_type = ? LIMIT 1"
        );
        $stmt->execute([$applicationId, $eventType]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * The unique (application_id, event_type) key is the real duplicate
     * guard — a race between two simultaneous requests still can't insert
     * twice. A duplicate-key error here just means another request beat
     * us to it, which is the desired outcome, so it's swallowed.
     */
    public function record(array $data): void
    {
        try {
            $this->create($data);
        } catch (\Throwable $e) {
            error_log('[RegistrationEmailLog] failed to record delivery log: ' . $e->getMessage());
        }
    }
}
