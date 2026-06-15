<div class="profile-banner">
    <div class="container">
        <div class="profile-banner-avatar">
            <?= mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <h1 class="profile-banner-name"><?= e($user['name'] ?? 'User') ?></h1>
        <span class="profile-banner-role"><?= ucfirst(e($user['role'] ?? 'member')) ?></span>
    </div>
</div>

<div style="padding: 2rem 0 3rem; background: var(--color-bg);">
    <div class="container" style="max-width:520px;">

        <div style="margin-bottom:1.25rem;">
            <a href="<?= url('profile') ?>" style="font-size:0.85rem; color:var(--color-text-muted); text-decoration:none;">
                &larr; Back to Profile
            </a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">&#128274; Change Password</h2>
            </div>
            <div class="admin-card-body">

                <?php if (!empty($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?>" style="margin-bottom:1rem;">
                    <?= e($_SESSION['flash_message']) ?>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>

                <?php $errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']); ?>

                <form action="<?= url('profile/password') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label required">Current Password</label>
                        <input type="password" name="current_password" class="form-input <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                               placeholder="Enter your current password" required autocomplete="current-password">
                        <?php if (isset($errors['current_password'])): ?>
                        <p class="form-error"><?= e($errors['current_password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">New Password</label>
                        <input type="password" name="new_password" class="form-input <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                               placeholder="At least 8 characters" required minlength="8" autocomplete="new-password">
                        <?php if (isset($errors['new_password'])): ?>
                        <p class="form-error"><?= e($errors['new_password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                               placeholder="Repeat new password" required minlength="8" autocomplete="new-password">
                        <?php if (isset($errors['confirm_password'])): ?>
                        <p class="form-error"><?= e($errors['confirm_password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Password</button>
                </form>

            </div>
        </div>

    </div>
</div>
