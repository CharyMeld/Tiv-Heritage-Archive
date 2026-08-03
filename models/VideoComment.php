<?php

require_once BASE_PATH . '/core/Model.php';

class VideoComment extends Model
{
    protected string $table = 'video_comments';

    public function getForVideo(int $videoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT vc.*, u.name AS user_name, u.avatar AS user_avatar
             FROM video_comments vc
             LEFT JOIN users u ON u.id = vc.user_id
             WHERE vc.video_id = ? AND vc.is_approved = 1
             ORDER BY vc.created_at ASC"
        );
        $stmt->execute([$videoId]);
        return $stmt->fetchAll();
    }

    public function countForVideo(int $videoId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM video_comments WHERE video_id = ? AND is_approved = 1"
        );
        $stmt->execute([$videoId]);
        return (int) $stmt->fetchColumn();
    }

    public function add(int $videoId, ?int $userId, ?string $guestName, string $body): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO video_comments (video_id, user_id, guest_name, body)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$videoId, $userId, $guestName, $body]);
        return (int) $this->db->lastInsertId();
    }
}
