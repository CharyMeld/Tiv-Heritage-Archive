<?php
/**
 * Source Model — References & Contributors
 */

require_once BASE_PATH . '/core/Model.php';

class Source extends Model
{
    protected string $table = 'sources';

    protected array $fillable = [
        'source_type', 'title', 'author', 'contributor_name',
        'location', 'year_recorded', 'notes'
    ];

    /**
     * Source type labels
     */
    public static array $typeLabels = [
        'book'                 => 'Books & Publications',
        'research'             => 'Academic Research',
        'oral_tradition'       => 'Oral Traditions',
        'interview'            => 'Interviews',
        'community_submission' => 'Community Contributors',
    ];

    /**
     * Source type icons
     */
    public static array $typeIcons = [
        'book'                 => '&#128218;',
        'research'             => '&#128300;',
        'oral_tradition'       => '&#127897;',
        'interview'            => '&#127908;',
        'community_submission' => '&#128101;',
    ];

    /**
     * Get all sources ordered by type then contributor name
     */
    public function getAllOrdered(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY FIELD(source_type,'book','research','oral_tradition','interview','community_submission'),
             contributor_name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Get sources grouped by type
     */
    public function getGroupedByType(): array
    {
        $rows = $this->getAllOrdered();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['source_type']][] = $row;
        }
        return $grouped;
    }

    /**
     * Get sources of a specific type
     */
    public function getByType(string $type): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE source_type = ? ORDER BY contributor_name ASC"
        );
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    /**
     * Count contributors (community submissions)
     */
    public function countContributors(): int
    {
        return $this->countWhere('source_type', 'community_submission');
    }
}
