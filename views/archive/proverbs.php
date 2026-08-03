<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128221; Archive</span>
        <h1 class="page-banner-title">Tiv Proverbs</h1>
        <p class="page-banner-sub">Ancient wisdom passed down through generations</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/proverbs') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search proverbs…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
        <?php if (!empty($categories)): ?>
        <div class="page-banner-pills">
            <a href="<?= url('archive/proverbs') ?>" class="explore-pill <?= empty($filter) ? 'explore-pill-active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= url('archive/proverbs?filter='.urlencode($cat)) ?>" class="explore-pill <?= $filter===$cat ? 'explore-pill-active' : '' ?>"><?= e($cat) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <p style="margin-top: 1rem;">
            <a href="<?= UPLOADS_URL ?>/downloads/tiv-proverbs.pdf" download class="explore-pill" style="display:inline-flex;align-items:center;gap:.4rem;">
                &#128196; Download all proverbs as PDF
            </a>
        </p>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128221;</div>
            <h3>No proverbs found</h3>
            <p>Try a different search or browse all proverbs.</p>
            <a href="<?= url('archive/proverbs') ?>" class="btn btn-primary">View All Proverbs</a>
        </div>
        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('proverb', $item['id'], $item['tiv_text'])) ?>" class="archive-card-modern">
                <div class="archive-card-modern-icon">&#128221;</div>
                <h3 class="archive-card-modern-title" style="font-style:italic;">&ldquo;<?= e(mb_substr($item['tiv_text'],0,55)) ?><?= mb_strlen($item['tiv_text'])>55?'…':'' ?>&rdquo;</h3>
                <p class="archive-card-modern-sub"><?= e(mb_substr($item['english_translation']??'',0,80)) ?></p>
                <?php if (!empty($item['category'])): ?>
                <span class="archive-card-modern-meta"><?= e($item['category']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>
        <?= pagination($pagination, url('archive/proverbs').($filter ? '?filter='.urlencode($filter) : '')) ?>
        <?php endif; ?>
    </div>
</div>
