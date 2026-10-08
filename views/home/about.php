<style>
/* ── About page styles ─────────────────────────────────────────── */
.about-banner { background: linear-gradient(135deg, #3d2610 0%, #5C3A21 60%, #7B4F2E 100%); }

.about-section { padding: 2.5rem 0; }
.about-section + .about-section { border-top: 1px solid var(--color-border-light); }

.about-lead {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #3a2a1a;
    max-width: 780px;
}

/* Category grid */
.about-cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.1rem;
    margin-top: 1.5rem;
}
.about-cat-card {
    background: #fff;
    border: 1px solid var(--color-border-light);
    border-radius: 12px;
    padding: 1.15rem 1.2rem;
    display: flex;
    flex-direction: column;
    gap: .55rem;
    transition: box-shadow .18s, transform .18s;
}
.about-cat-card:hover {
    box-shadow: 0 4px 18px rgba(92,58,33,.12);
    transform: translateY(-2px);
}
.about-cat-header {
    display: flex;
    align-items: center;
    gap: .65rem;
}
.about-cat-icon {
    font-size: 1.55rem;
    flex-shrink: 0;
    line-height: 1;
}
.about-cat-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0;
}
.about-cat-items {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.about-cat-items li {
    font-size: .83rem;
    color: #5a4a3a;
    padding: .28rem .6rem;
    background: var(--color-surface-alt);
    border-radius: 5px;
    display: flex;
    align-items: baseline;
    gap: .5rem;
}
.about-cat-items li strong {
    color: var(--color-primary);
    font-size: .82rem;
    white-space: nowrap;
}

/* Tool pills */
.about-tools-row {
    display: flex;
    flex-wrap: wrap;
    gap: .7rem;
    margin-top: 1.2rem;
}
.about-tool-pill {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem 1rem;
    background: #fff;
    border: 1.5px solid var(--color-border-light);
    border-radius: 50px;
    font-size: .86rem;
    font-weight: 600;
    color: var(--color-primary);
    text-decoration: none;
    transition: background .15s, border-color .15s;
}
.about-tool-pill:hover {
    background: var(--color-surface-alt);
    border-color: var(--color-accent);
    text-decoration: none;
    color: var(--color-primary);
}
.about-tool-pill span { font-size: 1.1rem; }

/* Involvement cards */
.about-involve-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1.2rem;
}
.about-involve-card {
    background: #fff;
    border: 1px solid var(--color-border-light);
    border-left: 4px solid var(--color-accent);
    border-radius: 0 10px 10px 0;
    padding: 1.1rem 1rem;
}
.about-involve-card h4 {
    font-size: .92rem;
    color: var(--color-primary);
    margin: 0 0 .4rem;
}
.about-involve-card p {
    font-size: .82rem;
    color: #5a4a3a;
    line-height: 1.55;
    margin: 0;
}

@media (max-width: 600px) {
    .about-cat-grid { grid-template-columns: 1fr; }
    .about-involve-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Banner -->
<div class="page-banner about-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#127758; About</span>
        <h1 class="page-banner-title">About Tiv Heritage Archive</h1>
        <p class="page-banner-sub">Preserving Language &bull; Literature &bull; Culture &bull; History &bull; Identity</p>
    </div>
</div>

<!-- MISSION -->
<section class="about-section">
    <div class="container">
        <h2 class="section-title">Our Mission</h2>
        <p class="about-lead">
            The Tiv Heritage Archive is a living digital library dedicated to documenting, preserving, and sharing
            the full breadth of Tiv heritage — from the spoken word to written history, from traditional customs
            to modern community knowledge. We exist to ensure that nothing is lost and that every generation of
            Tiv people and every student of African culture has a place to learn, contribute, and connect.
        </p>
    </div>
</section>

<!-- WHO ARE THE TIV -->
<section class="about-section" style="background:var(--color-surface);">
    <div class="container">
        <h2 class="section-title">Who Are the Tiv People?</h2>
        <p class="about-lead">
            The Tiv are one of the largest ethnic groups in Nigeria, numbering several million people and
            concentrated primarily in Benue State with communities across Nasarawa, Taraba, Plateau states,
            and beyond. The Tiv speak a distinctive Bantu-related language, practice a unique social structure
            built on lineage and kinship, and carry a rich oral tradition of proverbs, folktales, and poetry.
            Their festivals, crafts, attire, and cuisine reflect centuries of a people deeply connected to the
            land and to each other.
        </p>

    </div>
</section>

<!-- WHAT WE DOCUMENT -->
<section class="about-section">
    <div class="container">
        <h2 class="section-title">What We Document</h2>
        <p style="color:#5a4a3a;font-size:.92rem;margin-bottom:0;max-width:720px;line-height:1.7;">
            This archive spans six major pillars of Tiv heritage. Each pillar is an active, growing collection
            maintained by our community of contributors, researchers, and moderators.
        </p>

        <div class="about-cat-grid">

            <!-- LANGUAGE -->
            <div class="about-cat-card">
                <div class="about-cat-header">
                    <span class="about-cat-icon">&#128172;</span>
                    <h3 class="about-cat-title">Language</h3>
                </div>
                <ul class="about-cat-items">
                    <li><strong>Dictionary</strong> — Tiv words, English meanings, parts of speech &amp; usage examples</li>
                    <li><strong>Alphabet</strong> — 5 vowels, 18 consonants, 7 digraphs, tone markers &amp; audio pronunciations</li>
                    <li><strong>Lessons</strong> — Structured video lessons from beginner to advanced level</li>
                    <li><strong>Translation Engine</strong> — English ↔ Tiv translation with phrase database</li>
                    <li><strong>Tiv Names</strong> — Traditional given names with meanings and cultural context</li>
                </ul>
            </div>

            <!-- LITERATURE -->
            <div class="about-cat-card">
                <div class="about-cat-header">
                    <span class="about-cat-icon">&#128221;</span>
                    <h3 class="about-cat-title">Literature</h3>
                </div>
                <ul class="about-cat-items">
                    <li><strong>Proverbs</strong> — Ancient Tiv sayings preserving moral and philosophical wisdom</li>
                    <li><strong>Folktales</strong> — Traditional oral stories passed down through generations</li>
                    <li><strong>Stories</strong> — Historical and contemporary Tiv narratives</li>
                    <li><strong>Poems</strong> — Oral and written Tiv poetry celebrating identity and nature</li>
                </ul>
            </div>

            <!-- CULTURE -->
            <div class="about-cat-card">
                <div class="about-cat-header">
                    <span class="about-cat-icon">&#127981;</span>
                    <h3 class="about-cat-title">Culture</h3>
                </div>
                <ul class="about-cat-items">
                    <li><strong>Traditions</strong> — Documented customs, rites, and cultural practices</li>
                    <li><strong>Festivals</strong> — Harvest celebrations, rites of passage &amp; communal ceremonies</li>
                    <li><strong>Attire</strong> — Traditional clothing, fabric patterns &amp; ceremonial dress</li>
                    <li><strong>Marriage Customs</strong> — Bride price, courtship ceremonies &amp; family rites</li>
                    <li><strong>Foods</strong> — Traditional cuisine, ingredients &amp; preparation methods</li>
                    <li><strong>Plants</strong> — Medicinal, culinary &amp; ceremonially significant plants</li>
                    <li><strong>Animals</strong> — Animals in Tiv proverb, farming, and cultural practice</li>
                </ul>
            </div>

            <!-- HISTORY -->
            <div class="about-cat-card">
                <div class="about-cat-header">
                    <span class="about-cat-icon">&#128336;</span>
                    <h3 class="about-cat-title">History</h3>
                </div>
                <ul class="about-cat-items">
                    <li><strong>Origins</strong> — Oral and documented accounts of Tiv roots and ancestry</li>
                    <li><strong>Migration</strong> — Historical movements across the Benue Valley and beyond</li>
                    <li><strong>Historical Figures</strong> — Leaders, warriors, scholars and cultural icons</li>
                    <li><strong>Timeline</strong> — Chronological record of key Tiv milestones and events</li>
                </ul>
            </div>

            <!-- ARCHIVE COLLECTIONS -->
            <div class="about-cat-card">
                <div class="about-cat-header">
                    <span class="about-cat-icon">&#128196;</span>
                    <h3 class="about-cat-title">Archive Collections</h3>
                </div>
                <ul class="about-cat-items">
                    <li><strong>Documents</strong> — Historical manuscripts, letters &amp; written records</li>
                    <li><strong>Audio Recordings</strong> — Songs, speeches, oral traditions &amp; interviews</li>
                    <li><strong>Videos</strong> — Cultural recordings &amp; language learning content</li>
                    <li><strong>Research Publications</strong> — Academic papers &amp; community research</li>
                </ul>
            </div>

            <!-- BIBLE & SACRED TEXTS -->
            <div class="about-cat-card">
                <div class="about-cat-header">
                    <span class="about-cat-icon">&#128214;</span>
                    <h3 class="about-cat-title">Bible in Tiv</h3>
                </div>
                <ul class="about-cat-items">
                    <li><strong>Full Bible</strong> — All 66 books of the Bible in the Tiv language</li>
                    <li><strong>Searchable text</strong> — Browse by book, chapter and verse</li>
                    <li><strong>Cross-reference</strong> — Connect scripture vocabulary to the dictionary</li>
                    <li><strong>Tiv scripture</strong> — Preserving the historic Tiv Bible translation</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- TOOLS & FEATURES -->
<section class="about-section" style="background:var(--color-surface);">
    <div class="container">
        <h2 class="section-title">Tools &amp; Features</h2>
        <p style="color:#5a4a3a;font-size:.92rem;max-width:680px;line-height:1.7;margin-bottom:0;">
            Beyond content, the archive provides interactive tools that make the language and culture
            accessible and learnable.
        </p>
        <div class="about-tools-row">
            <a href="<?= url('translate') ?>" class="about-tool-pill"><span>&#127760;</span> English ↔ Tiv Translator</a>
            <a href="<?= url('language/alphabet') ?>" class="about-tool-pill"><span>&#127279;</span> Interactive Alphabet + Audio</a>
            <a href="<?= url('learn') ?>" class="about-tool-pill"><span>&#127979;</span> Video Lesson Library</a>
            <a href="<?= url(section_path('words')) ?>" class="about-tool-pill"><span>&#128218;</span> Tiv Dictionary</a>
            <a href="<?= url('bible') ?>" class="about-tool-pill"><span>&#128214;</span> Bible in Tiv</a>
            <a href="<?= url('references') ?>" class="about-tool-pill"><span>&#128279;</span> Knowledge References</a>
            <a href="<?= url('community') ?>" class="about-tool-pill"><span>&#128101;</span> Community Directory</a>
            <a href="<?= url('suggestions') ?>" class="about-tool-pill"><span>&#128161;</span> Feedback &amp; Suggestions</a>
        </div>
    </div>
</section>

<!-- COMMUNITY DRIVEN -->
<section class="about-section">
    <div class="container">
        <h2 class="section-title">Community Driven</h2>
        <p class="about-lead">
            This archive belongs to the Tiv people. Every entry — from a single word to a detailed festival
            description — can be contributed by anyone with knowledge to share. A team of approved contributors,
            researchers, and moderators review and enrich submissions to maintain accuracy and depth.
            Academic institutions, diaspora communities, and cultural organizations are all welcome partners.
        </p>
    </div>
</section>

<!-- GET INVOLVED -->
<section class="about-section" style="background:var(--color-surface);">
    <div class="container">
        <h2 class="section-title">Get Involved</h2>
        <div class="about-involve-grid">
            <div class="about-involve-card">
                <h4>&#9997; Contribute Content</h4>
                <p>Share a name, proverb, recipe, story, or historical account through our public submission form. No account needed.</p>
            </div>
            <div class="about-involve-card">
                <h4>&#128300; Join as Researcher</h4>
                <p>Apply as a community researcher or contributor to get an account and directly manage archive entries.</p>
            </div>
            <div class="about-involve-card">
                <h4>&#127911; Upload Audio</h4>
                <p>Record and upload pronunciation audio for alphabet letters, words, or oral literature to bring the archive to life.</p>
            </div>
            <div class="about-involve-card">
                <h4>&#128172; Send Feedback</h4>
                <p>Spot an error? Have a suggestion? Use our feedback system to report corrections or request new features.</p>
            </div>
            <div class="about-involve-card">
                <h4>&#128279; Share the Archive</h4>
                <p>Share the archive with schools, diaspora communities, researchers, and family. Every visitor helps the mission grow.</p>
            </div>
            <div class="about-involve-card">
                <h4>&#129309; Partner With Us</h4>
                <p>Academic institutions, publishers, and cultural organisations can partner with us to expand and authenticate the archive.</p>
            </div>
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem;">
            <a href="<?= url('contribute') ?>" class="btn btn-primary">&#9997; Start Contributing</a>
            <a href="<?= url('community/join') ?>" class="btn btn-secondary">&#128101; Join as Researcher</a>
            <a href="<?= url('contact') ?>" class="btn btn-secondary">&#128172; Contact Us</a>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/views/partials/team-section.php'; ?>
