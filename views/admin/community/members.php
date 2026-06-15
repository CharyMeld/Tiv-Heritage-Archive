<div class="admin-wrapper">
    <div class="admin-content">

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Community Members Directory</h1>
            <div style="display:flex;gap:.6rem;">
                <a href="<?= url('admin/community/members/export') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#11015; Export CSV
                </a>
                <a href="<?= url('admin/community/applications') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#128196; Applications
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
                    <div class="admin-stat-value"><?= number_format($stats['active'] ?? 0) ?></div>
                    <div class="admin-stat-label">Active</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['contributors'] ?? 0) ?></div>
                    <div class="admin-stat-label">Contributors</div>
                </div>
            </div>
            <div class="admin-stat">
                <div class="admin-stat-info">
                    <div class="admin-stat-value"><?= number_format($stats['researchers'] ?? 0) ?></div>
                    <div class="admin-stat-label">Researchers</div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" style="display:flex;gap:.7rem;margin-bottom:1.2rem;">
            <select name="type" class="form-select" style="padding:.45rem .7rem;font-size:.9rem;">
                <option value="">All Types</option>
                <option value="contributor" <?= $filter_type === 'contributor' ? 'selected' : '' ?>>Contributors</option>
                <option value="researcher"  <?= $filter_type === 'researcher'  ? 'selected' : '' ?>>Researchers</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding:.45rem 1rem;font-size:.9rem;">Filter</button>
        </form>

        <!-- Members Grid -->
        <?php if (empty($members)): ?>
            <div class="admin-card">
                <div class="admin-empty-state" style="padding:3rem;">
                    <p>No members yet. Approve applications to add members to the directory.</p>
                    <a href="<?= url('admin/community/applications') ?>" class="btn btn-primary" style="margin-top:1rem;">View Applications</a>
                </div>
            </div>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
                <?php foreach ($members as $m): ?>
                    <div class="admin-card" style="margin:0;<?= !$m['is_active'] ? 'opacity:.6;' : '' ?>">
                        <div class="admin-card-body" style="padding:1rem;">
                            <div style="display:flex;gap:.8rem;align-items:flex-start;">
                                <!-- Photo -->
                                <div style="flex-shrink:0;width:52px;height:52px;border-radius:50%;overflow:hidden;background:#f7f4ee;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#5C3A21;">
                                    <?php if (!empty($m['profile_photo'])): ?>
                                        <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($m['profile_photo']) ?>" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <?= strtoupper(substr($m['full_name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:600;color:#2d1b0e;font-size:.95rem;display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                                        <?= htmlspecialchars($m['full_name']) ?>
                                        <?php if ($m['is_featured']): ?>
                                            <span title="Featured" style="color:#C8A951;">&#11088;</span>
                                        <?php endif; ?>
                                    </div>
                                    <span style="background:<?= $m['member_type']==='researcher'?'#4a7c59':'#5C3A21' ?>;color:#fff;padding:.1rem .5rem;border-radius:10px;font-size:.7rem;font-weight:600;">
                                        <?= ucfirst($m['member_type']) ?>
                                    </span>
                                    <?php if (!$m['is_active']): ?>
                                        <span style="background:#fee2e2;color:#991b1b;padding:.1rem .5rem;border-radius:10px;font-size:.7rem;margin-left:.3rem;">Inactive</span>
                                    <?php endif; ?>
                                    <?php if (!empty($m['area_of_interest'])): ?>
                                        <div style="font-size:.78rem;color:#7a6a5a;margin-top:.3rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($m['area_of_interest']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div style="display:flex;gap:.4rem;margin-top:.8rem;flex-wrap:wrap;">
                                <a href="<?= url('admin/community/members/' . $m['id'] . '/edit') ?>"
                                   class="btn btn-secondary" style="font-size:.75rem;padding:.25rem .6rem;">Edit</a>

                                <form method="POST" action="<?= url('admin/community/members/' . $m['id'] . '/feature') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-secondary" style="font-size:.75rem;padding:.25rem .6rem;" title="<?= $m['is_featured'] ? 'Unfeature' : 'Feature' ?>">
                                        <?= $m['is_featured'] ? '&#11089; Unfeature' : '&#11088; Feature' ?>
                                    </button>
                                </form>

                                <form method="POST" action="<?= url('admin/community/members/' . $m['id'] . '/toggle') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-secondary" style="font-size:.75rem;padding:.25rem .6rem;">
                                        <?= $m['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <form method="POST" action="<?= url('admin/community/members/' . $m['id'] . '/remove') ?>"
                                      style="display:inline;" data-confirm="Remove this member from the directory?">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-secondary" style="font-size:.75rem;padding:.25rem .6rem;background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display:flex;justify-content:center;gap:.5rem;margin-top:1.5rem;">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$pagination['current_page']-1])) ?>" class="btn btn-secondary">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:.4rem .75rem;font-size:.85rem;color:#5a4a3a;">
                        Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                    </span>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$pagination['current_page']+1])) ?>" class="btn btn-secondary">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
