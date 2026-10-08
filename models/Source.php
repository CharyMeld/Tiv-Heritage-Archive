<?php
/**
 * Source Model — References & Contributors
 */

require_once BASE_PATH . '/core/Model.php';

class Source extends Model
{
    protected string $table = 'sources';

    protected array $fillable = [
        'source_type', 'title', 'author', 'publisher', 'isbn', 'doi', 'url', 'access_date',
        'contributor_name', 'location', 'year_recorded', 'notes', 'verification_status',
        'organisation', 'publication_date', 'publication_details', 'archive_reference',
        'source_kind', 'source_tier', 'lineage_group', 'rights_notes',
    ];

    /**
     * Public-facing verification status: badge + short, professional
     * explanation. Deliberately separate from the internal `notes`
     * field, which may contain researcher-facing caveats and is never
     * shown to public users.
     */
    public static array $verificationInfo = [
        'verified' => [
            'label' => 'Verified',
            'icon' => '&#128994;', // 🟢
            'class' => 'is-verified',
            'description' => 'This reference has been corroborated by multiple independent sources.',
        ],
        'needs_corroboration' => [
            'label' => 'Needs Corroboration',
            'icon' => '&#128993;', // 🟡
            'class' => 'is-needs-corroboration',
            'description' => 'This reference is currently supported by a single source. Additional corroboration is being sought.',
        ],
        'disputed' => [
            'label' => 'Disputed',
            'icon' => '&#128308;', // 🔴
            'class' => 'is-disputed',
            'description' => 'Sources consulted for this reference present conflicting information. Further research is underway to resolve the discrepancy.',
        ],
    ];

    public static function verificationInfo(?string $status): array
    {
        return self::$verificationInfo[$status] ?? self::$verificationInfo['needs_corroboration'];
    }

    /**
     * Source type labels
     */
    public static array $typeLabels = [
        'book'                 => 'Books & Publications',
        'research'             => 'Academic Research',
        'oral_tradition'       => 'Oral Traditions',
        'interview'            => 'Interviews',
        'community_submission' => 'Community Contributors',
        'journal_article'        => 'Journal Articles',
        'government_publication' => 'Government Publications',
        'official_website'       => 'Official Websites',
        'website'                => 'Websites',
        'news'                   => 'News Reports',
        'encyclopedia'           => 'Encyclopedias',
        'archival_record'        => 'Archival Records',
        'dataset'                => 'Datasets',
        'map'                    => 'Maps',
        'thesis'                 => 'Theses & Dissertations',
        'other'                  => 'Other Sources',
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
        'journal_article'        => '&#128240;',
        'government_publication' => '&#127963;',
        'official_website'       => '&#127760;',
        'website'                => '&#127760;',
        'news'                   => '&#128240;',
        'encyclopedia'           => '&#128214;',
        'archival_record'        => '&#128220;',
        'dataset'                => '&#128202;',
        'map'                    => '&#128506;',
        'thesis'                 => '&#127891;',
        'other'                  => '&#128196;',
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
    /**
     * May visitors see this source? False only for sources cited solely by
     * unpublished Nigeria Heritage research (see HeritagePublic::sourceIsPublicSql).
     */
    public function isPublic(int $id): bool
    {
        require_once BASE_PATH . '/services/HeritagePublic.php';
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} s WHERE s.id = ? AND " . HeritagePublic::sourceIsPublicSql('s'));
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    /** Sources visitors may see, grouped by type (public References page). */
    public function getPublicGroupedByType(): array
    {
        require_once BASE_PATH . '/services/HeritagePublic.php';
        $rows = $this->db->query(
            "SELECT s.* FROM {$this->table} s WHERE " . HeritagePublic::sourceIsPublicSql('s') . "
             ORDER BY FIELD(s.source_type,'book','research','oral_tradition','interview','community_submission'), s.contributor_name ASC"
        )->fetchAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['source_type']][] = $row;
        }
        return $grouped;
    }

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
