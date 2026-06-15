<?php
/**
 * Tiv Proverb Model
 */

require_once BASE_PATH . '/core/Model.php';

class TivProverb extends Model
{
    protected string $table = 'tiv_proverbs';

    protected array $fillable = [
        'tiv_text',
        'english_translation',
        'deeper_meaning',
        'usage_context',
        'category',
        'is_featured',
        'created_by'
    ];

 	/**
	 * Search Tiv proverbs
	 */
	public function search(string $query, array $fields = [], int $limit = 50): array
	{
	    // Default searchable fields for Tiv proverbs
	    if (empty($fields)) {
		$fields = ['tiv_text', 'english_translation', 'deeper_meaning', 'usage_context'];
	    }

	    return parent::search($query, $fields, $limit);
	}


    /**
     * Get random proverb
     */
    public function getRandom(): ?array
    {
        $count = $this->count();
        if ($count === 0) return null;

        $offset = rand(0, $count - 1);
        $stmt   = $this->db->prepare(
            "SELECT * FROM {$this->table} LIMIT 1 OFFSET ?"
        );
        $stmt->execute([$offset]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get proverbs by category
     */
    public function getByCategory(string $category, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE category = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$category, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get all unique categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT category FROM {$this->table}
            WHERE category IS NOT NULL AND category != ''
            ORDER BY category ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get related proverbs
     */
    public function getRelated(int $id, int $limit = 3): array
    {
        $proverb = $this->find($id);
        if (!$proverb || empty($proverb['category'])) {
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->table}
                WHERE id != ?
                ORDER BY id DESC
                LIMIT ?"
            );
            $stmt->execute([$id, $limit]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE id != ? AND category = ?
            ORDER BY id DESC
            LIMIT ?"
        );
        $stmt->execute([$id, $proverb['category'], $limit]);
        return $stmt->fetchAll();
    }
}
