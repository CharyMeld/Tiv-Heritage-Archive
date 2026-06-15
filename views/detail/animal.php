<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <a href="<?= url('archive/animals') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            All Animals
        </a>
        <span class="detail-banner-cat">&#128062; Tiv Animal</span>
        <h1 class="detail-banner-title"><?= e($item['tiv_name']) ?></h1>
        <?php if (!empty($item['name'])): ?>
        <p class="detail-banner-sub"><?= e($item['name']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['animal_type'])): ?>
        <span style="display: inline-block; margin-top: 0.5rem; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
                     background: <?= $item['animal_type'] === 'wild' ? '#d97706' : ($item['animal_type'] === 'domestic' ? '#16a34a' : '#7c3aed') ?>;
                     color: #fff;">
            <?= e($item['animal_type']) ?>
        </span>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['image'])): ?>
        <div class="detail-section-modern" style="padding: 0; overflow: hidden; border-radius: 12px; margin-bottom: 1.5rem;">
            <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>"
                 alt="<?= e($item['tiv_name']) ?>"
                 style="width: 100%; max-height: 420px; object-fit: cover; display: block;">
        </div>
        <?php endif; ?>

        <?php if (!empty($item['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">About This Animal</span>
            <p class="detail-section-text"><?= nl2br(e($item['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['cultural_use'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Cultural Use</span>
            <p class="detail-section-text"><?= nl2br(e($item['cultural_use'])) ?></p>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Other Animals</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url('animal/' . $relItem['id']) ?>" class="archive-card-modern archive-card-animals" style="position: relative; overflow: hidden;">
                    <?php if (!empty($relItem['image'])): ?>
                    <div style="width: 100%; aspect-ratio: 16/9; overflow: hidden; border-radius: 6px; margin-bottom: 0.6rem;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $relItem['image']) ?>"
                             alt="<?= e($relItem['tiv_name']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php else: ?>
                    <span class="archive-card-modern-icon">&#128062;</span>
                    <?php endif; ?>
                    <?php if (!empty($relItem['animal_type'])): ?>
                    <span style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase;
                                 background: <?= $relItem['animal_type'] === 'wild' ? '#d97706' : ($relItem['animal_type'] === 'domestic' ? '#16a34a' : '#7c3aed') ?>;
                                 color: #fff;">
                        <?= e($relItem['animal_type']) ?>
                    </span>
                    <?php endif; ?>
                    <h4 class="archive-card-modern-title"><?= e($relItem['tiv_name']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['name'] ?? '') ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/animals') ?>" class="btn btn-secondary">&larr; All Animals</a>
        </div>
    </div>
</div>
