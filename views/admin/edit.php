<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Edit <?= rtrim(ucfirst(e($category)), 's') ?></h1>
        <p class="admin-page-sub">Updating entry ID #<?= e($item['id']) ?></p>
    </div>
    <a href="<?= url('admin/content/' . $category) ?>" class="btn btn-secondary">&larr; Back</a>
</div>

<div style="max-width: 800px;">
    <form action="<?= url('admin/content/' . $category . '/' . $item['id'] . '/edit') ?>" method="POST" class="admin-card" style="padding:1.75rem;" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if ($category === 'names'): ?>
            <div class="form-group">
                <label for="tiv_name" class="form-label required">Tiv Name</label>
                <input type="text" id="tiv_name" name="tiv_name" class="form-input" value="<?= e($item['tiv_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="english_meaning" class="form-label required">English Meaning</label>
                <input type="text" id="english_meaning" name="english_meaning" class="form-input" value="<?= e($item['english_meaning']) ?>" required>
            </div>
            <div class="form-group">
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender" class="form-select">
                    <option value="unisex" <?= $item['gender'] === 'unisex' ? 'selected' : '' ?>>Unisex</option>
                    <option value="male" <?= $item['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $item['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-textarea"><?= e($item['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="pronunciation" class="form-label">Pronunciation</label>
                <input type="text" id="pronunciation" name="pronunciation" class="form-input" value="<?= e($item['pronunciation']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Record Pronunciation</label>
                <?php if (!empty($item['audio_file'])): ?>
                    <div id="existingAudio" style="margin-bottom: 10px; padding: 10px; background: var(--bg-secondary); border-radius: 4px;">
                        <p style="margin: 0 0 8px 0; font-size: 0.9em;">Current audio:</p>
                        <audio controls style="width: 100%; max-width: 300px;">
                            <source src="<?= e(AUDIO_UPLOADS_URL . '/' . $item['audio_file']) ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                <?php endif; ?>
                <div class="recording-controls">
                    <button type="button" class="record-btn" id="recordBtn" onclick="toggleRecording()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" x2="12" y1="19" y2="22"/>
                        </svg>
                        <span id="recordText"><?= !empty($item['audio_file']) ? 'Record New' : 'Record Pronunciation' ?></span>
                    </button>
                    <div id="audioPreview" class="audio-preview" style="display: none;">
                        <button type="button" class="play-btn" id="playBtn" onclick="playPreview()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="playIcon">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </button>
                        <span class="audio-status">New recording saved</span>
                        <button type="button" class="delete-btn" onclick="deleteRecording()" title="Delete recording">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="audio_data" id="audioData">
                <audio id="previewAudio" style="display: none;"></audio>
                <p class="form-hint"><?= !empty($item['audio_file']) ? 'Record to replace current audio' : 'Optional: Record how the name is pronounced' ?></p>
            </div>
            <div class="form-group">
                <label for="origin_story" class="form-label">Origin Story</label>
                <textarea id="origin_story" name="origin_story" class="form-textarea"><?= e($item['origin_story']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured</span>
                </label>
            </div>

        <?php elseif ($category === 'proverbs'): ?>
            <div class="form-group">
                <label for="tiv_text" class="form-label required">Tiv Text</label>
                <textarea id="tiv_text" name="tiv_text" class="form-textarea" required><?= e($item['tiv_text']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="english_translation" class="form-label required">English Translation</label>
                <textarea id="english_translation" name="english_translation" class="form-textarea" required><?= e($item['english_translation']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="deeper_meaning" class="form-label">Deeper Meaning</label>
                <textarea id="deeper_meaning" name="deeper_meaning" class="form-textarea"><?= e($item['deeper_meaning']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="usage_context" class="form-label">Usage Context</label>
                <textarea id="usage_context" name="usage_context" class="form-textarea"><?= e($item['usage_context']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" id="category" name="category" class="form-input" value="<?= e($item['category']) ?>">
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured</span>
                </label>
            </div>

        <?php elseif ($category === 'plants'): ?>
            <div class="form-group">
                <label for="tiv_name" class="form-label required">Tiv Name</label>
                <input type="text" id="tiv_name" name="tiv_name" class="form-input" value="<?= e($item['tiv_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="english_name" class="form-label">English Name</label>
                <input type="text" id="english_name" name="english_name" class="form-input" value="<?= e($item['english_name']) ?>">
            </div>
            <div class="form-group">
                <label for="scientific_name" class="form-label">Scientific Name</label>
                <input type="text" id="scientific_name" name="scientific_name" class="form-input" value="<?= e($item['scientific_name']) ?>">
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-textarea"><?= e($item['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="medicinal_uses" class="form-label">Medicinal Uses</label>
                <textarea id="medicinal_uses" name="medicinal_uses" class="form-textarea"><?= e($item['medicinal_uses']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="food_uses" class="form-label">Food Uses</label>
                <textarea id="food_uses" name="food_uses" class="form-textarea"><?= e($item['food_uses']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="ritual_uses" class="form-label">Ritual Uses</label>
                <textarea id="ritual_uses" name="ritual_uses" class="form-textarea"><?= e($item['ritual_uses']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="cultivation" class="form-label">Cultivation</label>
                <textarea id="cultivation" name="cultivation" class="form-textarea"><?= e($item['cultivation'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="image" class="form-label">Plant Image</label>
                <?php if (!empty($item['image'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="Current plant image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                        <p style="margin-top: 5px; font-size: 0.85em; color: #666;">Current image: <?= e($item['image']) ?></p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-input" accept="image/*">
                <p class="form-hint"><?= !empty($item['image']) ? 'Upload a new image to replace the current one' : 'Upload an image of the plant (JPG, PNG, GIF, WebP - max 5MB)' ?></p>
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured</span>
                </label>
            </div>

        <?php elseif ($category === 'festivals'): ?>
            <div class="form-group">
                <label for="tiv_name" class="form-label required">Tiv Name</label>
                <input type="text" id="tiv_name" name="tiv_name" class="form-input" value="<?= e($item['tiv_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="english_name" class="form-label">English Name</label>
                <input type="text" id="english_name" name="english_name" class="form-input" value="<?= e($item['english_name']) ?>">
            </div>
            <div class="form-group">
                <label for="festival_type" class="form-label">Festival Type</label>
                <input type="text" id="festival_type" name="festival_type" class="form-input" value="<?= e($item['festival_type'] ?? '') ?>" placeholder="e.g. Food and Cultural Festival">
            </div>
            <div class="form-group">
                <label for="description" class="form-label required">Description</label>
                <textarea id="description" name="description" class="form-textarea" required><?= e($item['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="significance" class="form-label">Significance</label>
                <textarea id="significance" name="significance" class="form-textarea"><?= e($item['significance']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="timing" class="form-label">Timing</label>
                <input type="text" id="timing" name="timing" class="form-input" value="<?= e($item['timing']) ?>">
            </div>
            <div class="form-group">
                <label for="activities" class="form-label">Activities</label>
                <textarea id="activities" name="activities" class="form-textarea"><?= e($item['activities']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-input" value="<?= e($item['location']) ?>">
            </div>
            <div class="form-group">
                <label for="image" class="form-label">Featured Image</label>
                <?php if (!empty($item['image'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="Current festival image" style="max-width:200px;max-height:200px;border-radius:8px;border:1px solid #ddd;">
                        <p style="margin-top:5px;font-size:0.85em;color:#666;">Current image: <?= e($item['image']) ?></p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-input" accept="image/*">
                <p class="form-hint"><?= !empty($item['image']) ? 'Upload a new image to replace the current one' : 'Upload a featured image (JPG, PNG, GIF, WebP — max 5MB). You can also add photos via the Gallery section below.' ?></p>
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured</span>
                </label>
            </div>

        <?php elseif ($category === 'foods'): ?>
            <div class="form-group">
                <label for="tiv_name" class="form-label required">Tiv Name</label>
                <input type="text" id="tiv_name" name="tiv_name" class="form-input" value="<?= e($item['tiv_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="english_name" class="form-label">English Name</label>
                <input type="text" id="english_name" name="english_name" class="form-input" value="<?= e($item['english_name']) ?>">
            </div>
            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" id="category" name="category" class="form-input" value="<?= e($item['category'] ?? '') ?>" placeholder="e.g. Staple Food, Soup, Snack, Beverage">
            </div>
            <div class="form-group">
                <label for="description" class="form-label required">Description</label>
                <textarea id="description" name="description" class="form-textarea" required><?= e($item['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="ingredients" class="form-label">Ingredients</label>
                <textarea id="ingredients" name="ingredients" class="form-textarea"><?= e($item['ingredients']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="preparation_method" class="form-label">Preparation Method</label>
                <textarea id="preparation_method" name="preparation_method" class="form-textarea"><?= e($item['preparation_method']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="serving_suggestions" class="form-label">Serving Suggestions</label>
                <textarea id="serving_suggestions" name="serving_suggestions" class="form-textarea"><?= e($item['serving_suggestions']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="cultural_significance" class="form-label">Cultural Significance</label>
                <textarea id="cultural_significance" name="cultural_significance" class="form-textarea"><?= e($item['cultural_significance'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="image" class="form-label">Food Image</label>
                <?php if (!empty($item['image'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="Current food image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                        <p style="margin-top: 5px; font-size: 0.85em; color: #666;">Current image: <?= e($item['image']) ?></p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-input" accept="image/*">
                <p class="form-hint"><?= !empty($item['image']) ? 'Upload a new image to replace the current one' : 'Upload an image of the food (JPG, PNG, GIF, WebP - max 5MB)' ?></p>
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured</span>
                </label>
            </div>

        <?php elseif ($category === 'words'): ?>
            <div class="form-group">
                <label for="tiv_word" class="form-label required">Tiv Word</label>
                <input type="text" id="tiv_word" name="tiv_word" class="form-input" value="<?= e($item['tiv_word']) ?>" required>
            </div>
            <div class="form-group">
                <label for="english_meaning" class="form-label required">English Meaning</label>
                <input type="text" id="english_meaning" name="english_meaning" class="form-input" value="<?= e($item['english_meaning']) ?>" required>
            </div>
            <div class="form-group">
                <label for="alternate_meaning" class="form-label">Alternate Meaning</label>
                <input type="text" id="alternate_meaning" name="alternate_meaning" class="form-input" value="<?= e($item['alternate_meaning'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="part_of_speech" class="form-label">Part of Speech</label>
                <select id="part_of_speech" name="part_of_speech" class="form-select">
                    <?php foreach (['noun', 'verb', 'adjective', 'adverb', 'pronoun', 'preposition', 'conjunction', 'interjection', 'phrase'] as $pos): ?>
                        <option value="<?= $pos ?>" <?= $item['part_of_speech'] === $pos ? 'selected' : '' ?>><?= ucfirst($pos) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="category" class="form-label">Semantic Category</label>
                <input type="text" id="category" name="category" class="form-input" list="semanticDomains" value="<?= e($item['category'] ?? '') ?>" placeholder="e.g. religion, farming, family">
                <datalist id="semanticDomains">
                    <option value="religion"><option value="farming"><option value="family">
                    <option value="greeting"><option value="health"><option value="education">
                    <option value="food"><option value="travel">
                </datalist>
                <p class="form-hint">Used by the translation engine to bias word choice by topic.</p>
            </div>
            <div class="form-group">
                <label for="pronunciation" class="form-label">Pronunciation</label>
                <input type="text" id="pronunciation" name="pronunciation" class="form-input" value="<?= e($item['pronunciation']) ?>">
            </div>
            <div class="form-group">
                <label for="ipa" class="form-label">IPA Transcription</label>
                <input type="text" id="ipa" name="ipa" class="form-input" value="<?= e($item['ipa'] ?? '') ?>" placeholder="e.g. /a.tsɛ/">
            </div>
            <div class="form-group">
                <label for="tone" class="form-label">Tone</label>
                <input type="text" id="tone" name="tone" class="form-input" value="<?= e($item['tone'] ?? '') ?>" placeholder="e.g. high-low">
            </div>
            <div class="form-group">
                <label for="root_word_tiv" class="form-label">Root Word (Tiv spelling)</label>
                <input type="text" id="root_word_tiv" name="root_word_tiv" class="form-input" value="<?= e($rootWord['tiv_word'] ?? '') ?>">
                <p class="form-hint">If this word is derived from another word already in the dictionary, type its exact Tiv spelling here.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Record Pronunciation</label>
                <?php if (!empty($item['audio_file'])): ?>
                    <div id="existingAudio" style="margin-bottom: 10px; padding: 10px; background: var(--bg-secondary); border-radius: 4px;">
                        <p style="margin: 0 0 8px 0; font-size: 0.9em;">Current audio:</p>
                        <audio controls style="width: 100%; max-width: 300px;">
                            <source src="<?= e(AUDIO_UPLOADS_URL . '/' . $item['audio_file']) ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                <?php endif; ?>
                <div class="recording-controls">
                    <button type="button" class="record-btn" id="recordBtn" onclick="toggleRecording()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" x2="12" y1="19" y2="22"/>
                        </svg>
                        <span id="recordText"><?= !empty($item['audio_file']) ? 'Record New' : 'Record Pronunciation' ?></span>
                    </button>
                    <div id="audioPreview" class="audio-preview" style="display: none;">
                        <button type="button" class="play-btn" id="playBtn" onclick="playPreview()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="playIcon">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </button>
                        <span class="audio-status">New recording saved</span>
                        <button type="button" class="delete-btn" onclick="deleteRecording()" title="Delete recording">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="audio_data" id="audioData">
                <audio id="previewAudio" style="display: none;"></audio>
                <p class="form-hint"><?= !empty($item['audio_file']) ? 'Record to replace current audio' : 'Optional: Record how the word is pronounced' ?></p>
            </div>
            <div class="form-group">
                <label for="example_tiv" class="form-label">Example (Tiv)</label>
                <input type="text" id="example_tiv" name="example_tiv" class="form-input" value="<?= e($item['example_tiv']) ?>">
            </div>
            <div class="form-group">
                <label for="example_english" class="form-label">Example (English)</label>
                <input type="text" id="example_english" name="example_english" class="form-input" value="<?= e($item['example_english']) ?>">
            </div>
            <div class="form-group">
                <label for="literal_meaning" class="form-label">Literal Meaning</label>
                <textarea id="literal_meaning" name="literal_meaning" class="form-textarea"><?= e($item['literal_meaning'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="figurative_meaning" class="form-label">Figurative Meaning</label>
                <textarea id="figurative_meaning" name="figurative_meaning" class="form-textarea"><?= e($item['figurative_meaning'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="usage_notes" class="form-label">Usage Notes</label>
                <textarea id="usage_notes" name="usage_notes" class="form-textarea"><?= e($item['usage_notes'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="dialect_region" class="form-label">Dialect / Regional Notes</label>
                <input type="text" id="dialect_region" name="dialect_region" class="form-input" value="<?= e($item['dialect_region'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="frequency" class="form-label">Frequency</label>
                <select id="frequency" name="frequency" class="form-select">
                    <option value="">— Not set —</option>
                    <?php foreach (['very_common' => 'Very Common', 'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($item['frequency'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?>>
                    <span>Active</span>
                </label>
            </div>

        <?php elseif ($category === 'animals'): ?>
            <div class="form-group">
                <label for="tiv_name" class="form-label required">Tiv Name</label>
                <input type="text" id="tiv_name" name="tiv_name" class="form-input" value="<?= e($item['tiv_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="name" class="form-label">English Name</label>
                <input type="text" id="name" name="name" class="form-input" value="<?= e($item['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="animal_type" class="form-label">Animal Type</label>
                <select id="animal_type" name="animal_type" class="form-select">
                    <?php foreach (['wild', 'domestic', 'pet'] as $type): ?>
                    <option value="<?= $type ?>" <?= ($item['animal_type'] ?? 'wild') === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description" class="form-label required">Description</label>
                <textarea id="description" name="description" class="form-textarea" required><?= e($item['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="cultural_use" class="form-label">Cultural Use</label>
                <textarea id="cultural_use" name="cultural_use" class="form-textarea"><?= e($item['cultural_use'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="image" class="form-label">Animal Image</label>
                <?php if (!empty($item['image'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= e(UPLOADS_URL . '/images/' . $item['image']) ?>" alt="Current animal image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                        <p style="margin-top: 5px; font-size: 0.85em; color: #666;">Current image: <?= e($item['image']) ?></p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-input" accept="image/*">
                <p class="form-hint"><?= !empty($item['image']) ? 'Upload a new image to replace the current one' : 'Upload an image of the animal (JPG, PNG, GIF, WebP - max 5MB)' ?></p>
            </div>

        <?php elseif ($category === 'videos'): ?>
            <div class="form-group">
                <label for="title" class="form-label required">Video Title</label>
                <input type="text" id="title" name="title" class="form-input" value="<?= e($item['title']) ?>" required>
            </div>
            <div class="form-group">
                <label for="youtube_id" class="form-label required">YouTube Video ID</label>
                <input type="text" id="youtube_id" name="youtube_id" class="form-input" value="<?= e($item['youtube_id']) ?>" required placeholder="e.g., dQw4w9WgXcQ or full YouTube URL">
                <p class="form-hint">Paste the full YouTube URL or just the video ID — the ID will be extracted automatically.</p>
            </div>
            <div class="form-group">
                <label for="rumble_id" class="form-label">Rumble Video ID <span style="font-weight:400;color:var(--text-muted);">(optional)</span></label>
                <input type="text" id="rumble_id" name="rumble_id" class="form-input" value="<?= e($item['rumble_id'] ?? '') ?>" placeholder="e.g., v79a9o4 or full Rumble URL">
                <p class="form-hint">Paste the Rumble video URL or just the video ID.</p>
            </div>
            <div class="form-group">
                <label for="facebook_url" class="form-label">Facebook Video URL <span style="font-weight:400;color:var(--text-muted);">(optional)</span></label>
                <input type="text" id="facebook_url" name="facebook_url" class="form-input" value="<?= e($item['facebook_url'] ?? '') ?>" placeholder="e.g., https://www.facebook.com/watch/?v=123456 or Reel URL">
                <p class="form-hint">Paste the Facebook video or Reel share URL. The video must be set to <strong>Public</strong> on Facebook.</p>
            </div>
            <div class="form-group">
                <label for="tiktok_id" class="form-label">TikTok Video URL <span style="font-weight:400;color:var(--text-muted);">(optional)</span></label>
                <input type="text" id="tiktok_id" name="tiktok_id" class="form-input" value="<?= e($item['tiktok_id'] ?? '') ?>" placeholder="e.g., https://www.tiktok.com/@user/video/1234567890">
                <p class="form-hint">Paste the full TikTok video link. The video ID will be extracted automatically.</p>
            </div>
            <div class="form-group">
                <label>Preview</label>
                <div style="max-width: 320px;">
                    <img src="https://img.youtube.com/vi/<?= e($item['youtube_id']) ?>/mqdefault.jpg" alt="Video thumbnail" style="width: 100%; border-radius: 4px;">
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-textarea"><?= e($item['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="video_category" class="form-label">Category</label>
                <input type="text" id="video_category" name="category" class="form-input" value="<?= e($item['category']) ?>" placeholder="e.g., Greetings, Numbers, Vocabulary">
            </div>
            <div class="form-group">
                <label for="difficulty" class="form-label">Difficulty Level</label>
                <select id="difficulty" name="difficulty" class="form-select">
                    <option value="beginner" <?= $item['difficulty'] === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= $item['difficulty'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= $item['difficulty'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>
            <div class="form-group">
                <label for="duration" class="form-label">Duration</label>
                <input type="text" id="duration" name="duration" class="form-input" value="<?= e($item['duration']) ?>" placeholder="e.g., 5:30">
            </div>
            <div class="form-group">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" class="form-input" value="<?= e($item['sort_order']) ?>" min="0">
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1" <?= $item['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured (show on homepage)</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?>>
                    <span>Active</span>
                </label>
            </div>
        <?php endif; ?>

        <?php if ($category !== 'videos' && !empty($sources)): ?>
        <div class="form-group" style="border-top:1px solid var(--color-border-light); padding-top:1.25rem; margin-top:0.5rem;">
            <label for="source_id" class="form-label">Source / Contributor</label>
            <select name="source_id" id="source_id" class="form-select">
                <option value="">— No source assigned —</option>
                <?php foreach ($sources as $src): ?>
                <option value="<?= $src['id'] ?>" <?= (int)($item['source_id'] ?? 0) === (int)$src['id'] ? 'selected' : '' ?>>
                    <?= e($src['contributor_name']) ?><?= $src['title'] ? ' — ' . e(mb_substr($src['title'], 0, 60)) : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">Attribute this entry to a contributor or reference. <a href="<?= url('admin/sources/create') ?>" style="color:var(--color-accent);">Add a new source</a></p>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<?php if ($category === 'words'): ?>
<!-- ── Synonyms, Antonyms & Related Words Manager ────────── -->
<div style="max-width:800px; margin-top:2rem;">
    <div class="admin-card" style="padding:1.75rem;">
        <h2 style="font-family:var(--font-heading);font-size:1.15rem;color:var(--color-primary);margin:0 0 1.25rem;">
            &#128279; Synonyms, Antonyms &amp; Related Words
        </h2>
        <?php if (empty($wordRelations)): ?>
        <p class="form-hint">No related words yet.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.2rem;">
            <?php foreach ($wordRelations as $rel): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.5rem .75rem;background:#f7f4ee;border-radius:6px;font-size:.85rem;">
                <span><strong style="text-transform:capitalize;"><?= e($rel['relation']) ?>:</strong> <?= e($rel['item']['tiv_word'] ?? '') ?> — <?= e($rel['item']['english_meaning'] ?? '') ?></span>
                <form method="POST" action="<?= url('admin/content/words/' . $item['id'] . '/relations/' . $rel['link_id'] . '/delete') ?>" onsubmit="return confirm('Remove this relation?')" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" style="background:none;border:none;color:#c0392b;cursor:pointer;font-size:.8rem;font-weight:600;">Remove</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="<?= url('admin/content/words/' . $item['id'] . '/relations') ?>" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap;">
            <?= csrf_field() ?>
            <div>
                <label style="font-size:.78rem;color:#7a6a5a;display:block;margin-bottom:.2rem;">Related word (Tiv spelling)</label>
                <input type="text" name="target_tiv_word" class="form-input" required style="width:200px;">
            </div>
            <div>
                <label style="font-size:.78rem;color:#7a6a5a;display:block;margin-bottom:.2rem;">Relation</label>
                <select name="relation_type" class="form-select">
                    <option value="synonym">Synonym</option>
                    <option value="antonym">Antonym</option>
                    <option value="see_also">See also</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Add</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($category === 'festivals'): ?>
<!-- ── Festival Gallery Manager ──────────────────────────── -->
<div style="max-width:800px; margin-top:2rem;">
    <div class="admin-card" style="padding:1.75rem;">
        <h2 style="font-family:var(--font-heading);font-size:1.15rem;color:var(--color-primary);margin:0 0 1.25rem;">
            🖼️ Festival Gallery
            <span style="font-size:.8rem;font-weight:400;color:var(--color-muted);margin-left:.5rem;"><?= count($gallery ?? []) ?> photo<?= count($gallery ?? []) !== 1 ? 's' : '' ?></span>
        </h2>

        <?php if (!empty($gallery)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;margin-bottom:1.5rem;">
            <?php foreach ($gallery as $photo): ?>
            <div style="position:relative;border-radius:.5rem;overflow:hidden;border:2px solid <?= $photo['is_featured'] ? 'var(--color-accent,#c8832a)' : 'var(--color-border-light,#e2ddd8)' ?>;">
                <img
                    src="<?= e(UPLOADS_URL . '/images/' . $photo['image_path']) ?>"
                    alt="<?= e($photo['alt_text'] ?? $photo['caption'] ?? 'Gallery photo') ?>"
                    style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block;"
                >
                <?php if ($photo['is_featured']): ?>
                <span style="position:absolute;top:.3rem;left:.3rem;background:var(--color-accent,#c8832a);color:#fff;font-size:.65rem;padding:.15rem .4rem;border-radius:999px;font-weight:600;">HERO</span>
                <?php endif; ?>
                <?php if (!empty($photo['caption'])): ?>
                <p style="font-size:.7rem;padding:.3rem .5rem;margin:0;background:var(--color-bg-soft,#f4f0eb);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($photo['caption']) ?></p>
                <?php endif; ?>
                <form action="<?= url('admin/festivals/' . $item['id'] . '/gallery/' . $photo['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Remove this photo from the gallery?');" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" style="width:100%;padding:.35rem;background:#fee2e2;color:#b91c1c;border:none;cursor:pointer;font-size:.75rem;font-weight:600;transition:background .2s;" onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'">
                        ✕ Remove
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:var(--color-muted);font-size:.9rem;margin-bottom:1.25rem;">No photos yet. Upload the first one below.</p>
        <?php endif; ?>

        <!-- Upload form -->
        <form action="<?= url('admin/festivals/' . $item['id'] . '/gallery/upload') ?>" method="POST" enctype="multipart/form-data" style="border-top:1px solid var(--color-border-light,#e2ddd8);padding-top:1.25rem;">
            <?= csrf_field() ?>
            <h3 style="font-size:.95rem;font-weight:600;margin:0 0 1rem;color:var(--color-heading);">Upload New Photo</h3>
            <div class="form-group">
                <label class="form-label required">Photo File</label>
                <input type="file" name="gallery_image" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <p class="form-hint">JPG, PNG, GIF or WebP — max 5 MB</p>
            </div>
            <div class="form-group">
                <label for="gc_caption" class="form-label">Caption</label>
                <input type="text" id="gc_caption" name="caption" class="form-input" placeholder="Short description of the photo">
            </div>
            <div class="form-group">
                <label for="gc_alt" class="form-label">Alt Text</label>
                <input type="text" id="gc_alt" name="alt_text" class="form-input" placeholder="Describe the image for screen readers">
            </div>
            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="is_featured" value="1">
                    <span>Set as hero image <span style="color:var(--color-muted);font-size:.8em;">(shown first, highlighted in border)</span></span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Upload Photo</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (in_array($category, ['names', 'words'])): ?>
<script>
let isRecording = false;
let mediaRecorder = null;
let audioChunks = [];
let audioBlob = null;

function toggleRecording() {
    const btn = document.getElementById('recordBtn');
    const text = document.getElementById('recordText');
    const preview = document.getElementById('audioPreview');

    if (!isRecording) {
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
</script>
<?php endif; ?>
