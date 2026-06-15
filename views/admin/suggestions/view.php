<div class="admin-wrapper">
    <div class="admin-content" style="max-width:760px;">

        <div style="margin-bottom:1.2rem;">
            <a href="<?= url('admin/suggestions') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; Back to Suggestions</a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header" style="flex-wrap:wrap;gap:.5rem;">
                <h2 class="admin-card-title" style="flex:1;"><?= htmlspecialchars($suggestion['subject']) ?></h2>
                <?php
                $statusStyles = [
                    'new'          => 'background:#fef3c7;color:#92400e;',
                    'read'         => 'background:#e0f2fe;color:#0369a1;',
                    'under_review' => 'background:#ede9fe;color:#5b21b6;',
                    'in_progress'  => 'background:#d1fae5;color:#065f46;',
                    'implemented'  => 'background:#d1fae5;color:#065f46;',
                    'closed'       => 'background:#f1f5f9;color:#475569;',
                ];
                $sStyle = $statusStyles[$suggestion['status']] ?? 'background:#f1f5f9;color:#475569;';
                ?>
                <span style="<?= $sStyle ?>padding:.3rem .8rem;border-radius:12px;font-size:.8rem;font-weight:600;">
                    <?= $statuses[$suggestion['status']] ?? ucfirst($suggestion['status']) ?>
                </span>
            </div>
            <div class="admin-card-body">
                <!-- Metadata -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.2rem;">
                    <div>
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.2rem;">From</div>
                        <div style="color:#2d1b0e;"><?= htmlspecialchars($suggestion['full_name']) ?></div>
                        <div style="color:#5a4a3a;font-size:.85rem;"><?= htmlspecialchars($suggestion['email']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.2rem;">Category</div>
                        <div style="color:#2d1b0e;"><?= htmlspecialchars($categories[$suggestion['category']] ?? $suggestion['category']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.2rem;">Submitted</div>
                        <div style="color:#2d1b0e;"><?= date('M j, Y \a\t g:ia', strtotime($suggestion['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Message -->
                <div style="background:#f7f4ee;border-radius:8px;padding:1.2rem;line-height:1.7;color:#2d1b0e;white-space:pre-line;">
                    <?= htmlspecialchars($suggestion['message']) ?>
                </div>

                <!-- Attachment -->
                <?php if (!empty($suggestion['attachment'])): ?>
                    <div style="margin-top:1rem;">
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.4rem;">Attachment</div>
                        <?php
                        $ext  = strtolower(pathinfo($suggestion['attachment'], PATHINFO_EXTENSION));
                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        ?>
                        <?php if ($isImg): ?>
                            <a href="<?= UPLOADS_URL . '/' . htmlspecialchars($suggestion['attachment']) ?>" target="_blank">
                                <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($suggestion['attachment']) ?>"
                                     style="max-width:300px;max-height:200px;border-radius:6px;border:1px solid #e5e0d5;">
                            </a>
                        <?php else: ?>
                            <a href="<?= UPLOADS_URL . '/' . htmlspecialchars($suggestion['attachment']) ?>" target="_blank"
                               class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .8rem;">
                                &#128196; Download Attachment
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Admin Notes -->
                <?php if (!empty($suggestion['admin_notes'])): ?>
                    <div style="margin-top:1rem;background:#fff8f0;border-left:3px solid #C8A951;border-radius:0 8px 8px 0;padding:.9rem;">
                        <div style="font-size:.78rem;color:#7a6a5a;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem;">Admin Notes</div>
                        <div style="color:#2d1b0e;line-height:1.6;white-space:pre-line;"><?= htmlspecialchars($suggestion['admin_notes']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Status Panel -->
        <div class="admin-card" style="margin-top:1.2rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Update Status</h3>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/suggestions/' . $suggestion['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statuses as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $suggestion['status']===$val?'selected':'' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-textarea" rows="2"
                                      placeholder="Internal notes..."><?= htmlspecialchars($suggestion['admin_notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
