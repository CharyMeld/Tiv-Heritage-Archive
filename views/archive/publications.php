<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128218; Archive</span>
        <h1 class="page-banner-title">Research Publications</h1>
        <p class="page-banner-sub">Academic and community research publications about Tiv language, culture, and history</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/publications') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search publications…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="padding: 1rem 0 3rem;">
    <div class="container">

        <?php if (!empty($search)): ?>
        <p style="margin-bottom:1rem;font-size:.88rem;color:#7a6a5a;">
            Results for "<strong><?= e($search) ?></strong>"
            &mdash; <a href="<?= url('archive/publications') ?>" style="color:#5C3A21;">Clear</a>
        </p>
        <?php endif; ?>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128218;</div>
            <?php if (!empty($search)): ?>
            <h3>No publications found for "<?= e($search) ?>"</h3>
            <p>Try different keywords or browse all publications.</p>
            <a href="<?= url('archive/publications') ?>" class="btn btn-primary">View All Publications</a>
            <?php else: ?>
            <h3>No publications yet</h3>
            <p>Academic and community research about Tiv language, culture, and history will appear here.<br>Be the first to contribute.</p>
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute a Publication</a>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url('content-item/' . $item['id']) ?>" class="archive-card-modern">

                <?php if ($item['media_type'] === 'image' && !empty($item['media_file'])): ?>
                <div class="archive-card-modern-img">
                    <img src="<?= e(UPLOADS_URL . '/' . $item['media_file']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="archive-card-modern-icon">&#128218;</div>
                <?php endif; ?>

                <?php if (!empty($item['is_featured'])): ?>
                <span style="position:absolute;top:.6rem;right:.6rem;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;background:#C8A951;color:#fff;">Featured</span>
                <?php elseif ($item['media_type'] === 'document' && !empty($item['media_file'])): ?>
                <span style="position:absolute;top:.6rem;right:.6rem;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;background:#5C3A21;color:#fff;">PDF</span>
                <?php endif; ?>

                <h3 class="archive-card-modern-title"><?= e($item['title']) ?></h3>
                <?php if (!empty($item['tiv_title'])): ?>
                <p class="archive-card-modern-sub" style="font-style:italic;"><?= e($item['tiv_title']) ?></p>
                <?php elseif (!empty($item['excerpt'])): ?>
                <p class="archive-card-modern-sub"><?= e(mb_substr($item['excerpt'], 0, 90)) ?><?= mb_strlen($item['excerpt']) > 90 ? '…' : '' ?></p>
                <?php endif; ?>
                <span class="archive-card-modern-meta"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <?= pagination($pagination, url('archive/publications') . (!empty($search) ? '?q=' . urlencode($search) : '')) ?>
        <?php endif; ?>

        <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid #e5e0d5;display:flex;gap:.8rem;flex-wrap:wrap;">
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute a Publication</a>
            <a href="<?= url('archive') ?>" class="btn btn-secondary">&larr; Back to Archive</a>
        </div>
    </div>
</div>
