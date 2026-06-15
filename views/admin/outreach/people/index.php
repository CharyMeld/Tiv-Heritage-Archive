<div class="admin-wrapper">
<div class="admin-content">

    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.2rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#127757; Influential People</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;"><?= number_format($total) ?> total record<?= $total !== 1 ? 's' : '' ?></p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= url('admin/outreach/people/create') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">+ Add Person</a>
            <a href="<?= url('admin/outreach') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Outreach</a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.2rem;">
        <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Search name, org, email…"
               class="form-input" style="flex:1;min-width:200px;max-width:280px;padding:.45rem .7rem;font-size:.88rem;">
        <select name="category" class="form-select" style="padding:.45rem .7rem;font-size:.88rem;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['category'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="contact_status" class="form-select" style="padding:.45rem .7rem;font-size:.88rem;">
            <option value="">All Contact Statuses</option>
            <?php foreach ($contactStatuses as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['contact_status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="consent_status" class="form-select" style="padding:.45rem .7rem;font-size:.88rem;">
            <option value="">All Consent Statuses</option>
            <?php foreach ($consentStatuses as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['consent_status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:.45rem 1rem;font-size:.88rem;">Filter</button>
        <?php if (array_filter($filters)): ?>
            <a href="<?= url('admin/outreach/people') ?>" class="btn btn-secondary" style="padding:.45rem .9rem;font-size:.88rem;">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="admin-card">
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($items)): ?>
                <div class="admin-empty-state" style="padding:3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <p>No people found. <?php if (!array_filter($filters)): ?><a href="<?= url('admin/outreach/people/create') ?>">Add the first one.</a><?php endif; ?></p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:2px solid #e5e0d5;">
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Name</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Email</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Contact</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Consent</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Last Contacted</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Sent</th>
                                <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $p): ?>
                            <tr style="border-bottom:1px solid #f0ede8;" <?= $p['is_unsubscribed'] ? 'style="opacity:.55;border-bottom:1px solid #f0ede8;"' : '' ?>>
                                <td style="padding:.7rem 1rem;">
                                    <div style="font-weight:500;color:#2d1b0e;"><?= e($p['name']) ?><?= $p['is_unsubscribed'] ? ' <span style="font-size:.7rem;background:#fee2e2;color:#991b1b;padding:.1rem .4rem;border-radius:8px;">unsub</span>' : '' ?></div>
                                    <div style="font-size:.78rem;color:#7a6a5a;"><?= e($p['title'] ? $p['title'] . ($p['organization'] ? ' · ' . $p['organization'] : '') : ($p['organization'] ?? '')) ?></div>
                                </td>
                                <td style="padding:.7rem 1rem;color:#5a4a3a;white-space:nowrap;"><?= e($categories[$p['category']] ?? $p['category']) ?></td>
                                <td style="padding:.7rem 1rem;color:#5a4a3a;">
                                    <?php if ($p['email']): ?>
                                        <a href="mailto:<?= e($p['email']) ?>" style="color:#5C3A21;"><?= e($p['email']) ?></a>
                                    <?php else: ?>
                                        <span style="color:#bbb;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:.7rem 1rem;">
                                    <?php
                                    $csBg = match($p['contact_status']) {
                                        'responded'    => 'background:#d1fae5;color:#065f46;',
                                        'partner'      => 'background:#dbeafe;color:#1e40af;',
                                        'contacted'    => 'background:#fef3c7;color:#92400e;',
                                        'unresponsive' => 'background:#fee2e2;color:#991b1b;',
                                        default        => 'background:#f3f4f6;color:#374151;',
                                    };
                                    ?>
                                    <span style="<?= $csBg ?>padding:.15rem .45rem;border-radius:10px;font-size:.74rem;font-weight:600;white-space:nowrap;">
                                        <?= e($contactStatuses[$p['contact_status']] ?? $p['contact_status']) ?>
                                    </span>
                                </td>
                                <td style="padding:.7rem 1rem;">
                                    <?php
                                    $cnBg = match($p['consent_status']) {
                                        'opted_in'  => 'background:#d1fae5;color:#065f46;',
                                        'opted_out' => 'background:#fee2e2;color:#991b1b;',
                                        default     => 'background:#f3f4f6;color:#374151;',
                                    };
                                    ?>
                                    <span style="<?= $cnBg ?>padding:.15rem .45rem;border-radius:10px;font-size:.74rem;font-weight:600;">
                                        <?= e($consentStatuses[$p['consent_status']] ?? $p['consent_status']) ?>
                                    </span>
                                </td>
                                <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.82rem;white-space:nowrap;">
                                    <?= $p['last_contact_date'] ? date('M j, Y', strtotime($p['last_contact_date'])) : '—' ?>
                                </td>
                                <td style="padding:.7rem 1rem;text-align:center;font-size:.85rem;color:#5C3A21;font-weight:600;">
                                    <?= $p['emails_sent'] ?>
                                </td>
                                <td style="padding:.7rem 1rem;">
                                    <div style="display:flex;gap:.4rem;align-items:center;">
                                        <a href="<?= url('admin/outreach/people/' . $p['id'] . '/edit') ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Edit</a>
                                        <form method="POST" action="<?= url('admin/outreach/people/' . $p['id'] . '/delete') ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.25rem .6rem;" data-confirm="Remove <?= e(addslashes($p['name'])) ?> permanently?">Del</button>
                                        </form>
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
                        <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:.35rem .75rem;font-size:.82rem;color:#5a4a3a;">
                        Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                    </span>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
