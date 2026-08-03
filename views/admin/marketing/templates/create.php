<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">New Prompt Template</h1>
        <a href="<?= url('admin/marketing/templates') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Templates</a>
    </div>

    <div class="admin-card" style="max-width:700px;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/templates/create') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label required">Template Name <span style="font-weight:400;color:#8a7a6a;">(internal label)</span></label>
                    <input type="text" name="name" class="form-input" value="<?= old('name') ?>" required placeholder="e.g. Word of the Day — Playful Tone">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label required">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('category', 'general') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Platform</label>
                        <select name="platform" class="form-select">
                            <?php foreach ($platforms as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('platform', 'any') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">System Prompt <span style="font-weight:400;color:#8a7a6a;">(sets the AI's tone/role — optional)</span></label>
                    <textarea name="system_prompt" class="form-textarea" rows="3" placeholder="You are a warm, knowledgeable social media writer for the Tiv Heritage Archive..."><?= old('system_prompt') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label required">Prompt Text</label>
                    <textarea name="user_prompt_template" class="form-textarea" rows="6" required
                              style="font-family:monospace;font-size:.85rem;"
                              placeholder="Create a Facebook post explaining this Tiv word in a simple educational style. Tiv word: {{tiv_word}}. English meaning: {{english_meaning}}."><?= old('user_prompt_template') ?></textarea>
                    <p class="form-hint">Use <code>{{field_name}}</code> to insert data from the picked archive item.</p>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
                        <input type="checkbox" name="is_default" value="1" <?= old('is_default') ? 'checked' : '' ?>>
                        Make this the default template for this category + platform
                    </label>
                </div>

                <div style="display:flex;gap:.7rem;">
                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <a href="<?= url('admin/marketing/templates') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
