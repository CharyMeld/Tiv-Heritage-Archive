<?php
/**
 * Tiv Animal Model
 */

require_once BASE_PATH . '/core/Model.php';

class TivAnimal extends Model
{
    protected string $table = 'tiv_animals';

    protected array $fillable = [
        'name',
        'tiv_name',
        'description',
        'cultural_use',
        'image',
        'animal_type',
        'source_id',
        'created_by'
    ];

    /**
     * Override: tiv_animals uses 'views' not 'view_count'
     */
    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET views = views + 1 WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Search animals
     */
    public function search(string $query, array $fields = [], int $limit = 50): array
    {
        if (empty($fields)) {
            $fields = ['name', 'tiv_name', 'description', 'cultural_use'];
        }
        return parent::search($query, $fields, $limit);
    }

    /**
     * Get all animals alphabetically
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
     * Get animals by type
     */
    public function getByType(string $type, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE animal_type = ?
             ORDER BY tiv_name ASC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$type, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Count animals by type
     */
    public function countByType(string $type): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE animal_type = ?"
        );
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get related animals (excluding current)
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
