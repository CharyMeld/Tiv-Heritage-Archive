<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#2d1b0e;margin:0;">&#129332; Historical Figures</h1>
            <p style="font-size:.83rem;color:#7a6a5a;margin:.25rem 0 0;">Biographical profiles of notable Tiv people</p>
        </div>
        <a href="<?= url('admin/historical-figures/create') ?>" class="btn btn-primary" style="white-space:nowrap;">+ Add Historical Figure</a>
    </div>

    <div class="admin-card">
        <?php if (empty($items)): ?>
        <div class="admin-card-body" style="color:#9a8a7a;font-size:.85rem;padding:1.5rem;">
            No historical figures yet. <a href="<?= url('admin/historical-figures/create') ?>" style="color:#7a4a2a;font-weight:600;">Add the first one</a>.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                <thead>
                    <tr style="background:#f7f4ee;text-align:left;">
                        <th style="padding:.6rem 1rem;font-weight:600;color:#5a4a3a;">Name</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;">Category</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;">Status</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;text-align:center;">Views</th>
                        <th style="padding:.6rem 1rem;font-weight:600;color:#5a4a3a;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i => $item): ?>
                    <tr style="border-top:1px solid #f0ece5;<?= $i % 2 ? 'background:#fdfcfb;' : '' ?>">
                        <td style="padding:.6rem 1rem;">
                            <strong style="color:#5C3A21;"><?= htmlspecialchars($item['english_name']) ?></strong>
                            <?php if (!empty($item['tiv_name'])): ?>
                            <div style="font-size:.76rem;color:#9a8a7a;"><?= htmlspecialchars($item['tiv_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="padding:.6rem .75rem;color:#5a4a3a;">
                            <?= htmlspecialchars($item['category']) ?>
                            <?php if (!empty($item['subcategory'])): ?>
                            <div style="font-size:.76rem;color:#9a8a7a;"><?= htmlspecialchars($item['subcategory']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="padding:.6rem .75rem;">
                            <span style="padding:.2rem .6rem;border-radius:20px;font-size:.75rem;font-weight:600;<?= $item['status'] === 'published' ? 'background:#edf7f0;color:#4a7c59;' : 'background:#f3ede6;color:#9a8a7a;' ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </td>
                        <td style="padding:.6rem .75rem;text-align:center;color:#7a6a5a;"><?= (int) $item['view_count'] ?></td>
                        <td style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">
                            <a href="<?= url('admin/historical-figures/' . $item['id'] . '/edit') ?>"
                               style="font-size:.8rem;color:#5C3A21;font-weight:600;text-decoration:none;margin-right:.6rem;">Edit</a>
                            <form method="POST" action="<?= url('admin/historical-figures/' . $item['id'] . '/delete') ?>"
                                  style="display:inline;"
                                  onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($item['english_name'])) ?>?')">
                                <?= csrf_field() ?>
                                <button type="submit" style="background:none;border:none;font-size:.8rem;color:#c0392b;cursor:pointer;font-weight:600;padding:0;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?= pagination($pagination, url('admin/historical-figures')) ?>

</div>
</div>
