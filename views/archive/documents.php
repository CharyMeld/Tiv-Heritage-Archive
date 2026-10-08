<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128196; Archive</span>
        <h1 class="page-banner-title">Documents</h1>
        <p class="page-banner-sub">Historical manuscripts, written records, and official documents from the Tiv people</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/documents') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search documents…" value="<?= e($search ?? '') ?>" autocomplete="off">
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
            &mdash; <a href="<?= url('archive/documents') ?>" style="color:#5C3A21;">Clear</a>
        </p>
        <?php endif; ?>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128196;</div>
            <?php if (!empty($search)): ?>
            <h3>No documents found for "<?= e($search) ?>"</h3>
            <p>Try different keywords or browse all documents.</p>
            <a href="<?= url('archive/documents') ?>" class="btn btn-primary">View All Documents</a>
            <?php else: ?>
            <h3>No documents yet</h3>
            <p>Historical manuscripts, written records, and official documents will appear here.<br>Be the first to contribute.</p>
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute a Document</a>
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
                <div class="archive-card-modern-icon">
                    <?php if ($item['media_type'] === 'audio'): ?>&#127911;
                    <?php elseif ($item['media_type'] === 'document'): ?>&#128196;
                    <?php else: ?>&#128196;
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($item['is_featured'])): ?>
                <span style="position:absolute;top:.6rem;right:.6rem;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;background:#C8A951;color:#fff;">Featured</span>
                <?php elseif ($item['media_type'] !== 'none'): ?>
                <span style="position:absolute;top:.6rem;right:.6rem;padding:.2rem .6rem;border-radius:999px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;background:#5C3A21;color:#fff;"><?= e(ucfirst($item['media_type'])) ?></span>
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

        <?php if (ads_on()): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="<?= ADSENSE_CLIENT ?>" data-ad-slot="<?= ADSENSE_SLOT ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <?= pagination($pagination, url('archive/documents') . (!empty($search) ? '?q=' . urlencode($search) : '')) ?>
        <?php endif; ?>

        <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid #e5e0d5;display:flex;gap:.8rem;flex-wrap:wrap;">
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute a Document</a>
            <a href="<?= url('archive') ?>" class="btn btn-secondary">&larr; Back to Archive</a>
        </div>
    </div>
</div>
