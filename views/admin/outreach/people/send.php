<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Send Email to <?= e($person['name']) ?></h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;"><?= e($person['email']) ?></p>
        </div>
        <a href="<?= url('admin/outreach/people') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; People</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        <!-- Left: template picker + send -->
        <div style="display:flex;flex-direction:column;gap:1.2rem;">
            <div class="admin-card">
                <div class="admin-card-body">
                    <form method="GET" action="<?= url('admin/outreach/people/' . $person['id'] . '/send') ?>" id="templatePickForm">
                        <div class="form-group">
                            <label class="form-label required">Email Template</label>
                            <select name="template_id" class="form-select" onchange="document.getElementById('templatePickForm').submit()">
                                <?php foreach ($templates as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= (int) $selected['id'] === (int) $t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['name']) ?> — <?= e(mb_strimwidth($t['subject'], 0, 50, '…')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">Preview updates when you change the template. <a href="<?= url('admin/outreach/templates/create') ?>" target="_blank">+ Create a new template</a> if needed.</p>
                        </div>
                    </form>

                    <form method="POST" action="<?= url('admin/outreach/people/' . $person['id'] . '/send') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="template_id" value="<?= (int) $selected['id'] ?>">
                        <p style="font-size:.85rem;color:#5a4a3a;margin:0 0 .8rem;">Subject: <strong><?= e($selected['subject']) ?></strong></p>
                        <button type="submit" class="btn btn-primary btn-block"
                                data-confirm="Send this email to <?= e(addslashes($person['name'])) ?> now?">
                            &#128231; Send Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Send History -->
            <?php if (!empty($history)): ?>
            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Previous Direct Sends</h2></div>
                <div class="admin-card-body" style="padding:0;">
                    <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Template</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Sent By</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr style="border-bottom:1px solid #f5f2ef;">
                                <td style="padding:.6rem 1rem;color:#2d1b0e;"><?= e($h['template_name'] ?? '—') ?></td>
                                <td style="padding:.6rem 1rem;">
                                    <span style="<?= $h['status']==='sent' ? 'color:#065f46;' : 'color:#991b1b;' ?>font-size:.8rem;font-weight:600;">
                                        <?= $h['status'] === 'sent' ? '&#10003; Sent' : '&#10007; Failed' ?>
                                    </span>
                                </td>
                                <td style="padding:.6rem 1rem;color:#7a6a5a;"><?= e($h['sender_name'] ?? '—') ?></td>
                                <td style="padding:.6rem 1rem;color:#8a7a6a;font-size:.8rem;"><?= date('M j, Y g:ia', strtotime($h['sent_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: live preview -->
        <div class="admin-card" style="position:sticky;top:80px;align-self:start;">
            <div class="admin-card-header"><h2 class="admin-card-title">Preview for <?= e($person['name']) ?></h2></div>
            <div class="admin-card-body" style="padding:.5rem;">
                <iframe srcdoc="<?= htmlspecialchars($previewBody, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:480px;border:1px solid #e5e0d5;border-radius:4px;"></iframe>
            </div>
        </div>
    </div>

</div>
</div>
