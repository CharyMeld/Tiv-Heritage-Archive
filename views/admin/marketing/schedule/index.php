<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Scheduled Posts</h1>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/marketing/calendar') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&#128197; Calendar View</a>
            <a href="<?= url('admin/marketing/generator') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="admin-empty-state" style="padding:3rem;">
            <p>Nothing scheduled yet. Approve a post, then schedule it from the calendar or its review page.</p>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Headline</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Platform</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Scheduled For</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $s): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.65rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($s['headline'] ?? '(untitled)') ?></td>
                                <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e(ucfirst($s['platform'])) ?></td>
                                <td style="padding:.65rem 1rem;color:#7a6a5a;font-size:.82rem;"><?= date('M j, Y g:ia', strtotime($s['scheduled_at'])) ?></td>
                                <td style="padding:.65rem 1rem;">
                                    <?php
                                    $bg = match($s['status']) {
                                        'published' => 'background:#d1fae5;color:#065f46;',
                                        'failed' => 'background:#fee2e2;color:#991b1b;',
                                        'cancelled' => 'background:#f3f4f6;color:#374151;',
                                        default => 'background:#fef3c7;color:#92400e;',
                                    };
                                    ?>
                                    <span style="<?= $bg ?>padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;"><?= e($statuses[$s['status']] ?? $s['status']) ?></span>
                                    <?php if ($s['attempts'] > 0): ?>
                                        <span style="font-size:.72rem;color:#991b1b;">(<?= (int) $s['attempts'] ?> attempt<?= $s['attempts'] > 1 ? 's' : '' ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:.65rem 1rem;">
                                    <div style="display:flex;gap:.4rem;">
                                        <?php if (!in_array($s['status'], ['published', 'cancelled'])): ?>
                                        <a href="<?= url('admin/marketing/schedule/' . $s['id'] . '/edit') ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Edit</a>
                                        <form method="POST" action="<?= url('admin/marketing/schedule/' . $s['id'] . '/cancel') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.25rem .6rem;" data-confirm="Cancel this schedule?">Cancel</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
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
