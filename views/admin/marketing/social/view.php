<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;"><?= e($post['headline']) ?></h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Generate and edit a caption per platform. Each is a separate AI call, so only generate what you need.</p>
        </div>
        <a href="<?= url('admin/marketing/social') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Social Media</a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:1.2rem;">
        <?php foreach ($platforms as $key => $label): ?>
        <?php $c = $captions[$key] ?? null; ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title"><?= e($label) ?></h2>
                <?php if ($c): ?><span style="font-size:.75rem;color:#7a6a5a;"><?= (int) $c['char_count'] ?> chars</span><?php endif; ?>
            </div>
            <div class="admin-card-body">
                <?php if (!$c): ?>
                    <form method="POST" action="<?= url('admin/marketing/social/' . $post['id'] . '/generate') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="platform" value="<?= e($key) ?>">
                        <p style="color:#7a6a5a;font-size:.85rem;margin:0 0 .8rem;">No caption generated yet for this platform.</p>
                        <button type="submit" class="btn btn-primary btn-block">&#129302; Generate <?= e($label) ?> Caption</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= url('admin/marketing/social/caption/' . $c['id'] . '/update') ?>" style="margin-bottom:.6rem;">
                        <?= csrf_field() ?>
                        <textarea name="caption_text" class="form-textarea" rows="5" style="font-size:.85rem;"><?= e($c['caption_text']) ?></textarea>
                        <input type="text" name="hashtags" class="form-input" style="margin-top:.4rem;font-size:.82rem;" value="<?= e($c['hashtags'] ?? '') ?>" placeholder="#hashtags">
                        <div style="display:flex;gap:.5rem;margin-top:.6rem;">
                            <button type="submit" class="btn btn-secondary" style="font-size:.8rem;flex:1;">Save Edits</button>
                        </div>
                    </form>
                    <form method="POST" action="<?= url('admin/marketing/social/caption/' . $c['id'] . '/regenerate') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-block" style="font-size:.8rem;">&#8635; Regenerate</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</div>
