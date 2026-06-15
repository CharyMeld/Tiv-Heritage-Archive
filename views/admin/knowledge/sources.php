<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128218; Sources &amp; Contributors</h1>
        <p class="admin-page-sub"><?= number_format($total) ?> total sources powering the References page</p>
    </div>
    <a href="<?= url('admin/sources/create') ?>" class="btn btn-primary">+ Add Source</a>
</div>

<?php foreach ($typeLabels as $typeKey => $typeLabel):
    if (empty($grouped[$typeKey])) continue;
    $sources = $grouped[$typeKey];
    $icon    = $typeIcons[$typeKey];
?>
<div style="margin-bottom: 2rem;">
    <div class="admin-table-header">
        <h3 style="margin:0; font-size:0.95rem; color:var(--color-primary); display:flex; align-items:center; gap:0.5rem;">
            <?= $icon ?> <?= e($typeLabel) ?>
            <span class="ref-section-count"><?= count($sources) ?></span>
        </h3>
    </div>
    <div class="admin-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contributor / Author</th>
                    <th>Title / Work</th>
                    <th>Location</th>
                    <th>Year</th>
                    <th>Notes</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sources as $s): ?>
                <tr>
                    <td><strong><?= e($s['contributor_name'] ?? $s['author'] ?? '—') ?></strong></td>
                    <td style="font-style:italic; color:var(--color-text-muted);"><?= e($s['title'] ?? '—') ?></td>
                    <td style="font-size:0.85rem;"><?= e($s['location'] ?? '—') ?></td>
                    <td style="font-size:0.85rem;"><?= e($s['year_recorded'] ?? '—') ?></td>
                    <td style="font-size:0.8rem; color:var(--color-text-muted); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= e($s['notes'] ?? '') ?>">
                        <?= e(mb_substr($s['notes'] ?? '', 0, 60)) ?><?= mb_strlen($s['notes'] ?? '') > 60 ? '…' : '' ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:0.4rem;">
                            <a href="<?= url('admin/sources/' . $s['id'] . '/edit') ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <form action="<?= url('admin/sources/' . $s['id'] . '/delete') ?>" method="POST"
                                  onsubmit="return confirm('Delete this source?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm" style="background:#c62828; color:#fff; border:none;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php if ($total === 0): ?>
<div style="text-align:center; padding:4rem;">
    <p style="font-size:2.5rem;">&#128218;</p>
    <p style="color:var(--color-text-muted);">No sources yet. Add your first source above.</p>
</div>
<?php endif; ?>
