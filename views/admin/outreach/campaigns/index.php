<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Email Campaigns</h1>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/outreach/campaigns/create') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ New Campaign</a>
            <a href="<?= url('admin/outreach') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Outreach</a>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="admin-empty-state" style="padding:3rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <p>No campaigns yet. <a href="<?= url('admin/outreach/campaigns/create') ?>">Create the first one.</a></p>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:2px solid #e5e0d5;">
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Campaign</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Template</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Schedule</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Sent</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $c): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.7rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($c['name']) ?></td>
                                <td style="padding:.7rem 1rem;color:#5a4a3a;"><?= e($c['template_name'] ?? '—') ?></td>
                                <td style="padding:.7rem 1rem;color:#7a6a5a;"><?= ucfirst(str_replace('_',' ', $c['schedule_type'])) ?></td>
                                <td style="padding:.7rem 1rem;">
                                    <?php
                                    $bg = match($c['status']) {
                                        'sent'      => 'background:#d1fae5;color:#065f46;',
                                        'cancelled' => 'background:#fee2e2;color:#991b1b;',
                                        default     => 'background:#fef3c7;color:#92400e;',
                                    };
                                    ?>
                                    <span style="<?= $bg ?>padding:.2rem .55rem;border-radius:12px;font-size:.75rem;font-weight:600;"><?= ucfirst($c['status']) ?></span>
                                </td>
                                <td style="padding:.7rem 1rem;text-align:center;font-weight:600;color:#5C3A21;"><?= number_format($c['total_sent']) ?></td>
                                <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.82rem;">
                                    <?= $c['sent_at'] ? date('M j, Y', strtotime($c['sent_at'])) : date('M j, Y', strtotime($c['created_at'])) ?>
                                </td>
                                <td style="padding:.7rem 1rem;">
                                    <a href="<?= url('admin/outreach/campaigns/' . $c['id']) ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display:flex;justify-content:center;gap:.5rem;padding:1rem;">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:.35rem .75rem;font-size:.82rem;color:#5a4a3a;">Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?></span>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
</div>
