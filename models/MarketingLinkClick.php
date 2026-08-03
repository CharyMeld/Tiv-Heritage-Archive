<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingLinkClick extends Model
{
    protected string $table = 'marketing_link_clicks';

    protected array $fillable = ['utm_link_id', 'ip_address', 'user_agent', 'referrer'];

    public function log(int $linkId, ?string $ip, ?string $userAgent, ?string $referrer): int
    {
        return $this->create([
            'utm_link_id' => $linkId,
            'ip_address'  => $ip,
            'user_agent'  => $userAgent ? substr($userAgent, 0, 500) : null,
            'referrer'    => $referrer ? substr($referrer, 0, 500) : null,
        ]);
    }

    public function dailyCounts(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE(clicked_at) AS day, COUNT(*) AS clicks
             FROM {$this->table}
             WHERE clicked_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(clicked_at)
             ORDER BY day ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function totalForLink(int $linkId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE utm_link_id = ?");
        $stmt->execute([$linkId]);
        return (int) $stmt->fetchColumn();
    }

    public function totalAll(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }
}
