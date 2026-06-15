<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <a href="<?= url('archive/proverbs') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            All Proverbs
        </a>
        <span class="detail-banner-cat">&#128221; Tiv Proverb</span>
        <h1 class="detail-banner-title" style="font-size: clamp(1.1rem, 3vw, 1.75rem); font-style: italic; line-height: 1.3;">
            "<?= e($item['tiv_text']) ?>"
        </h1>
        <?php if (!empty($item['category'])): ?>
        <div class="detail-banner-badges">
            <span class="detail-banner-badge"><?= e($item['category']) ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <div class="detail-quote-block">
            "<?= e($item['english_translation']) ?>"
        </div>

        <?php if (!empty($item['deeper_meaning'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Deeper Meaning</span>
            <p class="detail-section-text"><?= nl2br(e($item['deeper_meaning'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['usage_context'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">When to Use</span>
            <p class="detail-section-text"><?= nl2br(e($item['usage_context'])) ?></p>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">More Proverbs</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url('proverb/' . $relItem['id']) ?>" class="archive-card-modern archive-card-proverbs">
                    <span class="archive-card-modern-icon">&#128221;</span>
                    <p class="archive-card-modern-title" style="font-style: italic; font-size: 0.9rem;">"<?= e(mb_substr($relItem['tiv_text'], 0, 70)) ?>…"</p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/proverbs') ?>" class="btn btn-secondary">&larr; All Proverbs</a>
        </div>
    </div>
</div>
