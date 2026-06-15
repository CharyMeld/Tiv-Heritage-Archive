<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">
            &#128214; <?= e($bookName) ?> <?= $chapter ?>
        </h1>
        <p class="admin-page-sub"><?= count($verses) ?> verses &mdash; paste Tiv text below</p>
    </div>
    <a href="<?= url('admin/bible') ?>" class="btn btn-secondary">&larr; All Books</a>
</div>

<!-- Chapter navigation -->
<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <?php for ($c = 1; $c <= $maxChapter; $c++): ?>
    <a href="<?= url('admin/bible/' . $bookKey . '/' . $c) ?>"
       style="padding:.25rem .65rem;border-radius:6px;font-size:.82rem;font-weight:600;text-decoration:none;
              background:<?= $c === $chapter ? 'var(--color-accent)' : 'var(--color-border-light)' ?>;
              color:<?= $c === $chapter ? 'var(--color-primary)' : 'var(--color-text)' ?>;">
        <?= $c ?>
    </a>
    <?php endfor; ?>
</div>

<div class="bible-entry-wrap">

    <!-- ── LEFT: English reference ────────────────────────────────── -->
    <div class="bible-english-col admin-card" style="padding:1.25rem;">
        <h3 class="bible-col-heading">English (WEB)</h3>
        <div class="bible-verse-list">
            <?php foreach ($verses as $v): ?>
            <div class="bible-verse-row" id="en-<?= $v['verse'] ?>">
                <span class="bible-vnum"><?= $v['verse'] ?></span>
                <span class="bible-vtext"><?= e($v['english_web']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── RIGHT: Tiv input ───────────────────────────────────────── -->
    <div class="bible-tiv-col admin-card" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;gap:1rem;flex-wrap:wrap;">
            <h3 class="bible-col-heading" style="margin:0;">Tiv</h3>
            <div style="display:flex;gap:.5rem;">
                <button type="button" onclick="setMode('bulk')"
                        id="btn-bulk"
                        class="bible-mode-btn bible-mode-active">
                    Paste all at once
                </button>
                <button type="button" onclick="setMode('individual')"
                        id="btn-individual"
                        class="bible-mode-btn">
                    Verse by verse
                </button>
            </div>
        </div>

        <form action="<?= url('admin/bible/' . $bookKey . '/' . $chapter) ?>"
              method="POST" id="tivForm">
            <?= csrf_field() ?>
            <input type="hidden" name="input_mode" id="input_mode" value="bulk">

            <!-- ── Chapter metadata ── -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--color-border-light);">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Tiv Book Name</label>
                    <input type="text" name="tiv_book_name" class="form-input"
                           value="<?= e($chapterMeta['tiv_book_name'] ?? '') ?>"
                           placeholder="e.g. Genese">
                    <p class="form-hint" style="margin-top:.3rem;">Set once — auto-fills all chapters of this book</p>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Tiv Chapter Title</label>
                    <input type="text" name="tiv_chapter_title" class="form-input"
                           value="<?= e($chapterMeta['tiv_chapter_title'] ?? '') ?>"
                           placeholder="Tiv heading for this chapter">
                    <p class="form-hint" style="margin-top:.3rem;">Main heading shown above the verses</p>
                </div>
            </div>

            <!-- ── BULK MODE ── -->
            <div id="bulk-panel">
                <p style="font-size:.8rem;color:var(--color-text-muted);margin-bottom:.6rem;">
                    Paste all <?= count($verses) ?> verses below — <strong>one verse per line</strong>,
                    in order from verse 1. Blank lines are ignored.
                </p>
                <textarea id="tiv_bulk" name="tiv_bulk"
                          class="form-textarea bible-bulk-area"
                          placeholder="Verse 1 text&#10;Verse 2 text&#10;Verse 3 text&#10;..."
                          spellcheck="false"><?php
                    // Pre-fill with any already-saved Tiv text
                    echo implode("\n", array_map(fn($v) => e($v['tiv'] ?? ''), $verses));
                ?></textarea>
                <p class="form-hint" id="line-counter">
                    Lines typed: <strong id="lineCount">0</strong> / <?= count($verses) ?> needed
                </p>
            </div>

            <!-- ── INDIVIDUAL MODE ── -->
            <div id="individual-panel" style="display:none;">
                <div class="bible-individual-list">
                    <?php foreach ($verses as $v): ?>
                    <div class="bible-individual-row">
                        <span class="bible-vnum"><?= $v['verse'] ?></span>
                        <input type="text"
                               name="verse_<?= $v['verse'] ?>"
                               class="form-input"
                               value="<?= e($v['tiv'] ?? '') ?>"
                               placeholder="Tiv verse <?= $v['verse'] ?>...">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display:flex;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">
                    Save &amp; go to next chapter &rsaquo;
                </button>
                <?php if ($chapter > 1): ?>
                <a href="<?= url('admin/bible/' . $bookKey . '/' . ($chapter - 1)) ?>"
                   class="btn btn-secondary">&larr; Previous chapter</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div><!-- /.bible-entry-wrap -->

<style>
.bible-entry-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 900px) {
    .bible-entry-wrap { grid-template-columns: 1fr; }
}
.bible-col-heading {
    font-family: var(--font-heading);
    font-size: .95rem;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0 0 .85rem;
}
.bible-verse-list, .bible-individual-list {
    display: flex;
    flex-direction: column;
    gap: .55rem;
    max-height: 70vh;
    overflow-y: auto;
    padding-right: .25rem;
}
.bible-verse-row, .bible-individual-row {
    display: flex;
    gap: .6rem;
    align-items: flex-start;
}
.bible-vnum {
    flex-shrink: 0;
    width: 1.6rem;
    font-size: .72rem;
    font-weight: 700;
    color: var(--color-accent);
    padding-top: .2rem;
    text-align: right;
}
.bible-vtext {
    font-size: .88rem;
    line-height: 1.5;
    color: var(--color-text);
}
.bible-individual-row .form-input {
    font-size: .85rem;
    padding: .3rem .55rem;
    flex: 1;
}
.bible-bulk-area {
    width: 100%;
    min-height: 420px;
    font-size: .88rem;
    line-height: 1.8;
    font-family: var(--font-body, sans-serif);
    resize: vertical;
}
.bible-mode-btn {
    padding: .3rem .8rem;
    border: 1.5px solid var(--color-border-light);
    border-radius: 6px;
    background: var(--color-surface);
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    color: var(--color-text);
    transition: border-color .2s, background .2s;
}
.bible-mode-btn:hover  { border-color: var(--color-accent); }
.bible-mode-active     { background: var(--color-accent); color: var(--color-primary); border-color: var(--color-accent); }
</style>

<script>
function setMode(mode) {
    document.getElementById('input_mode').value = mode;
    const bulk = document.getElementById('bulk-panel');
    const ind  = document.getElementById('individual-panel');
    const btnB = document.getElementById('btn-bulk');
    const btnI = document.getElementById('btn-individual');
    if (mode === 'bulk') {
        bulk.style.display = ''; ind.style.display = 'none';
        btnB.classList.add('bible-mode-active');
        btnI.classList.remove('bible-mode-active');
    } else {
        bulk.style.display = 'none'; ind.style.display = '';
        btnI.classList.add('bible-mode-active');
        btnB.classList.remove('bible-mode-active');
    }
}

// Live line counter for bulk mode
const bulkArea = document.getElementById('tiv_bulk');
const lineCount = document.getElementById('lineCount');
function updateCount() {
    const lines = bulkArea.value.split('\n').filter(l => l.trim() !== '').length;
    lineCount.textContent = lines;
    lineCount.style.color = lines === <?= count($verses) ?> ? '#16a34a' : (lines > <?= count($verses) ?> ? '#dc2626' : 'inherit');
}
bulkArea.addEventListener('input', updateCount);
updateCount();

// Sync scroll: clicking a Tiv line highlights the matching English verse
bulkArea.addEventListener('keydown', function() {
    setTimeout(() => {
        const lines = bulkArea.value.substr(0, bulkArea.selectionStart).split('\n').length;
        const row = document.getElementById('en-' + lines);
        if (row) row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }, 10);
});

// ── Strip leading verse numbers BEFORE the form submits ──────────────
// Runs in the browser, so server-side caching cannot affect it.
// Handles: "1 text", "1. text", "1: text", "1) text", "(1) text"
function stripVerseNumbers(text) {
    return text
        .split('\n')
        .map(function(line) {
            var t = line.trim();
            // Whole line is just a number (standalone verse marker) → discard
            if (/^\(?\d+\)?$/.test(t)) return '';
            // Number at start followed by separator → strip it
            return line.replace(/^\s*\(?\d+[\)\.\:\,\s]+/, '').trimEnd();
        })
        .join('\n');
}

document.getElementById('tivForm').addEventListener('submit', function() {
    // Strip from bulk textarea
    const bulk = document.getElementById('tiv_bulk');
    if (bulk) bulk.value = stripVerseNumbers(bulk.value);

    // Strip from every individual verse input
    document.querySelectorAll('[name^="verse_"]').forEach(function(input) {
        input.value = input.value.replace(/^\s*\(?\d+[\)\.\:\,\s]+/, '').trim();
    });
});
</script>
