<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?= SITE_URL ?>">
    <title><?= e($title ?? SITE_NAME) ?></title>
    <meta name="description" content="<?= e($description ?? SITE_TAGLINE) ?>">
    <meta name="google-adsense-account" content="ca-pub-7960622250292703">
    <?php if (GSC_VERIFICATION_CODE !== ''): ?>
    <meta name="google-site-verification" content="<?= e(GSC_VERIFICATION_CODE) ?>">
    <?php endif; ?>
    <?php if (ADSENSE_ENABLED): ?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7960622250292703" crossorigin="anonymous"></script>
    <?php endif; ?>
    <!-- Theme color for PWA -->
    <meta name="theme-color" content="#5C3A21">
    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($description ?? SITE_TAGLINE) ?>">
    <meta property="og:type" content="<?= isset($ogType) ? e($ogType) : 'website' ?>">
    <meta property="og:url" content="<?= isset($ogUrl) ? e($ogUrl) : SITE_URL ?>">
    <meta property="og:site_name" content="<?= SITE_NAME ?>">
    <?php $ogImageFinal = !empty($ogImage) ? $ogImage : SITE_URL . '/favicon.png'; ?>
    <meta property="og:image" content="<?= e($ogImageFinal) ?>">
    <meta property="og:image:width" content="<?= !empty($ogImage) ? '480' : '512' ?>">
    <meta property="og:image:height" content="<?= !empty($ogImage) ? '360' : '512' ?>">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= !empty($ogImage) ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($title ?? SITE_NAME) ?>">
    <meta name="twitter:description" content="<?= e($description ?? SITE_TAGLINE) ?>">
    <meta name="twitter:image" content="<?= e($ogImageFinal) ?>">
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= isset($ogUrl) ? e($ogUrl) : e(SITE_URL . '/' . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '', '?'), '/')) ?>">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/favicon.png">
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <link rel="stylesheet" href="<?= asset(ENVIRONMENT === 'production' ? 'css/styles.min.css' : 'css/styles.css') ?>">
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
    <?php if (!empty($jsonLd)): ?>
        <?= $jsonLd ?>
    <?php endif; ?>
</head>
<body<?php
    // Expose user role to JS so content protection can be bypassed for admins/moderators
    $bodyRole = '';
    if (!empty($user['role'])) {
        $bodyRole = ' data-role="' . e($user['role']) . '"';
    }
    echo $bodyRole;
    // Site-wide content-protection toggle (config/config.php)
    echo ' data-protection="' . (CONTENT_PROTECTION_ENABLED ? '1' : '0') . '"';
?>>

    <?php $this->partial('header'); ?>
    <?php $this->partial('nav'); ?>

    <?php if ($flash = flash('message')): ?>
        <div class="container">
            <div class="alert alert-<?= e($flash['type']) ?>">
                <span><?= e($flash['message']) ?></span>
                <button class="alert-close">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <?= $this->getContent() ?>
    </main>

    <?php $this->partial('footer'); ?>
    <?php $this->partial('bottom-nav'); ?>

    <?php if (!empty($showWelcomePopup)): ?>
        <?php $this->partial('welcome-popup'); ?>
    <?php endif; ?>

    <script src="<?= asset(ENVIRONMENT === 'production' ? 'js/script.min.js' : 'js/script.js') ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>

    <?php $this->partial('charymeld'); ?>

    <?php if (ADSENSE_ENABLED): ?>
    <!-- Sticky anchor bar -->
    <div id="adAnchorBar" class="ad-anchor-bar">
        <button id="adAnchorClose" class="ad-anchor-close" aria-label="Close advertisement">&times;</button>
        <span class="ad-anchor-bar-label">Advertisement</span>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-7960622250292703"
             data-ad-slot="6111588136"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
    </div>

    <!-- AdSense lazy-loader + anchor dismiss -->
    <script>
    (function () {
        /* ── Anchor bar dismiss ── */
        var bar   = document.getElementById('adAnchorBar');
        var close = document.getElementById('adAnchorClose');
        if (bar) {
            if (sessionStorage.getItem('adAnchorClosed')) {
                bar.style.display = 'none';
            } else if (close) {
                close.addEventListener('click', function () {
                    bar.style.display = 'none';
                    sessionStorage.setItem('adAnchorClosed', '1');
                });
            }
        }

        /* ── Lazy-load all ads when scrolled into view ── */
        var ads = document.querySelectorAll('.adsbygoogle');
        if (!ads.length) return;

        function initAd() {
            (window.adsbygoogle = window.adsbygoogle || []).push({});
        }

        if (!('IntersectionObserver' in window)) {
            ads.forEach(initAd);
            return;
        }

        var observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    initAd();
                    obs.unobserve(entry.target);
                }
            });
        }, { rootMargin: '200px 0px' });

        ads.forEach(function (ad) { observer.observe(ad); });
    }());
    </script>
    <?php endif; ?>
</body>
</html>
