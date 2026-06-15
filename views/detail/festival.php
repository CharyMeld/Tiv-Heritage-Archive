<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <a href="<?= url('archive/festivals') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            All Festivals
        </a>
        <span class="detail-banner-cat">&#127881; Tiv Festival</span>
        <h1 class="detail-banner-title"><?= e($item['tiv_name']) ?></h1>
        <?php if (!empty($item['english_name'])): ?>
        <p class="detail-banner-sub"><?= e($item['english_name']) ?></p>
        <?php endif; ?>
        <div class="detail-banner-badges">
            <?php if (!empty($item['timing'])): ?>
            <span class="detail-banner-badge">&#128197; <?= e($item['timing']) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['duration'])): ?>
            <span class="detail-banner-badge">&#8986; <?= e($item['duration']) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['location'])): ?>
            <span class="detail-banner-badge">&#128205; <?= e($item['location']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">About the Festival</span>
            <p class="detail-section-text"><?= nl2br(e($item['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['significance'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Cultural Significance</span>
            <p class="detail-section-text"><?= nl2br(e($item['significance'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['activities'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Activities</span>
            <p class="detail-section-text"><?= nl2br(e($item['activities'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['festival_type'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Festival Type</span>
            <p class="detail-section-text"><?= e($item['festival_type']) ?></p>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($gallery)): ?>
        <!-- ── Festival Gallery ──────────────────────────────── -->
        <div class="festival-gallery" id="festivalGallery">
            <h3 class="festival-gallery-heading">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                Festival Gallery
                <span class="festival-gallery-count"><?= count($gallery) ?> photo<?= count($gallery) !== 1 ? 's' : '' ?></span>
            </h3>
            <div class="festival-gallery-grid">
                <?php foreach ($gallery as $i => $photo): ?>
                <button
                    class="festival-gallery-thumb<?= $photo['is_featured'] ? ' is-featured' : '' ?>"
                    onclick="openFestivalLightbox(<?= $i ?>)"
                    aria-label="<?= e($photo['alt_text'] ?? $photo['caption'] ?? 'Festival photo') ?>"
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
        <div class="fg-lightbox" id="fgLightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
            <button class="fg-lightbox-close" onclick="closeFestivalLightbox()" aria-label="Close">&times;</button>
            <button class="fg-lightbox-prev" onclick="moveFestivalLightbox(-1)" aria-label="Previous">&#8249;</button>
            <button class="fg-lightbox-next" onclick="moveFestivalLightbox(1)"  aria-label="Next">&#8250;</button>
            <div class="fg-lightbox-inner">
                <img class="fg-lightbox-img" id="fgLightboxImg" src="" alt="">
                <p  class="fg-lightbox-caption" id="fgLightboxCaption"></p>
                <p  class="fg-lightbox-counter" id="fgLightboxCounter"></p>
            </div>
        </div>

        <style>
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
        .festival-gallery-thumb img {
            width: 100%; height: 100%; object-fit: cover; display: block;
        }
        .festival-gallery-caption {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,.55); color: #fff;
            font-size: .7rem; padding: .3rem .45rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        /* Lightbox */
        .fg-lightbox {
            position: fixed; inset: 0; z-index: 9000;
            background: rgba(0,0,0,.93);
            display: flex; align-items: center; justify-content: center;
        }
        .fg-lightbox[hidden] { display: none; }
        .fg-lightbox-inner { text-align: center; max-width: 90vw; }
        .fg-lightbox-img {
            max-height: 80vh; max-width: 88vw;
            border-radius: .5rem; display: block; margin: 0 auto;
        }
        .fg-lightbox-caption { color: #ddd; margin-top: .75rem; font-size: .9rem; min-height: 1.2em; }
        .fg-lightbox-counter { color: #888; font-size: .8rem; margin-top: .25rem; }
        .fg-lightbox-close, .fg-lightbox-prev, .fg-lightbox-next {
            position: fixed; background: rgba(255,255,255,.12);
            border: none; color: #fff; cursor: pointer;
            border-radius: 50%; width: 2.5rem; height: 2.5rem;
            font-size: 1.4rem; display: flex; align-items: center; justify-content: center;
            transition: background .2s;
        }
        .fg-lightbox-close:hover, .fg-lightbox-prev:hover, .fg-lightbox-next:hover {
            background: rgba(255,255,255,.25);
        }
        .fg-lightbox-close { top: 1rem;  right: 1rem; font-size: 1.6rem; }
        .fg-lightbox-prev  { left: 1rem; top: 50%; transform: translateY(-50%); }
        .fg-lightbox-next  { right: 1rem; top: 50%; transform: translateY(-50%); }
        @media (max-width: 600px) {
            .festival-gallery-grid { grid-template-columns: repeat(2, 1fr); }
        }
        </style>

        <script>
        (function () {
            const photos = <?= json_encode(array_map(fn($p) => [
                'src'     => UPLOADS_URL . '/images/' . $p['image_path'],
                'caption' => $p['caption'] ?? '',
                'alt'     => $p['alt_text'] ?? $p['caption'] ?? '',
            ], $gallery)) ?>;
            let current = 0;

            window.openFestivalLightbox = function (index) {
                current = index;
                render();
                document.getElementById('fgLightbox').removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            };
            window.closeFestivalLightbox = function () {
                document.getElementById('fgLightbox').setAttribute('hidden', '');
                document.body.style.overflow = '';
            };
            window.moveFestivalLightbox = function (dir) {
                current = (current + dir + photos.length) % photos.length;
                render();
            };
            function render() {
                document.getElementById('fgLightboxImg').src        = photos[current].src;
                document.getElementById('fgLightboxImg').alt        = photos[current].alt;
                document.getElementById('fgLightboxCaption').textContent = photos[current].caption;
                document.getElementById('fgLightboxCounter').textContent =
                    (current + 1) + ' / ' + photos.length;
            }
            document.addEventListener('keydown', function (e) {
                const lb = document.getElementById('fgLightbox');
                if (lb.hasAttribute('hidden')) return;
                if (e.key === 'Escape')     closeFestivalLightbox();
                if (e.key === 'ArrowLeft')  moveFestivalLightbox(-1);
                if (e.key === 'ArrowRight') moveFestivalLightbox(1);
            });
        })();
        </script>
        <?php endif; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Other Festivals</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url('festival/' . $relItem['id']) ?>" class="archive-card-modern archive-card-festivals">
                    <span class="archive-card-modern-icon">&#127881;</span>
                    <h4 class="archive-card-modern-title"><?= e($relItem['tiv_name']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['english_name'] ?? '') ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/festivals') ?>" class="btn btn-secondary">&larr; All Festivals</a>
        </div>
    </div>
</div>
