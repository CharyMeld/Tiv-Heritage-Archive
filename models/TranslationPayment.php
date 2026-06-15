<?php
/**
 * Translation Payment Model
 * Subscription plans and credit packs for paid access.
 */

class TranslationPayment extends Model
{
    protected string $table = 'translation_payments';
    protected array $fillable = [
        'user_id', 'payment_ref', 'amount', 'currency',
        'payment_type', 'credits_granted', 'credits_remaining',
        'valid_from', 'valid_until', 'status', 'gateway',
    ];

    /**
     * Get active payment/subscription for a user.
     */
    public function activeForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE user_id = ?
               AND status = 'active'
               AND (
                   (payment_type IN ('subscription_monthly','subscription_yearly') AND valid_until > NOW())
                   OR
                   (payment_type = 'credit_pack' AND credits_remaining > 0)
               )
             ORDER BY valid_until DESC
             LIMIT 1"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * All payments for a user (history).
     */
    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE user_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Deduct one credit from a credit pack.
     */
    public function useCredit(int $paymentId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET credits_remaining = credits_remaining - 1
             WHERE id = ? AND credits_remaining > 0"
        );
        return $stmt->execute([$paymentId]);
    }

    /**
     * Expire stale subscriptions/credits.
     */
    public function expireStale(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET status = 'expired'
             WHERE status = 'active'
               AND (
                   (payment_type IN ('subscription_monthly','subscription_yearly') AND valid_until < NOW())
                   OR
                   (payment_type = 'credit_pack' AND credits_remaining = 0)
               )"
        );
        $stmt->execute();
        return (int) $stmt->rowCount();
    }

    /**
     * Admin list of all payments.
     */
    public function adminList(int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            "SELECT tp.*, u.name AS user_name, u.email AS user_email
             FROM {$this->table} tp
             JOIN users u ON tp.user_id = u.id
             ORDER BY tp.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}
