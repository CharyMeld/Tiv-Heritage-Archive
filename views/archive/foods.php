<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#127858; Archive</span>
        <h1 class="page-banner-title">Tiv Foods</h1>
        <p class="page-banner-sub">Traditional cuisine, recipes and culinary heritage</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/foods') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search foods…" value="<?= e($search ?? '') ?>" autocomplete="off">
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
            <div class="empty-icon">&#127858;</div>
            <h3>No foods found</h3>
            <p>Try a different search or browse all foods.</p>
            <a href="<?= url('archive/foods') ?>" class="btn btn-primary">View All Foods</a>
        </div>
        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url('food/' . $item['id']) ?>" class="archive-card-modern">
                <?php if (!empty($item['image'])): ?>
                <div class="archive-card-modern-img">
                    <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="<?= e($item['tiv_name']) ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="archive-card-modern-icon">&#127858;</div>
                <?php endif; ?>
                <h3 class="archive-card-modern-title"><?= e($item['tiv_name']) ?></h3>
                <p class="archive-card-modern-sub"><?= e(mb_substr($item['description']??'',0,80)) ?><?= mb_strlen($item['description']??'')>80?'…':'' ?></p>
                <?php if (!empty($item['english_name'])): ?>
                <span class="archive-card-modern-meta"><?= e($item['english_name']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?= pagination($pagination, url('archive/foods')) ?>
        <?php endif; ?>
    </div>
</div>
