<style>
.legal-banner { background: linear-gradient(135deg, #3d2610 0%, #5C3A21 60%, #7B4F2E 100%); }
.legal-section { padding: 2.5rem 0; }
.legal-body { max-width: 780px; line-height: 1.8; color: #3a2a1a; }
.legal-body h2 { font-size: 1.25rem; color: var(--color-primary); margin: 2rem 0 .7rem; }
.legal-body h2:first-child { margin-top: 0; }
.legal-body p { margin: 0 0 1rem; }
.legal-body ul { margin: 0 0 1rem; padding-left: 1.3rem; }
.legal-body li { margin-bottom: .4rem; }
.legal-updated { font-size: .85rem; color: #7a6a5a; margin-bottom: 1.5rem; }
</style>

<div class="page-banner legal-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128220; Legal</span>
        <h1 class="page-banner-title">Terms of Service</h1>
        <p class="page-banner-sub">The rules for using this Site</p>
    </div>
</div>

<section class="legal-section">
    <div class="container">
        <div class="legal-body">
            <p class="legal-updated">Last updated: <?= date('F j, Y') ?></p>

            <p>
                These Terms of Service ("Terms") govern your access to and use of <?= SITE_NAME ?>
                at <?= htmlspecialchars(SITE_URL) ?> (the "Site"). By using the Site, you agree to these Terms.
                If you do not agree, please do not use the Site.
            </p>

            <h2>Purpose of the Site</h2>
            <p>
                The Site is a digital archive dedicated to documenting and preserving Tiv language,
                history, literature, and culture, made freely available for educational and research purposes.
            </p>

            <h2>Use of Content</h2>
            <p>
                Content on the Site — including dictionary entries, translations, articles, proverbs, and
                historical material — is provided for personal, educational, and non-commercial use unless
                stated otherwise. You may share and cite content with proper attribution to <?= SITE_NAME ?>.
                Bulk reproduction, redistribution, or commercial use of Site content without our written
                permission is not permitted.
            </p>

            <h2>User Contributions</h2>
            <p>
                When you submit content to the Site — such as suggestions, corrections, translations, or
                community applications — you confirm that the submission is your own work or that you have
                the right to share it, and you grant us a non-exclusive, royalty-free license to use, edit,
                and publish that content as part of the archive. We may edit, moderate, or decline to publish
                any submission at our discretion.
            </p>

            <h2>Acceptable Use</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use the Site for any unlawful purpose or in violation of these Terms;</li>
                <li>Submit false, misleading, defamatory, or infringing content;</li>
                <li>Attempt to disrupt, hack, or gain unauthorized access to the Site or its systems;</li>
                <li>Scrape or systematically extract Site content without our permission.</li>
            </ul>

            <h2>Advertising</h2>
            <p>
                The Site may display advertisements served by third parties, including Google AdSense.
                See our <a href="<?= url('privacy-policy') ?>">Privacy Policy</a> for details on how
                advertising cookies are used.
            </p>

            <h2>Accuracy of Content</h2>
            <p>
                We make reasonable efforts to ensure the accuracy of language, historical, and cultural
                content on the Site, but the Site is a community-supported archive and content may be
                incomplete, evolving, or corrected over time. Content is provided "as is" without warranty
                of any kind.
            </p>

            <h2>Limitation of Liability</h2>
            <p>
                To the fullest extent permitted by law, <?= SITE_NAME ?> shall not be liable for any
                indirect, incidental, or consequential damages arising from your use of the Site.
            </p>

            <h2>Changes to These Terms</h2>
            <p>
                We may update these Terms from time to time. Continued use of the Site after changes are
                posted constitutes acceptance of the revised Terms.
            </p>

            <h2>Contact Us</h2>
            <p>
                Questions about these Terms can be sent via our <a href="<?= url('contact') ?>">contact page</a>
                or to <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.
            </p>
        </div>
    </div>
</section>
