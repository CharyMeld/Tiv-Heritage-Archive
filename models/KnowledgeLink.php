<?php
/**
 * KnowledgeLink Model — Cultural Knowledge Graph
 */

require_once BASE_PATH . '/core/Model.php';

class KnowledgeLink extends Model
{
    protected string $table = 'knowledge_links';

    protected array $fillable = [
        'source_table', 'source_id', 'target_table', 'target_id', 'relation_type'
    ];

    /**
     * Maps DB table names to display config
     */
    public static array $tableConfig = [
        'tiv_names'    => ['label' => 'Names',      'icon' => '&#128100;', 'url' => 'name',     'title' => 'tiv_name',  'sub' => 'english_meaning'],
        'tiv_proverbs' => ['label' => 'Proverbs',   'icon' => '&#128221;', 'url' => 'proverb',  'title' => 'tiv_text',  'sub' => 'english_translation'],
        'tiv_plants'   => ['label' => 'Plants',     'icon' => '&#127807;', 'url' => 'plant',    'title' => 'tiv_name',  'sub' => 'english_name'],
        'tiv_festivals'=> ['label' => 'Festivals',  'icon' => '&#127881;', 'url' => 'festival', 'title' => 'tiv_name',  'sub' => 'english_name'],
        'tiv_foods'    => ['label' => 'Foods',      'icon' => '&#127858;', 'url' => 'food',     'title' => 'tiv_name',  'sub' => 'english_name'],
        'daily_words'  => ['label' => 'Dictionary', 'icon' => '&#128172;', 'url' => 'word',     'title' => 'tiv_word',  'sub' => 'english_meaning'],
        'tiv_animals'  => ['label' => 'Animals',    'icon' => '&#128062;', 'url' => 'animal',   'title' => 'tiv_name',  'sub' => 'name'],
    ];

    /**
     * Get all links FROM a given item
     */
    public function getLinksFrom(string $table, int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE source_table = ? AND source_id = ?
             ORDER BY relation_type"
        );
        $stmt->execute([$table, $id]);
        return $stmt->fetchAll();
    }

    /**
     * Get all links TO a given item
     */
    public function getLinksTo(string $table, int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE target_table = ? AND target_id = ?
             ORDER BY relation_type"
        );
        $stmt->execute([$table, $id]);
        return $stmt->fetchAll();
    }

    /**
     * Get all linked items (both directions) with their data
     * Uses batch queries per table instead of one query per item
     */
    public function getRelatedItems(string $table, int $id): array
    {
        $outgoing = $this->getLinksFrom($table, $id);
        $incoming = $this->getLinksTo($table, $id);

        // Group IDs by table so we can batch-fetch in one query per table
        $toFetch = [];

        foreach ($outgoing as $link) {
            $cfg = self::$tableConfig[$link['target_table']] ?? null;
            if (!$cfg) continue;
            $toFetch[$link['target_table']][$link['target_id']] = [
                'link_id'   => $link['id'],
                'relation'  => $link['relation_type'],
                'direction' => 'out',
                'cfg'       => $cfg,
            ];
        }

        foreach ($incoming as $link) {
            $cfg = self::$tableConfig[$link['source_table']] ?? null;
            if (!$cfg) continue;
            $toFetch[$link['source_table']][$link['source_id']] = [
                'link_id'   => $link['id'],
                'relation'  => $link['relation_type'],
                'direction' => 'in',
                'cfg'       => $cfg,
            ];
        }

        $results = [];

        // One query per distinct table instead of one per item
        foreach ($toFetch as $tbl => $items) {
            $ids          = array_keys($items);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt         = $this->db->prepare("SELECT * FROM {$tbl} WHERE id IN ({$placeholders})");
            $stmt->execute($ids);

            foreach ($stmt->fetchAll() as $row) {
                $meta      = $items[$row['id']];
                $results[] = [
                    'link_id'   => $meta['link_id'],
                    'relation'  => $meta['relation'],
                    'direction' => $meta['direction'],
                    'table'     => $tbl,
                    'cfg'       => $meta['cfg'],
                    'item'      => $row,
                ];
            }
        }

        return $results;
    }

    /**
     * Get a human-readable label for an item in any content table
     */
    public function getItemLabel(string $table, int $id): string
    {
        $cfg = self::$tableConfig[$table] ?? null;
        if (!$cfg) return "#{$id}";
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return "#{$id}";
        $titleField = $cfg['title'];
        return mb_substr($row[$titleField] ?? "#{$id}", 0, 60);
    }

    /**
     * Fetch a single item from any content table
     */
    private function fetchItem(string $table, int $id, array $cfg): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Add a link between two items
     */
    public function addLink(string $sourceTable, int $sourceId, string $targetTable, int $targetId, string $relationType): int
    {
        // Avoid duplicates
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table}
             WHERE source_table=? AND source_id=? AND target_table=? AND target_id=?"
        );
        $stmt->execute([$sourceTable, $sourceId, $targetTable, $targetId]);
        if ($stmt->fetch()) return 0;

        return $this->create([
            'source_table'  => $sourceTable,
            'source_id'     => $sourceId,
            'target_table'  => $targetTable,
            'target_id'     => $targetId,
            'relation_type' => $relationType,
        ]);
    }
}
