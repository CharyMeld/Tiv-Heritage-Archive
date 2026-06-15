<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <a href="<?= url('archive/foods') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            All Foods
        </a>
        <span class="detail-banner-cat">&#127858; Tiv Food</span>
        <h1 class="detail-banner-title"><?= e($item['tiv_name']) ?></h1>
        <?php if (!empty($item['english_name'])): ?>
        <p class="detail-banner-sub"><?= e($item['english_name']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['image'])): ?>
        <div class="detail-section-modern" style="padding: 0; overflow: hidden; border-radius: .75rem;">
            <img
                src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>"
                alt="<?= e($item['tiv_name']) ?>"
                style="width:100%; max-height:420px; object-fit:cover; display:block; border-radius:.75rem;"
            >
        </div>
        <?php endif; ?>

        <?php if (!empty($item['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">About This Dish</span>
            <p class="detail-section-text"><?= nl2br(e($item['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['ingredients'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Ingredients</span>
            <p class="detail-section-text"><?= nl2br(e($item['ingredients'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['preparation_method'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Preparation Method</span>
            <p class="detail-section-text"><?= nl2br(e($item['preparation_method'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['serving_suggestions'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Serving Suggestions</span>
            <p class="detail-section-text"><?= nl2br(e($item['serving_suggestions'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['cultural_significance'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Cultural Significance</span>
            <p class="detail-section-text"><?= nl2br(e($item['cultural_significance'])) ?></p>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Other Foods</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url('food/' . $relItem['id']) ?>" class="archive-card-modern archive-card-foods">
                    <span class="archive-card-modern-icon">&#127858;</span>
                    <h4 class="archive-card-modern-title"><?= e($relItem['tiv_name']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['english_name'] ?? '') ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/foods') ?>" class="btn btn-secondary">&larr; All Foods</a>
        </div>
    </div>
</div>
