<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div style="max-width:720px;">
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#128100; Profile Pack</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">
                Ready-to-post Facebook items for your personal profile, from the Nigeria Heritage records. Facebook does not let apps post to personal profiles, so each item is prepared here for you to post.
                For each one: <strong>Copy post</strong> → <strong>Download image</strong> → post on Facebook (or schedule it for the suggested time) → paste <strong>Copy link comment</strong> as the first comment → <strong>Mark posted</strong>.
                A new week is prepared every Sunday morning.
            </p>
        </div>
        <form method="POST" action="<?= url('admin/marketing/profile/generate') ?>" style="display:flex;gap:.5rem;align-items:center;">
            <?= csrf_field() ?>
            <select name="per_day" class="form-select" style="font-size:.82rem;padding:.35rem .5rem;" title="Posts per day">
                <option value="1">1 post a day</option>
                <option value="2">2 posts a day</option>
                <option value="3">3 posts a day</option>
            </select>
            <button type="submit" class="btn btn-primary" style="font-size:.85rem;padding:.45rem .9rem;" onclick="this.disabled=true;this.textContent='Preparing… (up to a minute)';this.form.submit();">&#10133; Prepare next post</button>
        </form>
    </div>

    <p style="font-size:.85rem;color:#7a6a5a;margin:0 0 1rem;">
        Ready: <strong><?= (int) $counts['ready'] ?></strong> &middot; Posted: <strong><?= (int) $counts['posted'] ?></strong> &middot; Skipped: <strong><?= (int) $counts['skipped'] ?></strong>
    </p>

    <?php if (!$upcoming): ?>
        <div class="admin-card"><div class="admin-card-body" style="color:#7a6a5a;">Nothing ready to post. Click <em>Prepare next post</em>, or wait for Sunday's automatic batch.</div></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:1.2rem;">
        <?php foreach ($upcoming as $p): ?>
        <?php
            $overdue = strtotime($p['post_at']) < time();
        ?>
        <div class="admin-card" id="pp-<?= (int) $p['id'] ?>">
            <div class="admin-card-header" style="flex-wrap:wrap;gap:.3rem;">
                <h2 class="admin-card-title" style="font-size:1rem;"><?= e($p['record_name']) ?></h2>
                <span style="font-size:.75rem;color:<?= $overdue ? '#b3261e' : '#7a6a5a' ?>;">
                    <?= $overdue ? 'Overdue · ' : 'Post ' ?><?= e(date('D j M, g:ia', strtotime($p['post_at']))) ?>
                </span>
            </div>
            <div class="admin-card-body">
                <?php if ($p['image_path']): ?>
                    <img src="<?= UPLOADS_URL . '/' . e($p['image_path']) ?>" alt="" loading="lazy" style="width:100%;display:block;border-radius:6px;margin-bottom:.7rem;">
                <?php else: ?>
                    <p style="font-size:.8rem;color:#b3261e;">No image was generated for this item.</p>
                <?php endif; ?>

                <form method="POST" action="<?= url('admin/marketing/profile/' . (int) $p['id'] . '/update') ?>">
                    <?= csrf_field() ?>
                    <textarea name="caption" class="form-textarea pp-caption" rows="9" style="font-size:.85rem;"><?= e($p['caption']) ?></textarea>
                    <input type="text" name="hashtags" class="form-input pp-hashtags" style="margin-top:.4rem;font-size:.82rem;" value="<?= e($p['hashtags'] ?? '') ?>" placeholder="#hashtags">
                    <button type="submit" class="btn btn-secondary" style="font-size:.78rem;margin-top:.4rem;">Save edits</button>
                    <?php if ($p['generated_by'] === 'fallback'): ?>
                        <span style="font-size:.75rem;color:#8a6d00;margin-left:.4rem;">Plain text (AI was unavailable) — worth a quick edit.</span>
                    <?php endif; ?>
                </form>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.8rem;">
                    <button type="button" class="btn btn-primary pp-copy" data-card="pp-<?= (int) $p['id'] ?>" style="font-size:.8rem;">&#128203; Copy post</button>
                    <?php if ($p['image_path']): ?>
                        <a href="<?= UPLOADS_URL . '/' . e($p['image_path']) ?>" download="<?= e(preg_replace('/[^a-z0-9]+/i', '-', $p['record_name'])) ?>.png" class="btn btn-secondary" style="font-size:.8rem;text-align:center;">&#11015; Download image</a>
                    <?php else: ?><span></span><?php endif; ?>
                    <button type="button" class="btn btn-secondary pp-copy-text" data-text="<?= e($p['first_comment'] ?? '') ?>" style="font-size:.8rem;grid-column:1 / -1;">&#128279; Copy link comment</button>
                </div>

                <div style="display:flex;gap:.5rem;margin-top:.8rem;">
                    <form method="POST" action="<?= url('admin/marketing/profile/' . (int) $p['id'] . '/status') ?>" style="flex:1;">
                        <?= csrf_field() ?><input type="hidden" name="status" value="posted">
                        <button type="submit" class="btn btn-primary btn-block" style="font-size:.8rem;background:#2e7d32;border-color:#2e7d32;">&#10003; Mark posted</button>
                    </form>
                    <form method="POST" action="<?= url('admin/marketing/profile/' . (int) $p['id'] . '/status') ?>">
                        <?= csrf_field() ?><input type="hidden" name="status" value="skipped">
                        <button type="submit" class="btn btn-secondary" style="font-size:.8rem;">Skip</button>
                    </form>
                </div>
                <p style="font-size:.75rem;color:#7a6a5a;margin:.6rem 0 0;">
                    <?php if ($p['destination_url']): ?>
                        <a href="<?= e($p['destination_url']) ?>" target="_blank" rel="noopener">Open record &#8599;</a> to check the facts before posting
                    <?php else: ?>
                        <?= e(str_replace('_', ' ', $p['entity_table'])) ?> #<?= (int) $p['entity_id'] ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($history): ?>
    <div class="admin-card" style="margin-top:1.5rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Recently posted &amp; skipped</h2></div>
        <div class="admin-card-body" style="overflow-x:auto;">
            <table class="admin-table" style="width:100%;font-size:.85rem;">
                <thead><tr><th>Record</th><th>Status</th><th>When</th><th>Link clicks</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= e($h['record_name']) ?></td>
                        <td><?= e(MarketingProfilePost::STATUSES[$h['status']] ?? $h['status']) ?></td>
                        <td><?= e(date('j M Y, g:ia', strtotime($h['posted_at'] ?? $h['updated_at']))) ?></td>
                        <td><?= $h['short_code'] ? (int) $h['click_count'] : '—' ?></td>
                        <td>
                            <form method="POST" action="<?= url('admin/marketing/profile/' . (int) $h['id'] . '/status') ?>">
                                <?= csrf_field() ?><input type="hidden" name="status" value="ready">
                                <button type="submit" class="btn btn-secondary" style="font-size:.72rem;padding:.2rem .5rem;">Back to ready</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<script>
(function () {
    function copy(text, btn) {
        var done = function () { var t = btn.textContent; btn.textContent = 'Copied ✓'; setTimeout(function () { btn.textContent = t; }, 1500); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select();
            document.execCommand('copy'); document.body.removeChild(ta); done();
        }
    }
    document.querySelectorAll('.pp-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = document.getElementById(btn.dataset.card);
            // Copies what is in the boxes now, including unsaved edits.
            var text = card.querySelector('.pp-caption').value.trim();
            var tags = card.querySelector('.pp-hashtags').value.trim();
            copy(tags ? text + '\n\n' + tags : text, btn);
        });
    });
    document.querySelectorAll('.pp-copy-text').forEach(function (btn) {
        btn.addEventListener('click', function () { copy(btn.dataset.text, btn); });
    });
})();
</script>
