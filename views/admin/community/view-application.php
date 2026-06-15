<div class="admin-wrapper">
    <div class="admin-content" style="max-width:800px;">

        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
            <a href="<?= url('admin/community/applications') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">
                &larr; Back to Applications
            </a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Application: <?= htmlspecialchars($app['full_name']) ?></h2>
                <?php
                $badge = match($app['status']) {
                    'approved' => 'background:#d1fae5;color:#065f46;',
                    'rejected' => 'background:#fee2e2;color:#991b1b;',
                    default    => 'background:#fef3c7;color:#92400e;',
                };
                ?>
                <span style="<?= $badge ?>padding:.3rem .8rem;border-radius:12px;font-size:.8rem;font-weight:600;">
                    <?= ucfirst($app['status']) ?>
                </span>
            </div>
            <div class="admin-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
                    <?php
                    $fields = [
                        'Email'            => $app['email'],
                        'Phone'            => $app['phone'] ?? '-',
                        'Country'          => $app['country'] ?? '-',
                        'State / Region'   => $app['state_region'] ?? '-',
                        'Occupation'       => $app['occupation'] ?? '-',
                        'Member Type'      => ucfirst($app['member_type']),
                        'Area of Interest' => $app['area_of_interest'] ?? '-',
                        'Skills'           => $app['skills'] ?? '-',
                        'Applied'          => date('M j, Y \a\t g:ia', strtotime($app['created_at'])),
                    ];
                    foreach ($fields as $label => $value):
                    ?>
                        <div>
                            <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.2rem;">
                                <?= $label ?>
                            </div>
                            <div style="color:#2d1b0e;"><?= htmlspecialchars($value) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($app['short_bio'])): ?>
                    <div style="margin-top:1.2rem;">
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem;">Short Bio</div>
                        <div style="background:#f7f4ee;border-radius:8px;padding:.9rem;color:#2d1b0e;line-height:1.6;"><?= htmlspecialchars($app['short_bio']) ?></div>
                    </div>
                <?php endif; ?>

                <div style="margin-top:1.2rem;">
                    <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem;">Reason for Joining</div>
                    <div style="background:#f7f4ee;border-radius:8px;padding:.9rem;color:#2d1b0e;line-height:1.6;"><?= htmlspecialchars($app['reason_for_joining']) ?></div>
                </div>

                <?php if (!empty($app['profile_photo'])): ?>
                    <div style="margin-top:1.2rem;">
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.4rem;">Profile Photo</div>
                        <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($app['profile_photo']) ?>"
                             style="max-width:160px;max-height:160px;border-radius:8px;object-fit:cover;border:1px solid #e5e0d5;">
                    </div>
                <?php endif; ?>

                <?php if (!empty($app['supporting_document'])): ?>
                    <div style="margin-top:1.2rem;">
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.4rem;">Supporting Document</div>
                        <a href="<?= UPLOADS_URL . '/' . htmlspecialchars($app['supporting_document']) ?>" target="_blank" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .8rem;">
                            &#128196; View Document
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($app['admin_notes'])): ?>
                    <div style="margin-top:1.2rem;">
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem;">Admin Notes</div>
                        <div style="background:#fff8f0;border-left:3px solid #C8A951;border-radius:0 8px 8px 0;padding:.9rem;color:#2d1b0e;line-height:1.6;"><?= htmlspecialchars($app['admin_notes']) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($member): ?>
                    <div style="margin-top:1.2rem;background:#d1fae5;border-radius:8px;padding:.8rem 1rem;">
                        <strong style="color:#065f46;">&#10003; Member Profile Exists</strong>
                        <span style="color:#047857;font-size:.85rem;margin-left:.5rem;">
                            — Added to directory as <?= ucfirst($member['member_type']) ?>
                            <?= $member['is_active'] ? '' : ' (currently inactive)' ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Panel -->
        <?php if ($app['status'] === 'pending'): ?>
        <div class="admin-card" style="margin-top:1.2rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Review Application</h3>
            </div>
            <div class="admin-card-body">
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <!-- Approve -->
                    <form method="POST" action="<?= url('admin/community/applications/' . $app['id'] . '/approve') ?>" style="flex:1;min-width:220px;">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label">Admin Notes (optional)</label>
                            <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="Welcome message or notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:#2e7d32;border-color:#2e7d32;">
                            &#10003; Approve &amp; Add to Directory
                        </button>
                    </form>
                    <!-- Reject -->
                    <form method="POST" action="<?= url('admin/community/applications/' . $app['id'] . '/reject') ?>" style="flex:1;min-width:220px;">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label">Reason for Rejection (optional)</label>
                            <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="Reason..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary" style="background:#c62828;color:#fff;border-color:#c62828;">
                            &#10005; Reject Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
