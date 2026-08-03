<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">New Tracked Link</h1>
        <a href="<?= url('admin/marketing/links') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Links</a>
    </div>

    <div class="admin-card" style="max-width:560px;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/links/create') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label required">Destination URL</label>
                    <input type="url" name="destination_url" class="form-input" value="<?= old('destination_url') ?>" required placeholder="https://www.tivheritage.com/proverb/103-...">
                </div>

                <div class="form-group">
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-select">
                        <?php foreach ($platforms as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">UTM Source</label>
                        <input type="text" name="utm_source" class="form-input" placeholder="e.g. facebook">
                    </div>
                    <div class="form-group">
                        <label class="form-label">UTM Medium</label>
                        <input type="text" name="utm_medium" class="form-input" placeholder="e.g. social" value="social">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">UTM Campaign</label>
                    <input type="text" name="utm_campaign" class="form-input" placeholder="e.g. proverb-of-the-day">
                </div>

                <div class="form-group">
                    <label class="form-label">UTM Content <span style="font-weight:400;color:#8a7a6a;">(optional)</span></label>
                    <input type="text" name="utm_content" class="form-input" placeholder="e.g. carousel-image">
                </div>

                <button type="submit" class="btn btn-primary">Create Link</button>
            </form>
        </div>
    </div>

</div>
</div>
