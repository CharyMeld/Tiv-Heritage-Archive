<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingPromptTemplate extends Model
{
    protected string $table = 'marketing_prompt_templates';

    protected array $fillable = [
        'name', 'category', 'platform', 'system_prompt', 'user_prompt_template',
        'is_default', 'is_active', 'created_by',
    ];

    public const CATEGORIES = [
        'word'              => 'Word of the Day',
        'proverb'           => 'Proverb',
        'name'              => 'Name',
        'plant'             => 'Plant',
        'festival'          => 'Festival',
        'food'              => 'Traditional Food',
        'animal'            => 'Animal',
        'grammar'           => 'Grammar Rule',
        'history'           => 'Timeline Event',
        'historical_figure' => 'Historical Figure',
        'lesson'            => 'Learning Video',
        'reference'         => 'Source',
        'general'           => 'General',
    ];

    public const PLATFORMS = [
        'any'         => 'Any / Master Content',
        'facebook'    => 'Facebook',
        'instagram'   => 'Instagram',
        'x'           => 'X (Twitter)',
        'linkedin'    => 'LinkedIn',
        'telegram'    => 'Telegram',
        'whatsapp'    => 'WhatsApp',
        'newsletter'  => 'Newsletter',
    ];

    public function getAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['platform'])) {
            $where[] = 'platform = ?';
            $params[] = $filters['platform'];
        }

        $sql = "SELECT t.*, u.name AS creator_name
                FROM {$this->table} t
                LEFT JOIN users u ON u.id = t.created_by";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY category ASC, platform ASC, name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findDefaultFor(string $category, string $platform): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE category = ? AND platform = ? AND is_default = 1 AND is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$category, $platform]);
        $row = $stmt->fetch();
        if ($row) return $row;

        // Fall back to a "general"/"any" default if no category+platform-specific one exists.
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE category = 'general' AND platform = ? AND is_default = 1 AND is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$platform]);
        return $stmt->fetch() ?: null;
    }

    public function setDefault(int $id): void
    {
        $row = $this->find($id);
        if (!$row) return;

        $this->db->prepare(
            "UPDATE {$this->table} SET is_default = 0 WHERE category = ? AND platform = ?"
        )->execute([$row['category'], $row['platform']]);

        $this->db->prepare(
            "UPDATE {$this->table} SET is_default = 1 WHERE id = ?"
        )->execute([$id]);
    }
}
