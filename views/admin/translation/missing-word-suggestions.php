<div class="admin-page-header">
    <h1 class="admin-page-title">Word Suggestions</h1>
    <p class="admin-page-subtitle">Community-submitted meanings for words not in the dictionary. Approve to add them to daily_words.</p>
</div>

<div class="admin-tab-nav" style="margin-bottom:1rem;">
    <a href="<?= url('admin/translation') ?>" class="admin-tab">Logs</a>
    <a href="<?= url('admin/translation/phrases') ?>" class="admin-tab">Phrases</a>
    <a href="<?= url('admin/translation/rules') ?>" class="admin-tab">Rules</a>
    <a href="<?= url('admin/translation/feedback') ?>" class="admin-tab">Feedback</a>
    <a href="<?= url('admin/translation/missing-words') ?>" class="admin-tab">Missing Words</a>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="admin-tab active">Word Suggestions</a>
</div>

<form method="GET" class="admin-filter-form" style="margin-bottom:1rem;">
    <select name="status" class="form-select">
        <option value="">All statuses</option>
        <?php foreach (['pending','approved','rejected'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['admin_review_status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="source_language" class="form-select">
        <option value="">Both directions</option>
        <option value="tiv" <?= ($filters['source_language'] ?? '') === 'tiv' ? 'selected' : '' ?>>Tiv → English</option>
        <option value="english" <?= ($filters['source_language'] ?? '') === 'english' ? 'selected' : '' ?>>English → Tiv</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="btn btn-sm">Clear</a>
</form>

<?php if (empty($suggestions)): ?>
    <div class="admin-empty">No word suggestions found.</div>
<?php else: ?>

<?php foreach ($suggestions as $s): ?>
<div class="suggestion-card">
    <div class="suggestion-header">
        <div class="suggestion-word-info">
            <span class="missing-word"><?= e($s['word']) ?></span>
            <span class="lang-dir"><?= strtoupper($s['source_language']) ?> → <?= strtoupper($s['target_language']) ?></span>
        </div>
        <span class="status-badge status-<?= $s['admin_review_status'] ?>">
            <?= ucfirst($s['admin_review_status']) ?>
        </span>
    </div>

    <div class="suggestion-body">
        <div class="suggestion-col">
            <span class="col-label">Suggested meaning</span>
            <p class="suggestion-text"><?= e($s['suggested_meaning']) ?></p>
        </div>
        <?php if (!empty($s['part_of_speech'])): ?>
        <div class="suggestion-col">
            <span class="col-label">Part of speech</span>
            <p><?= e($s['part_of_speech']) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($s['example_sentence'])): ?>
        <div class="suggestion-col">
            <span class="col-label">Example sentence</span>
            <p class="example-text"><?= e($s['example_sentence']) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($s['notes'])): ?>
        <div class="suggestion-col">
            <span class="col-label">Notes</span>
            <p><?= e($s['notes']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="suggestion-meta">
        <span>By <strong><?= e($s['user_name'] ?? 'Anonymous') ?></strong></span>
        <span><?= date('M j, Y H:i', strtotime($s['created_at'])) ?></span>
        <?php if (!empty($s['admin_notes'])): ?>
            <span class="admin-note-text">Admin note: <?= e($s['admin_notes']) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($s['admin_review_status'] === 'pending'): ?>
    <div class="suggestion-actions">
        <form method="POST" action="<?= url('admin/translation/word-suggestions/' . $s['id'] . '/approve') ?>"
              style="display:inline;"
              onsubmit="return confirm('Approve this suggestion and add \"<?= e(addslashes($s['word'])) ?>\" to the dictionary?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-success">Approve &amp; Add to Dictionary</button>
        </form>

        <form method="POST" action="<?= url('admin/translation/word-suggestions/' . $s['id'] . '/reject') ?>"
              style="display:inline;" class="reject-form"
              onsubmit="return confirmReject(this)">
            <?= csrf_field() ?>
            <input type="hidden" name="notes" class="reject-notes-input" value="">
            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
        </form>

        <a href="<?= url('admin/translation/missing-words/' . $s['missing_word_id'] . '/add') ?>"
           class="btn btn-sm btn-secondary" title="Open full word form">
            Edit &amp; Add
        </a>
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
.admin-tab-nav { display: flex; gap: .5rem; border-bottom: 2px solid #eee; flex-wrap: wrap; }
.admin-tab { padding: .5em 1em; font-size: .9rem; font-weight: 600; color: #666; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.admin-tab.active, .admin-tab:hover { color: var(--color-primary,#5C3A21); border-bottom-color: var(--color-primary,#5C3A21); }
.admin-filter-form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.admin-page-subtitle { color: #888; font-size: .9rem; margin-top: .25rem; }
.suggestion-card { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
.suggestion-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.suggestion-word-info { display: flex; align-items: center; gap: .6rem; }
.missing-word { font-size: 1.1rem; font-weight: 700; color: var(--color-primary,#5C3A21); }
.lang-dir { font-size: .78rem; background: #f0f0f0; padding: .15em .5em; border-radius: 4px; color: #666; }
.suggestion-body { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: .75rem; margin-bottom: .75rem; }
.suggestion-col .col-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #aaa; font-weight: 600; }
.suggestion-col p { margin: .2rem 0 0; font-size: .95rem; }
.suggestion-text { font-weight: 600; color: #222; }
.example-text { font-style: italic; color: #555; }
.suggestion-meta { display: flex; gap: .75rem; flex-wrap: wrap; font-size: .8rem; color: #888; margin-bottom: .75rem; }
.admin-note-text { color: #e67e22; font-style: italic; }
.suggestion-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.status-badge { padding: .2em .6em; border-radius: 10px; font-size: .75rem; font-weight: 600; }
.status-pending { background: #fff8e1; color: #f57f17; }
.status-approved { background: #e8f5e9; color: #2e7d32; }
.status-rejected { background: #fce4ec; color: #c62828; }
.btn-success { background: #2e7d32; color: #fff; border: none; }
.btn-success:hover { background: #1b5e20; }
</style>

<script>
function confirmReject(form) {
    const notes = prompt('Reason for rejection (optional):');
    if (notes === null) return false; // cancelled
    form.querySelector('.reject-notes-input').value = notes;
    return true;
}
</script>
