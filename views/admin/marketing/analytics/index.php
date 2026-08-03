<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128200; Analytics</h1>
        <a href="<?= url('admin/marketing/traffic') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">Traffic Reports &rarr;</a>
    </div>

    <div class="admin-stats" style="margin-bottom:1.5rem;">
        <div class="admin-stat highlight">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($totalClicks) ?></div>
                <div class="admin-stat-label">Total Tracked Clicks</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($totalLinks) ?></div>
                <div class="admin-stat-label">Tracked Links</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= $generationRate7['rate'] !== null ? $generationRate7['rate'] . '%' : '—' ?></div>
                <div class="admin-stat-label">AI Success Rate (7d)</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= $generationRate30['rate'] !== null ? $generationRate30['rate'] . '%' : '—' ?></div>
                <div class="admin-stat-label">AI Success Rate (30d)</div>
            </div>
        </div>
    </div>

    <div class="admin-grid">
        <section class="admin-card">
            <div class="admin-card-header"><h2 class="admin-card-title">Content by Status</h2></div>
            <div class="admin-card-body">
                <?php $maxCnt = max(array_values($statusCounts)) ?: 1; ?>
                <?php foreach (MarketingPost::STATUSES as $key => $label): ?>
                <div style="margin-bottom:.6rem;">
                    <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:.2rem;">
                        <span style="color:#2d1b0e;"><?= e($label) ?></span>
                        <span style="color:#5C3A21;font-weight:600;"><?= number_format($statusCounts[$key] ?? 0) ?></span>
                    </div>
                    <div style="background:#f0ede8;border-radius:4px;height:6px;">
                        <div style="background:#C8A951;width:<?= round(($statusCounts[$key] ?? 0) / $maxCnt * 100) ?>%;height:6px;border-radius:4px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Clicks by Platform</h2>
                <a href="<?= url('admin/marketing/links') ?>" class="admin-card-link">Manage Links</a>
            </div>
            <div class="admin-card-body">
                <?php if (empty($trafficByPlatform)): ?>
                    <div class="admin-empty-state"><p>No tracked links yet. <a href="<?= url('admin/marketing/links/create') ?>">Create one.</a></p></div>
                <?php else: ?>
                    <?php $maxClicks = max(array_column($trafficByPlatform, 'total_clicks')) ?: 1; ?>
                    <?php foreach ($trafficByPlatform as $row): ?>
                    <div style="margin-bottom:.6rem;">
                        <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:.2rem;">
                            <span style="color:#2d1b0e;"><?= e(MarketingUtmLink::PLATFORMS[$row['platform']] ?? $row['platform']) ?> <span style="color:#8a7a6a;">(<?= (int) $row['link_count'] ?> link<?= $row['link_count'] != 1 ? 's' : '' ?>)</span></span>
                            <span style="color:#5C3A21;font-weight:600;"><?= number_format($row['total_clicks']) ?></span>
                        </div>
                        <div style="background:#f0ede8;border-radius:4px;height:6px;">
                            <div style="background:#5C3A21;width:<?= round($row['total_clicks'] / $maxClicks * 100) ?>%;height:6px;border-radius:4px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="admin-card admin-card-wide">
            <div class="admin-card-header"><h2 class="admin-card-title">Recent AI Generation Failures</h2></div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($recentFailures)): ?>
                    <div class="admin-empty-state" style="padding:1.5rem;"><p>No recent failures.</p></div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Purpose</th>
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Error</th>
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentFailures as $f): ?>
                                <tr style="border-bottom:1px solid #f0ede8;">
                                    <td style="padding:.6rem 1rem;color:#2d1b0e;"><?= e(ucwords(str_replace('_', ' ', $f['purpose']))) ?></td>
                                    <td style="padding:.6rem 1rem;color:#991b1b;font-size:.8rem;"><?= e($f['error_message'] ?? '—') ?></td>
                                    <td style="padding:.6rem 1rem;color:#7a6a5a;font-size:.8rem;"><?= date('M j, g:ia', strtotime($f['created_at'])) ?></td>
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
