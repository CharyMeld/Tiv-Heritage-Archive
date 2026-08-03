<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#127911; Archive</span>
        <h1 class="page-banner-title">Audio Recordings</h1>
        <p class="page-banner-sub">Recordings of Tiv songs, speeches, oral traditions, and language samples</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/audio') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search recordings…" value="<?= e($search ?? '') ?>" autocomplete="off">
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
            &mdash; <a href="<?= url('archive/audio') ?>" style="color:#5C3A21;">Clear</a>
        </p>
        <?php endif; ?>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#127911;</div>
            <?php if (!empty($search)): ?>
            <h3>No recordings found for "<?= e($search) ?>"</h3>
            <p>Try different keywords or browse all recordings.</p>
            <a href="<?= url('archive/audio') ?>" class="btn btn-primary">View All Recordings</a>
            <?php else: ?>
            <h3>No audio recordings yet</h3>
            <p>Songs, speeches, oral traditions, and language samples from the Tiv community will appear here.<br>Be the first to contribute.</p>
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute a Recording</a>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url('content-item/' . $item['id']) ?>" class="archive-card-modern">

                <div class="archive-card-modern-icon" style="background:linear-gradient(135deg,#5C3A21,#8B5E3C);color:#fff;display:flex;align-items:center;justify-content:center;">
                    &#127911;
                </div>

                <?php if (!empty($item['is_featured'])): ?>
                <span style="position:absolute;top:.6rem;right:.6rem;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;background:#C8A951;color:#fff;">Featured</span>
                <?php elseif (!empty($item['media_file'])): ?>
                <span style="position:absolute;top:.6rem;right:.6rem;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;background:#5C3A21;color:#fff;">Audio</span>
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

        <?= pagination($pagination, url('archive/audio') . (!empty($search) ? '?q=' . urlencode($search) : '')) ?>
        <?php endif; ?>

        <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid #e5e0d5;display:flex;gap:.8rem;flex-wrap:wrap;">
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute a Recording</a>
            <a href="<?= url('archive') ?>" class="btn btn-secondary">&larr; Back to Archive</a>
        </div>
    </div>
</div>
