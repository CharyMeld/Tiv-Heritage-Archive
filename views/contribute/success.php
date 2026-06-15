<div class="error-page-wrap" style="background: linear-gradient(160deg, #0d1a0a 0%, #1a2d10 40%, #1f3d15 100%);">
    <div class="error-page-inner">
        <div class="error-code" style="font-size: 5rem; color: #4caf50;">&#10003;</div>
        <h1 class="error-title">Thank You!</h1>
        <p class="error-message">Your contribution has been submitted and is awaiting review.</p>

        <div class="contribute-form-card" style="max-width: 480px; margin: 2rem auto; text-align: left; background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.12);">
            <h4 style="color: #fff; font-family: var(--font-heading); margin-bottom: 1rem; font-size: 1rem;">What happens next?</h4>
            <ol style="padding-left: 1.4rem; margin: 0; color: rgba(255,255,255,0.75); line-height: 2;">
                <li>Our team reviews your submission</li>
                <li>We may reach out if we need clarification</li>
                <li>Once approved, it will appear in the archive</li>
                <li>You'll receive a status notification</li>
            </ol>
        </div>

        <div class="error-actions">
            <a href="<?= url('contribute') ?>" class="btn btn-primary">Submit Another</a>
            <a href="<?= url('archive') ?>" class="btn btn-secondary" style="border-color: rgba(255,255,255,0.3); color: rgba(255,255,255,0.8);">Browse Archive</a>
        </div>
    </div>
</div>
