<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingUtmLink extends Model
{
    protected string $table = 'marketing_utm_links';

    protected array $fillable = [
        'post_id', 'schedule_id', 'platform', 'destination_url', 'utm_source',
        'utm_medium', 'utm_campaign', 'utm_content', 'short_code', 'click_count', 'created_by',
    ];

    public const PLATFORMS = [
        'facebook'   => 'Facebook',
        'instagram'  => 'Instagram',
        'x'          => 'X (Twitter)',
        'linkedin'   => 'LinkedIn',
        'telegram'   => 'Telegram',
        'whatsapp'   => 'WhatsApp',
        'newsletter' => 'Newsletter',
        'other'      => 'Other',
    ];

    public function findByShortCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE short_code = ? LIMIT 1");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public function findByPostAndPlatform(int $postId, string $platform): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE post_id = ? AND platform = ? ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$postId, $platform]);
        return $stmt->fetch() ?: null;
    }

    public function generateUniqueCode(int $length = 7): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while ($this->findByShortCode($code) !== null);

        return $code;
    }

    public function incrementClicks(int $id): void
    {
        $this->db->prepare("UPDATE {$this->table} SET click_count = click_count + 1 WHERE id = ?")->execute([$id]);
    }

    public function getAll(int $limit = 25, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function trafficByPlatform(): array
    {
        $stmt = $this->db->query(
            "SELECT platform, COUNT(*) AS link_count, COALESCE(SUM(click_count),0) AS total_clicks
             FROM {$this->table} GROUP BY platform ORDER BY total_clicks DESC"
        );
        return $stmt->fetchAll();
    }
}
