<?php
require_once BASE_PATH . '/core/Model.php';

/**
 * Which archive records a marketing_newsletter_issues row included.
 * item_table is the real table name (tiv_proverbs, historical_figures, ...),
 * mirroring saved_items' item_table/item_id pattern.
 */
class MarketingNewsletterIssueItem extends Model
{
    protected string $table = 'marketing_newsletter_issue_items';

    /**
     * Section for rows that are NOT shown in the email: the view_count of
     * every published popularity candidate at generation time, so the next
     * issue can rank by views gained since this one. Excluded from rotation
     * and from "what did this issue include".
     */
    public const SECTION_BASELINE = 'popular_baseline';

    protected array $fillable = [
        'issue_id', 'section', 'item_table', 'item_id', 'view_count_snapshot',
    ];

    /**
     * @param array<int, array{section:string, item_table:string, item_id:int, view_count_snapshot:?int}> $items
     */
    public function addForIssue(int $issueId, array $items): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (issue_id, section, item_table, item_id, view_count_snapshot)
             VALUES (?, ?, ?, ?, ?)"
        );
        foreach ($items as $item) {
            $stmt->execute([
                $issueId,
                $item['section'],
                $item['item_table'],
                (int) $item['item_id'],
                $item['view_count_snapshot'] ?? null,
            ]);
        }
    }

    /** Whether any sent issue has recorded view_count snapshots for $table. */
    public function hasViewSnapshots(string $table): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->table} i
             JOIN marketing_newsletter_issues n ON n.id = i.issue_id AND n.status IN ('approved', 'archived')
             WHERE i.item_table = ? AND i.view_count_snapshot IS NOT NULL LIMIT 1"
        );
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * item_id => view_count_snapshot from the most recent sent issue that
     * recorded one for that item (shown or baseline).
     */
    public function latestViewSnapshots(string $table): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.item_id, i.view_count_snapshot FROM {$this->table} i
             JOIN (
                 SELECT i2.item_id, MAX(i2.issue_id) AS issue_id FROM {$this->table} i2
                 JOIN marketing_newsletter_issues n ON n.id = i2.issue_id AND n.status IN ('approved', 'archived')
                 WHERE i2.item_table = ? AND i2.view_count_snapshot IS NOT NULL
                 GROUP BY i2.item_id
             ) latest ON latest.item_id = i.item_id AND latest.issue_id = i.issue_id
             WHERE i.item_table = ?"
        );
        $stmt->execute([$table, $table]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_KEY_PAIR));
    }

    /** Records shown in an issue (baseline rows excluded). */
    public function forIssue(int $issueId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE issue_id = ? AND section != ? ORDER BY id"
        );
        $stmt->execute([$issueId, self::SECTION_BASELINE]);
        return $stmt->fetchAll();
    }
}
