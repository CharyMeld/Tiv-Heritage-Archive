<section style="padding:4rem 0;min-height:60vh;display:flex;align-items:center;">
    <div class="container">
        <div style="max-width:500px;margin:0 auto;text-align:center;">
            <?php if ($success): ?>
                <div style="font-size:3.5rem;margin-bottom:1rem;">&#10003;</div>
                <h1 style="color:#5C3A21;font-size:1.5rem;">Unsubscribed Successfully</h1>
                <p style="color:#5a4a3a;line-height:1.7;">
                    <strong><?= e($name ?? 'You') ?></strong> will no longer receive outreach emails from the Tiv Heritage Archive.
                    You can always contact us if you change your mind.
                </p>
            <?php else: ?>
                <div style="font-size:3.5rem;margin-bottom:1rem;">&#10007;</div>
                <h1 style="color:#5C3A21;font-size:1.5rem;">Invalid Link</h1>
                <p style="color:#5a4a3a;"><?= e($message ?? 'This unsubscribe link is not valid.') ?></p>
            <?php endif; ?>
            <a href="<?= url('/') ?>" class="btn btn-secondary" style="margin-top:1.5rem;">Go to Homepage</a>
        </div>
    </div>
</section>
