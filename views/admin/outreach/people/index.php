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
                <div class="op-table-wrap">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th style="width:16%;">Name</th>
                                <th style="width:9%;">Category</th>
                                <th style="width:16%;">Email</th>
                                <th style="width:12%;">Contact</th>
                                <th style="width:11%;">Consent</th>
                                <th style="width:11%;">Last Contacted</th>
                                <th class="op-center-cell" style="width:6%;">Sent</th>
                                <th style="width:9%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $p): ?>
                            <tr class="<?= $p['is_unsubscribed'] ? 'op-row--muted' : '' ?>">
                                <td data-label="Name" class="op-truncate">
                                    <div class="op-truncate" title="<?= e($p['name']) ?>"><?= e($p['name']) ?><?= $p['is_unsubscribed'] ? ' <span class="op-badge" style="background:#fee2e2;color:#991b1b;">unsub</span>' : '' ?></div>
                                    <div class="op-truncate" style="font-size:.9em;color:#7a6a5a;" title="<?= e($p['title'] ? $p['title'] . ($p['organization'] ? ' · ' . $p['organization'] : '') : ($p['organization'] ?? '')) ?>"><?= e($p['title'] ? $p['title'] . ($p['organization'] ? ' · ' . $p['organization'] : '') : ($p['organization'] ?? '')) ?></div>
                                </td>
                                <td data-label="Category" class="op-truncate"><?= e($categories[$p['category']] ?? $p['category']) ?></td>
                                <td data-label="Email" class="op-truncate">
                                    <?php if ($p['email']): ?>
                                        <a href="mailto:<?= e($p['email']) ?>" class="op-truncate" style="color:#5C3A21;display:block;" title="<?= e($p['email']) ?>"><?= e($p['email']) ?></a>
                                    <?php else: ?>
                                        <span style="color:#bbb;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Contact">
                                    <?php
                                    $csBg = match($p['contact_status']) {
                                        'responded'    => 'background:#d1fae5;color:#065f46;',
                                        'partner'      => 'background:#dbeafe;color:#1e40af;',
                                        'contacted'    => 'background:#fef3c7;color:#92400e;',
                                        'unresponsive' => 'background:#fee2e2;color:#991b1b;',
                                        default        => 'background:#f3f4f6;color:#374151;',
                                    };
                                    ?>
                                    <span class="op-badge" style="<?= $csBg ?>">
                                        <?= e($contactStatuses[$p['contact_status']] ?? $p['contact_status']) ?>
                                    </span>
                                </td>
                                <td data-label="Consent">
                                    <?php
                                    $cnBg = match($p['consent_status']) {
                                        'opted_in'  => 'background:#d1fae5;color:#065f46;',
                                        'opted_out' => 'background:#fee2e2;color:#991b1b;',
                                        default     => 'background:#f3f4f6;color:#374151;',
                                    };
                                    ?>
                                    <span class="op-badge" style="<?= $cnBg ?>">
                                        <?= e($consentStatuses[$p['consent_status']] ?? $p['consent_status']) ?>
                                    </span>
                                </td>
                                <td data-label="Last Contacted" style="color:#7a6a5a;">
                                    <?= $p['last_contact_date'] ? date('M j, Y', strtotime($p['last_contact_date'])) : '—' ?>
                                </td>
                                <td data-label="Sent" class="op-center-cell" style="color:#5C3A21;font-weight:600;">
                                    <?= $p['emails_sent'] ?>
                                </td>
                                <td data-label="Actions" class="op-actions-cell">
                                    <button type="button" class="btn btn-secondary op-actions-toggle" onclick="opToggleActions('ppl-<?= (int) $p['id'] ?>')">
                                        Actions &#9662;
                                    </button>
                                    <div id="op-actions-ppl-<?= (int) $p['id'] ?>" class="op-actions-menu">
                                        <?php if ($p['email'] && !$p['is_unsubscribed'] && $p['consent_status'] !== 'opted_out'): ?>
                                            <a href="<?= url('admin/outreach/people/' . $p['id'] . '/send') ?>" class="btn btn-primary">&#128231; Send</a>
                                        <?php endif; ?>
                                        <a href="<?= url('admin/outreach/people/' . $p['id'] . '/edit') ?>" class="btn btn-secondary">Edit</a>
                                        <form method="POST" action="<?= url('admin/outreach/people/' . $p['id'] . '/delete') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger" style="width:100%;" data-confirm="Remove <?= e(addslashes($p['name'])) ?> permanently?">Delete</button>
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

<?php include BASE_PATH . '/views/admin/outreach/_responsive_table.php'; ?>
