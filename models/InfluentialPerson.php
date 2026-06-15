<?php
require_once BASE_PATH . '/core/Model.php';

class InfluentialPerson extends Model {
    protected string $table = 'influential_people';
    protected array $fillable = [
        'name', 'title', 'category', 'organization', 'email', 'phone',
        'website', 'country', 'state_region', 'notes',
        'source_url', 'source_notes',
        'contact_status', 'consent_status', 'consent_date', 'gdpr_basis', 'response_status',
        'last_contact_date', 'is_unsubscribed', 'unsubscribe_token',
        'nomination_id', 'added_by',
    ];

    public const GDPR_BASES = [
        'not_set'             => 'Not Set',
        'legitimate_interest' => 'Legitimate Interest',
        'consent'             => 'Consent',
    ];

    public const CATEGORIES = [
        'academic'          => 'Academic',
        'traditional_leader'=> 'Traditional Leader',
        'politician'        => 'Politician',
        'researcher'        => 'Researcher',
        'author'            => 'Author',
        'musician'          => 'Musician',
        'clergy'            => 'Clergy',
        'business_leader'   => 'Business Leader',
        'ngo'               => 'NGO / Nonprofit',
        'diaspora'          => 'Diaspora',
        'other'             => 'Other',
    ];

    public const CONTACT_STATUSES = [
        'not_contacted' => 'Not Contacted',
        'contacted'     => 'Contacted',
        'responded'     => 'Responded',
        'unresponsive'  => 'Unresponsive',
        'partner'       => 'Partner',
    ];

    public const CONSENT_STATUSES = [
        'unknown'   => 'Unknown',
        'opted_in'  => 'Opted In',
        'opted_out' => 'Opted Out',
    ];

    public const RESPONSE_STATUSES = [
        'none'     => 'None',
        'positive' => 'Positive',
        'negative' => 'Negative',
        'neutral'  => 'Neutral',
    ];

    public function getAll(array $filters = [], int $limit = 20, int $offset = 0): array {
        [$where, $params] = $this->buildFilters($filters);
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getForCampaign(array $categories = []): array {
        $catWhere = '';
        $params = ['opted_out'];
        if (!empty($categories)) {
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $catWhere = "AND category IN ({$placeholders})";
            array_push($params, ...$categories);
        }
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE email IS NOT NULL AND email != ''
               AND is_unsubscribed = 0
               AND consent_status != ?
               {$catWhere}
             ORDER BY name ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countForCampaign(array $categories = []): int {
        return count($this->getForCampaign($categories));
    }

    public function getStats(): array {
        $total     = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        $active    = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE is_unsubscribed = 0")->fetchColumn();
        $contacted = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE contact_status != 'not_contacted'")->fetchColumn();
        $emailsSent= (int) $this->db->query("SELECT COALESCE(SUM(emails_sent),0) FROM {$this->table}")->fetchColumn();

        $stmt = $this->db->query(
            "SELECT category, COUNT(*) AS cnt FROM {$this->table} GROUP BY category ORDER BY cnt DESC"
        );
        $byCategory = $stmt->fetchAll();

        return compact('total', 'active', 'contacted', 'emailsSent', 'byCategory');
    }

    public function markContacted(int $id): void {
        $this->db->prepare(
            "UPDATE {$this->table}
             SET last_contact_date = CURDATE(), emails_sent = emails_sent + 1
             WHERE id = ?"
        )->execute([$id]);
    }

    public function isEmailUnsubscribed(string $email): bool {
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table} WHERE email = ? AND is_unsubscribed = 1 LIMIT 1"
        );
        $stmt->execute([strtolower(trim($email))]);
        return (bool) $stmt->fetchColumn();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1"
        );
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByToken(string $token): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE unsubscribe_token = ? LIMIT 1"
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function buildFilters(array $filters): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['contact_status'])) {
            $where[] = 'contact_status = ?';
            $params[] = $filters['contact_status'];
        }
        if (!empty($filters['consent_status'])) {
            $where[] = 'consent_status = ?';
            $params[] = $filters['consent_status'];
        }
        if (isset($filters['is_unsubscribed']) && $filters['is_unsubscribed'] !== '') {
            $where[] = 'is_unsubscribed = ?';
            $params[] = (int) $filters['is_unsubscribed'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(name LIKE ? OR organization LIKE ? OR email LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        return [implode(' AND ', $where), $params];
    }
}
