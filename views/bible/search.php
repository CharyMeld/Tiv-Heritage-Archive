<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128214; <a href="<?= url('bible') ?>" style="color:inherit;text-decoration:none;">Bible</a></span>
        <h1 class="page-banner-title">Search the Bible</h1>
        <div class="page-banner-search">
            <form action="<?= url('bible/search') ?>" method="GET">
                <div class="explore-search-wrap">
                    <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" name="q" class="explore-search-input"
                           value="<?= e($query) ?>" placeholder="Search in English or Tiv…"
                           autocomplete="off" autofocus>
                    <button type="submit" class="explore-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="padding:1.5rem 0 3rem;">
    <div class="container">

        <?php if ($query && empty($results)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128214;</div>
            <h3>No verses found</h3>
            <p>Try different keywords or browse by book.</p>
            <a href="<?= url('bible') ?>" class="btn btn-primary">Browse Bible</a>
        </div>

        <?php elseif (!empty($results)): ?>
        <p style="font-size:.85rem;color:var(--color-text-muted);margin-bottom:1.25rem;">
            <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for
            &ldquo;<strong><?= e($query) ?></strong>&rdquo;
        </p>
        <div style="display:flex;flex-direction:column;gap:.65rem;">
            <?php foreach ($results as $v): ?>
            <a href="<?= url('bible/' . $v['book_key'] . '/' . $v['chapter']) . '#v' . $v['verse'] ?>"
               class="bible-search-result">
                <span class="bible-search-ref">
                    <?= e($v['book'] . ' ' . $v['chapter'] . ':' . $v['verse']) ?>
                    <span class="bible-search-testament"><?= $v['testament'] ?></span>
                </span>
                <p class="bible-search-eng"><?= e($v['english_web']) ?></p>
                <?php if (!empty($v['tiv'])): ?>
                <p class="bible-search-tiv"><?= e($v['tiv']) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php elseif ($query === ''): ?>
        <div style="text-align:center;padding:3rem 0;">
            <div style="font-size:3rem;margin-bottom:1rem;">&#128214;</div>
            <p style="color:var(--color-text-muted);">Enter a word or phrase above to search the Bible.</p>
            <a href="<?= url('bible') ?>" class="btn btn-primary" style="margin-top:1rem;">Browse by Book</a>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
.bible-search-result {
    display: flex;
    flex-direction: column;
    gap: .35rem;
    padding: 1rem 1.25rem;
    border: 1.5px solid var(--color-border-light);
    border-radius: 12px;
    background: var(--color-surface);
    text-decoration: none;
    color: var(--color-text);
    transition: border-color .2s, box-shadow .2s;
}
.bible-search-result:hover {
    border-color: var(--color-accent);
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    text-decoration: none;
    color: var(--color-text);
}
.bible-search-ref {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .75rem;
    font-weight: 700;
    color: var(--color-accent);
    text-transform: uppercase;
    letter-spacing: .05em;
}
.bible-search-testament {
    font-size: .65rem;
    background: var(--color-border-light);
    color: var(--color-text-muted);
    padding: .1rem .4rem;
    border-radius: 4px;
    font-weight: 600;
}
.bible-search-eng {
    font-size: .9rem;
    line-height: 1.5;
    margin: 0;
    color: var(--color-text);
}
.bible-search-tiv {
    font-size: .85rem;
    line-height: 1.45;
    margin: 0;
    color: var(--color-primary);
    font-weight: 500;
    border-left: 3px solid var(--color-accent);
    padding-left: .65rem;
}
</style>
