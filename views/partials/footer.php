<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-brand">
                <p class="footer-name"><?= SITE_NAME ?></p>
                <p class="footer-tagline">Preserving Language &bull; Culture &bull; Heritage</p>
            </div>
            <ul class="footer-links">
                <li><a href="<?= url('about') ?>">About</a></li>
                <li><a href="<?= url('archive') ?>">Explore</a></li>
                <li><a href="<?= url('contribute') ?>">Contribute</a></li>
                <li><a href="<?= url('contact') ?>">Contact</a></li>
                <li><a href="<?= url('privacy-policy') ?>">Privacy Policy</a></li>
                <li><a href="<?= url('terms-of-service') ?>">Terms of Service</a></li>
                <?php if (is_logged_in() && is_moderator()): ?>
                    <li><a href="<?= url('admin') ?>">Admin</a></li>
                <?php endif; ?>
            </ul>
            <p class="footer-copy">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        </div>
    </div>
</footer>
