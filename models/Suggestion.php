<?php

require_once BASE_PATH . '/core/Model.php';

class Suggestion extends Model
{
    protected string $table = 'suggestions';

    protected array $fillable = [
        'full_name', 'email', 'subject', 'category', 'message',
        'attachment', 'status', 'admin_notes', 'handled_by',
        'admin_reply', 'replied_at', 'replied_by',
    ];

    public static function categories(): array
    {
        return [
            'general_suggestion'   => 'General Suggestion',
            'website_improvement'  => 'Website Improvement',
            'feature_request'      => 'New Feature Request',
            'content_correction'   => 'Content Correction',
            'dictionary_correction'=> 'Dictionary Correction',
            'translation_suggestion' => 'Translation Suggestion',
            'historical_information' => 'Historical Information',
            'cultural_information' => 'Cultural Information',
            'research_contribution'=> 'Research Contribution',
            'partnership_request'  => 'Partnership Request',
            'technical_issue'      => 'Technical Issue',
            'other'                => 'Other',
        ];
    }

    public static function statuses(): array
    {
        return [
            'new'          => 'New',
            'read'         => 'Read',
            'under_review' => 'Under Review',
            'in_progress'  => 'In Progress',
            'implemented'  => 'Implemented',
            'closed'       => 'Closed',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match($status) {
            'new'          => 'badge-warning',
            'read'         => 'badge-info',
            'under_review' => 'badge-primary',
            'in_progress'  => 'badge-info',
            'implemented'  => 'badge-success',
            'closed'       => 'badge-secondary',
            default        => 'badge-secondary',
        };
    }

    public function getAll(string $status = '', string $category = '', int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }

        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(string $status = '', string $category = ''): int
    {
        $where = [];
        $params = [];

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function searchSuggestions(string $q, int $limit = 50): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE full_name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?
             ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$like, $like, $like, $like, $limit]);
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'new') AS new_count,
                SUM(status = 'under_review') AS under_review,
                SUM(status = 'in_progress') AS in_progress,
                SUM(status = 'implemented') AS implemented,
                SUM(status = 'closed') AS closed
             FROM {$this->table}"
        );
        return $stmt->fetch() ?: [];
    }
}
