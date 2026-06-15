<!-- Cinematic Video Banner -->
<div class="detail-banner" style="padding-bottom: 2rem;">
    <div class="container">
        <a href="<?= url('learn') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Lessons
        </a>
        <span class="detail-banner-cat">&#127891; Learn Tiv Language</span>
        <h1 class="detail-banner-title"><?= e($video['title']) ?></h1>
        <div class="detail-banner-badges">
            <?php if (!empty($video['category'])): ?>
            <span class="detail-banner-badge"><?= e($video['category']) ?></span>
            <?php endif; ?>
            <span class="detail-banner-badge difficulty-<?= e($video['difficulty']) ?>"><?= ucfirst(e($video['difficulty'])) ?></span>
            <?php if (!empty($video['duration'])): ?>
            <span class="detail-banner-badge">&#9201; <?= e($video['duration']) ?></span>
            <?php endif; ?>
            <span class="detail-banner-badge">&#128065; <?= number_format($video['view_count']) ?> views</span>
        </div>
    </div>
</div>

<!-- Video Player + Content -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 900px;">

        <!-- Embed -->
        <div style="border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-md); margin-bottom: 2rem; aspect-ratio: 16/9; background: #000;">
            <iframe
                src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>?rel=0"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                style="width: 100%; height: 100%; display: block;">
            </iframe>
        </div>

        <?php if (!empty($video['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">About This Lesson</span>
            <p class="detail-section-text"><?= nl2br(e($video['description'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════
             SHARE BAR
        ════════════════════════════════════════════ -->
        <?php
            $shareUrl   = urlencode(url('learn/' . $video['id']));
            $shareTitle = urlencode($video['title'] . ' — Learn Tiv Language');
            $shareDesc  = urlencode(!empty($video['description'])
                            ? mb_substr(strip_tags($video['description']), 0, 120) . '…'
                            : 'Watch this Tiv language lesson — free video lessons from beginner to advanced.');
            $thumbUrl   = 'https://img.youtube.com/vi/' . e($video['youtube_id']) . '/hqdefault.jpg';
        ?>
        <div class="vshare-block">
            <div class="vshare-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/>
                    <circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/>
                    <line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>
                </svg>
                Share this lesson
            </div>

            <!-- Preview card (how it looks when shared) -->
            <div class="vshare-preview">
                <img class="vshare-preview-thumb" src="<?= $thumbUrl ?>" alt="Video thumbnail">
                <div class="vshare-preview-body">
                    <span class="vshare-preview-site">tivheritageArchive.com</span>
                    <p class="vshare-preview-title"><?= e($video['title']) ?> — Learn Tiv Language</p>
                    <?php if (!empty($video['description'])): ?>
                    <p class="vshare-preview-desc"><?= e(mb_substr($video['description'], 0, 110)) ?>…</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Share buttons -->
            <div class="vshare-btns">

                <!-- Facebook -->
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>"
                   target="_blank" rel="noopener" class="vshare-btn vshare-btn--fb" aria-label="Share on Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>
                    <span>Facebook</span>
                </a>

                <!-- X / Twitter -->
                <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>"
                   target="_blank" rel="noopener" class="vshare-btn vshare-btn--x" aria-label="Share on X">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    <span>X (Twitter)</span>
                </a>

                <!-- WhatsApp -->
                <a href="https://wa.me/?text=<?= $shareTitle ?>%20-%20<?= $shareUrl ?>"
                   target="_blank" rel="noopener" class="vshare-btn vshare-btn--wa" aria-label="Share on WhatsApp">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                    </svg>
                    <span>WhatsApp</span>
                </a>

                <!-- Telegram -->
                <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>"
                   target="_blank" rel="noopener" class="vshare-btn vshare-btn--tg" aria-label="Share on Telegram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                    <span>Telegram</span>
                </a>

                <!-- LinkedIn -->
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>"
                   target="_blank" rel="noopener" class="vshare-btn vshare-btn--li" aria-label="Share on LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/>
                        <circle cx="4" cy="4" r="2"/>
                    </svg>
                    <span>LinkedIn</span>
                </a>

                <!-- Copy Link -->
                <button class="vshare-btn vshare-btn--copy" id="vShareCopy"
                        data-url="<?= e(url('learn/' . $video['id'])) ?>"
                        aria-label="Copy link">
                    <svg id="vShareCopyIcon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                    </svg>
                    <span id="vShareCopyLabel">Copy Link</span>
                </button>

            </div>
        </div>

        <!-- Related Videos -->
        <?php if (!empty($relatedVideos)): ?>
        <div style="margin-top: 2.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem;">
                More <?= e($video['category']) ?> Lessons
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                <?php foreach ($relatedVideos as $related): ?>
                <a href="<?= url('learn/' . $related['id']) ?>" class="home-video-card" style="text-decoration: none;">
                    <div class="home-video-thumb">
                        <img src="https://img.youtube.com/vi/<?= e($related['youtube_id']) ?>/mqdefault.jpg"
                             alt="<?= e($related['title']) ?>" loading="lazy">
                        <div class="home-video-play">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </div>
                        <?php if (!empty($related['duration'])): ?>
                        <span class="home-video-duration"><?= e($related['duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="home-video-info">
                        <h4 class="home-video-title"><?= e($related['title']) ?></h4>
                        <span class="home-video-diff difficulty-<?= e($related['difficulty']) ?>">
                            <?= ucfirst(e($related['difficulty'])) ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-light);">
            <a href="<?= url('learn') ?>" class="btn btn-secondary">&larr; All Lessons</a>
        </div>
    </div>
</div>

<script>
(function () {
    var btn   = document.getElementById('vShareCopy');
    var label = document.getElementById('vShareCopyLabel');
    var icon  = document.getElementById('vShareCopyIcon');
    if (!btn) return;

    btn.addEventListener('click', function () {
        var text = btn.dataset.url;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(done);
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            done();
        }
    });

    function done() {
        btn.classList.add('vshare-btn--copied');
        label.textContent = 'Copied!';
        icon.innerHTML = '<path d="M20 6 9 17l-5-5"/>';
        icon.setAttribute('stroke-linecap','round');
        icon.setAttribute('stroke-linejoin','round');
        setTimeout(function () {
            btn.classList.remove('vshare-btn--copied');
            label.textContent = 'Copy Link';
            icon.innerHTML = '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>';
        }, 2500);
    }
})();
</script>
