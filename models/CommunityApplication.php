<?php

require_once BASE_PATH . '/core/Model.php';

class CommunityApplication extends Model
{
    protected string $table = 'community_applications';

    protected array $fillable = [
        'full_name', 'email', 'phone', 'country', 'state_region',
        'occupation', 'area_of_interest', 'member_type', 'short_bio',
        'skills', 'reason_for_joining', 'profile_photo', 'supporting_document',
        'status', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    public function getAll(string $status = '', string $type = '', int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($type) {
            $where[] = 'member_type = ?';
            $params[] = $type;
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

    public function countAll(string $status = '', string $type = ''): int
    {
        $where = [];
        $params = [];

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($type) {
            $where[] = 'member_type = ?';
            $params[] = $type;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function searchApplicants(string $q, int $limit = 50): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE full_name LIKE ? OR email LIKE ? OR area_of_interest LIKE ?
             ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$like, $like, $like, $limit]);
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'pending') AS pending,
                SUM(status = 'approved') AS approved,
                SUM(status = 'rejected') AS rejected,
                SUM(member_type = 'contributor') AS contributors,
                SUM(member_type = 'researcher') AS researchers
             FROM {$this->table}"
        );
        return $stmt->fetch() ?: [];
    }
}
