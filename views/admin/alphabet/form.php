<?php
$isEdit     = $entry !== null;
$formAction = $isEdit ? url('admin/alphabet/' . $entry['id'] . '/edit') : url('admin/alphabet/create');
$presetType = $_GET['type'] ?? ($entry['type'] ?? 'plain');

/* Split stored letter into cap + small for plain edit */
$tivParts = explode(' ', $entry['letter'] ?? '', 2);
$tivCap   = $tivParts[0] ?? '';
$tivSmall = $tivParts[1] ?? '';

$engParts  = explode(' ', $entry['english_letter'] ?? '', 2);
$engCap    = $engParts[0] ?? '';
$engSmall  = $engParts[1] ?? '';
?>
<div class="admin-wrapper">
<div class="admin-content" style="max-width:780px;">

    <div style="margin-bottom:1.2rem;">
        <a href="<?= url('admin/alphabet') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; Back to Alphabet Manager</a>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= $isEdit ? 'Edit: ' . htmlspecialchars($entry['letter']) : 'Add Alphabet Entry' ?></h2>
            <span style="font-size:.78rem;color:#7a6a5a;">&#127279; Language › Alphabet</span>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data" id="alphaForm">
                <?= csrf_field() ?>

                <!-- TYPE -->
                <div class="form-group">
                    <label class="form-label">Type <span style="color:#c0392b;">*</span></label>
                    <select name="type" class="form-select" id="entryType" onchange="switchType(this.value)">
                        <?php foreach ([
                            'plain'     => 'Plain Letter (Tiv + English)',
                            'vowel'     => 'Vowel',
                            'consonant' => 'Consonant',
                            'digraph'   => 'Digraph',
                            'tonal'     => 'Tonal Mark',
                        ] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($entry['type'] ?? $presetType) === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ══ PLAIN LETTER FORM (simple) ══════════════════════════ -->
                <div id="plainSection">
                    <div style="background:#f7f4ee;border-radius:8px;padding:1.1rem 1.2rem;margin-bottom:1.2rem;">
                        <p style="margin:0 0 .9rem;font-size:.84rem;color:#5a4a3a;">
                            Enter the capital and small form of each letter separately.
                        </p>

                        <!-- Tiv Letter row -->
                        <div style="margin-bottom:.9rem;">
                            <label class="form-label" style="color:#4a7c59;margin-bottom:.45rem;display:block;">Tiv Letter</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                                <div>
                                    <label style="font-size:.78rem;color:#7a6a5a;display:block;margin-bottom:.25rem;">Capital (uppercase)</label>
                                    <input type="text" name="letter_cap" id="letter_cap" class="form-input"
                                           maxlength="10"
                                           value="<?= htmlspecialchars($tivCap) ?>"
                                           placeholder="e.g.  A  or  GB"
                                           style="font-size:1.3rem;font-weight:700;text-align:center;letter-spacing:.05em;">
                                </div>
                                <div>
                                    <label style="font-size:.78rem;color:#7a6a5a;display:block;margin-bottom:.25rem;">Small (lowercase)</label>
                                    <input type="text" name="letter_small" id="letter_small" class="form-input"
                                           maxlength="10"
                                           value="<?= htmlspecialchars($tivSmall) ?>"
                                           placeholder="e.g.  a  or  gb"
                                           style="font-size:1.3rem;font-weight:700;text-align:center;letter-spacing:.05em;">
                                </div>
                            </div>
                        </div>

                        <!-- English Letter row -->
                        <div>
                            <label class="form-label" style="color:#5C3A21;margin-bottom:.45rem;display:block;">English Equivalent Letter</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                                <div>
                                    <label style="font-size:.78rem;color:#7a6a5a;display:block;margin-bottom:.25rem;">Capital (uppercase)</label>
                                    <input type="text" name="english_cap" id="english_cap" class="form-input"
                                           maxlength="10"
                                           value="<?= htmlspecialchars($engCap) ?>"
                                           placeholder="e.g.  A"
                                           style="font-size:1.3rem;font-weight:700;text-align:center;letter-spacing:.05em;">
                                </div>
                                <div>
                                    <label style="font-size:.78rem;color:#7a6a5a;display:block;margin-bottom:.25rem;">Small (lowercase)</label>
                                    <input type="text" name="english_small" id="english_small" class="form-input"
                                           maxlength="10"
                                           value="<?= htmlspecialchars($engSmall) ?>"
                                           placeholder="e.g.  a"
                                           style="font-size:1.3rem;font-weight:700;text-align:center;letter-spacing:.05em;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ PHONOLOGICAL FORM (vowel / consonant / digraph / tonal) ═ -->
                <div id="phonoSection">

                    <!-- LETTER + IPA -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Tiv Letter <span style="color:#c0392b;">*</span> <span style="font-weight:400;color:#9a8a7a;font-size:.78rem;">uppercase &amp; lowercase</span></label>
                            <input type="text" name="letter" id="phonoLetter" class="form-input"
                                   value="<?= htmlspecialchars($entry['letter'] ?? '') ?>"
                                   placeholder="e.g.  A a  or  GB gb">
                            <p class="form-hint">Both forms separated by a space: <em>A a</em></p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IPA Symbol <span style="font-weight:400;color:#9a8a7a;font-size:.78rem;">International Phonetic Alphabet</span></label>
                            <input type="text" name="ipa" class="form-input"
                                   value="<?= htmlspecialchars($entry['ipa'] ?? '') ?>"
                                   placeholder="e.g.  /a/  or  /ɡ͡b/">
                        </div>
                    </div>

                    <!-- SOUND DESCRIPTION -->
                    <div class="form-group">
                        <label class="form-label">Sounds Like <span style="font-weight:400;color:#9a8a7a;font-size:.78rem;">English comparison for learners</span></label>
                        <input type="text" name="sound_desc" class="form-input"
                               value="<?= htmlspecialchars($entry['sound_desc'] ?? '') ?>"
                               placeholder='e.g.  like "a" in father'>
                    </div>

                    <!-- TIV EXAMPLE + ENGLISH MEANING -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" style="color:#4a7c59;">&#127981; Tiv Example Word</label>
                            <input type="text" name="tiv_example" class="form-input"
                                   value="<?= htmlspecialchars($entry['tiv_example'] ?? '') ?>"
                                   placeholder="e.g.  ata  or  gba">
                            <p class="form-hint">A Tiv word that features this sound.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="color:#5C3A21;">&#127760; English Meaning</label>
                            <input type="text" name="english_meaning" class="form-input"
                                   value="<?= htmlspecialchars($entry['english_meaning'] ?? '') ?>"
                                   placeholder="e.g.  three  or  arm / branch">
                            <p class="form-hint">English translation of the example word.</p>
                        </div>
                    </div>

                    <!-- AUDIO UPLOAD -->
                    <div class="admin-card" style="margin-bottom:1.2rem;">
                        <div class="admin-card-header" style="padding:.7rem 1rem;">
                            <h3 class="admin-card-title" style="font-size:.95rem;">&#127911; Pronunciation Audio</h3>
                            <?php if (!empty($entry['audio_file'])): ?>
                                <span style="font-size:.78rem;color:#4a7c59;font-weight:600;">&#10003; Audio uploaded</span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-card-body" style="padding:1rem;">
                            <?php if (!empty($entry['audio_file'])): ?>
                                <div style="margin-bottom:.9rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                                    <audio controls style="height:36px;">
                                        <source src="<?= UPLOADS_URL . '/' . htmlspecialchars($entry['audio_file']) ?>">
                                    </audio>
                                    <label style="display:flex;align-items:center;gap:.4rem;font-size:.83rem;cursor:pointer;color:#c0392b;">
                                        <input type="checkbox" name="remove_audio" value="1">
                                        Remove audio
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="audio_file" class="form-input" accept="audio/mp3,audio/mpeg,audio/ogg,audio/wav,audio/webm,.mp3,.ogg,.wav,.m4a,.webm">
                            <p class="form-hint">MP3, OGG, WAV, M4A or WebM — max 5 MB.<?= !empty($entry['audio_file']) ? ' Upload a new file to replace the current one.' : '' ?></p>
                        </div>
                    </div>

                </div><!-- /phonoSection -->

                <!-- SORT ORDER (shared) -->
                <div class="form-group" style="max-width:160px;">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" min="0" max="999"
                           value="<?= (int)($entry['sort_order'] ?? 0) ?>">
                    <p class="form-hint">Lower = first. 0 = append at end.</p>
                </div>

                <div style="display:flex;gap:.8rem;margin-top:.5rem;">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Add Entry' ?></button>
                    <a href="<?= url('admin/alphabet') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
function switchType(type) {
    var plain  = document.getElementById('plainSection');
    var phono  = document.getElementById('phonoSection');
    var pLetter = document.getElementById('phonoLetter');

    if (type === 'plain') {
        plain.style.display  = '';
        phono.style.display  = 'none';
        pLetter.required     = false;
    } else {
        plain.style.display  = 'none';
        phono.style.display  = '';
        pLetter.required     = true;
    }
}
switchType(document.getElementById('entryType').value);
</script>
