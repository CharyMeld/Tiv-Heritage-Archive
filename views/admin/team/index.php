<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#128101; Manage Team</h1>
        <p class="admin-page-sub"><?= count($members) ?> member<?= count($members) !== 1 ? 's' : '' ?> total</p>
    </div>
    <a href="<?= url('admin/team/create') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Add Member
    </a>
</div>

<?php if (empty($members)): ?>
<div class="admin-empty-state" style="padding:4rem 0;">
    <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    <p>No team members yet. <a href="<?= url('admin/team/create') ?>">Add the first one.</a></p>
</div>
<?php else: ?>
<div class="admin-card" style="overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width:60px;">Photo</th>
                <th>Name &amp; Role</th>
                <th>Category</th>
                <th>Socials</th>
                <th>Contribution</th>
                <th>Order</th>
                <th>Status</th>
                <th style="width:130px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($members as $m): ?>
        <?php
            $socials = TeamMember::decodeSocials($m['socials']);
            $imgUrl  = !empty($m['image'])
                ? UPLOADS_URL . '/team/' . e($m['image'])
                : 'https://ui-avatars.com/api/?name=' . urlencode($m['name']) . '&background=5C3A21&color=fff&size=80';
        ?>
        <tr>
            <td>
                <img src="<?= $imgUrl ?>" alt="<?= e($m['name']) ?>"
                     style="width:46px;height:46px;border-radius:50%;object-fit:cover;object-position:top;">
            </td>
            <td>
                <strong><?= e($m['name']) ?></strong>
                <br><small style="color:var(--color-text-muted);"><?= e($m['role']) ?></small>
            </td>
            <td>
                <span class="badge badge-<?= e($m['category']) ?>"><?= ucfirst(e($m['category'])) ?></span>
            </td>
            <td>
                <?php if (!empty($socials)): ?>
                <span style="font-size:0.8rem;color:var(--color-text-muted);"><?= implode(', ', array_map('ucfirst', array_keys($socials))) ?></span>
                <?php else: ?>
                <span style="color:var(--color-text-muted);font-size:0.8rem;">—</span>
                <?php endif; ?>
            </td>
            <td style="min-width:120px;">
                <?php $pct = (int)($m['contrib_pct'] ?? 0); ?>
                <?php if ($pct > 0): ?>
                <div class="contrib-admin-bar">
                    <div class="contrib-admin-track">
                        <div class="contrib-admin-fill" style="width:<?= $pct ?>%;"></div>
                    </div>
                    <span style="font-size:0.8rem;font-weight:700;color:var(--color-primary);white-space:nowrap;"><?= $pct ?>%</span>
                </div>
                <small style="color:var(--color-text-muted);font-size:0.72rem;"><?= number_format((int)($m['contrib_score'] ?? 0)) ?> items</small>
                <?php else: ?>
                <span style="color:var(--color-text-muted);font-size:0.8rem;">—</span>
                <?php endif; ?>
            </td>
            <td><?= (int)$m['sort_order'] ?></td>
            <td>
                <?php if ($m['is_active']): ?>
                <span style="color:#2E7D32;font-weight:600;font-size:0.8rem;">&#10003; Active</span>
                <?php else: ?>
                <span style="color:#C62828;font-size:0.8rem;">Hidden</span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display:flex;gap:0.4rem;">
                    <a href="<?= url('admin/team/' . $m['id'] . '/edit') ?>"
                       class="btn btn-secondary" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Edit</a>
                    <form action="<?= url('admin/team/' . $m['id'] . '/delete') ?>" method="POST" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger"
                                style="padding:0.35rem 0.75rem;font-size:0.8rem;"
                                data-confirm="Delete <?= e($m['name']) ?>? This cannot be undone.">
                            Delete
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
