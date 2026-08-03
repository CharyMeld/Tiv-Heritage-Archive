<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Schedule: <?= e($post['headline']) ?></h1>
        <a href="<?= url('admin/marketing/scheduled') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Scheduled Posts</a>
    </div>

    <div class="admin-card" style="max-width:520px;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/schedule/create') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">

                <div class="form-group">
                    <label class="form-label required">Platform</label>
                    <select name="platform" class="form-select">
                        <?php foreach ($platforms as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($images)): ?>
                <div class="form-group">
                    <label class="form-label">Image <span style="font-weight:400;color:#8a7a6a;">(optional)</span></label>
                    <select name="image_id" class="form-select">
                        <option value="">— No image —</option>
                        <?php foreach ($images as $img): ?>
                            <option value="<?= $img['id'] ?>"><?= e(MarketingImage::TEMPLATES[$img['template_type']] ?? $img['template_type']) ?> (<?= e($img['orientation']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label required">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" class="form-input" required min="<?= date('Y-m-d\TH:i') ?>">
                    <p class="form-hint">This is internal scheduling only — nothing will actually post to social media until the Facebook connection (or other platform) is live in a later phase.</p>
                </div>

                <button type="submit" class="btn btn-primary">&#128197; Schedule</button>
            </form>
        </div>
    </div>

</div>
</div>
