<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#9881;&#65039; Site Settings</h1>
        <p class="admin-page-sub">Configure your archive platform</p>
    </div>
    <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; Dashboard</a>
</div>

<div style="max-width: 600px;">
    <form action="<?= url('admin/settings') ?>" method="POST" class="admin-card" style="padding:1.75rem;">
        <?= csrf_field() ?>

        <?php foreach ($settings as $setting): ?>
            <div class="form-group">
                <label for="setting_<?= e($setting['setting_key']) ?>" class="form-label">
                    <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                </label>

                <?php if ($setting['setting_type'] === 'boolean'): ?>
                    <select id="setting_<?= e($setting['setting_key']) ?>"
                            name="settings[<?= e($setting['setting_key']) ?>]"
                            class="form-select">
                        <option value="1" <?= $setting['setting_value'] === '1' ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $setting['setting_value'] === '0' ? 'selected' : '' ?>>No</option>
                    </select>
                <?php elseif ($setting['setting_type'] === 'integer'): ?>
                    <input type="number" id="setting_<?= e($setting['setting_key']) ?>"
                           name="settings[<?= e($setting['setting_key']) ?>]"
                           class="form-input"
                           value="<?= e($setting['setting_value']) ?>">
                <?php else: ?>
                    <input type="text" id="setting_<?= e($setting['setting_key']) ?>"
                           name="settings[<?= e($setting['setting_key']) ?>]"
                           class="form-input"
                           value="<?= e($setting['setting_value']) ?>">
                <?php endif; ?>

                <?php if ($setting['description']): ?>
                    <p class="form-hint"><?= e($setting['description']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
