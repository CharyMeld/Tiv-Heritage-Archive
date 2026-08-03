<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128203; Pending Submissions</h1>
        <p class="admin-page-sub">Review and approve community contributions</p>
    </div>
    <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; Dashboard</a>
</div>

<?php if (empty($submissions)): ?>
    <div class="empty-state">
        <h3>No pending submissions</h3>
        <p>All submissions have been reviewed.</p>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Tiv Term</th>
                    <th>English Meaning</th>
                    <th>Submitted By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?= ucfirst(e($submission['category'])) ?></td>
                        <td><strong><?= e($submission['tiv_term']) ?></strong></td>
                        <td><?= e($submission['english_meaning']) ?></td>
                        <td><?= e($submission['user_name'] ?? 'Guest') ?></td>
                        <td><?= date('M j, Y', strtotime($submission['created_at'])) ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-secondary" onclick="showDetails(<?= $submission['id'] ?>)">
                                View
                            </button>
                            <form action="<?= url('admin/pending/' . $submission['id'] . '/approve') ?>" method="POST" style="display: inline-flex; gap:.3rem; align-items:center;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="add_to_content" value="1">
                                <input type="number" name="amount" class="form-input" placeholder="₦ Amount" min="0" step="0.01" required style="width:100px;padding:.35rem .5rem;">
                                <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                            </form>
                            <form action="<?= url('admin/pending/' . $submission['id'] . '/needs-revision') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-secondary">Needs Revision</button>
                            </form>
                            <form action="<?= url('admin/pending/' . $submission['id'] . '/reject') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <tr id="details-<?= $submission['id'] ?>" style="display: none;">
                        <td colspan="6" style="background: #f9f9f9; padding: 1.5rem;">
                            <h4 style="margin-bottom: 1rem;">Submission Details</h4>
                            <?php if ($submission['description']): ?>
                                <p><strong>Description:</strong> <?= nl2br(e($submission['description'])) ?></p>
                            <?php endif; ?>
                            <?php if ($submission['additional_data']): ?>
                                <p style="margin-top: 1rem;"><strong>Additional Data:</strong></p>
                                <pre style="background: #fff; padding: 1rem; border: 1px solid #ddd; overflow-x: auto;">
<?= e(json_encode(json_decode($submission['additional_data']), JSON_PRETTY_PRINT)) ?>
                                </pre>
                            <?php endif; ?>
                            <div style="margin-top: 1rem;">
                                <form action="<?= url('admin/pending/' . $submission['id'] . '/approve') ?>" method="POST" style="display: inline-block; margin-right: 1rem;">
                                    <?= csrf_field() ?>
                                    <label class="form-checkbox" style="margin-bottom: 0.5rem;">
                                        <input type="checkbox" name="add_to_content" value="1" checked>
                                        <span>Add to archive</span>
                                    </label>
                                    <div style="margin-top: 0.5rem;">
                                        <input type="number" name="amount" class="form-input" placeholder="Amount earned (₦)" min="0" step="0.01" required style="margin-bottom: 0.5rem;">
                                        <input type="text" name="notes" class="form-input" placeholder="Notes (optional)" style="margin-bottom: 0.5rem;">
                                        <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                    </div>
                                </form>
                                <form action="<?= url('admin/pending/' . $submission['id'] . '/needs-revision') ?>" method="POST" style="display: inline-block; margin-right: 1rem;">
                                    <?= csrf_field() ?>
                                    <input type="text" name="notes" class="form-input" placeholder="What needs to change?" style="margin-bottom: 0.5rem;">
                                    <button type="submit" class="btn btn-sm btn-secondary">Needs Revision</button>
                                </form>
                                <form action="<?= url('admin/pending/' . $submission['id'] . '/reject') ?>" method="POST" style="display: inline-block;">
                                    <?= csrf_field() ?>
                                    <input type="text" name="notes" class="form-input" placeholder="Rejection reason" style="margin-bottom: 0.5rem;">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= pagination($pagination, url('admin/pending')) ?>
<?php endif; ?>

<script>
function showDetails(id) {
    const row = document.getElementById('details-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
