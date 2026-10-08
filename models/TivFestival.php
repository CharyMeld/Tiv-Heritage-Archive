<?php
/**
 * Tiv Festival Model
 */

require_once BASE_PATH . '/core/Model.php';

class TivFestival extends Model
{
    protected string $table = 'tiv_festivals';

    protected array $fillable = [
        'tiv_name',
        'english_name',
        'festival_type',
        'description',
        'significance',
        'timing',
        'duration',
        'activities',
        'location',
        'image',
        'is_featured',
        'created_by'
    ];

    /**
     * Search festivals — also attaches first gallery cover image to each result
     */
    public function search(string $query, array $fields = [], int $limit = 50): array
    {
        if (empty($fields)) {
            $fields = ['tiv_name', 'english_name', 'description', 'significance'];
        }

        $results = parent::search($query, $fields, $limit);
        return $this->attachGalleryCovers($results);
    }

    /**
     * Attach gallery_image (first gallery photo) to an array of festival rows
     */
    private function attachGalleryCovers(array $rows): array
    {
        if (empty($rows)) return $rows;

        $ids = array_column($rows, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT fg.festival_id, fg.image_path
             FROM festival_gallery fg
             INNER JOIN (
                 SELECT festival_id, MIN(id) AS min_id
                 FROM festival_gallery
                 WHERE festival_id IN ($placeholders)
                 GROUP BY festival_id
             ) first ON fg.id = first.min_id"
        );
        $stmt->execute($ids);
        $covers = [];
        while ($row = $stmt->fetch()) {
            $covers[$row['festival_id']] = $row['image_path'];
        }
        foreach ($rows as &$r) {
            $r['gallery_image'] = $covers[$r['id']] ?? '';
        }
        return $rows;
    }

    /**
     * Get all festivals alphabetically
     */
    public function getAllAlphabetically(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             ORDER BY tiv_name ASC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get festivals by location
     */
    public function getByLocation(string $location, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE location LIKE ?
             ORDER BY tiv_name ASC
             LIMIT ?"
        );
        $stmt->execute(['%' . $location . '%', $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all gallery photos for a festival, ordered by sort_order
     */
    public function getGallery(int $festivalId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM festival_gallery
             WHERE festival_id = ?
             ORDER BY is_featured DESC, sort_order ASC, id ASC"
        );
        $stmt->execute([$festivalId]);
        return $stmt->fetchAll();
    }

    /**
     * Get gallery photos joined with festival info for the homepage marquee
     */
    public function getGalleryForHomepage(int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT fg.*, tf.tiv_name AS festival_name, tf.english_name, tf.id AS festival_id
             FROM festival_gallery fg
             JOIN tiv_festivals tf ON fg.festival_id = tf.id
             ORDER BY fg.is_featured DESC, fg.sort_order ASC, fg.id ASC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single gallery photo by ID
     */
    public function getGalleryPhoto(int $photoId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM festival_gallery WHERE id = ?");
        $stmt->execute([$photoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Insert a new gallery photo record
     */
    public function addGalleryPhoto(
        int $festivalId,
        string $filename,
        ?string $caption,
        ?string $altText,
        int $isFeatured,
        int $uploadedBy
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO festival_gallery
                (festival_id, image_path, caption, alt_text, is_featured, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$festivalId, $filename, $caption, $altText, $isFeatured, $uploadedBy]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete a gallery photo record (ownership check via festival_id)
     */
    public function removeGalleryPhoto(int $photoId, int $festivalId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM festival_gallery WHERE id = ? AND festival_id = ?"
        );
        $stmt->execute([$photoId, $festivalId]);
    }

    /**
     * Get recent festivals with their first gallery cover image
     */
    public function recentWithCover(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.*,
                    (SELECT fg.image_path FROM festival_gallery fg
                     WHERE fg.festival_id = f.id ORDER BY fg.id ASC LIMIT 1) AS gallery_image
             FROM {$this->table} f
             ORDER BY f.is_featured DESC, f.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all festivals alphabetically with their first gallery cover image
     */
    public function getAllAlphabeticallyWithCover(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.*,
                    (SELECT fg.image_path FROM festival_gallery fg
                     WHERE fg.festival_id = f.id ORDER BY fg.id ASC LIMIT 1) AS gallery_image
             FROM {$this->table} f
             ORDER BY f.tiv_name ASC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get related festivals
     */
    public function getRelated(int $id, int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE id != ?
             ORDER BY id DESC
             LIMIT ?"
        );
        $stmt->execute([$id, $limit]);
        return $stmt->fetchAll();
    }
}

