<div class="admin-wrapper">
    <div class="admin-content">

        <!-- Page Header -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Community Applications</h1>
            <div style="display:flex;gap:.6rem;">
                <a href="<?= url('admin/community/applications/export') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#11015; Export CSV
                </a>
                <a href="<?= url('admin/community/members') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#128101; Members Directory
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="admin-stats" style="margin-bottom:1.5rem;">
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                    <div class="admin-stat-label">Total</div>
                </div>
            </div>
            <div class="admin-stat highlight">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['pending'] ?? 0) ?></div>
                    <div class="admin-stat-label">Pending</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['approved'] ?? 0) ?></div>
                    <div class="admin-stat-label">Approved</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['rejected'] ?? 0) ?></div>
                    <div class="admin-stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" style="display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:1.2rem;">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search name, email..."
                   class="form-input" style="flex:1;min-width:180px;max-width:300px;padding:.45rem .7rem;font-size:.9rem;">
            <select name="status" class="form-select" style="padding:.45rem .7rem;font-size:.9rem;">
                <option value="">All Statuses</option>
                <?php foreach (['pending','approved','rejected'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" class="form-select" style="padding:.45rem .7rem;font-size:.9rem;">
                <option value="">All Types</option>
                <option value="contributor" <?= $filter_type === 'contributor' ? 'selected' : '' ?>>Contributor</option>
                <option value="researcher"  <?= $filter_type === 'researcher'  ? 'selected' : '' ?>>Researcher</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding:.45rem 1rem;font-size:.9rem;">Filter</button>
            <?php if ($q || $filter_status || $filter_type): ?>
                <a href="<?= url('admin/community/applications') ?>" class="btn btn-secondary" style="padding:.45rem .9rem;font-size:.9rem;">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($applications)): ?>
                    <div class="admin-empty-state" style="padding:3rem;">
                        <p>No applications found.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Applicant</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Type</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Country</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Applied</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr style="border-bottom:1px solid #f0ede8;">
                                        <td style="padding:.7rem 1rem;">
                                            <div style="font-weight:500;color:#2d1b0e;"><?= htmlspecialchars($app['full_name']) ?></div>
                                            <div style="font-size:.8rem;color:#7a6a5a;"><?= htmlspecialchars($app['email']) ?></div>
                                        </td>
                                        <td style="padding:.7rem 1rem;">
                                            <span style="background:<?= $app['member_type']==='researcher'?'#4a7c59':'#5C3A21' ?>;color:#fff;padding:.2rem .6rem;border-radius:12px;font-size:.75rem;font-weight:600;">
                                                <?= ucfirst($app['member_type']) ?>
                                            </span>
                                        </td>
                                        <td style="padding:.7rem 1rem;color:#5a4a3a;"><?= htmlspecialchars($app['country'] ?? '-') ?></td>
                                        <td style="padding:.7rem 1rem;">
                                            <?php
                                            $badge = match($app['status']) {
                                                'approved' => 'background:#d1fae5;color:#065f46;',
                                                'rejected' => 'background:#fee2e2;color:#991b1b;',
                                                default    => 'background:#fef3c7;color:#92400e;',
                                            };
                                            ?>
                                            <span style="<?= $badge ?>padding:.2rem .6rem;border-radius:12px;font-size:.75rem;font-weight:600;">
                                                <?= ucfirst($app['status']) ?>
                                            </span>
                                        </td>
                                        <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.82rem;">
                                            <?= date('M j, Y', strtotime($app['created_at'])) ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;">
                                            <div style="display:flex;gap:.4rem;align-items:center;">
                                                <a href="<?= url('admin/community/applications/' . $app['id']) ?>"
                                                   style="font-size:.8rem;padding:.3rem .6rem;" class="btn btn-secondary">View</a>
                                                <?php if ($app['status'] === 'pending'): ?>
                                                    <form method="POST" action="<?= url('admin/community/applications/' . $app['id'] . '/approve') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="pending-action approve" title="Approve">&#10003;</button>
                                                    </form>
                                                    <form method="POST" action="<?= url('admin/community/applications/' . $app['id'] . '/reject') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="pending-action reject" title="Reject">&#10005;</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display:flex;justify-content:center;gap:.5rem;padding:1rem;">
                            <?php if ($pagination['has_prev']): ?>
                                <a href="?<?= http_build_query(array_merge($_GET,['page'=>$pagination['current_page']-1])) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">&laquo; Prev</a>
                            <?php endif; ?>
                            <span style="padding:.35rem .75rem;font-size:.82rem;color:#5a4a3a;">
                                Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                            </span>
                            <?php if ($pagination['has_next']): ?>
                                <a href="?<?= http_build_query(array_merge($_GET,['page'=>$pagination['current_page']+1])) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
