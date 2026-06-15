<div class="admin-wrapper">
    <div class="admin-section-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
        <h2 style="margin:0">API Keys</h2>
        <span style="font-size:.9rem;color:#666"><?= count($keys) ?> key<?= count($keys) !== 1 ? 's' : '' ?> total</span>
    </div>

    <?php if (empty($keys)): ?>
        <div class="empty-state">
            <p>No API keys have been issued yet.</p>
            <a href="<?= url('api/request-key') ?>" class="btn btn-primary">Issue First Key</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name / Email</th>
                        <th>Key</th>
                        <th>Status</th>
                        <th>Usage</th>
                        <th>Daily Limit</th>
                        <th>Last Used</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keys as $k): ?>
                        <tr>
                            <td>
                                <strong><?= e($k['name']) ?></strong><br>
                                <small style="color:#888"><?= e($k['email']) ?></small>
                                <?php if ($k['purpose']): ?>
                                    <br><small style="color:#aaa;font-style:italic"><?= e(mb_substr($k['purpose'], 0, 60)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="font-size:.8rem;word-break:break-all">
                                    <?= e(substr($k['api_key'], 0, 12)) ?>…
                                </code>
                            </td>
                            <td>
                                <?php
                                $badge = match($k['status']) {
                                    'active'    => ['color'=>'#27ae60','bg'=>'#eafaf1','label'=>'Active'],
                                    'revoked'   => ['color'=>'#c0392b','bg'=>'#fdedec','label'=>'Revoked'],
                                    'suspended' => ['color'=>'#e67e22','bg'=>'#fef9e7','label'=>'Suspended'],
                                    default     => ['color'=>'#888','bg'=>'#f5f5f5','label'=>ucfirst($k['status'])],
                                };
                                ?>
                                <span style="background:<?= $badge['bg'] ?>;color:<?= $badge['color'] ?>;
                                            padding:.2rem .55rem;border-radius:4px;font-size:.8rem;font-weight:600">
                                    <?= $badge['label'] ?>
                                </span>
                            </td>
                            <td>
                                <span title="Today"><?= number_format($k['requests_today']) ?></span>
                                <span style="color:#ccc"> / </span>
                                <span title="All time" style="color:#888"><?= number_format($k['requests_total']) ?></span>
                            </td>
                            <td>
                                <form method="POST" action="<?= url('admin/api-keys/' . $k['id'] . '/limit') ?>"
                                      style="display:flex;gap:.3rem;align-items:center">
                                    <?= csrf_field() ?>
                                    <input type="number" name="daily_limit" value="<?= (int)$k['daily_limit'] ?>"
                                           min="1" max="1000000"
                                           style="width:80px;padding:.25rem .4rem;border:1px solid #ddd;border-radius:4px;font-size:.85rem">
                                    <button class="btn btn-sm" type="submit" style="font-size:.75rem;padding:.25rem .5rem">Set</button>
                                </form>
                            </td>
                            <td style="font-size:.85rem;color:#888">
                                <?= $k['last_used_at'] ? date('d M Y', strtotime($k['last_used_at'])) : '—' ?>
                            </td>
                            <td style="font-size:.85rem;color:#888">
                                <?= date('d M Y', strtotime($k['created_at'])) ?>
                            </td>
                            <td>
                                <?php if ($k['status'] === 'active'): ?>
                                    <form method="POST" action="<?= url('admin/api-keys/' . $k['id'] . '/revoke') ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger" type="submit"
                                                onclick="return confirm('Revoke this API key?')"
                                                style="font-size:.78rem">Revoke</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= url('admin/api-keys/' . $k['id'] . '/restore') ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm" type="submit"
                                                style="font-size:.78rem">Restore</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
