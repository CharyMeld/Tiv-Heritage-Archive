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

                </div><!-- /phonoSection -->

                <!-- ══ PRONUNCIATION AUDIO — shared for all types ══════════ -->
                <div class="form-group" style="margin-bottom:1.4rem;">
                    <label class="form-label" style="color:#4a7c59;">
                        &#127911; Pronunciation Audio
                        <span style="font-weight:400;color:#9a8a7a;font-size:.78rem;">— record how this letter or sound is spoken</span>
                    </label>

                    <?php if (!empty($entry['audio_file'])): ?>
                    <div style="margin-bottom:.75rem;padding:.75rem 1rem;background:#edf7f0;border-radius:8px;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                        <span style="font-size:.8rem;color:#4a7c59;font-weight:600;">&#10003; Current recording:</span>
                        <audio controls style="height:32px;">
                            <source src="<?= UPLOADS_URL . '/' . htmlspecialchars($entry['audio_file']) ?>">
                        </audio>
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;cursor:pointer;color:#c0392b;">
                            <input type="checkbox" name="remove_audio" value="1"> Remove
                        </label>
                    </div>
                    <?php endif; ?>

                    <div class="recording-controls">
                        <button type="button" class="record-btn" id="recordBtn" onclick="toggleAlphaRecording()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                <line x1="12" x2="12" y1="19" y2="22"/>
                            </svg>
                            <span id="recordText"><?= !empty($entry['audio_file']) ? 'Record New' : 'Record Pronunciation' ?></span>
                        </button>
                        <div id="audioPreview" class="audio-preview" style="display:none;">
                            <button type="button" class="play-btn" id="playBtn" onclick="playAlphaPreview()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="playIcon">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </button>
                            <span class="audio-status">Recording saved — ready to submit</span>
                            <button type="button" class="delete-btn" onclick="deleteAlphaRecording()" title="Delete recording">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="audio_data" id="audioData">
                    <audio id="previewAudio" style="display:none;"></audio>
                    <p class="form-hint">Click the mic to record. Click again to stop. Preview before saving.</p>
                </div>

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
    var plain   = document.getElementById('plainSection');
    var phono   = document.getElementById('phonoSection');
    var pLetter = document.getElementById('phonoLetter');

    if (type === 'plain') {
        plain.style.display = '';
        phono.style.display = 'none';
        pLetter.required    = false;
    } else {
        plain.style.display = 'none';
        phono.style.display = '';
        pLetter.required    = true;
    }
}
switchType(document.getElementById('entryType').value);

/* ── In-browser recorder (same method as Tiv names / words) ── */
var _alphaRecorder  = null;
var _alphaChunks    = [];
var _alphaBlob      = null;
var _alphaRecording = false;

function toggleAlphaRecording() {
    var btn     = document.getElementById('recordBtn');
    var text    = document.getElementById('recordText');
    var preview = document.getElementById('audioPreview');

    if (!_alphaRecording) {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(function(stream) {
                _alphaRecorder = new MediaRecorder(stream);
                _alphaChunks   = [];

                _alphaRecorder.ondataavailable = function(e) { _alphaChunks.push(e.data); };
                _alphaRecorder.onstop = function() {
                    _alphaBlob = new Blob(_alphaChunks, { type: 'audio/webm' });
                    var reader = new FileReader();
                    reader.onloadend = function() {
                        document.getElementById('audioData').value  = reader.result;
                        document.getElementById('previewAudio').src = reader.result;
                        preview.style.display = 'flex';
                    };
                    reader.readAsDataURL(_alphaBlob);
                };

                _alphaRecorder.start();
                _alphaRecording = true;
                btn.classList.add('recording');
                text.textContent = 'Stop Recording';
                preview.style.display = 'none';
            })
            .catch(function() {
                alert('Could not access microphone. Please allow microphone access in your browser.');
            });
    } else {
        _alphaRecorder.stop();
        _alphaRecorder.stream.getTracks().forEach(function(t){ t.stop(); });
        _alphaRecording = false;
        btn.classList.remove('recording');
        text.textContent = 'Re-record';
    }
}

function playAlphaPreview() {
    var audio    = document.getElementById('previewAudio');
    var playIcon = document.getElementById('playIcon');
    if (!audio.paused) {
        audio.pause(); audio.currentTime = 0;
        playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
        return;
    }
    audio.play().then(function() {
        playIcon.innerHTML = '<rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>';
    });
    audio.onended = function() {
        playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
    };
}

function deleteAlphaRecording() {
    document.getElementById('audioData').value  = '';
    document.getElementById('previewAudio').src = '';
    document.getElementById('audioPreview').style.display = 'none';
    document.getElementById('recordText').textContent = 'Record Pronunciation';
    _alphaBlob = null;
}
</script>
