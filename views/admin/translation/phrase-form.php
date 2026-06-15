<div class="admin-page-header">
    <h1 class="admin-page-title"><?= $phrase ? 'Edit Phrase' : 'Add Translation Phrase' ?></h1>
    <a href="<?= url('admin/translation/phrases') ?>" class="btn btn-secondary btn-sm">← Back</a>
</div>

<div class="admin-form-card">
    <form method="POST" action="<?= $phrase
        ? url('admin/translation/phrases/' . $phrase['id'] . '/edit')
        : url('admin/translation/phrases/create') ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="source_language">Source Language *</label>
                <select name="source_language" id="source_language" class="form-select" required>
                    <option value="tiv"     <?= ($phrase['source_language'] ?? 'tiv') === 'tiv'     ? 'selected' : '' ?>>Tiv</option>
                    <option value="english" <?= ($phrase['source_language'] ?? '') === 'english' ? 'selected' : '' ?>>English</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="target_language">Target Language *</label>
                <select name="target_language" id="target_language" class="form-select" required>
                    <option value="english" <?= ($phrase['target_language'] ?? 'english') === 'english' ? 'selected' : '' ?>>English</option>
                    <option value="tiv"     <?= ($phrase['target_language'] ?? '') === 'tiv'     ? 'selected' : '' ?>>Tiv</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="source_text">Source Text *</label>
            <textarea name="source_text" id="source_text" class="form-textarea" rows="3" required
                      placeholder="Enter the phrase in the source language"><?= e($phrase['source_text'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="target_text">Target Text (Translation) *</label>
            <textarea name="target_text" id="target_text" class="form-textarea" rows="3" required
                      placeholder="Enter the translation in the target language"><?= e($phrase['target_text'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="context_tag">Context Tag</label>
                <input type="text" name="context_tag" id="context_tag" class="form-input"
                       value="<?= e($phrase['context_tag'] ?? '') ?>"
                       placeholder="e.g. greeting, question, response, daily">
                <p class="form-help">Used to categorise the phrase for display and matching.</p>
            </div>
            <div class="form-group">
                <label class="form-label" for="confidence_score">Confidence Score (1–100)</label>
                <input type="number" name="confidence_score" id="confidence_score" class="form-input"
                       min="1" max="100" value="<?= (int)($phrase['confidence_score'] ?? 90) ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="active"   <?= ($phrase['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($phrase['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $phrase ? 'Update Phrase' : 'Add Phrase' ?></button>
            <a href="<?= url('admin/translation/phrases') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
