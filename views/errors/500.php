<div class="error-page-wrap">
    <div style="position:relative;z-index:1;text-align:center;">
        <div class="error-page-code">500</div>
        <h1 class="error-page-title">Something Went Wrong</h1>
        <p class="error-page-sub">We encountered an unexpected error. Please try again or contact us if the problem persists.</p>
        <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= url('/') ?>" class="btn home-btn-primary">Go Home</a>
            <a href="<?= url('contact') ?>" class="btn home-btn-ghost">Contact Us</a>
        </div>
    </div>
</div>
