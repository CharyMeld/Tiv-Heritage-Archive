<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">New Email Campaign</h1>
        <a href="<?= url('admin/outreach/campaigns') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Campaigns</a>
    </div>

    <div class="admin-card" style="max-width:700px;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/outreach/campaigns/create') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label required">Campaign Name</label>
                    <input type="text" name="name" class="form-input" value="<?= e(old('name')) ?>" required placeholder="e.g. Quarterly Update — June 2026">
                </div>

                <div class="form-group">
                    <label class="form-label required">Email Template</label>
                    <select name="template_id" class="form-select" required>
                        <option value="">— Select a template —</option>
                        <?php foreach ($templates as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= (int)(old('template_id')) === (int)$t['id'] ? 'selected' : '' ?>>
                                <?= e($t['name']) ?> — <?= e(mb_strimwidth($t['subject'], 0, 50, '…')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint"><a href="<?= url('admin/outreach/templates/create') ?>" target="_blank">+ Create a new template</a> if needed.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Schedule Type</label>
                    <select name="schedule_type" class="form-select">
                        <?php foreach (['once'=>'One-Time Send','monthly'=>'Monthly','quarterly'=>'Quarterly','custom'=>'Custom'] as $key => $label): ?>
                            <option value="<?= $key ?>" <?= old('schedule_type', 'once') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Categories <span style="font-weight:400;color:#8a7a6a;">(leave blank to target all active contacts)</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;background:#f7f4ee;border-radius:6px;padding:.8rem;">
                        <?php foreach ($categories as $key => $label): ?>
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;color:#2d1b0e;">
                            <input type="checkbox" name="target_categories[]" value="<?= e($key) ?>"
                                   <?= in_array($key, (array)($_POST['target_categories'] ?? []), true) ? 'checked' : '' ?>>
                            <?= e($label) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="form-hint">Only contacts with an email address and who have not opted out or unsubscribed will receive the email.</p>
                </div>

                <div style="display:flex;gap:.7rem;margin-top:1.2rem;">
                    <button type="submit" class="btn btn-primary">Create Campaign</button>
                    <a href="<?= url('admin/outreach/campaigns') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
