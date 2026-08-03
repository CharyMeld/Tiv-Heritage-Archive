<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;"><?= e($post['headline']) ?></h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Pick a template and orientation — rendered instantly, no AI call.</p>
        </div>
        <a href="<?= url('admin/marketing/images') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Image Generator</a>
    </div>

    <div class="admin-card" style="max-width:520px;margin-bottom:2rem;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/images/generate') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">

                <div class="form-group">
                    <label class="form-label required">Template</label>
                    <?php $recommended = MarketingImage::AUTO_TEMPLATE_FOR_SOURCE[$post['source_type']] ?? null; ?>
                    <select name="template_type" class="form-select">
                        <?php foreach ($templates as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $key === $recommended ? 'selected' : '' ?>>
                                <?= e($label) ?><?= $key === $recommended ? ' (recommended)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Orientation</label>
                    <select name="orientation" class="form-select">
                        <?php foreach ($orientations as $key => $o): ?>
                            <option value="<?= e($key) ?>"><?= e($o['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">&#128444; Generate & Save</button>
            </form>
        </div>
    </div>

    <?php if (!empty($existing)): ?>
    <div class="admin-card">
        <div class="admin-card-header"><h2 class="admin-card-title">Already Generated for This Post</h2></div>
        <div class="admin-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:1rem;">
                <?php foreach ($existing as $img): ?>
                <div style="border:1px solid #e5e0d5;border-radius:8px;overflow:hidden;">
                    <img src="<?= UPLOADS_URL . '/' . e($img['file_path']) ?>" style="width:100%;display:block;" loading="lazy">
                    <div style="padding:.5rem .6rem;font-size:.75rem;color:#7a6a5a;display:flex;justify-content:space-between;align-items:center;">
                        <span><?= e(MarketingImage::TEMPLATES[$img['template_type']] ?? $img['template_type']) ?> &middot; <?= e($img['orientation']) ?></span>
                        <form method="POST" action="<?= url('admin/marketing/images/' . $img['id'] . '/delete') ?>" style="margin:0;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger" style="font-size:.7rem;padding:.15rem .4rem;" data-confirm="Delete this image?">Del</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>
