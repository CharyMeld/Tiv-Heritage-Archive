<div class="profile-banner">
    <div class="container">
        <div class="profile-banner-avatar">
            <?= mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <h1 class="profile-banner-name"><?= e($user['name'] ?? 'User') ?></h1>
        <span class="profile-banner-role"><?= ucfirst(e($user['role'] ?? 'member')) ?></span>
        <p style="color:rgba(255,255,255,0.5);font-size:0.8rem;margin-top:0.5rem;font-family:var(--font-ui);">
            Member since <?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?>
        </p>
    </div>
</div>

<div style="padding: 0 0 2rem; background: var(--color-bg);">
    <div class="container">
        <!-- Stats -->
        <div class="profile-stats-modern" style="padding-top:1.5rem;">
            <div class="profile-stat-modern">
                <span class="profile-stat-modern-value"><?= number_format($totalSubmissions ?? 0) ?></span>
                <span class="profile-stat-modern-label">Submitted</span>
            </div>
            <div class="profile-stat-modern">
                <span class="profile-stat-modern-value"><?= number_format($approvedSubmissions ?? 0) ?></span>
                <span class="profile-stat-modern-label">Approved</span>
            </div>
            <div class="profile-stat-modern">
                <span class="profile-stat-modern-value"><?= number_format(($totalSubmissions??0) - ($approvedSubmissions??0)) ?></span>
                <span class="profile-stat-modern-label">Pending</span>
            </div>
        </div>

        <!-- Quick actions -->
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            <a href="<?= url('contribute') ?>" class="btn btn-primary" style="border-radius:50px;">+ Contribute</a>
            <a href="<?= url('profile/submissions') ?>" class="btn btn-secondary" style="border-radius:50px;">My Submissions</a>
            <a href="<?= url('profile/edit') ?>" class="btn btn-secondary" style="border-radius:50px;">Edit Profile</a>
            <a href="<?= url('profile/password') ?>" class="btn btn-secondary" style="border-radius:50px;">Change Password</a>
        </div>

        <!-- Recent contributions -->
        <?php if (!empty($recentSubmissions)): ?>
        <div class="admin-card" style="margin-bottom:1rem;">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Recent Contributions</h2>
                <a href="<?= url('profile/submissions') ?>" class="admin-card-link">View all</a>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php foreach ($recentSubmissions as $sub): ?>
                <div style="display:flex;align-items:center;gap:1rem;padding:0.875rem 1.25rem;border-bottom:1px solid var(--color-border-light);">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:0.9rem;color:var(--color-primary);"><?= e($sub['tiv_term']) ?></div>
                        <div style="font-size:0.78rem;color:var(--color-text-muted);font-family:var(--font-ui);"><?= ucfirst(e($sub['category'])) ?> &bull; <?= date('M j, Y', strtotime($sub['created_at'])) ?></div>
                    </div>
                    <span class="contribution-item-status <?= e($sub['status']) ?>"><?= ucfirst(e($sub['status'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Logout -->
        <div style="padding-top:0.5rem;">
            <form action="<?= url('logout') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="logout-btn">Sign Out</button>
            </form>
        </div>
    </div>
</div>
