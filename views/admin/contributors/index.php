<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128176; Contributors</h1>
        <p class="admin-page-sub">Earnings, payments, and outstanding balances across all contributors</p>
    </div>
    <a href="<?= url('admin/community/members') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">Community Directory</a>
</div>

<?php if (empty($roster)): ?>
    <div class="empty-state">
        <h3>No contributors yet</h3>
        <p>Approve a community application, or wait for existing users to be migrated in.</p>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contributor</th>
                    <th>Total Contributions</th>
                    <th>Approved</th>
                    <th>Lifetime Earnings</th>
                    <th>Total Paid</th>
                    <th>Outstanding Balance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roster as $c): ?>
                <tr>
                    <td>
                        <strong><?= e($c['full_name']) ?></strong><br>
                        <span style="font-size:.8rem;color:var(--color-text-muted);"><?= e($c['user_email'] ?? $c['email']) ?></span>
                    </td>
                    <td><?= number_format((int) $c['total_contributions']) ?></td>
                    <td><?= number_format((int) $c['approved_contributions']) ?></td>
                    <td>&#8358;<?= number_format((float) $c['lifetime_earnings'], 2) ?></td>
                    <td>&#8358;<?= number_format((float) $c['total_paid'], 2) ?></td>
                    <td>
                        <strong style="color:<?= (float) $c['outstanding_balance'] > 0 ? 'var(--color-warning)' : 'var(--color-text-muted)' ?>;">
                            &#8358;<?= number_format((float) $c['outstanding_balance'], 2) ?>
                        </strong>
                    </td>
                    <td class="actions">
                        <a href="<?= url('admin/contributors/' . $c['id']) ?>" class="btn btn-sm btn-secondary">View Profile</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
