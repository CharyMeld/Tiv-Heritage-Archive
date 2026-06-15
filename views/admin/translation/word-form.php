<div class="admin-page-header">
    <h1 class="admin-page-title">Add Word to Dictionary</h1>
    <p class="admin-page-subtitle">
        Adding: <strong>"<?= e($word['word']) ?>"</strong>
        (<?= strtoupper($word['source_language']) ?> → <?= strtoupper($word['target_language']) ?>)
        — searched <?= (int)$word['search_count'] ?> time<?= $word['search_count'] != 1 ? 's' : '' ?>
    </p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

    <!-- Add Word Form -->
    <div class="admin-card">
        <h2 class="admin-card-title">Dictionary Entry</h2>
        <form method="POST" action="<?= url('admin/translation/missing-words/' . (int)$word['id'] . '/add') ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Tiv Word *</label>
                <input type="text" name="tiv_word" class="form-control" required
                       value="<?= $word['source_language'] === 'tiv' ? e($word['word']) : '' ?>"
                       placeholder="Tiv word or phrase">
            </div>

            <div class="form-group">
                <label class="form-label">English Meaning *</label>
                <input type="text" name="english_meaning" class="form-control" required
                       value="<?= $word['source_language'] === 'english' ? e($word['word']) : '' ?>"
                       placeholder="Primary English meaning">
            </div>

            <div class="form-group">
                <label class="form-label">Alternate Meaning <span class="form-hint">(optional)</span></label>
                <input type="text" name="alternate_meaning" class="form-control"
                       placeholder="Secondary meaning or synonym">
            </div>

            <div class="form-group">
                <label class="form-label">Part of Speech</label>
                <select name="part_of_speech" class="form-select">
                    <option value="">— Select —</option>
                    <option value="noun">Noun</option>
                    <option value="verb">Verb</option>
                    <option value="adjective">Adjective</option>
                    <option value="adverb">Adverb</option>
                    <option value="pronoun">Pronoun</option>
                    <option value="preposition">Preposition</option>
                    <option value="conjunction">Conjunction</option>
                    <option value="interjection">Interjection</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Category <span class="form-hint">(optional)</span></label>
                <input type="text" name="category" class="form-control" placeholder="e.g. greetings, family, nature">
            </div>

            <div class="form-group">
                <label class="form-label">Example (Tiv) <span class="form-hint">(optional)</span></label>
                <input type="text" name="example_tiv" class="form-control" placeholder="Example sentence in Tiv">
            </div>

            <div class="form-group">
                <label class="form-label">Example (English) <span class="form-hint">(optional)</span></label>
                <input type="text" name="example_english" class="form-control" placeholder="English translation of the example">
            </div>

            <div class="form-group">
                <label class="form-label">Pronunciation <span class="form-hint">(optional)</span></label>
                <input type="text" name="pronunciation" class="form-control" placeholder="e.g. /tswar/, nde-er-u">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save to Dictionary</button>
                <a href="<?= url('admin/translation/missing-words') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Community Suggestions Panel -->
    <div>
        <?php if (!empty($suggestions)): ?>
        <div class="admin-card">
            <h2 class="admin-card-title">Community Suggestions (<?= count($suggestions) ?>)</h2>
            <p class="admin-card-hint">Use these to help fill in the form above.</p>
            <?php foreach ($suggestions as $s): ?>
            <div class="suggestion-item <?= $s['admin_review_status'] === 'approved' ? 'suggestion-approved' : '' ?>">
                <div class="suggestion-meaning">
                    <?= e($s['suggested_meaning']) ?>
                    <?php if (!empty($s['part_of_speech'])): ?>
                        <span class="badge-pos"><?= e($s['part_of_speech']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($s['example_sentence'])): ?>
                    <p class="suggestion-example">E.g. <?= e($s['example_sentence']) ?></p>
                <?php endif; ?>
                <?php if (!empty($s['notes'])): ?>
                    <p class="suggestion-notes"><?= e($s['notes']) ?></p>
                <?php endif; ?>
                <div class="suggestion-meta">
                    <span><?= e($s['user_name'] ?? 'Anonymous') ?></span>
                    <span><?= date('M j, Y', strtotime($s['created_at'])) ?></span>
                    <span class="badge-status-<?= $s['admin_review_status'] ?>"><?= ucfirst($s['admin_review_status']) ?></span>
                </div>
                <?php if ($s['admin_review_status'] === 'pending'): ?>
                <div class="suggestion-use-btn" style="margin-top:.4rem;">
                    <button type="button"
                        onclick="document.querySelector('[name=english_meaning]').value = <?= json_encode($s['suggested_meaning']) ?>;
                                 <?php if (!empty($s['part_of_speech'])): ?>
                                 document.querySelector('[name=part_of_speech]').value = <?= json_encode($s['part_of_speech']) ?>;
                                 <?php endif; ?>
                                 <?php if (!empty($s['example_sentence'])): ?>
                                 document.querySelector('[name=example_english]').value = <?= json_encode($s['example_sentence']) ?>;
                                 <?php endif; ?>"
                        class="btn btn-sm btn-outline">
                        Use This
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="admin-card">
            <h2 class="admin-card-title">Community Suggestions</h2>
            <p class="no-suggestions">No suggestions submitted for this word yet. Fill in the form yourself.</p>
        </div>
        <?php endif; ?>

        <!-- Word Info -->
        <div class="admin-card" style="margin-top:1rem;">
            <h2 class="admin-card-title">Word Statistics</h2>
            <dl class="stat-list">
                <dt>Searches</dt>
                <dd><?= (int)$word['search_count'] ?> time<?= $word['search_count'] != 1 ? 's' : '' ?></dd>
                <dt>First logged</dt>
                <dd><?= date('M j, Y', strtotime($word['created_at'])) ?></dd>
                <dt>Last searched</dt>
                <dd><?= date('M j, Y H:i', strtotime($word['last_searched_at'])) ?></dd>
            </dl>
        </div>
    </div>
</div>

<style>
.admin-card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 8px rgba(0,0,0,.06); margin-bottom: 1rem; }
.admin-card-title { font-size: 1rem; font-weight: 700; color: var(--color-primary,#5C3A21); margin-bottom: .75rem; }
.admin-card-hint { font-size: .85rem; color: #888; margin-bottom: 1rem; margin-top: -.5rem; }
.admin-page-subtitle { color: #888; font-size: .9rem; margin-top: .25rem; }
.form-group { display: flex; flex-direction: column; gap: .35rem; margin-bottom: 1rem; }
.form-label { font-weight: 600; font-size: .85rem; color: #444; }
.form-hint { font-weight: 400; color: #aaa; }
.form-control, .form-select { border: 1px solid #ddd; border-radius: 6px; padding: .5em .75em; font-size: .9rem; font-family: inherit; }
.form-control:focus, .form-select:focus { outline: none; border-color: var(--color-primary,#5C3A21); }
.form-actions { display: flex; gap: .75rem; padding-top: .5rem; }
.suggestion-item { background: #fafafa; border-radius: 6px; padding: .75rem; margin-bottom: .6rem; border-left: 3px solid #ddd; }
.suggestion-approved { border-left-color: #2e7d32; background: #f1f9f1; }
.suggestion-meaning { font-weight: 600; font-size: .95rem; }
.suggestion-example { font-size: .85rem; font-style: italic; color: #666; margin: .25rem 0 0; }
.suggestion-notes { font-size: .8rem; color: #888; margin: .2rem 0 0; }
.suggestion-meta { display: flex; gap: .5rem; flex-wrap: wrap; font-size: .78rem; color: #888; margin-top: .4rem; }
.badge-pos { background: #f0e8e0; color: #5C3A21; padding: .1em .4em; border-radius: 3px; font-size: .75rem; margin-left: .3rem; }
.badge-status-pending { background: #fff8e1; color: #f57f17; padding: .1em .4em; border-radius: 3px; }
.badge-status-approved { background: #e8f5e9; color: #2e7d32; padding: .1em .4em; border-radius: 3px; }
.badge-status-rejected { background: #fce4ec; color: #c62828; padding: .1em .4em; border-radius: 3px; }
.no-suggestions { color: #aaa; font-size: .9rem; }
.btn-outline { border: 1px solid #ddd; background: #fff; color: #555; cursor: pointer; }
.btn-outline:hover { background: #f5f5f5; }
.stat-list { display: grid; grid-template-columns: auto 1fr; gap: .35rem .75rem; font-size: .88rem; }
.stat-list dt { font-weight: 600; color: #666; }
.stat-list dd { color: #333; margin: 0; }
</style>
