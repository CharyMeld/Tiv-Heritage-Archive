<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingPostCaption extends Model
{
    protected string $table = 'marketing_post_captions';

    protected array $fillable = [
        'post_id', 'platform', 'caption_text', 'hashtags', 'char_count', 'prompt_template_id',
    ];

    public const PLATFORMS = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'x'         => 'X (Twitter)',
        'linkedin'  => 'LinkedIn',
        'telegram'  => 'Telegram',
        'whatsapp'  => 'WhatsApp',
    ];

    public function getForPost(int $postId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE post_id = ? ORDER BY FIELD(platform,'facebook','instagram','x','linkedin','telegram','whatsapp')"
        );
        $stmt->execute([$postId]);
        $rows = $stmt->fetchAll();
        $byPlatform = [];
        foreach ($rows as $row) {
            $byPlatform[$row['platform']] = $row;
        }
        return $byPlatform;
    }

    public function upsertForPlatform(int $postId, string $platform, array $data): int
    {
        $existing = $this->db->prepare("SELECT id FROM {$this->table} WHERE post_id = ? AND platform = ?");
        $existing->execute([$postId, $platform]);
        $id = $existing->fetchColumn();

        $data['post_id'] = $postId;
        $data['platform'] = $platform;

        if ($id) {
            $this->update((int) $id, $data);
            return (int) $id;
        }

        return $this->create($data);
    }
}
