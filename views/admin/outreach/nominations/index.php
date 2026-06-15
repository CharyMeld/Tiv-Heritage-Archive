<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.2rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Community Nominations</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Public nominations awaiting admin review</p>
        </div>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/outreach') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Outreach</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="admin-stats" style="margin-bottom:1.2rem;">
        <div class="admin-stat <?= ($stats['pending'] > 0) ? 'highlight' : '' ?>">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($stats['pending']) ?></div>
                <div class="admin-stat-label">Pending</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($stats['approved']) ?></div>
                <div class="admin-stat-label">Approved</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($stats['rejected']) ?></div>
                <div class="admin-stat-label">Rejected</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($stats['total']) ?></div>
                <div class="admin-stat-label">Total</div>
            </div>
        </div>
    </div>

    <!-- Status filter tabs -->
    <div style="display:flex;gap:.4rem;margin-bottom:1.2rem;flex-wrap:wrap;">
        <?php foreach ([''=>'All', 'pending'=>'Pending', 'approved'=>'Approved', 'rejected'=>'Rejected'] as $val => $lbl): ?>
            <a href="?status=<?= $val ?>"
               style="padding:.4rem .85rem;border-radius:20px;font-size:.82rem;text-decoration:none;border:1px solid;
                      <?= $statusFilter === $val ? 'background:#5C3A21;color:#fff;border-color:#5C3A21;' : 'background:#fff;color:#5a4a3a;border-color:#d5cfc5;' ?>">
                <?= $lbl ?>
                <?php if ($val === 'pending' && $stats['pending'] > 0): ?><span style="background:#C8A951;color:#2d1b0e;border-radius:10px;padding:.05rem .4rem;font-size:.75rem;margin-left:.3rem;"><?= $stats['pending'] ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="admin-card">
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($items)): ?>
                <div class="admin-empty-state" style="padding:3rem;">
                    <p>No nominations found for this filter.</p>
                    <a href="<?= url('nominate-influential') ?>" class="btn btn-secondary" style="margin-top:.5rem;" target="_blank">View Public Nomination Page</a>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:2px solid #e5e0d5;">
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Nominee</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Nominator</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $n): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.7rem 1rem;">
                                    <div style="font-weight:500;color:#2d1b0e;"><?= e($n['nominee_name']) ?></div>
                                    <div style="font-size:.78rem;color:#7a6a5a;"><?= e($n['nominee_organization'] ?? '') ?></div>
                                </td>
                                <td style="padding:.7rem 1rem;color:#5a4a3a;"><?= e($n['nominee_category'] ?? '—') ?></td>
                                <td style="padding:.7rem 1rem;">
                                    <div style="font-size:.85rem;color:#2d1b0e;"><?= e($n['nominator_name'] ?? '—') ?></div>
                                    <div style="font-size:.78rem;color:#7a6a5a;"><?= e($n['nominator_email'] ?? '') ?></div>
                                </td>
                                <td style="padding:.7rem 1rem;">
                                    <?php
                                    $bg = match($n['status']) {
                                        'approved' => 'background:#d1fae5;color:#065f46;',
                                        'rejected' => 'background:#fee2e2;color:#991b1b;',
                                        default    => 'background:#fef3c7;color:#92400e;',
                                    };
                                    ?>
                                    <span style="<?= $bg ?>padding:.2rem .55rem;border-radius:12px;font-size:.75rem;font-weight:600;">
                                        <?= ucfirst($n['status']) ?>
                                    </span>
                                </td>
                                <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.82rem;"><?= date('M j, Y', strtotime($n['created_at'])) ?></td>
                                <td style="padding:.7rem 1rem;">
                                    <a href="<?= url('admin/outreach/nominations/' . $n['id']) ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Review</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display:flex;justify-content:center;gap:.5rem;padding:1rem;">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?<?= http_build_query(['status'=>$statusFilter,'page'=>$pagination['current_page']-1]) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:.35rem .75rem;font-size:.82rem;color:#5a4a3a;">Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?></span>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(['status'=>$statusFilter,'page'=>$pagination['current_page']+1]) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
