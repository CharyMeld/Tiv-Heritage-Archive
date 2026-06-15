<!-- Breadcrumb -->
<div style="background:#faf8f5;border-bottom:1px solid #e5e0d5;padding:.6rem 0;">
    <div class="container" style="font-size:.83rem;color:#7a6a5a;">
        <a href="<?= url('/') ?>" style="color:#5C3A21;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url('archive') ?>" style="color:#5C3A21;text-decoration:none;">Archive</a>
        <span style="margin:0 .4rem;">›</span>
        <span><?= htmlspecialchars($categoryTitle) ?></span>
    </div>
</div>

<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128452; Archive</span>
        <h1 class="page-banner-title"><?= $categoryIcon ?> <?= htmlspecialchars($categoryTitle) ?></h1>
        <p class="page-banner-sub"><?= htmlspecialchars($categoryDescription) ?></p>
    </div>
</div>

<section style="padding:3rem 0 5rem;">
    <div class="container" style="max-width:760px;margin:0 auto;text-align:center;">

        <div style="font-size:4.5rem;margin-bottom:1.2rem;"><?= $categoryIcon ?></div>

        <h2 style="font-size:1.6rem;color:#5C3A21;margin-bottom:.8rem;">
            <?= htmlspecialchars($categoryTitle) ?> — Coming Soon
        </h2>

        <p style="font-size:1rem;color:#5a4a3a;line-height:1.8;margin-bottom:2rem;">
            This archive collection is actively being assembled. Community members, researchers, and
            institutional partners are invited to contribute to this growing section of the Tiv Heritage Archive.
        </p>

        <a href="<?= url('archive') ?>" class="btn btn-secondary">&larr; Back to Archive</a>
    </div>
</section>
