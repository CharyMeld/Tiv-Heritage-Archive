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
            <?php if (!empty($item['ipa'])): ?>
            <span class="detail-banner-badge" style="font-family:monospace;"><?= e($item['ipa']) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['tone'])): ?>
            <span class="detail-banner-badge">&#127925; <?= e($item['tone']) ?> tone</span>
            <?php endif; ?>
            <?php if (!empty($item['category'])): ?>
            <span class="detail-banner-badge"><?= e(ucfirst($item['category'])) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['frequency'])): ?>
            <span class="detail-banner-badge"><?= e(ucwords(str_replace('_', ' ', $item['frequency']))) ?></span>
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

        <?php if (!empty($item['literal_meaning']) || !empty($item['figurative_meaning'])): ?>
        <div class="detail-section-modern">
            <?php if (!empty($item['literal_meaning'])): ?>
            <span class="detail-section-label">Literal Meaning</span>
            <p class="detail-section-text"><?= e($item['literal_meaning']) ?></p>
            <?php endif; ?>
            <?php if (!empty($item['figurative_meaning'])): ?>
            <span class="detail-section-label" style="margin-top:.75rem;display:block;">Figurative Meaning</span>
            <p class="detail-section-text"><?= e($item['figurative_meaning']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['usage_notes']) || !empty($item['dialect_region'])): ?>
        <div class="detail-section-modern">
            <?php if (!empty($item['usage_notes'])): ?>
            <span class="detail-section-label">Usage Notes</span>
            <p class="detail-section-text"><?= e($item['usage_notes']) ?></p>
            <?php endif; ?>
            <?php if (!empty($item['dialect_region'])): ?>
            <span class="detail-section-label" style="margin-top:.75rem;display:block;">Dialect / Region</span>
            <p class="detail-section-text"><?= e($item['dialect_region']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($rootWord) || !empty($derivedWords)): ?>
        <div class="detail-section-modern">
            <?php if (!empty($rootWord)): ?>
            <span class="detail-section-label">Root Word</span>
            <p class="detail-section-text">
                <a href="<?= url('word/' . $rootWord['id']) ?>"><strong><?= e($rootWord['tiv_word']) ?></strong> — <?= e($rootWord['english_meaning']) ?></a>
            </p>
            <?php endif; ?>
            <?php if (!empty($derivedWords)): ?>
            <span class="detail-section-label" style="margin-top:.75rem;display:block;">Derived Words</span>
            <div class="archive-grid-modern">
                <?php foreach ($derivedWords as $dw): ?>
                <a href="<?= url('word/' . $dw['id']) ?>" class="archive-card-modern archive-card-words">
                    <span class="archive-card-modern-icon">&#128172;</span>
                    <h4 class="archive-card-modern-title"><?= e($dw['tiv_word']) ?></h4>
                    <p class="archive-card-modern-sub"><?= e($dw['english_meaning']) ?></p>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($wordRelations)): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Synonyms &amp; Related Words</span>
            <?php
            $grouped = ['synonym' => [], 'antonym' => [], 'see_also' => []];
            foreach ($wordRelations as $rel) { $grouped[$rel['relation']][] = $rel['item']; }
            $groupLabels = ['synonym' => 'Synonyms', 'antonym' => 'Antonyms', 'see_also' => 'See Also'];
            ?>
            <?php foreach ($groupLabels as $key => $label): if (empty($grouped[$key])) continue; ?>
            <p class="detail-section-text" style="margin-top:.5rem;">
                <strong><?= $label ?>:</strong>
                <?php foreach ($grouped[$key] as $i => $w): ?><?= $i ? ', ' : '' ?><a href="<?= url('word/' . $w['id']) ?>"><?= e($w['tiv_word']) ?></a><?php endforeach; ?>
            </p>
            <?php endforeach; ?>
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
