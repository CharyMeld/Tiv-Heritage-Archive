<div class="admin-wrapper">

    <div class="admin-content">
        <!-- Stats Grid -->
        <div class="admin-stats">
            <div class="admin-stat highlight">
                <div class="admin-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['pending']) ?></div>
                    <div class="admin-stat-label">Pending Review</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['users']) ?></div>
                    <div class="admin-stat-label">Users</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['names'] + $stats['proverbs'] + $stats['plants'] + $stats['festivals'] + $stats['foods'] + $stats['words'] + ($stats['animals'] ?? 0) + ($stats['videos'] ?? 0)) ?></div>
                    <div class="admin-stat-label">Total Content</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= date('M j') ?></div>
                    <div class="admin-stat-label">Today</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="admin-charts-grid">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Category Distribution</h2>
                </div>
                <div class="admin-card-body">
                    <canvas id="categoryChart" height="220"></canvas>
                </div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Submission Status</h2>
                </div>
                <div class="admin-card-body">
                    <canvas id="submissionChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Main Admin Grid -->
        <div class="admin-grid">
            <!-- Pending Submissions -->
            <section class="admin-card admin-card-wide">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                        </svg>
                        Pending Submissions
                    </h2>
                    <a href="<?= url('admin/pending') ?>" class="admin-card-link">View All (<?= $stats['pending'] ?>)</a>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($recentSubmissions)): ?>
                    <div class="pending-list">
                        <?php foreach (array_slice($recentSubmissions, 0, 5) as $submission): ?>
                        <div class="pending-item">
                            <div class="pending-item-content">
                                <h4 class="pending-item-title"><?= e($submission['tiv_term']) ?></h4>
                                <p class="pending-item-meta">
                                    <span class="pending-category"><?= ucfirst(e($submission['category'])) ?></span>
                                    <span class="pending-separator">•</span>
                                    <span><?= e($submission['user_name'] ?? 'Guest') ?></span>
                                </p>
                            </div>
                            <div class="pending-item-actions">
                                <form action="<?= url('admin/pending/' . $submission['id'] . '/approve') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="add_to_content" value="1">
                                    <button type="submit" class="pending-action approve" title="Approve">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </button>
                                </form>
                                <form action="<?= url('admin/pending/' . $submission['id'] . '/reject') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="pending-action reject" title="Reject">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="admin-empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <p>No pending submissions</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Manage Content -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"/>
                        </svg>
                        Manage Content
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="admin-categories">
                        <a href="<?= url('admin/content/names') ?>" class="admin-category">
                            <span class="admin-category-icon">&#128100;</span>
                            <span class="admin-category-name">Names</span>
                            <span class="admin-category-count"><?= number_format($stats['names']) ?></span>
                        </a>
                        <a href="<?= url('admin/content/proverbs') ?>" class="admin-category">
                            <span class="admin-category-icon">&#128221;</span>
                            <span class="admin-category-name">Proverbs</span>
                            <span class="admin-category-count"><?= number_format($stats['proverbs']) ?></span>
                        </a>
                        <a href="<?= url('admin/content/plants') ?>" class="admin-category">
                            <span class="admin-category-icon">&#127807;</span>
                            <span class="admin-category-name">Plants</span>
                            <span class="admin-category-count"><?= number_format($stats['plants']) ?></span>
                        </a>
                        <a href="<?= url('admin/content/festivals') ?>" class="admin-category">
                            <span class="admin-category-icon">&#127881;</span>
                            <span class="admin-category-name">Festivals</span>
                            <span class="admin-category-count"><?= number_format($stats['festivals']) ?></span>
                        </a>
                        <a href="<?= url('admin/content/foods') ?>" class="admin-category">
                            <span class="admin-category-icon">&#127858;</span>
                            <span class="admin-category-name">Foods</span>
                            <span class="admin-category-count"><?= number_format($stats['foods']) ?></span>
                        </a>
                        <a href="<?= url('admin/content/words') ?>" class="admin-category">
                            <span class="admin-category-icon">&#128172;</span>
                            <span class="admin-category-name">Words</span>
                            <span class="admin-category-count"><?= number_format($stats['words']) ?></span>
                        </a>
                        <a href="<?= url('admin/content/animals') ?>" class="admin-category">
                            <span class="admin-category-icon">&#128062;</span>
                            <span class="admin-category-name">Animals</span>
                            <span class="admin-category-count"><?= number_format($stats['animals'] ?? 0) ?></span>
                        </a>
                        <a href="<?= url('admin/content/videos') ?>" class="admin-category">
                            <span class="admin-category-icon">&#127909;</span>
                            <span class="admin-category-name">Videos</span>
                            <span class="admin-category-count"><?= number_format($stats['videos'] ?? 0) ?></span>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Bible Tiv Entry -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        Bible — Tiv Entry
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="admin-actions">
                        <a href="<?= url('admin/bible') ?>" class="admin-action-btn admin-action-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            Open Bible Editor
                        </a>
                        <a href="<?= url('admin/bible/JOH/1') ?>" class="admin-action-btn">
                            &#128214; Start with John 1
                        </a>
                    </div>
                </div>
            </section>

            <!-- Knowledge Graph -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M4.22 4.22l2.12 2.12m11.32 11.32 2.12 2.12M2 12h3m14 0h3"/></svg>
                        Knowledge Graph
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="admin-actions">
                        <a href="<?= url('admin/sources') ?>" class="admin-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            Manage Sources
                        </a>
                        <a href="<?= url('admin/links') ?>" class="admin-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            Graph Links
                        </a>
                        <a href="<?= url('admin/sources/create') ?>" class="admin-action-btn admin-action-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add Source
                        </a>
                    </div>
                </div>
            </section>

            <!-- Community & Suggestions Overview -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Community &amp; Feedback
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="admin-actions">
                        <a href="<?= url('admin/community/applications') ?>" class="admin-action-btn <?= ($stats['community_pending'] ?? 0) > 0 ? 'admin-action-primary' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1"/></svg>
                            Applications<?= ($stats['community_pending'] ?? 0) > 0 ? ' (' . $stats['community_pending'] . ' pending)' : '' ?>
                        </a>
                        <a href="<?= url('admin/community/members') ?>" class="admin-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            Members (<?= number_format($stats['community_members'] ?? 0) ?> active)
                        </a>
                        <a href="<?= url('admin/suggestions') ?>" class="admin-action-btn <?= ($stats['suggestions_new'] ?? 0) > 0 ? 'admin-action-primary' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Suggestions<?= ($stats['suggestions_new'] ?? 0) > 0 ? ' (' . $stats['suggestions_new'] . ' new)' : '' ?>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        Quick Actions
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="admin-actions">
                        <a href="<?= url('admin/team') ?>" class="admin-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Manage Team
                        </a>
                        <a href="<?= url('admin/users') ?>" class="admin-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Manage Users
                        </a>
                        <a href="<?= url('admin/settings') ?>" class="admin-action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Site Settings
                        </a>
                        <a href="<?= url('admin/duplicates') ?>" class="admin-action-btn" style="background:#fef3c7;color:#92400e;border-color:#fde68a;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Fix Duplicates
                        </a>
                        <a href="<?= url('contribute') ?>" class="admin-action-btn admin-action-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="M12 5v14"/>
                            </svg>
                            Add New Content
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    // Category Distribution Doughnut Chart
    var catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: ['Names', 'Proverbs', 'Plants', 'Festivals', 'Foods', 'Words', 'Animals', 'Videos'],
                datasets: [{
                    data: [
                        <?= (int)($stats['names'] ?? 0) ?>,
                        <?= (int)($stats['proverbs'] ?? 0) ?>,
                        <?= (int)($stats['plants'] ?? 0) ?>,
                        <?= (int)($stats['festivals'] ?? 0) ?>,
                        <?= (int)($stats['foods'] ?? 0) ?>,
                        <?= (int)($stats['words'] ?? 0) ?>,
                        <?= (int)($stats['animals'] ?? 0) ?>,
                        <?= (int)($stats['videos'] ?? 0) ?>
                    ],
                    backgroundColor: ['#5C3A21','#C8A951','#8B6F47','#D4A843','#A0522D','#CD853F','#7B9E3E','#DEB887'],
                    borderWidth: 2,
                    borderColor: '#F7F4EE'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter, sans-serif', size: 12 } } }
                }
            }
        });
    }

    // Submission Status Bar Chart
    var subCtx = document.getElementById('submissionChart');
    if (subCtx) {
        new Chart(subCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    label: 'Submissions',
                    data: [
                        <?= (int)($stats['pending'] ?? 0) ?>,
                        <?= (int)($stats['approved'] ?? 0) ?>,
                        <?= (int)($stats['rejected'] ?? 0) ?>
                    ],
                    backgroundColor: ['#ED6C02', '#2E7D32', '#C62828'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
})();
</script>
