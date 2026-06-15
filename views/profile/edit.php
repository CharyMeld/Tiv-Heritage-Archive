<!-- Page Banner -->
<div class="page-banner">
    <div class="container">
        <a href="<?= url('profile') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Profile
        </a>
        <h1 class="page-banner-title">Edit Profile</h1>
        <p class="page-banner-sub">Update your information and password</p>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container" style="max-width: 620px;">
        <?php $errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']); ?>

        <!-- Profile Information -->
        <div class="contribute-form-card">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; color: var(--color-primary); margin-bottom: 1.5rem;">Profile Information</h3>
            <form action="<?= url('profile/edit') ?>" method="POST" data-validate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name" class="form-label required">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input"
                           value="<?= old('name', $user['name']) ?>" required autocomplete="name">
                    <?php if (isset($errors['name'])): ?>
                        <div class="form-error"><?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?= old('email', $user['email']) ?>" required autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea id="bio" name="bio" class="form-textarea"
                              placeholder="Tell us about yourself..."><?= old('bio', $user['bio']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-full">Update Profile</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="contribute-form-card" style="margin-top: 1.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; color: var(--color-primary); margin-bottom: 1.5rem;">Change Password</h3>
            <form action="<?= url('profile/password') ?>" method="POST" data-validate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="current_password" class="form-label required">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required autocomplete="current-password">
                    <?php if (isset($errors['current_password'])): ?>
                        <div class="form-error"><?= e($errors['current_password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label required">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required autocomplete="new-password">
                    <p class="form-hint">At least 8 characters</p>
                    <?php if (isset($errors['new_password'])): ?>
                        <div class="form-error"><?= e($errors['new_password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label required">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required autocomplete="new-password">
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="form-error"><?= e($errors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary w-full">Change Password</button>
            </form>
        </div>
    </div>
</div>
