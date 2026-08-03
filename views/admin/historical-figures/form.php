<?php
$isEdit = $item !== null;
$formAction = $isEdit ? url('admin/historical-figures/' . $item['id'] . '/edit') : url('admin/historical-figures/create');
$old = fn($field, $default = '') => htmlspecialchars($_SESSION['old_input'][$field] ?? $item[$field] ?? $default);
unset($_SESSION['old_input'], $_SESSION['errors']);
?>
<div class="admin-wrapper">
<div class="admin-content" style="max-width:900px;">

    <div style="margin-bottom:1.2rem;">
        <a href="<?= url('admin/historical-figures') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; Back to Historical Figures</a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= $isEdit ? 'Edit: ' . htmlspecialchars($item['english_name']) : 'Add Historical Figure' ?></h2>
            <span style="font-size:.78rem;color:#7a6a5a;">&#129332; Historical Figures</span>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <h3 class="hf-form-heading">Identity</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label required">English Name</label>
                        <input type="text" name="english_name" class="form-input" value="<?= $old('english_name') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tiv Name</label>
                        <input type="text" name="tiv_name" class="form-input" value="<?= $old('tiv_name') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" value="<?= $old('title') ?>" placeholder="e.g. Tor Tiv V, Prof., Rev., Chief">
                </div>

                <h3 class="hf-form-heading">Classification</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label required">Category</label>
                        <select name="category" id="hfCategory" class="form-select" onchange="hfToggleSubcategory(this.value)" required>
                            <option value="">Select a category…</option>
                            <?php foreach (HistoricalFigure::CATEGORIES as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($item['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="hfSubcategoryGroup" style="display:none;">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory" id="hfSubcategory" class="form-select">
                            <option value="">Select…</option>
                            <?php foreach (HistoricalFigure::TRADITIONAL_SUBCATEGORIES as $sub): ?>
                            <option value="<?= e($sub) ?>" <?= ($item['subcategory'] ?? '') === $sub ? 'selected' : '' ?>><?= e($sub) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Only applies to Traditional Leadership</p>
                    </div>
                </div>

                <div class="form-group" id="hfReignOrderGroup" style="display:none;max-width:220px;">
                    <label class="form-label">Reign Order</label>
                    <input type="number" name="reign_order" id="hfReignOrder" class="form-input" min="1" step="1" value="<?= $old('reign_order') ?>" placeholder="e.g. 1, 2, 3…">
                    <p class="form-hint">Ordinal position within the subcategory — e.g. 3 for the third Tor Tiv. Rendered as "Tor Tiv III".</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Historical Period</label>
                    <select name="historical_period" class="form-select">
                        <option value="">Select…</option>
                        <?php foreach (HistoricalFigure::HISTORICAL_PERIODS as $period): ?>
                        <option value="<?= e($period) ?>" <?= ($item['historical_period'] ?? '') === $period ? 'selected' : '' ?>><?= e($period) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h3 class="hf-form-heading">Biographical Facts</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Unspecified</option>
                            <option value="male" <?= ($item['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($item['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Religion</label>
                        <input type="text" name="religion" class="form-input" value="<?= $old('religion') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-input" value="<?= $old('occupation') ?>">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" name="date_of_birth" class="form-input" value="<?= $old('date_of_birth') ?>" placeholder="e.g. circa 1850s, 12 March 1912, Unknown">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Birth Year</label>
                        <input type="number" name="birth_year" class="form-input" value="<?= $old('birth_year') ?>" placeholder="Numeric year, for filtering/sorting">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Date of Death</label>
                        <input type="text" name="date_of_death" class="form-input" value="<?= $old('date_of_death') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Death Year</label>
                        <input type="number" name="death_year" class="form-input" value="<?= $old('death_year') ?>">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" class="form-input" value="<?= $old('place_of_birth') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Burial Place</label>
                        <input type="text" name="burial_place" class="form-input" value="<?= $old('burial_place') ?>">
                    </div>
                </div>

                <h3 class="hf-form-heading">Geography &amp; Affiliation</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Clan</label>
                        <input type="text" name="clan" class="form-input" value="<?= $old('clan') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">District</label>
                        <input type="text" name="district" class="form-input" value="<?= $old('district') ?>">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Local Government</label>
                        <input type="text" name="local_government" class="form-input" value="<?= $old('local_government') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-input" value="<?= $old('state', 'Benue') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-input" value="<?= $old('country', 'Nigeria') ?>">
                </div>

                <h3 class="hf-form-heading">Narrative</h3>
                <div class="form-group">
                    <label class="form-label">Short Summary</label>
                    <textarea name="short_summary" class="form-input" rows="2" maxlength="500"><?= $old('short_summary') ?></textarea>
                    <p class="form-hint">Brief excerpt shown on list/card views (max 500 characters)</p>
                </div>
                <?php foreach ([
                    'biography'                => 'Biography',
                    'early_life'               => 'Early Life',
                    'education'                => 'Education',
                    'career'                   => 'Career',
                    'leadership_service'       => 'Leadership / Service',
                    'achievements'             => 'Major Achievements',
                    'historical_significance'  => 'Historical Significance',
                    'legacy'                   => 'Legacy',
                ] as $field => $label): ?>
                <div class="form-group">
                    <label class="form-label"><?= e($label) ?></label>
                    <textarea name="<?= $field ?>" class="form-input" rows="4"><?= $old($field) ?></textarea>
                </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <label class="form-label">Timeline</label>
                    <textarea name="timeline_notes" class="form-input" rows="4" placeholder="One event per line, e.g.&#10;1946 — Installed as first Tor Tiv&#10;1956 — Died in office"><?= $old('timeline_notes') ?></textarea>
                    <p class="form-hint">One "YEAR — event" per line</p>
                </div>

                <h3 class="hf-form-heading">Media</h3>
                <div class="form-group">
                    <label class="form-label">Hero Photo</label>
                    <?php if ($isEdit && !empty($item['image'])): ?>
                    <div style="margin-bottom:.6rem;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="Current photo" style="max-width:160px;border-radius:.5rem;display:block;">
                        <p class="form-hint">Current photo — upload a new file below to replace it</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp">
                    <p class="form-hint">JPG, PNG, GIF or WebP — max 5 MB</p>
                </div>

                <h3 class="hf-form-heading">References</h3>
                <div class="form-group">
                    <label class="form-label">Bibliography / Further Reading</label>
                    <textarea name="references_text" class="form-input" rows="3" placeholder="One citation per line"><?= $old('references_text') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Linked Source</label>
                    <select name="source_id" class="form-select">
                        <option value="">None</option>
                        <?php foreach ($sources ?? [] as $src): ?>
                        <option value="<?= (int) $src['id'] ?>" <?= (int) ($item['source_id'] ?? 0) === (int) $src['id'] ? 'selected' : '' ?>>
                            <?= e($src['contributor_name'] ?? $src['author'] ?? ('Source #' . $src['id'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint">Powers the "Source Reference" panel on the public page</p>
                </div>

                <h3 class="hf-form-heading">Publishing</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($item['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-group" style="align-self:end;">
                        <label class="form-checkbox">
                            <input type="checkbox" name="is_featured" value="1" <?= !empty($item['is_featured']) ? 'checked' : '' ?>>
                            <span>Feature this figure</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:.8rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Add Historical Figure' ?></button>
                    <a href="<?= url('admin/historical-figures') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

<?php if ($isEdit): ?>
<!-- ── Gallery Manager ──────────────────────────────── -->
<div class="admin-wrapper" style="margin-top:1.5rem;">
<div class="admin-content" style="max-width:900px;">
    <div class="admin-card" style="padding:1.75rem;">
        <h2 style="font-family:var(--font-heading);font-size:1.15rem;color:var(--color-primary);margin:0 0 1.25rem;">
            &#128247; Gallery
            <span style="font-size:.8rem;font-weight:400;color:var(--color-muted);margin-left:.5rem;"><?= count($gallery ?? []) ?> photo<?= count($gallery ?? []) !== 1 ? 's' : '' ?></span>
        </h2>

        <?php if (!empty($gallery)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;margin-bottom:1.5rem;">
            <?php foreach ($gallery as $photo): ?>
            <div style="position:relative;border-radius:.5rem;overflow:hidden;border:2px solid <?= $photo['is_featured'] ? 'var(--color-accent,#c8832a)' : 'var(--color-border-light,#e2ddd8)' ?>;">
                <img
                    src="<?= e(UPLOADS_URL . '/images/' . $photo['image_path']) ?>"
                    alt="<?= e($photo['alt_text'] ?? $photo['caption'] ?? 'Gallery photo') ?>"
                    style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block;"
                >
                <?php if ($photo['is_featured']): ?>
                <span style="position:absolute;top:.3rem;left:.3rem;background:var(--color-accent,#c8832a);color:#fff;font-size:.65rem;padding:.15rem .4rem;border-radius:999px;font-weight:600;">HERO</span>
                <?php endif; ?>
                <?php if (!empty($photo['caption'])): ?>
                <p style="font-size:.7rem;padding:.3rem .5rem;margin:0;background:var(--color-bg-soft,#f4f0eb);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($photo['caption']) ?></p>
                <?php endif; ?>
                <form action="<?= url('admin/historical-figures/' . $item['id'] . '/gallery/' . $photo['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Remove this photo from the gallery?');" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" style="width:100%;padding:.35rem;background:#fee2e2;color:#b91c1c;border:none;cursor:pointer;font-size:.75rem;font-weight:600;">
                        &#10005; Remove
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:var(--color-muted);font-size:.9rem;margin-bottom:1.25rem;">No photos yet. Upload the first one below.</p>
        <?php endif; ?>

        <form action="<?= url('admin/historical-figures/' . $item['id'] . '/gallery/upload') ?>" method="POST" enctype="multipart/form-data" style="border-top:1px solid var(--color-border-light,#e2ddd8);padding-top:1.25rem;">
            <?= csrf_field() ?>
            <h3 style="font-size:.95rem;font-weight:600;margin:0 0 1rem;color:var(--color-heading);">Upload New Photo</h3>
            <div class="form-group">
                <label class="form-label required">Photo File</label>
                <input type="file" name="gallery_image" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <p class="form-hint">JPG, PNG, GIF or WebP — max 5 MB</p>
            </div>
            <div class="form-group">
                <label class="form-label">Caption</label>
                <input type="text" name="caption" class="form-input" placeholder="Short description of the photo">
            </div>
            <div class="form-group">
                <label class="form-label">Alt Text</label>
                <input type="text" name="alt_text" class="form-input" placeholder="Describe the image for screen readers">
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1">
                    <span>Set as hero image</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Upload Photo</button>
        </form>
    </div>
</div>
</div>
<?php endif; ?>

<style>
.hf-form-heading { font-size: .95rem; font-weight: 700; color: var(--color-primary, #5C3A21); margin: 1.75rem 0 1rem; padding-bottom: .4rem; border-bottom: 1px solid #eee1d3; }
.hf-form-heading:first-of-type { margin-top: 0; }
</style>

<script>
function hfToggleSubcategory(category) {
    var group = document.getElementById('hfSubcategoryGroup');
    var select = document.getElementById('hfSubcategory');
    var reignGroup = document.getElementById('hfReignOrderGroup');
    var reignInput = document.getElementById('hfReignOrder');
    if (category === 'Traditional Leadership') {
        group.style.display = '';
        reignGroup.style.display = '';
    } else {
        group.style.display = 'none';
        select.value = '';
        reignGroup.style.display = 'none';
        reignInput.value = '';
    }
}
hfToggleSubcategory(document.getElementById('hfCategory').value);
</script>
