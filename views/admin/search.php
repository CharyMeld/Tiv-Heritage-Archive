<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128269; Search Content</h1>
        <p class="admin-page-sub">Find and edit any entry across all categories</p>
    </div>
    <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; Dashboard</a>
</div>

<!-- Search Form -->
<form method="GET" action="<?= url('admin/search') ?>" class="admin-search-form">
    <div class="admin-search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" class="admin-search-icon">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
        </svg>
        <input type="text" name="q" value="<?= e($q ?? '') ?>"
               placeholder="Search by Tiv name, English meaning, description..."
               class="admin-search-input" autofocus autocomplete="off">
        <button type="submit" class="btn btn-primary btn-sm" style="border-radius:50px;">Search</button>
        <?php if (!empty($q)): ?>
        <a href="<?= url('admin/search') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (!empty($q) && empty($results)): ?>
    <div class="empty-state">
        <h3>No results for &ldquo;<?= e($q) ?>&rdquo;</h3>
        <p>Try a shorter or different term. Search checks Tiv text and English meanings.</p>
    </div>
<?php elseif (!empty($results)): ?>
    <?php
    $totalFound = array_sum(array_map(fn($r) => count($r['items']), $results));
    ?>
    <p class="admin-search-summary">
        Found <strong><?= $totalFound ?></strong> result<?= $totalFound !== 1 ? 's' : '' ?>
        for &ldquo;<strong><?= e($q) ?></strong>&rdquo; across <?= count($results) ?> categor<?= count($results) !== 1 ? 'ies' : 'y' ?>
    </p>

    <?php foreach ($results as $category => $group): ?>
    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div class="admin-card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1rem;">
                <?= e($group['label']) ?>
                <span style="font-size:0.8rem;font-weight:400;color:var(--color-text-muted);">
                    &mdash; <?= count($group['items']) ?> match<?= count($group['items']) !== 1 ? 'es' : '' ?>
                </span>
            </h3>
            <a href="<?= url('admin/content/' . $category . '?q=' . urlencode($q)) ?>"
               style="font-size:0.82rem;color:var(--color-accent);">
                View in category &rsaquo;
            </a>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($category === 'names'): ?>
                            <th>Tiv Name</th><th>English Meaning</th><th>Gender</th>
                        <?php elseif ($category === 'proverbs'): ?>
                            <th>Tiv Text</th><th>English Translation</th><th>Category</th>
                        <?php elseif ($category === 'words'): ?>
                            <th>Tiv Word</th><th>English Meaning</th><th>Part of Speech</th>
                        <?php elseif ($category === 'videos'): ?>
                            <th>Title</th><th>Category</th><th>Difficulty</th>
                        <?php else: ?>
                            <th>Tiv Name</th><th>English Name</th><th>Featured</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($group['items'] as $item): ?>
                    <tr>
                        <td><?= e($item['id']) ?></td>
                        <?php if ($category === 'names'): ?>
                            <td><strong><?= e($item['tiv_name']) ?></strong></td>
                            <td><?= e($item['english_meaning']) ?></td>
                            <td><?= ucfirst(e($item['gender'])) ?></td>
                        <?php elseif ($category === 'proverbs'): ?>
                            <td><strong><?= e(mb_substr($item['tiv_text'], 0, 55)) ?>…</strong></td>
                            <td><?= e(mb_substr($item['english_translation'] ?? '', 0, 55)) ?>…</td>
                            <td><?= e($item['category'] ?? '-') ?></td>
                        <?php elseif ($category === 'words'): ?>
                            <td><strong><?= e($item['tiv_word']) ?></strong></td>
                            <td><?= e($item['english_meaning']) ?></td>
                            <td><?= e($item['part_of_speech']) ?></td>
                        <?php elseif ($category === 'videos'): ?>
                            <td><strong><?= e(mb_substr($item['title'], 0, 55)) ?></strong></td>
                            <td><?= e($item['category'] ?? '-') ?></td>
                            <td><?= e($item['difficulty'] ?? '-') ?></td>
                        <?php else: ?>
                            <td><strong><?= e($item['tiv_name']) ?></strong></td>
                            <td><?= e($item['english_name'] ?? '-') ?></td>
                            <td><?= $item['is_featured'] ? 'Yes' : 'No' ?></td>
                        <?php endif; ?>
                        <td class="actions">
                            <a href="<?= url('admin/content/' . $category . '/' . $item['id'] . '/edit') ?>"
                               class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php elseif (empty($q)): ?>
    <div class="admin-search-tips">
        <h3>How to use search</h3>
        <ul>
            <li>Type any Tiv word, English meaning, or phrase to find matching entries across all categories.</li>
            <li>Search checks: Tiv names, English meanings, translations, and descriptions.</li>
            <li>Click <strong>Edit</strong> next to any result to fix a mistake immediately.</li>
            <li>Use the category search bar on any content page to search within a single category.</li>
        </ul>
    </div>
<?php endif; ?>
