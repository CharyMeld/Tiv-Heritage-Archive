<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#127807; Archive</span>
        <h1 class="page-banner-title">Tiv Plants</h1>
        <p class="page-banner-sub">Medicinal, sacred and food plants of the Tiv people</p>
        <p style="margin: .75rem 0 0;"><a href="<?= url('collections/plants') ?>" class="explore-pill" style="display:inline-flex;align-items:center;gap:.4rem;">&#128214; Read every plant entry in full</a></p>
        <div class="page-banner-search">
            <form action="<?= url('archive/plants') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search plants…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
        <div class="page-banner-pills">
            <a href="<?= url('archive/plants') ?>" class="explore-pill <?= empty($filter) ? 'explore-pill-active' : '' ?>">All</a>
            <a href="<?= url('archive/plants?filter=medicinal') ?>" class="explore-pill <?= $filter==='medicinal' ? 'explore-pill-active' : '' ?>">Medicinal</a>
            <a href="<?= url('archive/plants?filter=edible') ?>" class="explore-pill <?= $filter==='edible' ? 'explore-pill-active' : '' ?>">Edible</a>
            <a href="<?= url('archive/plants?filter=ritual') ?>" class="explore-pill <?= $filter==='ritual' ? 'explore-pill-active' : '' ?>">Ritual</a>
        </div>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#127807;</div>
            <h3>No plants found</h3>
            <p>Try a different search or browse all plants.</p>
            <a href="<?= url('archive/plants') ?>" class="btn btn-primary">View All Plants</a>
        </div>
        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('plant', $item['id'], $item['tiv_name'])) ?>" class="archive-card-modern">
                <?php if (!empty($item['image'])): ?>
                <div class="archive-card-modern-img">
                    <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="<?= e($item['tiv_name']) ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="archive-card-modern-icon">&#127807;</div>
                <?php endif; ?>
                <h3 class="archive-card-modern-title"><?= e($item['tiv_name']) ?></h3>
                <p class="archive-card-modern-sub"><?= e($item['english_name'] ?? '') ?><?php if (!empty($item['scientific_name'])): ?><br><em style="font-size:0.75rem;"><?= e($item['scientific_name']) ?></em><?php endif; ?></p>
                <span class="archive-card-modern-meta">&#127807; Plant</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (ads_on()): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="<?= ADSENSE_CLIENT ?>" data-ad-slot="<?= ADSENSE_SLOT ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>
        <?= pagination($pagination, url('archive/plants').($filter ? '?filter='.$filter : '')) ?>
        <?php endif; ?>
    </div>
</div>
