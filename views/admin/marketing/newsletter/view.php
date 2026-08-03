<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;"><?= e($issue['subject']) ?></h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;"><?= date('F j, Y', strtotime($issue['issue_date'])) ?> &middot; Status: <?= e(ucfirst($issue['status'])) ?></p>
        </div>
        <a href="<?= url('admin/marketing/newsletter') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Newsletter</a>
    </div>

    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <?php if ($issue['status'] !== 'approved'): ?>
        <form method="POST" action="<?= url('admin/marketing/newsletter/' . $issue['id'] . '/approve') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">&#10003; Approve</button>
        </form>
        <?php endif; ?>
        <a href="<?= url('admin/marketing/newsletter/' . $issue['id'] . '/html') ?>" class="btn btn-secondary">&#11015; Download HTML</a>
        <a href="<?= url('admin/marketing/newsletter/' . $issue['id'] . '/text') ?>" class="btn btn-secondary">&#11015; Download Plain Text</a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2 class="admin-card-title">HTML Preview</h2></div>
        <div class="admin-card-body" style="padding:.5rem;">
            <iframe srcdoc="<?= htmlspecialchars($issue['html_body'], ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:600px;border:1px solid #e5e0d5;border-radius:4px;"></iframe>
        </div>
    </div>

    <div class="admin-card" style="margin-top:1.5rem;">
        <div class="admin-card-header"><h2 class="admin-card-title">Plain Text Version</h2></div>
        <div class="admin-card-body">
            <pre style="white-space:pre-wrap;font-family:monospace;font-size:.85rem;color:#2d1b0e;margin:0;"><?= e($issue['text_body']) ?></pre>
        </div>
    </div>

</div>
</div>
