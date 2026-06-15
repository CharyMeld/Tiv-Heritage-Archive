<?php

require_once BASE_PATH . '/core/Model.php';

class ContentSubmission extends Model
{
    protected string $table = 'content_submissions';

    protected array $fillable = [
        'section', 'subcategory',
        'title', 'tiv_title',
        'excerpt', 'tiv_excerpt',
        'content', 'tiv_content',
        'media_file', 'media_type',
        'submitter_name', 'submitter_email',
        'status', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    public function getPending(string $section = '', string $sub = '', int $limit = 20, int $offset = 0): array
    {
        $where  = ["status = 'pending'"];
        $params = [];
        if ($section) { $where[] = 'section = ?';     $params[] = $section; }
        if ($sub)     { $where[] = 'subcategory = ?'; $params[] = $sub; }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where)
             . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPending(string $section = '', string $sub = ''): int
    {
        $where  = ["status = 'pending'"];
        $params = [];
        if ($section) { $where[] = 'section = ?';     $params[] = $section; }
        if ($sub)     { $where[] = 'subcategory = ?'; $params[] = $sub; }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getAll(string $status = '', string $section = '', string $sub = '', int $limit = 20, int $offset = 0): array
    {
        $where  = [];
        $params = [];
        if ($status)  { $where[] = 'status = ?';      $params[] = $status; }
        if ($section) { $where[] = 'section = ?';     $params[] = $section; }
        if ($sub)     { $where[] = 'subcategory = ?'; $params[] = $sub; }

        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status='pending')  AS pending,
                SUM(status='approved') AS approved,
                SUM(status='rejected') AS rejected
             FROM {$this->table}"
        );
        return $stmt->fetch() ?: [];
    }
}
