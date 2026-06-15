<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?= SITE_URL ?>">
    <title><?= e($title ?? SITE_NAME) ?></title>
    <meta name="description" content="<?= e($description ?? SITE_TAGLINE) ?>">
    <!-- Theme color for PWA -->
    <meta name="theme-color" content="#5C3A21">
    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($description ?? SITE_TAGLINE) ?>">
    <meta property="og:type" content="<?= isset($ogType) ? e($ogType) : 'website' ?>">
    <meta property="og:url" content="<?= isset($ogUrl) ? e($ogUrl) : SITE_URL ?>">
    <meta property="og:site_name" content="<?= SITE_NAME ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:width" content="480">
    <meta property="og:image:height" content="360">
    <?php endif; ?>
    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= !empty($ogImage) ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($title ?? SITE_NAME) ?>">
    <meta name="twitter:description" content="<?= e($description ?? SITE_TAGLINE) ?>">
    <?php if (!empty($ogImage)): ?>
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/favicon.png">
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body<?php
    // Expose user role to JS so content protection can be bypassed for admins/moderators
    $bodyRole = '';
    if (!empty($user['role'])) {
        $bodyRole = ' data-role="' . e($user['role']) . '"';
    }
    echo $bodyRole;
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

    <script src="<?= asset('js/script.js') ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>

    <?php $this->partial('charymeld'); ?>
</body>
</html>
