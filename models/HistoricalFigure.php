<?php
/**
 * Historical Figure Model
 */

require_once BASE_PATH . '/core/Model.php';

class HistoricalFigure extends Model
{
    protected string $table = 'historical_figures';
    protected ?string $publicCollection = 'tiv';

    protected array $fillable = [
        'english_name', 'tiv_name', 'title', 'slug', 'category', 'subcategory', 'reign_order',
        'gender', 'date_of_birth', 'birth_year', 'place_of_birth', 'date_of_death', 'death_year', 'burial_place',
        'clan', 'district', 'local_government', 'state', 'country', 'religion', 'occupation', 'historical_period',
        'short_summary', 'biography', 'early_life', 'education', 'career', 'leadership_service',
        'achievements', 'historical_significance', 'legacy', 'timeline_notes',
        'image', 'references_text', 'source_id', 'status', 'is_featured', 'created_by',
    ];

    public const CATEGORIES = [
        'Traditional Leadership',
        'Warriors & Military Leaders',
        'Freedom Fighters & Resistance Leaders',
        'Political Leaders',
        'Judges & Legal Figures',
        'Scholars & Academics',
        'Religious Leaders',
        'Cultural Icons',
        'Artists & Craftsmen',
        'Musicians & Performing Artists',
        'Writers & Authors',
        'Journalists & Media Personalities',
        'Athletes',
        'Business Leaders & Entrepreneurs',
        'Women of Influence',
        'Tiv in the Diaspora',
        'Other Notable Personalities',
    ];

    public const TRADITIONAL_SUBCATEGORIES = [
        'Tor Tiv', 'Ator', 'Uter', 'Third Class Chiefs', 'Clan Heads', 'Tor-Kpande',
    ];

    public const HISTORICAL_PERIODS = [
        'Pre-Colonial Era', 'Colonial Era', 'Post-Colonial Era',
        'First Republic', 'Military Era', 'Fourth Republic', 'Contemporary Era',
    ];

    public const CATEGORY_ICONS = [
        'Traditional Leadership'                 => '&#128081;',
        'Warriors & Military Leaders'             => '&#9876;&#65039;',
        'Freedom Fighters & Resistance Leaders'   => '&#128737;&#65039;',
        'Political Leaders'                       => '&#127963;&#65039;',
        'Judges & Legal Figures'                   => '&#9878;&#65039;',
        'Scholars & Academics'                     => '&#128218;',
        'Religious Leaders'                        => '&#9962;',
        'Cultural Icons'                           => '&#127917;',
        'Artists & Craftsmen'                      => '&#127912;',
        'Musicians & Performing Artists'           => '&#127925;',
        'Writers & Authors'                        => '&#9997;&#65039;',
        'Journalists & Media Personalities'        => '&#128240;',
        'Athletes'                                 => '&#127941;',
        'Business Leaders & Entrepreneurs'         => '&#128188;',
        'Women of Influence'                       => '&#128105;',
        'Tiv in the Diaspora'                      => '&#127757;',
        'Other Notable Personalities'              => '&#11088;',
    ];

    public const CATEGORY_DESCRIPTIONS = [
        'Traditional Leadership'                 => 'Traditional rulers and custodians of Tiv customs and governance.',
        'Warriors & Military Leaders'             => 'Military heroes and defenders of Tivland.',
        'Freedom Fighters & Resistance Leaders'   => 'Leaders of resistance against colonial and oppressive rule.',
        'Political Leaders'                       => 'Politicians and public servants.',
        'Judges & Legal Figures'                   => 'Jurists and lawyers who shaped justice among the Tiv.',
        'Scholars & Academics'                     => 'Educators and researchers who advanced Tiv scholarship.',
        'Religious Leaders'                        => 'Clergy and faith leaders who shaped religious life in Tivland.',
        'Cultural Icons'                           => 'Custodians and icons of Tiv cultural identity.',
        'Artists & Craftsmen'                      => 'Visual artists and skilled craftsmen preserving Tiv artistry.',
        'Musicians & Performing Artists'           => 'Musicians and performers who carried Tiv sound and dance.',
        'Writers & Authors'                        => 'Novelists, poets, and authors documenting Tiv life and language.',
        'Journalists & Media Personalities'        => 'Broadcasters and journalists who informed the Tiv public.',
        'Athletes'                                 => 'Sportsmen and sportswomen who represented Tivland with distinction.',
        'Business Leaders & Entrepreneurs'         => 'Entrepreneurs and business leaders who built economic legacies.',
        'Women of Influence'                       => 'Women whose leadership and achievements shaped Tiv society.',
        'Tiv in the Diaspora'                      => 'Tiv sons and daughters who made their mark beyond Nigeria.',
        'Other Notable Personalities'              => 'Other notable Tiv individuals not captured above.',
    ];

    public const SUBCATEGORY_ICONS = [
        'Tor Tiv'                            => '&#128081;',
        'Ator'                               => '&#128081;',
        'Uter'                               => '&#128081;',
        'Third Class Chiefs & Clan Heads'    => '&#128081;',
        'Tor-Kpande'                         => '&#128081;',
    ];

    /**
     * Public-browse subcategory *cards* — groups the two DB enum values the
     * mockup shows as a single tile. Purely presentational: the DB enum and
     * admin CRUD form still use TRADITIONAL_SUBCATEGORIES (6, ungrouped).
     */
    public const TRADITIONAL_SUBCATEGORY_GROUPS = [
        'Tor Tiv'                          => ['Tor Tiv'],
        'Ator'                             => ['Ator'],
        'Uter'                             => ['Uter'],
        'Third Class Chiefs & Clan Heads'  => ['Third Class Chiefs', 'Clan Heads'],
        'Tor-Kpande'                       => ['Tor-Kpande'],
    ];

    /** Whitelisted equality-filter columns (prevents SQL injection via dynamic WHERE) */
    private const FILTERABLE_COLUMNS = [
        'category', 'subcategory', 'clan', 'district', 'local_government',
        'state', 'gender', 'occupation', 'religion', 'historical_period',
    ];

    public function search(string $query, array $fields = [], int $limit = 50): array
    {
        if (empty($fields)) {
            $fields = ['english_name', 'tiv_name', 'title', 'short_summary', 'biography', 'achievements', 'legacy'];
        }
        return parent::search($query, $fields, $limit);
    }

    /**
     * Public/admin filtered browse.
     * $filters keys: any of FILTERABLE_COLUMNS, plus 'title' (LIKE), 'birth_year', 'death_year', 'status'.
     */
    public function filter(array $filters, int $limit, int $offset, ?string $orderBy = null, string $direction = 'ASC'): array
    {
        [$where, $params] = $this->buildWhere($filters);

        if ($orderBy !== null) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $orderBy = in_array($orderBy, ['english_name', 'birth_year', 'death_year', 'created_at'], true) ? $orderBy : 'english_name';
            $orderClause = "{$orderBy} {$direction}";
        } else {
            // Default: figures with a reign_order (e.g. successive Tor Tiv holders) sort
            // newest reign first (III, II, I); everything else falls back to alphabetical.
            $orderClause = '(reign_order IS NULL) ASC, reign_order DESC, english_name ASC';
        }

        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY {$orderClause} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ── Slugs (computed on the fly — no persisted slug column needed) ─── */

    public static function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    /** e.g. 'political-leaders' -> 'Political Leaders', or null if no match */
    public static function categoryFromSlug(string $slug): ?string
    {
        foreach (self::CATEGORIES as $cat) {
            if (self::slugify($cat) === $slug) return $cat;
        }
        return null;
    }

    /** e.g. 'third-class-chiefs-clan-heads' -> ['label'=>..., 'values'=>[...]] */
    public static function subcategoryGroupFromSlug(string $slug): ?array
    {
        foreach (self::TRADITIONAL_SUBCATEGORY_GROUPS as $label => $values) {
            if (self::slugify($label) === $slug) return ['label' => $label, 'values' => $values];
        }
        return null;
    }

    /** Maps a raw DB subcategory value (e.g. 'Clan Heads') to its display group label */
    public static function subcategoryGroupLabelFor(?string $dbValue): ?string
    {
        if (!$dbValue) return null;
        foreach (self::TRADITIONAL_SUBCATEGORY_GROUPS as $label => $values) {
            if (in_array($dbValue, $values, true)) return $label;
        }
        return $dbValue;
    }

    /* ── Counts (published only) ─────────────────────────────────────── */

    /** All 17 category keys present, defaulting to 0 */
    public function countByCategory(): array
    {
        $stmt = $this->db->query(
            "SELECT category, COUNT(*) c FROM {$this->table} WHERE status = 'published' AND {$this->collectionScope()} GROUP BY category"
        );
        $raw = array_column($stmt->fetchAll(), 'c', 'category');
        $out = [];
        foreach (self::CATEGORIES as $cat) { $out[$cat] = (int) ($raw[$cat] ?? 0); }
        return $out;
    }

    /** All 5 display-group keys, summed from their underlying raw DB values */
    public function countBySubcategoryGroup(): array
    {
        $stmt = $this->db->prepare(
            "SELECT subcategory, COUNT(*) c FROM {$this->table}
             WHERE category = 'Traditional Leadership' AND status = 'published' AND {$this->collectionScope()}
             GROUP BY subcategory"
        );
        $stmt->execute();
        $raw = array_column($stmt->fetchAll(), 'c', 'subcategory');
        $out = [];
        foreach (self::TRADITIONAL_SUBCATEGORY_GROUPS as $label => $values) {
            $out[$label] = array_sum(array_map(fn($v) => (int) ($raw[$v] ?? 0), $values));
        }
        return $out;
    }

    /* ── Scoped search (category and/or subcategory), always published-only ── */

    private function buildSearchWhere(string $query, array $scope): array
    {
        $fields = ['english_name', 'tiv_name', 'title', 'short_summary', 'biography', 'achievements', 'legacy'];
        $like = '%' . $query . '%';
        $clauses = ['status = ?', '(' . implode(' OR ', array_map(fn($f) => "{$f} LIKE ?", $fields)) . ')',
                    $this->collectionScope()];
        $params = ['published'];
        foreach ($fields as $f) { $params[] = $like; }

        if (!empty($scope['category'])) {
            $clauses[] = 'category = ?';
            $params[] = $scope['category'];
        }
        if (!empty($scope['subcategory'])) {
            if (is_array($scope['subcategory'])) {
                $ph = implode(',', array_fill(0, count($scope['subcategory']), '?'));
                $clauses[] = "subcategory IN ({$ph})";
                foreach ($scope['subcategory'] as $v) { $params[] = $v; }
            } else {
                $clauses[] = 'subcategory = ?';
                $params[] = $scope['subcategory'];
            }
        }
        return ['WHERE ' . implode(' AND ', $clauses), $params];
    }

    /** $scope: optional ['category'=>string, 'subcategory'=>string|string[]] */
    public function searchScoped(string $query, array $scope, int $limit = 12, int $offset = 0): array
    {
        [$where, $params] = $this->buildSearchWhere($query, $scope);
        $sql = "SELECT * FROM {$this->table} {$where}
                ORDER BY (reign_order IS NULL) ASC, reign_order DESC, english_name ASC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countSearchScoped(string $query, array $scope): int
    {
        [$where, $params] = $this->buildSearchWhere($query, $scope);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildWhere(array $filters): array
    {
        $clauses = [$this->collectionScope()];
        $params = [];

        foreach (self::FILTERABLE_COLUMNS as $col) {
            if (!empty($filters[$col])) {
                if (is_array($filters[$col])) {
                    $placeholders = implode(',', array_fill(0, count($filters[$col]), '?'));
                    $clauses[] = "{$col} IN ({$placeholders})";
                    foreach ($filters[$col] as $v) { $params[] = $v; }
                } else {
                    $clauses[] = "{$col} = ?";
                    $params[] = $filters[$col];
                }
            }
        }
        if (!empty($filters['title'])) {
            $clauses[] = 'title LIKE ?';
            $params[] = '%' . $filters['title'] . '%';
        }
        if (!empty($filters['birth_year'])) {
            $clauses[] = 'birth_year = ?';
            $params[] = (int) $filters['birth_year'];
        }
        if (!empty($filters['death_year'])) {
            $clauses[] = 'death_year = ?';
            $params[] = (int) $filters['death_year'];
        }
        if (!empty($filters['status'])) {
            $clauses[] = 'status = ?';
            $params[] = $filters['status'];
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return [$where, $params];
    }

    /** Distinct values for a filter dropdown — column name whitelisted against FILTERABLE_COLUMNS */
    public function getDistinctValues(string $column): array
    {
        if (!in_array($column, self::FILTERABLE_COLUMNS, true)) return [];
        $stmt = $this->db->query(
            "SELECT DISTINCT {$column} FROM {$this->table} WHERE {$column} IS NOT NULL AND {$column} != '' AND status = 'published' AND {$this->collectionScope()} ORDER BY {$column} ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Convert an ordinal position (1, 2, 3...) to a Roman numeral for display, e.g. "Tor Tiv III" */
    public static function toRoman(int $num): string
    {
        if ($num <= 0) return '';
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];
        $result = '';
        foreach ($map as $value => $symbol) {
            while ($num >= $value) {
                $result .= $symbol;
                $num -= $value;
            }
        }
        return $result;
    }

    /** e.g. "Tor Tiv III" for subcategory="Tor Tiv", reign_order=3 */
    public static function ordinalLabel(array $item): string
    {
        if (empty($item['subcategory']) || empty($item['reign_order'])) return '';
        return $item['subcategory'] . ' ' . self::toRoman((int) $item['reign_order']);
    }

    public function getRelated(int $id, int $limit = 4): array
    {
        $figure = $this->find($id);
        if (!$figure) return [];
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE id != ? AND category = ? AND status = 'published' AND {$this->collectionScope()}
             ORDER BY id DESC LIMIT ?"
        );
        $stmt->execute([$id, $figure['category'], $limit]);
        return $stmt->fetchAll();
    }

    /* ── Gallery (mirrors TivFestival) ───────────────────────── */

    public function getGallery(int $figureId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM historical_figure_gallery WHERE figure_id = ? ORDER BY is_featured DESC, sort_order ASC, id ASC"
        );
        $stmt->execute([$figureId]);
        return $stmt->fetchAll();
    }

    public function getGalleryPhoto(int $photoId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM historical_figure_gallery WHERE id = ?');
        $stmt->execute([$photoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function addGalleryPhoto(int $figureId, string $filename, ?string $caption, ?string $altText, int $isFeatured, int $uploadedBy): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO historical_figure_gallery (figure_id, image_path, caption, alt_text, is_featured, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$figureId, $filename, $caption, $altText, $isFeatured, $uploadedBy]);
        return (int) $this->db->lastInsertId();
    }

    public function removeGalleryPhoto(int $photoId, int $figureId): void
    {
        $stmt = $this->db->prepare('DELETE FROM historical_figure_gallery WHERE id = ? AND figure_id = ?');
        $stmt->execute([$photoId, $figureId]);
    }

    /** Every published record (newsletter popularity candidates). */
    public function allPublished(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} WHERE status = 'published' AND {$this->collectionScope()}")->fetchAll();
    }
}
