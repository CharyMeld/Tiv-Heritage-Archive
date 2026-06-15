<?php
/**
 * Normalise a raw DB row into a card-ready array.
 * Returns: title, subtitle, meta, desc, url
 */
function exploreCard(string $cat, array $item): array
{
    switch ($cat) {
        case 'names':
            return [
                'title'    => $item['tiv_name'],
                'subtitle' => $item['english_meaning'] ?? '',
                'meta'     => ucfirst($item['gender'] ?? ''),
                'desc'     => $item['description'] ?? '',
                'url'      => url('name/' . $item['id']),
            ];
        case 'proverbs':
            $tiv = $item['tiv_text'] ?? '';
            return [
                'title'    => mb_strlen($tiv) > 44 ? mb_substr($tiv, 0, 42) . '…' : $tiv,
                'subtitle' => mb_substr($item['english_translation'] ?? '', 0, 70),
                'meta'     => $item['category'] ?? '',
                'desc'     => $item['deeper_meaning'] ?? '',
                'url'      => url('proverb/' . $item['id']),
            ];
        case 'plants':
            return [
                'title'    => $item['tiv_name'] ?? '',
                'subtitle' => $item['english_name'] ?? '',
                'meta'     => $item['scientific_name'] ?? '',
                'desc'     => $item['description'] ?? '',
                'url'      => url('plant/' . $item['id']),
                'image'    => $item['image'] ?? '',
            ];
        case 'festivals':
            return [
                'title'    => $item['tiv_name'] ?? '',
                'subtitle' => $item['english_name'] ?? '',
                'meta'     => $item['timing'] ?? '',
                'desc'     => $item['description'] ?? '',
                'url'      => url('festival/' . $item['id']),
                'image'    => !empty($item['gallery_image']) ? $item['gallery_image'] : ($item['image'] ?? ''),
            ];
        case 'foods':
            return [
                'title'    => $item['tiv_name'] ?? '',
                'subtitle' => $item['english_name'] ?? '',
                'meta'     => '',
                'desc'     => $item['description'] ?? '',
                'url'      => url('food/' . $item['id']),
                'image'    => $item['image'] ?? '',
            ];
        case 'words':
            return [
                'title'    => $item['tiv_word'] ?? '',
                'subtitle' => $item['english_meaning'] ?? '',
                'meta'     => $item['part_of_speech'] ?? '',
                'desc'     => $item['example_tiv'] ?? '',
                'url'      => url('word/' . $item['id']),
            ];
        case 'animals':
            return [
                'title'    => $item['tiv_name'] ?? '',
                'subtitle' => $item['name'] ?? '',
                'meta'     => '',
                'desc'     => $item['description'] ?? '',
                'url'      => url('animal/' . $item['id']),
                'image'    => $item['image'] ?? '',
            ];
        case 'bible':
            $ref = ($item['book'] ?? '') . ' ' . ($item['chapter'] ?? '') . ':' . ($item['verse'] ?? '');
            $eng = mb_substr($item['english_web'] ?? '', 0, 80);
            $tiv = mb_substr($item['tiv'] ?? '', 0, 80);
            return [
                'title'    => $ref,
                'subtitle' => $eng,
                'meta'     => !empty($tiv) ? $tiv : ($item['testament'] ?? ''),
                'desc'     => ($item['english_web'] ?? '') . (!empty($item['tiv']) ? ' | ' . $item['tiv'] : ''),
                'url'      => url('bible/' . ($item['book_key'] ?? '') . '/' . ($item['chapter'] ?? '')) . '#v' . ($item['verse'] ?? ''),
                'image'    => '',
            ];
        default:
            return ['title'=>'','subtitle'=>'','meta'=>'','desc'=>'','url'=>'#'];
    }
}
?>

<!-- ═══════════════════════════════════════════
     EXPLORE HERO BANNER
════════════════════════════════════════════ -->
<div class="explore-hero">
    <div class="explore-hero-inner">
        <div class="container">
            <p class="explore-eyebrow">&#128218; Tiv Culture Archive</p>
            <h1 class="explore-heading">Explore Tiv Culture</h1>
            <p class="explore-subheading">
                <?= number_format($totalEntries) ?> entries &mdash;
                names &bull; proverbs &bull; plants &bull; festivals &bull; foods &bull; dictionary &bull; animals &bull; bible
            </p>

            <!-- Search -->
            <form action="<?= url('archive') ?>" method="GET" class="explore-search-form">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" name="q" class="explore-search-input"
                           placeholder="Search the archive…" value="<?= e($_GET['q'] ?? '') ?>"
                           autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>

            <!-- Category quick-jump pills -->
            <div class="explore-pills">
                <?php foreach ($categories as $key => $cat): ?>
                <a href="<?= e($cat['url']) ?>" class="explore-pill explore-pill-<?= $key ?>">
                    <?= $cat['icon'] ?> <?= e($cat['label']) ?>
                    <span class="explore-pill-count"><?= number_format($cat['count']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     NETFLIX ROWS
════════════════════════════════════════════ -->
<div class="explore-rows-wrap">

    <?php foreach ($rows as $catKey => $row):
        if (empty($row['items'])) continue;
        $meta = $row['meta'];
    ?>
    <section class="explore-row" id="explore-<?= $catKey ?>">
        <div class="explore-row-header">
            <div class="explore-row-title-group">
                <span class="explore-row-icon"><?= $meta['icon'] ?></span>
                <h2 class="explore-row-title"><?= e($meta['label']) ?></h2>
                <span class="explore-row-badge"><?= number_format($meta['count']) ?></span>
            </div>
            <a href="<?= e($meta['url']) ?>" class="explore-row-seeall">
                See all <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>

        <div class="explore-row-track-wrap">
            <button class="explore-arrow explore-arrow-left"
                    aria-label="Scroll left"
                    onclick="slideRow(this,-1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </button>

            <div class="explore-track" data-cat="<?= $catKey ?>">
                <?php foreach ($row['items'] as $item):
                    $card = exploreCard($catKey, $item);
                    if (empty($card['title'])) continue;
                    $descSnip = $card['desc']
                        ? mb_substr(strip_tags($card['desc']), 0, 80) . '…'
                        : $card['subtitle'];
                ?>
                <?php $_img = !empty($card['image']) ? UPLOADS_URL . '/images/' . $card['image'] : ''; ?>
                <a href="<?= e($card['url']) ?>"
                   class="explore-card explore-card-<?= $catKey ?><?= $_img ? ' has-image' : '' ?>"
                   <?= $_img ? 'style="background-image:url(' . e($_img) . ')"' : '' ?>>
                    <div class="explore-card-front">
                        <?php if (!$_img): ?><div class="explore-card-cat-icon"><?= $meta['icon'] ?></div><?php endif; ?>
                        <h3 class="explore-card-tiv"><?= e($card['title']) ?></h3>
                        <p class="explore-card-eng"><?= e(mb_substr($card['subtitle'], 0, 55)) ?></p>
                        <?php if ($card['meta']): ?>
                        <span class="explore-card-meta"><?= e($card['meta']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="explore-card-hover">
                        <p class="explore-card-hover-label"><?= e($meta['label']) ?></p>
                        <h4 class="explore-card-hover-title"><?= e($card['title']) ?></h4>
                        <?php if ($descSnip): ?>
                        <p class="explore-card-hover-desc"><?= e($descSnip) ?></p>
                        <?php endif; ?>
                        <span class="explore-card-cta">Open &rsaquo;</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <button class="explore-arrow explore-arrow-right"
                    aria-label="Scroll right"
                    onclick="slideRow(this,1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </button>
        </div>
    </section>
    <?php endforeach; ?>

</div><!-- /.explore-rows-wrap -->

<!-- ═══════════════════════════════════════════
     BOTTOM CTA
════════════════════════════════════════════ -->
<section class="explore-cta">
    <div class="container">
        <h2 class="explore-cta-title">Help Preserve Tiv Heritage</h2>
        <p class="explore-cta-text">
            Every word, proverb, and tradition you add becomes part of a living digital museum for future generations.
        </p>
        <div class="hero-actions">
            <a href="<?= url('contribute') ?>" class="btn btn-primary">Contribute Content</a>
            <a href="<?= url('learn') ?>" class="btn btn-secondary">Learn Tiv Language</a>
        </div>
    </div>
</section>

<script>
function slideRow(btn, dir) {
    var wrap  = btn.closest('.explore-row-track-wrap');
    var track = wrap.querySelector('.explore-track');
    var card  = track.querySelector('.explore-card');
    var step  = card ? (card.offsetWidth + 12) * 3 : 660;
    track.scrollBy({ left: dir * step, behavior: 'smooth' });
}

// Show / hide arrows depending on scroll position
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
