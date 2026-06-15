<section style="padding:4rem 0;">
    <div class="container" style="max-width:580px;margin:0 auto;text-align:center;">
        <div style="font-size:4rem;margin-bottom:1rem;">&#127881;</div>
        <h1 style="color:#5C3A21;margin-bottom:.7rem;">Submission Received!</h1>
        <p style="color:#5a4a3a;font-size:1rem;line-height:1.8;margin-bottom:2rem;">
            Thank you for contributing <strong><?= htmlspecialchars($subLabel) ?></strong> content to the Tiv Heritage Archive.
            Our team will review your submission and publish it if it meets our guidelines.
        </p>
        <div style="background:#fdf8f0;border:1px solid #C8A951;border-radius:10px;padding:1.4rem;margin-bottom:2rem;text-align:left;">
            <h3 style="color:#5C3A21;margin:0 0 .7rem;font-size:.95rem;">What happens next?</h3>
            <ul style="color:#5a4a3a;line-height:2.1;margin:0;padding-left:1.2rem;font-size:.88rem;">
                <li>Our editorial team reviews the submission for accuracy</li>
                <li>Approved content is published to the <?= htmlspecialchars($subLabel) ?> section</li>
                <li>Your name will be credited as the contributor</li>
            </ul>
        </div>
        <div style="display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap;">
            <?php if ($section && $sub): ?>
                <a href="<?= url("submit/{$section}/{$sub}") ?>" class="btn btn-primary">Submit Another</a>
                <a href="<?= url("{$section}/{$sub}") ?>" class="btn btn-secondary">Back to <?= htmlspecialchars($subLabel) ?></a>
            <?php endif; ?>
            <a href="<?= url('/') ?>" class="btn btn-secondary">Return Home</a>
        </div>
    </div>
</section>
