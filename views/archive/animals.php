<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128062; Archive</span>
        <h1 class="page-banner-title">Tiv Animals</h1>
        <p class="page-banner-sub">Animals in Tiv culture, their names and significance</p>
        <div class="page-banner-search">
            <form action="<?= url('archive/animals') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search animals…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">

        <!-- Type filter -->
        <div style="margin-bottom: 1.25rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <?php
            $typeFilter = $_GET['type'] ?? '';
            $filterBase = url('archive/animals') . (!empty($search) ? '?q=' . urlencode($search) . '&' : '?');
            $types = ['' => 'All', 'wild' => 'Wild', 'domestic' => 'Domestic', 'pet' => 'Pet'];
            foreach ($types as $val => $label):
                $active = $typeFilter === $val;
            ?>
            <a href="<?= $filterBase ?>type=<?= urlencode($val) ?>"
               style="padding: 0.35rem 0.9rem; border-radius: 999px; font-size: 0.85rem; font-weight: 500; text-decoration: none;
                      background: <?= $active ? 'var(--color-primary)' : 'var(--color-border-light)' ?>;
                      color: <?= $active ? '#fff' : 'var(--color-text)' ?>;">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128062;</div>
            <h3>No animals found</h3>
            <p>Try a different search or browse all animals.</p>
            <a href="<?= url('archive/animals') ?>" class="btn btn-primary">View All Animals</a>
        </div>
        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($items as $item): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('animal', $item['id'], $item['tiv_name'] ?? $item['name'])) ?>" class="archive-card-modern">
                <?php if (!empty($item['image'])): ?>
                <div class="archive-card-modern-img">
                    <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="<?= e($item['tiv_name']) ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="archive-card-modern-icon">&#128062;</div>
                <?php endif; ?>
                <?php if (!empty($item['animal_type'])): ?>
                <span style="position: absolute; top: 0.6rem; right: 0.6rem; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;
                             background: <?= $item['animal_type'] === 'wild' ? '#d97706' : ($item['animal_type'] === 'domestic' ? '#16a34a' : '#7c3aed') ?>;
                             color: #fff;">
                    <?= e($item['animal_type']) ?>
                </span>
                <?php endif; ?>
                <h3 class="archive-card-modern-title"><?= e($item['tiv_name']) ?></h3>
                <p class="archive-card-modern-sub"><?= e(mb_substr($item['description']??'',0,80)) ?><?= mb_strlen($item['description']??'')>80?'…':'' ?></p>
                <?php if (!empty($item['name'])): ?>
                <span class="archive-card-modern-meta"><?= e($item['name']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>
        <?= pagination($pagination, url('archive/animals')) ?>
        <?php endif; ?>
    </div>
</div>
