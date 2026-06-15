<!-- Section banner -->
<div class="contribute-banner" style="background:linear-gradient(135deg,<?= htmlspecialchars($config['color']) ?> 0%,<?= htmlspecialchars($config['color']) ?>cc 100%);">
    <div class="container">
        <span class="page-banner-eyebrow"><?= $config['icon'] ?> <?= htmlspecialchars($config['title']) ?></span>
        <h1 class="page-banner-title"><?= htmlspecialchars($config['title']) ?></h1>
        <p class="page-banner-sub"><?= htmlspecialchars($config['description']) ?></p>
    </div>
</div>

<section style="padding:2.5rem 0 4rem;">
    <div class="container">

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.5rem;">
            <?php foreach ($config['subcategories'] as $key => $sub):
                $isPlaceholder = !empty($sub['placeholder']);
                $href = $isPlaceholder
                    ? url($section . '/' . $key)
                    : url($sub['redirect'] ?? ($section . '/' . $key));
            ?>
            <a href="<?= $href ?>"
               style="display:flex;flex-direction:column;gap:.7rem;padding:1.6rem;background:#fff;border:1px solid <?= $isPlaceholder ? '#e5e0d5' : htmlspecialchars($config['color']) ?>;border-radius:14px;text-decoration:none;color:inherit;transition:box-shadow .2s,transform .2s;<?= $isPlaceholder ? 'opacity:.85;' : '' ?>"
               onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,.12)';this.style.transform='translateY(-3px)'"
               onmouseout="this.style.boxShadow='';this.style.transform=''">
                <span style="font-size:2.2rem;line-height:1;"><?= $sub['icon'] ?></span>
                <div>
                    <h3 style="font-size:1.05rem;font-weight:700;color:#2d1b0e;margin:0 0 .4rem;">
                        <?= htmlspecialchars($sub['label']) ?>
                    </h3>
                    <p style="font-size:.85rem;color:#5a4a3a;line-height:1.55;margin:0;"><?= htmlspecialchars($sub['description']) ?></p>
                </div>
                <span style="margin-top:auto;font-size:.82rem;font-weight:600;color:<?= htmlspecialchars($config['color']) ?>;">
                    <?= $isPlaceholder ? 'Learn more &rarr;' : 'Explore &rarr;' ?>
                </span>
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
