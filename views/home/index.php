<!-- ═══════════════════════════════════════════
     CINEMATIC HERO
════════════════════════════════════════════ -->
<div class="home-hero">
    <div class="home-hero-inner">
        <div class="container">
            <div class="home-hero-content" style="text-align:center;margin:0 auto;">
                <h1 class="home-hero-title">Wisdom of the<br>Tiv People</h1>
                <p class="home-hero-sub">Preserving Language &bull; Culture &bull; Heritage</p>
                <div class="home-hero-actions" style="justify-content:center;">
                    <a href="<?= url('archive') ?>" class="btn home-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        Explore the Archive
                    </a>
                    <a href="<?= url('learn') ?>" class="btn home-btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                        Learn Tiv Language
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     CATEGORY GRID
════════════════════════════════════════════ -->
<section class="home-cats-section">
    <div class="container">
        <div class="home-section-header">
            <h2 class="home-section-title">Browse by Category</h2>
            <a href="<?= url('archive') ?>" class="home-section-link">View all &rsaquo;</a>
        </div>
        <div class="home-cats-grid">
            <?php
            $catCounts = $categoryCounts ?? [];
            $cats = [
                ['href'=>url(section_path('names')),        'icon'=>'&#128100;', 'label'=>'Tiv Names',    'desc'=>'Traditional names & meanings',          'cls'=>'names',        'count'=>$catCounts['names'] ?? null],
                ['href'=>url(section_path('proverbs')),     'icon'=>'&#128221;', 'label'=>'Proverbs',     'desc'=>'Ancient wisdom & sayings',              'cls'=>'proverbs',     'count'=>$catCounts['proverbs'] ?? null],
                ['href'=>url(section_path('plants')),       'icon'=>'&#127807;', 'label'=>'Plants',       'desc'=>'Medicinal & sacred plants',             'cls'=>'plants',       'count'=>$catCounts['plants'] ?? null],
                ['href'=>url(section_path('festivals')),    'icon'=>'&#127881;', 'label'=>'Festivals',    'desc'=>'Cultural celebrations',                 'cls'=>'festivals',    'count'=>$catCounts['festivals'] ?? null],
                ['href'=>url(section_path('foods')),        'icon'=>'&#127858;', 'label'=>'Foods',        'desc'=>'Traditional cuisine & recipes',         'cls'=>'foods',        'count'=>$catCounts['foods'] ?? null],
                ['href'=>url(section_path('words')),        'icon'=>'&#128172;', 'label'=>'Dictionary',   'desc'=>'Tiv words & translations',              'cls'=>'words',        'count'=>$catCounts['words'] ?? null],
                ['href'=>url(section_path('animals')),      'icon'=>'&#128062;', 'label'=>'Animals',      'desc'=>'Animals in Tiv culture & significance', 'cls'=>'animals',      'count'=>$catCounts['animals'] ?? null],
                ['href'=>url('bible'),                'icon'=>'&#128214;', 'label'=>'Bible',        'desc'=>'Icighan Bibilo — English &amp; Tiv',    'cls'=>'bible',        'count'=>$catCounts['bible'] ?? null],
                ['href'=>url('archive/documents'),    'icon'=>'&#128196;', 'label'=>'Documents',    'desc'=>'Historical manuscripts & records',      'cls'=>'documents',    'count'=>$catCounts['documents'] ?? null],
                ['href'=>url('archive/audio'),        'icon'=>'&#127911;', 'label'=>'Audio',        'desc'=>'Songs, speeches & oral traditions',     'cls'=>'audio',        'count'=>$catCounts['audio'] ?? null],
                ['href'=>url('archive/publications'), 'icon'=>'&#128218;', 'label'=>'Publications', 'desc'=>'Academic & community research',         'cls'=>'publications', 'count'=>$catCounts['publications'] ?? null],
            ];
            foreach ($cats as $cat):
            ?>
            <a href="<?= $cat['href'] ?>" class="home-cat-tile home-cat-<?= $cat['cls'] ?>">
                <span class="home-cat-icon"><?= $cat['icon'] ?></span>
                <div class="home-cat-body">
                    <h3 class="home-cat-label">
                        <?= $cat['label'] ?>
                        <?php if ($cat['count'] !== null): ?>
                        <span class="home-cat-count"><?= number_format($cat['count']) ?></span>
                        <?php endif; ?>
                    </h3>
                    <p class="home-cat-desc"><?= $cat['desc'] ?></p>
                </div>
                <svg class="home-cat-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     MAJOR SECTIONS — Language, Literature, Culture, History
     Same labels/descriptions/icons/links as the real /language, /literature,
     /culture, /history pages (views/content/section.php) — passed in from
     ContentCategoryController::getSections(), never duplicated here.
════════════════════════════════════════════ -->
<?php foreach (($contentSections ?? []) as $sectionKey => $sectionConfig): ?>
<section class="home-mega-section">
    <div class="container">
        <div class="home-mega-header">
            <div class="home-mega-eyebrow">
                <span class="home-mega-icon"><?= $sectionConfig['icon'] ?></span>
                <div>
                    <h2 class="home-mega-title"><?= e($sectionConfig['title']) ?></h2>
                    <p class="home-mega-desc"><?= e($sectionConfig['description']) ?></p>
                </div>
            </div>
            <a href="<?= url($sectionKey) ?>" class="home-mega-viewall">
                View <?= e($sectionConfig['title']) ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
        <div class="home-mega-grid">
            <?php foreach ($sectionConfig['subcategories'] as $subKey => $sub):
                $isPlaceholder = !empty($sub['placeholder']);
                $href = $isPlaceholder
                    ? url($sectionKey . '/' . $subKey)
                    : url($sub['redirect'] ?? ($sectionKey . '/' . $subKey));
            ?>
            <a href="<?= $href ?>" class="home-mega-card" style="--card-accent:<?= e($sectionConfig['color']) ?>"
               aria-label="<?= e($sub['label']) ?> — <?= e($sub['description']) ?>">
                <span class="home-mega-card-icon"><?= $sub['icon'] ?></span>
                <h3 class="home-mega-card-label"><?= e($sub['label']) ?></h3>
                <p class="home-mega-card-desc"><?= e($sub['description']) ?></p>
                <span class="home-mega-card-cta"><?= $isPlaceholder ? 'Learn more &rarr;' : 'Explore &rarr;' ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php if (ads_on()): ?>
<!-- Ad 3: between Category Grid and Learning Videos -->
<div class="container">
    <div class="adsense-wrap">
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="<?= ADSENSE_CLIENT ?>"
             data-ad-slot="<?= ADSENSE_SLOT ?>"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════
     LEARNING VIDEOS ROW
════════════════════════════════════════════ -->
<?php if (!empty($learningVideos)): ?>
<section class="home-videos-section">
    <div class="home-videos-inner">
        <div class="explore-row-header" style="padding: 0 1rem 1rem;">
            <div class="explore-row-title-group">
                <span class="explore-row-icon">&#127909;</span>
                <h2 class="explore-row-title" style="color:#fff;">Learn Tiv Language</h2>
            </div>
            <a href="<?= url('learn') ?>" class="explore-row-seeall" style="color:var(--color-accent);">
                All lessons <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
        <div class="explore-row-track-wrap">
            <button class="explore-arrow explore-arrow-left" aria-label="Scroll left" onclick="slideRow(this,-1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="explore-track home-video-track">
                <?php foreach ($learningVideos as $video): ?>
                <a href="<?= url('learn/' . $video['id']) ?>" class="home-video-card">
                    <div class="home-video-thumb">
                        <img src="https://img.youtube.com/vi/<?= e($video['youtube_id']) ?>/mqdefault.jpg"
                             alt="<?= e($video['title']) ?>" loading="lazy">
                        <div class="home-video-overlay">
                            <div class="home-video-play">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                     viewBox="0 0 24 24" fill="white">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                        </div>
                        <?php if (!empty($video['difficulty'])): ?>
                        <span class="home-video-diff difficulty-<?= e($video['difficulty']) ?>">
                            <?= ucfirst(e($video['difficulty'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="home-video-info">
                        <h4 class="home-video-title"><?= e($video['title']) ?></h4>
                        <?php if (!empty($video['description'])): ?>
                        <p class="home-video-desc"><?= e(mb_substr($video['description'],0,70)) ?>…</p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <button class="explore-arrow explore-arrow-right" aria-label="Scroll right" onclick="slideRow(this,1)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>



<!-- ═══════════════════════════════════════════
     FESTIVAL & FOOD PHOTO MARQUEE
════════════════════════════════════════════ -->
<?php
// Build festival photo cards (from gallery) + food placeholder cards
$marqueeCards = [];
if (!empty($festivalGalleryPhotos)) {
    foreach ($festivalGalleryPhotos as $p) {
        $marqueeCards[] = [
            'type'    => 'festival',
            'src'     => UPLOADS_URL . '/images/' . $p['image_path'],
            'label'   => $p['festival_name'] ?? '',
            'sub'     => $p['english_name'] ?? '',
            'link'    => url(SeoHelper::canonicalSlugPath('festival', $p['festival_id'], $p['festival_name'] ?? '')),
            'caption' => $p['caption'] ?? '',
        ];
    }
}
// Build food placeholder cards (always shown as row 2 seeds)
$foodCards = [];
if (!empty($featuredFoods)) {
    foreach ($featuredFoods as $fo) {
        $foodCards[] = [
            'type'  => 'food',
            'label' => $fo['tiv_name'] ?? '',
            'sub'   => $fo['english_name'] ?? '',
            'link'  => url(SeoHelper::canonicalSlugPath('food', $fo['id'], $fo['tiv_name'] ?? '')),
            'src'   => !empty($fo['image']) ? UPLOADS_URL . '/images/' . $fo['image'] : '',
        ];
    }
}
?>
<?php
// Interleave festival and food cards into one combined array
$allMarqueeCards = [];
$fi = 0; $fdi = 0;
$mc = count($marqueeCards); $fc = count($foodCards);
$total = max($mc, $fc) * 2;
for ($i = 0; $i < $total; $i++) {
    if ($mc > 0 && ($fc === 0 || $fi <= $fdi)) { $allMarqueeCards[] = $marqueeCards[$fi % $mc]; $fi++; }
    if ($fc > 0 && ($mc === 0 || $fdi < $fi))  { $allMarqueeCards[] = $foodCards[$fdi % $fc];    $fdi++; }
}
?>
<?php if (!empty($allMarqueeCards)): ?>
<?php
    // Repeat until at least 10 cards, then duplicate for seamless CSS loop
    $singleRow = $allMarqueeCards;
    while (count($singleRow) < 10) {
        $singleRow = array_merge($singleRow, $allMarqueeCards);
    }
    $singleRow = array_merge($singleRow, $singleRow);
?>
<section class="ffl-marquee-section" aria-label="Festival and Food Photo Gallery">

    <div class="ffl-row" aria-hidden="true">
        <div class="ffl-track ffl-track-left">
            <?php foreach ($singleRow as $card): ?>
            <a href="<?= e($card['link']) ?>"
               class="ffl-card ffl-card-<?= e($card['type']) ?>"
               style="<?= !empty($card['src']) ? 'background-image:url(' . e($card['src']) . ')' : '' ?>"
               title="<?= e($card['label']) ?>">
                <div class="ffl-card-overlay">
                    <span class="ffl-card-type"><?= $card['type'] === 'festival' ? 'Festival' : 'Food' ?></span>
                    <span class="ffl-card-name"><?= e($card['label']) ?></span>
                    <?php if (!empty($card['sub'])): ?>
                    <span class="ffl-card-sub"><?= e($card['sub']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (empty($card['src'])): ?>
                <span class="ffl-placeholder-icon" aria-hidden="true"><?= $card['type'] === 'festival' ? '&#127881;' : '&#127858;' ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="ffl-edge ffl-edge-left"  aria-hidden="true"></div>
    <div class="ffl-edge ffl-edge-right" aria-hidden="true"></div>

    <div class="ffl-label-bar">
        <span class="ffl-label-text">Living Culture &mdash; Festivals &amp; Foods of the Tiv People</span>
    </div>
</section>

<style>
/* ── Festival & Food Marquee ──────────────────────────────────────── */
.ffl-marquee-section {
    position: relative;
    background: #0f0a03;
    padding: 2.75rem 0 2.25rem;
    overflow: hidden;
}
.ffl-row { overflow: hidden; }

.ffl-track {
    display: flex;
    gap: .75rem;
    width: max-content;
}
.ffl-track-left { animation: ffl-left 90s linear infinite; }

@keyframes ffl-left { from { transform: translateX(0); } to { transform: translateX(-50%); } }

@media (prefers-reduced-motion: reduce) {
    .ffl-track-left { animation: none; }
}

.ffl-marquee-section:hover .ffl-track-left {
    animation-play-state: paused;
}

/* Cards */
.ffl-card {
    position: relative;
    display: flex;
    align-items: flex-end;
    width: 380px;
    height: 280px;
    border-radius: .75rem;
    overflow: hidden;
    flex-shrink: 0;
    text-decoration: none;
    background-size: cover;
    background-position: center;
    background-color: #1e1206;
    transition: transform .25s, box-shadow .25s;
}
@media (max-width: 600px) {
    .ffl-card { width: 260px; height: 190px; }
}
.ffl-card:hover {
    transform: scale(1.04);
    box-shadow: 0 8px 28px rgba(0,0,0,.6);
    z-index: 2;
}

.ffl-card-festival { border: 1.5px solid rgba(200,131,42,.18); }
.ffl-card-food     { border: 1.5px solid rgba(140,170,80,.18); }

.ffl-card-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: .55rem .65rem .6rem;
    background: linear-gradient(to top, rgba(0,0,0,.82) 0%, rgba(0,0,0,.25) 60%, transparent 100%);
}
.ffl-card-type {
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgba(255,255,255,.55);
    margin-bottom: .15rem;
}
.ffl-card-name {
    font-size: .82rem;
    font-weight: 600;
    color: #fff;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ffl-card-sub {
    font-size: .67rem;
    color: rgba(255,255,255,.55);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: .1rem;
}

.ffl-placeholder-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.8rem;
    opacity: .22;
    pointer-events: none;
}

/* Edge fade gradients */
.ffl-edge {
    position: absolute;
    top: 0; bottom: 0;
    width: 80px;
    pointer-events: none;
    z-index: 3;
}
.ffl-edge-left  { left: 0;  background: linear-gradient(to right, #0f0a03, transparent); }
.ffl-edge-right { right: 0; background: linear-gradient(to left,  #0f0a03, transparent); }

/* Bottom label */
.ffl-label-bar {
    text-align: center;
    margin-top: 1.25rem;
    padding: 0 1rem;
}
.ffl-label-text {
    font-size: .72rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: rgba(255,255,255,.28);
    font-family: var(--font-body, sans-serif);
}
</style>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     BIBLE SECTION
════════════════════════════════════════════ -->
<?php if (!empty($dailyVerse) || !empty($verseBatch)): ?>
<?php
$_batchJson  = json_encode($verseBatch['verses'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
$_verseTotal = (int)($verseTotal ?? 30861);
$_apiUrl     = url('bible/verses');
?>
<section class="home-bible-section">
    <div class="container">

        <div class="home-section-header">
            <h2 class="home-section-title" style="color:#fff;">&#128214; Icighan Bibilo &mdash; The Bible</h2>
            <a href="<?= url('bible') ?>" class="home-section-link" style="color:var(--color-accent);">
                Read Bible &rsaquo;
            </a>
        </div>

        <!-- ── Animated Verse Card ── -->
        <div class="bvc-outer">
            <div class="bvc-card" id="bvcCard">

                <!-- Top bar: reference + controls -->
                <div class="bvc-topbar">
                    <span class="bvc-ref" id="bvcRef">&#128214;</span>
                    <div class="bvc-controls">
                        <button class="bvc-btn" id="bvcPrev" onclick="bvcNav(-1)" title="Previous verse">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button class="bvc-btn" id="bvcPause" onclick="bvcTogglePause()" title="Pause / Play">
                            <svg id="bvcPauseIcon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        </button>
                        <button class="bvc-btn" id="bvcNext" onclick="bvcNav(1)" title="Next verse">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Verse text -->
                <div class="bvc-body" id="bvcBody">
                    <p class="bvc-tiv" id="bvcTiv"></p>
                    <p class="bvc-eng" id="bvcEng"></p>
                </div>

                <!-- Progress bar -->
                <div class="bvc-progress-track">
                    <div class="bvc-progress-bar" id="bvcBar"></div>
                </div>

                <!-- Footer: read link + verse counter -->
                <div class="bvc-footer">
                    <a class="bvc-read-link" id="bvcLink" href="#">Read chapter &rsaquo;</a>
                    <span class="bvc-counter" id="bvcCounter"></span>
                </div>

            </div>
        </div><!-- /.bvc-outer -->

        <div style="text-align:center;margin-top:1.75rem;display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= url('bible') ?>" class="btn home-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </svg>
                Read Full Bible
            </a>
            <a href="<?= url('admin/bible') ?>" class="btn home-btn-ghost">Add Tiv Text &rsaquo;</a>
        </div>

    </div>
</section>

<style>
/* ── Bible Section wrapper ── */
.home-bible-section {
    background: linear-gradient(160deg, #0d0702 0%, #1a0f05 50%, #0d0702 100%);
    padding: 3.5rem 0;
    border-top: 1px solid rgba(200,131,42,.18);
    border-bottom: 1px solid rgba(200,131,42,.18);
}

/* ── Card outer — max width centred ── */
.bvc-outer {
    max-width: 680px;
    margin: 1.75rem auto 0;
}

/* ── The card ── */
.bvc-card {
    background: linear-gradient(145deg, #1c1005, #2d1a0a);
    border: 1.5px solid rgba(200,131,42,.35);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 48px rgba(0,0,0,.55), 0 0 0 1px rgba(200,131,42,.08);
    transition: box-shadow .3s;
}
.bvc-card:hover {
    box-shadow: 0 16px 64px rgba(0,0,0,.65), 0 0 0 1px rgba(200,131,42,.18);
}

/* Top bar */
.bvc-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .85rem 1.25rem .6rem;
    border-bottom: 1px solid rgba(200,131,42,.12);
}
.bvc-ref {
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--color-accent, #c8832a);
    font-family: var(--font-ui, sans-serif);
}
.bvc-controls { display: flex; gap: .35rem; }
.bvc-btn {
    width: 2rem; height: 2rem;
    border: 1px solid rgba(200,131,42,.3);
    border-radius: 50%;
    background: rgba(200,131,42,.08);
    color: rgba(245,237,224,.7);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .18s, color .18s, border-color .18s;
}
.bvc-btn:hover {
    background: rgba(200,131,42,.25);
    color: #f5ede0;
    border-color: rgba(200,131,42,.6);
}

/* Body — verse text */
.bvc-body {
    padding: 1.75rem 1.75rem 1.25rem;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    gap: .85rem;
    transition: opacity .4s ease, transform .4s ease;
}
.bvc-body.fading {
    opacity: 0;
    transform: translateY(6px);
}
.bvc-tiv {
    font-family: var(--font-heading, serif);
    font-size: 1.18rem;
    font-weight: 700;
    font-style: italic;
    color: #f5ede0;
    line-height: 1.65;
    margin: 0;
    border-left: 3px solid var(--color-accent, #c8832a);
    padding-left: 1rem;
}
.bvc-tiv.tiv-absent {
    font-size: .88rem;
    font-weight: 400;
    color: rgba(245,237,224,.35);
    font-style: italic;
    border-left-color: rgba(200,131,42,.2);
}
.bvc-eng {
    font-size: .93rem;
    line-height: 1.65;
    color: rgba(245,237,224,.6);
    margin: 0;
    padding-left: 1rem;
    font-style: italic;
}

/* Progress bar */
.bvc-progress-track {
    height: 3px;
    background: rgba(200,131,42,.15);
    position: relative;
    overflow: hidden;
}
.bvc-progress-bar {
    height: 100%;
    background: var(--color-accent, #c8832a);
    width: 0%;
    transition: width linear;
}

/* Footer */
.bvc-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .65rem 1.25rem .85rem;
    border-top: 1px solid rgba(200,131,42,.1);
}
.bvc-read-link {
    font-size: .75rem;
    font-weight: 700;
    color: var(--color-accent, #c8832a);
    text-decoration: none;
    font-family: var(--font-ui, sans-serif);
}
.bvc-read-link:hover { text-decoration: underline; }
.bvc-counter {
    font-size: .68rem;
    color: rgba(245,237,224,.25);
    font-family: var(--font-ui, sans-serif);
}
</style>

<script>
(function () {
    var BATCH      = <?= $_batchJson ?>;  /* Genesis 1:1 … always */
    var batchStart = 0;                   /* global offset of BATCH[0] */
    var total      = <?= $_verseTotal ?>;
    var apiUrl     = '<?= $_apiUrl ?>';
    var INTERVAL   = 8000;
    var idx        = 0;
    var paused     = false;
    var timer      = null;

    var elRef       = document.getElementById('bvcRef');
    var elTiv       = document.getElementById('bvcTiv');
    var elEng       = document.getElementById('bvcEng');
    var elBody      = document.getElementById('bvcBody');
    var elBar       = document.getElementById('bvcBar');
    var elLink      = document.getElementById('bvcLink');
    var elCounter   = document.getElementById('bvcCounter');
    var elPauseIcon = document.getElementById('bvcPauseIcon');

    function render(v) {
        elRef.textContent = v.book + ' ' + v.chapter + ':' + v.verse;
        if (v.tiv) {
            elTiv.textContent = '“' + v.tiv + '”';
            elTiv.className   = 'bvc-tiv';
        } else {
            elTiv.textContent = 'Tiv translation coming soon…';
            elTiv.className   = 'bvc-tiv tiv-absent';
        }
        elEng.textContent     = '“' + v.english_web + '”';
        elLink.href           = '<?= url('bible') ?>/' + v.book_key + '/' + v.chapter + '#v' + v.verse;
        elCounter.textContent = ((batchStart + idx) % total + 1) + ' / ' + total;
    }

    function showVerse(v, skipAnim) {
        if (skipAnim) { render(v); return; }
        elBody.classList.add('fading');
        setTimeout(function () { render(v); elBody.classList.remove('fading'); }, 380);
    }

    function startBar() {
        elBar.style.transition = 'none';
        elBar.style.width      = '0%';
        setTimeout(function () {
            elBar.style.transition = 'width ' + INTERVAL + 'ms linear';
            elBar.style.width      = '100%';
        }, 30);
    }

    function fetchBatch(offset, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', apiUrl + '?offset=' + (offset % total) + '&limit=20');
        xhr.onload = function () {
            try {
                var d  = JSON.parse(xhr.responseText);
                BATCH      = d.verses;
                batchStart = offset % total;
            } catch (e) {}
            cb && cb();
        };
        xhr.onerror = function () { cb && cb(); };
        xhr.send();
    }

    function advance() {
        idx++;
        if (idx >= BATCH.length) {
            var next = (batchStart + BATCH.length) % total;
            fetchBatch(next, function () { idx = 0; showVerse(BATCH[0]); startBar(); });
            return;
        }
        showVerse(BATCH[idx]);
        startBar();
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(function () { if (!paused) advance(); }, INTERVAL);
    }

    /* ── Public controls ── */
    window.bvcNav = function (dir) {
        clearInterval(timer);
        var ni = idx + dir;
        if (ni >= 0 && ni < BATCH.length) {
            idx = ni;
            showVerse(BATCH[idx]);
            startBar();
            startTimer();
            return;
        }
        /* Need adjacent batch */
        var nextOff = dir > 0
            ? (batchStart + BATCH.length) % total
            : ((batchStart - 20) % total + total) % total;
        fetchBatch(nextOff, function () {
            idx = dir > 0 ? 0 : BATCH.length - 1;
            showVerse(BATCH[idx]);
            startBar();
            startTimer();
        });
    };

    window.bvcTogglePause = function () {
        paused = !paused;
        if (paused) {
            elBar.style.transition = 'none';
            elPauseIcon.innerHTML  = '<polygon points=”5 3 19 12 5 21 5 3”/>';
        } else {
            elPauseIcon.innerHTML  = '<rect x=”6” y=”4” width=”4” height=”16”/><rect x=”14” y=”4” width=”4” height=”16”/>';
            startBar();
        }
    };

    /* ── Always start at Genesis 1:1 ── */
    if (BATCH.length) {
        showVerse(BATCH[0], true);
        startBar();
        startTimer();
    }
}());
</script>
<?php endif; ?>



<!-- ═══════════════════════════════════════════
     BOTTOM CTA
════════════════════════════════════════════ -->
<section class="explore-cta">
    <div class="container">
        <h2 class="explore-cta-title">Preserve Our Heritage</h2>
        <p class="explore-cta-text">
            Every word, name, and tradition you add becomes part of a living digital museum
            for Tiv communities, researchers, and future generations.
        </p>
        <div class="home-hero-actions">
            <a href="<?= url('contribute') ?>" class="btn home-btn-primary">Contribute Content</a>
            <a href="<?= url('register') ?>" class="btn home-btn-ghost">Join the Community</a>
        </div>
    </div>
</section>

<script>
/* ── Netflix row scroll ── */
function slideRow(btn, dir) {
    var wrap  = btn.closest('.explore-row-track-wrap');
    var track = wrap.querySelector('.explore-track');
    var card  = track.querySelector('.explore-card, .home-video-card');
    var step  = card ? (card.offsetWidth + 12) * 3 : 660;
    track.scrollBy({ left: dir * step, behavior: 'smooth' });
}

/* ── Arrow show/hide ── */
document.querySelectorAll('.explore-track').forEach(function(track) {
    var wrap  = track.closest('.explore-row-track-wrap');
    if (!wrap) return;
    var left  = wrap.querySelector('.explore-arrow-left');
    var right = wrap.querySelector('.explore-arrow-right');
    function update() {
        if (left)  { left.style.opacity  = track.scrollLeft > 20 ? '1' : '0'; left.style.pointerEvents  = track.scrollLeft > 20 ? 'auto' : 'none'; }
        if (right) { var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 20; right.style.opacity = atEnd ? '0' : '1'; right.style.pointerEvents = atEnd ? 'none' : 'auto'; }
    }
    track.addEventListener('scroll', update, { passive: true });
    update();
});
</script>
