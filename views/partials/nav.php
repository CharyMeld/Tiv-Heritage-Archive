<?php
// Nigeria Heritage menu: only once the national section has published content,
// so visitors never land on an empty section.
require_once BASE_PATH . '/services/HeritagePublic.php';
$showNigeriaNav = HeritagePublic::hasPublishedContent();
?>
<style>
/* ============================================================
   MEGA MENU — Desktop only (≥ 1025 px)
   ============================================================ */
.nav-item-mega > .nav-link { display: flex; align-items: center; gap: .25rem; }
.nav-item-mega > .nav-link .mega-caret {
    display: inline-block; font-size: .65rem;
    transition: transform .2s; line-height: 1;
}
.nav-item-mega.mega-active > .nav-link .mega-caret { transform: rotate(180deg); }

.mega-panel {
    display: none;
    position: fixed;
    left: 0; right: 0;
    background: #fff;
    border-top: 3px solid #5C3A21;
    box-shadow: 0 8px 32px rgba(0,0,0,.15);
    z-index: 200;
    overflow-y: auto;
    max-height: calc(100vh - 120px);
}
.mega-panel.mega-panel--visible { display: block; animation: megaFadeIn .18s ease; }
@keyframes megaFadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.mega-inner { max-width: 1200px; margin: 0 auto; padding: 1.8rem 2rem; }
.mega-section-header { display: flex; align-items: center; gap: .6rem; padding-bottom: .9rem; margin-bottom: 1.2rem; border-bottom: 1px solid #e5e0d5; }
.mega-section-header-icon { font-size: 1.4rem; }
.mega-section-header-title { font-size: 1.25rem; font-weight: 700; color: #5C3A21; margin: 0; }
.mega-section-header-desc { font-size: .92rem; color: #7a6a5a; margin: 0 0 0 auto; max-width: 480px; text-align: right; }
.mega-grid { display: grid; gap: .8rem; }
.mega-grid-5 { grid-template-columns: repeat(5, 1fr); }
.mega-grid-4 { grid-template-columns: repeat(4, 1fr); }
.mega-grid-3 { grid-template-columns: repeat(3, 1fr); }
.mega-grid-2 { grid-template-columns: repeat(2, 1fr); }
.mega-item { display: flex; align-items: flex-start; gap: .8rem; padding: .85rem 1rem; border-radius: 10px; text-decoration: none; color: inherit; background: #faf8f5; border: 1px solid transparent; transition: background .15s, border-color .15s, transform .15s; }
.mega-item:hover { background: #fdf5e8; border-color: #C8A951; transform: translateY(-1px); text-decoration: none; color: inherit; }
.mega-item-icon { font-size: 1.5rem; flex-shrink: 0; line-height: 1; }
.mega-item-body { min-width: 0; }
.mega-item-label { font-weight: 600; color: #2d1b0e; font-size: 1rem; margin: 0 0 .2rem; white-space: nowrap; }
.mega-item-desc { font-size: .85rem; color: #7a6a5a; line-height: 1.4; margin: 0; }
.mega-item-coming { font-size: .68rem; color: #C8A951; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; display: block; margin-top: .25rem; }
.mega-archive-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
.mega-col-label { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9a8a7a; margin: 0 0 .6rem; }
.mega-suggestions-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .4rem; }
.mega-suggestions-item { display: flex; align-items: center; gap: .5rem; padding: .55rem .75rem; border-radius: 7px; text-decoration: none; color: #2d1b0e; font-size: .95rem; background: #faf8f5; border: 1px solid transparent; transition: background .15s, border-color .15s; }
.mega-suggestions-item:hover { background: #fdf5e8; border-color: #C8A951; text-decoration: none; color: #5C3A21; }

/* Panels are desktop-only; drawer handles mobile/tablet */
@media (max-width: 1024px) {
    .mega-panel, .mega-panel.mega-panel--visible { display: none !important; }
}

/* ============================================================
   MOBILE / TABLET ACCORDION DRAWER  (≤ 1024 px)
   ============================================================ */

/* Hidden on desktop */
@media (min-width: 1025px) {
    .mnav-overlay, .mnav-drawer { display: none !important; }
}

/* Dark backdrop */
.mnav-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 298;
    -webkit-tap-highlight-color: transparent;
}
.mnav-overlay.mnav--open { display: block; }

/* Sliding drawer panel */
.mnav-drawer {
    position: fixed;
    top: 0; left: -330px;
    width: 300px;
    max-width: 88vw;
    height: 100vh;
    height: 100dvh;
    background: #2e1a0a;
    z-index: 299;
    display: flex;
    flex-direction: column;
    transition: left .28s cubic-bezier(.4,0,.2,1), box-shadow .28s;
    overflow: hidden;
}
.mnav-drawer.mnav--open {
    left: 0;
    box-shadow: 6px 0 32px rgba(0,0,0,.55);
}

/* Drawer header bar */
.mnav-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .9rem 1.1rem;
    background: #5C3A21;
    border-bottom: 2px solid #C8A951;
    flex-shrink: 0;
}
.mnav-hdr-title {
    color: #C8A951;
    font-weight: 800;
    font-size: .85rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin: 0;
}
.mnav-x {
    background: none; border: none;
    color: rgba(255,255,255,.85);
    font-size: 1.5rem; line-height: 1;
    cursor: pointer;
    padding: .15rem .4rem;
    border-radius: 4px;
    transition: background .15s;
}
.mnav-x:hover { background: rgba(255,255,255,.18); color: #fff; }

/* Scrollable item list */
.mnav-list {
    list-style: none; margin: 0;
    padding: .4rem 0 1.5rem;
    overflow-y: auto; flex: 1;
    -webkit-overflow-scrolling: touch;
}
.mnav-list::-webkit-scrollbar { width: 4px; }
.mnav-list::-webkit-scrollbar-track { background: rgba(0,0,0,.2); }
.mnav-list::-webkit-scrollbar-thumb { background: rgba(200,169,81,.4); border-radius: 2px; }

/* Plain link (Home, References, etc.) */
.mnav-plain {
    display: block;
    padding: .78rem 1.1rem;
    color: rgba(255,255,255,.85);
    font-size: .875rem; font-weight: 600;
    letter-spacing: .05em; text-transform: uppercase;
    border-left: 3px solid transparent;
    text-decoration: none;
    transition: color .15s, border-color .15s, background .15s;
}
.mnav-plain:hover, .mnav-plain.mnav-active {
    color: #C8A951;
    border-left-color: #C8A951;
    background: rgba(200,169,81,.07);
    text-decoration: none;
}

/* Accordion toggle button */
.mnav-btn {
    display: flex; align-items: center; justify-content: space-between;
    width: 100%; padding: .78rem 1.1rem;
    background: none; border: none;
    border-left: 3px solid transparent;
    color: rgba(255,255,255,.85);
    font-size: .875rem; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    cursor: pointer; text-align: left;
    transition: color .15s, border-color .15s, background .15s;
}
.mnav-btn:hover,
.mnav-btn[aria-expanded="true"] {
    color: #C8A951;
    border-left-color: #C8A951;
    background: rgba(200,169,81,.07);
}
.mnav-arr {
    font-size: .58rem; line-height: 1;
    flex-shrink: 0; margin-left: .5rem;
    transition: transform .2s;
}
.mnav-btn[aria-expanded="true"] .mnav-arr { transform: rotate(180deg); }

/* Collapsible submenu — uses max-height animation */
.mnav-sub {
    list-style: none; margin: 0 .75rem .2rem 1.4rem;
    padding: 0; border-left: 2px solid rgba(200,169,81,.45);
    background: rgba(0,0,0,.22); border-radius: 0 0 5px 0;
    overflow: hidden;
    max-height: 0;
    transition: max-height .3s ease;
}
.mnav-sub.mnav-sub--open { max-height: 800px; }
.mnav-sub li a {
    display: block; padding: .58rem 1rem;
    color: rgba(255,255,255,.72);
    font-size: .86rem; text-decoration: none;
    transition: color .15s, padding-left .15s, background .15s;
}
.mnav-sub li a:hover {
    color: #C8A951; padding-left: 1.3rem;
    background: rgba(200,169,81,.06);
    text-decoration: none;
}
.mni { margin-right: .35rem; font-style: normal; opacity: .85; }

/* Thin rule between sections */
.mnav-sep { height: 1px; background: rgba(255,255,255,.09); margin: .3rem 1.1rem; }
</style>

<!-- ── Mobile / Tablet Accordion Drawer (hidden ≥ 1025px) ── -->
<div class="mnav-overlay" id="mnavOverlay"></div>
<nav class="mnav-drawer" id="mnavDrawer" aria-label="Site navigation" aria-hidden="true">
    <div class="mnav-hdr">
        <p class="mnav-hdr-title">&#9776; Menu</p>
        <button class="mnav-x" id="mnavClose" aria-label="Close menu">&times;</button>
    </div>
    <ul class="mnav-list">

        <li><a href="<?= url('/') ?>" class="mnav-plain">&#127968; Home</a></li>
        <li><div class="mnav-sep"></div></li>

        <!-- LANGUAGE -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-lang">
                <span>&#128218; Language</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-lang">
                <li><a href="<?= url(section_path('words')) ?>"><i class="mni">&#128218;</i>Dictionary</a></li>
                <li><a href="<?= url('language/alphabet') ?>"><i class="mni">&#127279;</i>Alphabet</a></li>
                <li><a href="<?= url('language/grammar') ?>"><i class="mni">&#128220;</i>Grammar</a></li>
                <li><a href="<?= url('learn') ?>"><i class="mni">&#127979;</i>Lessons</a></li>
                <li><a href="<?= url('translate') ?>"><i class="mni">&#127760;</i>Translation</a></li>
            </ul>
        </li>

        <!-- LITERATURE -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-lit">
                <span>&#128221; Literature</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-lit">
                <li><a href="<?= url('literature/folktales') ?>"><i class="mni">&#127919;</i>Folktales</a></li>
                <li><a href="<?= url(section_path('proverbs')) ?>"><i class="mni">&#128221;</i>Proverbs</a></li>
                <li><a href="<?= url('literature/stories') ?>"><i class="mni">&#128196;</i>Stories</a></li>
                <li><a href="<?= url('literature/poems') ?>"><i class="mni">&#128145;</i>Poems</a></li>
            </ul>
        </li>

        <!-- CULTURE -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-cult">
                <span>&#127981; Culture</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-cult">
                <li><a href="<?= url('culture/traditions') ?>"><i class="mni">&#127981;</i>Traditions</a></li>
                <li><a href="<?= url(section_path('festivals')) ?>"><i class="mni">&#127881;</i>Festivals</a></li>
                <li><a href="<?= url('culture/attire') ?>"><i class="mni">&#128255;</i>Attire</a></li>
                <li><a href="<?= url('culture/marriage-customs') ?>"><i class="mni">&#128149;</i>Marriage Customs</a></li>
            </ul>
        </li>

        <!-- HISTORY -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-hist">
                <span>&#127758; History</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-hist">
                <li><a href="<?= url('history/origins') ?>"><i class="mni">&#127758;</i>Origins</a></li>
                <li><a href="<?= url('history/migration') ?>"><i class="mni">&#128667;</i>Migration</a></li>
                <li><a href="<?= url('historical-figures') ?>"><i class="mni">&#129332;</i>Historical Figures</a></li>
                <li><a href="<?= url('history/timeline') ?>"><i class="mni">&#128337;</i>Timeline</a></li>
            </ul>
        </li>

        <!-- ARCHIVE -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-arch">
                <span>&#128196; Archive</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-arch">
                <li><a href="<?= url('archive/documents') ?>"><i class="mni">&#128196;</i>Documents</a></li>
                <li><a href="<?= url('archive/audio') ?>"><i class="mni">&#127911;</i>Audio Recordings</a></li>
                <li><a href="<?= url('learn') ?>"><i class="mni">&#127909;</i>Videos</a></li>
                <li><a href="<?= url('archive/publications') ?>"><i class="mni">&#128214;</i>Research Publications</a></li>
                <li><a href="<?= url(section_path('words')) ?>"><i class="mni">&#128172;</i>Dictionary</a></li>
                <li><a href="<?= url(section_path('names')) ?>"><i class="mni">&#128100;</i>Tiv Names</a></li>
                <li><a href="<?= url(section_path('plants')) ?>"><i class="mni">&#127807;</i>Plants</a></li>
                <li><a href="<?= url(section_path('foods')) ?>"><i class="mni">&#127858;</i>Foods</a></li>
                <li><a href="<?= url(section_path('animals')) ?>"><i class="mni">&#128062;</i>Animals</a></li>
                <li><a href="<?= url('archive') ?>"><i class="mni">&#128269;</i>Browse All</a></li>
            </ul>
        </li>

<?php if ($showNigeriaNav): ?>
        <!-- NIGERIA HERITAGE -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-ng">
                <span>&#127475;&#127468; Nigeria Heritage</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-ng">
                <li><a href="<?= nigeria_url() ?>"><i class="mni">&#127475;&#127468;</i>Explore Nigeria</a></li>
                <li><a href="<?= nigeria_url('states') ?>"><i class="mni">&#127963;</i>States &amp; FCT</a></li>
                <li><a href="<?= nigeria_url('ethnic-groups') ?>"><i class="mni">&#128101;</i>Ethnic Groups</a></li>
                <li><a href="<?= nigeria_url('languages') ?>"><i class="mni">&#128483;</i>Languages</a></li>
                <li><a href="<?= nigeria_url('events') ?>"><i class="mni">&#128337;</i>History &amp; Events</a></li>
                <li><a href="<?= nigeria_url('people') ?>"><i class="mni">&#129332;</i>People</a></li>
                <li><a href="<?= nigeria_url('places') ?>"><i class="mni">&#128205;</i>Places &amp; Sites</a></li>
                <li><a href="<?= nigeria_url('kingdoms') ?>"><i class="mni">&#128081;</i>Kingdoms &amp; Institutions</a></li>
                <li><a href="<?= nigeria_url('culture') ?>"><i class="mni">&#127917;</i>Culture</a></li>
            </ul>
        </li>
<?php endif; ?>
        <!-- COMMUNITY -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-comm">
                <span>&#128101; Community</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-comm">
                <li><a href="<?= url('community') ?>"><i class="mni">&#9997;</i>Contributors</a></li>
                <li><a href="<?= url('community') ?>#researchers"><i class="mni">&#128300;</i>Researchers</a></li>
                <li><a href="<?= url('community/join') ?>"><i class="mni">&#43;</i>Join the Community</a></li>
                <li><a href="<?= url('community') ?>"><i class="mni">&#128101;</i>Community Directory</a></li>
            </ul>
        </li>

        <!-- FEEDBACK -->
        <li>
            <button class="mnav-btn" aria-expanded="false" aria-controls="ms-feed">
                <span>&#128161; Feedback</span>
                <span class="mnav-arr">&#9660;</span>
            </button>
            <ul class="mnav-sub" id="ms-feed">
                <li><a href="<?= url('suggestions') ?>?category=general_suggestion"><i class="mni">&#128161;</i>General Suggestions</a></li>
                <li><a href="<?= url('suggestions') ?>?category=website_improvement"><i class="mni">&#128187;</i>Website Improvements</a></li>
                <li><a href="<?= url('suggestions') ?>?category=feature_request"><i class="mni">&#10024;</i>Feature Requests</a></li>
                <li><a href="<?= url('suggestions') ?>?category=content_correction"><i class="mni">&#9999;</i>Content Corrections</a></li>
                <li><a href="<?= url('suggestions') ?>?category=technical_issue"><i class="mni">&#9881;</i>Technical Issues</a></li>
                <li><a href="<?= url('suggestions') ?>?category=other"><i class="mni">&#128172;</i>Other Feedback</a></li>
            </ul>
        </li>

        <li><div class="mnav-sep"></div></li>
        <li><a href="<?= url('references') ?>" class="mnav-plain">&#128214; References</a></li>
        <li><a href="<?= url('about') ?>" class="mnav-plain">&#8505; About</a></li>

        <?php if (is_logged_in()): ?>
            <li><div class="mnav-sep"></div></li>
            <li><a href="<?= url('profile') ?>" class="mnav-plain">&#128100; Profile</a></li>
            <?php if (is_moderator()): ?>
                <li><a href="<?= url('admin') ?>" class="mnav-plain">&#9881; Admin</a></li>
            <?php endif; ?>
            <li><a href="<?= url('logout') ?>" class="mnav-plain">&#10006; Logout</a></li>
        <?php else: ?>
            <li><div class="mnav-sep"></div></li>
            <li><a href="<?= url('login') ?>" class="mnav-plain">&#128274; Login</a></li>
        <?php endif; ?>

    </ul>
</nav>

<!-- ── Desktop Mega-Menu Navigation (≥ 1025px) ── -->
<nav class="nav" id="navMenu">
    <div class="container">
        <ul class="nav-list">

            <!-- Home -->
            <li><a href="<?= url('/') ?>" class="nav-link">Home</a></li>

            <!-- ── LANGUAGE ─────────────────────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('language') ?>" class="nav-link">
                    Language <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-grid mega-grid-5">
                            <a href="<?= url(section_path('words')) ?>" class="mega-item">
                                <span class="mega-item-icon">&#128218;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Dictionary</p>
                                    <p class="mega-item-desc">Tiv words, meanings &amp; parts of speech</p>
                                </div>
                            </a>
                            <a href="<?= url('language/alphabet') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127279;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Alphabet</p>
                                    <p class="mega-item-desc">Consonants, vowels &amp; tonal markers</p>
                                </div>
                            </a>
                            <a href="<?= url('language/grammar') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128220;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Grammar</p>
                                    <p class="mega-item-desc">Nouns, pronouns, verbs &amp; sentence structure</p>
                                </div>
                            </a>
                            <a href="<?= url('learn') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127979;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Lessons</p>
                                    <p class="mega-item-desc">Structured lessons beginner to advanced</p>
                                </div>
                            </a>
                            <a href="<?= url('translate') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127760;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Translation</p>
                                    <p class="mega-item-desc">English ↔ Tiv translation engine</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- ── LITERATURE ────────────────────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('literature') ?>" class="nav-link">
                    Literature <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-grid mega-grid-4">
                            <a href="<?= url('literature/folktales') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127919;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Folktales</p>
                                    <p class="mega-item-desc">Traditional stories preserving cultural values</p>
                                </div>
                            </a>
                            <a href="<?= url(section_path('proverbs')) ?>" class="mega-item">
                                <span class="mega-item-icon">&#128221;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Proverbs</p>
                                    <p class="mega-item-desc">Ancient wisdom passed through generations</p>
                                </div>
                            </a>
                            <a href="<?= url('literature/stories') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128196;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Stories</p>
                                    <p class="mega-item-desc">Historical &amp; contemporary Tiv narratives</p>
                                </div>
                            </a>
                            <a href="<?= url('literature/poems') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128145;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Poems</p>
                                    <p class="mega-item-desc">Oral and written Tiv poetry</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- ── CULTURE ─────────────────────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('culture') ?>" class="nav-link">
                    Culture <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-grid mega-grid-4">
                            <a href="<?= url('culture/traditions') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127981;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Traditions</p>
                                    <p class="mega-item-desc">Cultural practices &amp; customs across generations</p>
                                </div>
                            </a>
                            <a href="<?= url(section_path('festivals')) ?>" class="mega-item">
                                <span class="mega-item-icon">&#127881;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Festivals</p>
                                    <p class="mega-item-desc">Harvest celebrations &amp; rites of passage</p>
                                </div>
                            </a>
                            <a href="<?= url('culture/attire') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128255;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Attire</p>
                                    <p class="mega-item-desc">Traditional clothing &amp; ceremonial dress</p>
                                </div>
                            </a>
                            <a href="<?= url('culture/marriage-customs') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128149;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Marriage Customs</p>
                                    <p class="mega-item-desc">Bride price, ceremonies &amp; family rites</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- ── HISTORY ─────────────────────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('history') ?>" class="nav-link">
                    History <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-grid mega-grid-4">
                            <a href="<?= url('history/origins') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127758;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Origins</p>
                                    <p class="mega-item-desc">Oral &amp; documented accounts of Tiv roots</p>
                                </div>
                            </a>
                            <a href="<?= url('history/migration') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128667;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Migration</p>
                                    <p class="mega-item-desc">Historical movements across the Benue Valley</p>
                                </div>
                            </a>
                            <a href="<?= url('historical-figures') ?>" class="mega-item">
                                <span class="mega-item-icon">&#129332;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Historical Figures</p>
                                    <p class="mega-item-desc">Leaders, warriors &amp; scholars of Tiv history</p>
                                </div>
                            </a>
                            <a href="<?= url('history/timeline') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128337;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Timeline</p>
                                    <p class="mega-item-desc">Chronological record of key Tiv milestones</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- ── ARCHIVE ─────────────────────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('archive') ?>" class="nav-link">
                    Archive <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-archive-wrap">
                            <div>
                                <p class="mega-col-label">Archive Collections</p>
                                <div class="mega-grid mega-grid-2">
                                    <a href="<?= url('archive/documents') ?>" class="mega-item">
                                        <span class="mega-item-icon">&#128196;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Documents</p>
                                            <p class="mega-item-desc">Historical manuscripts &amp; written records</p>
                                        </div>
                                    </a>
                                    <a href="<?= url('archive/audio') ?>" class="mega-item">
                                        <span class="mega-item-icon">&#127911;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Audio Recordings</p>
                                            <p class="mega-item-desc">Songs, speeches &amp; oral traditions</p>
                                        </div>
                                    </a>
                                    <a href="<?= url('learn') ?>" class="mega-item">
                                        <span class="mega-item-icon">&#127909;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Videos</p>
                                            <p class="mega-item-desc">Learning videos &amp; cultural recordings</p>
                                        </div>
                                    </a>
                                    <a href="<?= url('archive/publications') ?>" class="mega-item">
                                        <span class="mega-item-icon">&#128214;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Research Publications</p>
                                            <p class="mega-item-desc">Academic &amp; community research</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <p class="mega-col-label">Browse Content Library</p>
                                <div class="mega-grid mega-grid-2">
                                    <a href="<?= url(section_path('words')) ?>" class="mega-item">
                                        <span class="mega-item-icon">&#128172;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Dictionary</p>
                                            <p class="mega-item-desc">Tiv words &amp; definitions</p>
                                        </div>
                                    </a>
                                    <a href="<?= url(section_path('names')) ?>" class="mega-item">
                                        <span class="mega-item-icon">&#128100;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Tiv Names</p>
                                            <p class="mega-item-desc">Names &amp; their meanings</p>
                                        </div>
                                    </a>
                                    <a href="<?= url(section_path('plants')) ?>" class="mega-item">
                                        <span class="mega-item-icon">&#127807;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Plants</p>
                                            <p class="mega-item-desc">Medicinal &amp; cultural plants</p>
                                        </div>
                                    </a>
                                    <a href="<?= url(section_path('foods')) ?>" class="mega-item">
                                        <span class="mega-item-icon">&#127858;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Foods</p>
                                            <p class="mega-item-desc">Traditional cuisine &amp; recipes</p>
                                        </div>
                                    </a>
                                    <a href="<?= url(section_path('animals')) ?>" class="mega-item">
                                        <span class="mega-item-icon">&#128062;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Animals</p>
                                            <p class="mega-item-desc">Animals in Tiv culture</p>
                                        </div>
                                    </a>
                                    <a href="<?= url('archive') ?>" class="mega-item" style="background:#fdf5e8;border-color:#C8A951;">
                                        <span class="mega-item-icon">&#128269;</span>
                                        <div class="mega-item-body">
                                            <p class="mega-item-label">Browse All</p>
                                            <p class="mega-item-desc">View the full archive</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

<?php if ($showNigeriaNav): ?>
            <!-- ── NIGERIA HERITAGE (shown once the national section has published records) ── -->
            <style>
            /* One more top-level item: keep the bar on one line on mid-width desktops. */
            @media (min-width: 1025px) and (max-width: 1359px) {
                .nav-list > li > .nav-link { padding-left: .45rem; padding-right: .45rem; }
                .ng-nav-long { display: none; }
            }
            @media (min-width: 1360px) { .ng-nav-short { display: none; } }
            </style>
            <li class="nav-item-mega">
                <a href="<?= nigeria_url() ?>" class="nav-link">
                    <span class="ng-nav-long">Nigeria Heritage</span><span class="ng-nav-short">Nigeria</span> <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-grid mega-grid-4">
                            <a href="<?= nigeria_url('states') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127963;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">States &amp; FCT</p>
                                    <p class="mega-item-desc">States, LGAs &amp; how they were formed</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('ethnic-groups') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128101;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Ethnic Groups</p>
                                    <p class="mega-item-desc">Nigeria’s peoples and their heritage</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('languages') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128483;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Languages</p>
                                    <p class="mega-item-desc">Languages &amp; dialects of Nigeria</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('events') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128337;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">History &amp; Events</p>
                                    <p class="mega-item-desc">Events in Nigeria’s history</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('people') ?>" class="mega-item">
                                <span class="mega-item-icon">&#129332;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">People</p>
                                    <p class="mega-item-desc">Historical &amp; cultural figures</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('places') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128205;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Places &amp; Sites</p>
                                    <p class="mega-item-desc">Towns, heritage sites &amp; museums</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('kingdoms') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128081;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Kingdoms &amp; Institutions</p>
                                    <p class="mega-item-desc">Kingdoms, emirates &amp; chiefdoms</p>
                                </div>
                            </a>
                            <a href="<?= nigeria_url('culture') ?>" class="mega-item">
                                <span class="mega-item-icon">&#127917;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Culture</p>
                                    <p class="mega-item-desc">Festivals, food, dress, music &amp; crafts</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>
<?php endif; ?>
            <!-- ── COMMUNITY ───────────────────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('community') ?>" class="nav-link">
                    Community <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-grid mega-grid-4">
                            <a href="<?= url('community') ?>" class="mega-item">
                                <span class="mega-item-icon">&#9997;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Contributors</p>
                                    <p class="mega-item-desc">Members who share cultural knowledge</p>
                                </div>
                            </a>
                            <a href="<?= url('community') ?>#researchers" class="mega-item">
                                <span class="mega-item-icon">&#128300;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Researchers</p>
                                    <p class="mega-item-desc">Academic researchers &amp; scholars</p>
                                </div>
                            </a>
                            <a href="<?= url('community/join') ?>" class="mega-item" style="background:#fdf5e8;border-color:#C8A951;">
                                <span class="mega-item-icon">&#43;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Join the Community</p>
                                    <p class="mega-item-desc">Apply as contributor or researcher</p>
                                </div>
                            </a>
                            <a href="<?= url('community') ?>" class="mega-item">
                                <span class="mega-item-icon">&#128101;</span>
                                <div class="mega-item-body">
                                    <p class="mega-item-label">Community Directory</p>
                                    <p class="mega-item-desc">Browse all approved members</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- ── SUGGESTIONS & FEEDBACK ─────────────────────────── -->
            <li class="nav-item-mega">
                <a href="<?= url('suggestions') ?>" class="nav-link">
                    Feedback <span class="mega-caret">&#9660;</span>
                </a>
                <div class="mega-panel">
                    <div class="mega-inner">
                        <div class="mega-suggestions-grid">
                            <?php
                            $suggestionLinks = [
                                ['general_suggestion',    '&#128161;', 'General Suggestions'],
                                ['website_improvement',   '&#128187;', 'Website Improvements'],
                                ['feature_request',       '&#10024;',  'Feature Requests'],
                                ['content_correction',    '&#9999;',   'Content Corrections'],
                                ['dictionary_correction', '&#128218;', 'Dictionary Corrections'],
                                ['translation_suggestion','&#127760;', 'Translation Corrections'],
                                ['historical_information','&#128336;', 'Historical Contributions'],
                                ['cultural_information',  '&#127981;', 'Cultural Contributions'],
                                ['research_contribution', '&#128300;', 'Research Contributions'],
                                ['partnership_request',   '&#129309;', 'Partnership Requests'],
                                ['technical_issue',       '&#9881;',   'Technical Issues'],
                                ['other',                 '&#128172;', 'Other Feedback'],
                            ];
                            foreach ($suggestionLinks as [$cat, $icon, $label]):
                            ?>
                            <a href="<?= url('suggestions') ?>?category=<?= $cat ?>" class="mega-suggestions-item">
                                <span><?= $icon ?></span>
                                <span><?= $label ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </li>

            <!-- ── Standalone items ────────────────────────────────── -->
            <li><a href="<?= url('references') ?>" class="nav-link">References</a></li>
            <li><a href="<?= url('about') ?>" class="nav-link">About</a></li>

            <?php if (is_logged_in()): ?>
                <li><a href="<?= url('profile') ?>" class="nav-link">Profile</a></li>
                <?php if (is_moderator()): ?>
                    <li><a href="<?= url('admin') ?>" class="nav-link">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= url('logout') ?>" class="nav-link">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= url('login') ?>" class="nav-link">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
(function () {
    var BP = 1025; /* desktop breakpoint */

    function isDesktop() { return window.innerWidth >= BP; }

    /* ── MOBILE / TABLET DRAWER ── */
    var overlay  = document.getElementById('mnavOverlay');
    var drawer   = document.getElementById('mnavDrawer');
    var closeBtn = document.getElementById('mnavClose');
    var toggle   = document.getElementById('menuToggle');

    function openDrawer() {
        if (!drawer) return;
        overlay.classList.add('mnav--open');
        drawer.classList.add('mnav--open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (toggle) { toggle.classList.add('active'); toggle.setAttribute('aria-expanded', 'true'); }
    }

    function closeDrawer() {
        if (!drawer) return;
        overlay.classList.remove('mnav--open');
        drawer.classList.remove('mnav--open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (toggle) { toggle.classList.remove('active'); toggle.setAttribute('aria-expanded', 'false'); }
    }

    if (toggle) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (isDesktop()) return;
            drawer && drawer.classList.contains('mnav--open') ? closeDrawer() : openDrawer();
        });
    }
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay)  overlay.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });
    window.addEventListener('resize', function () {
        if (isDesktop()) closeDrawer();
    });

    /* Close drawer on any link click inside it */
    if (drawer) {
        drawer.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', closeDrawer);
        });
    }

    /* ── ACCORDION ── */
    document.querySelectorAll('.mnav-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('aria-controls');
            var sub = document.getElementById(targetId);
            var isOpen = btn.getAttribute('aria-expanded') === 'true';

            /* collapse all others */
            document.querySelectorAll('.mnav-btn[aria-expanded="true"]').forEach(function (b) {
                if (b !== btn) {
                    b.setAttribute('aria-expanded', 'false');
                    var other = document.getElementById(b.getAttribute('aria-controls'));
                    if (other) other.classList.remove('mnav-sub--open');
                }
            });

            /* toggle this one */
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            if (sub) sub.classList.toggle('mnav-sub--open', !isOpen);
        });
    });

    /* ── DESKTOP MEGA MENU ── */
    function navBottom() {
        var nav = document.getElementById('navMenu');
        return nav ? nav.getBoundingClientRect().bottom : 0;
    }

    function positionPanel(panel) {
        if (isDesktop()) panel.style.top = navBottom() + 'px';
    }

    function closeAll() {
        document.querySelectorAll('.nav-item-mega.mega-active').forEach(function (li) {
            li.classList.remove('mega-active');
            var p = li.querySelector('.mega-panel');
            if (p) p.classList.remove('mega-panel--visible');
        });
    }

    function openPanel(li) {
        var panel = li.querySelector('.mega-panel');
        if (!panel) return;
        positionPanel(panel);
        li.classList.add('mega-active');
        panel.classList.add('mega-panel--visible');
    }

    document.querySelectorAll('.nav-item-mega').forEach(function (li) {
        li.addEventListener('mouseenter', function () { if (isDesktop()) { closeAll(); openPanel(li); } });
        li.addEventListener('mouseleave', function () { if (isDesktop()) closeAll(); });
    });

    document.querySelectorAll('.nav-item-mega > .nav-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!isDesktop()) return;
            var li = this.closest('.nav-item-mega');
            var isOpen = li.classList.contains('mega-active');
            closeAll();
            if (!isOpen) { e.preventDefault(); openPanel(li); }
        });
    });

    window.addEventListener('scroll', function () {
        if (!isDesktop()) return;
        var open = document.querySelector('.nav-item-mega.mega-active');
        if (open) { var p = open.querySelector('.mega-panel'); if (p) p.style.top = navBottom() + 'px'; }
    }, { passive: true });

    window.addEventListener('resize', function () {
        var open = document.querySelector('.nav-item-mega.mega-active');
        if (open) { var p = open.querySelector('.mega-panel'); if (p) positionPanel(p); }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.nav-item-mega')) closeAll();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });
})();
</script>
