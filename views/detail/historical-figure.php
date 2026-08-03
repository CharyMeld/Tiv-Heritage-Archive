<?php
$sections = [
    'biography'                => 'Biography',
    'early_life'               => 'Early Life',
    'education'                => 'Education',
    'career'                   => 'Career',
    'leadership_service'       => 'Leadership & Service',
    'achievements'             => 'Major Achievements',
    'historical_significance'  => 'Historical Significance',
    'legacy'                   => 'Legacy',
];
?>
<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark">
            <?php $this->partial('breadcrumb'); ?>
        </div>
        <span class="detail-banner-cat">&#129332; <?= e($item['category']) ?><?= $item['subcategory'] ? ' &rsaquo; ' . e($item['subcategory']) : '' ?></span>
        <h1 class="detail-banner-title"><?= e($item['english_name']) ?></h1>
        <?php if (!empty($item['tiv_name'])): ?>
        <p class="detail-banner-sub"><?= e($item['tiv_name']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['title'])): ?>
        <p class="detail-banner-sub"><?= e($item['title']) ?></p>
        <?php endif; ?>
        <?php $ordinal = HistoricalFigure::ordinalLabel($item); ?>
        <?php if ($ordinal): ?>
        <span class="detail-banner-badge" style="background:var(--color-accent,#c8832a);color:#fff;font-weight:700;"><?= e($ordinal) ?></span>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['image'])): ?>
        <div style="margin-bottom:1.5rem;border-radius:.75rem;overflow:hidden;">
            <?= SeoHelper::heroPicture($item['image'], $item['english_name'], 'width:100%;max-height:480px;object-fit:cover;display:block;', 'fetchpriority="high"') ?>
        </div>
        <?php endif; ?>

        <!-- Quick Facts -->
        <?php
        $facts = [
            'Gender'            => $item['gender'] ? ucfirst($item['gender']) : null,
            'Date of Birth'     => $item['date_of_birth'],
            'Place of Birth'    => $item['place_of_birth'],
            'Date of Death'     => $item['date_of_death'],
            'Burial Place'      => $item['burial_place'],
            'Clan'              => $item['clan'],
            'District'          => $item['district'],
            'Local Government'  => $item['local_government'],
            'State'             => $item['state'],
            'Country'           => $item['country'],
            'Religion'          => $item['religion'],
            'Occupation'        => $item['occupation'],
            'Historical Period' => $item['historical_period'],
        ];
        $facts = array_filter($facts, fn($v) => !empty($v));
        ?>
        <?php if ($facts): ?>
        <div class="detail-section-modern hf-quick-facts">
            <span class="detail-section-label">Quick Facts</span>
            <dl class="hf-facts-grid">
                <?php foreach ($facts as $label => $value): ?>
                <div class="hf-fact">
                    <dt><?= e($label) ?></dt>
                    <dd><?= e($value) ?></dd>
                </div>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php endif; ?>

        <?php foreach ($sections as $field => $label): ?>
        <?php if (!empty($item[$field])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label"><?= e($label) ?></span>
            <p class="detail-section-text"><?= nl2br(e($item[$field])) ?></p>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($item['timeline_notes'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Timeline</span>
            <ul class="hf-timeline">
                <?php foreach (preg_split('/\r?\n/', trim($item['timeline_notes'])) as $line): ?>
                <?php if (trim($line) !== ''): ?>
                <li><?= e(trim($line)) ?></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['references_text'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Further Reading</span>
            <ul class="hf-references">
                <?php foreach (preg_split('/\r?\n/', trim($item['references_text'])) as $line): ?>
                <?php if (trim($line) !== ''): ?>
                <li><?= e(trim($line)) ?></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($gallery)): ?>
        <!-- ── Gallery ──────────────────────────────── -->
        <div class="festival-gallery" id="hfGallery">
            <h3 class="festival-gallery-heading">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                Gallery
                <span class="festival-gallery-count"><?= count($gallery) ?> photo<?= count($gallery) !== 1 ? 's' : '' ?></span>
            </h3>
            <div class="festival-gallery-grid">
                <?php foreach ($gallery as $i => $photo): ?>
                <button
                    class="festival-gallery-thumb<?= $photo['is_featured'] ? ' is-featured' : '' ?>"
                    onclick="openHfLightbox(<?= $i ?>)"
                    aria-label="<?= e($photo['alt_text'] ?? $photo['caption'] ?? 'Photo') ?>"
                    type="button"
                >
                    <img
                        src="<?= e(UPLOADS_URL . '/images/' . $photo['image_path']) ?>"
                        alt="<?= e($photo['alt_text'] ?? $photo['caption'] ?? '') ?>"
                        loading="lazy"
                    >
                    <?php if (!empty($photo['caption'])): ?>
                    <span class="festival-gallery-caption"><?= e($photo['caption']) ?></span>
                    <?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lightbox -->
        <div class="fg-lightbox" id="hfLightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
            <button class="fg-lightbox-close" onclick="closeHfLightbox()" aria-label="Close">&times;</button>
            <button class="fg-lightbox-prev" onclick="moveHfLightbox(-1)" aria-label="Previous">&#8249;</button>
            <button class="fg-lightbox-next" onclick="moveHfLightbox(1)"  aria-label="Next">&#8250;</button>
            <div class="fg-lightbox-inner">
                <img class="fg-lightbox-img" id="hfLightboxImg" src="" alt="">
                <p  class="fg-lightbox-caption" id="hfLightboxCaption"></p>
                <p  class="fg-lightbox-counter" id="hfLightboxCounter"></p>
            </div>
        </div>

        <script>
        (function () {
            const photos = <?= json_encode(array_map(fn($p) => [
                'src'     => UPLOADS_URL . '/images/' . $p['image_path'],
                'caption' => $p['caption'] ?? '',
                'alt'     => $p['alt_text'] ?? $p['caption'] ?? '',
            ], $gallery)) ?>;
            let current = 0;

            window.openHfLightbox = function (index) {
                current = index;
                render();
                document.getElementById('hfLightbox').removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            };
            window.closeHfLightbox = function () {
                document.getElementById('hfLightbox').setAttribute('hidden', '');
                document.body.style.overflow = '';
            };
            window.moveHfLightbox = function (dir) {
                current = (current + dir + photos.length) % photos.length;
                render();
            };
            function render() {
                document.getElementById('hfLightboxImg').src        = photos[current].src;
                document.getElementById('hfLightboxImg').alt        = photos[current].alt;
                document.getElementById('hfLightboxCaption').textContent = photos[current].caption;
                document.getElementById('hfLightboxCounter').textContent =
                    (current + 1) + ' / ' + photos.length;
            }
            document.addEventListener('keydown', function (e) {
                const lb = document.getElementById('hfLightbox');
                if (lb.hasAttribute('hidden')) return;
                if (e.key === 'Escape')     closeHfLightbox();
                if (e.key === 'ArrowLeft')  moveHfLightbox(-1);
                if (e.key === 'ArrowRight') moveHfLightbox(1);
            });
        })();
        </script>
        <?php endif; ?>

        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Related Historical Figures</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url(SeoHelper::canonicalSlugPath('historical-figure', $relItem['id'], $relItem['english_name'])) ?>" class="archive-card-modern">
                    <span class="archive-card-modern-icon">&#129332;</span>
                    <h4 class="archive-card-modern-title"><?= e($relItem['english_name']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['title'] ?? '') ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('historical-figures') ?>" class="btn btn-secondary">&larr; All Historical Figures</a>
        </div>
    </div>
</div>

<style>
.hf-facts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: .9rem; margin: 0; }
.hf-fact dt { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: var(--color-muted, #7a6a5a); margin: 0 0 .15rem; }
.hf-fact dd { font-size: .92rem; font-weight: 600; color: var(--color-heading, #2d1b0e); margin: 0; }
.hf-timeline, .hf-references { margin: 0; padding-left: 1.2rem; }
.hf-timeline li, .hf-references li { margin-bottom: .5rem; line-height: 1.5; }
.festival-gallery { margin-top: 2.5rem; }
.festival-gallery-heading {
    display: flex; align-items: center; gap: .5rem;
    font-family: var(--font-heading); font-size: 1.1rem;
    color: var(--color-primary); margin-bottom: 1rem;
}
.festival-gallery-count {
    font-size: .8rem; font-weight: 400;
    background: var(--color-bg-soft, #f4f0eb);
    color: var(--color-muted, #777);
    padding: .15rem .55rem; border-radius: 999px;
}
.festival-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: .75rem;
}
.festival-gallery-thumb {
    position: relative; overflow: hidden;
    border-radius: .5rem; aspect-ratio: 4/3;
    background: var(--color-bg-soft, #f4f0eb);
    border: 2px solid transparent;
    cursor: pointer; padding: 0;
    transition: border-color .2s, transform .2s;
}
.festival-gallery-thumb:hover { transform: scale(1.03); border-color: var(--color-primary); }
.festival-gallery-thumb.is-featured { border-color: var(--color-accent, #c8832a); }
.festival-gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.festival-gallery-caption {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(0,0,0,.55); color: #fff;
    font-size: .7rem; padding: .3rem .45rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.fg-lightbox {
    position: fixed; inset: 0; z-index: 9000;
    background: rgba(0,0,0,.93);
    display: flex; align-items: center; justify-content: center;
}
.fg-lightbox[hidden] { display: none; }
.fg-lightbox-inner { text-align: center; max-width: 90vw; }
.fg-lightbox-img { max-height: 80vh; max-width: 88vw; border-radius: .5rem; display: block; margin: 0 auto; }
.fg-lightbox-caption { color: #ddd; margin-top: .75rem; font-size: .9rem; min-height: 1.2em; }
.fg-lightbox-counter { color: #888; font-size: .8rem; margin-top: .25rem; }
.fg-lightbox-close, .fg-lightbox-prev, .fg-lightbox-next {
    position: fixed; background: rgba(255,255,255,.12);
    border: none; color: #fff; cursor: pointer;
    border-radius: 50%; width: 2.5rem; height: 2.5rem;
    font-size: 1.4rem; display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.fg-lightbox-close:hover, .fg-lightbox-prev:hover, .fg-lightbox-next:hover { background: rgba(255,255,255,.25); }
.fg-lightbox-close { top: 1rem;  right: 1rem; font-size: 1.6rem; }
.fg-lightbox-prev  { left: 1rem; top: 50%; transform: translateY(-50%); }
.fg-lightbox-next  { right: 1rem; top: 50%; transform: translateY(-50%); }
@media (max-width: 600px) {
    .festival-gallery-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
