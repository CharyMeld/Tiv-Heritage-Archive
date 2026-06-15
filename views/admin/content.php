<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#9999;&#65039; <?= ucfirst(e($category ?? 'Content')) ?></h1>
        <p class="admin-page-sub">Manage <?= e($category ?? 'content') ?> entries</p>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="<?= url('admin/content/'.$category.'/create') ?>" class="btn btn-primary btn-sm" style="border-radius:50px;">+ Add New</a>
        <?php if ($category === 'words'): ?>
        <a href="<?= url('admin/content/words/bulk-upload') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8679; Bulk Upload</a>
        <?php endif; ?>
        <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; Dashboard</a>
    </div>
</div>

<!-- Per-category search -->
<form method="GET" action="<?= url('admin/content/' . $category) ?>" class="admin-search-form">
    <div class="admin-search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" class="admin-search-icon">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
        </svg>
        <input type="text" name="q" value="<?= e($q ?? '') ?>"
               placeholder="Search <?= e($category) ?>..."
               class="admin-search-input" autocomplete="off">
        <button type="submit" class="btn btn-primary btn-sm" style="border-radius:50px;">Search</button>
        <?php if (!empty($q)): ?>
        <a href="<?= url('admin/content/' . $category) ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (!empty($q) && empty($items)): ?>
    <div class="empty-state">
        <h3>No results for &ldquo;<?= e($q) ?>&rdquo;</h3>
        <p>Try a different search term.</p>
        <a href="<?= url('admin/content/' . $category) ?>" class="btn btn-secondary">Show all <?= e($category) ?></a>
    </div>
<?php elseif (empty($items)): ?>
    <div class="empty-state">
        <h3>No <?= e($category) ?> found</h3>
        <p>Add your first entry to get started.</p>
        <a href="<?= url('admin/content/' . $category . '/create') ?>" class="btn btn-primary">Add New</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <?php if ($category === 'names'): ?>
                        <th>Tiv Name</th>
                        <th>English Meaning</th>
                        <th>Gender</th>
                    <?php elseif ($category === 'proverbs'): ?>
                        <th>Tiv Text</th>
                        <th>English Translation</th>
                        <th>Category</th>
                    <?php elseif ($category === 'words'): ?>
                        <th>Tiv Word</th>
                        <th>English Meaning</th>
                        <th>Part of Speech</th>
                    <?php elseif ($category === 'animals'): ?>
                        <th>Tiv Name</th>
                        <th>English Name</th>
                        <th>Cultural Use</th>
                    <?php else: ?>
                        <th>Tiv Name</th>
                        <th>English Name</th>
                        <th>Featured</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['id']) ?></td>
                        <?php if ($category === 'names'): ?>
                            <td><strong><?= e($item['tiv_name']) ?></strong></td>
                            <td><?= e($item['english_meaning']) ?></td>
                            <td><?= ucfirst(e($item['gender'])) ?></td>
                        <?php elseif ($category === 'proverbs'): ?>
                            <td><strong><?= e(mb_substr($item['tiv_text'], 0, 50)) ?>...</strong></td>
                            <td><?= e(mb_substr($item['english_translation'], 0, 50)) ?>...</td>
                            <td><?= e($item['category'] ?? '-') ?></td>
                        <?php elseif ($category === 'words'): ?>
                            <td><strong><?= e($item['tiv_word']) ?></strong></td>
                            <td><?= e($item['english_meaning']) ?></td>
                            <td><?= e($item['part_of_speech']) ?></td>
                        <?php elseif ($category === 'animals'): ?>
                            <td><strong><?= e($item['tiv_name']) ?></strong></td>
                            <td><?= e($item['name'] ?? '-') ?></td>
                            <td><?= e(mb_substr($item['cultural_use'] ?? '-', 0, 50)) ?></td>
                        <?php else: ?>
                            <td><strong><?= e($item['tiv_name']) ?></strong></td>
                            <td><?= e($item['english_name'] ?? '-') ?></td>
                            <td><?= $item['is_featured'] ? 'Yes' : 'No' ?></td>
                        <?php endif; ?>
                        <td class="actions">
                            <a href="<?= url('admin/content/' . $category . '/' . $item['id'] . '/edit') ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="<?= url('admin/content/' . $category . '/' . $item['id'] . '/delete') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to delete this item?">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($q) && $pagination): ?>
    <?= pagination($pagination, url('admin/content/' . $category)) ?>
    <?php elseif (!empty($q)): ?>
    <p style="padding:0.75rem 1rem;font-size:0.85rem;color:var(--color-text-muted);">
        Showing <?= count($items) ?> result<?= count($items) !== 1 ? 's' : '' ?> for &ldquo;<?= e($q) ?>&rdquo;
        &mdash; <a href="<?= url('admin/content/' . $category) ?>">Show all</a>
    </p>
    <?php endif; ?>
<?php endif; ?>
