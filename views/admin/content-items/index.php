<div class="admin-wrapper">
    <div class="admin-content">

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <div style="font-size:.78rem;color:#7a6a5a;text-transform:uppercase;letter-spacing:.06em;">
                    <?= ucfirst(htmlspecialchars($section)) ?>
                </div>
                <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;"><?= htmlspecialchars($label) ?></h1>
            </div>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                <a href="<?= url("admin/content-items/{$section}/{$sub}/submissions") ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#128196; Public Submissions
                </a>
                <a href="<?= url($section . '/' . $sub) ?>" target="_blank" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#127760; View Page
                </a>
                <a href="<?= url("admin/content-items/{$section}/{$sub}/create") ?>" class="btn btn-primary" style="font-size:.85rem;padding:.4rem .9rem;">
                    &#43; Add Item
                </a>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <?php if (empty($items)): ?>
                    <div class="admin-empty-state" style="padding:3rem;text-align:center;">
                        <p>No <?= htmlspecialchars($label) ?> items yet.</p>
                        <a href="<?= url("admin/content-items/{$section}/{$sub}/create") ?>" class="btn btn-primary" style="margin-top:.8rem;">Add First Item</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
                            <thead>
                                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Title</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Tiv Title</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Media</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Status</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Featured</th>
                                    <th style="padding:.7rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Added</th>
                                    <th style="padding:.7rem 1rem;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr style="border-bottom:1px solid #f0ede8;">
                                        <td style="padding:.7rem 1rem;">
                                            <div style="font-weight:500;color:#2d1b0e;"><?= htmlspecialchars($item['title']) ?></div>
                                            <?php if (!empty($item['author_name'])): ?>
                                                <div style="font-size:.76rem;color:#7a6a5a;">by <?= htmlspecialchars($item['author_name']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;color:#5C3A21;font-style:italic;font-size:.85rem;">
                                            <?= htmlspecialchars($item['tiv_title'] ?? '-') ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;font-size:.82rem;color:#5a4a3a;">
                                            <?= $item['media_type'] !== 'none' ? ucfirst($item['media_type']) : '—' ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;">
                                            <span style="<?= $item['status']==='published' ? 'background:#d1fae5;color:#065f46;' : 'background:#fef3c7;color:#92400e;' ?>padding:.15rem .5rem;border-radius:10px;font-size:.75rem;font-weight:600;">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td style="padding:.7rem 1rem;text-align:center;">
                                            <?= $item['is_featured'] ? '&#11088;' : '—' ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;color:#7a6a5a;font-size:.8rem;white-space:nowrap;">
                                            <?= date('M j, Y', strtotime($item['created_at'])) ?>
                                        </td>
                                        <td style="padding:.7rem 1rem;">
                                            <div style="display:flex;gap:.4rem;">
                                                <a href="<?= url("admin/content-items/{$section}/{$sub}/{$item['id']}/edit") ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Edit</a>
                                                <form method="POST" action="<?= url("admin/content-items/{$section}/{$sub}/{$item['id']}/delete") ?>" data-confirm="Delete this item permanently?">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;background:#fee2e2;color:#991b1b;border-color:#fca5a5;">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display:flex;justify-content:center;gap:.5rem;padding:1rem;">
                            <?php if ($pagination['has_prev']): ?>
                                <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">&laquo; Prev</a>
                            <?php endif; ?>
                            <span style="padding:.35rem .75rem;font-size:.82rem;color:#5a4a3a;">
                                Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                            </span>
                            <?php if ($pagination['has_next']): ?>
                                <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="btn btn-secondary" style="font-size:.82rem;padding:.35rem .75rem;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
