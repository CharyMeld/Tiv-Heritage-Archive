<?php
require_once BASE_PATH . '/core/Model.php';

/**
 * Ready-to-post items for the owner's personal Facebook profile (the "Profile
 * pack"). Meta offers no API for personal profiles, so these are prepared here
 * and posted by hand; see services/ProfilePackGenerator.php.
 */
class MarketingProfilePost extends Model
{
    protected string $table = 'marketing_profile_posts';

    protected array $fillable = [
        'entity_table', 'entity_id', 'record_name', 'caption', 'hashtags', 'first_comment',
        'image_path', 'utm_link_id', 'post_at', 'status', 'posted_at', 'generated_by', 'model_name',
    ];

    public const STATUSES = ['ready' => 'Ready', 'posted' => 'Posted', 'skipped' => 'Skipped'];

    /** Items still to post, earliest first (overdue ones included). */
    public function upcoming(): array
    {
        return $this->db->query(
            "SELECT p.*, l.short_code, l.click_count, l.destination_url FROM {$this->table} p
             LEFT JOIN marketing_utm_links l ON l.id = p.utm_link_id
             WHERE p.status = 'ready' ORDER BY p.post_at, p.id"
        )->fetchAll();
    }

    /** Recently posted or skipped items, newest first. */
    public function history(int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, l.short_code, l.click_count, l.destination_url FROM {$this->table} p
             LEFT JOIN marketing_utm_links l ON l.id = p.utm_link_id
             WHERE p.status <> 'ready' ORDER BY COALESCE(p.posted_at, p.updated_at) DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /** Suggested posting times (Y-m-d H:i:s) already taken by ready or posted items. */
    public function takenSlots(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT post_at FROM {$this->table} WHERE status <> 'skipped' AND post_at BETWEEN ? AND ?"
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** "table:id" => last time it was prepared, for every record prepared since $since. */
    public function usedSince(string $since): array
    {
        $stmt = $this->db->prepare(
            "SELECT CONCAT(entity_table, ':', entity_id) AS k, MAX(created_at) AS last_used
             FROM {$this->table} WHERE created_at >= ? GROUP BY entity_table, entity_id"
        );
        $stmt->execute([$since]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function counts(): array
    {
        $rows = $this->db->query("SELECT status, COUNT(*) FROM {$this->table} GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
        return array_map('intval', $rows + array_fill_keys(array_keys(self::STATUSES), 0));
    }
}
