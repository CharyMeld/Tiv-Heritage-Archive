<div class="admin-page-header">
    <h1 class="admin-page-title">Translation Feedback</h1>
</div>

<div class="admin-tab-nav" style="margin-bottom:1rem;">
    <a href="<?= url('admin/translation') ?>" class="admin-tab">Logs</a>
    <a href="<?= url('admin/translation/phrases') ?>" class="admin-tab">Phrases</a>
    <a href="<?= url('admin/translation/rules') ?>" class="admin-tab">Rules</a>
    <a href="<?= url('admin/translation/feedback') ?>" class="admin-tab active">Feedback</a>
    <a href="<?= url('admin/translation/missing-words') ?>" class="admin-tab">Missing Words</a>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="admin-tab">Word Suggestions</a>
</div>

<form method="GET" class="admin-filter-form" style="margin-bottom:1rem;">
    <select name="status" class="form-select">
        <option value="">All statuses</option>
        <?php foreach (['pending','approved','rejected','converted'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['admin_review_status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="<?= url('admin/translation/feedback') ?>" class="btn btn-sm">Clear</a>
</form>

<?php if (empty($feedback)): ?>
    <div class="admin-empty">No feedback entries found.</div>
<?php else: ?>

<?php foreach ($feedback as $fb): ?>
<div class="feedback-item">
    <div class="feedback-header">
        <div class="feedback-meta">
            <span class="feedback-user"><?= e($fb['user_name'] ?? 'Anonymous') ?></span>
            <span class="feedback-date"><?= date('M j, Y H:i', strtotime($fb['created_at'])) ?></span>
            <span class="rating-display">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= (int)$fb['rating'] ? 'star-filled' : 'star-empty' ?>">&#9733;</span>
                <?php endfor; ?>
            </span>
        </div>
        <span class="badge <?= $fb['admin_review_status'] === 'pending' ? 'badge-warning' : ($fb['admin_review_status'] === 'approved' ? 'badge-success' : ($fb['admin_review_status'] === 'converted' ? 'badge-info' : '')) ?>">
            <?= ucfirst($fb['admin_review_status']) ?>
        </span>
    </div>

    <div class="feedback-translation">
        <div class="fb-col">
            <strong>Original:</strong>
            <p><?= e($fb['source_text']) ?></p>
        </div>
        <div class="fb-col">
            <strong>Engine output:</strong>
            <p><?= e($fb['translated_text']) ?></p>
        </div>
        <?php if (!empty($fb['suggested_correction'])): ?>
        <div class="fb-col fb-col--correction">
            <strong>Suggested correction:</strong>
            <p><?= e($fb['suggested_correction']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="fb-direction">
        <span class="badge"><?= strtoupper($fb['source_language']) ?> → <?= strtoupper($fb['target_language']) ?></span>
    </div>

    <?php if ($fb['admin_review_status'] === 'pending'): ?>
    <div class="feedback-actions">
        <form method="POST" action="<?= url('admin/translation/feedback/' . $fb['id'] . '/approve') ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-primary">Approve</button>
        </form>

        <?php if (!empty($fb['suggested_correction'])): ?>
        <form method="POST" action="<?= url('admin/translation/feedback/' . $fb['id'] . '/convert') ?>" style="display:inline;" onsubmit="return confirm('Convert this correction into a new translation phrase?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-success">Convert to Phrase</button>
        </form>
        <?php endif; ?>

        <form method="POST" action="<?= url('admin/translation/feedback/' . $fb['id'] . '/reject') ?>" style="display:inline;" onsubmit="return confirm('Reject this feedback?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php if ($paging['total_pages'] > 1): ?>
<div class="admin-pagination">
    <?php if ($paging['has_prev']): ?><a href="?page=<?= $paging['current_page']-1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn">&laquo;</a><?php endif; ?>
    <span>Page <?= $paging['current_page'] ?> of <?= $paging['total_pages'] ?></span>
    <?php if ($paging['has_next']): ?><a href="?page=<?= $paging['current_page']+1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn">&raquo;</a><?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<style>
.admin-tab-nav { display: flex; gap: .5rem; border-bottom: 2px solid #eee; }
.admin-tab { padding: .5em 1em; font-size: .9rem; font-weight: 600; color: #666; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.admin-tab.active, .admin-tab:hover { color: var(--color-primary,#5C3A21); border-bottom-color: var(--color-primary,#5C3A21); }
.admin-filter-form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.feedback-item { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
.feedback-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.feedback-meta { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.feedback-user { font-weight: 600; }
.feedback-date { font-size: .8rem; color: #888; }
.rating-display .star-filled { color: #f59e0b; }
.rating-display .star-empty { color: #ddd; }
.feedback-translation { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 1rem; margin-bottom: .75rem; }
.fb-col strong { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #888; }
.fb-col p { margin: .25rem 0 0; }
.fb-col--correction { background: #f0f9ff; padding: .5rem; border-radius: 6px; border-left: 3px solid #2196f3; }
.fb-direction { margin-bottom: .75rem; }
.feedback-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.badge-success { background:#e8f5e9;color:#2e7d32; }
.badge-warning { background:#fff8e1;color:#f57f17; }
.badge-info { background:#e3f2fd;color:#1565c0; }
.btn-success { background: #2e7d32; color: #fff; }
</style>
