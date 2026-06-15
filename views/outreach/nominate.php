<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#127757; Community</span>
        <h1 class="page-banner-title">Nominate an Influential Tiv Person</h1>
        <p class="page-banner-sub">Help us reach Tiv leaders, scholars, artists, and cultural figures so they can learn about and support the Tiv Heritage Archive.</p>
    </div>
</div>

<section style="padding:1.5rem 0 3rem;">
    <div class="container">

        <?php
        $errors = $_SESSION['errors']   ?? [];
        $old    = $_SESSION['old_input']?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        ?>

        <div style="max-width:680px;margin:0 auto;">

            <!-- What happens next -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:2rem;">
                <div style="background:#fdf8f0;border:1px solid #e5e0d5;border-radius:10px;padding:1rem;text-align:center;">
                    <div style="font-size:1.6rem;">&#128221;</div>
                    <div style="font-size:.85rem;color:#5C3A21;font-weight:600;margin:.3rem 0 .2rem;">You Nominate</div>
                    <div style="font-size:.8rem;color:#7a6a5a;">Submit the person's details</div>
                </div>
                <div style="background:#fdf8f0;border:1px solid #e5e0d5;border-radius:10px;padding:1rem;text-align:center;">
                    <div style="font-size:1.6rem;">&#128269;</div>
                    <div style="font-size:.85rem;color:#5C3A21;font-weight:600;margin:.3rem 0 .2rem;">We Review</div>
                    <div style="font-size:.8rem;color:#7a6a5a;">Admin verifies and approves</div>
                </div>
                <div style="background:#fdf8f0;border:1px solid #e5e0d5;border-radius:10px;padding:1rem;text-align:center;">
                    <div style="font-size:1.6rem;">&#127757;</div>
                    <div style="font-size:.85rem;color:#5C3A21;font-weight:600;margin:.3rem 0 .2rem;">We Reach Out</div>
                    <div style="font-size:.8rem;color:#7a6a5a;">We invite them to the archive</div>
                </div>
            </div>

            <form action="<?= url('nominate-influential') ?>" method="POST" class="contribute-form contribute-form-card" data-validate>
                <?= csrf_field() ?>

                <!-- Nominee -->
                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:0 0 1.2rem;">About the Nominee</h3>

                <div class="form-group">
                    <label for="nominee_name" class="form-label required">Full Name</label>
                    <input type="text" id="nominee_name" name="nominee_name" class="form-input" required
                           value="<?= htmlspecialchars($old['nominee_name'] ?? '') ?>" placeholder="e.g. Prof. James Ayam">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="nominee_title" class="form-label">Title / Role</label>
                        <input type="text" id="nominee_title" name="nominee_title" class="form-input"
                               value="<?= htmlspecialchars($old['nominee_title'] ?? '') ?>" placeholder="e.g. Professor, Chief, Artist">
                    </div>
                    <div class="form-group">
                        <label for="nominee_category" class="form-label">Category</label>
                        <select id="nominee_category" name="nominee_category" class="form-select">
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= ($old['nominee_category'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nominee_organization" class="form-label">Organization / Institution</label>
                    <input type="text" id="nominee_organization" name="nominee_organization" class="form-input"
                           value="<?= htmlspecialchars($old['nominee_organization'] ?? '') ?>" placeholder="e.g. University of Jos, Tiv Cultural Council">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="nominee_email" class="form-label">Their Email <span style="font-weight:400;color:#8a7a6a;">(if known)</span></label>
                        <input type="email" id="nominee_email" name="nominee_email" class="form-input"
                               value="<?= htmlspecialchars($old['nominee_email'] ?? '') ?>" placeholder="nominee@example.com">
                    </div>
                    <div class="form-group">
                        <label for="nominee_website" class="form-label">Website / Social</label>
                        <input type="url" id="nominee_website" name="nominee_website" class="form-input"
                               value="<?= htmlspecialchars($old['nominee_website'] ?? '') ?>" placeholder="https://…">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="nominee_country" class="form-label">Country</label>
                        <input type="text" id="nominee_country" name="nominee_country" class="form-input"
                               value="<?= htmlspecialchars($old['nominee_country'] ?? '') ?>" placeholder="e.g. Nigeria, UK">
                    </div>
                    <div class="form-group">
                        <label for="nominee_state_region" class="form-label">State / Region</label>
                        <input type="text" id="nominee_state_region" name="nominee_state_region" class="form-input"
                               value="<?= htmlspecialchars($old['nominee_state_region'] ?? '') ?>" placeholder="e.g. Benue">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason_for_nomination" class="form-label required">Why are you nominating this person?</label>
                    <textarea id="reason_for_nomination" name="reason_for_nomination" class="form-textarea" rows="4" required
                              placeholder="Describe their contributions to Tiv culture, language, society, or any reason you think they would be a valuable contact for the archive."><?= htmlspecialchars($old['reason_for_nomination'] ?? '') ?></textarea>
                </div>

                <!-- Nominator -->
                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:1.5rem 0 1.2rem;">About You <span style="font-size:.85rem;font-weight:400;color:#8a7a6a;">(optional — your info is kept private)</span></h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="nominator_name" class="form-label">Your Name</label>
                        <input type="text" id="nominator_name" name="nominator_name" class="form-input"
                               value="<?= htmlspecialchars($old['nominator_name'] ?? '') ?>" autocomplete="name">
                    </div>
                    <div class="form-group">
                        <label for="nominator_email" class="form-label">Your Email</label>
                        <input type="email" id="nominator_email" name="nominator_email" class="form-input"
                               value="<?= htmlspecialchars($old['nominator_email'] ?? '') ?>" autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;">
                    Submit Nomination
                </button>
                <div class="submit-notice">All nominations are reviewed by our team before the person is contacted. We will never share your personal information.</div>
            </form>
        </div>
    </div>
</section>
