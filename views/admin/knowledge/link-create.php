<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">+ Add Knowledge Graph Link</h1>
        <p class="admin-page-sub">Connect two cultural items with a relationship</p>
    </div>
    <a href="<?= url('admin/links') ?>" class="btn btn-secondary">&larr; Back to Links</a>
</div>

<div style="max-width:760px; padding:1.5rem;">
    <form action="<?= url('admin/links/create') ?>" method="POST" class="contribute-form-card" id="linkForm">
        <?= csrf_field() ?>

        <!-- Quick examples -->
        <div style="background:rgba(200,169,81,0.07); border:1px solid rgba(200,169,81,0.25); border-radius:10px; padding:1rem; margin-bottom:1.5rem;">
            <p style="font-family:var(--font-ui); font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-accent); margin:0 0 0.5rem;">
                &#128161; Example Relationships
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                <?php foreach (['ingredient_for','used_in_festival','mentioned_in_proverb','related_to','derived_from','used_for_food','medicinal_use','symbolises'] as $ex): ?>
                <button type="button" onclick="document.getElementById('relation_type').value='<?= $ex ?>'"
                        style="background:rgba(92,58,33,0.08); border:1px solid var(--color-border-light); border-radius:50px; padding:0.2rem 0.6rem; font-size:0.75rem; cursor:pointer; font-family:var(--font-ui);">
                    <?= $ex ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SOURCE -->
        <div style="background:var(--color-surface-alt); border-radius:10px; padding:1.25rem; margin-bottom:1rem;">
            <h3 style="font-family:var(--font-ui); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-primary); margin:0 0 1rem;">
                SOURCE Item
            </h3>

            <div class="form-group">
                <label class="form-label required">Category</label>
                <select name="source_table" id="source_table" class="form-select" required
                        onchange="updateItemDropdown('source')">
                    <option value="">— Select category —</option>
                    <?php foreach ($tableConfig as $table => $cfg): ?>
                    <option value="<?= $table ?>"><?= $cfg['icon'] ?> <?= e($cfg['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">Item</label>
                <?php foreach ($tableConfig as $table => $cfg): ?>
                <select name="source_id_<?= $table ?>" id="source_items_<?= $table ?>"
                        class="form-select source-item-select" style="display:none;">
                    <option value="">— Select <?= e($cfg['label']) ?> —</option>
                    <?php foreach ($items[$table] ?? [] as $it): ?>
                    <option value="<?= $it['id'] ?>"><?= e(mb_substr($it['label'], 0, 80)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endforeach; ?>
                <input type="hidden" name="source_id" id="source_id_hidden">
            </div>
        </div>

        <!-- RELATION -->
        <div style="text-align:center; margin:0.5rem 0;">
            <div class="form-group" style="max-width:320px; margin:0 auto;">
                <label class="form-label required">Relationship Type</label>
                <input type="text" name="relation_type" id="relation_type" class="form-input"
                       placeholder="e.g. ingredient_for, used_in_festival" required
                       style="text-align:center;">
                <p class="form-hint" style="text-align:center;">Use underscore_case, no spaces</p>
            </div>
            <div style="font-size:2rem; color:var(--color-accent); margin:0.25rem 0;">&#8595;</div>
        </div>

        <!-- TARGET -->
        <div style="background:var(--color-surface-alt); border-radius:10px; padding:1.25rem; margin-bottom:1.5rem;">
            <h3 style="font-family:var(--font-ui); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-primary); margin:0 0 1rem;">
                TARGET Item
            </h3>

            <div class="form-group">
                <label class="form-label required">Category</label>
                <select name="target_table" id="target_table" class="form-select" required
                        onchange="updateItemDropdown('target')">
                    <option value="">— Select category —</option>
                    <?php foreach ($tableConfig as $table => $cfg): ?>
                    <option value="<?= $table ?>"><?= $cfg['icon'] ?> <?= e($cfg['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">Item</label>
                <?php foreach ($tableConfig as $table => $cfg): ?>
                <select name="target_id_<?= $table ?>" id="target_items_<?= $table ?>"
                        class="form-select target-item-select" style="display:none;">
                    <option value="">— Select <?= e($cfg['label']) ?> —</option>
                    <?php foreach ($items[$table] ?? [] as $it): ?>
                    <option value="<?= $it['id'] ?>"><?= e(mb_substr($it['label'], 0, 80)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endforeach; ?>
                <input type="hidden" name="target_id" id="target_id_hidden">
            </div>
        </div>

        <div style="display:flex; gap:1rem;">
            <button type="submit" class="btn btn-primary">Save Link</button>
            <a href="<?= url('admin/links') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function updateItemDropdown(side) {
    var tableSelect = document.getElementById(side + '_table');
    var chosen = tableSelect.value;

    // Hide all selects for this side
    document.querySelectorAll('.' + side + '-item-select').forEach(function(sel) {
        sel.style.display = 'none';
        sel.removeAttribute('required');
    });

    if (!chosen) return;

    // Show the right one
    var target = document.getElementById(side + '_items_' + chosen);
    if (target) {
        target.style.display = 'block';
        target.setAttribute('required', 'required');
    }
}

// On form submit, copy the visible select's value into the hidden input
document.getElementById('linkForm').addEventListener('submit', function() {
    ['source', 'target'].forEach(function(side) {
        var tableVal = document.getElementById(side + '_table').value;
        if (tableVal) {
            var sel = document.getElementById(side + '_items_' + tableVal);
            if (sel) {
                document.getElementById(side + '_id_hidden').value = sel.value;
            }
        }
    });
});
</script>
