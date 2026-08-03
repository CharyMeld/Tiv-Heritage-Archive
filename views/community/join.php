<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128101; Community</span>
        <h1 class="page-banner-title">Join the Community</h1>
        <p class="page-banner-sub">Apply to become a Contributor or Researcher in the Tiv Heritage Archive</p>
    </div>
</div>

<section style="padding: 1.5rem 0 3rem;">
    <div class="container">

        <?php
        $errors   = $_SESSION['errors'] ?? [];
        $old      = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        ?>

        <div style="max-width:680px;margin:0 auto;">

            <!-- Member type info -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem;">
                <div style="background:#fdf8f0;border:2px solid #C8A951;border-radius:10px;padding:1.2rem;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;">&#9997;</div>
                    <h3 style="font-size:1rem;color:#5C3A21;margin:0 0 .4rem;">Contributor</h3>
                    <p style="font-size:.85rem;color:#6b5a4a;margin:0;">Share cultural knowledge, words, stories, and heritage items.</p>
                </div>
                <div style="background:#f0f7f3;border:2px solid #4a7c59;border-radius:10px;padding:1.2rem;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;">&#128300;</div>
                    <h3 style="font-size:1rem;color:#4a7c59;margin:0 0 .4rem;">Researcher</h3>
                    <p style="font-size:.85rem;color:#3a5a4a;margin:0;">Conduct academic research, publish findings, and verify content.</p>
                </div>
            </div>

            <form action="<?= url('community/join') ?>" method="POST" enctype="multipart/form-data" class="contribute-form contribute-form-card" data-validate>
                <?= csrf_field() ?>

                <!-- Personal Information -->
                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin-bottom:1.2rem;">Personal Information</h3>

                <div class="form-group">
                    <label for="full_name" class="form-label required">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-input"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required autocomplete="name">
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label required">Email Address <span style="font-weight:400;color:#8a7a6a;">(used as your login username)</span></label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?= htmlspecialchars(!empty($user) ? $user['email'] : ($old['email'] ?? '')) ?>"
                           <?= !empty($user) ? 'readonly' : '' ?> required autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($user)): ?>
                <div class="form-group">
                    <p style="background:#f0f7f3;border:1px solid #4a7c59;border-radius:8px;padding:.75rem 1rem;font-size:.85rem;color:#3a5a4a;margin:0;">
                        &#9989; You're logged in as <strong><?= htmlspecialchars($user['email']) ?></strong> — this application will be linked to your existing account.
                    </p>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label for="password" class="form-label required">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required autocomplete="new-password" minlength="8">
                    <p class="form-hint">At least 8 characters. If this email is already registered, enter <em>that account's</em> password instead — your application will be linked to it rather than creating a duplicate.</p>
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password_confirm" class="form-label required">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required autocomplete="new-password" minlength="8">
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number <span style="font-weight:400;color:#8a7a6a;">(Optional)</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>" autocomplete="tel">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" id="country" name="country" class="form-input"
                               value="<?= htmlspecialchars($old['country'] ?? '') ?>" placeholder="e.g. Nigeria">
                    </div>
                    <div class="form-group">
                        <label for="state_region" class="form-label">State / Region</label>
                        <input type="text" id="state_region" name="state_region" class="form-input"
                               value="<?= htmlspecialchars($old['state_region'] ?? '') ?>" placeholder="e.g. Benue">
                    </div>
                </div>

                <div class="form-group">
                    <label for="occupation" class="form-label">Occupation</label>
                    <input type="text" id="occupation" name="occupation" class="form-input"
                           value="<?= htmlspecialchars($old['occupation'] ?? '') ?>" placeholder="e.g. Teacher, Student, Linguist">
                </div>

                <!-- Membership -->
                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:1.5rem 0 1.2rem;">Membership Details</h3>

                <div class="form-group">
                    <label class="form-label required">Membership Type</label>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                            <input type="radio" name="member_type" value="contributor"
                                   <?= ($old['member_type'] ?? 'contributor') === 'contributor' ? 'checked' : '' ?> required>
                            <span>Contributor</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                            <input type="radio" name="member_type" value="researcher"
                                   <?= ($old['member_type'] ?? '') === 'researcher' ? 'checked' : '' ?>>
                            <span>Researcher</span>
                        </label>
                    </div>
                    <?php if (isset($errors['member_type'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['member_type']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="area_of_interest" class="form-label">Area of Interest</label>
                    <input type="text" id="area_of_interest" name="area_of_interest" class="form-input"
                           value="<?= htmlspecialchars($old['area_of_interest'] ?? '') ?>"
                           placeholder="e.g. Tiv Language, Cultural Traditions, History">
                </div>

                <div class="form-group">
                    <label for="skills" class="form-label">Skills / Expertise</label>
                    <input type="text" id="skills" name="skills" class="form-input"
                           value="<?= htmlspecialchars($old['skills'] ?? '') ?>"
                           placeholder="e.g. Translation, Documentation, Photography">
                </div>

                <div class="form-group">
                    <label for="short_bio" class="form-label">Short Biography</label>
                    <textarea id="short_bio" name="short_bio" class="form-textarea" rows="3"
                              placeholder="Tell us a little about yourself..."><?= htmlspecialchars($old['short_bio'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="reason_for_joining" class="form-label required">Reason for Joining</label>
                    <textarea id="reason_for_joining" name="reason_for_joining" class="form-textarea" rows="4"
                              placeholder="Why do you want to join the Tiv Heritage Archive community?" required><?= htmlspecialchars($old['reason_for_joining'] ?? '') ?></textarea>
                    <?php if (isset($errors['reason_for_joining'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['reason_for_joining']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Optional Files -->
                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:1.5rem 0 1.2rem;">Optional Uploads</h3>

                <div class="form-group">
                    <label for="profile_photo" class="form-label">Profile Photo</label>
                    <input type="file" id="profile_photo" name="profile_photo" class="form-input" accept="image/*">
                    <p class="form-hint">JPG, PNG, WebP — max 5MB</p>
                </div>

                <div class="form-group">
                    <label for="supporting_document" class="form-label">Supporting Document</label>
                    <input type="file" id="supporting_document" name="supporting_document" class="form-input"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <p class="form-hint">PDF, Word, or image — max 5MB. E.g. CV, credentials, published work.</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;">
                    Submit Application
                </button>
                <div class="submit-notice">All applications are reviewed by the admin team before approval.</div>
            </form>
        </div>
    </div>
</section>
