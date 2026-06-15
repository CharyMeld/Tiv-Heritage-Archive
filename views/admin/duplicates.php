<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#9898; Duplicate Entries</h1>
        <p class="admin-page-sub">
            <?= $totalGroups > 0
                ? $totalGroups . ' duplicate group' . ($totalGroups > 1 ? 's' : '') . ' found — keep the best copy and delete the rest'
                : 'No duplicates found — your archive is clean!' ?>
        </p>
    </div>
    <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; Dashboard</a>
</div>

<?php if (empty($groups)): ?>
    <div class="empty-state">
        <h3>&#10003; No duplicates found</h3>
        <p>All entries in your archive are unique.</p>
    </div>
<?php else: ?>

<style>
.dup-group { background:#fff; border:1px solid #e5e7eb; border-radius:12px; margin-bottom:2rem; overflow:hidden; }
.dup-group-header { background:#fef3c7; padding:1rem 1.5rem; border-bottom:1px solid #fde68a; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
.dup-group-title { font-weight:700; font-size:1rem; color:#92400e; }
.dup-group-meta { font-size:.8rem; color:#b45309; background:#fde68a; padding:.2rem .7rem; border-radius:50px; }
.dup-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; padding:1.5rem; }
.dup-card { border:1px solid #e5e7eb; border-radius:10px; padding:1rem; position:relative; }
.dup-card.oldest { border-color:#3b82f6; }
.dup-card.oldest::before { content:'OLDEST'; position:absolute; top:-1px; right:-1px; background:#3b82f6; color:#fff; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:0 10px 0 8px; }
.dup-card.newest { border-color:#10b981; }
.dup-card.newest::before { content:'NEWEST'; position:absolute; top:-1px; right:-1px; background:#10b981; color:#fff; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:0 10px 0 8px; }
.dup-field { font-size:.8rem; color:#6b7280; margin-bottom:.2rem; }
.dup-field strong { color:#111; }
.dup-delete-form { margin-top:1rem; }
.btn-danger-sm { background:#ef4444; color:#fff; border:none; padding:.4rem 1rem; border-radius:6px; font-size:.82rem; cursor:pointer; }
.btn-danger-sm:hover { background:#dc2626; }
</style>

<?php foreach ($groups as $group): ?>
<div class="dup-group">
    <div class="dup-group-header">
        <span class="dup-group-title">
            &ldquo;<?= e($group['term']) ?>&rdquo; &mdash; <?= e($group['label']) ?>
        </span>
        <span class="dup-group-meta"><?= $group['count'] ?> copies</span>
    </div>

    <div class="dup-cards">
        <?php $last = count($group['rows']) - 1; ?>
        <?php foreach ($group['rows'] as $i => $row): ?>
        <?php $cardClass = $i === 0 ? 'oldest' : ($i === $last ? 'newest' : ''); ?>
        <div class="dup-card <?= $cardClass ?>">

            <div class="dup-field">ID: <strong>#<?= e($row['id']) ?></strong></div>

            <?php if ($group['category'] === 'words'): ?>
                <div class="dup-field">Word: <strong><?= e($row['tiv_word']) ?></strong></div>
                <div class="dup-field">Meaning: <strong><?= e($row['english_meaning'] ?? '—') ?></strong></div>
                <div class="dup-field">Part of speech: <strong><?= e($row['part_of_speech'] ?? '—') ?></strong></div>
                <div class="dup-field">Example: <strong><?= e($row['example_tiv'] ?? '—') ?></strong></div>

            <?php elseif ($group['category'] === 'names'): ?>
                <div class="dup-field">Name: <strong><?= e($row['tiv_name']) ?></strong></div>
                <div class="dup-field">Meaning: <strong><?= e($row['english_meaning'] ?? '—') ?></strong></div>
                <div class="dup-field">Gender: <strong><?= e($row['gender'] ?? '—') ?></strong></div>
                <div class="dup-field">Description: <strong><?= e(mb_strimwidth($row['description'] ?? '—', 0, 80, '…')) ?></strong></div>

            <?php elseif ($group['category'] === 'proverbs'): ?>
                <div class="dup-field">Text: <strong><?= e(mb_strimwidth($row['tiv_text'], 0, 80, '…')) ?></strong></div>
                <div class="dup-field">Translation: <strong><?= e(mb_strimwidth($row['english_translation'] ?? '—', 0, 80, '…')) ?></strong></div>

            <?php elseif ($group['category'] === 'plants'): ?>
                <div class="dup-field">Name: <strong><?= e($row['tiv_name']) ?></strong></div>
                <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                <div class="dup-field">Medicinal: <strong><?= e(mb_strimwidth($row['medicinal_uses'] ?? '—', 0, 60, '…')) ?></strong></div>

            <?php elseif ($group['category'] === 'festivals'): ?>
                <div class="dup-field">Name: <strong><?= e($row['tiv_name']) ?></strong></div>
                <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                <div class="dup-field">Timing: <strong><?= e($row['timing'] ?? '—') ?></strong></div>

            <?php elseif ($group['category'] === 'foods'): ?>
                <div class="dup-field">Name: <strong><?= e($row['tiv_name']) ?></strong></div>
                <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                <div class="dup-field">Ingredients: <strong><?= e(mb_strimwidth($row['ingredients'] ?? '—', 0, 60, '…')) ?></strong></div>
            <?php endif; ?>

            <div class="dup-field">Added: <strong><?= e($row['created_at'] ?? '—') ?></strong></div>

            <form class="dup-delete-form" method="POST" action="<?= url('admin/duplicates/delete') ?>"
                  onsubmit="return confirm('Delete entry #<?= $row['id'] ?> (<?= e(addslashes($group['term'])) ?>)? This cannot be undone.')">
                <?= csrf_field() ?>
                <input type="hidden" name="category" value="<?= e($group['category']) ?>">
                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                <button type="submit" class="btn-danger-sm">&#128465; Delete this copy</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
