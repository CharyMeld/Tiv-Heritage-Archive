<?php
/**
 * Partial: Vertical timeline list (grouped by era) + pagination
 * Variables expected: $items, $pagination, $baseUrl
 */
$confidenceClass = function (int $score): string {
    if ($score >= 75) return 'is-high';
    if ($score >= 50) return 'is-medium';
    return 'is-low';
};
?>
<?php if (empty($items)): ?>
<div class="empty-state">
    <div class="empty-icon">&#128197;</div>
    <h3>No timeline events found.</h3>
    <p>Try a different search or clear your filters.</p>
</div>
<?php else: ?>
<div class="tl-track">
    <?php $lastEra = null; ?>
    <?php foreach ($items as $item): ?>
        <?php if ($item['era'] !== $lastEra): $lastEra = $item['era']; ?>
        <div class="tl-era-heading"><?= e($item['era']) ?></div>
        <?php endif; ?>

        <div class="tl-node-row">
            <div class="tl-node"></div>
            <a href="<?= url(SeoHelper::canonicalSlugPath('timeline-event', $item['id'], $item['title'])) ?>" class="tl-card">
                <?php if (!empty($item['image'])): ?>
                <div class="tl-card-img">
                    <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="tl-card-body">
                    <div class="tl-card-meta">
                        <span class="tl-card-date"><?= e($item['event_date'] ?? $item['year'] ?? '') ?><?= !empty($item['is_estimated']) ? ' (est.)' : '' ?></span>
                        <span class="tl-confidence-badge <?= $confidenceClass((int) $item['confidence_score']) ?>" title="Sourcing confidence">
                            <?= (int) $item['confidence_score'] ?>%
                        </span>
                    </div>
                    <h3 class="tl-card-title"><?= e($item['title']) ?></h3>
                    <?php if (!empty($item['short_summary'])): ?>
                    <p class="tl-card-excerpt"><?= e(mb_strimwidth($item['short_summary'], 0, 160, '…')) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($item['categories'])): ?>
                    <div class="tl-card-tags">
                        <?php foreach ($item['categories'] as $cat): ?>
                        <span class="tl-tag"><?= e($cat) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php if (ADSENSE_ENABLED): ?>
<div class="adsense-wrap">
    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
</div>
<?php endif; ?>
<?= pagination($pagination, $baseUrl) ?>
<?php endif; ?>
