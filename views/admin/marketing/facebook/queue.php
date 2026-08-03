<?php
function renderQueueTable(array $items, bool $connected, bool $igConnected, string $emptyMsg): void {
    if (empty($items)) {
        echo '<div class="admin-empty-state" style="padding:1.5rem;"><p>' . e($emptyMsg) . '</p></div>';
        return;
    }
    ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Headline</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Platform</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Scheduled For</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Attempts</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $s): ?>
                <?php
                $wiredUp = in_array($s['platform'], ['facebook', 'instagram'], true);
                $platformConnected = $s['platform'] === 'facebook' ? $connected : ($s['platform'] === 'instagram' ? $igConnected : false);
                ?>
                <tr style="border-bottom:1px solid #f0ede8;">
                    <td style="padding:.6rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($s['headline'] ?? '(untitled)') ?></td>
                    <td style="padding:.6rem 1rem;color:#5a4a3a;"><?= e(ucfirst($s['platform'])) ?></td>
                    <td style="padding:.6rem 1rem;color:#7a6a5a;font-size:.8rem;"><?= date('M j, Y g:ia', strtotime($s['scheduled_at'])) ?></td>
                    <td style="padding:.6rem 1rem;"><?= (int) $s['attempts'] ?></td>
                    <td style="padding:.6rem 1rem;">
                        <?php if ($wiredUp): ?>
                        <form method="POST" action="<?= url('admin/marketing/facebook/retry/' . $s['id']) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary" style="font-size:.75rem;padding:.2rem .55rem;" <?= !$platformConnected ? 'disabled title="' . e(ucfirst($s['platform'])) . ' is not connected"' : '' ?>>
                                &#8635; <?= $s['attempts'] > 0 ? 'Retry' : 'Publish Now' ?>
                            </button>
                        </form>
                        <?php else: ?>
                            <span style="font-size:.78rem;color:#8a7a6a;">No publisher wired up for this platform yet.</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function renderPublishedTable(array $items, string $emptyMsg, array $whatsappCaptions = [], array $imageUrls = []): void {
    if (empty($items)) {
        echo '<div class="admin-empty-state" style="padding:1.5rem;"><p>' . e($emptyMsg) . '</p></div>';
        return;
    }
    ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Headline</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Platform</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Published</th>
                    <th style="padding:.6rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $s): ?>
                <tr style="border-bottom:1px solid #f0ede8;">
                    <td style="padding:.6rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($s['headline'] ?? '(untitled)') ?></td>
                    <td style="padding:.6rem 1rem;color:#5a4a3a;"><?= e(ucfirst($s['platform'])) ?></td>
                    <td style="padding:.6rem 1rem;color:#7a6a5a;font-size:.8rem;"><?= $s['published_at'] ? date('M j, Y g:ia', strtotime($s['published_at'])) : '—' ?></td>
                    <td style="padding:.6rem 1rem;">
                        <?php if (!empty($s['published_url'])): ?>
                        <?php if ($s['platform'] === 'facebook'): ?>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($s['published_url']) ?>"
                           target="_blank" rel="noopener"
                           class="btn btn-secondary" style="font-size:.75rem;padding:.2rem .55rem;">
                            &#128257; Share to My Profile
                        </a>
                        <?php endif; ?>
                        <a href="<?= e($s['published_url']) ?>" target="_blank" rel="noopener" style="margin-left:.5rem;font-size:.78rem;color:#5C3A21;">
                            View <?= $s['platform'] === 'instagram' ? 'on Instagram' : 'Post' ?>
                        </a>
                        <?php
                        $waCaption = $whatsappCaptions[(int) $s['post_id']] ?? null;
                        $waText = trim(($waCaption['caption_text'] ?? $s['headline'] ?? '') . "\n\n" . $s['published_url']);
                        $waImageUrl = $imageUrls[(int) $s['id']] ?? '';
                        ?>
                        <button type="button" class="wa-share-btn btn btn-secondary"
                                data-caption="<?= e($waText) ?>"
                                data-image="<?= e($waImageUrl) ?>"
                                data-wa-fallback="https://wa.me/?text=<?= urlencode($waText) ?>"
                                style="margin-left:.5rem;font-size:.75rem;padding:.2rem .55rem;background:#25D366;color:#fff;border-color:#25D366;">
                            &#128241; Share to WhatsApp
                        </button>
                        <?php else: ?>
                            <span style="font-size:.78rem;color:#8a7a6a;">No link recorded.</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Publishing Queue</h1>
        <a href="<?= url('admin/marketing/facebook/settings') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Facebook &amp; Instagram Settings</a>
    </div>

    <?php if (!$connected): ?>
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1rem;color:#92400e;font-size:.88rem;">
            Facebook isn't connected yet — items here will queue but publishing is disabled until you add real credentials in Settings and click Test Connection.
        </div>
    <?php endif; ?>

    <?php if (!$igConnected): ?>
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1.5rem;color:#92400e;font-size:.88rem;">
            Instagram isn't connected yet — items here will queue but publishing is disabled until you add an Instagram Business Account ID in Settings and click Test Instagram Connection.
        </div>
    <?php endif; ?>

    <div class="admin-card" style="margin-bottom:1.2rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Pending</h2></div>
        <div class="admin-card-body" style="padding:0;">
            <?php renderQueueTable($pending, $connected, $igConnected, 'Nothing pending.'); ?>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:1.2rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Due Now</h2></div>
        <div class="admin-card-body" style="padding:0;">
            <?php renderQueueTable($due, $connected, $igConnected, 'Nothing due right now.'); ?>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:1.2rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Failed</h2></div>
        <div class="admin-card-body" style="padding:0;">
            <?php renderQueueTable($failed, $connected, $igConnected, 'No failed publish attempts.'); ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Published</h2>
            <a href="<?= e(WHATSAPP_CHANNEL_URL) ?>" target="_blank" rel="noopener" class="admin-card-link">Open My WhatsApp Channel &#8599;</a>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <p style="padding:.9rem 1rem 0;margin:0;font-size:.82rem;color:#7a6a5a;">
                Neither Facebook nor WhatsApp let an app post to a personal profile or channel
                on anyone's behalf — both buttons open that platform's own share dialog with the
                post pre-filled, so it's one click to confirm rather than composing anything
                yourself. For WhatsApp, pick your channel from the share sheet that opens. On phones,
                the branded post image attaches automatically; on desktop browsers that don't support
                sharing files, it falls back to a text-only link and you'll need to attach the image
                yourself (right-click &rarr; Save Image on "View Post", or grab it from the Image
                Generator).
            </p>
            <?php renderPublishedTable($published, 'Nothing published yet.', $whatsappCaptions, $imageUrls); ?>
        </div>
    </div>

</div>
</div>
<script>
document.querySelectorAll('.wa-share-btn').forEach(function (btn) {
    btn.addEventListener('click', async function () {
        var text = btn.dataset.caption;
        var imageUrl = btn.dataset.image;
        var fallback = btn.dataset.waFallback;

        if (imageUrl && navigator.share && navigator.canShare) {
            try {
                var resp = await fetch(imageUrl);
                var blob = await resp.blob();
                var filename = imageUrl.split('/').pop() || 'post.png';
                var file = new File([blob], filename, { type: blob.type || 'image/png' });

                if (navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({ files: [file], text: text });
                    } catch (shareErr) {
                        // User cancelled the share sheet, or the share failed after
                        // opening — either way, don't also pop the fallback link.
                    }
                    return;
                }
            } catch (fetchErr) {
                // Couldn't fetch/build the image file — fall through to the
                // text-only link below.
            }
        }

        window.open(fallback, '_blank', 'noopener');
    });
});
</script>
