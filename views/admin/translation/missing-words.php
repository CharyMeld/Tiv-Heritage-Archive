<div class="admin-page-header">
    <h1 class="admin-page-title">Missing Words</h1>
    <p class="admin-page-subtitle">Words that users searched for but were not in any dictionary source.</p>
</div>

<div class="admin-tab-nav" style="margin-bottom:1rem;">
    <a href="<?= url('admin/translation') ?>" class="admin-tab">Logs</a>
    <a href="<?= url('admin/translation/phrases') ?>" class="admin-tab">Phrases</a>
    <a href="<?= url('admin/translation/rules') ?>" class="admin-tab">Rules</a>
    <a href="<?= url('admin/translation/feedback') ?>" class="admin-tab">Feedback</a>
    <a href="<?= url('admin/translation/missing-words') ?>" class="admin-tab active">Missing Words</a>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="admin-tab">
        Word Suggestions
        <?php if (!empty($pendingSuggestions) && $pendingSuggestions > 0): ?>
            <span class="badge-count"><?= (int)$pendingSuggestions ?></span>
        <?php endif; ?>
    </a>
</div>

<!-- Filters -->
<form method="GET" class="admin-filter-form" style="margin-bottom:1rem;">
    <select name="status" class="form-select">
        <option value="">All statuses</option>
        <?php foreach (['missing','has_suggestions','approved','rejected'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
            <?= ucfirst(str_replace('_', ' ', $s)) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <select name="source_language" class="form-select">
        <option value="">Both directions</option>
        <option value="tiv" <?= ($filters['source_language'] ?? '') === 'tiv' ? 'selected' : '' ?>>Tiv → English</option>
        <option value="english" <?= ($filters['source_language'] ?? '') === 'english' ? 'selected' : '' ?>>English → Tiv</option>
    </select>
    <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Search word…" class="form-control" style="width:200px;">
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="<?= url('admin/translation/missing-words') ?>" class="btn btn-sm">Clear</a>
</form>

<?php if (empty($words)): ?>
    <div class="admin-empty">No missing words found. Great — the dictionary is covering everything searched so far!</div>
<?php else: ?>

<div class="missing-words-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Word</th>
                <th>Direction</th>
                <th>Searches</th>
                <th>Suggestions</th>
                <th>Status</th>
                <th>Last Searched</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($words as $w): ?>
            <tr>
                <td class="word-cell"><strong><?= e($w['word']) ?></strong></td>
                <td><span class="lang-dir"><?= strtoupper($w['source_language']) ?> → <?= strtoupper($w['target_language']) ?></span></td>
                <td>
                    <span class="search-count <?= (int)$w['search_count'] >= 10 ? 'count-high' : ((int)$w['search_count'] >= 3 ? 'count-medium' : '') ?>">
                        <?= (int)$w['search_count'] ?>
                    </span>
                </td>
                <td>
                    <?php if ((int)$w['total_suggestions'] > 0): ?>
                        <span class="sugg-count">
                            <?= (int)$w['total_suggestions'] ?> total
                            <?php if ((int)$w['pending_suggestions'] > 0): ?>
                                <span class="badge-count"><?= (int)$w['pending_suggestions'] ?> pending</span>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="no-sugg">None yet</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge status-<?= $w['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $w['status'])) ?>
                    </span>
                </td>
                <td class="date-cell"><?= date('M j, Y', strtotime($w['last_searched_at'])) ?></td>
                <td>
                    <div class="action-btns">
                        <?php if (in_array($w['status'], ['missing', 'has_suggestions'])): ?>
                        <a href="<?= url('admin/translation/missing-words/' . $w['id'] . '/add') ?>"
                           class="btn btn-sm btn-primary" title="Add to dictionary">
                            + Add to Dictionary
                        </a>
                        <form method="POST" action="<?= url('admin/translation/missing-words/' . $w['id'] . '/reject') ?>" style="display:inline;"
                              onsubmit="return confirm('Mark this word as rejected (close without adding)?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                        <?php elseif ($w['status'] === 'approved'): ?>
                            <span class="approved-note">Added to dictionary</span>
                        <?php else: ?>
                            <span class="rejected-note">Closed</span>
                        <?php endif; ?>
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
.admin-tab-nav { display: flex; gap: .5rem; border-bottom: 2px solid #eee; flex-wrap: wrap; }
.admin-tab { padding: .5em 1em; font-size: .9rem; font-weight: 600; color: #666; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; position: relative; }
.admin-tab.active, .admin-tab:hover { color: var(--color-primary,#5C3A21); border-bottom-color: var(--color-primary,#5C3A21); }
.badge-count { display: inline-flex; align-items: center; justify-content: center; background: #e53935; color: #fff; border-radius: 10px; font-size: .7rem; font-weight: 700; padding: .1em .45em; min-width: 18px; margin-left: .25rem; }
.admin-filter-form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.admin-page-subtitle { color: #888; font-size: .9rem; margin-top: .25rem; }
.missing-words-table-wrap { overflow-x: auto; }
.admin-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
.admin-table th { background: #f9f9f9; font-weight: 700; text-align: left; padding: .6em .8em; border-bottom: 2px solid #eee; color: #555; }
.admin-table td { padding: .6em .8em; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.word-cell { font-size: 1rem; }
.lang-dir { font-size: .8rem; background: #f0f0f0; padding: .15em .5em; border-radius: 4px; }
.search-count { font-weight: 700; font-size: .95rem; }
.count-high { color: #c0392b; }
.count-medium { color: #e67e22; }
.sugg-count { font-size: .85rem; }
.no-sugg { font-size: .85rem; color: #bbb; }
.date-cell { font-size: .8rem; color: #888; }
.action-btns { display: flex; gap: .4rem; flex-wrap: wrap; align-items: center; }
.status-badge { padding: .2em .6em; border-radius: 10px; font-size: .75rem; font-weight: 600; }
.status-missing { background: #fce4ec; color: #c62828; }
.status-has_suggestions { background: #fff8e1; color: #f57f17; }
.status-approved { background: #e8f5e9; color: #2e7d32; }
.status-rejected { background: #f5f5f5; color: #888; }
.approved-note { font-size: .8rem; color: #2e7d32; font-weight: 600; }
.rejected-note { font-size: .8rem; color: #aaa; }
</style>
