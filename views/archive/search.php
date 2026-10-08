<!-- Search Hero -->
<div class="explore-hero" style="padding-bottom: 2.5rem;">
    <div class="explore-hero-inner">
        <div class="container">
            <p class="explore-eyebrow">&#128269; Search Results</p>
            <h1 class="explore-heading" style="font-size: clamp(1.5rem, 4vw, 2.5rem);">
                <?php if ($totalResults > 0): ?>
                    <?= number_format($totalResults) ?> results for &ldquo;<?= e($search) ?>&rdquo;
                <?php else: ?>
                    No results for &ldquo;<?= e($search) ?>&rdquo;
                <?php endif; ?>
            </h1>

            <!-- Re-search form -->
            <form action="<?= url('archive') ?>" method="GET" class="explore-search-form" style="margin-top: 1.5rem;">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" name="q" class="explore-search-input"
                           placeholder="Search the archive…" value="<?= e($search) ?>"
                           autocomplete="off" autofocus>
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Results -->
<div class="explore-rows-wrap">
    <?php if ($totalResults === 0): ?>
    <div class="container" style="padding: 3rem 1rem; text-align: center;">
        <p style="font-size: 3rem; margin-bottom: 1rem;">&#128269;</p>
        <h3 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--color-primary); margin-bottom: 0.5rem;">Nothing found</h3>
        <p style="color: var(--color-text-muted); margin-bottom: 2rem;">Try a different search term or browse by category.</p>
        <a href="<?= url('archive') ?>" class="btn btn-primary">Browse Archive</a>
    </div>
    <?php else: ?>

    <?php
    $catConfig = [
        'names'     => ['icon' => '&#128100;', 'label' => 'Names',     'url_prefix' => 'name',     'title_field' => 'tiv_name',    'sub_field' => 'english_meaning'],
        'proverbs'  => ['icon' => '&#128221;', 'label' => 'Proverbs',  'url_prefix' => 'proverb',  'title_field' => 'tiv_text',    'sub_field' => 'english_translation'],
        'plants'    => ['icon' => '&#127807;', 'label' => 'Plants',    'url_prefix' => 'plant',    'title_field' => 'tiv_name',    'sub_field' => 'english_name'],
        'festivals' => ['icon' => '&#127881;', 'label' => 'Festivals', 'url_prefix' => 'festival', 'title_field' => 'tiv_name',    'sub_field' => 'english_name'],
        'foods'     => ['icon' => '&#127858;', 'label' => 'Foods',     'url_prefix' => 'food',     'title_field' => 'tiv_name',    'sub_field' => 'english_name'],
        'words'     => ['icon' => '&#128172;', 'label' => 'Dictionary','url_prefix' => 'word',     'title_field' => 'tiv_word',    'sub_field' => 'english_meaning'],
    ];
    foreach ($catConfig as $catKey => $cfg):
        if (empty($results[$catKey])) continue;
        $items = $results[$catKey];
    ?>
    <section class="explore-row" id="search-<?= $catKey ?>">
        <div class="explore-row-header">
            <div class="explore-row-title-group">
                <span class="explore-row-icon"><?= $cfg['icon'] ?></span>
                <h2 class="explore-row-title"><?= $cfg['label'] ?></h2>
                <span class="explore-row-badge"><?= count($items) ?></span>
            </div>
            <a href="<?= url('archive/' . $catKey . '?q=' . urlencode($search)) ?>" class="explore-row-seeall">
                View all <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        <div class="explore-row-track-wrap">
            <button class="explore-arrow explore-arrow-left" aria-label="Scroll left" onclick="slideRow(this,-1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="explore-track" data-cat="<?= $catKey ?>">
                <?php foreach ($items as $item):
                    $titleRaw = SeoHelper::displayWord($item[$cfg['title_field']] ?? '');
                    $title    = mb_strlen($titleRaw) > 50 ? mb_substr($titleRaw, 0, 48) . '…' : $titleRaw;
                    $sub      = mb_substr($item[$cfg['sub_field']] ?? '', 0, 60);
                    $url      = url($cfg['url_prefix'] . '/' . $item['id']);
                    if (!$title) continue;
                ?>
                <a href="<?= e($url) ?>" class="explore-card explore-card-<?= $catKey ?>">
                    <div class="explore-card-front">
                        <div class="explore-card-cat-icon"><?= $cfg['icon'] ?></div>
                        <h3 class="explore-card-tiv"><?= e($title) ?></h3>
                        <p class="explore-card-eng"><?= e($sub) ?></p>
                    </div>
                    <div class="explore-card-hover">
                        <p class="explore-card-hover-label"><?= $cfg['label'] ?></p>
                        <h4 class="explore-card-hover-title"><?= e($title) ?></h4>
                        <?php if ($sub): ?><p class="explore-card-hover-desc"><?= e($sub) ?></p><?php endif; ?>
                        <span class="explore-card-cta">Open &rsaquo;</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <button class="explore-arrow explore-arrow-right" aria-label="Scroll right" onclick="slideRow(this,1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </section>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<script>
function slideRow(btn, dir) {
    var wrap  = btn.closest('.explore-row-track-wrap');
    var track = wrap.querySelector('.explore-track');
    var card  = track.querySelector('.explore-card');
    var step  = card ? (card.offsetWidth + 12) * 3 : 660;
    track.scrollBy({ left: dir * step, behavior: 'smooth' });
}
document.querySelectorAll('.explore-track').forEach(function(track) {
    var wrap  = track.closest('.explore-row-track-wrap');
    var left  = wrap.querySelector('.explore-arrow-left');
    var right = wrap.querySelector('.explore-arrow-right');
    function update() {
        left.style.opacity  = track.scrollLeft > 20 ? '1' : '0';
        left.style.pointerEvents = track.scrollLeft > 20 ? 'auto' : 'none';
        var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 20;
        right.style.opacity = atEnd ? '0' : '1';
        right.style.pointerEvents = atEnd ? 'none' : 'auto';
    }
    track.addEventListener('scroll', update, { passive: true });
    update();
});
</script>
