<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.2rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128240; Newsletter Subscribers</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">
                <?= number_format($counts['subscribed']) ?> subscribed &middot; <?= number_format($counts['unsubscribed']) ?> unsubscribed
                &mdash; collected via the homepage welcome popup.
            </p>
        </div>
        <a href="<?= url('admin/marketing/newsletter') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Newsletter</a>
    </div>

    <form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.2rem;">
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Search name or email…"
               class="form-input" style="flex:1;min-width:200px;max-width:280px;padding:.45rem .7rem;font-size:.88rem;">
        <select name="status" class="form-select" style="padding:.45rem .7rem;font-size:.88rem;">
            <option value="">All Statuses</option>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:.45rem 1rem;font-size:.88rem;">Filter</button>
        <?php if (array_filter($filters)): ?>
            <a href="<?= url('admin/marketing/newsletter/subscribers') ?>" class="btn btn-secondary" style="padding:.45rem .9rem;font-size:.88rem;">Clear</a>
        <?php endif; ?>
    </form>

    <div class="admin-card">
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($items)): ?>
                <div class="admin-empty-state" style="padding:3rem;"><p>No subscribers found.</p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Name</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Email</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Source</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Subscribed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $s): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.6rem 1rem;color:#2d1b0e;"><?= e($s['name'] ?: '—') ?></td>
                                <td style="padding:.6rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($s['email']) ?></td>
                                <td style="padding:.6rem 1rem;color:#7a6a5a;"><?= e($s['source']) ?></td>
                                <td style="padding:.6rem 1rem;">
                                    <span style="background:<?= $s['status'] === 'subscribed' ? '#d1fae5' : '#f3f4f6' ?>;color:<?= $s['status'] === 'subscribed' ? '#065f46' : '#374151' ?>;padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;"><?= e(ucfirst($s['status'])) ?></span>
                                </td>
                                <td style="padding:.6rem 1rem;color:#7a6a5a;font-size:.8rem;"><?= date('M j, Y g:ia', strtotime($s['subscribed_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?= pagination($pagination, url('admin/marketing/newsletter/subscribers') . '?' . http_build_query(array_filter($filters))) ?>

</div>
</div>
