<!-- Cinematic Detail Banner -->
<div class="detail-banner">
    <div class="container">
        <a href="<?= url('archive/words') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Dictionary
        </a>
        <span class="detail-banner-cat">&#128172; Tiv Word</span>
        <h1 class="detail-banner-title"><?= e($item['tiv_word']) ?></h1>
        <p class="detail-banner-sub"><?= e($item['english_meaning']) ?></p>
        <div class="detail-banner-badges">
            <?php if (!empty($item['part_of_speech'])): ?>
            <span class="detail-banner-badge"><?= ucfirst(e($item['part_of_speech'])) ?></span>
            <?php endif; ?>
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

        <?php if (!empty($item['example_tiv']) || !empty($item['example_english'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Example Usage</span>
            <?php if (!empty($item['example_tiv'])): ?>
            <div class="detail-quote-block" style="margin-bottom: 0.75rem;">
                <?= e($item['example_tiv']) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($item['example_english'])): ?>
            <p class="detail-section-text" style="font-style: italic; color: var(--color-text-muted);">
                "<?= e($item['example_english']) ?>"
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php include BASE_PATH . '/views/partials/source-and-links.php'; ?>

        <?php if (!empty($related)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">Related Words</h3>
            <div class="archive-grid-modern">
                <?php foreach ($related as $relItem): ?>
                <a href="<?= url('word/' . $relItem['id']) ?>" class="archive-card-modern archive-card-words">
                    <span class="archive-card-modern-icon">&#128172;</span>
                    <h4 class="archive-card-modern-title"><?= e($relItem['tiv_word']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($relItem['english_meaning']) ?></p>
                    <?php if (!empty($relItem['part_of_speech'])): ?>
                    <span style="font-size: 0.7rem; color: var(--color-text-muted); font-family: var(--font-ui);"><?= ucfirst(e($relItem['part_of_speech'])) ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('archive/words') ?>" class="btn btn-secondary">&larr; Dictionary</a>
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
