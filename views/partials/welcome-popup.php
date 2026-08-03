<div id="welcomePopupOverlay" class="welcome-popup-overlay" hidden>
    <div class="welcome-popup" role="dialog" aria-modal="true" aria-labelledby="welcomePopupTitle">
        <button type="button" class="welcome-popup-close" id="welcomePopupClose" aria-label="Close">&times;</button>

        <div class="welcome-popup-body">
            <img src="<?= asset('images/logo.png') ?>" alt="<?= SITE_NAME ?>" class="welcome-popup-logo">
            <h2 id="welcomePopupTitle" class="welcome-popup-title">Welcome to Tiv Heritage Archive</h2>
            <p class="welcome-popup-intro">
                Discover the rich history, language, traditions, names, proverbs, culture, historical figures,
                and heritage of the Tiv people&mdash;all in one place.
            </p>

            <p class="welcome-popup-lead">Stay connected by subscribing to our Newsletter and receive:</p>
            <ul class="welcome-popup-list">
                <li>Newly added cultural content</li>
                <li>Tiv language lessons</li>
                <li>Historical discoveries</li>
                <li>Proverbs and traditional wisdom</li>
                <li>Festival and cultural event updates</li>
                <li>Website feature announcements</li>
                <li>Research publications</li>
                <li>Community news</li>
            </ul>

            <form id="welcomePopupForm" class="welcome-popup-form" novalidate>
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <div class="welcome-popup-field">
                    <label for="welcomePopupName">Full Name <span class="welcome-popup-optional">(optional)</span></label>
                    <input type="text" id="welcomePopupName" name="name" autocomplete="name" placeholder="Your name">
                </div>
                <div class="welcome-popup-field">
                    <label for="welcomePopupEmail">Email Address <span class="welcome-popup-required">*</span></label>
                    <input type="email" id="welcomePopupEmail" name="email" autocomplete="email" required placeholder="you@example.com">
                </div>

                <div class="welcome-popup-status" id="welcomePopupStatus" role="status" aria-live="polite" hidden></div>

                <div class="welcome-popup-actions">
                    <button type="submit" class="welcome-popup-btn welcome-popup-btn-primary" id="welcomePopupSubmit">
                        Subscribe &amp; Continue
                    </button>
                    <button type="button" class="welcome-popup-btn welcome-popup-btn-secondary" id="welcomePopupSkip">
                        Continue Without Subscribing
                    </button>
                </div>

                <p class="welcome-popup-fineprint">
                    Your email will only be used for Tiv Heritage Archive updates and you may unsubscribe at any time.
                </p>
            </form>

            <?php if (!empty(WELCOME_POPUP_CHANNELS)): ?>
            <div class="welcome-popup-channels">
                <p class="welcome-popup-channels-label">Or connect with us</p>
                <div class="welcome-popup-channels-row">
                    <?php foreach (WELCOME_POPUP_CHANNELS as $key => $channel): ?>
                    <a href="<?= e($channel['url']) ?>" target="_blank" rel="noopener"
                       class="welcome-popup-channel-btn" data-channel="<?= e($key) ?>" aria-label="<?= e($channel['label']) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="<?= e($channel['icon']) ?>"/></svg>
                        <span><?= e($channel['label']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
