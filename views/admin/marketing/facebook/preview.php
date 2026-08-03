<?php
$displayText = $fbCaption['caption_text'] ?? $post['caption'];
$hashtags = $fbCaption['hashtags'] ?? $post['hashtags'];
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Facebook Post Preview</h1>
        <a href="<?= url('admin/marketing/posts/' . $post['id']) ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Back to Post</a>
    </div>

    <?php if (!$fbCaption): ?>
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1.5rem;color:#92400e;font-size:.88rem;">
            No Facebook-specific caption generated yet — showing the master caption instead.
            <a href="<?= url('admin/marketing/social/' . $post['id']) ?>">Generate one</a> for a more accurate preview.
        </div>
    <?php endif; ?>

    <!-- Mockup of a Facebook post card -->
    <div style="max-width:500px;background:#fff;border:1px solid #dddfe2;border-radius:8px;font-family:Helvetica,Arial,sans-serif;box-shadow:0 1px 2px rgba(0,0,0,.1);">
        <div style="display:flex;align-items:center;gap:.6rem;padding:.8rem 1rem;">
            <div style="width:40px;height:40px;border-radius:50%;background:#5C3A21;color:#C8A951;display:flex;align-items:center;justify-content:center;font-weight:700;">T</div>
            <div>
                <div style="font-weight:600;font-size:.9rem;color:#050505;">Tiv Heritage Archive</div>
                <div style="font-size:.78rem;color:#65676b;">Just now &middot; &#127760;</div>
            </div>
        </div>
        <div style="padding:0 1rem .8rem;font-size:.92rem;color:#050505;line-height:1.4;white-space:pre-line;"><?= e($displayText) ?><?= $hashtags ? "\n\n" . e($hashtags) : '' ?></div>
        <div style="background:#f0f2f5;height:220px;display:flex;align-items:center;justify-content:center;color:#65676b;font-size:.85rem;">
            [ Image would appear here if attached ]
        </div>
        <div style="padding:.5rem 1rem;border-top:1px solid #e4e6ea;display:flex;justify-content:space-between;color:#65676b;font-size:.85rem;">
            <span>&#128077; Like</span>
            <span>&#128172; Comment</span>
            <span>&#8635; Share</span>
        </div>
    </div>

</div>
</div>
