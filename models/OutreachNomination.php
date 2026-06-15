<?php
require_once BASE_PATH . '/core/Model.php';

class OutreachNomination extends Model {
    protected string $table = 'outreach_nominations';
    protected array $fillable = [
        'nominee_name', 'nominee_title', 'nominee_category',
        'nominee_organization', 'nominee_email', 'nominee_website',
        'nominee_country', 'nominee_state_region',
        'reason_for_nomination',
        'nominator_name', 'nominator_email',
        'status', 'admin_notes', 'reviewed_by', 'reviewed_at', 'person_id',
    ];

    public function getAll(string $status = '', int $limit = 20, int $offset = 0): array {
        $where  = '1=1';
        $params = [];
        if ($status !== '') {
            $where    = 'status = ?';
            $params[] = $status;
        }
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(string $status = ''): int {
        if ($status !== '') {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = ?");
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        }
        return (int) $stmt->fetchColumn();
    }

    public function getStats(): array {
        $total    = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        $pending  = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status='pending'")->fetchColumn();
        $approved = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status='approved'")->fetchColumn();
        $rejected = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status='rejected'")->fetchColumn();
        return compact('total', 'pending', 'approved', 'rejected');
    }

    public function approve(int $id, int $reviewerId, int $personId, ?string $notes): bool {
        return $this->update($id, [
            'status'      => 'approved',
            'person_id'   => $personId,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $notes,
        ]);
    }

    public function reject(int $id, int $reviewerId, ?string $notes): bool {
        return $this->update($id, [
            'status'      => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $notes,
        ]);
    }
}
