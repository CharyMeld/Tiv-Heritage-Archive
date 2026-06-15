<?php

require_once BASE_PATH . '/core/Model.php';

class TivAlphabetEntry extends Model
{
    protected string $table = 'tiv_alphabet';

    protected array $fillable = [
        'type',
        'letter',
        'english_letter',
        'ipa',
        'sound_desc',
        'tiv_example',
        'english_meaning',
        'audio_file',
        'sort_order',
    ];

    /** Get all entries of one type ordered by sort_order, then id */
    public function getByType(string $type): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE type = ?
             ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    /** Get all entries grouped by type */
    public function getAllGrouped(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY FIELD(type,'plain','vowel','consonant','digraph','tonal'), sort_order ASC, id ASC"
        );
        $rows = $stmt->fetchAll();

        $grouped = ['plain' => [], 'vowel' => [], 'consonant' => [], 'digraph' => [], 'tonal' => []];
        foreach ($rows as $row) {
            $grouped[$row['type']][] = $row;
        }
        return $grouped;
    }

    /** Total count per type */
    public function countByType(string $type): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE type = ?"
        );
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn();
    }

    /** True if the table has any rows at all */
    public function hasData(): bool
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM {$this->table}"
        )->fetchColumn() > 0;
    }
}
