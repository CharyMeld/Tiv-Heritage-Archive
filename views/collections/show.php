<?php
/**
 * Full-text collection page (CollectionController::respond).
 * Variables: $heading, $eyebrow, $intro, $entries, $pageNo, $pages, $prevUrl, $nextUrl,
 *            $pageUrl (callable), $nav, $navLabel, $breadcrumb
 */
?>
<?php $this->partial('collection-styles'); ?>

<div class="page-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark"><?php $this->partial('breadcrumb', ['breadcrumb' => $breadcrumb]); ?></div>
        <span class="page-banner-eyebrow"><?= e($eyebrow) ?></span>
        <h1 class="page-banner-title"><?= e($heading) ?></h1>
        <p class="page-banner-sub"><?= count($entries) ?> entries<?= $pages > 1 ? ' · part ' . $pageNo . ' of ' . $pages : '' ?></p>
        <div class="page-banner-search">
            <form action="<?= e($searchUrl) ?>" method="GET" role="search">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" id="collectionSearch" class="explore-search-input" placeholder="<?= e($searchLabel) ?>" aria-label="<?= e($searchLabel) ?>" autocomplete="off" required>
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="col-wrap">
    <div class="container col-container">

        <p class="col-intro"><?= e($intro) ?></p>

        <?php if (!empty($nav)): ?>
        <nav class="col-nav" aria-label="<?= e($navLabel) ?>">
            <?php foreach ($nav as $n): ?>
            <a href="<?= e($n['url']) ?>" class="col-nav-link<?= $n['active'] ? ' is-active' : '' ?>"<?= $n['active'] ? ' aria-current="page"' : '' ?>><?= e($n['label']) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
        <nav class="col-pages" aria-label="Parts of this collection">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="<?= e($pageUrl($i)) ?>" class="col-nav-link<?= $i === $pageNo ? ' is-active' : '' ?>"<?= $i === $pageNo ? ' aria-current="page"' : '' ?>>Part <?= $i ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>

        <div class="col-entries">
            <?php foreach ($entries as $i => $entry): ?>
            <article class="col-entry" id="<?= e($entry['anchor']) ?>">
                <h2 class="col-entry-title<?= $entry['italic'] ? ' is-italic' : '' ?>">
                    <a href="<?= e($entry['url']) ?>"><?= e($entry['title']) ?></a>
                </h2>
                <?php if ($entry['subtitle'] !== ''): ?>
                <p class="col-entry-sub"><?= e($entry['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($entry['chips'])): ?>
                <p class="col-entry-chips">
                    <?php foreach ($entry['chips'] as $chip): ?><span><?= e($chip) ?></span><?php endforeach; ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($entry['fields'])): ?>
                <dl class="col-entry-fields">
                    <?php foreach ($entry['fields'] as $label => $text): ?>
                    <dt><?= e($label) ?></dt>
                    <dd><?= nl2br(e($text)) ?></dd>
                    <?php endforeach; ?>
                </dl>
                <?php endif; ?>
            </article>
            <?php if (ads_on() && $i === 9 && count($entries) > 20): ?>
            <div class="adsense-wrap">
                <ins class="adsbygoogle" style="display:block" data-ad-client="<?= ADSENSE_CLIENT ?>" data-ad-slot="<?= ADSENSE_SLOT ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($prevUrl || $nextUrl): ?>
        <nav class="col-prevnext" aria-label="Previous and next part">
            <?php if ($prevUrl): ?><a href="<?= e($prevUrl) ?>" class="btn btn-secondary">&larr; Part <?= $pageNo - 1 ?></a><?php else: ?><span></span><?php endif; ?>
            <?php if ($nextUrl): ?><a href="<?= e($nextUrl) ?>" class="btn btn-primary">Part <?= $pageNo + 1 ?> &rarr;</a><?php endif; ?>
        </nav>
        <?php endif; ?>

        <p class="col-back"><a href="<?= url('collections') ?>">&larr; All collections</a></p>
    </div>
</div>
