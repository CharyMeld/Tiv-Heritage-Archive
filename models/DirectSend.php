<?php
require_once BASE_PATH . '/core/Model.php';

class DirectSend extends Model {
    protected string $table = 'direct_sends';
    protected array $fillable = ['person_id', 'template_id', 'email', 'status', 'sent_by'];

    public function getForPerson(int $personId): array {
        $stmt = $this->db->prepare(
            "SELECT d.*, t.name AS template_name, u.name AS sender_name
             FROM {$this->table} d
             LEFT JOIN email_templates t ON t.id = d.template_id
             LEFT JOIN users u ON u.id = d.sent_by
             WHERE d.person_id = ?
             ORDER BY d.sent_at DESC"
        );
        $stmt->execute([$personId]);
        return $stmt->fetchAll();
    }
}
