<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#127760; Knowledge Graph Links</h1>
        <p class="admin-page-sub"><?= number_format(count($links)) ?> connections between cultural items</p>
    </div>
    <a href="<?= url('admin/links/create') ?>" class="btn btn-primary">+ Add Link</a>
</div>

<?php if (empty($links)): ?>
<div style="text-align:center; padding:5rem 1rem;">
    <p style="font-size:3rem; margin-bottom:1rem;">&#127760;</p>
    <h3 style="font-family:var(--font-heading); color:var(--color-primary); margin-bottom:0.5rem;">No links yet</h3>
    <p style="color:var(--color-text-muted); margin-bottom:2rem;">
        Connect cultural items together — e.g. a plant used in a festival, a proverb mentioning a food.
    </p>
    <a href="<?= url('admin/links/create') ?>" class="btn btn-primary">Create First Link</a>
</div>
<?php else: ?>

<!-- Quick-tip -->
<div class="detail-section-modern" style="margin:0 0 1.5rem; background:rgba(200,169,81,0.06); border-color:rgba(200,169,81,0.3);">
    <span class="detail-section-label">&#128161; How links work</span>
    <p class="detail-section-text" style="margin:0;">
        Each link connects two content items with a relationship label (e.g. "ingredient_for", "used_in_festival", "mentioned_in_proverb").
        These connections appear as <strong>Related Cultural Knowledge</strong> on every detail page.
    </p>
</div>

<div class="admin-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Source Item</th>
                <th>Relation</th>
                <th>Target Item</th>
                <th>Added</th>
                <th style="width:80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($links as $link):
                $srcCfg = $tableConfig[$link['source_table']] ?? null;
                $tgtCfg = $tableConfig[$link['target_table']] ?? null;
            ?>
            <tr>
                <td>
                    <?php if ($srcCfg): ?>
                    <span class="ref-meta-chip"><?= $srcCfg['icon'] ?> <?= e($srcCfg['label']) ?></span><br>
                    <span style="font-size:0.85rem;"><?= e($link['source_label'] ?? "#{$link['source_id']}") ?></span>
                    <?php else: ?>
                    <?= e($link['source_table']) ?> #<?= $link['source_id'] ?>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="font-family:var(--font-ui); font-size:0.78rem; font-weight:700; color:var(--color-accent); background:rgba(200,169,81,0.1); padding:0.2rem 0.6rem; border-radius:50px;">
                        <?= e(str_replace('_', ' ', $link['relation_type'])) ?>
                    </span>
                </td>
                <td>
                    <?php if ($tgtCfg): ?>
                    <span class="ref-meta-chip"><?= $tgtCfg['icon'] ?> <?= e($tgtCfg['label']) ?></span><br>
                    <span style="font-size:0.85rem;"><?= e($link['target_label'] ?? "#{$link['target_id']}") ?></span>
                    <?php else: ?>
                    <?= e($link['target_table']) ?> #<?= $link['target_id'] ?>
                    <?php endif; ?>
                </td>
                <td style="font-size:0.8rem; color:var(--color-text-muted);">
                    <?= date('M j, Y', strtotime($link['created_at'])) ?>
                </td>
                <td>
                    <form action="<?= url('admin/links/' . $link['id'] . '/delete') ?>" method="POST"
                          onsubmit="return confirm('Remove this link?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm" style="background:#c62828; color:#fff; border:none;">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
