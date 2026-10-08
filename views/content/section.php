<!-- Section banner -->
<div class="contribute-banner" style="background:linear-gradient(135deg,<?= htmlspecialchars($config['color']) ?> 0%,<?= htmlspecialchars($config['color']) ?>cc 100%);">
    <div class="container">
        <span class="page-banner-eyebrow"><?= $config['icon'] ?> <?= htmlspecialchars($config['title']) ?></span>
        <h1 class="page-banner-title"><?= htmlspecialchars($config['title']) ?></h1>
        <p class="page-banner-sub"><?= htmlspecialchars($config['description']) ?></p>
    </div>
</div>

    <?php
    // Reuses the exact same .home-mega-* classes as the homepage's Language/
    // Literature/Culture/History feature (views/home/index.php) — same cards,
    // same responsive/mobile-compact behavior, one definition to keep in sync.
    ?>
<section class="home-mega-section">
    <div class="container">

        <div class="home-mega-grid">
            <?php foreach ($config['subcategories'] as $key => $sub):
                $isPlaceholder = !empty($sub['placeholder']);
                $href = $isPlaceholder
                    ? url($section . '/' . $key)
                    : url($sub['redirect'] ?? ($section . '/' . $key));
            ?>
            <a href="<?= $href ?>" class="home-mega-card" style="--card-accent:<?= htmlspecialchars($config['color']) ?>"
               aria-label="<?= htmlspecialchars($sub['label']) ?> — <?= htmlspecialchars($sub['description']) ?>">
                <span class="home-mega-card-icon"><?= $sub['icon'] ?></span>
                <h3 class="home-mega-card-label"><?= htmlspecialchars($sub['label']) ?></h3>
                <p class="home-mega-card-desc"><?= htmlspecialchars($sub['description']) ?></p>
                <span class="home-mega-card-cta"><?= $isPlaceholder ? 'Learn more &rarr;' : 'Explore &rarr;' ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- CTA strip — mirrors the two menu bar paths exactly -->
        <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid #e5e0d5;display:flex;gap:1rem;flex-wrap:wrap;">
            <!-- Matches standalone "Contribute" in the menu bar -->
            <a href="<?= url('contribute') ?>"
               style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1.4rem;background:<?= htmlspecialchars($config['color']) ?>;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:.9rem;transition:opacity .15s;"
               onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                &#9997; Contribute Content
            </a>
            <!-- Matches Community → Join the Community in the menu bar -->
            <a href="<?= url('community/join') ?>"
               style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1.4rem;background:#fff;color:<?= htmlspecialchars($config['color']) ?>;border:2px solid <?= htmlspecialchars($config['color']) ?>;border-radius:8px;text-decoration:none;font-weight:600;font-size:.9rem;transition:background .15s,color .15s;"
               onmouseover="this.style.background='<?= htmlspecialchars($config['color']) ?>';this.style.color='#fff'"
               onmouseout="this.style.background='#fff';this.style.color='<?= htmlspecialchars($config['color']) ?>'">
                &#128101; Join the Community
            </a>
        </div>

    </div>
</section>
