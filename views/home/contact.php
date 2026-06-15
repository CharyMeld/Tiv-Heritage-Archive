<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128140; Contact</span>
        <h1 class="page-banner-title">Get in Touch</h1>
        <p class="page-banner-sub">Questions, feedback or contributions &mdash; we&rsquo;d love to hear from you</p>
    </div>
</div>

<section class="content-section">
    <div class="container">
        <div class="auth-container" style="max-width: 600px;">
            <?php $errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']); ?>

            <form action="<?= url('contact') ?>" method="POST" class="auth-box" data-validate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name" class="form-label required">Your Name</label>
                    <input type="text" id="name" name="name" class="form-input"
                           value="<?= old('name', $user['name'] ?? '') ?>" required autocomplete="name">
                    <?php if (isset($errors['name'])): ?>
                        <div class="form-error"><?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?= old('email', $user['email'] ?? '') ?>" required autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-input"
                           value="<?= old('subject') ?>" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="message" class="form-label required">Message</label>
                    <textarea id="message" name="message" class="form-textarea" required><?= old('message') ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <div class="form-error"><?= e($errors['message']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary w-full">Send Message</button>
            </form>

            <div class="text-center mt-4">
                <p style="color: #666;">
                    You can also reach us at:<br>
                    <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>
                </p>
            </div>
        </div>
    </div>
</section>
