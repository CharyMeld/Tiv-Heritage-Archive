<?php

require_once BASE_PATH . '/core/Model.php';

class TivGrammarRule extends Model
{
    protected string $table = 'tiv_grammar_rules';

    protected array $fillable = [
        'category',
        'title',
        'summary',
        'explanation',
        'examples',
        'source_note',
        'sort_order',
    ];

    /** Fixed display order for the accordion sections */
    public const CATEGORIES = ['noun', 'pronoun', 'verb', 'adjective', 'sentence_structure', 'question_formation'];

    /** Get all rules in one category, ordered by sort_order then id */
    public function getByCategory(string $category): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE category = ?
             ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([$category]);
        return array_map([$this, 'decodeExamples'], $stmt->fetchAll());
    }

    /** Get all rules grouped by category, in CATEGORIES order */
    public function getAllGrouped(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY FIELD(category, '" . implode("','", self::CATEGORIES) . "'), sort_order ASC, id ASC"
        );
        $rows = $stmt->fetchAll();

        $grouped = array_fill_keys(self::CATEGORIES, []);
        foreach ($rows as $row) {
            $grouped[$row['category']][] = $this->decodeExamples($row);
        }
        return $grouped;
    }

    /** Total count per category */
    public function countByCategory(string $category): int
    {
        return $this->countWhere('category', $category);
    }

    /** True if the table has any rows at all */
    public function hasData(): bool
    {
        return $this->count() > 0;
    }

    public function create(array $data): int
    {
        return parent::create($this->encodeExamples($data));
    }

    public function update(int $id, array $data): bool
    {
        return parent::update($id, $this->encodeExamples($data));
    }

    private function encodeExamples(array $data): array
    {
        if (isset($data['examples']) && is_array($data['examples'])) {
            $data['examples'] = json_encode(array_values($data['examples']), JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }

    private function decodeExamples(array $row): array
    {
        $row['examples'] = json_decode($row['examples'] ?? '', true) ?: [];
        return $row;
    }
}
