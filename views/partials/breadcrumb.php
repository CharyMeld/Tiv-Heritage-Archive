<?php
/**
 * Partial: Breadcrumb
 * Variables expected: $breadcrumb (array of ['label','url']; terminal entry has url=null)
 */
?>
<nav class="breadcrumb-nav" aria-label="Breadcrumb">
    <?php foreach ($breadcrumb as $i => $crumb): ?>
        <?php if ($i > 0): ?><span class="breadcrumb-sep">&rsaquo;</span><?php endif; ?>
        <?php if (!empty($crumb['url'])): ?>
        <a href="<?= e($crumb['url']) ?>" class="breadcrumb-link"><?= e($crumb['label']) ?></a>
        <?php else: ?>
        <span class="breadcrumb-current"><?= e($crumb['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
