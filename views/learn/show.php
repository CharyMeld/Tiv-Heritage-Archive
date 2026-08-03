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
            <span class="detail-banner-badge">&#128172; <?= number_format($commentCount) ?> comments</span>
        </div>
    </div>
</div>

<!-- Video Player + Content -->
<div class="detail-body-wrap">
    <div class="container" style="max-width: 900px;">

        <!-- Embed -->
        <?php
            $hasFacebook  = !empty($video['facebook_url']);
            $hasTiktok    = !empty($video['tiktok_id']);
            $hasRumble    = !empty($video['rumble_id']);
            $hasMultiple  = $hasFacebook || $hasTiktok || $hasRumble;

            // YouTube is always the default
            $defaultPanel = 'panel-yt';
        ?>

        <?php if ($hasMultiple): ?>
        <div class="vplatform-tabs" role="tablist" aria-label="Video platform">
            <?php if ($hasFacebook): ?>
            <button class="vplatform-tab <?= $defaultPanel === 'panel-fb' ? 'vplatform-tab--active' : '' ?>"
                    id="tab-fb" role="tab"
                    aria-selected="<?= $defaultPanel === 'panel-fb' ? 'true' : 'false' ?>"
                    aria-controls="panel-fb" data-panel="panel-fb">
                <!-- Facebook icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                Facebook
            </button>
            <?php endif; ?>

            <?php if ($hasTiktok): ?>
            <button class="vplatform-tab <?= $defaultPanel === 'panel-tiktok' ? 'vplatform-tab--active' : '' ?>"
                    id="tab-tiktok" role="tab"
                    aria-selected="<?= $defaultPanel === 'panel-tiktok' ? 'true' : 'false' ?>"
                    aria-controls="panel-tiktok" data-panel="panel-tiktok">
                <!-- TikTok icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.79 1.54V6.78a4.85 4.85 0 0 1-1.02-.09z"/></svg>
                TikTok
            </button>
            <?php endif; ?>

            <?php if ($hasRumble): ?>
            <button class="vplatform-tab <?= $defaultPanel === 'panel-rumble' ? 'vplatform-tab--active' : '' ?>"
                    id="tab-rumble" role="tab"
                    aria-selected="<?= $defaultPanel === 'panel-rumble' ? 'true' : 'false' ?>"
                    aria-controls="panel-rumble" data-panel="panel-rumble">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4zm9.5 2.5v11l7-5.5-7-5.5z"/></svg>
                Rumble
            </button>
            <?php endif; ?>

            <button class="vplatform-tab <?= $defaultPanel === 'panel-yt' ? 'vplatform-tab--active' : '' ?>"
                    id="tab-yt" role="tab"
                    aria-selected="<?= $defaultPanel === 'panel-yt' ? 'true' : 'false' ?>"
                    aria-controls="panel-yt" data-panel="panel-yt">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3.01 3.01 0 0 0-2.12-2.13C19.54 3.6 12 3.6 12 3.6s-7.54 0-9.38.47A3.01 3.01 0 0 0 .5 6.2C.04 8.05 0 12 0 12s.04 3.95.5 5.8a3.01 3.01 0 0 0 2.12 2.13C4.46 20.4 12 20.4 12 20.4s7.54 0 9.38-.47a3.01 3.01 0 0 0 2.12-2.13C23.96 15.95 24 12 24 12s-.04-3.95-.5-5.8zM9.6 15.6V8.4l6.4 3.6-6.4 3.6z"/></svg>
                YouTube
            </button>
        </div>
        <?php endif; ?>

        <!-- position:relative + aspect-ratio; panels are absolute so height is always correct -->
        <div style="position:relative; border-radius:<?= $hasMultiple ? '0 16px 16px 16px' : '16px' ?>; overflow:hidden; box-shadow:var(--shadow-md); margin-bottom:2rem; aspect-ratio:16/9; background:#000;">

            <?php if ($hasFacebook): ?>
            <div id="panel-fb" role="tabpanel" aria-labelledby="tab-fb"
                 style="position:absolute;inset:0;overflow:hidden;<?= $defaultPanel !== 'panel-fb' ? 'display:none;' : '' ?>">
                <div class="fb-video"
                     data-href="<?= e($video['facebook_url']) ?>"
                     data-width="auto"
                     data-show-text="false"
                     data-allowfullscreen="true"
                     style="width:100%;">
                </div>
            </div>
            <?php endif; ?>

            <?php if ($hasTiktok): ?>
            <div id="panel-tiktok" role="tabpanel" aria-labelledby="tab-tiktok"
                 style="position:absolute;inset:0;<?= $defaultPanel !== 'panel-tiktok' ? 'display:none;' : '' ?>">
                <iframe
                    src="https://www.tiktok.com/embed/v2/<?= e($video['tiktok_id']) ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    style="width:100%;height:100%;display:block;">
                </iframe>
            </div>
            <?php endif; ?>

            <?php if ($hasRumble): ?>
            <div id="panel-rumble" role="tabpanel" aria-labelledby="tab-rumble"
                 style="position:absolute;inset:0;<?= $defaultPanel !== 'panel-rumble' ? 'display:none;' : '' ?>">
                <iframe
                    src="https://rumble.com/embed/<?= e($video['rumble_id']) ?>/"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    style="width:100%;height:100%;display:block;">
                </iframe>
            </div>
            <?php endif; ?>

            <div id="panel-yt" role="tabpanel" aria-labelledby="tab-yt"
                 style="position:absolute;inset:0;<?= $defaultPanel !== 'panel-yt' ? 'display:none;' : '' ?>">
                <iframe
                    id="ytPlayer"
                    src="https://www.youtube.com/embed/<?= e($video['youtube_id']) ?>?rel=0&enablejsapi=1&origin=<?= urlencode(SITE_URL) ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    style="width:100%;height:100%;display:block;">
                </iframe>
            </div>

        </div>

        <!-- ═══════════════════════════════════════════
             INLINE ACTION BAR: Like · Comments · Watch on YouTube
        ════════════════════════════════════════════ -->
        <div class="vaction-bar">

            <!-- Like -->
            <button
                id="vLikeBtn"
                class="vaction-btn<?= $hasReacted ? ' vaction-btn--liked' : '' ?>"
                data-url="<?= e(url('learn/' . $video['id'] . '/react')) ?>"
                data-token="<?= csrf_token() ?>"
                aria-pressed="<?= $hasReacted ? 'true' : 'false' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                     fill="<?= $hasReacted ? 'currentColor' : 'none' ?>" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="vLikeIcon">
                    <path d="M7 10v12M15 5.88L14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/>
                </svg>
                <span id="vLikeCount"><?= number_format($reactionCount) ?></span>
                <span id="vLikeLabel"><?= $hasReacted ? 'Liked' : 'Like' ?></span>
            </button>

            <!-- Comment trigger -->
            <button type="button"
                    id="vCommentTrigger"
                    class="vaction-btn"
                    aria-expanded="false" aria-controls="vCommentForm">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Comments <span class="vaction-count" id="vCommentCountBadge"><?= $commentCount ?></span>
            </button>

        </div><!-- /.vaction-bar -->

        <?php if (ADSENSE_ENABLED): ?>
        <!-- Ad 1: below Like/Comments, above description -->
        <div class="adsense-wrap">
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-7960622250292703"
                 data-ad-slot="6111588136"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
        </div>
        <?php endif; ?>

        <!-- Comment form + list — hidden until Comments button clicked -->
        <div class="vcomments-section" id="vComments">

            <form class="vcomments-form vcomments-form--hidden" id="vCommentForm"
                  data-url="<?= e(url('learn/' . $video['id'] . '/comment')) ?>"
                  data-token="<?= csrf_token() ?>">

                <?php if (!$user): ?>
                <input type="text" class="vcomments-name-input" id="vCommentName"
                       name="name" placeholder="Your name (required)" maxlength="100" required>
                <?php endif; ?>

                <div class="vcomments-textarea-wrap">
                    <textarea class="vcomments-textarea" id="vCommentBody" name="body"
                              placeholder="Share your thoughts on this lesson…"
                              rows="3" maxlength="2000" required></textarea>
                    <div class="vcomments-form-actions">
                        <span class="vcomments-charcount" id="vCharCount">0 / 2000</span>
                        <div style="display:flex;gap:.5rem;align-items:center;">
                            <button type="button" class="vcomments-cancel" id="vCommentCancel">Cancel</button>
                            <button type="submit" class="vcomments-submit" id="vCommentSubmit">Post Comment</button>
                        </div>
                    </div>
                </div>

                <div class="vcomments-yt-hint" id="vYtHint" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                    Comment posted! Want it on YouTube too?
                    <button type="button" class="vcomments-yt-copy" id="vYtCopyBtn">Copy &amp; open YouTube</button>
                </div>
            </form>

            <div class="vcomments-list" id="vCommentsList">
                <?php if (empty($comments)): ?>
                <p class="vcomments-empty" id="vCommentsEmpty">Be the first to comment on this lesson.</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                    <div class="vcomment-item">
                        <div class="vcomment-avatar vcomment-avatar--initials" aria-hidden="true">
                            <?= strtoupper(mb_substr($c['user_name'] ?? $c['guest_name'] ?? '?', 0, 1)) ?>
                        </div>
                        <div class="vcomment-body">
                            <div class="vcomment-meta">
                                <strong class="vcomment-name">
                                    <?= e($c['user_name'] ?? $c['guest_name'] ?? 'Anonymous') ?>
                                </strong>
                                <span class="vcomment-date">
                                    <?= date('M j, Y', strtotime($c['created_at'])) ?>
                                </span>
                            </div>
                            <p class="vcomment-text"><?= nl2br(e($c['body'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div><!-- /.vcomments-section -->

        <?php if (!empty($video['description'])): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">About This Lesson</span>
            <p class="detail-section-text"><?= nl2br(e($video['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (ADSENSE_ENABLED): ?>
        <!-- Ad 2: between description and share bar -->
        <div class="adsense-wrap">
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-7960622250292703"
                 data-ad-slot="6111588136"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
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

<script>
/* YouTube IFrame API — count a view only when the video actually starts playing */
(function () {
    var viewRecorded = false;
    var recordUrl    = '<?= url('learn/' . $video['id'] . '/view') ?>';
    var csrfToken    = '<?= csrf_token() ?>';

    /* Load YouTube IFrame API asynchronously */
    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);

    /* Called automatically by the API once the script loads */
    window.onYouTubeIframeAPIReady = function () {
        new YT.Player('ytPlayer', {
            events: { onStateChange: onStateChange }
        });
    };

    function onStateChange(event) {
        /* YT.PlayerState.PLAYING === 1 */
        if (!viewRecorded && event.data === 1) {
            viewRecorded = true;
            fetch(recordUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ _token: csrfToken })
            });
        }
    }
}());
</script>

<script>
/* ── Like button ── */
(function () {
    var btn       = document.getElementById('vLikeBtn');
    var countEl   = document.getElementById('vLikeCount');
    var labelEl   = document.getElementById('vLikeLabel');
    var iconEl    = document.getElementById('vLikeIcon');
    if (!btn) return;

    btn.addEventListener('click', function () {
        btn.disabled = true;
        fetch(btn.dataset.url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ _token: btn.dataset.token })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.ok) return;
            btn.disabled = false;
            countEl.textContent = d.count.toLocaleString();
            if (d.liked) {
                btn.classList.add('vaction-btn--liked');
                btn.setAttribute('aria-pressed', 'true');
                labelEl.textContent = 'Liked';
                iconEl.setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('vaction-btn--liked');
                btn.setAttribute('aria-pressed', 'false');
                labelEl.textContent = 'Like';
                iconEl.setAttribute('fill', 'none');
            }
        })
        .catch(function () { btn.disabled = false; });
    });
}());

/* ── Comment form (expand on click, collapse on cancel) ── */
(function () {
    var trigger   = document.getElementById('vCommentTrigger');
    var form      = document.getElementById('vCommentForm');
    var cancelBtn = document.getElementById('vCommentCancel');
    var textarea  = document.getElementById('vCommentBody');
    var charCount = document.getElementById('vCharCount');
    var list      = document.getElementById('vCommentsList');
    var empty     = document.getElementById('vCommentsEmpty');
    var badge     = document.getElementById('vCommentCountBadge');
    var hint      = document.getElementById('vYtHint');
    var copyBtn   = document.getElementById('vYtCopyBtn');
    var ytUrl     = 'https://www.youtube.com/watch?v=<?= e($video['youtube_id']) ?>';
    var lastComment = '';

    if (!form) return;

    function expand() {
        form.classList.remove('vcomments-form--hidden');
        trigger.setAttribute('aria-expanded', 'true');
        trigger.classList.add('vaction-btn--open');
        textarea.focus();
    }

    function collapse() {
        form.classList.add('vcomments-form--hidden');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.classList.remove('vaction-btn--open');
        textarea.value = '';
        charCount.textContent = '0 / 2000';
        var nameEl = document.getElementById('vCommentName');
        if (nameEl) nameEl.value = '';
        if (hint) hint.style.display = 'none';
    }

    trigger.addEventListener('click', expand);
    cancelBtn.addEventListener('click', collapse);

    textarea.addEventListener('input', function () {
        charCount.textContent = textarea.value.length + ' / 2000';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var submit = document.getElementById('vCommentSubmit');
        var nameEl = document.getElementById('vCommentName');
        var body   = textarea.value.trim();
        var name   = nameEl ? nameEl.value.trim() : '';

        if (body.length < 2) return;
        submit.disabled = true;
        submit.textContent = 'Posting…';

        var payload = { _token: form.dataset.token, body: body };
        if (nameEl) payload.name = name;

        fetch(form.dataset.url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            submit.disabled = false;
            submit.textContent = 'Post Comment';
            if (!d.ok) {
                alert(d.error || 'Could not post comment. Please try again.');
                return;
            }

            lastComment = body;

            if (empty) { empty.remove(); }
            var c = d.comment;
            var initial = (c.name || '?').charAt(0).toUpperCase();
            var item = document.createElement('div');
            item.className = 'vcomment-item vcomment-item--new';
            item.innerHTML =
                '<div class="vcomment-avatar vcomment-avatar--initials" aria-hidden="true">' + initial + '</div>' +
                '<div class="vcomment-body">' +
                  '<div class="vcomment-meta">' +
                    '<strong class="vcomment-name">' + escHtml(c.name) + '</strong>' +
                    '<span class="vcomment-date">Just now</span>' +
                  '</div>' +
                  '<p class="vcomment-text">' + escHtml(body).replace(/\n/g, '<br>') + '</p>' +
                '</div>';
            list.appendChild(item);

            var cur = parseInt(badge.textContent, 10) || 0;
            badge.textContent = cur + 1;

            textarea.value = '';
            if (nameEl) nameEl.value = '';
            charCount.textContent = '0 / 2000';
            collapse();

            if (hint) hint.style.display = 'flex';
        })
        .catch(function () {
            submit.disabled = false;
            submit.textContent = 'Post Comment';
            alert('Network error. Please try again.');
        });
    });

    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            if (navigator.clipboard && lastComment) {
                navigator.clipboard.writeText(lastComment).then(function () {
                    window.open(ytUrl, '_blank', 'noopener');
                    hint.style.display = 'none';
                });
            } else {
                window.open(ytUrl, '_blank', 'noopener');
                hint.style.display = 'none';
            }
        });
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}());
</script>

<?php if ($hasFacebook): ?>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous"
    src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0">
</script>
<?php endif; ?>

<?php if ($hasMultiple): ?>
<script>
(function () {
    var tabs = document.querySelectorAll('.vplatform-tab');
    if (!tabs.length) return;
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var panelId = tab.dataset.panel;

            tabs.forEach(function (t) {
                t.classList.toggle('vplatform-tab--active', t === tab);
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });

            document.querySelectorAll('[role="tabpanel"]').forEach(function (panel) {
                panel.style.display = panel.id === panelId ? '' : 'none';
            });
        });
    });
}());
</script>
<?php endif; ?>
