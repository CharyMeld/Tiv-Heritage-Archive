<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#129302; Content Generator</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Generates from your archive using the local Ollama model — nothing leaves this server.</p>
        </div>
        <a href="<?= url('admin/marketing') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Dashboard</a>
    </div>

    <?php if (!$ollamaAvailable): ?>
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.9rem 1.2rem;margin-bottom:1.5rem;color:#991b1b;font-size:.88rem;">
            &#9888; The local AI model is not responding right now. Generation will fail until Ollama is back up.
        </div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:720px;margin-bottom:2rem;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/marketing/generate') ?>" id="genForm">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label required">Selection Mode</label>
                    <select name="mode" id="mode" class="form-select">
                        <option value="random">Random (any category)</option>
                        <option value="specific_category">Specific Category (random item within it)</option>
                        <option value="latest">Latest (in a category)</option>
                        <option value="most_popular">Most Popular (in a category)</option>
                        <option value="specific_item">Specific Item (by ID, in a category)</option>
                    </select>
                </div>

                <div class="form-group" id="categoryGroup" style="display:none;">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach ($categories as $key => $c): ?>
                            <option value="<?= e($key) ?>"><?= e($c['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint" id="popularityHint" style="display:none;">Some categories have no view-tracking yet — "Most Popular" will fall back to "Latest" for those.</p>
                </div>

                <div class="form-group" id="itemIdGroup" style="display:none;">
                    <label class="form-label">Item ID</label>
                    <input type="number" name="item_id" class="form-input" placeholder="e.g. 42" min="1">
                    <p class="form-hint">Find the ID from the item's page in the archive.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Prompt Template <span style="font-weight:400;color:#8a7a6a;">(leave blank to use the category's default)</span></label>
                    <select name="template_id" class="form-select">
                        <option value="">— Use default for category —</option>
                        <?php foreach ($templates as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['category']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint"><a href="<?= url('admin/marketing/templates/create') ?>" target="_blank">+ Create a new template</a> if needed.</p>
                </div>

                <button type="submit" class="btn btn-primary" <?= !$ollamaAvailable ? 'disabled' : '' ?>>
                    &#129302; Generate
                </button>
                <p class="form-hint" style="margin-top:.5rem;">This calls the local AI model directly and may take 15-60 seconds — please wait for the page to redirect.</p>
            </form>
        </div>
    </div>

    <!-- Drafts awaiting review -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Drafts Awaiting Review</h2>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($recentPosts)): ?>
                <div class="admin-empty-state" style="padding:2rem;">
                    <p>No drafts yet. Generate your first post above.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.87rem;">
                        <thead>
                            <tr style="background:#f7f4ee;border-bottom:1px solid #e5e0d5;">
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Headline</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Category</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Generated</th>
                                <th style="padding:.65rem 1rem;text-align:left;color:#5C3A21;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPosts as $p): ?>
                            <tr style="border-bottom:1px solid #f0ede8;">
                                <td style="padding:.65rem 1rem;font-weight:500;color:#2d1b0e;"><?= e($p['headline'] ?? '(untitled)') ?></td>
                                <td style="padding:.65rem 1rem;color:#5a4a3a;"><?= e(MarketingPost::SOURCE_TYPES[$p['source_type']] ?? $p['source_type']) ?></td>
                                <td style="padding:.65rem 1rem;color:#7a6a5a;font-size:.82rem;"><?= date('M j, g:ia', strtotime($p['created_at'])) ?></td>
                                <td style="padding:.65rem 1rem;">
                                    <a href="<?= url('admin/marketing/posts/' . $p['id']) ?>" class="btn btn-secondary" style="font-size:.78rem;padding:.25rem .6rem;">Review</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
<script>
function updateGenFormVisibility() {
    var mode = document.getElementById('mode').value;
    var needsCategory = mode !== 'random';
    var needsItemId = mode === 'specific_item';
    document.getElementById('categoryGroup').style.display = needsCategory ? 'block' : 'none';
    document.getElementById('itemIdGroup').style.display = needsItemId ? 'block' : 'none';
    document.getElementById('popularityHint').style.display = mode === 'most_popular' ? 'block' : 'none';
}
document.getElementById('mode').addEventListener('change', updateGenFormVisibility);
updateGenFormVisibility();
</script>
