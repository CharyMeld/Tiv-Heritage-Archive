<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128241; Social Media Captions</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Generate platform-specific captions for approved posts — Facebook, Instagram, X, LinkedIn, Telegram, WhatsApp.</p>
        </div>
        <a href="<?= url('admin/marketing') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
    </div>

    <div class="admin-card">
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
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $p): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.65rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($p['headline'] ?? '(untitled)') ?></td>
                                <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e(MarketingPost::SOURCE_TYPES[$p['source_type']] ?? $p['source_type']) ?></td>
                                <td style="padding:.65rem 1rem;">
                                    <span style="background:#dbeafe;color:#1e40af;padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;"><?= e(MarketingPost::STATUSES[$p['status']] ?? $p['status']) ?></span>
                                </td>
                                <td style="padding:.65rem 1rem;">
                                    <a href="<?= url('admin/marketing/social/' . $p['id']) ?>" class="btn btn-primary" style="font-size:.78rem;padding:.25rem .6rem;">Manage Captions</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
