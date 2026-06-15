<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128214; Icighan Bibilo</span>
        <h1 class="page-banner-title">The Bible</h1>
        <p class="page-banner-sub">
            World English Bible &bull; Tiv Translation &bull;
            <?= number_format(array_sum(array_column(array_merge($ot,$nt), 'total_verses'))) ?> verses
        </p>
        <div class="page-banner-search" style="max-width:480px;">
            <form action="<?= url('bible/search') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" name="q" class="explore-search-input"
                           placeholder="Search verses in English or Tiv…" autocomplete="off">
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bible-index-page">
    <div class="container">

        <!-- ── Old Testament ── -->
        <div class="bible-testament-block">
            <div class="bible-testament-header bible-testament-ot">
                <div class="bible-testament-label">
                    <span class="bible-testament-badge">OT</span>
                    <h2>Old Testament</h2>
                </div>
                <span class="bible-testament-count"><?= count($ot) ?> books &bull; <?= number_format(array_sum(array_column($ot, 'total_verses'))) ?> verses</span>
            </div>
            <div class="bible-book-grid">
                <?php foreach ($ot as $b): ?>
                <a href="<?= url('bible/' . $b['book_key'] . '/1') ?>" class="bible-book-card bible-book-ot">
                    <span class="bible-book-abbr"><?= e($b['book_key']) ?></span>
                    <span class="bible-book-name"><?= e($b['book']) ?></span>
                    <span class="bible-book-meta"><?= $b['chapters'] ?> ch</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── New Testament ── -->
        <div class="bible-testament-block">
            <div class="bible-testament-header bible-testament-nt">
                <div class="bible-testament-label">
                    <span class="bible-testament-badge bible-badge-nt">NT</span>
                    <h2>New Testament</h2>
                </div>
                <span class="bible-testament-count"><?= count($nt) ?> books &bull; <?= number_format(array_sum(array_column($nt, 'total_verses'))) ?> verses</span>
            </div>
            <div class="bible-book-grid">
                <?php foreach ($nt as $b): ?>
                <a href="<?= url('bible/' . $b['book_key'] . '/1') ?>" class="bible-book-card bible-book-nt">
                    <span class="bible-book-abbr"><?= e($b['book_key']) ?></span>
                    <span class="bible-book-name"><?= e($b['book']) ?></span>
                    <span class="bible-book-meta"><?= $b['chapters'] ?> ch</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<style>
.bible-index-page { padding: 2rem 0 4rem; }

/* Testament block */
.bible-testament-block { margin-bottom: 3rem; }
.bible-testament-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .75rem 1.1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: .5rem;
}
.bible-testament-ot { background: linear-gradient(135deg, #1a3a5c22, #2e6da418); border-left: 4px solid #2e6da4; }
.bible-testament-nt { background: linear-gradient(135deg, #7B1D1D22, #C0392B18); border-left: 4px solid #C0392B; }
.bible-testament-label { display: flex; align-items: center; gap: .65rem; }
.bible-testament-label h2 {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0;
}
.bible-testament-badge {
    display: inline-block;
    padding: .2rem .55rem;
    border-radius: 5px;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .08em;
    background: #2e6da4;
    color: #fff;
}
.bible-badge-nt { background: #C0392B; }
.bible-testament-count {
    font-size: .78rem;
    color: var(--color-text-muted);
    font-family: var(--font-ui);
}

/* Book grid */
.bible-book-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: .5rem;
}
@media (max-width: 480px) { .bible-book-grid { grid-template-columns: repeat(3, 1fr); } }

/* Book card */
.bible-book-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: .15rem;
    padding: .65rem .8rem;
    border-radius: 10px;
    text-decoration: none;
    transition: transform .18s, box-shadow .18s;
    position: relative;
    overflow: hidden;
}
.bible-book-card::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity .18s;
}
.bible-book-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,.12); text-decoration: none; }
.bible-book-card:hover::before { opacity: 1; }

.bible-book-ot {
    background: linear-gradient(135deg, #0d2137ee, #1a3a5cee);
    color: #e8f0f8;
}
.bible-book-ot:hover { color: #fff; }
.bible-book-nt {
    background: linear-gradient(135deg, #2d0a0aee, #7B1D1Dee);
    color: #f8e8e8;
}
.bible-book-nt:hover { color: #fff; }

.bible-book-abbr {
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .06em;
    opacity: .55;
    text-transform: uppercase;
}
.bible-book-name {
    font-size: .82rem;
    font-weight: 700;
    line-height: 1.2;
}
.bible-book-meta {
    font-size: .65rem;
    opacity: .5;
    margin-top: .1rem;
}
</style>
