<?php
/**
 * Learning Video Model
 */

require_once BASE_PATH . '/core/Model.php';

class LearningVideo extends Model
{
    protected string $table = 'learning_videos';

    protected array $fillable = [
        'title',
        'description',
        'youtube_id',
        'rumble_id',
        'facebook_url',
        'tiktok_id',
        'category',
        'difficulty',
        'duration',
        'sort_order',
        'is_featured',
        'is_active',
        'created_by'
    ];

    /**
     * Get featured videos for homepage
     */
    public function getFeatured(int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE is_featured = 1 AND is_active = 1
            ORDER BY sort_order ASC, created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all active videos
     */
    public function getActive(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE is_active = 1
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get videos by category
     */
    public function getByCategory(string $category, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE category = ? AND is_active = 1
            ORDER BY created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$category, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get videos by difficulty level
     */
    public function getByDifficulty(string $difficulty, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE difficulty = ? AND is_active = 1
            ORDER BY created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$difficulty, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT category FROM {$this->table}
            WHERE category IS NOT NULL AND is_active = 1
            ORDER BY category ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Count active videos
     */
    public function countActive(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE is_active = 1"
        );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Search videos
     */
    public function search(string $query, array $fields = [], int $limit = 50): array
    {
        if (empty($fields)) {
            $fields = ['title', 'description', 'category'];
        }
        return parent::search($query, $fields, $limit);
    }
}
