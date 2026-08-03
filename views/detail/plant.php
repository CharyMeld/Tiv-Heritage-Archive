<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark">
            <?php $this->partial('breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
        </div>
        <a href="<?= url('archive/plants') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            All Plants
        </a>
        <span class="detail-banner-cat">&#127807; Tiv Plant</span>
        <h1 class="detail-banner-title"><?= e($item['tiv_name']) ?></h1>
        <?php if (!empty($item['english_name'])): ?>
        <p class="detail-banner-sub"><?= e($item['english_name']) ?></p>
        <?php endif; ?>
        <div class="detail-banner-badges">
            <?php if (!empty($item['scientific_name'])): ?>
            <span class="detail-banner-badge" style="font-style: italic;"><?= e($item['scientific_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['is_medicinal'])): ?><span class="detail-banner-badge">Medicinal</span><?php endif; ?>
            <?php if (!empty($item['is_edible'])): ?><span class="detail-banner-badge">Edible</span><?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['image'])): ?>
        <div style="margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden; max-height: 320px;">
            <?= SeoHelper::heroPicture($item['image'], $item['tiv_name'], 'width: 100%; height: 320px; object-fit: cover;', 'fetchpriority="high"') ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Description</span>
            <p class="detail-section-text"><?= nl2br(e($item['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['medicinal_uses'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Medicinal Uses</span>
            <p class="detail-section-text"><?= nl2br(e($item['medicinal_uses'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['food_uses'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Food Uses</span>
            <p class="detail-section-text"><?= nl2br(e($item['food_uses'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['ritual_uses'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Ritual / Cultural Uses</span>
            <p class="detail-section-text"><?= nl2br(e($item['ritual_uses'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['cultivation'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Cultivation</span>
            <p class="detail-section-text"><?= nl2br(e($item['cultivation'])) ?></p>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Other Plants</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url(SeoHelper::canonicalSlugPath('plant', $relItem['id'], $relItem['tiv_name'])) ?>" class="archive-card-modern archive-card-plants">
                    <?php if (!empty($relItem['image'])): ?>
                    <div style="height: 80px; border-radius: 8px; overflow: hidden; margin-bottom: 0.5rem;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $relItem['image']) ?>" alt="<?= e($relItem['tiv_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php else: ?>
                    <span class="archive-card-modern-icon">&#127807;</span>
                    <?php endif; ?>
                    <h4 class="archive-card-modern-title"><?= e($relItem['tiv_name']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['english_name'] ?? '') ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/plants') ?>" class="btn btn-secondary">&larr; All Plants</a>
        </div>
    </div>
</div>
