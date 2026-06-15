<div class="admin-wrapper">
    <div class="admin-content">

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <div style="font-size:.78rem;color:#7a6a5a;text-transform:uppercase;letter-spacing:.06em;"><?= ucfirst($section) ?> › Public Submissions</div>
                <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;"><?= htmlspecialchars($label) ?> — Submissions</h1>
            </div>
            <div style="display:flex;gap:.6rem;">
                <a href="<?= url("admin/content-items/{$section}/{$sub}") ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#128218; Published Items
                </a>
                <?php if ($pendingCount > 0): ?>
                    <span style="background:#ED6C02;color:#fff;padding:.3rem .7rem;border-radius:20px;font-size:.8rem;font-weight:700;">
                        <?= $pendingCount ?> pending
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status filter -->
        <div style="display:flex;gap:.5rem;margin-bottom:1.2rem;flex-wrap:wrap;">
            <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $s => $l): ?>
                <a href="?status=<?= $s ?>"
                   style="padding:.35rem .9rem;border-radius:20px;font-size:.82rem;font-weight:600;text-decoration:none;
                          background:<?= $status===$s?'#5C3A21':'#f7f4ee' ?>;color:<?= $status===$s?'#fff':'#5a4a3a' ?>;
                          border:1px solid <?= $status===$s?'#5C3A21':'#e5e0d5' ?>;">
                    <?= $l ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($items)): ?>
            <div class="admin-card">
                <div class="admin-empty-state" style="padding:3rem;">
                    <p>No <?= $status ?> submissions for <?= htmlspecialchars($label) ?>.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $sub_item): ?>
                <div class="admin-card" style="margin-bottom:1.2rem;">
                    <div class="admin-card-header" style="flex-wrap:wrap;gap:.5rem;">
                        <div style="flex:1;">
                            <h3 style="font-size:1rem;font-weight:700;color:#2d1b0e;margin:0;"><?= htmlspecialchars($sub_item['title']) ?></h3>
                            <?php if (!empty($sub_item['tiv_title'])): ?>
                                <p style="font-size:.85rem;color:#5C3A21;font-style:italic;margin:.2rem 0 0;"><?= htmlspecialchars($sub_item['tiv_title']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php
                        $sBadge = match($sub_item['status']) {
                            'approved' => 'background:#d1fae5;color:#065f46;',
                            'rejected' => 'background:#fee2e2;color:#991b1b;',
                            default    => 'background:#fef3c7;color:#92400e;',
                        };
                        ?>
                        <span style="<?= $sBadge ?>padding:.25rem .7rem;border-radius:12px;font-size:.75rem;font-weight:600;height:fit-content;">
                            <?= ucfirst($sub_item['status']) ?>
                        </span>
                    </div>
                    <div class="admin-card-body">

                        <!-- Submitter info -->
                        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1rem;padding:.7rem 1rem;background:#f7f4ee;border-radius:8px;font-size:.84rem;">
                            <span><strong>From:</strong> <?= htmlspecialchars($sub_item['submitter_name']) ?></span>
                            <span><strong>Email:</strong> <?= htmlspecialchars($sub_item['submitter_email']) ?></span>
                            <span><strong>Submitted:</strong> <?= date('M j, Y g:ia', strtotime($sub_item['created_at'])) ?></span>
                        </div>

                        <!-- Bilingual preview -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                            <div>
                                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#2d5a9e;margin-bottom:.4rem;">English</div>
                                <?php if (!empty($sub_item['excerpt'])): ?>
                                    <p style="font-size:.85rem;color:#5a4a3a;font-style:italic;margin:0 0 .5rem;line-height:1.55;"><?= htmlspecialchars(mb_substr($sub_item['excerpt'], 0, 200)) ?>…</p>
                                <?php endif; ?>
                                <?php if (!empty($sub_item['content'])): ?>
                                    <p style="font-size:.82rem;color:#3a2a1a;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars(mb_substr($sub_item['content'], 0, 300))) ?>…</p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#5C3A21;margin-bottom:.4rem;">Tiv</div>
                                <?php if (!empty($sub_item['tiv_excerpt'])): ?>
                                    <p style="font-size:.85rem;color:#5a4a3a;font-style:italic;margin:0 0 .5rem;line-height:1.55;"><?= htmlspecialchars(mb_substr($sub_item['tiv_excerpt'], 0, 200)) ?>…</p>
                                <?php endif; ?>
                                <?php if (!empty($sub_item['tiv_content'])): ?>
                                    <p style="font-size:.82rem;color:#3a2a1a;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars(mb_substr($sub_item['tiv_content'], 0, 300))) ?>…</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($sub_item['media_file'])): ?>
                            <div style="margin-bottom:.8rem;font-size:.82rem;">
                                <strong>Media:</strong> <?= ucfirst($sub_item['media_type']) ?> —
                                <a href="<?= UPLOADS_URL . '/' . htmlspecialchars($sub_item['media_file']) ?>" target="_blank">View file</a>
                            </div>
                        <?php endif; ?>

                        <!-- Admin actions -->
                        <?php if ($sub_item['status'] === 'pending'): ?>
                        <div style="display:flex;gap:1rem;flex-wrap:wrap;padding-top:.8rem;border-top:1px solid #f0ede8;">
                            <form method="POST" action="<?= url("admin/content-items/{$section}/{$sub}/submissions/{$sub_item['id']}/approve") ?>" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap;">
                                <?= csrf_field() ?>
                                <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                                    <label class="form-label" style="font-size:.78rem;">Admin note (optional)</label>
                                    <input type="text" name="admin_notes" class="form-input" style="padding:.35rem .6rem;font-size:.82rem;" placeholder="Note for the record...">
                                </div>
                                <button type="submit" class="btn btn-primary" style="font-size:.85rem;padding:.4rem 1rem;background:#2e7d32;border-color:#2e7d32;">
                                    &#10003; Approve &amp; Publish
                                </button>
                            </form>
                            <form method="POST" action="<?= url("admin/content-items/{$section}/{$sub}/submissions/{$sub_item['id']}/reject") ?>" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap;">
                                <?= csrf_field() ?>
                                <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                                    <label class="form-label" style="font-size:.78rem;">Reason (optional)</label>
                                    <input type="text" name="admin_notes" class="form-input" style="padding:.35rem .6rem;font-size:.82rem;" placeholder="Reason for rejection...">
                                </div>
                                <button type="submit" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem 1rem;background:#c62828;color:#fff;border-color:#c62828;">
                                    &#10005; Reject
                                </button>
                            </form>
                        </div>
                        <?php elseif (!empty($sub_item['admin_notes'])): ?>
                            <div style="margin-top:.6rem;padding:.6rem .9rem;background:#fff8f0;border-left:3px solid #C8A951;border-radius:0 6px 6px 0;font-size:.82rem;color:#5a4a3a;">
                                <strong>Admin note:</strong> <?= htmlspecialchars($sub_item['admin_notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display:flex;justify-content:center;gap:.5rem;margin-top:1rem;">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?status=<?= $status ?>&page=<?= $pagination['current_page']-1 ?>" class="btn btn-secondary">&laquo; Prev</a>
                    <?php endif; ?>
                    <span style="padding:.4rem .75rem;font-size:.85rem;color:#5a4a3a;">
                        Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                    </span>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?status=<?= $status ?>&page=<?= $pagination['current_page']+1 ?>" class="btn btn-secondary">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
