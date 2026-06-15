<style>
.bilingual-display {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border: 1px solid #e5e0d5;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 2rem;
}
.bilingual-display-col {
    padding: 1.4rem 1.6rem;
}
.bilingual-display-col:first-child {
    border-right: 1px solid #e5e0d5;
    background: #fdfcfb;
}
.bilingual-display-col:last-child {
    background: #fdf8f0;
}
.bilingual-display-heading {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin: 0 0 1rem;
    padding-bottom: .5rem;
    border-bottom: 2px solid #e5e0d5;
}
.bilingual-display-col:first-child .bilingual-display-heading { color: #2d5a9e; border-color: #2d5a9e44; }
.bilingual-display-col:last-child  .bilingual-display-heading { color: #5C3A21; border-color: #5C3A2144; }
.bilingual-text {
    font-size: 1rem;
    line-height: 1.85;
    color: #2d1b0e;
    white-space: pre-line;
}
@media (max-width: 640px) {
    .bilingual-display { grid-template-columns: 1fr; }
    .bilingual-display-col:first-child { border-right: none; border-bottom: 1px solid #e5e0d5; }
}
</style>

<!-- Breadcrumb -->
<div style="background:#faf8f5;border-bottom:1px solid #e5e0d5;padding:.6rem 0;">
    <div class="container" style="font-size:.83rem;color:#7a6a5a;">
        <a href="<?= url('/') ?>" style="color:#5C3A21;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url($section) ?>" style="color:#5C3A21;text-decoration:none;"><?= htmlspecialchars($sectionConfig['title'] ?? ucfirst($section)) ?></a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url($section . '/' . $sub) ?>" style="color:#5C3A21;text-decoration:none;"><?= htmlspecialchars($subConfig['label']) ?></a>
        <span style="margin:0 .4rem;">›</span>
        <span><?= htmlspecialchars(mb_substr($item['title'], 0, 50)) ?><?= mb_strlen($item['title']) > 50 ? '…' : '' ?></span>
    </div>
</div>

<article style="padding:2rem 0 4rem;">
    <div class="container" style="max-width:1000px;">

        <!-- Title block — bilingual -->
        <header style="margin-bottom:1.8rem;">
            <?php if ($item['is_featured']): ?>
                <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#C8A951;display:block;margin-bottom:.4rem;">&#11088; Featured</span>
            <?php endif; ?>

            <?php if (!empty($item['tiv_title'])): ?>
                <!-- Both titles present: show side by side -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:.6rem;" class="bilingual-title-row">
                    <div>
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#2d5a9e;margin-bottom:.3rem;">English</div>
                        <h1 style="font-size:1.8rem;font-weight:700;color:#2d1b0e;margin:0;line-height:1.25;"><?= htmlspecialchars($item['title']) ?></h1>
                    </div>
                    <div>
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#5C3A21;margin-bottom:.3rem;">Tiv</div>
                        <h1 style="font-size:1.8rem;font-weight:700;color:#5C3A21;margin:0;line-height:1.25;font-style:italic;"><?= htmlspecialchars($item['tiv_title']) ?></h1>
                    </div>
                </div>
            <?php else: ?>
                <h1 style="font-size:1.8rem;font-weight:700;color:#2d1b0e;margin:0 0 .6rem;line-height:1.25;"><?= htmlspecialchars($item['title']) ?></h1>
            <?php endif; ?>

            <div style="display:flex;gap:1rem;flex-wrap:wrap;font-size:.8rem;color:#7a6a5a;margin-top:.6rem;">
                <span><?= htmlspecialchars($subConfig['label']) ?></span>
                <span>&#128197; <?= date('F j, Y', strtotime($item['created_at'])) ?></span>
                <?php if ($item['view_count'] > 0): ?>
                    <span>&#128065; <?= number_format($item['view_count']) ?> views</span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Media -->
        <?php if (!empty($item['media_file'])): ?>
            <div style="margin-bottom:1.8rem;">
                <?php if ($item['media_type'] === 'image'): ?>
                    <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         style="width:100%;max-height:460px;object-fit:cover;border-radius:12px;border:1px solid #e5e0d5;">
                <?php elseif ($item['media_type'] === 'audio'): ?>
                    <div style="background:#faf8f5;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;display:flex;align-items:center;gap:1rem;">
                        <span style="font-size:2rem;">&#127911;</span>
                        <audio controls style="flex:1;min-width:0;">
                            <source src="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>">
                            Your browser does not support audio playback.
                        </audio>
                    </div>
                <?php elseif ($item['media_type'] === 'document'): ?>
                    <a href="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>" target="_blank"
                       class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:.5rem;">
                        &#128196; Download Document
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Excerpt / Summary — bilingual -->
        <?php $hasEnExcerpt = !empty($item['excerpt']); $hasTivExcerpt = !empty($item['tiv_excerpt']); ?>
        <?php if ($hasEnExcerpt || $hasTivExcerpt): ?>
            <div class="bilingual-display" style="margin-bottom:1.8rem;">
                <?php if ($hasEnExcerpt): ?>
                    <div class="bilingual-display-col">
                        <p class="bilingual-display-heading">&#127760; English Summary</p>
                        <p style="font-size:1rem;color:#3a2a1a;line-height:1.75;margin:0;font-style:italic;"><?= htmlspecialchars($item['excerpt']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($hasTivExcerpt): ?>
                    <div class="bilingual-display-col" <?= !$hasEnExcerpt ? 'style="grid-column:1/-1;"' : '' ?>>
                        <p class="bilingual-display-heading">&#127981; Sha u Tiv</p>
                        <p style="font-size:1rem;color:#3a2a1a;line-height:1.75;margin:0;font-style:italic;"><?= htmlspecialchars($item['tiv_excerpt']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Main content — bilingual side by side -->
        <?php $hasEnContent = !empty($item['content']); $hasTivContent = !empty($item['tiv_content']); ?>
        <?php if ($hasEnContent || $hasTivContent): ?>
            <div class="bilingual-display">
                <?php if ($hasEnContent): ?>
                    <div class="bilingual-display-col">
                        <p class="bilingual-display-heading">&#127760; English</p>
                        <div class="bilingual-text"><?= htmlspecialchars($item['content']) ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($hasTivContent): ?>
                    <div class="bilingual-display-col" <?= !$hasEnContent ? 'style="grid-column:1/-1;"' : '' ?>>
                        <p class="bilingual-display-heading">&#127981; U Tiv</p>
                        <div class="bilingual-text"><?= htmlspecialchars($item['tiv_content']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Back / CTAs -->
        <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #e5e0d5;display:flex;gap:.8rem;flex-wrap:wrap;">
            <a href="<?= url($section . '/' . $sub) ?>" class="btn btn-secondary">
                &larr; Back to <?= htmlspecialchars($subConfig['label']) ?>
            </a>
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Contribute</a>
            <a href="<?= url('community/join') ?>" class="btn btn-secondary">&#128101; Join Community</a>
        </div>

    </div>
</article>

<style>
@media (max-width: 640px) {
    .bilingual-title-row { grid-template-columns: 1fr !important; }
}
</style>
