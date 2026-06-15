<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Review Nomination</h1>
        <a href="<?= url('admin/outreach/nominations') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; All Nominations</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;max-width:900px;">

        <!-- Nominee Details -->
        <div class="admin-card">
            <div class="admin-card-header"><h2 class="admin-card-title">Nominee Information</h2></div>
            <div class="admin-card-body">
                <?php
                $fields = [
                    'Name'          => $nomination['nominee_name'],
                    'Title'         => $nomination['nominee_title'],
                    'Category'      => $nomination['nominee_category'],
                    'Organization'  => $nomination['nominee_organization'],
                    'Email'         => $nomination['nominee_email'],
                    'Website'       => $nomination['nominee_website'],
                    'Country'       => $nomination['nominee_country'],
                    'State/Region'  => $nomination['nominee_state_region'],
                ];
                foreach ($fields as $label => $value):
                    if (empty($value)) continue;
                ?>
                <div style="margin-bottom:.8rem;">
                    <div style="font-size:.78rem;color:#8a7a6a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;"><?= e($label) ?></div>
                    <div style="color:#2d1b0e;margin-top:.1rem;">
                        <?php if ($label === 'Email'): ?>
                            <a href="mailto:<?= e($value) ?>" style="color:#5C3A21;"><?= e($value) ?></a>
                        <?php elseif ($label === 'Website'): ?>
                            <a href="<?= e($value) ?>" target="_blank" style="color:#5C3A21;"><?= e($value) ?></a>
                        <?php else: ?>
                            <?= e($value) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top:1rem;">
                    <div style="font-size:.78rem;color:#8a7a6a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Reason for Nomination</div>
                    <div style="color:#2d1b0e;margin-top:.3rem;line-height:1.6;background:#f7f4ee;border-radius:6px;padding:.8rem;"><?= nl2br(e($nomination['reason_for_nomination'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Actions & Nominator -->
        <div style="display:flex;flex-direction:column;gap:1rem;">

            <!-- Nominator Info -->
            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Submitted By</h2></div>
                <div class="admin-card-body">
                    <div style="color:#2d1b0e;"><?= e($nomination['nominator_name'] ?: 'Anonymous') ?></div>
                    <?php if ($nomination['nominator_email']): ?>
                        <div style="font-size:.85rem;"><a href="mailto:<?= e($nomination['nominator_email']) ?>" style="color:#5C3A21;"><?= e($nomination['nominator_email']) ?></a></div>
                    <?php endif; ?>
                    <div style="font-size:.8rem;color:#8a7a6a;margin-top:.4rem;">Submitted <?= date('M j, Y \a\t g:ia', strtotime($nomination['created_at'])) ?></div>
                </div>
            </div>

            <!-- Current Status -->
            <?php if ($nomination['status'] !== 'pending'): ?>
            <div class="admin-card" style="border-left:3px solid <?= $nomination['status']==='approved' ? '#4a7c59' : '#c62828' ?>;">
                <div class="admin-card-body">
                    <div style="font-weight:600;color:<?= $nomination['status']==='approved' ? '#065f46' : '#991b1b' ?>;">
                        <?= $nomination['status'] === 'approved' ? '&#10003; Approved' : '&#10007; Rejected' ?>
                    </div>
                    <?php if ($nomination['admin_notes']): ?>
                        <div style="margin-top:.4rem;font-size:.85rem;color:#5a4a3a;"><?= e($nomination['admin_notes']) ?></div>
                    <?php endif; ?>
                    <?php if ($nomination['person_id']): ?>
                        <a href="<?= url('admin/outreach/people/' . $nomination['person_id'] . '/edit') ?>" style="display:inline-block;margin-top:.5rem;font-size:.82rem;color:#5C3A21;">&#128279; View Person Record</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Approve Action -->
            <?php if ($nomination['status'] === 'pending'): ?>
            <div class="admin-card" style="border-left:3px solid #4a7c59;">
                <div class="admin-card-header"><h2 class="admin-card-title" style="color:#065f46;">&#10003; Approve Nomination</h2></div>
                <div class="admin-card-body">
                    <form method="POST" action="<?= url('admin/outreach/nominations/' . $nomination['id'] . '/approve') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label">Confirm Category</label>
                            <select name="category" class="form-select">
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= ($nomination['nominee_category'] === $key) ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Notes <span style="font-weight:400;color:#8a7a6a;">(optional)</span></label>
                            <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="Internal note about this approval…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:#4a7c59;border-color:#4a7c59;" data-confirm="Approve and add to influential people database?">Approve &amp; Add to Database</button>
                    </form>
                </div>
            </div>

            <!-- Reject Action -->
            <div class="admin-card" style="border-left:3px solid #c62828;">
                <div class="admin-card-header"><h2 class="admin-card-title" style="color:#991b1b;">&#10007; Reject Nomination</h2></div>
                <div class="admin-card-body">
                    <form method="POST" action="<?= url('admin/outreach/nominations/' . $nomination['id'] . '/reject') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label">Reason for Rejection <span style="font-weight:400;color:#8a7a6a;">(optional)</span></label>
                            <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="e.g. Duplicate, insufficient information…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" data-confirm="Reject this nomination?">Reject Nomination</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>
</div>
