<div class="auth-page-wrap">
    <div class="auth-glass-card" style="max-width: 420px;">
        <div class="auth-glass-logo">
            <span style="font-size: 2rem;">&#128274;</span>
        </div>
        <h2 class="auth-glass-title">Reset Password</h2>
        <p class="auth-glass-sub">Enter your email and we'll send you reset instructions.</p>

        <form action="<?= url('forgot-password') ?>" method="POST" data-validate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email" class="form-label required">Email Address</label>
                <input type="email" id="email" name="email" class="form-input"
                       placeholder="your@email.com" required autofocus autocomplete="email">
            </div>

            <button type="submit" class="btn btn-primary w-full" style="margin-top: 0.5rem;">
                Send Reset Link
            </button>
        </form>

        <div class="auth-footer" style="margin-top: 1.5rem; text-align: center;">
            <a href="<?= url('login') ?>" style="font-size: 0.875rem; color: var(--color-primary);">
                &larr; Back to Login
            </a>
        </div>
    </div>
</div>
