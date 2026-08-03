<div class="auth-page-wrap">
    <div class="auth-glass-card">
        <img src="<?= asset('images/logo.png') ?>" alt="<?= SITE_NAME ?>" class="auth-glass-logo">
        <h1 class="auth-glass-title">Join the Archive</h1>
        <p class="auth-glass-sub">Create an account to contribute to Tiv cultural heritage</p>

        <form action="<?= url('register') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label required" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input"
                       value="<?= old('name') ?>" required autocomplete="name" autofocus>
                <?php if (!empty($errors['name'] ?? '')): ?><p class="form-error"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label required" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="<?= old('email') ?>" required autocomplete="email">
                <?php if (!empty($errors['email'] ?? '')): ?><p class="form-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label required" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required autocomplete="new-password">
                <p class="form-hint">Minimum 8 characters</p>
                <?php if (!empty($errors['password'] ?? '')): ?><p class="form-error"><?= e($errors['password']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label required" for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" required autocomplete="new-password">
                <?php if (!empty($errors['password_confirm'] ?? '')): ?><p class="form-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="border-radius:50px;margin-top:0.5rem;">Create Account</button>
        </form>
        <div class="auth-glass-footer">
            Already have an account? <a href="<?= url('login') ?>">Sign in</a>
        </div>
    </div>
</div>
