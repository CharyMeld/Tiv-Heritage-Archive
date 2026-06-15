<!-- Breadcrumb -->
<div style="background:#faf8f5;border-bottom:1px solid #e5e0d5;padding:.6rem 0;">
    <div class="container" style="font-size:.83rem;color:#7a6a5a;">
        <a href="<?= url('/') ?>" style="color:#5C3A21;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url($section) ?>" style="color:#5C3A21;text-decoration:none;"><?= htmlspecialchars($sectionConfig['title']) ?></a>
        <span style="margin:0 .4rem;">›</span>
        <span><?= htmlspecialchars($subConfig['label']) ?></span>
    </div>
</div>

<!-- Banner -->
<div class="contribute-banner" style="background:linear-gradient(135deg,<?= htmlspecialchars($sectionConfig['color']) ?> 0%,<?= htmlspecialchars($sectionConfig['color']) ?>bb 100%);">
    <div class="container">
        <span class="page-banner-eyebrow"><?= $sectionConfig['icon'] ?> <?= htmlspecialchars($sectionConfig['title']) ?></span>
        <h1 class="page-banner-title"><?= htmlspecialchars($subConfig['label']) ?></h1>
        <p class="page-banner-sub"><?= htmlspecialchars($subConfig['description']) ?></p>
    </div>
</div>

<!-- Coming Soon body -->
<section style="padding:3rem 0 5rem;">
    <div class="container" style="max-width:760px;margin:0 auto;text-align:center;">

        <div style="font-size:4.5rem;margin-bottom:1.2rem;"><?= $subConfig['icon'] ?></div>

        <h2 style="font-size:1.6rem;color:#5C3A21;margin-bottom:.8rem;">
            <?= htmlspecialchars($subConfig['label']) ?> — Coming Soon
        </h2>

        <p style="font-size:1rem;color:#5a4a3a;line-height:1.8;margin-bottom:2rem;">
            We are actively building this section of the Tiv Heritage Archive. It will contain
            curated, community-verified <?= strtolower(htmlspecialchars($subConfig['label'])) ?>
            content contributed by researchers and community members.
        </p>

        <!-- Back to section -->
        <a href="<?= url($section) ?>" class="btn btn-secondary">
            &larr; Back to <?= htmlspecialchars($sectionConfig['title']) ?>
        </a>

        <!-- Explore available content -->
        <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid #e5e0d5;">
            <p style="color:#7a6a5a;font-size:.88rem;margin-bottom:1rem;">Explore other available sections:</p>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;justify-content:center;">
                <?php
                $otherSections = [
                    'language'   => ['&#128172;', 'Language'],
                    'literature' => ['&#128212;', 'Literature'],
                    'culture'    => ['&#127981;', 'Culture'],
                    'history'    => ['&#128336;', 'History'],
                    'archive'    => ['&#128452;', 'Archive'],
                    'community'  => ['&#128101;', 'Community'],
                ];
                foreach ($otherSections as $slug => [$icon, $label]):
                    if ($slug === $section) continue;
                ?>
                <a href="<?= url($slug) ?>"
                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;background:#faf8f5;border:1px solid #e5e0d5;border-radius:20px;font-size:.83rem;text-decoration:none;color:#5C3A21;transition:border-color .15s;"
                   onmouseover="this.style.borderColor='#C8A951'" onmouseout="this.style.borderColor='#e5e0d5'">
                    <?= $icon ?> <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>
