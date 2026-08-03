<?php
$statusBg = match($settings['connection_status']) {
    'connected' => 'background:#d1fae5;color:#065f46;',
    'error' => 'background:#fee2e2;color:#991b1b;',
    default => 'background:#f3f4f6;color:#374151;',
};
$igStatusBg = match($settings['instagram_connection_status'] ?? 'disconnected') {
    'connected' => 'background:#d1fae5;color:#065f46;',
    'error' => 'background:#fee2e2;color:#991b1b;',
    default => 'background:#f3f4f6;color:#374151;',
};
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#9881; Facebook &amp; Instagram Settings</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Connect a Facebook Page (and, optionally, its linked Instagram Business Account) to enable live publishing via the Meta Graph API.</p>
        </div>
        <a href="<?= url('admin/marketing/facebook/queue') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">Publishing Queue</a>
    </div>

    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;">
        <span>Facebook status:</span>
        <span style="<?= $statusBg ?>padding:.2rem .6rem;border-radius:10px;font-size:.85rem;font-weight:600;"><?= e(ucfirst($settings['connection_status'])) ?></span>
        <?php if ($settings['last_checked_at']): ?>
            <span style="font-size:.8rem;color:#7a6a5a;">Last checked <?= date('M j, Y g:ia', strtotime($settings['last_checked_at'])) ?></span>
        <?php endif; ?>
    </div>

    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.5rem;">
        <span>Instagram status:</span>
        <span style="<?= $igStatusBg ?>padding:.2rem .6rem;border-radius:10px;font-size:.85rem;font-weight:600;"><?= e(ucfirst($settings['instagram_connection_status'] ?? 'disconnected')) ?></span>
        <?php if (!empty($settings['instagram_last_checked_at'])): ?>
            <span style="font-size:.8rem;color:#7a6a5a;">Last checked <?= date('M j, Y g:ia', strtotime($settings['instagram_last_checked_at'])) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($settings['connection_status'] === 'error' && !empty($settings['last_error'])): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1rem;color:#991b1b;font-size:.88rem;">
            <strong>Facebook error:</strong> <?= e($settings['last_error']) ?>
        </div>
    <?php endif; ?>

    <?php if (($settings['instagram_connection_status'] ?? null) === 'error' && !empty($settings['instagram_last_error'])): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1.5rem;color:#991b1b;font-size:.88rem;">
            <strong>Instagram error:</strong> <?= e($settings['instagram_last_error']) ?>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:600px;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/facebook/settings') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">App ID</label>
                    <input type="text" name="app_id" class="form-input" value="<?= e($settings['app_id'] ?? '') ?>" placeholder="From your Meta Developer App">
                </div>

                <div class="form-group">
                    <label class="form-label">App Secret</label>
                    <input type="password" name="app_secret" class="form-input" autocomplete="new-password" placeholder="<?= $settings['has_app_secret'] ? '•••••••• (leave blank to keep current)' : 'Not set' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Page ID</label>
                    <input type="text" name="page_id" class="form-input" value="<?= e($settings['page_id'] ?? '') ?>" placeholder="The Facebook Page you'll publish to">
                </div>

                <div class="form-group">
                    <label class="form-label">Page Access Token</label>
                    <input type="password" name="page_access_token" class="form-input" autocomplete="new-password" placeholder="<?= $settings['has_page_token'] ? '•••••••• (leave blank to keep current)' : 'Not set' ?>">
                    <p class="form-hint">Stored encrypted at rest. Only Facebook <strong>Pages</strong> are supported, not personal profiles, per Meta's platform rules.</p>
                </div>

                <hr style="border:none;border-top:1px solid #e5e0d5;margin:1.4rem 0;">

                <div class="form-group">
                    <label class="form-label">Instagram Business Account ID</label>
                    <input type="text" name="instagram_business_account_id" class="form-input" value="<?= e($settings['instagram_business_account_id'] ?? '') ?>" placeholder="The Instagram Business Account linked to the Page above">
                    <p class="form-hint">Instagram publishing reuses the Page Access Token above — no separate Instagram credentials needed, just the Business Account's numeric ID (found via Meta Business Suite or <code>GET /{page-id}?fields=instagram_business_account</code>).</p>
                </div>

                <div style="display:flex;gap:.7rem;">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card" style="max-width:600px;margin-top:1.5rem;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/facebook/test') ?>">
                <?= csrf_field() ?>
                <p style="font-size:.88rem;color:#5a4a3a;margin:0 0 .8rem;">Verifies the Page ID and Access Token above actually work against Facebook right now.</p>
                <button type="submit" class="btn btn-secondary">&#128279; Test Facebook Connection</button>
            </form>
        </div>
    </div>

    <div class="admin-card" style="max-width:600px;margin-top:1.5rem;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/instagram/test') ?>">
                <?= csrf_field() ?>
                <p style="font-size:.88rem;color:#5a4a3a;margin:0 0 .8rem;">Verifies the Instagram Business Account ID above is real and readable with the saved Page Access Token.</p>
                <button type="submit" class="btn btn-secondary">&#128279; Test Instagram Connection</button>
            </form>
        </div>
    </div>

</div>
</div>
