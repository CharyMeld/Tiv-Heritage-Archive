<?php
/**
 * Timeline event detail page
 * Variables: $item, $categories, $gallery, $sources, $neighbors, $links, $breadcrumb
 */
$confidenceClass = $item['confidence_score'] >= 75 ? 'is-high' : ($item['confidence_score'] >= 50 ? 'is-medium' : 'is-low');
?>
<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark">
            <?php $this->partial('breadcrumb'); ?>
        </div>
        <span class="detail-banner-cat">&#128197; <?= e($item['era'] ?? 'Timeline') ?></span>
        <h1 class="detail-banner-title"><?= e($item['title']) ?></h1>
        <p class="detail-banner-sub">
            <?= e($item['event_date'] ?? $item['year'] ?? '') ?><?= !empty($item['is_estimated']) ? ' (estimated)' : '' ?>
        </p>
        <span class="tl-confidence-badge <?= $confidenceClass ?>" title="Sourcing confidence"><?= (int) $item['confidence_score'] ?>% confidence</span>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['image'])): ?>
        <div style="margin-bottom:1.5rem;border-radius:.75rem;overflow:hidden;">
            <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="<?= e($item['title']) ?>" style="width:100%;max-height:480px;object-fit:cover;display:block;">
        </div>
        <?php endif; ?>

        <?php if (!empty($categories)): ?>
        <div class="tl-card-tags" style="margin-bottom:1.25rem;">
            <?php foreach ($categories as $cat): ?>
            <span class="tl-tag"><?= e($cat) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Facts -->
        <?php
        $facts = [
            'Era'      => $item['era'],
            'Century'  => $item['century'],
            'Decade'   => $item['decade'],
            'Location' => $item['location'],
            'Clan'     => $item['clan'],
        ];
        $facts = array_filter($facts, fn($v) => !empty($v));
        ?>
        <?php if ($facts): ?>
        <div class="detail-section-modern hf-quick-facts">
            <span class="detail-section-label">Quick Facts</span>
            <dl class="hf-facts-grid">
                <?php foreach ($facts as $label => $value): ?>
                <div class="hf-fact">
                    <dt><?= e($label) ?></dt>
                    <dd><?= e($value) ?></dd>
                </div>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['short_summary'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Overview</span>
            <p class="detail-section-text"><?= nl2br(e($item['short_summary'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Full Account</span>
            <p class="detail-section-text"><?= nl2br(e($item['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['historical_significance'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Historical Significance</span>
            <p class="detail-section-text"><?= nl2br(e($item['historical_significance'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['causes'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Causes</span>
            <p class="detail-section-text"><?= nl2br(e($item['causes'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['consequences'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Consequences</span>
            <p class="detail-section-text"><?= nl2br(e($item['consequences'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['related_institutions'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Related Institutions</span>
            <ul class="hf-references">
                <?php foreach (preg_split('/\r?\n/', trim($item['related_institutions'])) as $line): ?>
                <?php if (trim($line) !== ''): ?>
                <li><?= e(trim($line)) ?></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['alternative_dates_notes'])): ?>
        <div class="detail-section-modern tl-notes-block">
            <span class="detail-section-label">Sourcing &amp; Dating Notes</span>
            <p class="detail-section-text"><?= nl2br(e($item['alternative_dates_notes'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($sources)): ?>
        <div class="detail-section-modern kg-source-block">
            <span class="detail-section-label">&#128218; Sources &amp; Citations</span>
            <ul class="hf-references">
                <?php foreach ($sources as $src): ?>
                <li>
                    <?php if (!empty($src['author'])): ?><?= e($src['author']) ?>.<?php endif; ?>
                    <?php if (!empty($src['title'])): ?> <em><?= e($src['title']) ?></em>.<?php endif; ?>
                    <?php if (!empty($src['publisher'])): ?> <?= e($src['publisher']) ?>.<?php endif; ?>
                    <?php if (!empty($src['year_recorded'])): ?> <?= e($src['year_recorded']) ?>.<?php endif; ?>
                    <?php if (!empty($src['url'])): ?> <a href="<?= e($src['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($src['url']) ?></a><?php endif; ?>
                    <?php if (!empty($src['access_date'])): ?> (accessed <?= e($src['access_date']) ?>)<?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php $this->partial('source-and-links', ['source' => null, 'links' => $links]); ?>

        <?php if (ADSENSE_ENABLED): ?>
        <div class="adsense-wrap">
            <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7960622250292703" data-ad-slot="6111588136" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <?php if (!empty($neighbors['prev']) || !empty($neighbors['next'])): ?>
        <div class="tl-neighbor-nav">
            <?php if (!empty($neighbors['prev'])): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('timeline-event', $neighbors['prev']['id'], $neighbors['prev']['title'])) ?>" class="tl-neighbor-link tl-neighbor-prev">
                <span class="tl-neighbor-label">&larr; <?= e($neighbors['prev']['year']) ?></span>
                <span class="tl-neighbor-title"><?= e($neighbors['prev']['title']) ?></span>
            </a>
            <?php else: ?><span></span><?php endif; ?>
            <?php if (!empty($neighbors['next'])): ?>
            <a href="<?= url(SeoHelper::canonicalSlugPath('timeline-event', $neighbors['next']['id'], $neighbors['next']['title'])) ?>" class="tl-neighbor-link tl-neighbor-next">
                <span class="tl-neighbor-label"><?= e($neighbors['next']['year']) ?> &rarr;</span>
                <span class="tl-neighbor-title"><?= e($neighbors['next']['title']) ?></span>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('timeline') ?>" class="btn btn-secondary">&larr; All Timeline Events</a>
        </div>
    </div>
</div>
