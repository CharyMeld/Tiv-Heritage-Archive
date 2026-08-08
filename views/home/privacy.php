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
        <span class="page-banner-eyebrow">&#128274; Legal</span>
        <h1 class="page-banner-title">Privacy Policy</h1>
        <p class="page-banner-sub">How we collect, use, and protect your information</p>
    </div>
</div>

<section class="legal-section">
    <div class="container">
        <div class="legal-body">
            <p class="legal-updated">Last updated: <?= date('F j, Y') ?></p>

            <p>
                <?= SITE_NAME ?> ("we", "us", or "our") operates <?= htmlspecialchars(SITE_URL) ?> (the "Site").
                This Privacy Policy explains what information we collect, how we use it, and the choices you have.
                By using the Site, you agree to the collection and use of information in accordance with this policy.
            </p>

            <h2>Information We Collect</h2>
            <p>We collect information in the following ways:</p>
            <ul>
                <li><strong>Information you provide directly</strong> — such as your name and email address when you submit a suggestion, join the community, apply as a contributor, or contact us through a form on the Site.</li>
                <li><strong>Automatically collected information</strong> — such as your IP address, browser type, device information, pages visited, and time spent on the Site, collected through standard server logs and cookies.</li>
                <li><strong>Cookies and similar technologies</strong> — small files stored on your device that help the Site function and allow us and our partners to understand how the Site is used.</li>
            </ul>

            <h2>How We Use Your Information</h2>
            <ul>
                <li>To operate, maintain, and improve the Site and its content;</li>
                <li>To respond to suggestions, contributions, and contact requests;</li>
                <li>To review and process community membership and contributor applications;</li>
                <li>To understand how visitors use the Site so we can improve it;</li>
                <li>To display relevant advertising, as described below.</li>
            </ul>

            <h2>Advertising and Google AdSense</h2>
            <p>
                We use Google AdSense to display advertisements on the Site. Google, as a third-party vendor,
                uses cookies (including the DoubleClick cookie) to serve ads based on a visitor's prior visits
                to this and other websites. Google's use of advertising cookies enables it and its partners to
                serve ads based on your visit to this Site and/or other sites on the Internet.
            </p>
            <p>
                You may opt out of personalized advertising by visiting
                <a href="https://adssettings.google.com" target="_blank" rel="noopener">Google Ads Settings</a>.
                Alternatively, you can opt out of third-party vendor use of cookies for personalized advertising
                by visiting <a href="https://www.aboutads.info/choices" target="_blank" rel="noopener">www.aboutads.info</a>.
            </p>

            <h2>Third-Party Links and Services</h2>
            <p>
                The Site may contain links to external sites (including social media platforms) that are not
                operated by us. We are not responsible for the content or privacy practices of any third-party
                sites. We encourage you to review the privacy policy of every site you visit.
            </p>

            <h2>Children's Privacy</h2>
            <p>
                The Site is not directed at children under 13, and we do not knowingly collect personal
                information from children under 13. If you believe a child has provided us with personal
                information, please contact us and we will remove it.
            </p>

            <h2>Data Retention and Security</h2>
            <p>
                We retain information you submit (such as suggestions and applications) for as long as needed
                to fulfill the purpose it was collected for, or as required by law. We take reasonable measures
                to protect your information, but no method of transmission or storage is 100% secure.
            </p>

            <h2>Your Choices</h2>
            <p>
                You can disable cookies through your browser settings, though some parts of the Site may not
                function properly without them. You may also contact us at any time to request that we delete
                information you have submitted to us.
            </p>

            <h2>Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. Any changes will be posted on this page
                with an updated revision date.
            </p>

            <h2>Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy, please
                <a href="<?= url('contact') ?>">contact us</a> or email
                <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.
            </p>
        </div>
    </div>
</section>
