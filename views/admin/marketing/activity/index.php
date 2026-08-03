<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128225; Activity Log</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Live output from the cron pipeline — generation, captions, images, scheduling, and Facebook publishing, in the order they happened.</p>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#5a4a3a;cursor:pointer;">
                <input type="checkbox" id="autoRefreshToggle" checked>
                Auto-refresh every 20s
            </label>
            <a href="<?= url('admin/marketing/activity') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&#8635; Refresh Now</a>
        </div>
    </div>

    <?php if ($unreadable): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1.5rem;color:#991b1b;font-size:.88rem;">
            &#9888; Can't read <code><?= e(MARKETING_CRON_LOG_PATH) ?></code>. This page only shows anything once the cron pipeline has logged its first run on this server.
        </div>
    <?php elseif (empty($lines)): ?>
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="admin-empty-state" style="padding:2rem;">
                    <p>No activity logged yet. The next scheduled run will appear here automatically.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div class="admin-card-body" style="padding:0;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;white-space:nowrap;">Time (WAT)</th>
                                <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Step</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lines as $line):
                                $text = $line['text'];
                                $rowStyle = 'border-bottom:1px solid #f0ede8;';
                                $badge = null;
                                if (stripos($text, 'failed') !== false || stripos($text, 'error') !== false) {
                                    $rowStyle .= 'background:#fef2f2;';
                                    $badge = ['label' => 'Failed', 'bg' => '#fee2e2', 'fg' => '#991b1b'];
                                } elseif (stripos($text, 'published successfully') !== false) {
                                    $badge = ['label' => 'Published', 'bg' => '#d1fae5', 'fg' => '#065f46'];
                                } elseif (stripos($text, 'Found 0 due') !== false) {
                                    $rowStyle .= 'color:#a89a8a;';
                                } elseif (preg_match('/^(Generated post|Caption generated|Image #|Schedule #)/', $text)) {
                                    $badge = ['label' => 'Step', 'bg' => '#dbeafe', 'fg' => '#1e40af'];
                                }
                            ?>
                            <tr style="<?= $rowStyle ?>">
                                <td style="padding:.5rem 1rem;color:#7a6a5a;font-size:.8rem;white-space:nowrap;vertical-align:top;"><?= e($line['time'] ?? '—') ?></td>
                                <td style="padding:.5rem 1rem;color:#2d1b0e;">
                                    <?php if ($badge): ?>
                                        <span style="background:<?= $badge['bg'] ?>;color:<?= $badge['fg'] ?>;padding:.1rem .5rem;border-radius:10px;font-size:.72rem;font-weight:600;margin-right:.5rem;"><?= e($badge['label']) ?></span>
                                    <?php endif; ?>
                                    <?= e($text) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <p style="margin-top:.8rem;font-size:.78rem;color:#a89a8a;">Showing the most recent <?= number_format(count($lines)) ?> log lines.</p>
    <?php endif; ?>

</div>
</div>
<script>
(function () {
    var KEY = 'marketingActivityAutoRefresh';
    var toggle = document.getElementById('autoRefreshToggle');
    var stored = localStorage.getItem(KEY);
    if (stored !== null) toggle.checked = stored === '1';

    toggle.addEventListener('change', function () {
        localStorage.setItem(KEY, toggle.checked ? '1' : '0');
    });

    if (toggle.checked) {
        setTimeout(function () { window.location.reload(); }, 20000);
    }
})();
</script>
