<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">New Email Template</h1>
        <a href="<?= url('admin/outreach/templates') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Templates</a>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;max-width:1000px;">

        <div class="admin-card">
            <div class="admin-card-body">
                <form method="POST" action="<?= url('admin/outreach/templates/create') ?>" id="templateForm">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label required">Template Name <span style="font-weight:400;color:#8a7a6a;">(internal label)</span></label>
                        <input type="text" name="name" class="form-input" value="<?= e(old('name')) ?>" required placeholder="e.g. Quarterly Outreach — Q1 2026">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Email Subject</label>
                        <input type="text" name="subject" class="form-input" value="<?= e(old('subject')) ?>" required placeholder="e.g. Invitation to Join the Tiv Heritage Archive">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">
                            Email Body <span style="font-weight:400;color:#8a7a6a;">(HTML supported)</span>
                        </label>
                        <textarea name="body_html" id="bodyHtml" class="form-textarea" rows="18"
                                  required style="font-family:monospace;font-size:.85rem;"
                                  placeholder="<p>Dear {{name}},</p>&#10;<p>We are reaching out …</p>"><?= e(old('body_html')) ?></textarea>
                        <p class="form-hint">
                            Variables: <code>{{name}}</code> <code>{{title}}</code> <code>{{site_name}}</code> <code>{{site_url}}</code>
                            &mdash; An unsubscribe link footer is appended automatically.
                        </p>
                    </div>

                    <div style="display:flex;gap:.7rem;">
                        <button type="submit" class="btn btn-primary">Save Template</button>
                        <a href="<?= url('admin/outreach/templates') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Panel -->
        <div>
            <div class="admin-card" style="position:sticky;top:80px;">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Live Preview</h2>
                    <button type="button" onclick="updatePreview()" class="btn btn-secondary" style="font-size:.75rem;padding:.2rem .6rem;">Refresh</button>
                </div>
                <div class="admin-card-body" style="padding:.5rem;">
                    <iframe id="previewFrame" style="width:100%;height:420px;border:none;border-radius:4px;background:#f4f0e8;"></iframe>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<script>
function updatePreview() {
    var body = document.getElementById('bodyHtml').value
        .replace(/\{\{name\}\}/g, 'Honorable Person')
        .replace(/\{\{title\}\}/g, 'Professor')
        .replace(/\{\{site_name\}\}/g, '<?= addslashes(SITE_NAME) ?>')
        .replace(/\{\{site_url\}\}/g, '<?= addslashes(SITE_URL) ?>');

    var html = '<!DOCTYPE html><html><body style="margin:0;padding:20px;background:#f4f0e8;font-family:Georgia,serif;">'
        + '<div style="background:#fff;border-radius:8px;overflow:hidden;max-width:560px;margin:0 auto;">'
        + '<div style="background:#5C3A21;padding:14px 24px;color:#C8A951;font-weight:bold;font-size:16px;"><?= addslashes(SITE_NAME) ?></div>'
        + '<div style="padding:24px;color:#2d1b0e;font-size:14px;line-height:1.7;">' + body + '</div>'
        + '<div style="background:#f7f4ee;padding:10px 24px;font-size:11px;color:#8a7a6a;text-align:center;">&copy; <?= addslashes(SITE_NAME) ?></div>'
        + '</div></body></html>';

    var frame = document.getElementById('previewFrame');
    frame.contentDocument.open();
    frame.contentDocument.write(html);
    frame.contentDocument.close();
}
document.getElementById('bodyHtml').addEventListener('input', function() {
    clearTimeout(this._t);
    this._t = setTimeout(updatePreview, 600);
});
updatePreview();
</script>
