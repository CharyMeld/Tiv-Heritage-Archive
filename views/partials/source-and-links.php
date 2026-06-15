<?php
/**
 * Partial: Source Reference + Related Cultural Knowledge
 * Variables expected: $source (array|null), $links (array)
 */
?>

<?php if (!empty($source)): ?>
<!-- Source Reference -->
<div class="detail-section-modern kg-source-block">
    <span class="detail-section-label">&#128218; Source Reference</span>
    <div class="kg-source-inner">
        <div class="kg-source-avatar">
            <?= strtoupper(mb_substr($source['contributor_name'] ?? $source['author'] ?? 'S', 0, 1)) ?>
        </div>
        <div>
            <p class="kg-source-name">
                <?= e($source['contributor_name'] ?? $source['author'] ?? 'Unknown') ?>
            </p>
            <?php if (!empty($source['title'])): ?>
            <p class="kg-source-work"><?= e($source['title']) ?></p>
            <?php endif; ?>
            <div class="kg-source-chips">
                <?php if (!empty($source['source_type'])): ?>
                <span class="ref-meta-chip"><?= e(ucwords(str_replace('_', ' ', $source['source_type']))) ?></span>
                <?php endif; ?>
                <?php if (!empty($source['location'])): ?>
                <span class="ref-meta-chip">&#128205; <?= e($source['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($source['year_recorded'])): ?>
                <span class="ref-meta-chip">&#128197; <?= e($source['year_recorded']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <a href="<?= url('references') ?>" class="kg-source-link">View all references &rsaquo;</a>
</div>
<?php endif; ?>

<?php if (!empty($links)): ?>
<!-- Related Cultural Knowledge -->
<div style="margin-top: 2rem;">
    <div class="kg-links-header">
        <span class="kg-links-icon">&#127760;</span>
        <h3 class="kg-links-title">Related Cultural Knowledge</h3>
    </div>
    <div class="kg-links-grid">
        <?php foreach ($links as $link):
            $cfg  = $link['cfg'];
            $item = $link['item'];
            $titleField = $cfg['title'];
            $subField   = $cfg['sub'];
            $title = mb_substr($item[$titleField] ?? '', 0, 60);
            $sub   = mb_substr($item[$subField]   ?? '', 0, 60);
            $url   = url($cfg['url'] . '/' . $item['id']);
            $relation = ucwords(str_replace('_', ' ', $link['relation']));
        ?>
        <a href="<?= e($url) ?>" class="kg-link-card">
            <span class="kg-link-icon"><?= $cfg['icon'] ?></span>
            <div class="kg-link-body">
                <span class="kg-link-relation"><?= e($relation) ?></span>
                <h4 class="kg-link-title"><?= e($title) ?></h4>
                <?php if ($sub): ?>
                <p class="kg-link-sub"><?= e($sub) ?></p>
                <?php endif; ?>
            </div>
            <span class="kg-link-arrow">&rsaquo;</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
