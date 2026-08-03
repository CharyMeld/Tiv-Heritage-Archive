<?php
/**
 * Partial: Historical Figures card grid + pagination
 * Variables expected: $items, $pagination, $baseUrl, $emptyMessage (optional)
 */
$emptyMessage = $emptyMessage ?? 'No historical figures found.';
?>
<?php if (empty($items)): ?>
<div class="empty-state">
    <div class="empty-icon">&#129332;</div>
    <h3><?= e($emptyMessage) ?></h3>
    <p>Try a different search or clear your filters.</p>
</div>
<?php else: ?>
<div class="archive-grid-modern">
    <?php foreach ($items as $item): ?>
    <a href="<?= url(SeoHelper::canonicalSlugPath('historical-figure', $item['id'], $item['english_name'])) ?>" class="archive-card-modern hf-figure-card">
        <?php if (!empty($item['image'])): ?>
        <div class="archive-card-modern-icon" style="padding:0;overflow:hidden;border-radius:.5rem;">
            <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="<?= e($item['english_name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>
        <?php else: ?>
        <div class="archive-card-modern-icon">&#129332;</div>
        <?php endif; ?>
        <h3 class="archive-card-modern-title"><?= e($item['english_name']) ?></h3>
        <?php $ordinal = HistoricalFigure::ordinalLabel($item); ?>
        <?php if ($ordinal): ?>
        <p class="archive-card-modern-sub" style="font-weight:700;color:var(--color-accent,#c8832a);"><?= e($ordinal) ?></p>
        <?php elseif (!empty($item['title'])): ?>
        <p class="archive-card-modern-sub"><?= e($item['title']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['birth_year']) || !empty($item['death_year'])): ?>
        <p class="hf-figure-card-years"><?= e(($item['birth_year'] ?? '?') . ' – ' . ($item['death_year'] ?? 'present')) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['short_summary'])): ?>
        <p class="hf-figure-card-excerpt"><?= e(mb_strimwidth($item['short_summary'], 0, 130, '…')) ?></p>
        <?php endif; ?>
        <span class="archive-card-modern-meta"><?= e($item['category']) ?><?= !empty($item['subcategory']) ? ' · ' . e($item['subcategory']) : '' ?></span>
        <span class="hf-figure-card-cta">Read Biography &rarr;</span>
    </a>
    <?php endforeach; ?>
</div>
<?php if (ADSENSE_ENABLED): ?>
<div class="adsense-wrap">
    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
</div>
<?php endif; ?>
<?= pagination($pagination, $baseUrl) ?>
<?php endif; ?>
