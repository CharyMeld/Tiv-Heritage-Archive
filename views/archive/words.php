<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128172; Archive</span>
        <h1 class="page-banner-title">Tiv Dictionary</h1>
        <p class="page-banner-sub">Words, meanings and usage in the Tiv language</p>
        <p style="margin: .75rem 0 0;"><a href="<?= url('collections') ?>" class="explore-pill" style="display:inline-flex;align-items:center;gap:.4rem;">&#128214; Read the dictionary in full, letter by letter</a></p>
        <div class="page-banner-search">
            <form action="<?= url('archive/words') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" class="explore-search-input" placeholder="Search words…" value="<?= e($search ?? '') ?>" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
        <div class="page-banner-pills">
            <a href="<?= url('archive/words') ?>" class="explore-pill <?= empty($filter) ? 'explore-pill-active' : '' ?>">All</a>
            <?php foreach (['noun','verb','adjective','adverb','pronoun','interjection'] as $pos): ?>
            <a href="<?= url('archive/words?filter='.$pos) ?>" class="explore-pill <?= $filter===$pos ? 'explore-pill-active' : '' ?>"><?= ucfirst($pos) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128172;</div>
            <h3>No words found</h3>
            <p>Try a different search or browse all words.</p>
            <a href="<?= url('archive/words') ?>" class="btn btn-primary">View All Words</a>
        </div>
        <?php else: ?>
        <div>
            <?php foreach ($items as $item): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('word', $item['id'], $item['tiv_word'])) ?>" class="archive-item-modern">
                <div class="archive-item-modern-icon" style="background:rgba(26,82,118,0.12);">&#128172;</div>
                <div class="archive-item-modern-body">
                    <div class="archive-item-modern-title">
                        <?= e(SeoHelper::displayWord($item['tiv_word'])) ?>
                        <?php if (!empty($item['ipa'])): ?><span style="font-family:monospace;font-weight:400;font-size:.8em;color:var(--color-text-muted);"><?= e($item['ipa']) ?></span><?php endif; ?>
                    </div>
                    <div class="archive-item-modern-sub"><?= e($item['english_meaning']) ?></div>
                </div>
                <?php if (!empty($item['part_of_speech'])): ?>
                <span class="archive-item-modern-badge"><?= e($item['part_of_speech']) ?></span>
                <?php endif; ?>
                <svg class="archive-item-modern-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (ads_on()): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="<?= ADSENSE_CLIENT ?>" data-ad-slot="<?= ADSENSE_SLOT ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>
        <?= pagination($pagination, url('archive/words').($filter ? '?filter='.$filter : '')) ?>
        <?php endif; ?>
    </div>
</div>
