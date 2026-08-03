<!-- Cinematic Banner -->
<div class="detail-banner" style="padding-bottom: 3rem;">
    <div class="container">
        <span class="detail-banner-cat">&#128218; Knowledge Sources</span>
        <h1 class="detail-banner-title">References &amp; Contributors</h1>
        <p class="detail-banner-sub">
            Every item in this archive stands on the shoulders of oral historians, researchers,
            elders, and community members who shared their knowledge.
        </p>
        <div class="detail-banner-badges" style="margin-top: 1.25rem;">
            <span class="detail-banner-badge">&#128218; <?= number_format($total) ?> Sources &amp; Contributors</span>
            <?php foreach ($typeLabels as $key => $label): ?>
                <?php if (!empty($grouped[$key])): ?>
                <span class="detail-banner-badge"><?= $typeIcons[$key] ?> <?= count($grouped[$key]) ?> <?= e($label) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- References Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 900px;">

        <?php if (empty($grouped)): ?>
        <div style="text-align:center; padding: 4rem 0;">
            <p style="font-size:3rem;">&#128218;</p>
            <p style="color: var(--color-text-muted);">No sources have been added yet. Add them via the Admin panel.</p>
        </div>
        <?php else: ?>

        <?php foreach ($typeLabels as $typeKey => $typeLabel):
            if (empty($grouped[$typeKey])) continue;
            $sources = $grouped[$typeKey];
            $icon    = $typeIcons[$typeKey];
        ?>
        <!-- ── Section ── -->
        <div class="ref-section" id="ref-<?= $typeKey ?>">
            <div class="ref-section-header">
                <span class="ref-section-icon"><?= $icon ?></span>
                <h2 class="ref-section-title"><?= e($typeLabel) ?></h2>
                <span class="ref-section-count"><?= count($sources) ?></span>
            </div>

            <div class="ref-cards">
                <?php foreach ($sources as $source): ?>
                <a href="<?= url('references/' . $source['id']) ?>" class="ref-card ref-card-<?= $typeKey ?>" style="display:block;text-decoration:none;color:inherit;">
                    <div class="ref-card-top">
                        <div class="ref-card-avatar">
                            <?php
                            $name = $source['contributor_name'] ?? $source['author'] ?? '?';
                            echo strtoupper(mb_substr($name, 0, 1));
                            ?>
                        </div>
                        <div class="ref-card-head">
                            <h3 class="ref-card-name">
                                <?= e($source['contributor_name'] ?? $source['author'] ?? 'Unknown') ?>
                            </h3>
                            <?php if (!empty($source['title'])): ?>
                            <p class="ref-card-work"><?= e($source['title']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ref-card-meta">
                        <?php if (!empty($source['location'])): ?>
                        <span class="ref-meta-chip">&#128205; <?= e($source['location']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($source['year_recorded'])): ?>
                        <span class="ref-meta-chip">&#128197; <?= e($source['year_recorded']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($source['author']) && $source['author'] !== $source['contributor_name']): ?>
                        <span class="ref-meta-chip">&#9997; <?= e($source['author']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php $verification = Source::verificationInfo($source['verification_status'] ?? null); ?>
                    <div class="ref-verification-badge ref-verification-badge--sm <?= e($verification['class']) ?>">
                        <span class="ref-verification-icon" aria-hidden="true"><?= $verification['icon'] ?></span>
                        <span class="ref-verification-label"><?= e($verification['label']) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php endif; ?>

        <!-- How to Cite -->
        <div class="detail-section-modern" style="margin-top: 3rem;">
            <span class="detail-section-label">How to Cite This Archive</span>
            <p class="detail-section-text">
                <em>Tiv Heritage Archive</em>. (<?= date('Y') ?>). Tiv Cultural Knowledge Archive [Online Database].
                Retrieved from <?= SITE_URL ?>
            </p>
        </div>

        <!-- Call to contribute -->
        <div class="explore-cta" style="margin-top: 3rem; border-radius: 16px;">
            <div>
                <h2 class="explore-cta-title" style="font-size: 1.4rem;">Become a Contributor</h2>
                <p class="explore-cta-text" style="font-size: 0.95rem;">
                    Share your knowledge of Tiv culture and be recognised as an official contributor to this archive.
                </p>
                <a href="<?= url('contribute') ?>" class="btn btn-primary">Contribute Now</a>
            </div>
        </div>

    </div>
</div>
