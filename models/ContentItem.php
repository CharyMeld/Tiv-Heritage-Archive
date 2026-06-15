<?php

require_once BASE_PATH . '/core/Model.php';

class ContentItem extends Model
{
    protected string $table = 'content_items';

    protected array $fillable = [
        'section', 'subcategory',
        'title', 'tiv_title',
        'excerpt', 'tiv_excerpt',
        'content', 'tiv_content',
        'media_file', 'media_type',
        'is_featured', 'status', 'created_by',
    ];

    public function getBySubcategory(string $section, string $sub, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE section = ? AND subcategory = ? AND status = 'published'
             ORDER BY is_featured DESC, created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$section, $sub, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countBySubcategory(string $section, string $sub): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE section = ? AND subcategory = ? AND status = 'published'"
        );
        $stmt->execute([$section, $sub]);
        return (int) $stmt->fetchColumn();
    }

    public function searchInSubcategory(string $section, string $sub, string $q): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE section = ? AND subcategory = ? AND status = 'published'
               AND (title LIKE ? OR tiv_title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
             ORDER BY is_featured DESC, created_at DESC
             LIMIT 50"
        );
        $stmt->execute([$section, $sub, $like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function getFeatured(string $section, string $sub, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE section = ? AND subcategory = ? AND is_featured = 1 AND status = 'published'
             ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$section, $sub, $limit]);
        return $stmt->fetchAll();
    }

    public function adminList(string $section, string $sub, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT ci.*, u.name AS author_name
             FROM {$this->table} ci
             LEFT JOIN users u ON u.id = ci.created_by
             WHERE ci.section = ? AND ci.subcategory = ?
             ORDER BY ci.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$section, $sub, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function adminCount(string $section, string $sub): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE section = ? AND subcategory = ?"
        );
        $stmt->execute([$section, $sub]);
        return (int) $stmt->fetchColumn();
    }

    /** Label shown in admin/UI for a subcategory key */
    public static function subcategoryLabel(string $sub): string
    {
        $map = [
            'alphabet'           => 'Alphabet',
            'folktales'          => 'Folktales',
            'stories'            => 'Stories',
            'poems'              => 'Poems',
            'traditions'         => 'Traditions',
            'attire'             => 'Attire',
            'marriage-customs'   => 'Marriage Customs',
            'origins'            => 'Origins',
            'migration'          => 'Migration',
            'historical-figures' => 'Historical Figures',
            'timeline'           => 'Timeline',
            'documents'          => 'Documents',
            'audio'              => 'Audio Recordings',
            'publications'       => 'Research Publications',
        ];
        return $map[$sub] ?? ucfirst($sub);
    }
}
