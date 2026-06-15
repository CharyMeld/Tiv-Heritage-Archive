<?php
/**
 * Tiv Culture Archive - Base Model
 * All models extend this class
 */

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Find record by field
     */
    public function findBy(string $field, $value): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$field} = ? LIMIT 1"
        );
        $stmt->execute([$value]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Get all records
     */
    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}"
        );
        return $stmt->fetchAll();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $limit, int $offset, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Count all records
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if a record with the given field value already exists
     */
    public function existsByField(string $field, string $value, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM {$this->table} WHERE {$field} = ? AND {$this->primaryKey} != ? LIMIT 1"
            );
            $stmt->execute([$value, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM {$this->table} WHERE {$field} = ? LIMIT 1"
            );
            $stmt->execute([$value]);
        }
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Count records by condition
     */
    public function countWhere(string $field, $value): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$field} = ?"
        );
        $stmt->execute([$value]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Create new record
     */
    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $fields = array_map(fn($field) => "{$field} = ?", array_keys($data));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $fields),
            $this->primaryKey
        );

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Get featured records (random subset without ORDER BY RAND())
     */
    public function featured(int $limit = 5): array
    {
        $count = (int) $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE is_featured = 1"
        )->fetchColumn();

        if ($count === 0) return [];

        $offset = $count > $limit ? rand(0, $count - $limit) : 0;

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE is_featured = 1 LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Full text search with LIKE fallback when no FULLTEXT index exists
     */
    public function search(string $query, array $fields, int $limit = 50): array
    {
        $fieldList = implode(', ', $fields);
        try {
            $stmt = $this->db->prepare(
                "SELECT *, MATCH({$fieldList}) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance
                FROM {$this->table}
                WHERE MATCH({$fieldList}) AGAINST(? IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC
                LIMIT ?"
            );
            $stmt->execute([$query, $query, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // FULLTEXT index missing — fall back to LIKE search
            if (str_contains($e->getMessage(), '1191') || str_contains($e->getMessage(), 'FULLTEXT')) {
                return $this->searchLike($query, $fields, $limit);
            }
            throw $e;
        }
    }

    /**
     * Simple LIKE search
     */
    public function searchLike(string $query, array $fields, int $limit = 50): array
    {
        $conditions = array_map(fn($field) => "{$field} LIKE ?", $fields);
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s LIMIT ?",
            $this->table,
            implode(' OR ', $conditions)
        );

        $searchTerm = "%{$query}%";
        $params = array_fill(0, count($fields), $searchTerm);
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Get most viewed
     */
    public function mostViewed(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY view_count DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent records
     */
    public function recent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Execute raw query
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute raw statement
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
