<?php
/**
 * Translation Rule Model
 * Grammar and phrase correction rules applied after assembly.
 */

class TranslationRule extends Model
{
    protected string $table = 'translation_rules';
    protected array $fillable = [
        'rule_name', 'source_language', 'target_language',
        'pattern_text', 'replacement_text', 'rule_type',
        'priority_score', 'status', 'created_by',
    ];

    public function activeRules(string $sourceLang, string $targetLang): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE source_language = ?
               AND target_language = ?
               AND status = 'active'
             ORDER BY priority_score DESC"
        );
        $stmt->execute([$sourceLang, $targetLang]);
        return $stmt->fetchAll();
    }

    public function adminList(array $filters, int $limit, int $offset): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['source_language'])) {
            $where[]  = 'source_language = ?';
            $params[] = $filters['source_language'];
        }
        if (!empty($filters['rule_type'])) {
            $where[]  = 'rule_type = ?';
            $params[] = $filters['rule_type'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(rule_name LIKE ? OR pattern_text LIKE ? OR replacement_text LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             {$whereClause}
             ORDER BY priority_score DESC, id DESC
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
        if (!empty($filters['rule_type'])) {
            $where[]  = 'rule_type = ?';
            $params[] = $filters['rule_type'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(rule_name LIKE ? OR pattern_text LIKE ? OR replacement_text LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
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
}
