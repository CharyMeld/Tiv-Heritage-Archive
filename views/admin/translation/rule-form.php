<div class="admin-page-header">
    <h1 class="admin-page-title"><?= $rule ? 'Edit Rule' : 'Add Translation Rule' ?></h1>
    <a href="<?= url('admin/translation/rules') ?>" class="btn btn-secondary btn-sm">← Back</a>
</div>

<div class="admin-form-card">
    <form method="POST" action="<?= $rule
        ? url('admin/translation/rules/' . $rule['id'] . '/edit')
        : url('admin/translation/rules/create') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="form-label" for="rule_name">Rule Name *</label>
            <input type="text" name="rule_name" id="rule_name" class="form-input" required
                   value="<?= e($rule['rule_name'] ?? '') ?>"
                   placeholder="e.g. Very intensifier, Negation marker">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="source_language">Source Language</label>
                <select name="source_language" id="source_language" class="form-select">
                    <option value="tiv"     <?= ($rule['source_language'] ?? 'tiv') === 'tiv'     ? 'selected' : '' ?>>Tiv</option>
                    <option value="english" <?= ($rule['source_language'] ?? '') === 'english' ? 'selected' : '' ?>>English</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="target_language">Target Language</label>
                <select name="target_language" id="target_language" class="form-select">
                    <option value="english" <?= ($rule['target_language'] ?? 'english') === 'english' ? 'selected' : '' ?>>English</option>
                    <option value="tiv"     <?= ($rule['target_language'] ?? '') === 'tiv'     ? 'selected' : '' ?>>Tiv</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="pattern_text">Pattern Text *</label>
            <input type="text" name="pattern_text" id="pattern_text" class="form-input" required
                   value="<?= e($rule['pattern_text'] ?? '') ?>"
                   placeholder="Text to find in the assembled translation (case-insensitive)">
            <p class="form-help">This text is searched within the assembled translation output using case-insensitive matching.</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="replacement_text">Replacement Text *</label>
            <input type="text" name="replacement_text" id="replacement_text" class="form-input" required
                   value="<?= e($rule['replacement_text'] ?? '') ?>"
                   placeholder="What to replace it with">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="rule_type">Rule Type</label>
                <select name="rule_type" id="rule_type" class="form-select">
                    <?php foreach (['word_order','phrase_fix','common_word','structure','other'] as $rt): ?>
                    <option value="<?= $rt ?>" <?= ($rule['rule_type'] ?? 'phrase_fix') === $rt ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_',' ',$rt)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="priority_score">Priority (1–100)</label>
                <input type="number" name="priority_score" id="priority_score" class="form-input"
                       min="1" max="100" value="<?= (int)($rule['priority_score'] ?? 50) ?>">
                <p class="form-help">Higher = applied first.</p>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="active"   <?= ($rule['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($rule['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $rule ? 'Update Rule' : 'Add Rule' ?></button>
            <a href="<?= url('admin/translation/rules') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
