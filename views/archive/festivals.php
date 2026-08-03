<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#127881; Archive</span>
        <h1 class="page-banner-title">Tiv Festivals</h1>
        <p class="page-banner-sub">Cultural celebrations and traditions of the Tiv people</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/festivals') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search festivals…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#127881;</div>
            <h3>No festivals found</h3>
            <p>Try a different search or browse all festivals.</p>
            <a href="<?= url('archive/festivals') ?>" class="btn btn-primary">View All Festivals</a>
        </div>
        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('festival', $item['id'], $item['tiv_name'])) ?>" class="archive-card-modern">
                <?php $_fimg = !empty($item['gallery_image']) ? $item['gallery_image'] : ($item['image'] ?? ''); ?>
                <?php if (!empty($_fimg)): ?>
                <div class="archive-card-modern-img">
                    <img src="<?= e(UPLOADS_URL . '/images/' . $_fimg) ?>" alt="<?= e($item['tiv_name']) ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="archive-card-modern-icon">&#127881;</div>
                <?php endif; ?>
                <h3 class="archive-card-modern-title"><?= e($item['tiv_name']) ?></h3>
                <p class="archive-card-modern-sub"><?= e($item['english_name'] ?? '') ?></p>
                <?php if (!empty($item['timing'])): ?>
                <span class="archive-card-modern-meta"><?= e($item['timing']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>
        <?= pagination($pagination, url('archive/festivals')) ?>
        <?php endif; ?>
    </div>
</div>
