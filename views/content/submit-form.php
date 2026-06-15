<style>
.submit-bilingual {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border: 1px solid #e5e0d5;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1.3rem;
}
.submit-bilingual-col { padding: 1.1rem 1.2rem; }
.submit-bilingual-col:first-child {
    border-right: 1px solid #e5e0d5;
    background: #fdfcfb;
}
.submit-bilingual-col:last-child { background: #fdf8f0; }
.submit-bilingual-heading {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin: 0 0 .8rem;
    padding-bottom: .4rem;
    border-bottom: 2px solid;
}
.submit-bilingual-col:first-child .submit-bilingual-heading { color:#2d5a9e; border-color:#2d5a9e44; }
.submit-bilingual-col:last-child  .submit-bilingual-heading { color:#5C3A21; border-color:#5C3A2144; }
@media (max-width:640px) {
    .submit-bilingual { grid-template-columns: 1fr; }
    .submit-bilingual-col:first-child { border-right:none; border-bottom:1px solid #e5e0d5; }
}
</style>

<!-- Breadcrumb -->
<div style="background:#faf8f5;border-bottom:1px solid #e5e0d5;padding:.6rem 0;">
    <div class="container" style="font-size:.83rem;color:#7a6a5a;">
        <a href="<?= url('/') ?>" style="color:#5C3A21;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url($section) ?>" style="color:#5C3A21;text-decoration:none;"><?= htmlspecialchars($sectionMeta['title']) ?></a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url($section . '/' . $sub) ?>" style="color:#5C3A21;text-decoration:none;"><?= htmlspecialchars($subLabel) ?></a>
        <span style="margin:0 .4rem;">›</span>
        <span>Submit</span>
    </div>
</div>

<!-- Banner -->
<div class="contribute-banner" style="background:linear-gradient(135deg,<?= htmlspecialchars($sectionMeta['color']) ?> 0%,<?= htmlspecialchars($sectionMeta['color']) ?>bb 100%);">
    <div class="container">
        <span class="page-banner-eyebrow"><?= $sectionMeta['icon'] ?> <?= htmlspecialchars($sectionMeta['title']) ?></span>
        <h1 class="page-banner-title">Submit <?= htmlspecialchars($subLabel) ?></h1>
        <p class="page-banner-sub">Share your knowledge in English and Tiv — our team will review your submission before publishing.</p>
    </div>
</div>

<section style="padding:2rem 0 4rem;">
    <div class="container" style="max-width:860px;">

        <form action="<?= url("submit/{$section}/{$sub}") ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- ── Your details ──────────────────────────────────────── -->
            <div class="contribute-form-card" style="margin-bottom:1.5rem;padding:1.3rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#5C3A21;margin:0 0 1rem;padding-bottom:.5rem;border-bottom:1px solid #e5e0d5;">Your Details</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label required">Full Name</label>
                        <input type="text" name="submitter_name" class="form-input" required
                               value="<?= htmlspecialchars($old['submitter_name'] ?? ($user['name'] ?? '')) ?>"
                               placeholder="Your full name" autocomplete="name">
                        <?php if (!empty($errors['submitter_name'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['submitter_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label required">Email Address</label>
                        <input type="email" name="submitter_email" class="form-input" required
                               value="<?= htmlspecialchars($old['submitter_email'] ?? ($user['email'] ?? '')) ?>"
                               placeholder="your@email.com" autocomplete="email">
                        <?php if (!empty($errors['submitter_email'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['submitter_email']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── Title ─────────────────────────────────────────────── -->
            <div class="submit-bilingual">
                <div class="submit-bilingual-col">
                    <p class="submit-bilingual-heading">&#127760; English Title</p>
                    <div class="form-group" style="margin:0;">
                        <input type="text" name="title" class="form-input" required
                               value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                               placeholder="Title in English">
                        <?php if (!empty($errors['title'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="submit-bilingual-col">
                    <p class="submit-bilingual-heading">&#127981; Tiv Title</p>
                    <div class="form-group" style="margin:0;">
                        <input type="text" name="tiv_title" class="form-input"
                               value="<?= htmlspecialchars($old['tiv_title'] ?? '') ?>"
                               placeholder="U Tiv (wan sha)">
                    </div>
                </div>
            </div>

            <!-- ── Summary ───────────────────────────────────────────── -->
            <div class="submit-bilingual">
                <div class="submit-bilingual-col">
                    <p class="submit-bilingual-heading">&#127760; English Summary</p>
                    <div class="form-group" style="margin:0;">
                        <textarea name="excerpt" class="form-textarea" rows="3"
                                  placeholder="Short English description..."><?= htmlspecialchars($old['excerpt'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="submit-bilingual-col">
                    <p class="submit-bilingual-heading">&#127981; Tiv Summary</p>
                    <div class="form-group" style="margin:0;">
                        <textarea name="tiv_excerpt" class="form-textarea" rows="3"
                                  placeholder="Kper sha u Tiv..."><?= htmlspecialchars($old['tiv_excerpt'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ── Full content ──────────────────────────────────────── -->
            <div class="submit-bilingual">
                <div class="submit-bilingual-col">
                    <p class="submit-bilingual-heading">&#127760; Full Content (English)</p>
                    <div class="form-group" style="margin:0;">
                        <textarea name="content" class="form-textarea" rows="12"
                                  placeholder="Full content in English — story, description, historical account..."><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
                        <p class="form-hint" style="margin-top:.35rem;">Plain text. Line breaks preserved.</p>
                    </div>
                </div>
                <div class="submit-bilingual-col">
                    <p class="submit-bilingual-heading">&#127981; Full Content (Tiv)</p>
                    <div class="form-group" style="margin:0;">
                        <textarea name="tiv_content" class="form-textarea" rows="12"
                                  placeholder="Kper sha u Tiv — tar, nom, sha msugh..."><?= htmlspecialchars($old['tiv_content'] ?? '') ?></textarea>
                        <p class="form-hint" style="margin-top:.35rem;">U Tiv sha. Tor sha i gbe hen a mba.</p>
                    </div>
                </div>
            </div>

            <!-- ── Media ─────────────────────────────────────────────── -->
            <div class="contribute-form-card" style="margin-bottom:1.5rem;padding:1.3rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#5C3A21;margin:0 0 .8rem;">
                    Supporting Media <span style="font-weight:400;color:#7a6a5a;font-size:.85rem;">(Optional)</span>
                </h3>
                <input type="file" name="media_file" class="form-input"
                       accept="image/*,audio/*,.pdf,.doc,.docx">
                <p class="form-hint">Image (JPG/PNG/WebP), Audio (MP3/WAV), or Document (PDF/Word) — max 5MB</p>
            </div>

            <!-- ── Submit ────────────────────────────────────────────── -->
            <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem;padding:.85rem;">
                Submit for Review
            </button>
            <div class="submit-notice" style="margin-top:.8rem;">
                All submissions are reviewed by our team before being published. You will not receive an automated email, but we appreciate every contribution.
            </div>
        </form>

    </div>
</section>
