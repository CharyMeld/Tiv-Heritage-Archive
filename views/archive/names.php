<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128100; Archive</span>
        <h1 class="page-banner-title">Tiv Names</h1>
        <p class="page-banner-sub">Traditional names and their meanings &mdash; discover your heritage</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/names') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search names…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
        <div class="page-banner-pills">
            <a href="<?= url('archive/names') ?>" class="explore-pill <?= empty($filter) ? 'explore-pill-active' : '' ?>">All</a>
            <a href="<?= url('archive/names?filter=male') ?>" class="explore-pill <?= $filter==='male' ? 'explore-pill-active' : '' ?>">Male</a>
            <a href="<?= url('archive/names?filter=female') ?>" class="explore-pill <?= $filter==='female' ? 'explore-pill-active' : '' ?>">Female</a>
            <a href="<?= url('archive/names?filter=unisex') ?>" class="explore-pill <?= $filter==='unisex' ? 'explore-pill-active' : '' ?>">Unisex</a>
        </div>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128100;</div>
            <h3>No names found</h3>
            <p>Try a different search term or browse all names.</p>
            <a href="<?= url('archive/names') ?>" class="btn btn-primary">View All Names</a>
        </div>
        <?php else: ?>
        <div>
            <?php foreach ($items as $item): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('name', $item['id'], $item['tiv_name'])) ?>" class="archive-item-modern">
                <div class="archive-item-modern-icon" style="background:rgba(200,169,81,0.12);">&#128100;</div>
                <div class="archive-item-modern-body">
                    <div class="archive-item-modern-title"><?= e($item['tiv_name']) ?></div>
                    <div class="archive-item-modern-sub"><?= e($item['english_meaning']) ?></div>
                </div>
                <span class="archive-item-modern-badge"><?= ucfirst(e($item['gender'])) ?></span>
                <svg class="archive-item-modern-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="load-more" style="margin-top:1rem;">
            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <a href="<?= url('archive/names') ?>?page=<?= $pagination['current_page']+1 ?><?= $filter ? '&filter='.$filter : '' ?>" class="load-more-btn">Load More</a>
            <?php endif; ?>
            <p class="pagination-info mt-2">Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?></p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
