<?php
require_once BASE_PATH . '/core/Model.php';

class EmailTemplate extends Model {
    protected string $table = 'email_templates';
    protected array $fillable = ['name', 'subject', 'body_html', 'created_by'];

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT t.*, u.name AS creator_name
             FROM {$this->table} t
             LEFT JOIN users u ON u.id = t.created_by
             ORDER BY t.created_at DESC"
        );
        return $stmt->fetchAll();
    }
}
