<?php
$h = fn($v) => htmlspecialchars((string) $v);
$listLabel = function (string $col, array $row) use ($e, $fkLabels, $h) {
    $f = $e['fields'][$col] ?? null;
    $v = $row[$col] ?? null;
    if ($v === null || $v === '') return '<span style="color:#b8aa9a;">—</span>';
    if ($f && $f['type'] === 'select') return $h($f['options'][$v] ?? $v);
    if ($f && $f['type'] === 'fk') return $h($fkLabels[$col][$v] ?? "#{$v}");
    return $h($v);
};
$badge = function (?string $status) use ($h) {
    $colors = ['published' => '#edf7f0;color:#4a7c59', 'in_review' => '#fff6e0;color:#8a6d1f', 'disputed' => '#fbeaea;color:#a33',
               'needs_corroboration' => '#fff6e0;color:#8a6d1f', 'unverified' => '#f3ede6;color:#9a8a7a'];
    $style = $colors[$status] ?? '#f3ede6;color:#6a5a4a';
    return '<span style="padding:.15rem .55rem;border-radius:20px;font-size:.74rem;font-weight:600;background:' . $style . ';">'
        . $h(HeritageRegistry::EVIDENCE[$status] ?? HeritageRegistry::REVIEW[$status] ?? ucfirst(str_replace('_', ' ', (string) $status))) . '</span>';
};
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="margin-bottom:.8rem;"><a href="<?= url('admin/heritage') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; Nigeria Heritage dashboard</a></div>

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.2rem;">
        <h1 style="font-size:1.35rem;font-weight:700;color:#2d1b0e;margin:0;"><?= $e['icon'] ?> <?= $h($e['plural']) ?></h1>
        <?php if (empty($e['readonly'])): ?>
        <a href="<?= url("admin/heritage/{$e['key']}/create") ?>" class="btn btn-primary" style="white-space:nowrap;">+ Add <?= $h($e['label']) ?></a>
        <?php endif; ?>
    </div>

    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        <input type="text" name="q" value="<?= $h($q) ?>" class="form-input" placeholder="Search by name…" style="max-width:260px;">
        <?php if (!empty($e['filter'])): ?>
        <select name="filter" class="form-select" style="max-width:220px;">
            <option value="">All types</option>
            <?php foreach ($e['fields'][$e['filter']]['options'] as $k => $label): if ($k === '') continue; ?>
            <option value="<?= $h($k) ?>" <?= $filterValue === (string) $k ? 'selected' : '' ?>><?= $h($label) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <div class="admin-card">
        <?php if (empty($items)): ?>
        <div class="admin-card-body" style="color:#9a8a7a;font-size:.85rem;padding:1.5rem;">
            Nothing here yet<?= ($q || $filterValue) ? ' for this filter' : '' ?>.
            Records are added in small, sourced research batches — see the dashboard.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                <thead>
                    <tr style="background:#f7f4ee;text-align:left;">
                        <?php foreach ($e['list'] as $col): ?>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;"><?= $h($e['fields'][$col]['label'] ?? ucfirst(str_replace('_', ' ', $col))) ?></th>
                        <?php endforeach; ?>
                        <th style="padding:.6rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i => $row): ?>
                    <tr style="border-top:1px solid #f0ece5;<?= $i % 2 ? 'background:#fdfcfb;' : '' ?>">
                        <?php foreach ($e['list'] as $j => $col): ?>
                        <td style="padding:.55rem .75rem;">
                            <?php if ($j === 0): ?>
                                <a href="<?= url("admin/heritage/{$e['key']}/{$row['id']}/edit") ?>" style="color:#5C3A21;font-weight:600;text-decoration:none;"><?= ($e['fields'][$col]['type'] ?? '') === 'fk' ? $listLabel($col, $row) : $h(mb_strimwidth((string) $row[$col], 0, 120, '…')) ?></a>
                            <?php elseif (in_array($col, ['evidence_status', 'review_status', 'status'], true)): ?>
                                <?= $badge($row[$col]) ?>
                            <?php else: ?>
                                <?= $listLabel($col, $row) ?>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td style="padding:.55rem 1rem;text-align:right;"><a href="<?= url("admin/heritage/{$e['key']}/{$row['id']}/edit") ?>" style="font-size:.8rem;color:#5C3A21;font-weight:600;">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?= pagination($pagination, url("admin/heritage/{$e['key']}")) ?>
</div>
</div>
