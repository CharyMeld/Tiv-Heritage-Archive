<?php
/**
 * Timeline Event Model
 */

require_once BASE_PATH . '/core/Model.php';

class TimelineEvent extends Model
{
    protected string $table = 'timeline_events';
    protected ?string $publicCollection = 'tiv';

    protected array $fillable = [
        'title', 'slug', 'event_date', 'start_date', 'end_date', 'year', 'is_estimated',
        'century', 'decade', 'era', 'short_summary', 'description', 'historical_significance',
        'causes', 'consequences', 'location', 'clan', 'latitude', 'longitude',
        'related_institutions', 'image', 'source_id', 'references_text', 'alternative_dates_notes',
        'confidence_score', 'status', 'is_featured', 'created_by',
    ];

    public const ERAS = [
        'Origins (Before 1600)',
        'Migration Era (1600-1750)',
        'Expansion Era (1750-1850)',
        'Pre-Colonial Era (1850-1900)',
        'Colonial Era (1900-1960)',
        'Post-Independence (1960-1999)',
        'Modern Era (2000-Present)',
    ];

    public const CATEGORIES = [
        'Migration', 'Politics', 'Leadership', 'Culture', 'Religion', 'Education',
        'Conflict', 'Diplomacy', 'Economy', 'Agriculture', 'Colonial Administration',
        'Traditional Governance', 'Language', 'Heritage', 'Archaeology',
    ];

    /** Whitelisted equality-filter columns (prevents SQL injection via dynamic WHERE) */
    private const FILTERABLE_COLUMNS = ['era', 'century', 'decade', 'clan', 'status'];

    /**
     * Maps a year to the fixed date-range era. Null years (undated
     * pre-colonial/oral-tradition content) default to "Origins".
     * Tie-break at the 1960 boundary: <=1959 Colonial, >=1960 Post-Independence
     * (Nigeria's own independence, Oct 1960, belongs to Post-Independence).
     */
    public static function eraForYear(?int $year): string
    {
        if ($year === null || $year < 1600) return 'Origins (Before 1600)';
        if ($year < 1750) return 'Migration Era (1600-1750)';
        if ($year < 1850) return 'Expansion Era (1750-1850)';
        if ($year < 1900) return 'Pre-Colonial Era (1850-1900)';
        if ($year < 1960) return 'Colonial Era (1900-1960)';
        if ($year < 2000) return 'Post-Independence (1960-1999)';
        return 'Modern Era (2000-Present)';
    }

    public function search(string $query, array $fields = [], int $limit = 50): array
    {
        if (empty($fields)) {
            $fields = ['title', 'short_summary', 'description', 'historical_significance'];
        }
        return parent::search($query, $fields, $limit);
    }

    /**
     * Public/admin filtered browse, chronological by default.
     * $filters keys: any of FILTERABLE_COLUMNS, plus 'title' (LIKE), 'year'.
     */
    public function filter(array $filters, int $limit, int $offset, ?string $orderBy = null, string $direction = 'ASC'): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = in_array($orderBy, ['title', 'year', 'created_at'], true) ? $orderBy : 'year';
        $orderClause = "(`{$orderBy}` IS NULL) ASC, `{$orderBy}` {$direction}";

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
        if (!empty($filters['year'])) {
            $clauses[] = 'year = ?';
            $params[] = (int) $filters['year'];
        }
        if (!empty($filters['category'])) {
            $categories = is_array($filters['category']) ? $filters['category'] : [$filters['category']];
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $clauses[] = "id IN (SELECT event_id FROM timeline_event_categories WHERE category IN ({$placeholders}))";
            foreach ($categories as $c) { $params[] = $c; }
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return [$where, $params];
    }

    /* ── Slugs (computed on the fly — no persisted-slug lookups needed) ─── */

    public static function slugify(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    /* ── Gallery (mirrors HistoricalFigure/TivFestival) ─────────────── */

    public function getGallery(int $eventId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM timeline_event_gallery WHERE event_id = ? ORDER BY is_featured DESC, sort_order ASC, id ASC"
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public function addGalleryImage(int $eventId, string $filename, ?string $caption, ?string $altText, string $mediaType, int $isFeatured, int $uploadedBy, ?string $photographer = null, ?string $copyright = null, ?string $license = null, ?string $sourceUrl = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO timeline_event_gallery
             (event_id, image_path, media_type, caption, alt_text, photographer, copyright, license, source_url, is_featured, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$eventId, $filename, $mediaType, $caption, $altText, $photographer, $copyright, $license, $sourceUrl, $isFeatured, $uploadedBy]);
        return (int) $this->db->lastInsertId();
    }

    public function removeGalleryImage(int $imageId, int $eventId): void
    {
        $stmt = $this->db->prepare('DELETE FROM timeline_event_gallery WHERE id = ? AND event_id = ?');
        $stmt->execute([$imageId, $eventId]);
    }

    /* ── Sources (many-to-many via timeline_event_sources) ──────────── */

    public function getSources(int $eventId): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.* FROM sources s
             INNER JOIN timeline_event_sources tes ON tes.source_id = s.id
             WHERE tes.event_id = ?
             ORDER BY s.source_type, s.title"
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public function addSource(int $eventId, int $sourceId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO timeline_event_sources (event_id, source_id) VALUES (?, ?)'
        );
        $stmt->execute([$eventId, $sourceId]);
    }

    public function removeSource(int $eventId, int $sourceId): void
    {
        $stmt = $this->db->prepare('DELETE FROM timeline_event_sources WHERE event_id = ? AND source_id = ?');
        $stmt->execute([$eventId, $sourceId]);
    }

    /* ── Categories (many-to-many via timeline_event_categories) ────── */

    public function getCategories(int $eventId): array
    {
        $stmt = $this->db->prepare(
            'SELECT category FROM timeline_event_categories WHERE event_id = ? ORDER BY category'
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Replaces an event's full category set with $categories (whitelisted against CATEGORIES). */
    public function setCategories(int $eventId, array $categories): void
    {
        $categories = array_values(array_intersect($categories, self::CATEGORIES));

        $del = $this->db->prepare('DELETE FROM timeline_event_categories WHERE event_id = ?');
        $del->execute([$eventId]);

        if (empty($categories)) return;

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO timeline_event_categories (event_id, category) VALUES (?, ?)'
        );
        foreach ($categories as $c) {
            $stmt->execute([$eventId, $c]);
        }
    }

    /** Distinct categories in use, for filter-panel checkboxes (published events only for public callers). */
    public function getCategoryCounts(bool $publishedOnly = true): array
    {
        $sql = "SELECT tec.category, COUNT(*) c
                FROM timeline_event_categories tec
                INNER JOIN timeline_events te ON te.id = tec.event_id
                WHERE {$this->collectionScope('te')}";
        if ($publishedOnly) $sql .= " AND te.status = 'published'";
        $sql .= " GROUP BY tec.category ORDER BY tec.category";

        $stmt = $this->db->query($sql);
        return array_column($stmt->fetchAll(), 'c', 'category');
    }

    /* ── Counts for filter chips (published only) ────────────────────── */

    /** All ERAS keys present, defaulting to 0 */
    public function countByEra(): array
    {
        $stmt = $this->db->query(
            "SELECT era, COUNT(*) c FROM {$this->table} WHERE status = 'published' AND era IS NOT NULL AND {$this->collectionScope()} GROUP BY era"
        );
        $raw = array_column($stmt->fetchAll(), 'c', 'era');
        $out = [];
        foreach (self::ERAS as $era) { $out[$era] = (int) ($raw[$era] ?? 0); }
        return $out;
    }

    /** Distinct centuries/decades in use (published only), for dropdown options */
    public function getDistinctCenturies(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT century FROM {$this->table} WHERE century IS NOT NULL AND status = 'published' AND {$this->collectionScope()} ORDER BY century"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctDecades(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT decade FROM {$this->table} WHERE decade IS NOT NULL AND status = 'published' AND {$this->collectionScope()} ORDER BY decade"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* ── Chronological navigation (detail-page prev/next) ────────────── */

    public function getChronologicalNeighbors(int $eventId): array
    {
        $event = $this->find($eventId);
        if (!$event || $event['year'] === null) {
            return ['prev' => null, 'next' => null];
        }

        $prevStmt = $this->db->prepare(
            "SELECT id, title, year FROM {$this->table}
             WHERE status = 'published' AND year IS NOT NULL AND {$this->collectionScope()}
               AND (year < ? OR (year = ? AND id < ?))
             ORDER BY year DESC, id DESC LIMIT 1"
        );
        $prevStmt->execute([$event['year'], $event['year'], $eventId]);

        $nextStmt = $this->db->prepare(
            "SELECT id, title, year FROM {$this->table}
             WHERE status = 'published' AND year IS NOT NULL AND {$this->collectionScope()}
               AND (year > ? OR (year = ? AND id > ?))
             ORDER BY year ASC, id ASC LIMIT 1"
        );
        $nextStmt->execute([$event['year'], $event['year'], $eventId]);

        $prev = $prevStmt->fetch();
        $next = $nextStmt->fetch();

        return ['prev' => $prev ?: null, 'next' => $next ?: null];
    }

    /** Every published record (newsletter popularity candidates). */
    public function allPublished(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} WHERE status = 'published' AND {$this->collectionScope()}")->fetchAll();
    }
}
