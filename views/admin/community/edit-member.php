<div class="admin-wrapper">
    <div class="admin-content" style="max-width:600px;">

        <div style="margin-bottom:1.2rem;">
            <a href="<?= url('admin/community/members') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; Back to Members</a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Edit Member: <?= htmlspecialchars($member['full_name']) ?></h2>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/community/members/' . $member['id'] . '/edit') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($member['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Member Type</label>
                        <select name="member_type" class="form-select">
                            <option value="contributor" <?= $member['member_type']==='contributor'?'selected':'' ?>>Contributor</option>
                            <option value="researcher"  <?= $member['member_type']==='researcher'?'selected':'' ?>>Researcher</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Area of Interest</label>
                        <input type="text" name="area_of_interest" class="form-input"
                               value="<?= htmlspecialchars($member['area_of_interest'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input"
                               value="<?= htmlspecialchars($member['location'] ?? '') ?>" placeholder="State, Country">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Short Biography</label>
                        <textarea name="short_bio" class="form-textarea" rows="4"><?= htmlspecialchars($member['short_bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Profile Photo</label>
                        <?php if (!empty($member['profile_photo'])): ?>
                            <div style="margin-bottom:.5rem;">
                                <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($member['profile_photo']) ?>"
                                     style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:1px solid #e5e0d5;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="profile_photo" class="form-input" accept="image/*">
                        <p class="form-hint">Upload new photo to replace current</p>
                    </div>

                    <div style="display:flex;gap:1rem;">
                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                            <input type="checkbox" name="is_featured" value="1" <?= $member['is_featured']?'checked':'' ?>>
                            <span>Featured Member</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= $member['is_active']?'checked':'' ?>>
                            <span>Active (visible in directory)</span>
                        </label>
                    </div>

                    <div style="margin-top:1.2rem;display:flex;gap:.8rem;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="<?= url('admin/community/members') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
