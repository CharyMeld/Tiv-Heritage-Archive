<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128214; Bible — Tiv Entry</h1>
        <p class="admin-page-sub">Add Tiv translations chapter by chapter</p>
    </div>
    <form action="<?= url('admin/bible/mine') ?>" method="POST"
          onsubmit="return confirm('Re-mine all aligned verses into the translation engine? This may take a moment.');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-secondary">
            &#9881; Sync to Translation Engine
        </button>
    </form>
</div>

<?php
// Split books into NT and OT
$nt = array_filter($books, fn($b) => $b['testament'] === 'NT');
$ot = array_filter($books, fn($b) => $b['testament'] === 'OT');

function renderBookGrid(array $bookList, array $statsMap): void {
    foreach ($bookList as $b):
        $key   = $b['book_key'];
        $s     = $statsMap[$key] ?? ['tiv_done' => 0, 'total' => 1];
        $pct   = $s['total'] ? round($s['tiv_done'] / $s['total'] * 100) : 0;
        $done  = (int)$s['tiv_done'];
        $total = (int)$s['total'];
        $color = $pct === 100 ? '#16a34a' : ($pct > 0 ? '#d97706' : '#6b7280');
?>
    <a href="<?= url('admin/bible/' . $key . '/1') ?>"
       class="bible-book-card"
       title="<?= e($b['book']) ?> — <?= $done ?>/<?= $total ?> verses">
        <div class="bible-book-name"><?= e($b['book']) ?></div>
        <div class="bible-book-chapters"><?= $b['chapters'] ?> ch &middot; <?= $total ?> v</div>
        <div class="bible-book-bar-wrap">
            <div class="bible-book-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
        </div>
        <div class="bible-book-pct" style="color:<?= $color ?>"><?= $pct ?>%</div>
    </a>
<?php endforeach;
}
?>

<div class="admin-card" style="padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin:0 0 1rem;color:var(--color-primary);">
        New Testament
    </h2>
    <div class="bible-book-grid">
        <?php renderBookGrid($nt, $statsMap) ?>
    </div>
</div>

<div class="admin-card" style="padding:1.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin:0 0 1rem;color:var(--color-primary);">
        Old Testament <span style="font-size:.8rem;font-weight:400;color:var(--color-text-muted);">(add when Tiv OT is available)</span>
    </h2>
    <div class="bible-book-grid">
        <?php renderBookGrid($ot, $statsMap) ?>
    </div>
</div>

<style>
.bible-book-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: .6rem;
}
.bible-book-card {
    display: flex;
    flex-direction: column;
    gap: .2rem;
    padding: .65rem .75rem;
    border: 1.5px solid var(--color-border-light);
    border-radius: 10px;
    text-decoration: none;
    color: var(--color-text);
    background: var(--color-surface);
    transition: border-color .2s, box-shadow .2s;
}
.bible-book-card:hover {
    border-color: var(--color-accent);
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    text-decoration: none;
    color: var(--color-text);
}
.bible-book-name    { font-weight: 700; font-size: .88rem; }
.bible-book-chapters{ font-size: .72rem; color: var(--color-text-muted); }
.bible-book-bar-wrap{ height: 4px; background: var(--color-border-light); border-radius: 2px; overflow: hidden; margin-top: .3rem; }
.bible-book-bar     { height: 100%; border-radius: 2px; transition: width .3s; }
.bible-book-pct     { font-size: .7rem; font-weight: 700; }
</style>
