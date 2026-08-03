<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128240; Newsletter</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">
                <?= number_format($subscriberCounts['subscribed']) ?> active subscriber<?= $subscriberCounts['subscribed'] !== 1 ? 's' : '' ?> from the homepage welcome popup — sending to them is a later phase.
            </p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= url('admin/marketing/newsletter/subscribers') ?>" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;">&#128101; Subscribers</a>
            <a href="<?= url('admin/marketing') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
        </div>
    </div>

    <div class="admin-card" style="max-width:520px;margin-bottom:2rem;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/newsletter/generate') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label required">Issue Type</label>
                    <select name="issue_type" class="form-select">
                        <?php foreach ($issueTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint">Built entirely from real archive data — an optional short intro is written by the local AI model, but the item list is never invented.</p>
                </div>
                <button type="submit" class="btn btn-primary">&#128240; Generate Issue</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2 class="admin-card-title">Past Issues</h2></div>
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($items)): ?>
                <div class="admin-empty-state" style="padding:2rem;"><p>No newsletter issues generated yet.</p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Subject</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Type</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $n): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.65rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($n['subject']) ?></td>
                                <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e($issueTypes[$n['issue_type']] ?? $n['issue_type']) ?></td>
                                <td style="padding:.65rem 1rem;color:#7a6a5a;font-size:.82rem;"><?= date('M j, Y', strtotime($n['issue_date'])) ?></td>
                                <td style="padding:.65rem 1rem;">
                                    <span style="background:<?= $n['status'] === 'approved' ? '#d1fae5' : '#f3f4f6' ?>;color:<?= $n['status'] === 'approved' ? '#065f46' : '#374151' ?>;padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;"><?= e(ucfirst($n['status'])) ?></span>
                                </td>
                                <td style="padding:.65rem 1rem;">
                                    <a href="<?= url('admin/marketing/newsletter/' . $n['id']) ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">View</a>
                                </td>
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
