<style>
.bilingual-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border: 1px solid #e5e0d5;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1.4rem;
}
.bilingual-col {
    padding: 1.2rem;
}
.bilingual-col:first-child {
    border-right: 1px solid #e5e0d5;
    background: #fdfcfb;
}
.bilingual-col:last-child {
    background: #f7f4ee;
}
.bilingual-col-heading {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin: 0 0 1rem;
    padding-bottom: .6rem;
    border-bottom: 2px solid #e5e0d5;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.bilingual-col:first-child .bilingual-col-heading { color: #2d5a9e; border-color: #2d5a9e33; }
.bilingual-col:last-child  .bilingual-col-heading { color: #5C3A21; border-color: #5C3A2133; }
@media (max-width: 640px) {
    .bilingual-grid { grid-template-columns: 1fr; }
    .bilingual-col:first-child { border-right: none; border-bottom: 1px solid #e5e0d5; }
}
</style>

<div class="admin-wrapper">
    <div class="admin-content" style="max-width:960px;">

        <div style="margin-bottom:1.2rem;">
            <a href="<?= url("admin/content-items/{$section}/{$sub}") ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">
                &larr; Back to <?= htmlspecialchars($label) ?>
            </a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <?= $item ? 'Edit: ' . htmlspecialchars($item['title']) : 'Add ' . htmlspecialchars($label) . ' Item' ?>
                </h2>
                <span style="font-size:.78rem;color:#7a6a5a;"><?= ucfirst($section) ?> › <?= htmlspecialchars($label) ?></span>
            </div>
            <div class="admin-card-body">

                <?php
                $action = $item
                    ? url("admin/content-items/{$section}/{$sub}/{$item['id']}/edit")
                    : url("admin/content-items/{$section}/{$sub}/create");
                ?>
                <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- ── TITLE row ──────────────────────────────── -->
                    <div class="bilingual-grid">
                        <div class="bilingual-col">
                            <p class="bilingual-col-heading">&#127760; English Title</p>
                            <div class="form-group" style="margin:0;">
                                <input type="text" name="title" class="form-input" required
                                       value="<?= htmlspecialchars($item['title'] ?? '') ?>"
                                       placeholder="Title in English">
                            </div>
                        </div>
                        <div class="bilingual-col">
                            <p class="bilingual-col-heading">&#127981; Tiv Title</p>
                            <div class="form-group" style="margin:0;">
                                <input type="text" name="tiv_title" class="form-input"
                                       value="<?= htmlspecialchars($item['tiv_title'] ?? '') ?>"
                                       placeholder="U Tiv (wan sha)">
                            </div>
                        </div>
                    </div>

                    <!-- ── EXCERPT row ────────────────────────────── -->
                    <div class="bilingual-grid">
                        <div class="bilingual-col">
                            <p class="bilingual-col-heading">&#127760; English Summary</p>
                            <div class="form-group" style="margin:0;">
                                <textarea name="excerpt" class="form-textarea" rows="3"
                                          placeholder="Short English description shown in listings..."><?= htmlspecialchars($item['excerpt'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="bilingual-col">
                            <p class="bilingual-col-heading">&#127981; Tiv Summary</p>
                            <div class="form-group" style="margin:0;">
                                <textarea name="tiv_excerpt" class="form-textarea" rows="3"
                                          placeholder="Kper sha u Tiv..."><?= htmlspecialchars($item['tiv_excerpt'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ── FULL CONTENT row ───────────────────────── -->
                    <div class="bilingual-grid">
                        <div class="bilingual-col">
                            <p class="bilingual-col-heading">&#127760; English Content</p>
                            <div class="form-group" style="margin:0;">
                                <textarea name="content" class="form-textarea" rows="14"
                                          placeholder="Full content in English — story, description, article, historical account..."><?= htmlspecialchars($item['content'] ?? '') ?></textarea>
                                <p class="form-hint" style="margin-top:.4rem;">Plain text. Line breaks are preserved on the public page.</p>
                            </div>
                        </div>
                        <div class="bilingual-col">
                            <p class="bilingual-col-heading">&#127981; Tiv Content</p>
                            <div class="form-group" style="margin:0;">
                                <textarea name="tiv_content" class="form-textarea" rows="14"
                                          placeholder="Kper sha u Tiv — tar, nom, sha msugh..."><?= htmlspecialchars($item['tiv_content'] ?? '') ?></textarea>
                                <p class="form-hint" style="margin-top:.4rem;">U Tiv sha. Tor sha i gbe hen a mba.</p>
                            </div>
                        </div>
                    </div>

                    <!-- ── MEDIA ──────────────────────────────────── -->
                    <div class="admin-card" style="margin-bottom:1.2rem;">
                        <div class="admin-card-header" style="padding:.75rem 1rem;">
                            <h3 class="admin-card-title" style="font-size:.95rem;">Media File</h3>
                            <?php if (!empty($item['media_file'])): ?>
                                <span style="font-size:.8rem;color:#5C3A21;">Current: <?= ucfirst($item['media_type']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-card-body" style="padding:1rem;">
                            <?php if (!empty($item['media_file'])): ?>
                                <div style="margin-bottom:.8rem;">
                                    <?php if ($item['media_type'] === 'image'): ?>
                                        <img src="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>"
                                             style="max-width:200px;max-height:140px;border-radius:6px;object-fit:cover;border:1px solid #e5e0d5;">
                                    <?php elseif ($item['media_type'] === 'audio'): ?>
                                        <audio controls style="max-width:360px;display:block;">
                                            <source src="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>">
                                        </audio>
                                    <?php elseif ($item['media_type'] === 'document'): ?>
                                        <a href="<?= UPLOADS_URL . '/' . htmlspecialchars($item['media_file']) ?>" target="_blank" class="btn btn-secondary" style="font-size:.82rem;">&#128196; View Current Document</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="media_file" class="form-input"
                                   accept="image/*,audio/*,.pdf,.doc,.docx">
                            <p class="form-hint">Image (JPG/PNG/WebP), Audio (MP3/OGG/WAV), or Document (PDF/Word) — max 5MB.<?= !empty($item['media_file']) ? ' Leave blank to keep existing.' : '' ?></p>
                        </div>
                    </div>

                    <!-- ── STATUS / FEATURED ──────────────────────── -->
                    <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1.4rem;padding:1rem;background:#f7f4ee;border-radius:8px;">
                        <div class="form-group" style="margin:0;min-width:160px;">
                            <label class="form-label" style="margin-bottom:.3rem;">Status</label>
                            <select name="status" class="form-select">
                                <option value="published" <?= ($item['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>&#128994; Published</option>
                                <option value="draft"     <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>&#128992; Draft</option>
                            </select>
                        </div>
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;margin-top:.4rem;">
                            <input type="checkbox" name="is_featured" value="1"
                                   <?= !empty($item['is_featured']) ? 'checked' : '' ?>>
                            <span>&#11088; Featured Item (shown first in listings)</span>
                        </label>
                    </div>

                    <div style="display:flex;gap:.8rem;">
                        <button type="submit" class="btn btn-primary">
                            <?= $item ? 'Save Changes' : 'Add Item' ?>
                        </button>
                        <a href="<?= url("admin/content-items/{$section}/{$sub}") ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
