<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <a href="<?= url('archive/names') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            All Names
        </a>
        <span class="detail-banner-cat">&#128100; Tiv Name</span>
        <h1 class="detail-banner-title"><?= e($item['tiv_name']) ?></h1>
        <p class="detail-banner-sub"><?= e($item['english_meaning']) ?></p>
        <div class="detail-banner-badges">
            <span class="detail-banner-badge"><?= ucfirst(e($item['gender'])) ?></span>
            <?php if (!empty($item['pronunciation'])): ?>
            <span class="detail-banner-badge">&#127908; <?= e($item['pronunciation']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Body -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 780px;">

        <?php if (!empty($item['audio_file'])): ?>
        <div class="detail-section-modern" style="display:flex; align-items:center; gap:1rem;">
            <button type="button" class="play-pronunciation-btn" onclick="playPronunciation(this)"
                    data-audio="<?= e(AUDIO_UPLOADS_URL . '/' . $item['audio_file']) ?>"
                    style="flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="play-icon">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span>Listen to Pronunciation</span>
            </button>
            <audio id="audioPlayer" style="display:none;"></audio>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Description</span>
            <p class="detail-section-text"><?= nl2br(e($item['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['origin_story'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Origin Story</span>
            <p class="detail-section-text"><?= nl2br(e($item['origin_story'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['usage_context'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Usage Context</span>
            <p class="detail-section-text"><?= nl2br(e($item['usage_context'])) ?></p>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Related Names</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url('name/' . $relItem['id']) ?>" class="archive-card-modern archive-card-names">
                    <span class="archive-card-modern-icon">&#128100;</span>
                    <h4 class="archive-card-modern-title"><?= e($relItem['tiv_name']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['english_meaning']) ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/names') ?>" class="btn btn-secondary">&larr; All Names</a>
        </div>
    </div>
</div>

<script>
function playPronunciation(button) {
    const audioUrl = button.dataset.audio;
    const audio = document.getElementById('audioPlayer');
    const span = button.querySelector('span');
    const playIcon = button.querySelector('.play-icon');

    if (audio.src === audioUrl && !audio.paused) {
        audio.pause();
        audio.currentTime = 0;
        span.textContent = 'Listen to Pronunciation';
        playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
        return;
    }

    audio.src = audioUrl;
    audio.play().then(() => {
        span.textContent = 'Playing...';
        playIcon.innerHTML = '<rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>';
    }).catch(err => {
        span.textContent = 'Playback failed';
    });

    audio.onended = () => {
        span.textContent = 'Listen to Pronunciation';
        playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
    };
}
</script>
