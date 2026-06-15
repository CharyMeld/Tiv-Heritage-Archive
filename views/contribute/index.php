<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#10133; Contribute</span>
        <h1 class="page-banner-title">Share Your Knowledge</h1>
        <p class="page-banner-sub">Help preserve Tiv cultural heritage by submitting names, proverbs, plants, festivals, foods and words</p>
    </div>
</div>

<section class="contribute-section" style="padding: 1.5rem 0 3rem;">
    <div class="container">
        <?php $errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']); ?>

        <form action="<?= url('contribute') ?>" method="POST" class="contribute-form contribute-form-card" data-validate enctype="multipart/form-data">
            <?= csrf_field() ?>

            <?php if (!is_logged_in()): ?>
            <div class="form-group">
                <label for="contributor_name" class="form-label">Your Name (optional)</label>
                <input type="text" id="contributor_name" name="contributor_name" class="form-input"
                       value="<?= old('contributor_name') ?>" placeholder="Anonymous" autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email" class="form-label required">Your Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="<?= old('email') ?>" placeholder="your@email.com" required autocomplete="email">
                <?php if (isset($errors['email'])): ?>
                    <div class="form-error"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="category" class="form-label required">Select Category</label>
                <select id="category" name="category" class="form-select" required onchange="showCategoryFields()">
                    <option value="">Choose category...</option>
                    <option value="name" <?= old('category') === 'name' ? 'selected' : '' ?>>Name</option>
                    <option value="proverb" <?= old('category') === 'proverb' ? 'selected' : '' ?>>Proverb</option>
                    <option value="plant" <?= old('category') === 'plant' ? 'selected' : '' ?>>Plant</option>
                    <option value="festival" <?= old('category') === 'festival' ? 'selected' : '' ?>>Festival</option>
                    <option value="food" <?= old('category') === 'food' ? 'selected' : '' ?>>Food</option>
                    <option value="word" <?= old('category') === 'word' ? 'selected' : '' ?>>Word</option>
                    <option value="animal" <?= old('category') === 'animal' ? 'selected' : '' ?>>Animal</option>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <div class="form-error"><?= e($errors['category']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="tiv_term" class="form-label required">Tiv Name/Word</label>
                <input type="text" id="tiv_term" name="tiv_term" class="form-input"
                       value="<?= old('tiv_term') ?>" placeholder="Enter Tiv term" required>
                <?php if (isset($errors['tiv_term'])): ?>
                    <div class="form-error"><?= e($errors['tiv_term']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="english_meaning" class="form-label required">English Meaning</label>
                <input type="text" id="english_meaning" name="english_meaning" class="form-input"
                       value="<?= old('english_meaning') ?>" placeholder="English translation" required>
                <?php if (isset($errors['english_meaning'])): ?>
                    <div class="form-error"><?= e($errors['english_meaning']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-textarea"
                          placeholder="Additional details..."><?= old('description') ?></textarea>
            </div>

            <div class="form-group">
                <label for="source" class="form-label">Source (Book, Region)</label>
                <input type="text" id="source" name="source" class="form-input"
                       value="<?= old('source') ?>" placeholder="Where did you learn this?">
            </div>

            <!-- Pronunciation Recording Button -->
            <div class="form-group">
                <label class="form-label">Record Pronunciation</label>
                <div class="recording-controls">
                    <button type="button" class="record-btn" id="recordBtn" onclick="toggleRecording()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" x2="12" y1="19" y2="22"/>
                        </svg>
                        <span id="recordText">Record Pronunciation</span>
                    </button>
                    <div id="audioPreview" class="audio-preview" style="display: none;">
                        <button type="button" class="play-btn" id="playBtn" onclick="playPreview()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="playIcon">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </button>
                        <span class="audio-status">Recording saved</span>
                        <button type="button" class="delete-btn" onclick="deleteRecording()" title="Delete recording">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="audio_data" id="audioData">
                <audio id="previewAudio" style="display: none;"></audio>
                <p class="form-hint">Optional: Record how the word is pronounced</p>
            </div>

            <!-- Category-specific fields -->
            <div id="name-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="gender" class="form-label">Gender</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="unisex">Unisex</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>

            <div id="proverb-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="deeper_meaning" class="form-label">Deeper Meaning</label>
                    <textarea id="deeper_meaning" name="deeper_meaning" class="form-textarea"
                              placeholder="Explain the wisdom behind this proverb..."></textarea>
                </div>
            </div>

            <div id="plant-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="medicinal_uses" class="form-label">Medicinal Uses</label>
                    <textarea id="medicinal_uses" name="medicinal_uses" class="form-textarea"
                              placeholder="Describe medicinal uses..."></textarea>
                </div>
                <div class="form-group">
                    <label for="plant_image" class="form-label">Plant Image</label>
                    <input type="file" id="plant_image" name="image" class="form-input" accept="image/*">
                    <p class="form-hint">Optional: Upload a photo of the plant (JPG, PNG, GIF, WebP — max 5MB)</p>
                </div>
            </div>

            <div id="festival-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="timing" class="form-label">When is it Celebrated?</label>
                    <input type="text" id="timing" name="timing" class="form-input"
                           placeholder="e.g., During harvest season">
                </div>
                <div class="form-group">
                    <label for="festival_image" class="form-label">Festival Image</label>
                    <input type="file" id="festival_image" name="image" class="form-input" accept="image/*">
                    <p class="form-hint">Optional: Upload a photo of the festival (JPG, PNG, GIF, WebP — max 5MB)</p>
                </div>
            </div>

            <div id="food-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="ingredients" class="form-label">Ingredients</label>
                    <textarea id="ingredients" name="ingredients" class="form-textarea"
                              placeholder="List the ingredients..."></textarea>
                </div>
                <div class="form-group">
                    <label for="food_image" class="form-label">Food Image</label>
                    <input type="file" id="food_image" name="image" class="form-input" accept="image/*">
                    <p class="form-hint">Optional: Upload a photo of the food (JPG, PNG, GIF, WebP — max 5MB)</p>
                </div>
            </div>

            <div id="animal-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="animal_type" class="form-label">Animal Type</label>
                    <select id="animal_type" name="animal_type" class="form-select">
                        <option value="wild">Wild</option>
                        <option value="domestic">Domestic</option>
                        <option value="pet">Pet</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cultural_use" class="form-label">Cultural Significance</label>
                    <textarea id="cultural_use" name="cultural_use" class="form-textarea"
                              placeholder="Describe its role in Tiv culture..."></textarea>
                </div>
                <div class="form-group">
                    <label for="animal_image" class="form-label">Animal Image</label>
                    <input type="file" id="animal_image" name="image" class="form-input" accept="image/*">
                    <p class="form-hint">Optional: Upload a photo of the animal (JPG, PNG, GIF, WebP — max 5MB)</p>
                </div>
            </div>

            <div id="word-fields" class="category-fields" style="display: none;">
                <div class="form-group">
                    <label for="part_of_speech" class="form-label">Part of Speech</label>
                    <select id="part_of_speech" name="part_of_speech" class="form-select">
                        <option value="noun">Noun</option>
                        <option value="verb">Verb</option>
                        <option value="adjective">Adjective</option>
                        <option value="phrase">Phrase</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Submit</button>

            <div class="submit-notice">
                All submissions are reviewed & approved by Admin
            </div>
        </form>
    </div>
</section>

<script>
function showCategoryFields() {
    document.querySelectorAll('.category-fields').forEach(el => {
        el.style.display = 'none';
    });
    const category = document.getElementById('category').value;
    const fieldsEl = document.getElementById(category + '-fields');
    if (fieldsEl) {
        fieldsEl.style.display = 'block';
    }
}

let isRecording = false;
let mediaRecorder = null;
let audioChunks = [];
let audioBlob = null;

function toggleRecording() {
    const btn = document.getElementById('recordBtn');
    const text = document.getElementById('recordText');
    const preview = document.getElementById('audioPreview');

    if (!isRecording) {
        // Start recording
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];

                mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
                mediaRecorder.onstop = () => {
                    audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        document.getElementById('audioData').value = reader.result;
                        // Show preview
                        preview.style.display = 'flex';
                        document.getElementById('previewAudio').src = reader.result;
                    };
                    reader.readAsDataURL(audioBlob);
                };

                mediaRecorder.start();
                isRecording = true;
                btn.classList.add('recording');
                text.textContent = 'Stop Recording';
                preview.style.display = 'none';
            })
            .catch(err => {
                alert('Could not access microphone. Please allow microphone access.');
            });
    } else {
        // Stop recording
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
        isRecording = false;
        btn.classList.remove('recording');
        text.textContent = 'Re-record';
    }
}

function playPreview() {
    const audio = document.getElementById('previewAudio');
    const playIcon = document.getElementById('playIcon');
    const playBtn = document.getElementById('playBtn');

    if (!audio.paused) {
        audio.pause();
        audio.currentTime = 0;
        playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
        return;
    }

    audio.play().then(() => {
        playIcon.innerHTML = '<rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>';
    }).catch(err => {
        console.error('Playback failed:', err);
    });

    audio.onended = () => {
        playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
    };
}

function deleteRecording() {
    document.getElementById('audioData').value = '';
    document.getElementById('audioPreview').style.display = 'none';
    document.getElementById('recordText').textContent = 'Record Pronunciation';
    document.getElementById('previewAudio').src = '';
    audioBlob = null;
}

document.addEventListener('DOMContentLoaded', showCategoryFields);
</script>
