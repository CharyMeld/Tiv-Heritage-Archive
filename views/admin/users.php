<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128101; Manage Users</h1>
        <p class="admin-page-sub"><?= number_format($pagination['total']) ?> registered users</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:2rem; align-items:start;">

    <!-- Users Table -->
    <div>
        <div class="admin-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $userItem): ?>
                    <tr>
                        <td>
                            <strong><?= e($userItem['name']) ?></strong>
                            <?php if ($userItem['id'] === $user['id']): ?>
                            <span style="font-size:0.7rem; background:rgba(200,169,81,0.15); color:var(--color-accent); padding:0.1rem 0.4rem; border-radius:50px; margin-left:0.3rem;">You</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.85rem; color:var(--color-text-muted);"><?= e($userItem['email']) ?></td>
                        <td>
                            <?php
                            $roleColors = [
                                'admin'       => '#c62828',
                                'moderator'   => '#1565c0',
                                'contributor' => '#2e7d32',
                                'user'        => 'var(--color-text-muted)',
                            ];
                            $roleColor = $roleColors[$userItem['role']] ?? 'var(--color-text-muted)';
                            ?>
                            <span style="font-size:0.75rem; font-weight:700; color:<?= $roleColor ?>; background:rgba(0,0,0,0.06); padding:0.2rem 0.6rem; border-radius:50px; text-transform:uppercase; letter-spacing:0.05em;">
                                <?= e(USER_ROLES[$userItem['role']]['label'] ?? $userItem['role']) ?>
                            </span>
                        </td>
                        <td style="font-size:0.8rem; color:var(--color-text-muted);"><?= date('M j, Y', strtotime($userItem['created_at'])) ?></td>
                        <td>
                            <?php if ($userItem['id'] !== $user['id']): ?>
                            <div style="display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap;">
                                <!-- Change Role -->
                                <form action="<?= url('admin/users/' . $userItem['id'] . '/role') ?>" method="POST" style="display:flex; gap:0.4rem; align-items:center;">
                                    <?= csrf_field() ?>
                                    <select name="role" class="form-select" style="padding:0.3rem 0.5rem; font-size:0.8rem; width:auto;">
                                        <?php foreach ($roles as $roleKey => $roleData): ?>
                                        <option value="<?= $roleKey ?>" <?= $userItem['role'] === $roleKey ? 'selected' : '' ?>>
                                            <?= e($roleData['label']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-secondary" style="white-space:nowrap;">Save</button>
                                </form>
                                <!-- Reset Password -->
                                <button type="button" class="btn btn-sm btn-secondary"
                                        onclick="openResetModal(<?= $userItem['id'] ?>, '<?= e(addslashes($userItem['name'])) ?>')"
                                        style="white-space:nowrap;">&#128274; Reset PW</button>
                                <!-- Delete -->
                                <form action="<?= url('admin/users/' . $userItem['id'] . '/delete') ?>" method="POST"
                                      onsubmit="return confirm('Delete user <?= e(addslashes($userItem['name'])) ?>? This cannot be undone.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm" style="background:#c62828; color:#fff; border:none;">Delete</button>
                                </form>
                            </div>
                            <?php else: ?>
                            <span style="font-size:0.8rem; color:var(--color-text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= pagination($pagination, url('admin/users')) ?>
    </div>

    <!-- Add User Panel -->
    <div style="position:sticky; top:1.5rem;">
        <div class="contribute-form-card" style="padding:1.5rem;">
            <h2 style="font-family:var(--font-heading); font-size:1.1rem; color:var(--color-primary); margin:0 0 0.25rem;">+ Add Admin / Staff User</h2>
            <p style="font-size:0.8rem; color:var(--color-text-muted); margin:0 0 1.25rem;">Create a new user account and assign their role immediately.</p>

            <form action="<?= url('admin/users/create') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label required">Full Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Aondona Iember" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="e.g. admin@example.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Min. 8 characters" required minlength="8">
                </div>

                <div class="form-group">
                    <label class="form-label required">Role</label>
                    <select name="role" class="form-select" required>
                        <?php foreach ($roles as $roleKey => $roleData): ?>
                        <?php if ($roleKey === 'user') continue; ?>
                        <option value="<?= $roleKey ?>" <?= $roleKey === 'admin' ? 'selected' : '' ?>>
                            <?= e($roleData['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint">
                        <strong>Admin</strong> — full access &nbsp;|&nbsp;
                        <strong>Moderator</strong> — manage content &nbsp;|&nbsp;
                        <strong>Contributor</strong> — submit content
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Create User</button>
            </form>
        </div>
    </div>

</div>

<!-- Reset Password Modal -->
<div id="reset-pw-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
    <div style="background:var(--color-surface);border-radius:12px;padding:2rem;width:100%;max-width:420px;margin:1rem;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="margin:0 0 0.25rem;font-family:var(--font-heading);color:var(--color-primary);">&#128274; Reset Password</h3>
        <p id="reset-pw-label" style="margin:0 0 1.25rem;font-size:0.85rem;color:var(--color-text-muted);"></p>

        <form id="reset-pw-form" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label required">New Password</label>
                <input type="password" name="new_password" class="form-input" placeholder="At least 8 characters" required minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label required">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Repeat new password" required minlength="8" autocomplete="new-password">
            </div>
            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Set Password</button>
                <button type="button" class="btn btn-secondary" onclick="closeResetModal()" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(userId, userName) {
    document.getElementById('reset-pw-form').action = '<?= url('admin/users/') ?>' + userId + '/password';
    document.getElementById('reset-pw-label').textContent = 'Setting a new password for: ' + userName;
    document.getElementById('reset-pw-form').querySelectorAll('input[type=password]').forEach(i => i.value = '');
    const modal = document.getElementById('reset-pw-modal');
    modal.style.display = 'flex';
}
function closeResetModal() {
    document.getElementById('reset-pw-modal').style.display = 'none';
}
document.getElementById('reset-pw-modal').addEventListener('click', function(e) {
    if (e.target === this) closeResetModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeResetModal();
});
</script>
