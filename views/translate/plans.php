<section class="archive-section">
    <div class="container">

        <div class="page-header" style="justify-content:center;text-align:center;display:block;margin-bottom:2rem;">
            <h1 class="page-title">Translation Plans</h1>
            <p class="page-subtitle">Unlock unlimited Tiv translations</p>
        </div>

        <?php if ($isPaid): ?>
        <div class="alert alert-success" style="max-width:500px;margin:0 auto 2rem;">
            You have an active paid plan. Enjoy unlimited translations!
        </div>
        <?php endif; ?>

        <div class="plans-grid">
            <!-- Free -->
            <div class="plan-card">
                <div class="plan-header">
                    <h2 class="plan-name">Free</h2>
                    <div class="plan-price"><span class="price-amount">&#8358;0</span></div>
                    <p class="plan-period">Forever free</p>
                </div>
                <ul class="plan-features">
                    <li>15 translations per day (registered users)</li>
                    <li>5 translations per day (guests)</li>
                    <li>All translation types</li>
                    <li>Word-by-word breakdown</li>
                    <li>Basic explanations</li>
                </ul>
                <a href="<?= url('translate') ?>" class="btn btn-secondary plan-btn">Start Translating</a>
            </div>

            <!-- Paid (scaffold — payment gateway to be integrated) -->
            <div class="plan-card plan-card--featured">
                <div class="plan-badge">Popular</div>
                <div class="plan-header">
                    <h2 class="plan-name">Premium Monthly</h2>
                    <div class="plan-price"><span class="price-amount">&#8358;500</span></div>
                    <p class="plan-period">per month</p>
                </div>
                <ul class="plan-features">
                    <li>Unlimited translations daily</li>
                    <li>Full cultural explanations</li>
                    <li>Proverb meaning details</li>
                    <li>Translation history saved</li>
                    <li>Priority matching</li>
                    <li>Support heritage preservation</li>
                </ul>
                <a href="<?= url('contact') ?>" class="btn btn-primary plan-btn">Contact to Subscribe</a>
                <p class="plan-note">Payment gateway integration coming soon. Contact us to activate.</p>
            </div>

            <!-- Credits -->
            <div class="plan-card">
                <div class="plan-header">
                    <h2 class="plan-name">Credit Pack</h2>
                    <div class="plan-price"><span class="price-amount">&#8358;200</span></div>
                    <p class="plan-period">100 translations</p>
                </div>
                <ul class="plan-features">
                    <li>100 translation credits</li>
                    <li>Never expire</li>
                    <li>Full explanations</li>
                    <li>All content categories</li>
                </ul>
                <a href="<?= url('contact') ?>" class="btn btn-secondary plan-btn">Contact to Purchase</a>
                <p class="plan-note">Payment via Paystack/Flutterwave coming soon.</p>
            </div>
        </div>

        <p style="text-align:center;color:#888;margin-top:2rem;font-size:.9rem;">
            Revenue from paid plans directly supports the preservation of Tiv cultural heritage.
        </p>
    </div>
</section>

<style>
.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; max-width: 900px; margin: 0 auto; }
.plan-card { background: #fff; border-radius: 12px; padding: 2rem 1.5rem; box-shadow: 0 2px 16px rgba(0,0,0,.08); position: relative; display: flex; flex-direction: column; gap: 1rem; }
.plan-card--featured { border: 2px solid var(--color-primary, #5C3A21); }
.plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--color-primary, #5C3A21); color: #fff; padding: .2em .9em; border-radius: 20px; font-size: .75rem; font-weight: 700; letter-spacing: .05em; white-space: nowrap; }
.plan-header { text-align: center; }
.plan-name { font-size: 1.2rem; font-weight: 700; color: #222; margin-bottom: .5rem; }
.plan-price { margin-bottom: .25rem; }
.price-amount { font-size: 2rem; font-weight: 800; color: var(--color-primary, #5C3A21); }
.plan-period { font-size: .85rem; color: #888; }
.plan-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .5rem; }
.plan-features li::before { content: '✓ '; color: #2e7d32; font-weight: 700; }
.plan-features li { font-size: .9rem; color: #444; }
.plan-btn { text-align: center; width: 100%; margin-top: auto; }
.plan-note { font-size: .75rem; color: #aaa; text-align: center; margin: 0; }
</style>
