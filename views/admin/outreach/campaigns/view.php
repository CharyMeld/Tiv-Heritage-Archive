<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;"><?= e($campaign['name']) ?></h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">
                Template: <strong><?= e($campaign['template_name']) ?></strong>
                &middot; Schedule: <strong><?= ucfirst(str_replace('_',' ', $campaign['schedule_type'])) ?></strong>
                &middot; Status:
                <?php
                $bg = match($campaign['status']) {
                    'sent'   => 'background:#d1fae5;color:#065f46;',
                    default  => 'background:#fef3c7;color:#92400e;',
                };
                ?>
                <span style="<?= $bg ?>padding:.1rem .45rem;border-radius:10px;font-size:.8rem;font-weight:600;"><?= ucfirst($campaign['status']) ?></span>
            </p>
        </div>
        <a href="<?= url('admin/outreach/campaigns') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Campaigns</a>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

        <!-- Left column -->
        <div style="display:flex;flex-direction:column;gap:1.2rem;">

            <!-- Recipients -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Recipients</h2>
                    <span style="font-size:.85rem;color:#7a6a5a;"><?= count($recipients) ?> eligible contact<?= count($recipients) !== 1 ? 's' : '' ?></span>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($targetCategories)): ?>
                        <p style="font-size:.88rem;color:#5a4a3a;margin:0;">Targeting <strong>all categories</strong></p>
                    <?php else: ?>
                        <p style="font-size:.88rem;color:#5a4a3a;margin:0 0 .5rem;">Categories:
                            <?php foreach ($targetCategories as $cat): ?>
                                <span style="background:#f7f4ee;border:1px solid #e5e0d5;border-radius:12px;padding:.1rem .5rem;font-size:.8rem;margin:.15rem;"><?= e($categories[$cat] ?? $cat) ?></span>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>

                    <?php if (empty($recipients)): ?>
                        <div style="background:#fef3c7;border-radius:6px;padding:.8rem 1rem;font-size:.87rem;color:#92400e;margin-top:.8rem;">
                            No eligible recipients. Ensure contacts have email addresses and haven't opted out.
                        </div>
                    <?php else: ?>
                        <div style="margin-top:.8rem;max-height:280px;overflow-y:auto;border:1px solid #f0ede8;border-radius:6px;">
                            <?php foreach ($recipients as $i => $r): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem .8rem;<?= $i % 2 === 0 ? 'background:#fafaf8;' : '' ?>border-bottom:1px solid #f0ede8;font-size:.83rem;">
                                <div>
                                    <span style="font-weight:500;color:#2d1b0e;"><?= e($r['name']) ?></span>
                                    <?php if ($r['title']): ?><span style="color:#8a7a6a;"> &middot; <?= e($r['title']) ?></span><?php endif; ?>
                                </div>
                                <span style="color:#7a6a5a;"><?= e($r['email']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Send History -->
            <?php if (!empty($sends)): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Send History</h2>
                    <span style="font-size:.85rem;color:#7a6a5a;"><?= number_format($campaign['total_sent']) ?> sent <?= $campaign['sent_at'] ? 'on ' . date('M j, Y \a\t g:ia', strtotime($campaign['sent_at'])) : '' ?></span>
                </div>
                <div class="admin-card-body" style="padding:0;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Recipient</th>
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Email</th>
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Sent At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sends as $s): ?>
                                <tr style="border-bottom:1px solid #f5f2ef;">
                                    <td style="padding:.6rem 1rem;color:#2d1b0e;"><?= e($s['person_name'] ?? '—') ?></td>
                                    <td style="padding:.6rem 1rem;color:#5a4a3a;"><?= e($s['email']) ?></td>
                                    <td style="padding:.6rem 1rem;">
                                        <span style="<?= $s['status']==='sent' ? 'color:#065f46;' : 'color:#991b1b;' ?>font-size:.8rem;font-weight:600;">
                                            <?= $s['status'] === 'sent' ? '&#10003; Sent' : '&#10007; Failed' ?>
                                        </span>
                                    </td>
                                    <td style="padding:.6rem 1rem;color:#8a7a6a;font-size:.8rem;"><?= date('M j, g:ia', strtotime($s['sent_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right column: email preview + send -->
        <div style="display:flex;flex-direction:column;gap:1rem;">

            <!-- Send Action -->
            <?php if ($campaign['status'] !== 'sent'): ?>
            <div class="admin-card" style="border-left:3px solid #5C3A21;">
                <div class="admin-card-header"><h2 class="admin-card-title" style="color:#5C3A21;">&#128231; Send Campaign</h2></div>
                <div class="admin-card-body">
                    <?php if (empty($recipients)): ?>
                        <p style="font-size:.85rem;color:#991b1b;">No eligible recipients — cannot send.</p>
                    <?php else: ?>
                        <p style="font-size:.88rem;color:#5a4a3a;margin:0 0 1rem;">
                            This will send the email to <strong><?= count($recipients) ?></strong> contact<?= count($recipients) !== 1 ? 's' : '' ?> immediately.
                        </p>
                        <form method="POST" action="<?= url('admin/outreach/campaigns/' . $campaign['id'] . '/send') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary btn-block"
                                    data-confirm="Send this campaign to <?= count($recipients) ?> contact(s) now? This cannot be undone.">
                                &#128231; Send Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="admin-card" style="border-left:3px solid #4a7c59;">
                <div class="admin-card-body">
                    <div style="color:#065f46;font-weight:600;font-size:.9rem;">&#10003; Campaign Sent</div>
                    <div style="font-size:.83rem;color:#5a4a3a;margin-top:.3rem;"><?= number_format($campaign['total_sent']) ?> email<?= $campaign['total_sent'] !== 1 ? 's' : '' ?> dispatched<?= $campaign['sent_at'] ? ' on ' . date('M j, Y', strtotime($campaign['sent_at'])) : '' ?>.</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Template Preview -->
            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Email Preview</h2></div>
                <div class="admin-card-body" style="padding:.5rem;">
                    <div style="font-size:.8rem;color:#8a7a6a;margin-bottom:.4rem;padding:.3rem .5rem;">
                        Subject: <strong><?= e($campaign['subject']) ?></strong>
                    </div>
                    <iframe srcdoc="<?= htmlspecialchars(
                        '<style>body{margin:0;padding:10px;font-family:Georgia,serif;font-size:13px;color:#2d1b0e;line-height:1.6;}</style>'
                        . str_replace(
                            ['{{name}}','{{title}}','{{site_name}}','{{site_url}}'],
                            ['[Name]','[Title]', SITE_NAME, SITE_URL],
                            $campaign['body_html']
                        ),
                        ENT_QUOTES, 'UTF-8'
                    ) ?>" style="width:100%;height:380px;border:1px solid #e5e0d5;border-radius:4px;"></iframe>
                </div>
            </div>

        </div>
    </div>

</div>
</div>
