<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128172; Feedback</span>
        <h1 class="page-banner-title">Suggestions &amp; Feedback</h1>
        <p class="page-banner-sub">Help us improve the Tiv Heritage Archive — share corrections, ideas, and contributions</p>
    </div>
</div>

<section style="padding: 1.5rem 0 3rem;">
    <div class="container">

        <?php
        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        ?>

        <!-- Category quick-links -->
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:2rem;">
            <?php
            $quickCats = [
                'dictionary_correction'  => ['&#128218;', 'Dictionary Correction'],
                'translation_suggestion' => ['&#127760;', 'Translation Suggestion'],
                'content_correction'     => ['&#128221;', 'Content Correction'],
                'feature_request'        => ['&#128161;', 'Feature Request'],
                'technical_issue'        => ['&#9881;', 'Technical Issue'],
            ];
            foreach ($quickCats as $val => [$icon, $label]):
            ?>
            <button type="button" onclick="document.getElementById('category').value='<?= $val ?>';this.scrollIntoView({block:'nearest'})"
                    style="background:#fdf8f0;border:1px solid #C8A951;border-radius:20px;padding:.35rem .9rem;font-size:.82rem;cursor:pointer;color:#5C3A21;">
                <?= $icon ?> <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div style="max-width:680px;">
            <form action="<?= url('suggestions') ?>" method="POST" enctype="multipart/form-data" class="contribute-form contribute-form-card" data-validate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="full_name" class="form-label required">Your Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-input"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required autocomplete="name">
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label required">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label required">Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Select a category...</option>
                        <?php
                        $selectedCat = $old['category'] ?? $preCategory ?? '';
                        foreach ($categories as $val => $label):
                        ?>
                            <option value="<?= $val ?>" <?= $selectedCat === $val ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['category']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label required">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-input"
                           value="<?= htmlspecialchars($old['subject'] ?? '') ?>"
                           placeholder="Brief description of your feedback" required>
                    <?php if (isset($errors['subject'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['subject']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="message" class="form-label required">Message</label>
                    <textarea id="message" name="message" class="form-textarea" rows="6"
                              placeholder="Please be as detailed as possible..." required><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['message']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="attachment" class="form-label">Attachment <span style="font-weight:400;color:#8a7a6a;">(Optional)</span></label>
                    <input type="file" id="attachment" name="attachment" class="form-input"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                    <p class="form-hint">Image, PDF, Word or text — max 5MB</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;">Send Feedback</button>
                <div class="submit-notice">All submissions are reviewed by our team.</div>
            </form>
        </div>
    </div>
</section>
