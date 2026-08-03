<?php
$platformColors = [
    'facebook' => '#1877F2', 'instagram' => '#E1306C', 'x' => '#000000',
    'linkedin' => '#0A66C2', 'telegram' => '#26A5E4', 'whatsapp' => '#25D366',
];
$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128197; Content Calendar</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Internal scheduling only — nothing here publishes automatically yet.</p>
        </div>
        <a href="<?= url('admin/marketing/scheduled') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&#128203; List View</a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <a href="<?= url('admin/marketing/calendar?year=' . $prevYear . '&month=' . $prevMonth) ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .8rem;">&larr; Prev</a>
        <h2 style="margin:0;color:#5C3A21;font-size:1.15rem;"><?= e($monthLabel) ?></h2>
        <a href="<?= url('admin/marketing/calendar?year=' . $nextYear . '&month=' . $nextMonth) ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.4rem .8rem;">Next &rarr;</a>
    </div>

    <div class="admin-card">
        <div class="admin-card-body" style="padding:.75rem;">
            <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:2px;font-size:.75rem;font-weight:600;color:#8a7a6a;text-transform:uppercase;margin-bottom:.4rem;">
                <div style="padding:.4rem;text-align:center;">Sun</div>
                <div style="padding:.4rem;text-align:center;">Mon</div>
                <div style="padding:.4rem;text-align:center;">Tue</div>
                <div style="padding:.4rem;text-align:center;">Wed</div>
                <div style="padding:.4rem;text-align:center;">Thu</div>
                <div style="padding:.4rem;text-align:center;">Fri</div>
                <div style="padding:.4rem;text-align:center;">Sat</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:2px;">
                <?php for ($i = 0; $i < $startWeekday; $i++): ?>
                    <div style="min-height:100px;background:#faf8f4;border-radius:6px;"></div>
                <?php endfor; ?>
                <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                    <?php $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day); ?>
                    <div style="min-height:100px;background:<?= $dateStr === $today ? '#fdf8f0' : '#fff' ?>;border:1px solid <?= $dateStr === $today ? '#C8A951' : '#f0ede8' ?>;border-radius:6px;padding:.4rem;">
                        <div style="font-size:.78rem;font-weight:600;color:<?= $dateStr === $today ? '#5C3A21' : '#8a7a6a' ?>;margin-bottom:.3rem;"><?= $day ?></div>
                        <?php if (!empty($byDay[$day])): ?>
                            <?php foreach (array_slice($byDay[$day], 0, 3) as $entry): ?>
                                <a href="<?= url('admin/marketing/schedule/' . $entry['id'] . '/edit') ?>" style="display:block;font-size:.68rem;color:#fff;background:<?= $platformColors[$entry['platform']] ?? '#5C3A21' ?>;padding:.15rem .35rem;border-radius:4px;margin-bottom:.2rem;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($entry['headline'] ?? '') ?>">
                                    <?= date('g:ia', strtotime($entry['scheduled_at'])) ?> <?= e(ucfirst($entry['platform'])) ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if (count($byDay[$day]) > 3): ?>
                                <div style="font-size:.68rem;color:#8a7a6a;">+<?= count($byDay[$day]) - 3 ?> more</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

</div>
</div>
