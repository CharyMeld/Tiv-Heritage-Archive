<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Prompt Templates</h1>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/marketing/templates/create') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ New Template</a>
            <a href="<?= url('admin/marketing') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
        </div>
    </div>

    <div style="background:#fdf8f0;border:1px solid #C8A951;border-radius:8px;padding:1rem 1.2rem;margin-bottom:1.5rem;font-size:.85rem;color:#5a4a3a;">
        <strong style="color:#5C3A21;">Available in the prompt text:</strong> any field from the source item, e.g.
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{tiv_word}}</code>
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{english_meaning}}</code>
        <code style="background:#fff;border:1px solid #e5e0d5;border-radius:4px;padding:.1rem .4rem;margin:0 .3rem;">{{description}}</code>
        — the exact field names depend on the category.
    </div>

    <?php if (empty($items)): ?>
        <div class="admin-empty-state" style="padding:3rem;">
            <p>No templates yet. <a href="<?= url('admin/marketing/templates/create') ?>">Create one.</a></p>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <div class="op-table-wrap">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th style="width:22%;">Name</th>
                                <th style="width:14%;">Category</th>
                                <th style="width:14%;">Platform</th>
                                <th style="width:10%;">Default</th>
                                <th style="width:10%;">Active</th>
                                <th style="width:14%;">Created By</th>
                                <th style="width:16%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $t): ?>
                            <tr class="<?= !$t['is_active'] ? 'op-row--muted' : '' ?>">
                                <td data-label="Name" class="op-truncate" style="font-weight:500;color:#2d1b0e;" title="<?= e($t['name']) ?>"><?= e($t['name']) ?></td>
                                <td data-label="Category"><?= e($categories[$t['category']] ?? $t['category']) ?></td>
                                <td data-label="Platform"><?= e($platforms[$t['platform']] ?? $t['platform']) ?></td>
                                <td data-label="Default"><?= $t['is_default'] ? '<span class="op-badge" style="background:#d1fae5;color:#065f46;">Default</span>' : '—' ?></td>
                                <td data-label="Active"><?= $t['is_active'] ? '<span class="op-badge" style="background:#d1fae5;color:#065f46;">Active</span>' : '<span class="op-badge" style="background:#f3f4f6;color:#374151;">Inactive</span>' ?></td>
                                <td data-label="Created By" style="color:#7a6a5a;"><?= e($t['creator_name'] ?? '—') ?></td>
                                <td data-label="Actions" class="op-actions-cell">
                                    <button type="button" class="btn btn-secondary op-actions-toggle" onclick="opToggleActions('tpl-<?= (int) $t['id'] ?>')">
                                        Actions &#9662;
                                    </button>
                                    <div id="op-actions-tpl-<?= (int) $t['id'] ?>" class="op-actions-menu">
                                        <a href="<?= url('admin/marketing/templates/' . $t['id'] . '/edit') ?>" class="btn btn-secondary">Edit</a>
                                        <?php if (!$t['is_default']): ?>
                                        <form method="POST" action="<?= url('admin/marketing/templates/' . $t['id'] . '/default') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary" style="width:100%;">Make Default</button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" action="<?= url('admin/marketing/templates/' . $t['id'] . '/delete') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger" style="width:100%;" data-confirm="Delete this template?">Delete</button>
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
