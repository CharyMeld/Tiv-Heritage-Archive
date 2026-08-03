<!-- Page Banner -->
<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128176; Contributor Portal</span>
        <h1 class="page-banner-title">My Contributor Dashboard</h1>
        <p class="page-banner-sub">Your earnings and contribution history at a glance</p>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container">

        <!-- Earnings summary -->
        <div class="admin-stats" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
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

        <!-- Contribution status counts -->
        <div class="admin-stats" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="admin-stat">
                <div class="admin-stat-icon">&#128203;</div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($counts['approved']) ?></div>
                    <div class="admin-stat-label">Approved Contributions</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-icon">&#128340;</div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($counts['pending']) ?></div>
                    <div class="admin-stat-label">Pending Review</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-icon">&#9998;&#65039;</div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($counts['needs_revision']) ?></div>
                    <div class="admin-stat-label">Needs Revision</div>
                </div>
            </div>
        </div>

        <!-- Earnings history -->
        <h2 style="font-size:1.1rem;color:var(--color-primary);margin:2rem 0 1rem;">Earnings History</h2>
        <?php if (empty($history)): ?>
            <div class="admin-empty-state" style="padding:3rem;text-align:center;">
                <p>No contributions yet. Once you submit content and it's reviewed, it'll show up here.</p>
                <a href="<?= url('contribute') ?>" class="btn btn-primary" style="margin-top:1rem;">Contribute Now</a>
            </div>
        <?php else: ?>
        <div class="admin-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Submitted</th>
                        <th>Reviewed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                    <tr>
                        <td><strong><?= e($row['tiv_term']) ?></strong><br><span style="color:var(--color-text-muted);font-size:.85rem;"><?= e($row['english_meaning']) ?></span></td>
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
        <?php if (!empty($payments)): ?>
        <h2 style="font-size:1.1rem;color:var(--color-primary);margin:2rem 0 1rem;">Payment History</h2>
        <div class="admin-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Contributions Covered</th>
                        <th>Method</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($p['paid_at'])) ?></td>
                        <td><strong>&#8358;<?= number_format((float) $p['total_amount'], 2) ?></strong></td>
                        <td><?= (int) $p['contribution_count'] ?></td>
                        <td><?= e($p['payment_method'] ?? '—') ?></td>
                        <td><?= e($p['reference'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>
