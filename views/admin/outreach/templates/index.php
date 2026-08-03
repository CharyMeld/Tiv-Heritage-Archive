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
                <div class="op-table-wrap">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th style="width:20%;">Template Name</th>
                                <th style="width:34%;">Subject</th>
                                <th style="width:16%;">Created By</th>
                                <th style="width:14%;">Date</th>
                                <th style="width:16%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $t): ?>
                            <tr>
                                <td data-label="Template Name" class="op-truncate" style="font-weight:500;color:#2d1b0e;" title="<?= e($t['name']) ?>"><?= e($t['name']) ?></td>
                                <td data-label="Subject" class="op-truncate" style="color:#5a4a3a;" title="<?= e($t['subject']) ?>"><?= e($t['subject']) ?></td>
                                <td data-label="Created By" style="color:#7a6a5a;"><?= e($t['creator_name'] ?? '—') ?></td>
                                <td data-label="Date" style="color:#7a6a5a;"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                                <td data-label="Actions" class="op-actions-cell">
                                    <button type="button" class="btn btn-secondary op-actions-toggle" onclick="opToggleActions('tpl-<?= (int) $t['id'] ?>')">
                                        Actions &#9662;
                                    </button>
                                    <div id="op-actions-tpl-<?= (int) $t['id'] ?>" class="op-actions-menu">
                                        <a href="<?= url('admin/outreach/campaigns/create?template_id=' . $t['id']) ?>" class="btn btn-primary">&#128231; Send</a>
                                        <a href="<?= url('admin/outreach/templates/' . $t['id'] . '/edit') ?>" class="btn btn-secondary">Edit</a>
                                        <form method="POST" action="<?= url('admin/outreach/templates/' . $t['id'] . '/delete') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger" style="width:100%;" data-confirm="Delete this template? Campaigns using it will also be removed.">Delete</button>
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

<?php include BASE_PATH . '/views/admin/outreach/_responsive_table.php'; ?>
