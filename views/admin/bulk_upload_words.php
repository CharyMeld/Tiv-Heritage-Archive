<?php
/**
 * Bulk Upload Dictionary Words — Admin View
 */
$result = $result ?? null;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Bulk Upload Dictionary Words</h1>
        <p class="admin-page-sub">Import multiple words at once from an Excel (.xlsx) or CSV file</p>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="<?= url('admin/content/words') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&larr; Back to Dictionary</a>
    </div>
</div>

<?php if ($result): ?>
<!-- ── Results Panel ──────────────────────────────────────── -->
<div class="admin-card" style="padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="margin:0 0 1rem;font-size:1.1rem;">Upload Results</h2>

    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        <div style="flex:1;min-width:130px;background:var(--color-success-bg,#f0fdf4);border:1px solid var(--color-success-border,#bbf7d0);border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#16a34a;"><?= $result['inserted'] ?></div>
            <div style="font-size:0.82rem;color:#15803d;margin-top:2px;">Words Added</div>
        </div>
        <div style="flex:1;min-width:130px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#d97706;"><?= $result['skipped'] ?></div>
            <div style="font-size:0.82rem;color:#b45309;margin-top:2px;">Duplicates Skipped</div>
        </div>
        <div style="flex:1;min-width:130px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:#dc2626;"><?= $result['errors'] ?></div>
            <div style="font-size:0.82rem;color:#b91c1c;margin-top:2px;">Errors</div>
        </div>
    </div>

    <?php if (!empty($result['details'])): ?>
    <div style="overflow-x:auto;">
        <table class="data-table" style="font-size:0.83rem;">
            <thead>
                <tr>
                    <th style="width:50px;">Row</th>
                    <th>Tiv Word</th>
                    <th>English Word</th>
                    <th>Status</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['details'] as $d): ?>
                <tr>
                    <td style="color:var(--color-text-muted);"><?= e($d['row']) ?></td>
                    <td><strong><?= e($d['tiv']) ?></strong></td>
                    <td><?= e($d['eng']) ?></td>
                    <td>
                        <?php if ($d['status'] === 'inserted'): ?>
                            <span style="color:#16a34a;font-weight:600;">&#10003; Added</span>
                        <?php elseif ($d['status'] === 'duplicate'): ?>
                            <span style="color:#d97706;font-weight:600;">&#8594; Duplicate</span>
                        <?php else: ?>
                            <span style="color:#dc2626;font-weight:600;">&#10005; Error</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--color-text-muted);"><?= e($d['note']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div style="margin-top:1.25rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
        <a href="<?= url('admin/content/words') ?>" class="btn btn-primary btn-sm">View Dictionary</a>
        <a href="<?= url('admin/content/words/bulk-upload') ?>" class="btn btn-secondary btn-sm">Upload Another File</a>
    </div>
</div>
<?php endif; ?>

<!-- ── Upload Form ────────────────────────────────────────── -->
<?php if (!$result): ?>
<div style="max-width:720px;">

    <!-- Format guide -->
    <div class="admin-card" style="padding:1.5rem;margin-bottom:1.25rem;background:#f8fafc;border:1px solid #e2e8f0;">
        <h3 style="margin:0 0 0.75rem;font-size:0.95rem;font-weight:600;">Required File Format</h3>
        <p style="margin:0 0 0.75rem;font-size:0.85rem;color:var(--color-text-muted);">
            The first row must be a header row. Columns must appear in this order:
        </p>
        <div style="overflow-x:auto;margin-bottom:0.75rem;">
            <table style="border-collapse:collapse;font-size:0.8rem;width:100%;">
                <thead>
                    <tr style="background:#e2e8f0;">
                        <th style="padding:6px 10px;text-align:left;border:1px solid #cbd5e1;">Tiv_Word</th>
                        <th style="padding:6px 10px;text-align:left;border:1px solid #cbd5e1;">English_Word</th>
                        <th style="padding:6px 10px;text-align:left;border:1px solid #cbd5e1;">Part_Of_Speech</th>
                        <th style="padding:6px 10px;text-align:left;border:1px solid #cbd5e1;">Meaning</th>
                        <th style="padding:6px 10px;text-align:left;border:1px solid #cbd5e1;">Example_Tiv</th>
                        <th style="padding:6px 10px;text-align:left;border:1px solid #cbd5e1;">Example_English</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">abainyam</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">trap-jaw ant</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">noun</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">predator ant that produces smell when crushed</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">Abainyam kpe; ihuma ngee</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">The trap-jaw ant died; there's a strong smell</td>
                    </tr>
                    <tr style="background:#f8fafc;">
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">abaver</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">news</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">noun</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">information coming from different directions</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">M ungwa abaver a doon</td>
                        <td style="padding:6px 10px;border:1px solid #cbd5e1;">I heard the good news</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <ul style="margin:0;padding-left:1.25rem;font-size:0.83rem;color:var(--color-text-muted);line-height:1.7;">
            <li><strong>Tiv_Word</strong> and <strong>English_Word</strong> are required. All other columns are optional.</li>
            <li><strong>Part_Of_Speech</strong> must be one of: noun, verb, adjective, adverb, pronoun, preposition, conjunction, interjection, phrase. Defaults to <em>noun</em> if blank or unrecognised.</li>
            <li><strong>Meaning</strong> maps to the alternate/detailed meaning field.</li>
            <li>Rows where the same <em>Tiv_Word + English_Word</em> pair already exists in the dictionary are automatically skipped — no duplicates are created.</li>
            <li>Completely blank rows are silently ignored.</li>
        </ul>
    </div>

    <!-- Download template link -->
    <div style="margin-bottom:1.25rem;">
        <button type="button" onclick="downloadCsvTemplate()" class="btn btn-secondary btn-sm" style="border-radius:50px;">
            Download CSV Template
        </button>
    </div>

    <!-- Upload form -->
    <form action="<?= url('admin/content/words/bulk-upload') ?>" method="POST"
          enctype="multipart/form-data" class="admin-card" style="padding:1.75rem;"
          id="bulkUploadForm">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="bulk_file" class="form-label required">Select File</label>
            <div id="dropZone" style="
                border:2px dashed #cbd5e1;border-radius:10px;padding:2.5rem 1.5rem;
                text-align:center;cursor:pointer;transition:border-color 0.2s,background 0.2s;
                background:#fafafa;
            " onclick="document.getElementById('bulk_file').click()">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                     fill="none" stroke="#94a3b8" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:0.75rem;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <p id="dropText" style="margin:0;font-size:0.9rem;color:#64748b;">
                    Drag &amp; drop your file here, or <strong>click to browse</strong>
                </p>
                <p style="margin:0.4rem 0 0;font-size:0.78rem;color:#94a3b8;">
                    Accepted: .xlsx (Excel) and .csv (comma or tab separated)
                </p>
            </div>
            <input type="file" id="bulk_file" name="bulk_file"
                   accept=".xlsx,.csv" style="display:none;"
                   onchange="onFileSelected(this)">
        </div>

        <!-- File preview (populated by JS after selection) -->
        <div id="previewSection" style="display:none;margin-top:1rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;
                        background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="#16a34a" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <div>
                    <div id="fileName" style="font-weight:600;font-size:0.9rem;"></div>
                    <div id="fileSize" style="font-size:0.78rem;color:#64748b;"></div>
                </div>
                <button type="button" onclick="clearFile()"
                        style="margin-left:auto;background:none;border:none;cursor:pointer;color:#64748b;"
                        title="Remove file">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- CSV-only inline preview table -->
            <div id="csvPreviewWrap" style="display:none;margin-top:0.75rem;overflow-x:auto;max-height:220px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <table id="csvPreviewTable" class="data-table" style="font-size:0.78rem;margin:0;"></table>
            </div>
        </div>

        <div style="margin-top:1.5rem;display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                Upload &amp; Import
            </button>
            <a href="<?= url('admin/content/words') ?>" class="btn btn-secondary">Cancel</a>
            <span id="uploadingMsg" style="display:none;font-size:0.85rem;color:#64748b;">
                Processing, please wait…
            </span>
        </div>
    </form>

</div>
<?php endif; ?>

<script>
// ── Drag & drop + file selection ──────────────────────────────
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('bulk_file');

dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.style.borderColor = '#6366f1';
    dropZone.style.background  = '#eef2ff';
});
dropZone.addEventListener('dragleave', () => {
    dropZone.style.borderColor = '#cbd5e1';
    dropZone.style.background  = '#fafafa';
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.borderColor = '#cbd5e1';
    dropZone.style.background  = '#fafafa';
    const files = e.dataTransfer.files;
    if (files.length) {
        fileInput.files = files;
        onFileSelected(fileInput);
    }
});

function onFileSelected(input) {
    const file = input.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'xlsx' && ext !== 'csv') {
        alert('Only .xlsx and .csv files are accepted.');
        clearFile();
        return;
    }

    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent  = formatBytes(file.size);
    document.getElementById('previewSection').style.display = 'block';
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('dropText').textContent = 'File selected — you can change it by dropping another file here';

    // For CSV, show an inline preview of first 6 rows
    if (ext === 'csv') {
        const reader = new FileReader();
        reader.onload = e => renderCsvPreview(e.target.result);
        reader.readAsText(file);
    } else {
        document.getElementById('csvPreviewWrap').style.display = 'none';
    }
}

function renderCsvPreview(text) {
    const lines = text.trim().split('\n').slice(0, 7);
    if (!lines.length) return;

    // Auto-detect delimiter
    const delim = (lines[0].split('\t').length > lines[0].split(',').length) ? '\t' : ',';
    const wrap  = document.getElementById('csvPreviewWrap');
    const table = document.getElementById('csvPreviewTable');
    table.innerHTML = '';

    lines.forEach((line, i) => {
        const cells = line.split(delim).map(c => c.replace(/^"|"$/g, '').trim());
        const tr    = document.createElement('tr');
        cells.forEach(val => {
            const td = document.createElement(i === 0 ? 'th' : 'td');
            td.textContent = val;
            tr.appendChild(td);
        });
        table.appendChild(tr);
    });

    wrap.style.display = 'block';
}

function clearFile() {
    fileInput.value = '';
    document.getElementById('previewSection').style.display    = 'none';
    document.getElementById('csvPreviewWrap').style.display    = 'none';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('dropText').innerHTML =
        'Drag &amp; drop your file here, or <strong>click to browse</strong>';
    dropZone.style.borderColor = '#cbd5e1';
    dropZone.style.background  = '#fafafa';
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

// Show "processing" message on submit
document.getElementById('bulkUploadForm')?.addEventListener('submit', () => {
    document.getElementById('submitBtn').disabled   = true;
    document.getElementById('uploadingMsg').style.display = 'inline';
});

// ── CSV Template download ─────────────────────────────────────
function downloadCsvTemplate() {
    const header = 'Tiv_Word\tEnglish_Word\tPart_Of_Speech\tMeaning\tExample_Tiv\tExample_English\n';
    const rows = [
        'abainyam\ttrap-jaw ant\tnoun\tpredator ant that produces smell when crushed\tAbainyam kpe; ihuma ngee\tThe trap-jaw ant died; there\'s a strong smell',
        'abaver\tnews\tnoun\tinformation coming from different directions\tM ungwa abaver a doon\tI heard the good news',
        'abeda\twrapper\tnoun\tlarge cloth worn around the waist\tZer abeda\tWear the wrapper',
    ].join('\n');
    const blob = new Blob([header + rows], { type: 'text/tab-separated-values' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'tiv_words_template.csv';
    a.click();
    URL.revokeObjectURL(a.href);
}
</script>
