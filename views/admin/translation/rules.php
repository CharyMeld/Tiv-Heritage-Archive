<div class="admin-page-header">
    <h1 class="admin-page-title">Translation Rules</h1>
    <a href="<?= url('admin/translation/rules/create') ?>" class="btn btn-primary btn-sm">+ Add Rule</a>
</div>

<div class="admin-tab-nav" style="margin-bottom:1rem;">
    <a href="<?= url('admin/translation') ?>" class="admin-tab">Logs</a>
    <a href="<?= url('admin/translation/phrases') ?>" class="admin-tab">Phrases</a>
    <a href="<?= url('admin/translation/rules') ?>" class="admin-tab active">Rules</a>
    <a href="<?= url('admin/translation/feedback') ?>" class="admin-tab">Feedback</a>
    <a href="<?= url('admin/translation/missing-words') ?>" class="admin-tab">Missing Words</a>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="admin-tab">Word Suggestions</a>
</div>

<p class="admin-help-text" style="margin-bottom:1rem;color:#666;font-size:.9rem;">
    Rules are applied <strong>after</strong> translation assembly to correct common structural errors.
    Higher priority rules run first.
</p>

<form method="GET" class="admin-filter-form" style="margin-bottom:1rem;">
    <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Search rules…" class="form-input">
    <select name="source_language" class="form-select">
        <option value="">All languages</option>
        <option value="tiv"     <?= $filters['source_language'] === 'tiv'     ? 'selected' : '' ?>>Tiv source</option>
        <option value="english" <?= $filters['source_language'] === 'english' ? 'selected' : '' ?>>English source</option>
    </select>
    <select name="rule_type" class="form-select">
        <option value="">All types</option>
        <?php foreach (['word_order','phrase_fix','common_word','structure','other'] as $rt): ?>
        <option value="<?= $rt ?>" <?= $filters['rule_type'] === $rt ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$rt)) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="<?= url('admin/translation/rules') ?>" class="btn btn-sm">Clear</a>
</form>

<?php if (empty($rules)): ?>
    <div class="admin-empty">No rules found. <a href="<?= url('admin/translation/rules/create') ?>">Add one</a>.</div>
<?php else: ?>
<div style="overflow-x:auto;">
<table class="admin-table">
    <thead>
        <tr>
            <th>Rule Name</th>
            <th>Pattern</th>
            <th>Replacement</th>
            <th>Direction</th>
            <th>Type</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rules as $rule): ?>
        <tr>
            <td><strong><?= e($rule['rule_name']) ?></strong></td>
            <td><code><?= e($rule['pattern_text']) ?></code></td>
            <td><code><?= e($rule['replacement_text']) ?></code></td>
            <td><span class="badge"><?= strtoupper($rule['source_language']) ?> → <?= strtoupper($rule['target_language']) ?></span></td>
            <td><?= e(ucfirst(str_replace('_',' ',$rule['rule_type']))) ?></td>
            <td><?= (int)$rule['priority_score'] ?></td>
            <td>
                <span class="badge <?= $rule['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                    <?= ucfirst($rule['status']) ?>
                </span>
            </td>
            <td>
                <div class="table-actions">
                    <a href="<?= url('admin/translation/rules/' . $rule['id'] . '/edit') ?>" class="btn btn-sm btn-secondary">Edit</a>
                    <form method="POST" action="<?= url('admin/translation/rules/' . $rule['id'] . '/delete') ?>" style="display:inline;" onsubmit="return confirm('Delete this rule?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if ($paging['total_pages'] > 1): ?>
<div class="admin-pagination">
    <?php if ($paging['has_prev']): ?><a href="?page=<?= $paging['current_page']-1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn">&laquo;</a><?php endif; ?>
    <span>Page <?= $paging['current_page'] ?> of <?= $paging['total_pages'] ?></span>
    <?php if ($paging['has_next']): ?><a href="?page=<?= $paging['current_page']+1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="page-btn">&raquo;</a><?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<style>
.admin-tab-nav { display: flex; gap: .5rem; border-bottom: 2px solid #eee; padding-bottom: 0; }
.admin-tab { padding: .5em 1em; font-size: .9rem; font-weight: 600; color: #666; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.admin-tab.active, .admin-tab:hover { color: var(--color-primary,#5C3A21); border-bottom-color: var(--color-primary,#5C3A21); }
.admin-filter-form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.table-actions { display: flex; gap: .4rem; }
.badge-success { background:#e8f5e9;color:#2e7d32; }
.badge-warning { background:#fff8e1;color:#f57f17; }
code { background:#f5f5f5;padding:.1em .4em;border-radius:4px;font-size:.85em; }
</style>
