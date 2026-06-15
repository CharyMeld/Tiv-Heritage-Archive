<?php
require_once BASE_PATH . '/core/Model.php';

class EmailCampaign extends Model {
    protected string $table = 'email_campaigns';
    protected array $fillable = [
        'name', 'template_id', 'target_categories',
        'schedule_type', 'status', 'total_sent', 'sent_at', 'created_by',
    ];

    public function getAll(int $limit = 20, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT c.*, t.name AS template_name, u.name AS creator_name
             FROM {$this->table} c
             LEFT JOIN email_templates t ON t.id = c.template_id
             LEFT JOIN users u ON u.id = c.created_by
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getDetail(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT c.*, t.name AS template_name, t.subject, t.body_html, u.name AS creator_name
             FROM {$this->table} c
             LEFT JOIN email_templates t ON t.id = c.template_id
             LEFT JOIN users u ON u.id = c.created_by
             WHERE c.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getSends(int $campaignId): array {
        $stmt = $this->db->prepare(
            "SELECT s.*, p.name AS person_name, p.category
             FROM campaign_sends s
             LEFT JOIN influential_people p ON p.id = s.person_id
             WHERE s.campaign_id = ?
             ORDER BY s.sent_at DESC"
        );
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public function recordSend(int $campaignId, int $personId, string $email, string $status): void {
        $this->db->prepare(
            "INSERT INTO campaign_sends (campaign_id, person_id, email, status) VALUES (?, ?, ?, ?)"
        )->execute([$campaignId, $personId, $email, $status]);
    }

    public function markSent(int $id, int $totalSent): void {
        $this->db->prepare(
            "UPDATE {$this->table}
             SET status = 'sent', total_sent = ?, sent_at = NOW()
             WHERE id = ?"
        )->execute([$totalSent, $id]);
    }

    public function getTargetCategories(int $id): array {
        $row = $this->find($id);
        if (!$row || empty($row['target_categories'])) return [];
        $decoded = json_decode($row['target_categories'], true);
        return is_array($decoded) ? $decoded : [];
    }
}
