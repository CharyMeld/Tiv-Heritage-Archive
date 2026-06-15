<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $source ? '&#9998; Edit Source' : '+ Add Source / Contributor' ?></h1>
        <p class="admin-page-sub">Sources appear on the public References page and on each content detail page</p>
    </div>
    <a href="<?= url('admin/sources') ?>" class="btn btn-secondary">&larr; Back to Sources</a>
</div>

<div style="max-width: 680px; padding: 1.5rem;">
    <?php
    $action = $source
        ? url('admin/sources/' . $source['id'] . '/edit')
        : url('admin/sources/create');
    ?>
    <form action="<?= $action ?>" method="POST" class="contribute-form-card">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="form-label required">Source Type</label>
            <select name="source_type" class="form-select" required>
                <?php foreach ($typeLabels as $key => $label): ?>
                <option value="<?= $key ?>"
                    <?= ($source['source_type'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">What kind of source is this?</p>
        </div>

        <div class="form-group">
            <label class="form-label required">Contributor / Person Name</label>
            <input type="text" name="contributor_name" class="form-input"
                   value="<?= e($source['contributor_name'] ?? old('contributor_name')) ?>"
                   placeholder="e.g. Chief Iorbee Akper" required>
            <p class="form-hint">The person who provided or is associated with this knowledge</p>
        </div>

        <div class="form-group">
            <label class="form-label">Author (if different from Contributor)</label>
            <input type="text" name="author" class="form-input"
                   value="<?= e($source['author'] ?? old('author')) ?>"
                   placeholder="e.g. Dr. J.T. Tor">
        </div>

        <div class="form-group">
            <label class="form-label">Title of Work / Publication</label>
            <input type="text" name="title" class="form-input"
                   value="<?= e($source['title'] ?? old('title')) ?>"
                   placeholder="e.g. Tiv Proverbs and Wisdom">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input"
                       value="<?= e($source['location'] ?? old('location')) ?>"
                       placeholder="e.g. Makurdi, Benue State">
            </div>
            <div class="form-group">
                <label class="form-label">Year Recorded</label>
                <input type="number" name="year_recorded" class="form-input"
                       value="<?= e($source['year_recorded'] ?? old('year_recorded')) ?>"
                       placeholder="<?= date('Y') ?>" min="1900" max="<?= date('Y') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-textarea" rows="3"
                      placeholder="Additional context about this source or contributor..."><?= e($source['notes'] ?? old('notes')) ?></textarea>
        </div>

        <div style="display:flex; gap:1rem; margin-top:0.5rem;">
            <button type="submit" class="btn btn-primary">
                <?= $source ? 'Update Source' : 'Add Source' ?>
            </button>
            <a href="<?= url('admin/sources') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
