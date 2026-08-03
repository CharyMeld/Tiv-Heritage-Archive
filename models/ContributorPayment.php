<?php
/**
 * ContributorPayment Model — bookkeeping record of a payment run to a
 * contributor. No payment gateway involved: the admin pays externally
 * (bank transfer, cash, etc.) and this records that it happened.
 */

require_once BASE_PATH . '/core/Model.php';

class ContributorPayment extends Model
{
    protected string $table = 'contributor_payments';

    protected array $fillable = [
        'user_id', 'total_amount', 'currency', 'contribution_count',
        'payment_method', 'reference', 'notes', 'paid_by',
    ];

    public function historyForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT cp.*, u.name AS paid_by_name
             FROM {$this->table} cp
             LEFT JOIN users u ON u.id = cp.paid_by
             WHERE cp.user_id = ?
             ORDER BY cp.paid_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** The specific submissions a given payment covered (audit trail). */
    public function coveredSubmissions(int $paymentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, category, tiv_term, english_meaning, amount, reviewed_at
             FROM submissions WHERE payment_id = ? ORDER BY reviewed_at ASC"
        );
        $stmt->execute([$paymentId]);
        return $stmt->fetchAll();
    }
}
