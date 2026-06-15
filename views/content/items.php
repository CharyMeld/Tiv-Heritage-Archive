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
        <h1 class="page-banner-title"><?= $subConfig['icon'] ?> <?= htmlspecialchars($subConfig['label']) ?></h1>
        <p class="page-banner-sub"><?= htmlspecialchars($subConfig['description']) ?></p>
    </div>
</div>

<section style="padding:2rem 0 4rem;">
    <div class="container">

        <!-- Search & stats bar -->
        <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;margin-bottom:1.5rem;">
            <form method="GET" style="display:flex;gap:.5rem;flex:1;min-width:220px;max-width:400px;">
                <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
                       placeholder="Search <?= htmlspecialchars($subConfig['label']) ?>..."
                       class="form-input" style="flex:1;">
                <button type="submit" class="btn btn-primary" style="padding:.5rem 1rem;">Search</button>
                <?php if ($search): ?>
                    <a href="<?= url($section . '/' . $sub) ?>" class="btn btn-secondary" style="padding:.5rem .9rem;">Clear</a>
                <?php endif; ?>
            </form>
            <span style="color:#7a6a5a;font-size:.85rem;margin-left:auto;">
                <?= number_format($pagination['total']) ?> <?= $pagination['total'] === 1 ? 'item' : 'items' ?>
            </span>
        </div>

        <!-- Items grid -->
        <?php if (empty($items)): ?>
            <div style="text-align:center;padding:4rem 1rem;">
                <div style="font-size:3.5rem;margin-bottom:1rem;"><?= $subConfig['icon'] ?></div>
                <h2 style="color:#5C3A21;margin-bottom:.6rem;">No <?= htmlspecialchars($subConfig['label']) ?> Yet</h2>
                <p style="color:#5a4a3a;max-width:480px;margin:0 auto 1.5rem;line-height:1.7;">
                    <?= htmlspecialchars($subConfig['description']) ?> Be the first to contribute.
                </p>
            </div>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;">
                <?php foreach ($items as $item): ?>
                    <?php
                    $hasMedia = !empty($item['media_file']);
                    $isAudio  = $item['media_type'] === 'audio';
                    $isImage  = $item['media_type'] === 'image';
                    $isDoc    = $item['media_type'] === 'document';
                    ?>
                    <a href="<?= url('content-item/' . $item['id']) ?>"
                       style="display:flex;flex-direction:column;background:#fff;border:1px solid <?= $item['is_featured'] ? '#C8A951' : '#e5e0d5' ?>;border-radius:12px;overflow:hidden;text-decoration:none;color:inherit;transition:box-shadow .2s,transform .2s;"
                       onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'"
                       onmouseout="this.style.boxShadow='';this.style.transform=''">

                        <?php if ($isImage && $hasMedia): ?>
                            <div style="height:180px;overflow:hidden;background:#f7f4ee;">
                                <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>"
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                            </div>
                        <?php elseif ($isAudio): ?>
                            <div style="height:80px;background:linear-gradient(135deg,<?= htmlspecialchars($sectionConfig['color']) ?>,<?= htmlspecialchars($sectionConfig['color']) ?>99);display:flex;align-items:center;justify-content:center;">
                                <span style="font-size:2.5rem;">&#127911;</span>
                            </div>
                        <?php elseif ($isDoc): ?>
                            <div style="height:80px;background:#f7f4ee;display:flex;align-items:center;justify-content:center;">
                                <span style="font-size:2.5rem;">&#128196;</span>
                            </div>
                        <?php endif; ?>

                        <div style="padding:1rem;flex:1;display:flex;flex-direction:column;gap:.4rem;">
                            <?php if ($item['is_featured']): ?>
                                <span style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#C8A951;">&#11088; Featured</span>
                            <?php endif; ?>
                            <h3 style="font-size:1rem;font-weight:700;color:#2d1b0e;margin:0;"><?= htmlspecialchars($item['title']) ?></h3>
                            <?php if (!empty($item['tiv_title'])): ?>
                                <p style="font-size:.82rem;color:#5C3A21;font-style:italic;margin:0;"><?= htmlspecialchars($item['tiv_title']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['excerpt'])): ?>
                                <p style="font-size:.84rem;color:#5a4a3a;line-height:1.5;margin:0;flex:1;"><?= htmlspecialchars(mb_substr($item['excerpt'], 0, 120)) ?><?= mb_strlen($item['excerpt']) > 120 ? '…' : '' ?></p>
                            <?php endif; ?>
                            <span style="font-size:.78rem;color:#9a8a7a;margin-top:auto;"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display:flex;justify-content:center;gap:.5rem;margin-top:2rem;">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>" class="btn btn-secondary">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:.4rem .9rem;font-size:.85rem;color:#5a4a3a;">
                        Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                    </span>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>" class="btn btn-secondary">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Footer CTAs -->
        <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid #e5e0d5;display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="<?= url("submit/{$section}/{$sub}") ?>" class="btn btn-primary">&#9997; Submit <?= htmlspecialchars($subConfig['label']) ?></a>
            <a href="<?= url('community/join') ?>" class="btn btn-secondary">&#128101; Join the Community</a>
            <a href="<?= url($section) ?>" class="btn btn-secondary">&larr; Back to <?= htmlspecialchars($sectionConfig['title']) ?></a>
        </div>

    </div>
</section>
