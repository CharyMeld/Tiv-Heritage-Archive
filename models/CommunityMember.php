<?php

require_once BASE_PATH . '/core/Model.php';

class CommunityMember extends Model
{
    protected string $table = 'community_members';

    protected array $fillable = [
        'application_id', 'full_name', 'email', 'member_type',
        'short_bio', 'area_of_interest', 'location', 'profile_photo',
        'is_featured', 'is_active', 'contributions_count', 'research_count',
        'suggestions_count', 'date_joined',
    ];

    public function getDirectory(string $type = '', bool $featuredFirst = true): array
    {
        $where = ['is_active = 1'];
        $params = [];

        if ($type) {
            $where[] = 'member_type = ?';
            $params[] = $type;
        }

        $order = $featuredFirst
            ? 'is_featured DESC, date_joined ASC'
            : 'date_joined ASC';

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " ORDER BY {$order}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(string $type = '', int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'member_type = ?';
            $params[] = $type;
        }

        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY is_featured DESC, date_joined DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(string $type = ''): int
    {
        if ($type) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE member_type = ?");
            $stmt->execute([$type]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        }
        return (int) $stmt->fetchColumn();
    }

    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(is_active = 1) AS active,
                SUM(member_type = 'contributor' AND is_active = 1) AS contributors,
                SUM(member_type = 'researcher' AND is_active = 1) AS researchers
             FROM {$this->table}"
        );
        return $stmt->fetch() ?: [];
    }

    public function createFromApplication(array $app): int
    {
        $location = implode(', ', array_filter([$app['state_region'] ?? '', $app['country'] ?? '']));

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (application_id, full_name, email, member_type, short_bio, area_of_interest,
              location, profile_photo, is_featured, is_active, date_joined)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, CURDATE())"
        );
        $stmt->execute([
            $app['id'],
            $app['full_name'],
            $app['email'],
            $app['member_type'],
            $app['short_bio'] ?? null,
            $app['area_of_interest'] ?? null,
            $location ?: null,
            $app['profile_photo'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByApplicationId(int $appId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE application_id = ?");
        $stmt->execute([$appId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
