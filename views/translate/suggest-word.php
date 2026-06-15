<section class="suggest-word-page">
    <div class="container">

        <div class="suggest-hero">
            <h1 class="suggest-title">Suggest a Meaning</h1>
            <p class="suggest-subtitle">
                Help grow the Tiv dictionary by providing a meaning for
                <span class="missing-word-highlight">"<?= e($word['word']) ?>"</span>
                <span class="lang-badge"><?= strtoupper($word['source_language']) ?></span>
            </p>
            <p class="suggest-note">Your suggestion will be reviewed by an admin before it is added to the dictionary.</p>
        </div>

        <?php if ($alreadySuggested): ?>
        <div class="alert alert-info">
            You have already submitted a suggestion for this word. Thank you!
            An admin will review it soon.
        </div>
        <?php else: ?>

        <div class="suggest-card">
            <form method="POST" action="<?= url('translate/suggest/' . (int)$word['id']) ?>" class="suggest-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">
                        <?= $word['source_language'] === 'tiv' ? 'English meaning *' : 'Tiv word / meaning *' ?>
                    </label>
                    <textarea
                        name="suggested_meaning"
                        class="form-control"
                        rows="3"
                        required
                        placeholder="<?= $word['source_language'] === 'tiv'
                            ? 'What does "' . e($word['word']) . '" mean in English?'
                            : 'What is "' . e($word['word']) . '" in Tiv?' ?>"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Part of speech</label>
                    <select name="part_of_speech" class="form-select">
                        <option value="">— Select if known —</option>
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
                    <label class="form-label">Example sentence <span class="form-hint">(optional)</span></label>
                    <textarea
                        name="example_sentence"
                        class="form-control"
                        rows="2"
                        placeholder="Use the word in a sentence to show context…"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Additional notes <span class="form-hint">(optional)</span></label>
                    <input type="text" name="notes" class="form-control"
                           placeholder="Dialect, region, cultural notes…">
                </div>

                <div class="suggest-actions">
                    <button type="submit" class="btn btn-primary">Submit Suggestion</button>
                    <a href="<?= url('translate') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <?php endif; ?>

        <?php if (!empty($existing)): ?>
        <div class="existing-suggestions">
            <h2 class="existing-heading">Existing Suggestions (<?= count($existing) ?>)</h2>
            <?php foreach ($existing as $s): ?>
            <div class="existing-item">
                <div class="existing-meaning"><?= e($s['suggested_meaning']) ?></div>
                <div class="existing-meta">
                    <?php if (!empty($s['part_of_speech'])): ?>
                        <span class="badge-pos"><?= e($s['part_of_speech']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($s['example_sentence'])): ?>
                        <p class="existing-example">E.g. <?= e($s['example_sentence']) ?></p>
                    <?php endif; ?>
                    <span class="existing-status badge-status-<?= $s['admin_review_status'] ?>">
                        <?= ucfirst($s['admin_review_status']) ?>
                    </span>
                    <span class="existing-date"><?= date('M j, Y', strtotime($s['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<style>
.suggest-word-page { padding: 2rem 0 4rem; }
.suggest-hero { text-align: center; margin-bottom: 2rem; }
.suggest-title { font-size: 1.8rem; font-weight: 700; color: var(--color-primary,#5C3A21); }
.suggest-subtitle { font-size: 1.1rem; color: #555; margin: .5rem 0; }
.missing-word-highlight { font-weight: 700; color: var(--color-primary,#5C3A21); }
.lang-badge { background: #f0e8e0; color: #5C3A21; font-size: .7rem; font-weight: 700; padding: .15em .5em; border-radius: 4px; vertical-align: middle; margin-left: .25rem; }
.suggest-note { font-size: .85rem; color: #888; }
.suggest-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,.08); padding: 2rem; max-width: 640px; margin: 0 auto 2rem; }
.suggest-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: .4rem; }
.form-label { font-weight: 600; font-size: .9rem; color: #444; }
.form-hint { font-weight: 400; color: #999; }
.form-control { border: 1px solid #ddd; border-radius: 6px; padding: .6em .8em; font-size: .95rem; font-family: inherit; }
.form-control:focus { outline: none; border-color: var(--color-primary,#5C3A21); box-shadow: 0 0 0 2px rgba(92,58,33,.12); }
.form-select { border: 1px solid #ddd; border-radius: 6px; padding: .6em .8em; font-size: .95rem; background: #fff; }
.suggest-actions { display: flex; gap: 1rem; padding-top: .5rem; }
.alert-info { background: #e3f2fd; color: #1565c0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
.existing-suggestions { max-width: 640px; margin: 0 auto; }
.existing-heading { font-size: 1rem; font-weight: 700; color: var(--color-primary,#5C3A21); margin-bottom: 1rem; }
.existing-item { background: #fff; border-radius: 8px; padding: 1rem; margin-bottom: .75rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
.existing-meaning { font-size: 1.05rem; font-weight: 600; color: #222; margin-bottom: .4rem; }
.existing-meta { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; font-size: .8rem; color: #888; }
.badge-pos { background: #f0e8e0; color: #5C3A21; padding: .15em .5em; border-radius: 4px; font-weight: 600; }
.existing-example { font-style: italic; color: #666; margin: .25rem 0 0; width: 100%; }
.badge-status-pending { background: #fff8e1; color: #f57f17; padding: .15em .5em; border-radius: 4px; }
.badge-status-approved { background: #e8f5e9; color: #2e7d32; padding: .15em .5em; border-radius: 4px; }
.badge-status-rejected { background: #fce4ec; color: #c62828; padding: .15em .5em; border-radius: 4px; }
.existing-date { color: #bbb; }
</style>
