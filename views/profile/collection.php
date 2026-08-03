<!-- Page Banner -->
<div class="page-banner">
    <div class="container">
        <a href="<?= url('profile') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Profile
        </a>
        <h1 class="page-banner-title">&#11088; My Collection</h1>
        <p class="page-banner-sub">References you've saved for later</p>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container">
        <?php if (empty($sources)): ?>
        <div style="text-align: center; padding: 4rem 1rem;">
            <p style="font-size: 3rem; margin-bottom: 1rem;">&#11088;</p>
            <h3 style="font-family: var(--font-heading); color: var(--color-primary); margin-bottom: 0.5rem;">Nothing saved yet</h3>
            <p style="color: var(--color-text-muted); margin-bottom: 2rem;">Use the &#11088; Save button on any reference's details page to add it here.</p>
            <a href="<?= url('references') ?>" class="btn btn-primary">Browse References</a>
        </div>
        <?php else: ?>
        <div class="archive-grid-modern">
            <?php foreach ($sources as $source): ?>
            <a href="<?= url('references/' . $source['id']) ?>" class="archive-card-modern">
                <span class="archive-card-modern-icon"><?= Source::$typeIcons[$source['source_type']] ?? '&#128218;' ?></span>
                <h3 class="archive-card-modern-title"><?= e($source['title'] ?: 'Untitled source') ?></h3>
                <p class="archive-card-modern-sub"><?= e($source['author'] ?? $source['contributor_name'] ?? '') ?></p>
                <span class="archive-card-modern-meta"><?= e(Source::$typeLabels[$source['source_type']] ?? ucfirst($source['source_type'])) ?><?= !empty($source['year_recorded']) ? ' · ' . e($source['year_recorded']) : '' ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <a href="<?= url('profile') ?>" class="btn btn-secondary">&larr; Back to Profile</a>
        </div>
    </div>
</div>
