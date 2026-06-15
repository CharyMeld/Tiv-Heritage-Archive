<?php
/**
 * Translation Phrase Model
 * Common sentence pairs and expressions.
 */

class TranslationPhrase extends Model
{
    protected string $table = 'translation_phrases';
    protected array $fillable = [
        'source_text', 'source_language', 'target_text', 'target_language',
        'context_tag', 'confidence_score', 'status', 'created_by',
    ];

    public function active(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE status = 'active'
             ORDER BY context_tag ASC, id ASC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        return $this->countWhere('status', 'active');
    }

    public function adminList(array $filters, int $limit, int $offset): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['source_language'])) {
            $where[]  = 'source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['context_tag'])) {
            $where[]  = 'context_tag = ?';
            $params[] = $filters['context_tag'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(source_text LIKE ? OR target_text LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             {$whereClause}
             ORDER BY source_language ASC, context_tag ASC, id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function adminCount(array $filters): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['source_language'])) {
            $where[]  = 'source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['context_tag'])) {
            $where[]  = 'context_tag = ?';
            $params[] = $filters['context_tag'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(source_text LIKE ? OR target_text LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function stats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*)                                              AS total,
                SUM(status = 'active')                               AS active,
                SUM(status = 'inactive')                             AS inactive,
                SUM(source_language = 'tiv')                        AS tiv_source,
                SUM(source_language = 'english')                     AS english_source,
                ROUND(AVG(confidence_score), 1)                     AS avg_confidence,
                SUM(source_text LIKE '% %')                          AS multi_word
             FROM {$this->table}"
        );
        return $stmt->fetch() ?: [];
    }

    public function allContextTags(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT context_tag FROM {$this->table}
             WHERE context_tag IS NOT NULL
             ORDER BY context_tag ASC"
        );
        return array_column($stmt->fetchAll(), 'context_tag');
    }
}
