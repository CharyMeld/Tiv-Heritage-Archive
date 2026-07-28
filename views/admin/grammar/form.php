<?php
$isEdit     = $rule !== null;
$formAction = $isEdit ? url('admin/grammar/' . $rule['id'] . '/edit') : url('admin/grammar/create');
$presetCategory = $_GET['category'] ?? ($rule['category'] ?? 'noun');
$examplesJson = json_encode($rule['examples'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$categoryLabels = [
    'noun'                => 'Noun',
    'pronoun'             => 'Pronoun',
    'verb'                => 'Verb',
    'adjective'           => 'Adjective',
    'sentence_structure'  => 'Sentence Structure',
    'question_formation'  => 'Question Formation',
];
?>
<div class="admin-wrapper">
<div class="admin-content" style="max-width:780px;">

    <div style="margin-bottom:1.2rem;">
        <a href="<?= url('admin/grammar') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; Back to Grammar Manager</a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= $isEdit ? 'Edit: ' . htmlspecialchars($rule['title']) : 'Add Grammar Rule' ?></h2>
            <span style="font-size:.78rem;color:#7a6a5a;">&#128220; Language &rsaquo; Grammar</span>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="<?= $formAction ?>">
                <?= csrf_field() ?>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Category <span style="color:#c0392b;">*</span></label>
                        <select name="category" class="form-select">
                            <?php foreach ($categoryLabels as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($rule['category'] ?? $presetCategory) === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-input" value="<?= (int) ($rule['sort_order'] ?? 0) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Title <span style="color:#c0392b;">*</span></label>
                    <input type="text" name="title" class="form-input" required
                           value="<?= htmlspecialchars($rule['title'] ?? '') ?>"
                           placeholder="e.g. Subject Pronouns">
                </div>

                <div class="form-group">
                    <label class="form-label">Summary</label>
                    <input type="text" name="summary" class="form-input"
                           value="<?= htmlspecialchars($rule['summary'] ?? '') ?>"
                           placeholder="One-line description shown on the accordion card">
                </div>

                <div class="form-group">
                    <label class="form-label">Explanation</label>
                    <textarea name="explanation" class="form-textarea" rows="5"
                              placeholder="Prose explanation of the rule"><?= htmlspecialchars($rule['explanation'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Examples (JSON)</label>
                    <textarea name="examples_json" class="form-textarea" rows="10"
                              style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($examplesJson) ?></textarea>
                    <p class="form-hint">Array of objects: <code>[{"tiv": "...", "english": "...", "note": "optional"}]</code></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Source Note</label>
                    <input type="text" name="source_note" class="form-input"
                           value="<?= htmlspecialchars($rule['source_note'] ?? '') ?>"
                           placeholder="Provenance, e.g. paragraph reference or inferred-pattern flag">
                </div>

                <div style="display:flex;gap:.75rem;margin-top:1.2rem;">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Add Rule' ?></button>
                    <a href="<?= url('admin/grammar') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
