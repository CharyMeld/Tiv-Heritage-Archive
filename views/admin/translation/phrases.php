<div class="admin-page-header">
    <h1 class="admin-page-title">Translation Phrases</h1>
    <a href="<?= url('admin/translation/phrases/create') ?>" class="btn btn-primary btn-sm">+ Add Phrase</a>
</div>

<div class="admin-tab-nav" style="margin-bottom:1rem;">
    <a href="<?= url('admin/translation') ?>" class="admin-tab">Logs</a>
    <a href="<?= url('admin/translation/phrases') ?>" class="admin-tab active">Phrases</a>
    <a href="<?= url('admin/translation/rules') ?>" class="admin-tab">Rules</a>
    <a href="<?= url('admin/translation/feedback') ?>" class="admin-tab">Feedback</a>
    <a href="<?= url('admin/translation/missing-words') ?>" class="admin-tab">Missing Words</a>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="admin-tab">Word Suggestions</a>
</div>

<!-- Phrase Stats -->
<div class="admin-stats-grid" style="margin-bottom:1.5rem;">
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($phraseStats['total'] ?? 0)) ?></div>
        <div class="stat-label">Total Phrases</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($phraseStats['active'] ?? 0)) ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($phraseStats['inactive'] ?? 0)) ?></div>
        <div class="stat-label">Inactive</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($phraseStats['tiv_source'] ?? 0)) ?></div>
        <div class="stat-label">Tiv → English</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($phraseStats['english_source'] ?? 0)) ?></div>
        <div class="stat-label">English → Tiv</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((float)($phraseStats['avg_confidence'] ?? 0), 1) ?>%</div>
        <div class="stat-label">Avg Confidence</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($phraseStats['multi_word'] ?? 0)) ?></div>
        <div class="stat-label">Multi-word Phrases</div>
    </div>
</div>

<form method="GET" class="admin-filter-form" style="margin-bottom:1rem;">
    <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Search phrases…" class="form-input">
    <select name="source_language" class="form-select">
        <option value="">All languages</option>
        <option value="tiv"     <?= $filters['source_language'] === 'tiv'     ? 'selected' : '' ?>>Tiv source</option>
        <option value="english" <?= $filters['source_language'] === 'english' ? 'selected' : '' ?>>English source</option>
    </select>
    <?php if (!empty($tags)): ?>
    <select name="context_tag" class="form-select">
        <option value="">All contexts</option>
        <?php foreach ($tags as $tag): ?>
        <option value="<?= e($tag) ?>" <?= $filters['context_tag'] === $tag ? 'selected' : '' ?>><?= e(ucfirst($tag)) ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="<?= url('admin/translation/phrases') ?>" class="btn btn-sm">Clear</a>
</form>

<?php if (empty($phrases)): ?>
    <div class="admin-empty">No phrases found. <a href="<?= url('admin/translation/phrases/create') ?>">Add one</a>.</div>
<?php else: ?>
<div style="overflow-x:auto;">
<table class="admin-table">
    <thead>
        <tr>
            <th>Source Text</th>
            <th>Target Text</th>
            <th>Direction</th>
            <th>Context</th>
            <th>Confidence</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($phrases as $phrase): ?>
        <tr>
            <td style="max-width:220px;word-break:break-word;"><?= e(mb_strimwidth($phrase['source_text'], 0, 80, '…')) ?></td>
            <td style="max-width:220px;word-break:break-word;"><?= e(mb_strimwidth($phrase['target_text'], 0, 80, '…')) ?></td>
            <td><span class="badge"><?= strtoupper($phrase['source_language']) ?> → <?= strtoupper($phrase['target_language']) ?></span></td>
            <td><?= e($phrase['context_tag'] ?? '—') ?></td>
            <td><?= (int)$phrase['confidence_score'] ?>%</td>
            <td>
                <span class="badge <?= $phrase['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>">
                    <?= ucfirst($phrase['status']) ?>
                </span>
            </td>
            <td>
                <div class="table-actions">
                    <a href="<?= url('admin/translation/phrases/' . $phrase['id'] . '/edit') ?>" class="btn btn-sm btn-secondary">Edit</a>
                    <form method="POST" action="<?= url('admin/translation/phrases/' . $phrase['id'] . '/delete') ?>" style="display:inline;" onsubmit="return confirm('Delete this phrase?')">
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
</style>
