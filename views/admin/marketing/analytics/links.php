<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">UTM Tracked Links</h1>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/marketing/links/create') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ New Link</a>
            <a href="<?= url('admin/marketing/traffic') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Traffic Reports</a>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="admin-empty-state" style="padding:3rem;">
            <p>No tracked links yet. <a href="<?= url('admin/marketing/links/create') ?>">Create one.</a></p>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <div class="op-table-wrap">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th style="width:10%;">Short Link</th>
                                <th style="width:12%;">Platform</th>
                                <th style="width:28%;">Destination</th>
                                <th style="width:14%;">Campaign</th>
                                <th class="op-center-cell" style="width:8%;">Clicks</th>
                                <th style="width:12%;">Created</th>
                                <th style="width:16%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $l): ?>
                            <tr>
                                <td data-label="Short Link"><a href="<?= url('go/' . $l['short_code']) ?>" target="_blank" style="color:#5C3A21;"><?= e($l['short_code']) ?></a></td>
                                <td data-label="Platform"><?= e($platforms[$l['platform']] ?? $l['platform']) ?></td>
                                <td data-label="Destination" class="op-truncate" title="<?= e($l['destination_url']) ?>"><?= e($l['destination_url']) ?></td>
                                <td data-label="Campaign" class="op-truncate" title="<?= e($l['utm_campaign']) ?>"><?= e($l['utm_campaign']) ?></td>
                                <td data-label="Clicks" class="op-center-cell" style="font-weight:600;color:#5C3A21;"><?= number_format($l['click_count']) ?></td>
                                <td data-label="Created" style="color:#7a6a5a;font-size:.82rem;"><?= date('M j, Y', strtotime($l['created_at'])) ?></td>
                                <td data-label="Actions">
                                    <form method="POST" action="<?= url('admin/marketing/links/' . $l['id'] . '/delete') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.25rem .6rem;" data-confirm="Delete this tracked link?">Delete</button>
                                    </form>
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

<?php include BASE_PATH . '/views/admin/outreach/_responsive_table.php'; ?>
