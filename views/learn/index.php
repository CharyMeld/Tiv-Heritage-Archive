<?php
/* Use the DB total from pagination, not just the current page count */
$totalVids   = $pagination['total'] ?? count($videos ?? []);
$activeDiff  = $difficulty ?? '';
$activeCat   = $category   ?? '';
?>
<?php if (is_moderator() && $totalVids > count($videos ?? []) * 2): ?>
<div style="background:#fff3cd;border-left:4px solid #f59e0b;padding:.75rem 1.25rem;font-size:.85rem;color:#856404;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
    <span>&#9888; The database has <strong><?= $totalVids ?></strong> active video records but your YouTube channel may have fewer. Remove invalid or duplicate entries to keep this count accurate.</span>
    <a href="<?= url('admin/content/videos') ?>" style="color:#5C3A21;font-weight:700;text-decoration:none;white-space:nowrap;">&#9881; Manage Videos &rarr;</a>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     CHANNEL BANNER
════════════════════════════════════════════════════════ -->
<div class="lch-banner">
    <div class="lch-banner-noise"></div>
    <div class="container lch-banner-inner">
        <div class="lch-avatar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="36" height="36">
                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
            </svg>
        </div>
        <div class="lch-identity">
            <div class="lch-eyebrow">&#127909; YouTube Channel</div>
            <h1 class="lch-title">Learn Tiv Language</h1>
            <p class="lch-sub">Video lessons from beginner to advanced &mdash; learn at your own pace</p>
            <div class="lch-meta-row">
                <span class="lch-stat">
                    <strong><?= $totalVids ?></strong> Lessons
                </span>
                <span class="lch-dot">&#183;</span>
                <span class="lch-stat">3 Levels</span>
                <span class="lch-dot">&#183;</span>
                <span class="lch-stat"><?= count($categories ?? []) ?> Topics</span>
                <a href="https://www.youtube.com/@TivHeritageArchive" target="_blank" rel="noopener" class="lch-subscribe-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                    </svg>
                    Subscribe on YouTube
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     LEVEL SELECTOR STRIP
════════════════════════════════════════════════════════ -->
<div class="lch-levels-strip">
    <div class="container">
        <div class="lch-level-row">

            <a href="<?= url('learn' . ($activeDiff === 'beginner' ? '' : '?difficulty=beginner')) ?>"
               class="lch-lvl-card <?= $activeDiff === 'beginner' ? 'active beginner' : '' ?>">
                <div class="lch-lvl-glow"></div>
                <div class="lch-lvl-icon">&#127775;</div>
                <div class="lch-lvl-body">
                    <span class="lch-lvl-name">Beginner</span>
                    <span class="lch-lvl-desc">Start from scratch</span>
                </div>
                <?php if ($activeDiff === 'beginner'): ?>
                <div class="lch-lvl-active-dot"></div>
                <?php endif; ?>
            </a>

            <a href="<?= url('learn' . ($activeDiff === 'intermediate' ? '' : '?difficulty=intermediate')) ?>"
               class="lch-lvl-card <?= $activeDiff === 'intermediate' ? 'active intermediate' : '' ?>">
                <div class="lch-lvl-glow"></div>
                <div class="lch-lvl-icon">&#128218;</div>
                <div class="lch-lvl-body">
                    <span class="lch-lvl-name">Intermediate</span>
                    <span class="lch-lvl-desc">Build on basics</span>
                </div>
                <?php if ($activeDiff === 'intermediate'): ?>
                <div class="lch-lvl-active-dot"></div>
                <?php endif; ?>
            </a>

            <a href="<?= url('learn' . ($activeDiff === 'advanced' ? '' : '?difficulty=advanced')) ?>"
               class="lch-lvl-card <?= $activeDiff === 'advanced' ? 'active advanced' : '' ?>">
                <div class="lch-lvl-glow"></div>
                <div class="lch-lvl-icon">&#127941;</div>
                <div class="lch-lvl-body">
                    <span class="lch-lvl-name">Advanced</span>
                    <span class="lch-lvl-desc">Master fluency</span>
                </div>
                <?php if ($activeDiff === 'advanced'): ?>
                <div class="lch-lvl-active-dot"></div>
                <?php endif; ?>
            </a>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SEARCH + FILTER BAR
════════════════════════════════════════════════════════ -->
<div class="lch-filter-section">
    <div class="container">

        <!-- Live Search -->
        <div class="lch-search-wrap">
            <svg class="lch-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input id="learnSearch" type="search" class="lch-search-input"
                   placeholder="Search lessons by title, topic or level..."
                   autocomplete="off" spellcheck="false">
            <button id="learnClear" class="lch-search-clear" aria-label="Clear search" style="display:none">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
            <span id="learnCount" class="lch-search-count"></span>
        </div>

        <!-- Category Pills -->
        <?php if (!empty($categories)): ?>
        <div class="filter-pills lch-cat-pills">
            <a href="<?= url('learn' . ($activeDiff ? '?difficulty='.$activeDiff : '')) ?>"
               class="filter-pill <?= empty($activeCat) ? 'active' : '' ?>">All Topics</a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= url('learn?category='.urlencode($cat) . ($activeDiff ? '&difficulty='.$activeDiff : '')) ?>"
               class="filter-pill <?= $activeCat === $cat ? 'active' : '' ?>"><?= e($cat) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     VIDEO GRID  (wrapped in subscribe-gate shell)
════════════════════════════════════════════════════════ -->
<div class="lch-videos-section">
    <div class="container">

        <?php if (empty($videos)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#127909;</div>
            <h3>No lessons found</h3>
            <p>Check back soon &mdash; new video lessons are added regularly.</p>
            <a href="<?= url('learn') ?>" class="btn btn-primary">View All Lessons</a>
        </div>

        <?php else: ?>

        <!-- ── Subscribe Gate overlay ──────────────────── -->
        <div class="lch-gate" id="lchGate">
            <div class="lch-gate-card">
                <!-- Channel avatar -->
                <div class="lch-gate-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40">
                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                    </svg>
                </div>

                <!-- Lock icon -->
                <div class="lch-gate-lock">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>

                <h2 class="lch-gate-title">Subscribe to Unlock Lessons</h2>
                <p class="lch-gate-sub">
                    Join our YouTube channel to get free access to all
                    <strong>Tiv language lessons</strong> &mdash;
                    from beginner greetings to advanced conversation.
                </p>

                <!-- Perks list -->
                <ul class="lch-gate-perks">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Access all video lessons — Beginner to Advanced
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Get notified when new lessons are published
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Support the preservation of Tiv language &amp; culture
                    </li>
                </ul>

                <!-- Subscribe CTA -->
                <a href="https://www.youtube.com/@TivHeritageArchive?sub_confirmation=1"
                   target="_blank" rel="noopener"
                   class="lch-gate-subscribe-btn"
                   id="lchYtBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                    </svg>
                    Subscribe on YouTube — It&rsquo;s Free
                </a>

                <!-- Confirm button -->
                <button class="lch-gate-confirm-btn" id="lchConfirm">
                    &#10003;&nbsp; I&rsquo;ve subscribed &mdash; Watch lessons now
                </button>

                <p class="lch-gate-note">Already subscribed? Click the button above to unlock.</p>
            </div>

            <!-- Blurred preview of videos behind the gate -->
            <div class="lch-gate-preview" aria-hidden="true">
                <?php foreach (array_slice($videos, 0, 8) as $pv): ?>
                <div class="lch-gate-preview-card">
                    <img src="https://img.youtube.com/vi/<?= e($pv['youtube_id']) ?>/mqdefault.jpg"
                         alt="" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- ── End Gate ──────────────────────────────── -->

        <!-- Video grid (hidden until subscribed) -->
        <div id="lchContent" style="display:none">

            <div class="lch-video-grid" id="learnGrid">
                <?php $lchAdShown = false; $lchIdx = 0; foreach ($videos as $v): $lchIdx++; ?>
                <div class="lch-vcard"
                     data-title="<?= strtolower(htmlspecialchars($v['title'], ENT_QUOTES)) ?>"
                     data-category="<?= strtolower(htmlspecialchars($v['category'] ?? '', ENT_QUOTES)) ?>"
                     data-difficulty="<?= strtolower(htmlspecialchars($v['difficulty'] ?? '', ENT_QUOTES)) ?>">

                    <a href="<?= url('learn/' . $v['id']) ?>" class="lch-vcard-thumb-link">
                        <div class="lch-vcard-thumb">
                            <img src="https://img.youtube.com/vi/<?= e($v['youtube_id']) ?>/mqdefault.jpg"
                                 alt="<?= e($v['title']) ?>" loading="lazy">
                            <div class="lch-play-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="white">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </div>
                            <?php if (!empty($v['duration'])): ?>
                            <span class="lch-vcard-dur"><?= e($v['duration']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($v['difficulty'])): ?>
                            <span class="lch-vcard-lvl difficulty-<?= e($v['difficulty']) ?>">
                                <?= $v['difficulty'] === 'beginner' ? '🌟' : ($v['difficulty'] === 'intermediate' ? '📚' : '🏅') ?>
                                <?= ucfirst(e($v['difficulty'])) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="lch-vcard-info">
                        <?php if (!empty($v['category'])): ?>
                        <span class="lch-vcard-cat"><?= e($v['category']) ?></span>
                        <?php endif; ?>
                        <a href="<?= url('learn/' . $v['id']) ?>" class="lch-vcard-title">
                            <?= e($v['title']) ?>
                        </a>
                        <?php if (!empty($v['description'])): ?>
                        <p class="lch-vcard-desc"><?= e(mb_substr($v['description'], 0, 85)) ?>…</p>
                        <?php endif; ?>
                        <?php if (!empty($v['view_count'])): ?>
                        <span class="lch-vcard-views">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <?= number_format($v['view_count']) ?> views
                        </span>
                        <?php endif; ?>
                    </div>

                </div>
                <?php if (ads_on() && $lchIdx === 4 && !$lchAdShown): $lchAdShown = true; ?>
                <div style="grid-column: 1 / -1;">
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
                <?php endforeach; ?>
            </div>

            <div id="learnNoResults" class="lch-no-results" style="display:none">
                <div class="lch-no-results-icon">&#128269;</div>
                <h3>No lessons match your search</h3>
                <p>Try a different keyword, or <button class="lch-reset-btn" id="learnReset">clear the search</button> to browse all lessons.</p>
            </div>

            <?= pagination($pagination ?? ['total_pages'=>1,'current_page'=>1,'has_prev'=>false,'has_next'=>false], url('learn')) ?>

        </div><!-- end #lchContent -->

        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var STORAGE_KEY = 'tiv_yt_subscribed';
    var gate        = document.getElementById('lchGate');
    var content     = document.getElementById('lchContent');
    var confirmBtn  = document.getElementById('lchConfirm');
    var ytBtn       = document.getElementById('lchYtBtn');

    /* ── Unlock helper ───────────────────────────── */
    function unlock(animate) {
        if (!gate || !content) return;
        if (animate) {
            gate.classList.add('lch-gate--unlocking');
            setTimeout(function () {
                gate.style.display    = 'none';
                content.style.display = 'block';
            }, 420);
        } else {
            gate.style.display    = 'none';
            content.style.display = 'block';
        }
        localStorage.setItem(STORAGE_KEY, '1');
    }

    /* ── Check on page load ──────────────────────── */
    if (localStorage.getItem(STORAGE_KEY) === '1') {
        unlock(false);
    }

    /* ── "I've subscribed" button ────────────────── */
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            unlock(true);
        });
    }

    /* ── Clicking Subscribe opens YT then shows confirm hint ── */
    if (ytBtn) {
        ytBtn.addEventListener('click', function () {
            setTimeout(function () {
                if (confirmBtn) {
                    confirmBtn.classList.add('lch-gate-confirm-btn--pulse');
                }
            }, 1800);
        });
    }

    /* ── Live search (only runs when content is visible) ── */
    var input    = document.getElementById('learnSearch');
    var clearBtn = document.getElementById('learnClear');
    var countEl  = document.getElementById('learnCount');
    var grid     = document.getElementById('learnGrid');
    var noRes    = document.getElementById('learnNoResults');
    var resetBtn = document.getElementById('learnReset');
    var cards    = grid ? Array.from(grid.querySelectorAll('.lch-vcard')) : [];

    function run() {
        var q = input.value.toLowerCase().trim();
        clearBtn.style.display = q ? 'flex' : 'none';
        var n = 0;
        cards.forEach(function (c) {
            var match = !q ||
                c.dataset.title.indexOf(q) > -1 ||
                c.dataset.category.indexOf(q) > -1 ||
                c.dataset.difficulty.indexOf(q) > -1;
            c.style.display = match ? '' : 'none';
            if (match) n++;
        });
        if (noRes) noRes.style.display = (n === 0 && q) ? 'block' : 'none';
        if (countEl) countEl.textContent = q ? n + ' lesson' + (n !== 1 ? 's' : '') + ' found' : '';
    }

    if (input) {
        input.addEventListener('input', run);
        clearBtn.addEventListener('click', function () { input.value = ''; run(); input.focus(); });
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', function () { input.value = ''; run(); input.focus(); });
    }
})();
</script>
