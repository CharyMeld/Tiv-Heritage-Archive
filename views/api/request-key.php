<section class="section">
    <div class="container" style="max-width:640px">
        <div class="card" style="padding:2rem">
            <h1 style="margin-top:0">Request an API Key</h1>
            <p style="color:#555;margin-bottom:1.5rem">
                Fill in the form below to receive a free API key that grants programmatic access
                to the Tiv Heritage Archive data. Keys are auto-generated instantly.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom:1.25rem">
                    <ul style="margin:0;padding-left:1.25rem">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('api/request-key') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Your Name *</label>
                    <input class="form-control" type="text" id="name" name="name"
                           value="<?= e($old['name'] ?? '') ?>" required
                           placeholder="e.g. John Adamu" autocomplete="name">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input class="form-control" type="email" id="email" name="email"
                           value="<?= e($old['email'] ?? '') ?>" required
                           placeholder="you@example.com" autocomplete="email">
                    <small class="form-hint">Your key will be tied to this email. Submitting again with the same email returns your existing key.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="purpose">Intended Use (optional)</label>
                    <textarea class="form-control" id="purpose" name="purpose" rows="3"
                              placeholder="e.g. research project, mobile app, data analysis"><?= e($old['purpose'] ?? '') ?></textarea>
                </div>

                <button class="btn btn-primary" type="submit" style="width:100%">
                    Generate My API Key
                </button>
            </form>

            <hr style="margin:2rem 0">

            <h3 style="margin-top:0;font-size:1rem">How to use your key</h3>
            <p style="font-size:.9rem;color:#555;margin-bottom:.5rem">
                Pass the key via the <code>X-API-Key</code> request header or the <code>api_key</code> query parameter:
            </p>
            <pre style="background:#f4f4f5;border-radius:6px;padding:1rem;font-size:.85rem;overflow-x:auto"># Header (recommended)
curl -H "X-API-Key: tiv_your_key_here" \
     <?= e(url('api/search?q=akura')) ?>


# Query parameter
<?= e(url('api/search?q=akura&api_key=tiv_your_key_here')) ?></pre>

            <p style="font-size:.85rem;color:#777;margin-bottom:0">
                Default rate limit: <strong>1,000 requests / day</strong>. Contact us if you need a higher limit.
            </p>
        </div>
    </div>
</section>
