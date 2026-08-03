<?php
/**
 * Reference Details page
 * Variables: $source, $canonicalUrl, $defaultCitation, $saved, $breadcrumb
 */
$icon = Source::$typeIcons[$source['source_type']] ?? '&#128218;';
$typeLabel = Source::$typeLabels[$source['source_type']] ?? ucfirst(str_replace('_', ' ', $source['source_type']));

$shareTitle = $source['title'] ?: 'Reference';
$emailBody = rawurlencode($shareTitle . "\n\n" . $canonicalUrl);
$emailSubject = rawurlencode('Tiv Heritage Archive: ' . $shareTitle);
$shareUrlEnc = rawurlencode($canonicalUrl);
$shareTitleEnc = rawurlencode($shareTitle);

$exportFormats = [
    'apa'     => 'APA',
    'mla'     => 'MLA',
    'chicago' => 'Chicago',
    'harvard' => 'Harvard',
    'bibtex'  => 'BibTeX',
    'ris'     => 'RIS',
];
?>
<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark">
            <?php $this->partial('breadcrumb'); ?>
        </div>

        <!-- Action Toolbar -->
        <div class="ref-toolbar" style="margin-bottom:1.25rem;">

            <div class="ref-toolbar-item">
                <button type="button" class="ref-toolbar-btn" data-action="copy-citation"
                        data-citation="<?= e($defaultCitation) ?>"
                        title="Copy the APA citation to your clipboard" aria-label="Copy Citation">
                    <span class="ref-toolbar-icon" aria-hidden="true">&#128203;</span>
                    <span class="ref-toolbar-label">Copy Citation</span>
                </button>
            </div>

            <div class="ref-toolbar-item" data-dropdown>
                <button type="button" class="ref-toolbar-btn"
                        title="Export this citation in APA, MLA, Chicago, Harvard, BibTeX, or RIS" aria-label="Export Citation">
                    <span class="ref-toolbar-icon" aria-hidden="true">&#11015;&#65039;</span>
                    <span class="ref-toolbar-label">Export Citation</span>
                </button>
                <div class="ref-toolbar-menu" role="menu">
                    <?php foreach ($exportFormats as $fmt => $label): ?>
                    <a href="<?= url('references/' . $source['id'] . '/export/' . $fmt) ?>"
                       class="ref-toolbar-menu-link" role="menuitem"
                       data-action="export-format" data-format-label="<?= e($label) ?>">
                        <?= e($label) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ref-toolbar-item">
                <button type="button" class="ref-toolbar-btn" data-action="copy-link"
                        data-url="<?= e($canonicalUrl) ?>"
                        title="Copy the permanent link to this reference" aria-label="Copy Permanent Link">
                    <span class="ref-toolbar-icon" aria-hidden="true">&#128279;</span>
                    <span class="ref-toolbar-label">Copy Link</span>
                </button>
            </div>

            <div class="ref-toolbar-item" data-dropdown>
                <button type="button" class="ref-toolbar-btn" data-action="share"
                        data-share-title="<?= e($shareTitle) ?>" data-share-url="<?= e($canonicalUrl) ?>"
                        title="Share this reference" aria-label="Share">
                    <span class="ref-toolbar-icon" aria-hidden="true">&#128228;</span>
                    <span class="ref-toolbar-label">Share</span>
                </button>
                <div class="ref-toolbar-menu" role="menu">
                    <a class="ref-toolbar-menu-link" role="menuitem" target="_blank" rel="noopener noreferrer" data-action="share-fallback-link"
                       href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrlEnc ?>">&#128075; Facebook</a>
                    <a class="ref-toolbar-menu-link" role="menuitem" target="_blank" rel="noopener noreferrer" data-action="share-fallback-link"
                       href="https://twitter.com/intent/tweet?url=<?= $shareUrlEnc ?>&text=<?= $shareTitleEnc ?>">&#10006; X (Twitter)</a>
                    <a class="ref-toolbar-menu-link" role="menuitem" target="_blank" rel="noopener noreferrer" data-action="share-fallback-link"
                       href="https://wa.me/?text=<?= $shareTitleEnc ?>%20-%20<?= $shareUrlEnc ?>">&#128241; WhatsApp</a>
                    <a class="ref-toolbar-menu-link" role="menuitem" target="_blank" rel="noopener noreferrer" data-action="share-fallback-link"
                       href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrlEnc ?>">&#128188; LinkedIn</a>
                    <a class="ref-toolbar-menu-link" role="menuitem" data-action="share-fallback-link"
                       href="mailto:?subject=<?= $emailSubject ?>&body=<?= $emailBody ?>">&#9993;&#65039; Email</a>
                </div>
            </div>

            <div class="ref-toolbar-item">
                <?php if (!empty($user)): ?>
                <button type="button" class="ref-toolbar-btn<?= $saved ? ' is-active' : '' ?>" data-action="save"
                        data-url="<?= url('references/' . $source['id'] . '/save') ?>"
                        data-token="<?= csrf_token() ?>"
                        aria-pressed="<?= $saved ? 'true' : 'false' ?>"
                        title="Save this reference to your personal collection" aria-label="Save to Collection">
                    <span class="ref-toolbar-icon" aria-hidden="true"><?= $saved ? '⭐' : '☆' ?></span>
                    <span class="ref-toolbar-label"><?= $saved ? 'Saved' : 'Save to Collection' ?></span>
                </button>
                <?php else: ?>
                <button type="button" class="ref-toolbar-btn" data-action="save" data-auth-required="1"
                        data-login-url="<?= url('login') ?>"
                        title="Log in to save this reference to your collection" aria-label="Save to Collection (login required)">
                    <span class="ref-toolbar-icon" aria-hidden="true">☆</span>
                    <span class="ref-toolbar-label">Save to Collection</span>
                </button>
                <?php endif; ?>
            </div>

            <div class="ref-toolbar-item">
                <a class="ref-toolbar-btn" data-action="print"
                   href="<?= url('references/' . $source['id'] . '/print') ?>" target="_blank" rel="noopener"
                   title="Open a print-friendly version of this reference" aria-label="Print">
                    <span class="ref-toolbar-icon" aria-hidden="true">&#128424;&#65039;</span>
                    <span class="ref-toolbar-label">Print</span>
                </a>
            </div>
        </div>

        <span class="detail-banner-cat"><?= $icon ?> <?= e($typeLabel) ?></span>
        <h1 class="detail-banner-title"><?= e($source['title'] ?: 'Untitled source') ?></h1>
        <?php if (!empty($source['author']) || !empty($source['contributor_name'])): ?>
        <p class="detail-banner-sub"><?= e($source['author'] ?: $source['contributor_name']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php
        $facts = [
            'Author'      => $source['author'],
            'Contributor' => $source['contributor_name'],
            'Publisher'   => $source['publisher'],
            'Year'        => $source['year_recorded'],
            'Location'    => $source['location'],
            'ISBN'        => $source['isbn'],
            'DOI'         => $source['doi'],
            'Access Date' => $source['access_date'],
        ];
        $facts = array_filter($facts, fn($v) => !empty($v));
        ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Reference Information</span>
            <div class="ref-field-grid">
                <div class="ref-field">
                    <span class="ref-field-label">Type</span>
                    <span class="ref-field-value"><span class="ref-type-pill"><?= $icon ?> <?= e($typeLabel) ?></span></span>
                </div>
                <?php foreach ($facts as $label => $value): ?>
                <div class="ref-field">
                    <span class="ref-field-label"><?= e($label) ?></span>
                    <span class="ref-field-value"><?= e($value) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (!empty($source['url'])): ?>
                <div class="ref-field">
                    <span class="ref-field-label">Source URL</span>
                    <span class="ref-field-value"><a href="<?= e($source['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($source['url']) ?></a></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-section-modern">
            <span class="detail-section-label">Default Citation (APA)</span>
            <div class="ref-citation-block"><?= e($defaultCitation) ?></div>
        </div>

        <?php $verification = Source::verificationInfo($source['verification_status'] ?? null); ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Verification Status</span>
            <div class="ref-verification-card <?= e($verification['class']) ?>">
                <span class="ref-verification-icon-lg" aria-hidden="true"><?= $verification['icon'] ?></span>
                <div class="ref-verification-body">
                    <p class="ref-verification-title"><?= e($verification['label']) ?></p>
                    <p class="ref-verification-desc-lg"><?= e($verification['description']) ?></p>
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('references') ?>" class="btn btn-secondary">&larr; All References</a>
        </div>
    </div>
</div>
