<?php
require_once BASE_PATH . '/core/Model.php';

class NewsletterSubscriber extends Model
{
    protected string $table = 'newsletter_subscribers';

    protected array $fillable = [
        'name', 'email', 'status', 'source', 'ip_address', 'subscribed_at', 'unsubscribed_at',
    ];

    public const STATUSES = [
        'subscribed'   => 'Subscribed',
        'unsubscribed' => 'Unsubscribed',
    ];

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Subscribes an email, distinguishing three outcomes the popup needs to
     * render differently: a brand new signup, a returning already-active
     * subscriber (no duplicate row created), and a previously-unsubscribed
     * email being reactivated.
     *
     * @return array{outcome: 'new'|'already_subscribed'|'reactivated', id: int}
     */
    public function subscribe(string $email, ?string $name, string $source, ?string $ip): array
    {
        $existing = $this->findByEmail($email);

        if ($existing && $existing['status'] === 'subscribed') {
            return ['outcome' => 'already_subscribed', 'id' => (int) $existing['id']];
        }

        if ($existing) {
            $this->update((int) $existing['id'], [
                'status' => 'subscribed',
                'name' => $name ?: $existing['name'],
                'subscribed_at' => date('Y-m-d H:i:s'),
                'unsubscribed_at' => null,
            ]);
            return ['outcome' => 'reactivated', 'id' => (int) $existing['id']];
        }

        $id = $this->create([
            'name' => $name,
            'email' => $email,
            'status' => 'subscribed',
            'source' => $source,
            'ip_address' => $ip,
            'subscribed_at' => date('Y-m-d H:i:s'),
        ]);
        return ['outcome' => 'new', 'id' => $id];
    }

    public function unsubscribe(string $email): bool
    {
        $existing = $this->findByEmail($email);
        if (!$existing) {
            return false;
        }
        $this->update((int) $existing['id'], [
            'status' => 'unsubscribed',
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function getAll(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= ' WHERE ' . $where;
        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        [$where, $params] = $this->buildFilters($filters);
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= ' WHERE ' . $where;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus(): array
    {
        $stmt = $this->db->query("SELECT status, COUNT(*) AS cnt FROM {$this->table} GROUP BY status");
        $rows = $stmt->fetchAll();
        $counts = array_fill_keys(array_keys(self::STATUSES), 0);
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }
        return $counts;
    }

    private function buildFilters(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(email LIKE ? OR name LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        return [implode(' AND ', $where), $params];
    }
}
