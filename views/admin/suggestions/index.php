<div class="admin-wrapper">
    <div class="admin-content">

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Suggestions &amp; Feedback</h1>
            <a href="<?= url('admin/suggestions/export') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .9rem;">
                &#11015; Export CSV
            </a>
        </div>

        <!-- Stats -->
        <div class="admin-stats" style="margin-bottom:1.5rem;">
            <div class="admin-stat highlight">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['new_count'] ?? 0) ?></div>
                    <div class="admin-stat-label">New</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                    <div class="admin-stat-label">Total</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['under_review'] ?? 0) ?></div>
                    <div class="admin-stat-label">Under Review</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['implemented'] ?? 0) ?></div>
                    <div class="admin-stat-label">Implemented</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" style="display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:1.2rem;">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search..."
                   class="form-input" style="flex:1;min-width:180px;max-width:280px;padding:.45rem .7rem;font-size:.9rem;">
            <select name="status" class="form-select" style="padding:.45rem .7rem;font-size:.9rem;">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $filter_status === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category" class="form-select" style="padding:.45rem .7rem;font-size:.9rem;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $filter_category === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary" style="padding:.45rem 1rem;font-size:.9rem;">Filter</button>
            <?php if ($q || $filter_status || $filter_category): ?>
                <a href="<?= url('admin/suggestions') ?>" class="btn btn-secondary" style="padding:.45rem .9rem;font-size:.9rem;">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($suggestions)): ?>
                    <div class="admin-empty-state" style="padding:3rem;">
                        <p>No suggestions found.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Sender</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Subject</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suggestions as $s):
                                    $statusStyles = [
                                        'new'          => 'background:#fef3c7;color:#92400e;',
                                        'read'         => 'background:#e0f2fe;color:#0369a1;',
                                        'under_review' => 'background:#ede9fe;color:#5b21b6;',
                                        'in_progress'  => 'background:#d1fae5;color:#065f46;',
                                        'implemented'  => 'background:#d1fae5;color:#065f46;',
                                        'closed'       => 'background:#f1f5f9;color:#475569;',
                                    ];
                                    $sStyle = $statusStyles[$s['status']] ?? 'background:#f1f5f9;color:#475569;';
                                ?>
                                    <tr style="border-bottom:1px solid #f0ede8;<?= $s['status']==='new'?'background:#fffbf0;':'' ?>">
                                        <td style="padding:.7rem 1rem;">
                                            <div style="font-weight:500;color:#2d1b0e;"><?= htmlspecialchars($s['full_name']) ?></div>
                                            <div style="font-size:.78rem;color:#7a6a5a;"><?= htmlspecialchars($s['email']) ?></div>
                                        </td>
                                        <td style="padding:.7rem 1rem;color:#2d1b0e;max-width:200px;">
                                            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($s['subject']) ?></div>
                                        </td>
                                        <td style="padding:.7rem 1rem;font-size:.8rem;color:#5a4a3a;">
                                            <?= htmlspecialchars($categories[$s['category']] ?? $s['category']) ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;">
                                            <span style="<?= $sStyle ?>padding:.2rem .6rem;border-radius:12px;font-size:.75rem;font-weight:600;">
                                                <?= $statuses[$s['status']] ?? ucfirst($s['status']) ?>
                                            </span>
                                        </td>
                                        <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.82rem;white-space:nowrap;">
                                            <?= date('M j, Y', strtotime($s['created_at'])) ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;">
                                            <a href="<?= url('admin/suggestions/' . $s['id']) ?>"
                                               class="btn btn-secondary" style="font-size:.8rem;padding:.3rem .6rem;">
                                                <?= $s['status']==='new' ? '&#128270; Open' : 'View' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

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
