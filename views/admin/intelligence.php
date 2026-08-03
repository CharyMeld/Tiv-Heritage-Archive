<div class="admin-page-header">
    <h1>Archive Intelligence</h1>
    <p class="admin-page-subtitle">Self-contained AI — all knowledge from the archive database</p>
</div>

<?php if (!empty($stats['error'])): ?>
<div class="alert alert-warning">
    <strong>Index not ready:</strong> <?= e($stats['error']) ?>
    <br>Run the migration: <code>database/migrations/create_search_index.sql</code>
</div>
<?php else: ?>

<!-- Status cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#5C3A21;"><?= number_format($stats['total_indexed'] ?? 0) ?></div>
        <div style="color:#7a6a5a;font-size:.85rem;margin-top:.3rem;">Records Indexed</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#5C3A21;"><?= number_format($stats['knowledge_links'] ?? 0) ?></div>
        <div style="color:#7a6a5a;font-size:.85rem;margin-top:.3rem;">Knowledge Links</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#5C3A21;"><?= number_format($stats['ngrams'] ?? 0) ?></div>
        <div style="color:#7a6a5a;font-size:.85rem;margin-top:.3rem;">Learned Translations</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;text-align:center;">
        <div style="font-size:.95rem;font-weight:600;color:#5C3A21;"><?= $stats['last_updated'] ? date('M j, Y H:i', strtotime($stats['last_updated'])) : 'Never' ?></div>
        <div style="color:#7a6a5a;font-size:.85rem;margin-top:.3rem;">Last Indexed</div>
    </div>
</div>

<!-- Content type breakdown -->
<?php if (!empty($stats['by_type'])): ?>
<div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.5rem;margin-bottom:2rem;">
    <h3 style="margin:0 0 1rem;font-size:1rem;color:#2d1b0e;">Index by Content Type</h3>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
        <?php foreach ($stats['by_type'] as $t): ?>
        <span style="padding:.3rem .8rem;background:#faf8f5;border:1px solid #e5e0d5;border-radius:20px;font-size:.82rem;">
            <strong><?= e($t['content_type']) ?></strong> <span style="color:#7a6a5a;"><?= number_format($t['cnt']) ?></span>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- File extraction tools -->
<div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.5rem;margin-bottom:2rem;">
    <h3 style="margin:0 0 1rem;font-size:1rem;">File Extraction Tools</h3>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
        <?php foreach (($stats['tools'] ?? []) as $tool => $info): ?>
        <span style="padding:.35rem .9rem;border-radius:20px;font-size:.82rem;font-weight:600;
                     background:<?= $info['available'] ? '#dcfce7' : '#fee2e2' ?>;
                     color:<?= $info['available'] ? '#166534' : '#991b1b' ?>;">
            <?= $info['available'] ? '✓' : '✗' ?> <?= e($tool) ?>
            <?php if ($info['available'] && $info['path']): ?><span style="font-weight:400;opacity:.7;"> (<?= e($info['path']) ?>)</span><?php endif; ?>
        </span>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($stats['extract_col_missing'])): ?>
    <p style="color:#dc2626;font-size:.85rem;margin:0;">⚠ Run <code>database/migrations/add_extracted_text.sql</code> to enable file text extraction.</p>
    <?php else: ?>
    <div style="display:flex;gap:1.5rem;font-size:.85rem;color:#5a4a3a;">
        <span>📄 Files extracted: <strong><?= number_format($stats['files_done'] ?? 0) ?></strong></span>
        <span>⏳ Pending: <strong><?= number_format($stats['files_pending'] ?? 0) ?></strong></span>
        <?php if ($stats['files_failed'] ?? 0): ?><span style="color:#dc2626;">✗ Failed: <strong><?= number_format($stats['files_failed']) ?></strong></span><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Reindex action -->
<div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.5rem;margin-bottom:2rem;">
    <h3 style="margin:0 0 .5rem;font-size:1rem;">Rebuild Search Index</h3>
    <p style="color:#5a4a3a;font-size:.88rem;margin:0 0 1rem;">
        Scans all archive tables (words, names, proverbs, foods, plants, festivals, animals, Bible, documents, audio, publications),
        rebuilds knowledge graph links, and refreshes learned translation statistics.
        Run after importing new content or making bulk edits.
    </p>
    <button id="reindexBtn" class="btn btn-primary" onclick="triggerReindex()">
        &#128260; Rebuild Index Now
    </button>
    <span id="reindexStatus" style="margin-left:1rem;font-size:.88rem;color:#7a6a5a;"></span>
</div>

<!-- How it works -->
<div style="background:#faf8f5;border:1px solid #e5e0d5;border-radius:10px;padding:1.5rem;">
    <h3 style="margin:0 0 1rem;font-size:1rem;color:#5C3A21;">How the Local AI Works</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;font-size:.85rem;color:#3a2a1a;line-height:1.7;">
        <div>
            <strong>&#128269; Bilingual Semantic Search</strong><br>
            Queries expand cross-lingually: "God" automatically also searches "Aondo", "Ter", "Tor" (the Tiv equivalents from the archive dictionary). Knowledge graph results are boosted.
        </div>
        <div>
            <strong>&#128196; File Text Extraction</strong><br>
            Uploaded PDFs are extracted with <em>pdftotext</em>. Scanned images and documents go through <em>Tesseract OCR</em>. Word .docx files are unzipped and parsed. All extracted text becomes searchable.
        </div>
        <div>
            <strong>&#128101; Knowledge Graph</strong><br>
            Related records are automatically linked (proverbs ↔ words they contain, festivals ↔ foods, plants ↔ foods they appear in). Graph links boost search ranking.
        </div>
        <div>
            <strong>&#128218; Auto-Indexing on Save</strong><br>
            Every admin create, edit, or approval immediately updates the search index. No manual reindex needed for day-to-day content updates.
        </div>
        <div>
            <strong>&#127881; Self-Improving Translation</strong><br>
            High-confidence approved translations are recorded as n-gram statistics. The Bible bilingual corpus auto-resolves unknown Tiv tokens. Both grow with every translation.
        </div>
        <div>
            <strong>&#127760; No External APIs</strong><br>
            Assistant, translation engine, semantic search, file extraction — all self-contained. No Anthropic, Hugging Face, OpenAI, or other external service required.
        </div>
    </div>
</div>

<script>
function triggerReindex() {
    const btn = document.getElementById('reindexBtn');
    const status = document.getElementById('reindexStatus');
    btn.disabled = true;
    btn.textContent = '⏳ Indexing…';
    status.textContent = 'This may take 30–60 seconds for large archives…';

    fetch('<?= url('admin/intelligence/reindex') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.style.color = '#16a34a';
            status.textContent = '✓ ' + data.message;
            setTimeout(() => location.reload(), 2000);
        } else {
            status.style.color = '#dc2626';
            status.textContent = '✗ ' + (data.message || 'Reindex failed');
        }
        btn.disabled = false;
        btn.textContent = '🔄 Rebuild Index Now';
    })
    .catch(() => {
        status.style.color = '#dc2626';
        status.textContent = 'Network error. Try again.';
        btn.disabled = false;
        btn.textContent = '🔄 Rebuild Index Now';
    });
}
</script>
