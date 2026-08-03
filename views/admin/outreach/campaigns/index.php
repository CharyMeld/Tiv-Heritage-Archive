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
                <div class="op-table-wrap">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th style="width:20%;">Campaign</th>
                                <th style="width:18%;">Template</th>
                                <th style="width:12%;">Schedule</th>
                                <th style="width:12%;">Status</th>
                                <th class="op-center-cell" style="width:8%;">Sent</th>
                                <th style="width:14%;">Date</th>
                                <th style="width:16%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $c): ?>
                            <tr>
                                <td data-label="Campaign" class="op-truncate" style="font-weight:500;color:#2d1b0e;" title="<?= e($c['name']) ?>"><?= e($c['name']) ?></td>
                                <td data-label="Template" class="op-truncate" style="color:#5a4a3a;" title="<?= e($c['template_name'] ?? '—') ?>"><?= e($c['template_name'] ?? '—') ?></td>
                                <td data-label="Schedule" style="color:#7a6a5a;"><?= ucfirst(str_replace('_',' ', $c['schedule_type'])) ?></td>
                                <td data-label="Status">
                                    <?php
                                    $bg = match($c['status']) {
                                        'sent'      => 'background:#d1fae5;color:#065f46;',
                                        'cancelled' => 'background:#fee2e2;color:#991b1b;',
                                        default     => 'background:#fef3c7;color:#92400e;',
                                    };
                                    ?>
                                    <span class="op-badge" style="<?= $bg ?>"><?= ucfirst($c['status']) ?></span>
                                </td>
                                <td data-label="Sent" class="op-center-cell" style="font-weight:600;color:#5C3A21;"><?= number_format($c['total_sent']) ?></td>
                                <td data-label="Date" style="color:#7a6a5a;">
                                    <?= $c['sent_at'] ? date('M j, Y', strtotime($c['sent_at'])) : date('M j, Y', strtotime($c['created_at'])) ?>
                                </td>
                                <td data-label="Actions" class="op-actions-cell">
                                    <button type="button" class="btn btn-secondary op-actions-toggle" onclick="opToggleActions('camp-<?= (int) $c['id'] ?>')">
                                        Actions &#9662;
                                    </button>
                                    <div id="op-actions-camp-<?= (int) $c['id'] ?>" class="op-actions-menu">
                                        <a href="<?= url('admin/outreach/campaigns/' . $c['id']) ?>" class="btn btn-secondary">View</a>
                                        <?php if ($c['status'] !== 'sent' && $c['recipient_count'] > 0): ?>
                                        <form method="POST" action="<?= url('admin/outreach/campaigns/' . $c['id'] . '/send') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary" style="width:100%;"
                                                    data-confirm="Send this campaign to <?= $c['recipient_count'] ?> contact(s) now? This cannot be undone.">
                                                &#128231; Send Now
                                            </button>
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

<?php include BASE_PATH . '/views/admin/outreach/_responsive_table.php'; ?>
