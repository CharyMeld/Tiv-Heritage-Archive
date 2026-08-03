<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">All Generated Content</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;"><?= number_format($pagination['total']) ?> post<?= $pagination['total'] === 1 ? '' : 's' ?> generated so far.</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= url('admin/marketing') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
            <a href="<?= url('admin/marketing/generator') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ Generate Content</a>
        </div>
    </div>

    <form method="GET" action="<?= url('admin/marketing/posts') ?>" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem;">
        <div class="form-group" style="margin:0;">
            <label class="form-label" style="font-size:.8rem;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label" style="font-size:.8rem;">Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filters['category'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($filters['status'] !== '' || $filters['category'] !== ''): ?>
            <a href="<?= url('admin/marketing/posts') ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.4rem .8rem;">Clear Filters</a>
        <?php endif; ?>
    </form>

    <section class="admin-card">
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($posts)): ?>
                <div class="admin-empty-state" style="padding:2rem;">
                    <p>No content matches these filters.</p>
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
                            <?php foreach ($posts as $p): ?>
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

                <?php if ($pagination['total_pages'] > 1): ?>
                    <div style="display:flex;justify-content:center;gap:.5rem;padding:1rem;">
                        <?php if ($pagination['has_prev']): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">&laquo; Prev</a>
                        <?php endif; ?>
                        <span style="padding:.35rem .75rem;font-size:.82rem;color:#5a4a3a;">
                            Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                        </span>
                        <?php if ($pagination['has_next']): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

</div>
</div>
</content>
