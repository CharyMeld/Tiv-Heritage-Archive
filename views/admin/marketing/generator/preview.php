<?php
$snapshot = $post['source_snapshot'] ? json_decode($post['source_snapshot'], true) : null;
$statusBg = match($post['status']) {
    'published' => 'background:#d1fae5;color:#065f46;',
    'approved', 'scheduled' => 'background:#dbeafe;color:#1e40af;',
    'rejected' => 'background:#fee2e2;color:#991b1b;',
    default => 'background:#f3f4f6;color:#374151;',
};
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Review Post</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">
                <?= e($sourceLabel) ?> &middot;
                <span style="<?= $statusBg ?>padding:.15rem .5rem;border-radius:10px;font-size:.78rem;font-weight:600;"><?= e(MarketingPost::STATUSES[$post['status']] ?? $post['status']) ?></span>
            </p>
        </div>
        <a href="<?= url('admin/marketing/generator') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Generator</a>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

        <div style="display:flex;flex-direction:column;gap:1.2rem;">

            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Content</h2></div>
                <div class="admin-card-body">
                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#8a7a6a;text-transform:uppercase;margin-bottom:.3rem;">Headline</label>
                        <p style="margin:0;font-size:1.1rem;font-weight:600;color:#2d1b0e;"><?= e($post['headline']) ?></p>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#8a7a6a;text-transform:uppercase;margin-bottom:.3rem;">Caption</label>
                        <p style="margin:0;color:#2d1b0e;line-height:1.6;white-space:pre-line;"><?= e($post['caption']) ?></p>
                    </div>
                    <?php if (!empty($post['cta'])): ?>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#8a7a6a;text-transform:uppercase;margin-bottom:.3rem;">Call to Action</label>
                        <p style="margin:0;color:#5C3A21;font-weight:500;"><?= e($post['cta']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($post['hashtags'])): ?>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#8a7a6a;text-transform:uppercase;margin-bottom:.3rem;">Hashtags</label>
                        <p style="margin:0;color:#5C3A21;"><?= e($post['hashtags']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">SEO Metadata</h2></div>
                <div class="admin-card-body" style="font-size:.88rem;">
                    <p><strong>SEO Title:</strong> <?= e($post['seo_title']) ?></p>
                    <p><strong>Meta Description:</strong> <?= e($post['meta_description']) ?></p>
                    <p><strong>OpenGraph Description:</strong> <?= e($post['og_description']) ?></p>
                    <p style="margin:0;"><strong>Twitter Description:</strong> <?= e($post['twitter_description']) ?></p>
                </div>
            </div>

            <?php if ($snapshot): ?>
            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Source Item (as it looked when generated)</h2></div>
                <div class="admin-card-body" style="font-size:.85rem;color:#5a4a3a;max-height:220px;overflow-y:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <?php foreach ($snapshot as $field => $value): ?>
                            <?php if (is_scalar($value) && $value !== ''): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.3rem .5rem;color:#8a7a6a;white-space:nowrap;vertical-align:top;"><?= e($field) ?></td>
                                <td style="padding:.3rem .5rem;"><?= e((string) $value) ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div style="display:flex;flex-direction:column;gap:1rem;">
            <div class="admin-card" style="border-left:3px solid #5C3A21;">
                <div class="admin-card-header"><h2 class="admin-card-title">Actions</h2></div>
                <div class="admin-card-body" style="display:flex;flex-direction:column;gap:.6rem;">
                    <?php if (!in_array($post['status'], ['approved', 'scheduled', 'published'])): ?>
                        <form method="POST" action="<?= url('admin/marketing/posts/' . $post['id'] . '/approve') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary btn-block">&#10003; Approve</button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($post['status'], ['approved', 'scheduled'])): ?>
                        <a href="<?= url('admin/marketing/posts/' . $post['id'] . '/images') ?>" class="btn btn-secondary btn-block">&#128444; Generate Image</a>
                        <a href="<?= url('admin/marketing/social/' . $post['id']) ?>" class="btn btn-secondary btn-block">&#128241; Social Captions</a>
                        <a href="<?= url('admin/marketing/facebook/preview/' . $post['id']) ?>" class="btn btn-secondary btn-block">&#128064; Facebook Preview</a>
                        <a href="<?= url('admin/marketing/posts/' . $post['id'] . '/schedule') ?>" class="btn btn-secondary btn-block">&#128197; Schedule</a>
                    <?php endif; ?>
                    <?php if ($post['status'] !== 'rejected'): ?>
                        <form method="POST" action="<?= url('admin/marketing/posts/' . $post['id'] . '/reject') ?>" onsubmit="return promptReject(this);">
                            <?= csrf_field() ?>
                            <input type="hidden" name="reason" id="rejectReason_<?= $post['id'] ?>">
                            <button type="submit" class="btn btn-secondary btn-block">&#10007; Reject</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($post['rejection_reason'])): ?>
                        <p style="font-size:.82rem;color:#991b1b;margin:0;">Rejected: <?= e($post['rejection_reason']) ?></p>
                    <?php endif; ?>
                    <form method="POST" action="<?= url('admin/marketing/posts/' . $post['id'] . '/delete') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-block" data-confirm="Delete this post permanently?">Delete</button>
                    </form>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Details</h2></div>
                <div class="admin-card-body" style="font-size:.85rem;color:#5a4a3a;">
                    <p><strong>Model:</strong> <?= e($post['model_name'] ?? '—') ?></p>
                    <p><strong>Selection mode:</strong> <?= e(ucwords(str_replace('_', ' ', $post['selection_mode']))) ?></p>
                    <p style="margin:0;"><strong>Generated:</strong> <?= date('M j, Y g:ia', strtotime($post['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<script>
function promptReject(form) {
    var reason = prompt('Reason for rejecting (optional):', '');
    if (reason === null) return false;
    form.querySelector('#rejectReason_<?= $post['id'] ?>').value = reason;
    return true;
}
</script>
