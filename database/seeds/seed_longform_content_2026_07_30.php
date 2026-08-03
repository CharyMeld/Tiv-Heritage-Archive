#!/usr/bin/env php
<?php
/**
 * Publishes the three Tier-3 long-form drafts (see OUTREACH_PLAN.md and
 * content_drafts/*.md) into the content_items CMS table as DRAFTS.
 *
 * Deliberately inserted with status='draft', not 'published' — each source
 * markdown file still has [VERIFY] flags on claims (Tor Tiv's 1948 founding,
 * bride-price abolition timing, population figures, etc.) that need
 * Charles's own review before these go live, since the whole point of the
 * outreach plan is that these pages need to hold up to outside scrutiny.
 * Flip status to 'published' via admin/content-items/{section}/{sub} once
 * reviewed.
 *
 * Content is stored as plain text (the item-detail view renders it via
 * htmlspecialchars() + white-space:pre-line, not a markdown parser), so
 * markdown formatting from the source .md files has been stripped/adapted
 * here rather than pasted verbatim.
 *
 * Run: DB_CLI_PASS='...' php database/seeds/seed_longform_content_2026_07_30.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';

$dbPass = getenv('DB_CLI_PASS');
if ($dbPass === false || $dbPass === '') {
    fwrite(STDERR, "Set DB_CLI_PASS environment variable before running this script.\n");
    exit(1);
}

$pdo = new PDO(
    'mysql:host=localhost;dbname=tiv_archive;charset=utf8mb4',
    'tivuser',
    $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function line(string $text = ''): void { echo $text . PHP_EOL; }
function ok(string $text): void        { line("  \e[0;32m✔\e[0m  {$text}"); }
function skip(string $text): void      { line("  \e[0;33m–\e[0m  {$text}"); }

$ADMIN_USER_ID = 1; // Administrator <admin@tivarchive.com>, confirmed on production

$items = [
    [
        'section'     => 'history',
        'subcategory' => 'migration',
        'title'       => 'The History of Tiv Migration',
        'excerpt'     => 'How the Tiv people trace their origins to the sacred mountain of Swem, and the generations-long migration that brought them into the Benue Valley — including where oral tradition and archaeology disagree.',
        'content'     => <<<'TEXT'
Where the story begins: Swem

Tiv oral tradition traces the ethnic group's origin to a single ancestor named Tiv, and to an ancestral homeland called Swem — a mountain most accounts place near the Nigeria–Cameroon border. Tiv is said to have had two sons, Ichôngo and Ipusu, and to this day most Tiv people identify as descendants of one branch or the other — a division still used informally to describe kinship and geography within Tiv society.

Swem is not treated as a simple place of origin. In Tiv cultural life it remains a living spiritual reference point — a symbol of unity and, in some accounts, a source of ritual power — rather than a purely historical location people have moved on from.

Why the Tiv moved

Oral accounts point to two recurring pressures behind the migration out of the Swem area: population growth outpacing available farmland, and conflict with neighboring groups. Communities moved in stages rather than as a single migration event, pushing gradually north-westward and eventually down into what is now the Benue Valley in central Nigeria.

The journey, as remembered orally, wasn't a straight line or a peaceful one. Groups moved from refuge to refuge, facing pressure from other populations along the way. One especially well-known point of contact was at a place called Gashaka/Gashinbila, where Tiv migrants encountered Fulani, Kuteb, Jukun, Chamba, and other groups already settled in the area — encounters that included conflict before the Tiv secured their own settlement.

One recurring motif in the oral tradition — crossing a great river on the back of a friendly snake — is worth including as folklore rather than literal history; it's the kind of detail that signals these accounts function partly as origin myth, not just as a travel log.

Arrival in the Benue Valley

By the time this migration process had run its course, Tiv communities were settled across the Middle Benue Valley — the core of what is now Benue State, and the geographic center of Tiv life today, with Tiv populations extending into neighboring Nasarawa, Taraba, Plateau, Cross River, Adamawa, and Kaduna states, the Federal Capital Territory, and parts of Cameroon.

What's settled, and what's contested

It's worth being direct about this rather than presenting the migration story as flat fact: Tiv historiography has an active, unresolved debate between what oral tradition describes and what archaeological investigation in the Benue Valley has so far found. Researchers have noted that the traditional accounts of origin and migration don't line up cleanly with the archaeological record, and that there hasn't yet been enough dedicated archaeological and anthropological research on Tiv history to settle the question either way.

That's not a weakness specific to Tiv history — most orally-transmitted migration histories worldwide face the same evidentiary gap — but it means this account is presented as the Tiv people's own account of their origins, rather than as an independently verified historical timeline.

Why this still matters today

The migration narrative isn't just historical trivia — it's the backbone of how Tiv identity, kinship, and social organization are understood. The Ichôngo/Ipusu division from the Swem story maps onto the segmentary lineage system that has traditionally organized Tiv society, and Swem itself remains a real destination for cultural and spiritual observance, not just a name in an old story.

Sources consulted (secondary/web sources — not primary fieldwork): Mdzough U Tiv ("A Deep Dive into Tiv History and Heritage"), MUTUK ("The Founding of the Tiv"), Tourism and Heritage Journal ("Swem: The Tangible and Intangible Cultural Heritage of the Tiv of Central Nigeria"), and academic commentary on Akiga Sai's Akiga's Story (1939) — the most-cited primary written source on Tiv history, worth reading directly before finalizing this page.

--- REVIEW NOTES (remove before publishing) ---
- Confirm Ichôngo/Ipusu spelling and translation conventions used elsewhere on this site.
- Charles may know a more precise or more commonly-told version of the river-crossing episode than what surfaced in general web research.
- Consider citing Akiga Sai's Akiga's Story directly if accessible — it would upgrade this from a web-researched summary to grounded-in-the-primary-source, which matters for this page's credibility as a citation target.
TEXT,
    ],
    [
        'section'     => 'culture',
        'subcategory' => 'marriage-customs',
        'title'       => 'Tiv Marriage Customs',
        'excerpt'     => 'From yamshe (exchange marriage) to kem kwase (bride price) — the customs, vocabulary, and family obligations that shape a Tiv marriage, and how they’ve changed over time.',
        'content'     => <<<'TEXT'
Tiv marriage has never been treated as a transaction between two individuals — it's understood as an alliance between two families, and the customs around it reflect that: multi-stage, family-negotiated, and historically tied to broader questions of social alliance rather than just a wedding day.

Yamshe: exchange marriage

The earliest documented form of Tiv marriage is yamshe, an exchange system in which families gave daughters or sisters to one another rather than exchanging money or goods. The point of yamshe wasn't just to marry off individuals — it built reciprocal obligations and lasting bonds between the two families involved, which functioned as a form of social alliance and trust-building well beyond the couple themselves.

Yamshe is no longer practiced — it was abolished and replaced by the bride-price system described below.

Kem / Kem Kwase: bride-price marriage

The system that replaced yamshe — and the one most associated with Tiv marriage today — is kem (also referred to as kem kwase, "bride price"). Unlike a one-time payment, kem is traditionally understood as an ongoing process: payment to the bride's family, in cash or in kind, made in installments rather than settled in a single transaction. Some accounts describe it as functionally open-ended rather than a fixed, closed obligation.

Items traditionally associated with kem include things like a large pig, a wheelbarrow, salt, palm oil, meat, fish, and jewelry for the bride's mother specifically — a mix of practical goods and gestures aimed at the extended family, not just the bride's parents narrowly.

There has been recent public attention — including a directive reported in Nigerian media — around capping bride-price amounts via traditional/monarchical authority, reflecting ongoing negotiation within Tiv society itself about what kem should look like today.

The wedding itself

On the wedding day, the bride is traditionally brought to the groom's house in a procession involving singing, drumming, and choreographed dancing. Both families dress in anger cloth — the black-and-white striped fabric that functions as a visual marker of Tiv identity — as part of the occasion, not just as festive dress but as a statement of who these families are.

Family involvement runs through the entire process, from the initial negotiation through the wedding day itself — consistent with the broader Tiv view that marriage joins two families, not just two people.

Why this belongs on a heritage archive

Marriage customs like these are exactly the kind of living cultural knowledge that erodes fastest — not because anyone forgets it exists, but because the specific vocabulary (yamshe, kem, kem kwase), the specific items, and the reasoning behind them tend to get flattened into generic "traditional wedding" descriptions once they're no longer actively practiced day-to-day. Documenting the actual terms and their meaning is squarely in scope for what this archive is for.

Sources consulted (secondary/web sources): Greenweblife ("The Customs & Practices Surrounding a Tiv Marriage"), Nnewi City ("Tiv Traditional Marriage Rites"), Tiv Nation ("Traditional Marriage"), Journal of Culture, Society and Development ("Evaluation of the Tiv and Igbo Marriage Systems"), Scielo South Africa ("Nuptial poetry among the Tiv of Nigeria"), and Punch Nigeria coverage of a recent bride-price directive.

--- REVIEW NOTES (remove before publishing) ---
- Exact period/circumstances of yamshe's abolition weren't found in research — confirm with Charles, or reframe as gradual decline rather than a formal abolition if that's more accurate.
- The list of kem items reads like a fairly modernized version of the custom — check against what's actually still exchanged vs. paid as a cash equivalent.
- The bride-price-cap directive (cited figure: N100,000) needs a specific, dated source read in full before being stated as current practice — only a headline was seen during research.
- This is the piece most likely to need direct correction — marriage customs are lived practice with real regional/family variation.
TEXT,
    ],
    [
        'section'     => 'history',
        'subcategory' => 'origins',
        'title'       => 'Who Are the Tiv People?',
        'excerpt'     => 'A general overview of the Tiv people: homeland, language, social structure, and origins — the missing middle ground between a quick reference lookup and the archive’s deeper material.',
        'content'     => <<<'TEXT'
The Tiv are one of Nigeria's largest ethnic groups, numbering in the low millions and concentrated in the Middle Benue Valley of central Nigeria — with smaller populations across Nasarawa, Taraba, Plateau, Cross River, Adamawa, and Kaduna states, the Federal Capital Territory, and parts of Cameroon. Population estimates vary by source and year — one estimate puts Tiv speakers at roughly 5.2 million as of 2024, while broader ethnic-population figures (including non-speakers) run somewhat higher, cited elsewhere as around 3.5% of Nigeria's total population.

Homeland

Benue State — whose official state motto is "Food Basket of the Nation," reflecting the region's role as one of Nigeria's major agricultural centers — is the historic and demographic core of Tiv life. Makurdi is the state capital; Gboko is the traditional seat of Tiv leadership (the seat of the Tor Tiv and the Tiv Traditional Council).

Language

Tiv is a Benue-Congo language within the wider Niger-Congo language family, specifically classified in the Tivoid group. It's written in the Latin script — there is no traditional indigenous script — with the current orthography developed in the 20th century with the help of linguists and missionaries. Tiv is a tonal language, meaning pitch changes word meaning, not just emphasis.

Origins

Tiv oral tradition traces the group's ancestry to a single progenitor, Tiv, and an ancestral homeland called Swem near the Nigeria–Cameroon border, with migration into the Benue Valley driven by population pressure and conflict with neighboring groups over an extended period rather than a single event.

Social structure

Traditionally, Tiv society is organized as a segmentary lineage system — a form of social organization built entirely on patrilineal descent rather than centralized kingship. Tiv trace descent from the ancestor Tiv through his two sons, Ichôngo and Ipusu, and every Tiv person identifies with one branch or the other. Smaller kin-based residential units (an ipaven) cluster into larger territorial communities (a tar), with political alliance or conflict between groups traditionally determined by how closely related their lineages are.

Notably, the Tiv historically had no paramount chief or king — political and legal disputes were handled by lineage elders at the appropriate level of the kinship structure, not by a central authority. The now-familiar office of the Tor Tiv (paramount ruler) is a comparatively recent institution, established by the British colonial administration in 1948 rather than being part of the pre-colonial system.

Economy and daily life

Tiv livelihood has traditionally centered on farming — a fact reflected in Benue State's "Food Basket of the Nation" identity — alongside the broader cultural life documented elsewhere on this site: proverbs, traditional names, festivals, foods, and material culture like anger cloth, the black-and-white striped fabric that functions as a marker of Tiv identity at weddings and other significant occasions.

Sources consulted (secondary/web sources): Wikipedia (Tiv people, Tiv language), Encyclopaedia Britannica, Minority Rights Group ("Tiv in Nigeria"), Joshua Project people-group profile, and WorldAtlas ("Who are the Tiv?").

--- REVIEW NOTES (remove before publishing) ---
- Pick and cite one authoritative, dated source for the population figure rather than a range.
- Confirm the 1948 date and colonial-administration framing for the Tor Tiv office — an important and somewhat sensitive historical detail to get exactly right.
- Confirm "Food Basket of the Nation" as Benue State's actual official motto before stating it as fact.
TEXT,
    ],
];

line();
line('Publishing ' . count($items) . ' long-form content items to content_items (status: draft)');
line(str_repeat('─', 60));

$inserted = 0;
$skippedCount = 0;
foreach ($items as $it) {
    $stmt = $pdo->prepare(
        'SELECT id FROM content_items WHERE section = ? AND subcategory = ? AND title = ? LIMIT 1'
    );
    $stmt->execute([$it['section'], $it['subcategory'], $it['title']]);
    if ($stmt->fetch()) {
        skip("{$it['title']} — already exists, skipped");
        $skippedCount++;
        continue;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO content_items
            (section, subcategory, title, tiv_title, excerpt, tiv_excerpt, content, tiv_content,
             media_file, media_type, is_featured, status, created_by)
         VALUES (?, ?, ?, NULL, ?, NULL, ?, NULL, NULL, \'none\', 0, \'draft\', ?)'
    );
    $stmt->execute([
        $it['section'],
        $it['subcategory'],
        $it['title'],
        $it['excerpt'],
        $it['content'],
        $ADMIN_USER_ID,
    ]);
    ok("{$it['title']} ({$it['section']}/{$it['subcategory']}) — id " . $pdo->lastInsertId());
    $inserted++;
}

line(str_repeat('─', 60));
line("Done. Inserted {$inserted}, skipped {$skippedCount} (already present).");
line('Review and publish via admin/content-items/{section}/{sub} — items are drafts, not yet public.');
line();
