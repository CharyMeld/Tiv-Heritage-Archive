<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128279; Traffic Reports</h1>
        <div style="display:flex;gap:.5rem;">
            <a href="<?= url('admin/marketing/links') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">Manage Links</a>
            <a href="<?= url('admin/marketing/analytics') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Analytics</a>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Clicks — Last 30 Days (<?= number_format($totalClicks) ?> all-time)</h2></div>
        <div class="admin-card-body">
            <?php if (empty($dailyCounts)): ?>
                <div class="admin-empty-state"><p>No click data yet. Clicks are tracked whenever someone follows a generated <code>/go/{code}</code> link.</p></div>
            <?php else: ?>
                <div style="display:flex;align-items:flex-end;gap:3px;height:140px;">
                    <?php foreach ($dailyCounts as $d): ?>
                        <?php $h = max(2, round($d['clicks'] / $maxDaily * 130)); ?>
                        <div style="flex:1;background:#C8A951;height:<?= $h ?>px;border-radius:2px 2px 0 0;" title="<?= e($d['day']) ?>: <?= (int) $d['clicks'] ?> clicks"></div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#8a7a6a;margin-top:.4rem;">
                    <span><?= e($dailyCounts[0]['day']) ?></span>
                    <span><?= e($dailyCounts[count($dailyCounts) - 1]['day']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2 class="admin-card-title">By Platform</h2></div>
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($trafficByPlatform)): ?>
                <div class="admin-empty-state" style="padding:1.5rem;"><p>No tracked links yet.</p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Platform</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Links</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Total Clicks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trafficByPlatform as $row): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.65rem 1rem;font-weight:500;color:#2d1b0e;"><?= e(MarketingUtmLink::PLATFORMS[$row['platform']] ?? $row['platform']) ?></td>
                                <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= (int) $row['link_count'] ?></td>
                                <td style="padding:.65rem 1rem;color:#5C3A21;font-weight:600;"><?= number_format($row['total_clicks']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
