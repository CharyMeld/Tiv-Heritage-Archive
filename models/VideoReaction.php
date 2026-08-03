<?php

require_once BASE_PATH . '/core/Model.php';

class VideoReaction extends Model
{
    protected string $table = 'video_reactions';

    public function countForVideo(int $videoId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM video_reactions WHERE video_id = ?"
        );
        $stmt->execute([$videoId]);
        return (int) $stmt->fetchColumn();
    }

    public function hasReacted(int $videoId, string $identifier): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM video_reactions WHERE video_id = ? AND identifier = ? LIMIT 1"
        );
        $stmt->execute([$videoId, $identifier]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Toggle reaction. Returns true if added, false if removed.
     */
    public function toggle(int $videoId, string $identifier): bool
    {
        if ($this->hasReacted($videoId, $identifier)) {
            $stmt = $this->db->prepare(
                "DELETE FROM video_reactions WHERE video_id = ? AND identifier = ?"
            );
            $stmt->execute([$videoId, $identifier]);
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO video_reactions (video_id, identifier) VALUES (?, ?)"
        );
        $stmt->execute([$videoId, $identifier]);
        return true;
    }
}
