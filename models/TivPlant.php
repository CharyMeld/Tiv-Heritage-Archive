<?php
/**
 * Tiv Plant Model
 */

require_once BASE_PATH . '/core/Model.php';

class TivPlant extends Model
{
    protected string $table = 'tiv_plants';

    protected array $fillable = [
        'tiv_name',
        'english_name',
        'scientific_name',
        'description',
        'medicinal_uses',
        'food_uses',
        'ritual_uses',
        'cultivation',
        'image',
        'is_featured',
        'created_by'
    ];

	  /**
	 * Search Tiv plants using LIKE (no FULLTEXT index required)
	 */
	public function search(string $query, array $fields = [], int $limit = 50): array
	{
	    if (empty($fields)) {
		$fields = ['tiv_name', 'english_name', 'scientific_name', 'description', 'medicinal_uses', 'food_uses'];
	    }

	    return parent::searchLike($query, $fields, $limit);
	}


    /**
     * Get plants with medicinal uses
     */
    public function getMedicinal(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE medicinal_uses IS NOT NULL AND medicinal_uses != ''
            ORDER BY tiv_name ASC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get plants used as food
     */
    public function getEdible(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE food_uses IS NOT NULL AND food_uses != ''
            ORDER BY tiv_name ASC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get plants used in rituals
     */
    public function getRitual(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE ritual_uses IS NOT NULL AND ritual_uses != ''
            ORDER BY tiv_name ASC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get all plants alphabetically
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
     * Get related plants
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
