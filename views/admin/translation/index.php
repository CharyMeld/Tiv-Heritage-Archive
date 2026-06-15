<div class="admin-page-header">
    <h1 class="admin-page-title">Translation Engine</h1>
    <div class="admin-page-actions">
        <a href="<?= url('admin/translation/phrases/create') ?>" class="btn btn-primary btn-sm">+ Add Phrase</a>
        <a href="<?= url('admin/translation/rules/create') ?>" class="btn btn-secondary btn-sm">+ Add Rule</a>
    </div>
</div>

<!-- Stats Row -->
<div class="admin-stats-grid" style="margin-bottom:2rem;">
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
        <div class="stat-label">Total Translations</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($stats['today'] ?? 0)) ?></div>
        <div class="stat-label">Today</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= round((float)($stats['avg_confidence'] ?? 0)) ?>%</div>
        <div class="stat-label">Avg Confidence</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= number_format((int)($stats['failed'] ?? 0)) ?></div>
        <div class="stat-label">Failed (no result)</div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-value"><?= (int)$pendingFeedback ?></div>
        <div class="stat-label">Pending Feedback</div>
        <?php if ($pendingFeedback > 0): ?>
        <a href="<?= url('admin/translation/feedback') ?>" class="stat-link">Review</a>
        <?php endif; ?>
    </div>
</div>

<!-- Match type breakdown -->
<div class="admin-two-col" style="margin-bottom:2rem;">
    <div class="admin-panel">
        <h3 class="admin-panel-title">Match Type Breakdown</h3>
        <?php
            $matchTypes = [
                'proverb_hits'  => ['Proverb',     'conf-high'],
                'phrase_hits'   => ['Phrase',       'conf-high'],
                'word_hits'     => ['Word',         'conf-medium'],
                'category_hits' => ['Category',     'conf-medium'],
                'partial_hits'  => ['Word-by-Word', 'conf-low'],
                'failed'        => ['None/Failed',  'conf-low'],
            ];
            $total = max(1, (int)($stats['total'] ?? 1));
        ?>
        <div style="display:flex;flex-direction:column;gap:.5rem;">
            <?php foreach ($matchTypes as $key => [$label, $cls]): ?>
            <?php $count = (int)($stats[$key] ?? 0); $pct = round($count / $total * 100); ?>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:2px;">
                    <span><?= $label ?></span>
                    <span><?= $count ?> (<?= $pct ?>%)</span>
                </div>
                <div style="background:#f0f0f0;border-radius:4px;height:8px;overflow:hidden;">
                    <div style="width:<?= $pct ?>%;height:100%;background:var(--color-primary,#5C3A21);border-radius:4px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="admin-panel">
        <h3 class="admin-panel-title">Top Searched Queries</h3>
        <?php if (empty($topQueries)): ?>
            <p class="text-muted">No data yet.</p>
        <?php else: ?>
        <table class="admin-table admin-table--compact">
            <thead><tr><th>Query</th><th>Lang</th><th>Count</th><th>Avg Conf</th></tr></thead>
            <tbody>
                <?php foreach ($topQueries as $q): ?>
                <tr>
                    <td><?= e(mb_strimwidth($q['source_text'], 0, 40, '…')) ?></td>
                    <td><?= strtoupper($q['source_language']) ?></td>
                    <td><?= (int)$q['search_count'] ?></td>
                    <td><?= round((float)$q['avg_confidence']) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Navigation tabs -->
<div class="admin-tab-nav" style="margin-bottom:1rem;">
    <a href="<?= url('admin/translation') ?>" class="admin-tab active">Logs</a>
    <a href="<?= url('admin/translation/phrases') ?>" class="admin-tab">Phrases</a>
    <a href="<?= url('admin/translation/rules') ?>" class="admin-tab">Rules</a>
    <a href="<?= url('admin/translation/feedback') ?>" class="admin-tab">
        Feedback
        <?php if ($pendingFeedback > 0): ?>
            <span class="badge"><?= $pendingFeedback ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= url('admin/translation/missing-words') ?>" class="admin-tab">Missing Words</a>
    <a href="<?= url('admin/translation/word-suggestions') ?>" class="admin-tab">Word Suggestions</a>
</div>

<!-- Filters -->
<form method="GET" class="admin-filter-form" style="margin-bottom:1rem;">
    <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Search translations…" class="form-input">
    <select name="match_type" class="form-select">
        <option value="">All match types</option>
        <?php foreach (['proverb','phrase','word','category','word_by_word','none'] as $mt): ?>
        <option value="<?= $mt ?>" <?= $filters['match_type'] === $mt ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$mt)) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="source_language" class="form-select">
        <option value="">All languages</option>
        <option value="tiv"     <?= $filters['source_language'] === 'tiv'     ? 'selected' : '' ?>>Tiv → English</option>
        <option value="english" <?= $filters['source_language'] === 'english' ? 'selected' : '' ?>>English → Tiv</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="<?= url('admin/translation') ?>" class="btn btn-sm">Clear</a>
</form>

<!-- Logs Table -->
<?php if (empty($logs)): ?>
    <div class="admin-empty">No translation logs found.</div>
<?php else: ?>
<div style="overflow-x:auto;">
<table class="admin-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Source</th>
            <th>Translation</th>
            <th>Direction</th>
            <th>Match</th>
            <th>Confidence</th>
            <th>User</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= (int)$log['id'] ?></td>
            <td style="max-width:200px;word-break:break-word;"><?= e(mb_strimwidth($log['source_text'], 0, 60, '…')) ?></td>
            <td style="max-width:200px;word-break:break-word;"><?= e(mb_strimwidth($log['translated_text'], 0, 60, '…')) ?></td>
            <td><span class="badge"><?= strtoupper($log['source_language']) ?> → <?= strtoupper($log['target_language']) ?></span></td>
            <td>
                <?php $mt = $log['match_type']; $cls = in_array($mt,['proverb','phrase']) ? 'badge-success' : (in_array($mt,['word','category']) ? 'badge-info' : 'badge-warning'); ?>
                <span class="badge <?= $cls ?>"><?= e(ucfirst(str_replace('_',' ',$mt))) ?></span>
            </td>
            <td>
                <?php $c=(int)$log['confidence_score']; $cc=$c>=80?'badge-success':($c>=50?'badge-warning':'badge-danger'); ?>
                <span class="badge <?= $cc ?>"><?= $c ?>%</span>
            </td>
            <td><?= e($log['user_name'] ?? 'Guest') ?></td>
            <td style="white-space:nowrap;font-size:.8rem;color:#888;"><?= date('M j, H:i', strtotime($log['created_at'])) ?></td>
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
.admin-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 700px) { .admin-two-col { grid-template-columns: 1fr; } }
.admin-panel { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
.admin-panel-title { font-size: .9rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--color-primary,#5C3A21); margin-bottom: 1rem; }
.admin-tab-nav { display: flex; gap: .5rem; border-bottom: 2px solid #eee; padding-bottom: 0; }
.admin-tab { padding: .5em 1em; font-size: .9rem; font-weight: 600; color: #666; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.admin-tab.active, .admin-tab:hover { color: var(--color-primary,#5C3A21); border-bottom-color: var(--color-primary,#5C3A21); }
.admin-tab .badge { background: #e74c3c; color:#fff; font-size:.7rem; padding:.1em .4em; border-radius:10px; margin-left:.3rem; }
.admin-filter-form { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.stat-link { font-size: .75rem; color: var(--color-primary,#5C3A21); text-decoration: none; }
.badge-success { background:#e8f5e9;color:#2e7d32; }
.badge-info { background:#e3f2fd;color:#1565c0; }
.badge-warning { background:#fff8e1;color:#f57f17; }
.badge-danger { background:#fce4ec;color:#c62828; }
</style>
