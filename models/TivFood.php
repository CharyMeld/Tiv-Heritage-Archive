<?php
/**
 * Tiv Food Model
 */

require_once BASE_PATH . '/core/Model.php';

class TivFood extends Model
{
    protected string $table = 'tiv_foods';

    protected array $fillable = [
        'tiv_name',
        'english_name',
        'category',
        'description',
        'ingredients',
        'preparation_method',
        'serving_suggestions',
        'cultural_significance',
        'image',
        'is_featured',
        'created_by'
    ];

    /**
     * Search foods
     * Must match Model::search() signature
     */
    public function search(string $query, array $fields = [], int $limit = 50): array
    {
        // Default searchable fields for Tiv foods
        if (empty($fields)) {
            $fields = [
                'tiv_name',
                'english_name',
                'description',
                'ingredients',
                'cultural_significance'
            ];
        }

        return parent::search($query, $fields, $limit);
    }

    /**
     * Get all foods alphabetically
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
     * Get foods by ingredient
     */
    public function getByIngredient(string $ingredient, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE ingredients LIKE ?
             ORDER BY tiv_name ASC
             LIMIT ?"
        );
        $stmt->execute(['%' . $ingredient . '%', $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get related foods
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

