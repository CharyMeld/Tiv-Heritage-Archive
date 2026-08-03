<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128444; Image Generator</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Branded graphics rendered instantly (no AI call) — pick a post to generate from.</p>
        </div>
        <a href="<?= url('admin/marketing') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
    </div>

    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Pick a Post</h2></div>
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($posts)): ?>
                <div class="admin-empty-state" style="padding:2rem;">
                    <p>No approved posts yet. <a href="<?= url('admin/marketing/generator') ?>">Generate and approve content first.</a></p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Headline</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $p): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.65rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($p['headline'] ?? '(untitled)') ?></td>
                                <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e(MarketingPost::SOURCE_TYPES[$p['source_type']] ?? $p['source_type']) ?></td>
                                <td style="padding:.65rem 1rem;">
                                    <a href="<?= url('admin/marketing/posts/' . $p['id'] . '/images') ?>" class="btn btn-primary" style="font-size:.78rem;padding:.25rem .6rem;">Generate Image</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2 class="admin-card-title">Recently Generated</h2></div>
        <div class="admin-card-body">
            <?php if (empty($gallery)): ?>
                <div class="admin-empty-state"><p>No images generated yet.</p></div>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:1rem;">
                    <?php foreach ($gallery as $img): ?>
                    <div style="border:1px solid #e5e0d5;border-radius:8px;overflow:hidden;">
                        <img src="<?= UPLOADS_URL . '/' . e($img['file_path']) ?>" style="width:100%;display:block;" loading="lazy">
                        <div style="padding:.5rem .6rem;font-size:.75rem;color:#7a6a5a;">
                            <?= e(MarketingImage::TEMPLATES[$img['template_type']] ?? $img['template_type']) ?> &middot; <?= e($img['orientation']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
