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
            <label class="form-label">Contributor / Person Name</label>
            <input type="text" name="contributor_name" class="form-input"
                   value="<?= e($source['contributor_name'] ?? old('contributor_name')) ?>"
                   placeholder="e.g. Chief Iorbee Akper">
            <p class="form-hint">The person who provided or is associated with this knowledge (oral, interview and community sources). Leave empty for publications and websites.</p>
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
                <label class="form-label">Publisher</label>
                <input type="text" name="publisher" class="form-input" value="<?= e($source['publisher'] ?? old('publisher')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Organisation</label>
                <input type="text" name="organisation" class="form-input" value="<?= e($source['organisation'] ?? old('organisation')) ?>" placeholder="e.g. National Population Commission">
            </div>
            <div class="form-group">
                <label class="form-label">Publication date</label>
                <input type="text" name="publication_date" class="form-input" value="<?= e($source['publication_date'] ?? old('publication_date')) ?>" placeholder="e.g. 1953, March 2011">
            </div>
            <div class="form-group">
                <label class="form-label">Publication details</label>
                <input type="text" name="publication_details" class="form-input" value="<?= e($source['publication_details'] ?? old('publication_details')) ?>" placeholder="Journal, volume, issue, edition">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">URL</label>
            <input type="url" name="url" class="form-input" value="<?= e($source['url'] ?? old('url')) ?>" placeholder="https://…">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Access date</label>
                <input type="date" name="access_date" class="form-input" value="<?= e($source['access_date'] ?? old('access_date')) ?>">
                <p class="form-hint">When an online source was consulted</p>
            </div>
            <div class="form-group">
                <label class="form-label">Archive reference</label>
                <input type="text" name="archive_reference" class="form-input" value="<?= e($source['archive_reference'] ?? old('archive_reference')) ?>" placeholder="e.g. National Archives Kaduna file no.">
            </div>
            <div class="form-group">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-input" value="<?= e($source['isbn'] ?? old('isbn')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">DOI</label>
                <input type="text" name="doi" class="form-input" value="<?= e($source['doi'] ?? old('doi')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Verification status</label>
            <select name="verification_status" class="form-select">
                <?php foreach (Source::$verificationInfo as $key => $info): ?>
                <option value="<?= $key ?>" <?= ($source['verification_status'] ?? 'needs_corroboration') === $key ? 'selected' : '' ?>><?= e($info['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">Shown on the public References page.</p>
        </div>

        <?php require_once BASE_PATH . '/services/HeritageRegistry.php'; ?>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Source type (research)</label>
                <select name="source_kind" id="source_kind" class="form-select">
                    <option value="">— not classified —</option>
                    <?php foreach (HeritageRegistry::SOURCE_KIND as $key => [$label, $tier]): ?>
                    <option value="<?= $key ?>" data-tier="<?= (int) $tier ?>" <?= ($source['source_kind'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Reliability tier</label>
                <select name="source_tier" id="source_tier" class="form-select">
                    <option value="">— not set —</option>
                    <?php foreach (HeritageRegistry::SOURCE_TIER as $t => $label): ?>
                    <option value="<?= $t ?>" <?= (int) ($source['source_tier'] ?? 0) === $t ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="form-hint">Set from the type; change it only with a reason in the notes.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Copy group</label>
                <input type="text" name="lineage_group" class="form-input" maxlength="40" value="<?= e($source['lineage_group'] ?? '') ?>" placeholder="e.g. LIN-KANO">
                <p class="form-hint">Sources that copy each other share a group and count as one.</p>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Rights / reuse notes</label>
            <textarea name="rights_notes" class="form-textarea" rows="2" placeholder="Copyright, licence, permission to quote…"><?= e($source['rights_notes'] ?? '') ?></textarea>
        </div>
        <script>
        document.getElementById('source_kind').addEventListener('change', function () {
            var tier = this.options[this.selectedIndex].getAttribute('data-tier'), sel = document.getElementById('source_tier');
            if (tier && tier !== '0' && !sel.value) sel.value = tier; // fills an empty tier only
        });
        </script>

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
