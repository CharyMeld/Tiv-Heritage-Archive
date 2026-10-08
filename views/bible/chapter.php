<!-- ═══════════════════════════════════════════
     PAGE HEADER — always shows book name + chapter
════════════════════════════════════════════ -->
<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">
            &#128214; <a href="<?= url('bible') ?>" style="color:inherit;text-decoration:none;">Icighan Bibilo</a>
            &rsaquo; <a href="<?= url('bible/' . $bookKey . '/1') ?>" style="color:inherit;text-decoration:none;"><?= e($bookName) ?></a>
        </span>

        <?php if (!empty($tivBookName)): ?>
            <!-- Tiv name is the main heading -->
            <h1 class="page-banner-title"><?= e($tivBookName) ?> <?= $chapter ?></h1>
            <?php if (!empty($tivChapterTitle)): ?>
            <p class="page-banner-sub bible-chapter-title-tiv"><?= e($tivChapterTitle) ?></p>
            <?php endif; ?>
            <p class="page-banner-sub" style="font-size:.82rem;opacity:.55;margin-top:.25rem;">
                <?= e($bookName) ?> Chapter <?= $chapter ?>
            </p>
        <?php else: ?>
            <!-- No Tiv name yet — show English, prompt to add Tiv -->
            <h1 class="page-banner-title"><?= e($bookName) ?> <?= $chapter ?></h1>
            <p class="page-banner-sub">
                <a href="<?= url('admin/bible/' . $bookKey . '/' . $chapter) ?>"
                   style="color:var(--color-accent);font-size:.85rem;">
                    &#9998; Add Tiv book name &amp; chapter title
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>

<div style="padding:1.5rem 0 3rem;">
    <div class="container">

        <!-- ── Chapter navigation pills ── -->
        <div class="bible-ch-nav">
            <?php if ($chapter > 1): ?>
            <a href="<?= url('bible/' . $bookKey . '/' . ($chapter - 1)) ?>" class="bible-ch-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <?= $chapter - 1 ?>
            </a>
            <?php else: ?><span></span><?php endif; ?>

            <div class="bible-ch-pills">
                <?php
                $start = max(1, $chapter - 5);
                $end   = min($maxChapter, $chapter + 5);
                if ($start > 1): ?><span class="bible-ch-ellipsis">1 …</span><?php endif;
                for ($c = $start; $c <= $end; $c++): ?>
                <a href="<?= url('bible/' . $bookKey . '/' . $c) ?>"
                   class="bible-ch-pill <?= $c === $chapter ? 'active' : '' ?>"><?= $c ?></a>
                <?php endfor;
                if ($end < $maxChapter): ?><span class="bible-ch-ellipsis">… <?= $maxChapter ?></span><?php endif; ?>
            </div>

            <?php if ($chapter < $maxChapter): ?>
            <a href="<?= url('bible/' . $bookKey . '/' . ($chapter + 1)) ?>" class="bible-ch-arrow">
                <?= $chapter + 1 ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            <?php else: ?><span></span><?php endif; ?>
        </div>

        <!-- ── View mode toggle ── -->
        <div class="bible-view-toggle">
            <button onclick="setView('paired')" id="btn-paired" class="bible-view-btn bible-view-active">Paired</button>
            <button onclick="setView('tiv')"    id="btn-tiv"    class="bible-view-btn">Tiv only</button>
            <button onclick="setView('eng')"    id="btn-eng"    class="bible-view-btn">English only</button>
        </div>

        <!-- ── Verses ── -->
        <div class="bible-verses-wrap" id="bible-verses">
            <?php foreach ($verses as $v): ?>
            <div class="bible-verse-block" id="v<?= $v['verse'] ?>">

                <!-- Single verse number — never duplicated -->
                <span class="bible-vnum"><?= $v['verse'] ?></span>

                <!-- Both language texts in one column -->
                <div class="bible-verse-texts">

                    <!-- Tiv text (hidden in English-only mode) -->
                    <p class="bible-text-tiv"><?php if (!empty($v['tiv'])): ?><?= e($v['tiv']) ?><?php else: ?><a href="<?= url('admin/bible/' . $bookKey . '/' . $chapter) ?>" class="bible-add-link">+ Add Tiv</a><?php endif; ?></p>

                    <!-- English text (hidden in Tiv-only mode) -->
                    <p class="bible-text-eng"><?= e($v['english_web']) ?></p>

                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <?php if (ads_on()): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="<?= ADSENSE_CLIENT ?>" data-ad-slot="<?= ADSENSE_SLOT ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <!-- ── Bottom navigation ── -->
        <div class="bible-ch-nav" style="margin-top:2rem;">
            <?php if ($chapter > 1): ?>
            <a href="<?= url('bible/' . $bookKey . '/' . ($chapter - 1)) ?>" class="btn btn-secondary">
                &larr; <?= e(!empty($tivBookName) ? $tivBookName : $bookName) ?> <?= $chapter - 1 ?>
            </a>
            <?php else: ?>
            <a href="<?= url('bible') ?>" class="btn btn-secondary">&larr; All Books</a>
            <?php endif; ?>

            <?php if ($chapter < $maxChapter): ?>
            <a href="<?= url('bible/' . $bookKey . '/' . ($chapter + 1)) ?>" class="btn btn-primary">
                <?= e(!empty($tivBookName) ? $tivBookName : $bookName) ?> <?= $chapter + 1 ?> &rarr;
            </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
/* ── Chapter title ── */
.bible-chapter-title-tiv {
    font-style: italic;
    color: var(--color-accent);
    font-size: 1.05rem;
    font-weight: 500;
}

/* ── Chapter navigation ── */
.bible-ch-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.bible-ch-arrow {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    font-size: .85rem;
    font-weight: 600;
    color: var(--color-primary);
    text-decoration: none;
    padding: .35rem .75rem;
    border: 1.5px solid var(--color-border-light);
    border-radius: 8px;
    background: var(--color-surface);
    transition: border-color .2s;
}
.bible-ch-arrow:hover { border-color: var(--color-accent); text-decoration: none; color: var(--color-primary); }
.bible-ch-pills { display: flex; gap: .3rem; flex-wrap: wrap; align-items: center; }
.bible-ch-pill {
    min-width: 2rem; text-align: center;
    padding: .25rem .5rem; border-radius: 6px;
    font-size: .82rem; font-weight: 600;
    text-decoration: none;
    color: var(--color-text);
    background: var(--color-border-light);
    transition: background .15s;
}
.bible-ch-pill:hover, .bible-ch-pill.active {
    background: var(--color-accent); color: var(--color-primary); text-decoration: none;
}
.bible-ch-ellipsis { font-size: .78rem; color: var(--color-text-muted); }

/* ── View toggle ── */
.bible-view-toggle { display: flex; gap: .4rem; margin-bottom: 1.25rem; }
.bible-view-btn {
    padding: .3rem .9rem;
    border: 1.5px solid var(--color-border-light);
    border-radius: 6px; font-size: .78rem; font-weight: 600;
    cursor: pointer; background: var(--color-surface); color: var(--color-text);
    transition: border-color .15s, background .15s;
}
.bible-view-btn:hover  { border-color: var(--color-accent); }
.bible-view-active     { background: var(--color-accent); color: var(--color-primary); border-color: var(--color-accent); }

/* ── Verse blocks ── */
.bible-verses-wrap { display: flex; flex-direction: column; }

.bible-verse-block {
    display: grid;
    grid-template-columns: 2.2rem 1fr;
    gap: .5rem;
    align-items: start;
    padding: .7rem 0;
    border-bottom: 1px solid var(--color-border-light);
    scroll-margin-top: 5rem;
}
.bible-verse-block:last-child { border-bottom: none; }

/* Verse number — one per verse, never duplicated */
.bible-vnum {
    font-size: .68rem;
    font-weight: 800;
    color: var(--color-accent);
    text-align: right;
    padding-top: .25rem;
    line-height: 1;
}

/* Text column */
.bible-verse-texts {
    display: flex;
    flex-direction: column;
    gap: .3rem;
}

.bible-text-tiv {
    font-size: .97rem;
    font-weight: 600;
    line-height: 1.65;
    color: var(--color-primary);
    margin: 0;
}
.bible-add-link {
    font-size: .72rem; color: var(--color-accent);
    opacity: .5; text-decoration: none; font-style: italic;
}
.bible-add-link:hover { opacity: 1; }
.bible-text-eng {
    font-size: .88rem;
    line-height: 1.6;
    color: var(--color-text-muted);
    margin: 0;
    padding-top: .2rem;
    border-top: 1px dashed var(--color-border-light);
}

/* ── View modes ── */

/* Tiv only — hide English paragraph only */
.bible-verses-wrap.view-tiv  .bible-text-eng { display: none; }
.bible-verses-wrap.view-tiv  .bible-text-tiv { border-top: none; }

/* English only — hide Tiv paragraph only */
.bible-verses-wrap.view-eng  .bible-text-tiv { display: none; }
.bible-verses-wrap.view-eng  .bible-text-eng {
    font-size: .97rem;
    color: var(--color-text);
    border-top: none;
    padding-top: 0;
}
</style>

<script>
function setView(mode) {
    const wrap = document.getElementById('bible-verses');
    wrap.className = 'bible-verses-wrap';
    if (mode !== 'paired') wrap.classList.add('view-' + mode);
    ['paired','tiv','eng'].forEach(function(m) {
        document.getElementById('btn-' + m).classList.toggle('bible-view-active', m === mode);
    });
    localStorage.setItem('bibleView', mode);
}
(function() { setView(localStorage.getItem('bibleView') || 'paired'); }());
</script>
