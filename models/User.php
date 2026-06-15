<?php
/**
 * User Model
 */

require_once BASE_PATH . '/core/Model.php';

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'avatar',
        'email_verified_at',
        'remember_token'
    ];

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Create new user with hashed password
     */
    public function createUser(array $data): int
    {
        $data['password'] = Security::hashPassword($data['password']);
        return $this->create($data);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password' => Security::hashPassword($newPassword)
        ]);
    }

    /**
     * Update user role
     */
    public function updateRole(int $id, string $role): bool
    {
        if (!array_key_exists($role, USER_ROLES)) {
            return false;
        }
        return $this->update($id, ['role' => $role]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE role = ? ORDER BY name ASC"
        );
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    /**
     * Get user submission count
     */
    public function getSubmissionCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM submissions WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get user's approved submission count
     */
    public function getApprovedSubmissionCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM submissions WHERE user_id = ? AND status = 'approved'"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Authenticate user
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!Security::verifyPassword($password, $user['password'])) {
            return null;
        }

        // Rehash password if needed
        if (Security::needsRehash($user['password'])) {
            $this->updatePassword($user['id'], $password);
        }

        // Remove password from returned data
        unset($user['password']);

        return $user;
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Set remember token
     */
    public function setRememberToken(int $id, string $token): bool
    {
        return $this->update($id, ['remember_token' => $token]);
    }

    /**
     * Find by remember token
     */
    public function findByRememberToken(string $token): ?array
    {
        return $this->findBy('remember_token', $token);
    }

    /**
     * Get recent users
     */
    public function getRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, created_at
            FROM {$this->table}
            ORDER BY created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
