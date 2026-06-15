<?php
/**
 * Tiv Name Model
 */

require_once BASE_PATH . '/core/Model.php';

class TivName extends Model
{
    protected string $table = 'tiv_names';

    protected array $fillable = [
        'tiv_name',
        'english_meaning',
        'gender',
        'description',
        'pronunciation',
        'audio_file',
        'related_names',
        'origin_story',
        'usage_context',
        'is_featured',
        'created_by'
    ];

 
    	/**
	 * Search Tiv names
	 */
	public function search(string $query, array $fields = [], int $limit = 50): array
	{
	    // Default searchable fields for Tiv names
	    if (empty($fields)) {
		$fields = ['tiv_name', 'english_meaning', 'description', 'origin_story'];
	    }

	    return parent::search($query, $fields, $limit);
	}


    /**
     * Get names by gender
     */
    public function getByGender(string $gender, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE gender = ?
            ORDER BY tiv_name ASC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$gender, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Count by gender
     */
    public function countByGender(string $gender): int
    {
        return $this->countWhere('gender', $gender);
    }

    /**
     * Get all names alphabetically
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
     * Get names starting with letter
     */
    public function getByLetter(string $letter, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE tiv_name LIKE ?
            ORDER BY tiv_name ASC
            LIMIT ?"
        );
        $stmt->execute([$letter . '%', $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get related names
     */
    public function getRelated(int $id, int $limit = 5): array
    {
        $name = $this->find($id);
        if (!$name) {
            return [];
        }

        // Get names with same gender (excluding current)
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE id != ? AND gender = ?
            ORDER BY id DESC
            LIMIT ?"
        );
        $stmt->execute([$id, $name['gender'], $limit]);
        return $stmt->fetchAll();
    }
}
