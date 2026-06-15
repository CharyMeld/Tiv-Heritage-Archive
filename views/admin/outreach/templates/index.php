<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Email Templates</h1>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/outreach/templates/create') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ New Template</a>
            <a href="<?= url('admin/outreach') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Outreach</a>
        </div>
    </div>

    <!-- Variable Guide -->
    <div style="background:#fdf8f0;border:1px solid #C8A951;border-radius:8px;padding:1rem 1.2rem;margin-bottom:1.5rem;font-size:.85rem;color:#5a4a3a;">
        <strong style="color:#5C3A21;">Template variables you can use in the body:</strong>
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{name}}</code>
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{title}}</code>
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{site_name}}</code>
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{site_url}}</code>
        — An unsubscribe link is appended automatically.
    </div>

    <?php if (empty($items)): ?>
        <div class="admin-empty-state" style="padding:3rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            <p>No templates yet. <a href="<?= url('admin/outreach/templates/create') ?>">Create one.</a></p>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:2px solid #e5e0d5;">
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Template Name</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Subject</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Created By</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $t): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.7rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($t['name']) ?></td>
                                <td style="padding:.7rem 1rem;color:#5a4a3a;"><?= e($t['subject']) ?></td>
                                <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.83rem;"><?= e($t['creator_name'] ?? '—') ?></td>
                                <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.83rem;"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                                <td style="padding:.7rem 1rem;">
                                    <div style="display:flex;gap:.4rem;">
                                        <a href="<?= url('admin/outreach/templates/' . $t['id'] . '/edit') ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Edit</a>
                                        <form method="POST" action="<?= url('admin/outreach/templates/' . $t['id'] . '/delete') ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.25rem .6rem;" data-confirm="Delete this template? Campaigns using it will also be removed.">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
</div>
