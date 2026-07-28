<?php
/**
 * Daily Word Model
 */

require_once BASE_PATH . '/core/Model.php';

class DailyWord extends Model
{
    protected string $table = 'daily_words';

    protected array $fillable = [
        'tiv_word',
        'english_meaning',
        'alternate_meaning',
        'part_of_speech',
        'category',
        'pronunciation',
        'ipa',
        'tone',
        'root_word_id',
        'audio_file',
        'example_tiv',
        'example_english',
        'literal_meaning',
        'figurative_meaning',
        'usage_notes',
        'dialect_region',
        'frequency',
        'related_words',
        'is_active',
        'display_date',
        'created_by',
        'source_id'
    ];

    /**
     * Get today's word (cached for the day)
     */
    public function getToday(): ?array
    {
        $cacheKey = 'daily_word_' . date('Y-m-d');
        $cached   = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        // First, try to get a word scheduled for today
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE display_date = CURDATE() AND is_active = 1
            LIMIT 1"
        );
        $stmt->execute();
        $word = $stmt->fetch();

        if (!$word) {
            $word = $this->getRandom();
        }

        if ($word) {
            // Cache until midnight
            $ttl = strtotime('tomorrow') - time();
            Cache::set($cacheKey, $word, $ttl);
        }

        return $word ?: null;
    }

    /**
     * Get a random active word without ORDER BY RAND()
     */
    public function getRandom(): ?array
    {
        $count = $this->countActive();
        if ($count === 0) return null;

        $offset = rand(0, $count - 1);
        $stmt   = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE is_active = 1 LIMIT 1 OFFSET ?"
        );
        $stmt->execute([$offset]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all active words
     */
    public function getActive(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE is_active = 1
            ORDER BY tiv_word ASC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get words by part of speech
     */
    public function getByPartOfSpeech(string $partOfSpeech, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE part_of_speech = ? AND is_active = 1
            ORDER BY tiv_word ASC
            LIMIT ?"
        );
        $stmt->execute([$partOfSpeech, $limit]);
        return $stmt->fetchAll();
    }

    	/**
	 * Search words
	 */
	public function search(string $query, array $fields = [], int $limit = 50): array
	{
	    // Enforce DailyWord-specific searchable fields
	    if (empty($fields)) {
		$fields = ['tiv_word', 'english_meaning', 'alternate_meaning'];
	    }

	    return parent::search($query, $fields, $limit);
	}


    /**
     * Get all alphabetically
     */
    public function getAllAlphabetically(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            ORDER BY tiv_word ASC
            LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get words starting with letter
     */
    public function getByLetter(string $letter, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE tiv_word LIKE ? AND is_active = 1
            ORDER BY tiv_word ASC
            LIMIT ?"
        );
        $stmt->execute([$letter . '%', $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Schedule word for a date
     */
    public function scheduleForDate(int $id, string $date): bool
    {
        return $this->update($id, ['display_date' => $date]);
    }

    /**
     * Get words derived from a given root word
     */
    public function getDerivedWords(int $rootId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
            WHERE root_word_id = ? AND is_active = 1
            ORDER BY tiv_word ASC
            LIMIT ?"
        );
        $stmt->execute([$rootId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Count active words
     */
    public function countActive(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE is_active = 1"
        );
        return (int) $stmt->fetchColumn();
    }
}
