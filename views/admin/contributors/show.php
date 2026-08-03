<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128100; <?= e($member['full_name']) ?></h1>
        <p class="admin-page-sub"><?= ucfirst(e($member['member_type'])) ?> &middot; Joined <?= $member['date_joined'] ? date('M j, Y', strtotime($member['date_joined'])) : '—' ?></p>
    </div>
    <a href="<?= url('admin/contributors') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; All Contributors</a>
</div>

<!-- Earnings summary -->
<div class="admin-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="admin-stat highlight">
        <div class="admin-stat-icon">&#128176;</div>
        <div class="admin-stat-info">
            <div class="admin-stat-value">&#8358;<?= number_format($lifetimeEarnings, 2) ?></div>
            <div class="admin-stat-label">Lifetime Earnings</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon">&#9989;</div>
        <div class="admin-stat-info">
            <div class="admin-stat-value">&#8358;<?= number_format($totalPaid, 2) ?></div>
            <div class="admin-stat-label">Total Paid</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon">&#8987;</div>
        <div class="admin-stat-info">
            <div class="admin-stat-value">&#8358;<?= number_format($outstandingBalance, 2) ?></div>
            <div class="admin-stat-label">Outstanding Balance</div>
        </div>
    </div>
</div>

<!-- Generate payment -->
<?php if ($outstandingBalance > 0): ?>
<div class="admin-card" style="margin:1.5rem 0;padding:1.25rem;">
    <h3 style="margin:0 0 .8rem;font-size:1rem;color:var(--color-primary);">Generate Payment</h3>
    <p style="font-size:.85rem;color:var(--color-text-muted);margin:0 0 1rem;">
        This records that you've paid <?= e($member['full_name']) ?> <strong>&#8358;<?= number_format($outstandingBalance, 2) ?></strong> externally
        (bank transfer, cash, etc.) and marks all currently approved-and-unpaid contributions as Paid. No money moves through this system.
    </p>
    <form method="POST" action="<?= url('admin/contributors/' . $member['id'] . '/pay') ?>" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <?= csrf_field() ?>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Payment Method</label>
            <input type="text" name="payment_method" class="form-input" placeholder="e.g. Bank Transfer" style="width:180px;">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" class="form-input" placeholder="Transaction ref (optional)" style="width:180px;">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-input" placeholder="Optional notes">
        </div>
        <button type="submit" class="btn btn-primary" data-confirm="Confirm you've already paid &#8358;<?= number_format($outstandingBalance, 2) ?> to <?= e($member['full_name']) ?> externally?">
            Mark &#8358;<?= number_format($outstandingBalance, 2) ?> as Paid
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Personal information -->
<div class="admin-card" style="margin-bottom:1.5rem;padding:1.25rem;">
    <h3 style="margin:0 0 .8rem;font-size:1rem;color:var(--color-primary);">Personal Information</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Email</span><br><?= e($member['user_email'] ?? $member['email']) ?></div>
        <?php if ($profileUser): ?>
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Account Role</span><br><?= ucfirst(e($profileUser['role'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($application['phone'])): ?>
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Phone</span><br><?= e($application['phone']) ?></div>
        <?php endif; ?>
        <?php if (!empty($application['country']) || !empty($application['state_region'])): ?>
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Location</span><br><?= e(trim(($application['state_region'] ?? '') . ', ' . ($application['country'] ?? ''), ', ')) ?></div>
        <?php endif; ?>
        <?php if (!empty($application['occupation'])): ?>
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Occupation</span><br><?= e($application['occupation']) ?></div>
        <?php endif; ?>
        <?php if (!empty($member['area_of_interest'])): ?>
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Area of Interest</span><br><?= e($member['area_of_interest']) ?></div>
        <?php endif; ?>
        <?php if (!empty($application['skills'])): ?>
        <div><span style="font-size:.75rem;text-transform:uppercase;color:var(--color-text-muted);">Skills</span><br><?= e($application['skills']) ?></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($member['short_bio'])): ?>
    <p style="margin-top:1rem;font-size:.9rem;color:var(--color-text);"><?= nl2br(e($member['short_bio'])) ?></p>
    <?php endif; ?>
</div>

<!-- Contribution history -->
<h3 style="font-size:1rem;color:var(--color-primary);margin:1.5rem 0 .8rem;">Contribution History</h3>
<?php if (empty($history)): ?>
    <div class="empty-state"><p>No contributions yet.</p></div>
<?php else: ?>
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Type</th><th>Amount</th><th>Status</th><th>Payment</th><th>Submitted</th><th>Reviewed</th></tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
            <tr>
                <td><strong><?= e($row['tiv_term']) ?></strong></td>
                <td><?= ucfirst(e($row['category'])) ?></td>
                <td><?= $row['amount'] !== null ? '&#8358;' . number_format((float) $row['amount'], 2) : '&mdash;' ?></td>
                <td><span class="badge badge-<?= e($row['status']) ?>"><?= ucwords(str_replace('_', ' ', $row['status'])) ?></span></td>
                <td><span class="badge badge-<?= $row['payment_status'] === 'paid' ? 'approved' : 'pending' ?>"><?= ucfirst(e($row['payment_status'])) ?></span></td>
                <td style="font-size:.85rem;color:var(--color-text-muted);"><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                <td style="font-size:.85rem;color:var(--color-text-muted);"><?= $row['reviewed_at'] ? date('M j, Y', strtotime($row['reviewed_at'])) : '&mdash;' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Payment history -->
<h3 style="font-size:1rem;color:var(--color-primary);margin:1.5rem 0 .8rem;">Payment History</h3>
<?php if (empty($payments)): ?>
    <div class="empty-state"><p>No payments recorded yet.</p></div>
<?php else: ?>
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr><th>Date</th><th>Amount</th><th>Covered</th><th>Method</th><th>Reference</th><th>Recorded By</th></tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= date('M j, Y', strtotime($p['paid_at'])) ?></td>
                <td><strong>&#8358;<?= number_format((float) $p['total_amount'], 2) ?></strong></td>
                <td><?= (int) $p['contribution_count'] ?> contribution(s)</td>
                <td><?= e($p['payment_method'] ?? '—') ?></td>
                <td><?= e($p['reference'] ?? '—') ?></td>
                <td><?= e($p['paid_by_name'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
