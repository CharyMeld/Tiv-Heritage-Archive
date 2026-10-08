<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#129302; Marketing Dashboard</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">AI-generated content, powered entirely by a local model — no external AI API</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= url('admin/marketing/generator') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ Generate Content</a>
        </div>
    </div>

    <?php if (!$ollamaAvailable): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1.5rem;color:#991b1b;font-size:.88rem;">
            &#9888; The local AI model (Ollama) is not responding right now. Content generation will fail until it's back up.
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="admin-stats" style="margin-bottom:1.5rem;">
        <div class="admin-stat highlight">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($todaysPosts) ?></div>
                <div class="admin-stat-label">Today's Posts</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($statusCounts['pending_review'] + $statusCounts['approved'] + $statusCounts['scheduled']) ?></div>
                <div class="admin-stat-label">Awaiting / Scheduled</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($statusCounts['published']) ?></div>
                <div class="admin-stat-label">Posts Published</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= $generationRate['rate'] !== null ? $generationRate['rate'] . '%' : '—' ?></div>
                <div class="admin-stat-label">AI Success Rate (7d)</div>
            </div>
        </div>
        <div class="admin-stat">
            <div class="admin-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </div>
            <div class="admin-stat-info">
                <div class="admin-stat-value"><?= number_format($totalClicks) ?></div>
                <div class="admin-stat-label">Website Clicks</div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="admin-grid">

        <!-- Post status breakdown -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Content Pipeline</h2>
                <a href="<?= url('admin/marketing/posts') ?>" class="admin-card-link">View All</a>
            </div>
            <div class="admin-card-body">
                <?php
                $maxCnt = max(array_values($statusCounts)) ?: 1;
                foreach (MarketingPost::STATUSES as $key => $label):
                    $cnt = $statusCounts[$key] ?? 0;
                    $pct = round($cnt / $maxCnt * 100);
                ?>
                <div style="margin-bottom:.6rem;">
                    <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:.2rem;">
                        <span style="color:#2d1b0e;"><?= e($label) ?></span>
                        <span style="color:#5C3A21;font-weight:600;"><?= number_format($cnt) ?></span>
                    </div>
                    <div style="background:#f0ede8;border-radius:4px;height:6px;">
                        <div style="background:#C8A951;width:<?= $pct ?>%;height:6px;border-radius:4px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Traffic by platform -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Traffic by Platform</h2>
                <a href="<?= url('admin/marketing/traffic') ?>" class="admin-card-link">View All</a>
            </div>
            <div class="admin-card-body">
                <?php if (empty($trafficByPlatform)): ?>
                    <div class="admin-empty-state">
                        <p>No tracked links yet. Once posts are generated and shared with tracked links, click data will show up here.</p>
                    </div>
                <?php else: ?>
                    <?php $maxClicks = max(array_column($trafficByPlatform, 'total_clicks')) ?: 1; ?>
                    <?php foreach ($trafficByPlatform as $row): ?>
                    <div style="margin-bottom:.6rem;">
                        <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:.2rem;">
                            <span style="color:#2d1b0e;"><?= e(MarketingUtmLink::channelLabel($row['platform'])) ?></span>
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

        <!-- Quick Actions -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Quick Actions</h2>
            </div>
            <div class="admin-card-body">
                <div class="admin-actions">
                    <a href="<?= url('admin/marketing/generator') ?>" class="admin-action-btn admin-action-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Content Generator
                    </a>
                    <a href="<?= url('admin/marketing/templates') ?>" class="admin-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        Prompt Templates
                    </a>
                </div>
            </div>
        </section>

        <!-- Recently generated -->
        <section class="admin-card admin-card-wide">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Recently Generated</h2>
                <a href="<?= url('admin/marketing/posts') ?>" class="admin-card-link">View All</a>
            </div>
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($recentPosts)): ?>
                    <div class="admin-empty-state" style="padding:2rem;">
                        <p>No content generated yet. <a href="<?= url('admin/marketing/generator') ?>">Generate the first post.</a></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Headline</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPosts as $p): ?>
                                <tr style="border-bottom:1px solid #f0ede8;">
                                    <td style="padding:.65rem 1rem;">
                                        <a href="<?= url('admin/marketing/posts/' . $p['id']) ?>" style="color:#2d1b0e;font-weight:500;"><?= e($p['headline'] ?? '(untitled)') ?></a>
                                    </td>
                                    <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e(MarketingPost::SOURCE_TYPES[$p['source_type']] ?? $p['source_type']) ?></td>
                                    <td style="padding:.65rem 1rem;">
                                        <?php
                                        $bg = match($p['status']) {
                                            'published' => 'background:#d1fae5;color:#065f46;',
                                            'approved', 'scheduled' => 'background:#dbeafe;color:#1e40af;',
                                            'rejected' => 'background:#fee2e2;color:#991b1b;',
                                            default => 'background:#f3f4f6;color:#374151;',
                                        };
                                        ?>
                                        <span style="<?= $bg ?>padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;"><?= e(MarketingPost::STATUSES[$p['status']] ?? $p['status']) ?></span>
                                    </td>
                                    <td style="padding:.65rem 1rem;color:#7a6a5a;font-size:.82rem;"><?= date('M j, Y g:ia', strtotime($p['created_at'])) ?></td>
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
