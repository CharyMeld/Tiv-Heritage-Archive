<div class="auth-page-wrap">
    <div class="auth-glass-card">
        <img src="<?= asset('images/logo.png') ?>" alt="<?= SITE_NAME ?>" class="auth-glass-logo">
        <h1 class="auth-glass-title">Welcome Back</h1>
        <p class="auth-glass-sub">Sign in to your Tiv Archive account</p>

        <?php if (!empty($errors['general'] ?? '')): ?>
        <div class="alert alert-error" style="margin-bottom:1rem;">
            <span><?= e($errors['general']) ?></span>
        </div>
        <?php endif; ?>

        <form action="<?= url('login') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label required" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="<?= e(old('email')) ?>" required autocomplete="email" autofocus>
                <?php if (!empty($errors['email'] ?? '')): ?><p class="form-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label required" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
                <?php if (!empty($errors['password'] ?? '')): ?><p class="form-error"><?= e($errors['password']) ?></p><?php endif; ?>
            </div>
            <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
                <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;font-family:var(--font-ui);cursor:pointer;">
                    <input type="checkbox" name="remember" value="1" style="accent-color:var(--color-primary);">
                    Remember me
                </label>
                <a href="<?= url('forgot-password') ?>" style="font-size:0.82rem;font-family:var(--font-ui);color:var(--color-accent);">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="border-radius:50px;margin-top:0.5rem;">Sign In</button>
        </form>
        <div class="auth-glass-footer">
            Don&rsquo;t have an account? <a href="<?= url('register') ?>">Create one</a>
        </div>
    </div>
</div>
