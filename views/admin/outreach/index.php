<div class="admin-wrapper">
<div class="admin-content">

    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#127757; Outreach Dashboard</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Manage influential Tiv people and email campaigns</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= url('admin/outreach/people/create') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ Add Person</a>
            <a href="<?= url('nominate-influential') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;" target="_blank">&#128279; Public Nominate Page</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="admin-stats" style="margin-bottom:1.5rem;">
        <div class="admin-stat highlight">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($peopleStats['total']) ?></div>
                <div class="admin-stat-label">Total People</div>
            </div>
        </div>
        <div class="admin-stat <?= ($nominationStats['pending'] > 0) ? 'highlight' : '' ?>">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($nominationStats['pending']) ?></div>
                <div class="admin-stat-label">Pending Nominations</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($peopleStats['emailsSent']) ?></div>
                <div class="admin-stat-label">Emails Sent</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($peopleStats['active']) ?></div>
                <div class="admin-stat-label">Active Contacts</div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="admin-grid">

        <!-- Category Breakdown -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">People by Category</h2>
                <a href="<?= url('admin/outreach/people') ?>" class="admin-card-link">View All</a>
            </div>
            <div class="admin-card-body">
                <?php if (empty($peopleStats['byCategory'])): ?>
                    <div class="admin-empty-state">
                        <p>No people added yet.</p>
                        <a href="<?= url('admin/outreach/people/create') ?>" class="btn btn-primary btn-sm" style="margin-top:.5rem;">Add First Person</a>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:.5rem;">
                        <?php
                        $categoryLabels = \InfluentialPerson::CATEGORIES;
                        $maxCnt = max(array_column($peopleStats['byCategory'], 'cnt'));
                        foreach ($peopleStats['byCategory'] as $row):
                            $pct = $maxCnt > 0 ? round($row['cnt'] / $maxCnt * 100) : 0;
                        ?>
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:.2rem;">
                                <span style="color:#2d1b0e;"><?= e($categoryLabels[$row['category']] ?? ucfirst($row['category'])) ?></span>
                                <span style="color:#5C3A21;font-weight:600;"><?= number_format($row['cnt']) ?></span>
                            </div>
                            <div style="background:#f0ede8;border-radius:4px;height:6px;">
                                <div style="background:#C8A951;width:<?= $pct ?>%;height:6px;border-radius:4px;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Pending Nominations -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Pending Nominations</h2>
                <a href="<?= url('admin/outreach/nominations') ?>" class="admin-card-link">View All (<?= $nominationStats['total'] ?>)</a>
            </div>
            <div class="admin-card-body">
                <?php if (empty($recentNominations)): ?>
                    <div class="admin-empty-state">
                        <p>No pending nominations.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:.7rem;">
                        <?php foreach ($recentNominations as $nom): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #f0ede8;padding-bottom:.6rem;">
                            <div>
                                <div style="font-weight:500;color:#2d1b0e;font-size:.9rem;"><?= e($nom['nominee_name']) ?></div>
                                <div style="font-size:.78rem;color:#7a6a5a;"><?= e($nom['nominee_category'] ?? '—') ?> &middot; <?= date('M j', strtotime($nom['created_at'])) ?></div>
                            </div>
                            <a href="<?= url('admin/outreach/nominations/' . $nom['id']) ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Review</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Outreach Actions</h2>
            </div>
            <div class="admin-card-body">
                <div class="admin-actions">
                    <a href="<?= url('admin/outreach/people') ?>" class="admin-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Manage People
                    </a>
                    <a href="<?= url('admin/outreach/nominations') ?>" class="admin-action-btn <?= $nominationStats['pending'] > 0 ? 'admin-action-primary' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Nominations<?= $nominationStats['pending'] > 0 ? ' (' . $nominationStats['pending'] . ')' : '' ?>
                    </a>
                    <a href="<?= url('admin/outreach/templates') ?>" class="admin-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        Email Templates
                    </a>
                    <a href="<?= url('admin/outreach/campaigns') ?>" class="admin-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        Campaigns (<?= number_format($campaignsTotal) ?>)
                    </a>
                    <a href="<?= url('admin/outreach/discovery') ?>" class="admin-action-btn admin-action-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Discovery Assistant
                    </a>
                </div>
            </div>
        </section>

        <!-- Recently Added People -->
        <section class="admin-card admin-card-wide">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Recently Added</h2>
                <a href="<?= url('admin/outreach/people') ?>" class="admin-card-link">View All</a>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($recentPeople)): ?>
                    <div class="admin-empty-state" style="padding:2rem;">
                        <p>No people in the database yet. <a href="<?= url('admin/outreach/people/create') ?>">Add the first one.</a></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Name</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Contact Status</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $cats = \InfluentialPerson::CATEGORIES;
                                foreach ($recentPeople as $p):
                                ?>
                                <tr style="border-bottom:1px solid #f0ede8;">
                                    <td style="padding:.65rem 1rem;">
                                        <div style="font-weight:500;color:#2d1b0e;"><?= e($p['name']) ?></div>
                                        <div style="font-size:.78rem;color:#7a6a5a;"><?= e($p['organization'] ?? '—') ?></div>
                                    </td>
                                    <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e($cats[$p['category']] ?? $p['category']) ?></td>
                                    <td style="padding:.65rem 1rem;">
                                        <?php
                                        $csBadge = match($p['contact_status']) {
                                            'responded'     => 'background:#d1fae5;color:#065f46;',
                                            'partner'       => 'background:#dbeafe;color:#1e40af;',
                                            'contacted'     => 'background:#fef3c7;color:#92400e;',
                                            'unresponsive'  => 'background:#fee2e2;color:#991b1b;',
                                            default         => 'background:#f3f4f6;color:#374151;',
                                        };
                                        ?>
                                        <span style="<?= $csBadge ?>padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;">
                                            <?= e(\InfluentialPerson::CONTACT_STATUSES[$p['contact_status']] ?? $p['contact_status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding:.65rem 1rem;">
                                        <a href="<?= url('admin/outreach/people/' . $p['id'] . '/edit') ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>
</div>
