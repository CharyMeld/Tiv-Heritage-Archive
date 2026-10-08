<?php
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/MarketingNewsletterIssueItem.php';

class MarketingNewsletterIssue extends Model
{
    protected string $table = 'marketing_newsletter_issues';

    protected array $fillable = [
        'issue_type', 'issue_date', 'subject', 'html_body', 'text_body', 'status', 'created_by',
    ];

    public const ISSUE_TYPES = [
        'weekly_summary'  => 'Weekly Summary',
        'new_words'       => 'New Words',
        'featured_items'  => 'Featured Items',
        'top_five'        => 'Top 5 Articles',
        'custom'          => 'Custom',
    ];

    public function getAll(int $limit = 25, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT n.*, u.name AS creator_name
             FROM {$this->table} n
             LEFT JOIN users u ON u.id = n.created_by
             ORDER BY n.issue_date DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function latest(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY issue_date DESC, id DESC LIMIT 1");
        return $stmt->fetch() ?: null;
    }

    /**
     * id of the most recent sent (approved/archived) issue of this type —
     * its created_at is the start of the period the next issue covers.
     * Drafts don't count: they were never sent. An id rather than the
     * timestamp itself, so period comparisons stay entirely in SQL
     * (TIMESTAMP vs TIMESTAMP) and never pass through PHP's timezone
     * (Africa/Lagos) or depend on MySQL's session zone (UTC in production).
     */
    public function previousSentId(string $issueType): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table}
             WHERE issue_type = ? AND status IN ('approved', 'archived')
             ORDER BY created_at DESC, id DESC LIMIT 1"
        );
        $stmt->execute([$issueType]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    /** The approved issue of this type dated $issueDate, if one exists. */
    public function approvedForDate(string $issueType, string $issueDate): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE issue_type = ? AND issue_date = ? AND status = 'approved'
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$issueType, $issueDate]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Creates the issue and records the content it includes in one
     * transaction, so an issue never exists without its item history and no
     * item row can be left without its issue.
     */
    public function createWithItems(array $data, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->create($data);
            (new MarketingNewsletterIssueItem())->addForIssue($id, $items);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function approve(int $id): void
    {
        $this->db->prepare("UPDATE {$this->table} SET status = 'approved' WHERE id = ?")->execute([$id]);
    }
}
