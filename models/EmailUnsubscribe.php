<?php
require_once BASE_PATH . '/core/Model.php';

class EmailUnsubscribe extends Model
{
    protected string $table = 'email_unsubscribes';

    protected array $fillable = [
        'email', 'token', 'unsubscribed_at',
    ];

    public function findByToken(string $token): ?array
    {
        return $this->findBy('token', $token);
    }

    public function isUnsubscribed(string $email): bool
    {
        $row = $this->findBy('email', $email);
        return $row !== null && $row['unsubscribed_at'] !== null;
    }

    /**
     * Every recipient needs a token up front (embedded in the email before
     * it's sent), whether or not they ever click it, so this always returns
     * one instead of only creating a row once someone unsubscribes.
     */
    public function getOrCreateToken(string $email): string
    {
        $existing = $this->findBy('email', $email);
        if ($existing) {
            return $existing['token'];
        }

        $token = bin2hex(random_bytes(24));
        $this->create([
            'email' => $email,
            'token' => $token,
        ]);
        return $token;
    }

    public function markUnsubscribed(string $token): ?array
    {
        $row = $this->findByToken($token);
        if (!$row) {
            return null;
        }
        if ($row['unsubscribed_at'] === null) {
            $this->update((int) $row['id'], ['unsubscribed_at' => date('Y-m-d H:i:s')]);
        }
        return $row;
    }
}
