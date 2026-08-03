<?php
require_once BASE_PATH . '/core/Model.php';

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

    public function approve(int $id): void
    {
        $this->db->prepare("UPDATE {$this->table} SET status = 'approved' WHERE id = ?")->execute([$id]);
    }
}
