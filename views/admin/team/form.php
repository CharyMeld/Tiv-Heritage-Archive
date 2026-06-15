<?php
$isEdit    = !empty($member);
$action    = $isEdit
    ? url('admin/team/' . $member['id'] . '/edit')
    : url('admin/team/create');
$socials   = $isEdit ? TeamMember::decodeSocials($member['socials'] ?? null) : [];
$contribs  = $isEdit ? TeamMember::decodeContributions($member['contributions'] ?? null) : [];
$imgUrl    = ($isEdit && !empty($member['image']))
    ? UPLOADS_URL . '/team/' . e($member['image'])
    : null;

function teamOld(string $key, $member, $default = ''): string {
    $oldVal = old($key);
    $val    = ($oldVal !== '') ? $oldVal : ($member[$key] ?? $default);
    return e((string)$val);
}
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $isEdit ? 'Edit Team Member' : 'Add Team Member' ?></h1>
        <p class="admin-page-sub"><?= $isEdit ? 'Update the member\'s information' : 'Fill in the details for the new team member' ?></p>
    </div>
    <a href="<?= url('admin/team') ?>" class="btn btn-secondary">&larr; Back</a>
</div>

<div style="max-width:820px;">
<form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="admin-card" style="padding:1.75rem;">
    <?= csrf_field() ?>

    <!-- ── Basic Info ── -->
    <h3 style="margin:0 0 1.25rem;font-size:1rem;letter-spacing:0.05em;text-transform:uppercase;color:var(--color-text-muted);">Basic Information</h3>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
            <label for="name" class="form-label required">Full Name</label>
            <input type="text" id="name" name="name" class="form-input"
                   value="<?= teamOld('name', $member) ?>" required>
        </div>
        <div class="form-group">
            <label for="role" class="form-label required">Role / Title</label>
            <input type="text" id="role" name="role" class="form-input"
                   value="<?= teamOld('role', $member) ?>"
                   placeholder="e.g. Lead Developer" required>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
            <label for="category" class="form-label required">Category</label>
            <select id="category" name="category" class="form-select">
                <?php foreach (['developer'=>'Developer','researcher'=>'Researcher','contributor'=>'Contributor'] as $val=>$lbl): ?>
                <?php $selCat = (old('category') !== '') ? old('category') : ($member['category'] ?? 'contributor'); ?>
                <option value="<?= $val ?>" <?= $selCat === $val ? 'selected' : '' ?>>
                    <?= $lbl ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sort_order" class="form-label">Display Order</label>
            <input type="number" id="sort_order" name="sort_order" class="form-input"
                   value="<?= teamOld('sort_order', $member, '0') ?>" min="0">
            <p class="form-hint">Lower numbers appear first</p>
        </div>
    </div>

    <!-- User account link -->
    <div class="form-group">
        <label for="user_id" class="form-label">Link to User Account <small style="font-weight:400;">(enables auto contribution rating)</small></label>
        <?php
        $db    = Database::getInstance();
        $users = $db->query("SELECT id, name, email FROM users ORDER BY name ASC")->fetchAll();
        ?>
        <select id="user_id" name="user_id" class="form-select">
            <option value="">— Not linked —</option>
            <?php foreach ($users as $u): ?>
            <?php $selUid = (old('user_id') !== '') ? old('user_id') : ($member['user_id'] ?? ''); ?>
            <option value="<?= $u['id'] ?>"
                <?= $selUid == $u['id'] ? 'selected' : '' ?>>
                <?= e($u['name']) ?> (<?= e($u['email']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
        <p class="form-hint">Links this team member to a site user so their content count is auto-pulled from the database.</p>
    </div>

    <!-- ── Photo ── -->
    <div class="form-group" style="margin-top:0.5rem;">
        <label for="image" class="form-label">Profile Photo</label>
        <?php if ($imgUrl): ?>
        <div style="margin-bottom:0.75rem;">
            <img src="<?= $imgUrl ?>" alt="Current photo"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;object-position:top;border:2px solid var(--color-border);">
            <p style="margin:0.35rem 0 0;font-size:0.8rem;color:var(--color-text-muted);">Current photo — upload a new one to replace it</p>
        </div>
        <?php endif; ?>
        <input type="file" id="image" name="image" class="form-input" accept="image/*"
               onchange="previewPhoto(this)">
        <img id="photoPreview" src="" alt="" style="display:none;margin-top:0.5rem;width:80px;height:80px;border-radius:50%;object-fit:cover;object-position:top;">
        <p class="form-hint">JPG, PNG, WebP — max 5MB. Square photos work best.</p>
    </div>

    <!-- ── Bio ── -->
    <div class="form-group">
        <label for="short_bio" class="form-label">Short Bio <small style="font-weight:400;">(shown on hover)</small></label>
        <input type="text" id="short_bio" name="short_bio" class="form-input"
               value="<?= teamOld('short_bio', $member) ?>"
               maxlength="160" placeholder="One-line description (max 160 chars)">
    </div>
    <div class="form-group">
        <label for="full_bio" class="form-label">Full Bio <small style="font-weight:400;">(shown in modal)</small></label>
        <textarea id="full_bio" name="full_bio" class="form-textarea" rows="4"
                  placeholder="Detailed biography..."><?= teamOld('full_bio', $member) ?></textarea>
    </div>

    <!-- ── Contributions ── -->
    <div class="form-group">
        <label for="contributions" class="form-label">Contributions</label>
        <textarea id="contributions" name="contributions" class="form-textarea" rows="4"
                  placeholder="One contribution per line, e.g.:&#10;Added 120 Proverbs&#10;Led System Development&#10;Cultural Research Lead"><?= e(implode("\n", $contribs)) ?></textarea>
        <p class="form-hint">Enter one contribution per line. These appear in the profile modal.</p>
    </div>

    <!-- ── Social Links ── -->
    <h3 style="margin:1.5rem 0 0.25rem;font-size:1rem;letter-spacing:0.05em;text-transform:uppercase;color:var(--color-text-muted);">Social Links</h3>
    <p style="font-size:0.85rem;color:var(--color-text-muted);margin-bottom:1.25rem;">Leave blank to hide a platform. Only filled platforms appear on the public profile.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.85rem 1.25rem;">
    <?php foreach ($platforms as $key => $platform): ?>
    <div class="form-group" style="margin:0;">
        <label for="social_<?= $key ?>" class="form-label" style="font-size:0.85rem;">
            <?= $platform['icon'] ?> <?= $platform['label'] ?>
        </label>
        <input type="url" id="social_<?= $key ?>" name="social_<?= $key ?>"
               class="form-input" style="font-size:0.875rem;"
               value="<?= e($socials[$key] ?? '') ?>"
               placeholder="https://...">
    </div>
    <?php endforeach; ?>

    <!-- WhatsApp gets special handling (phone number or wa.me link) -->
    </div>

    <!-- ── Visibility ── -->
    <div class="form-group" style="margin-top:1.5rem;">
        <label class="form-checkbox">
            <input type="checkbox" name="is_active" value="1"
                <?= ((old('is_active') !== '') ? old('is_active') : ($member['is_active'] ?? 1)) ? 'checked' : '' ?>>
            <span>Show on homepage (active)</span>
        </label>
    </div>

    <div style="display:flex;gap:1rem;margin-top:1.5rem;">
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Save Changes' : 'Add Team Member' ?>
        </button>
        <a href="<?= url('admin/team') ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>
</div>

<script>
function previewPhoto(input) {
    var preview = document.getElementById('photoPreview');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
