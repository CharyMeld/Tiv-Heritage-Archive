<section class="section">
    <div class="container" style="max-width:640px">
        <div class="card" style="padding:2rem;text-align:center">

            <?php if ($is_new): ?>
                <div style="font-size:3rem;margin-bottom:.5rem">🎉</div>
                <h1 style="margin-top:0;color:var(--color-primary)">Key Generated!</h1>
                <p style="color:#555">
                    Welcome, <strong><?= e($name) ?></strong>. Your API key has been created.
                    Copy it now — for security it will not be shown again in full on this page.
                </p>
            <?php else: ?>
                <div style="font-size:3rem;margin-bottom:.5rem">🔑</div>
                <h1 style="margin-top:0">Your Existing Key</h1>
                <p style="color:#555">
                    An active API key already exists for this email address.
                </p>
            <?php endif; ?>

            <div style="position:relative;margin:1.5rem 0">
                <input id="apiKeyValue"
                       type="text"
                       readonly
                       value="<?= e($api_key) ?>"
                       style="width:100%;box-sizing:border-box;font-family:monospace;
                              font-size:1rem;padding:.75rem 3rem .75rem 1rem;
                              border:2px solid var(--color-primary);border-radius:8px;
                              background:#f0f7ff;color:#1a1a2e;outline:none">
                <button onclick="copyKey()"
                        title="Copy"
                        style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;font-size:1.2rem"
                        id="copyBtn">📋</button>
            </div>

            <p style="font-size:.85rem;color:#888;margin-bottom:1.5rem">
                Keep this key private. Do not commit it to public repositories.
            </p>

            <a href="<?= url('api/request-key') ?>" class="btn btn-outline" style="margin-right:.5rem">
                Back
            </a>
            <a href="<?= url('archive') ?>" class="btn btn-primary">
                Browse Archive
            </a>

            <hr style="margin:2rem 0">

            <div style="text-align:left;font-size:.9rem;color:#555">
                <h3 style="margin-top:0">Quick start</h3>
                <pre style="background:#f4f4f5;border-radius:6px;padding:1rem;overflow-x:auto;font-size:.82rem"># Search
curl -H "X-API-Key: <?= e($api_key) ?>" \
     "<?= e(url('api/search?q=akura')) ?>"

# Daily word
curl -H "X-API-Key: <?= e($api_key) ?>" \
     "<?= e(url('api/daily-word')) ?>"

# Random proverb
curl -H "X-API-Key: <?= e($api_key) ?>" \
     "<?= e(url('api/random-proverb')) ?>"</pre>
                <p style="margin-bottom:0">Rate limit: <strong>1,000 requests/day</strong>. Resets at midnight.</p>
            </div>
        </div>
    </div>
</section>

<script>
function copyKey() {
    const input = document.getElementById('apiKeyValue');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✅';
        setTimeout(() => btn.textContent = '📋', 2000);
    });
}
</script>
