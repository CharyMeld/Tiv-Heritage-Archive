#!/usr/bin/env php
<?php
/**
 * Persona-specific outreach email templates, per OUTREACH_PLAN.md Tier 2:
 * "campaign templates personalized per person's field (linguist ->
 * dictionary/grammar angle; pastor -> Bible translation angle; politician
 * -> cultural preservation angle)."
 *
 * Covers the categories actually represented among the 14 seeded outreach
 * targets (academic, clergy, traditional_leader, diaspora, musician) plus
 * politician/business_leader from the plan's original wording, kept
 * deliberately non-partisan (civic/legacy framing, not policy).
 *
 * Inserted directly, not sent — same as seed_email_template_2026_07_30.php.
 * body_html is INNER content only; OutreachMailer::wrapHtml() adds
 * header/footer/unsubscribe chrome at send time.
 *
 * Run: DB_CLI_PASS='...' php database/seeds/seed_email_templates_personas_2026_07_30.php
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

$ADMIN_USER_ID = 1;

$signature = "<p>Warm regards,<br>Charles Ikyese<br>{{site_name}}</p>";

$templates = [
    [
        'name'    => 'Academic / Linguist — Dictionary & Documentation Angle',
        'subject' => 'A digital Tiv dictionary and documentation resource — {{site_name}}',
        'body'    => <<<HTML
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese. Given your work as {{title}}, I wanted to bring a project to your attention: <strong>{{site_name}}</strong> ({{site_url}}), a free, public digital archive documenting the Tiv language.</p>

<p>The core of it is a Tiv-English dictionary with IPA transcription, tone marking, part-of-speech tagging, and usage examples, alongside a full alphabet and tonal-system reference, and a growing set of grammar notes. It's built to be a working reference for exactly the kind of research and teaching you're engaged in &mdash; and it's actively maintained, not a static archive.</p>

<p>I'd value your perspective on it &mdash; both as a potential citation resource, and as someone whose corrections or additions would genuinely improve its accuracy. If anything looks off linguistically, I'd rather hear it from you than leave it wrong.</p>

<p>Happy to share more detail, or set up a call if useful.</p>
HTML,
    ],
    [
        'name'    => 'Clergy — Bible Translation Angle',
        'subject' => 'A Tiv Bible translation and language resource — {{site_name}}',
        'body'    => <<<HTML
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese, and I'm writing to share a resource that may be useful to your ministry: <strong>{{site_name}}</strong> ({{site_url}}), a free, public digital archive built to preserve the Tiv language and culture.</p>

<p>Alongside a full Tiv-English dictionary and alphabet/tone guide, the archive includes a Tiv Bible translation feature, built specifically because language preservation and scripture access in Tiv go hand in hand for so many congregations. Given your role as {{title}}, I thought this might be a resource worth knowing about &mdash; whether for your own study, or to share with members of your congregation looking to read or teach in Tiv.</p>

<p>It's entirely free and will remain so. I'd welcome any feedback, corrections, or ideas for how it could better serve the church community.</p>

<p>Thank you for the work you do.</p>
HTML,
    ],
    [
        'name'    => 'Traditional Leader — Cultural Heritage & Legacy Angle',
        'subject' => 'Preserving Tiv cultural heritage for the next generation — {{site_name}}',
        'body'    => <<<HTML
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese. I'm writing to introduce <strong>{{site_name}}</strong> ({{site_url}}), a free, public digital archive built to document and preserve Tiv language and culture for future generations &mdash; proverbs, traditional names, festivals, marriage customs, historical figures, and the Tiv language itself.</p>

<p>As {{title}}, you hold a position of real weight in how Tiv cultural knowledge is carried forward, and I wanted this project to be on your radar. Much of what's in the archive today comes from published research and community contribution, but there is no substitute for guidance from those closest to the tradition itself &mdash; and I would be honored to have your input, correction, or blessing on the work.</p>

<p>I'd welcome the opportunity to share more about the archive, or to visit and discuss it in person if that would be preferred.</p>

<p>With respect,</p>
HTML,
    ],
    [
        'name'    => 'Diaspora — Connecting the Diaspora Angle',
        'subject' => 'A free way to keep Tiv language and culture alive abroad — {{site_name}}',
        'body'    => <<<HTML
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese. I wanted to introduce <strong>{{site_name}}</strong> ({{site_url}}) to you and your community: a free, public digital archive of the Tiv language and culture, built in part for Tiv families abroad who want their children to stay connected to home.</p>

<p>It includes a Tiv-English dictionary, the alphabet and tone system, a translation tool, traditional proverbs and names, and cultural documentation &mdash; all free, and accessible from anywhere. Given the work {{title}} does connecting the diaspora, I thought this might be a resource worth sharing with your members, especially families raising a second generation who may not have regular exposure to spoken Tiv.</p>

<p>I'd be glad to talk more, provide materials for your community, or hear any suggestions on what diaspora families would find most useful.</p>

<p>Thank you for the work you do keeping the community connected.</p>
HTML,
    ],
    [
        'name'    => 'Musician / Author — Creative & Cultural Celebration Angle',
        'subject' => 'Celebrating Tiv language and culture through {{site_name}}',
        'body'    => <<<HTML
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese, and I'm a fan of the way your work as {{title}} carries Tiv culture forward. I wanted to introduce you to <strong>{{site_name}}</strong> ({{site_url}}), a free, public digital archive documenting the Tiv language &mdash; dictionary, proverbs, traditional names, festivals, and more.</p>

<p>Creative work like yours and documentation projects like this one are, I think, doing the same job from different angles: making sure Tiv culture stays alive and visible. I'd love for the archive to be a resource you or your audience might draw on &mdash; whether for lyrics, storytelling, or just reconnecting with specific words and phrases &mdash; and I'd be glad to feature or link to your work from the archive if that's of interest.</p>

<p>Happy to share more, or just say thank you for what you do.</p>
HTML,
    ],
    [
        'name'    => 'Politician / Public Office — Civic Legacy Angle',
        'subject' => 'A civic-legacy project worth knowing about — {{site_name}}',
        'body'    => <<<HTML
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese. I wanted to bring a nonpartisan cultural-preservation project to your attention: <strong>{{site_name}}</strong> ({{site_url}}), a free, public digital archive documenting the Tiv language and culture for anyone, anywhere, at no cost.</p>

<p>Given your role as {{title}}, I thought this might be relevant to your work supporting Tiv communities and cultural institutions &mdash; whether as a resource to point constituents to, a project worth public recognition, or simply something worth being aware of as part of the cultural landscape you represent.</p>

<p>I'd welcome the chance to share more detail, and any support &mdash; formal or informal &mdash; would help the archive reach more of the people it's meant for.</p>

<p>Thank you for your time and for your service to the Tiv community.</p>
HTML,
    ],
];

line();
line('Seeding ' . count($templates) . ' persona-specific email templates');
line(str_repeat('─', 60));

$inserted = 0;
$skippedCount = 0;
foreach ($templates as $t) {
    $stmt = $pdo->prepare('SELECT id FROM email_templates WHERE name = ? LIMIT 1');
    $stmt->execute([$t['name']]);
    if ($stmt->fetch()) {
        skip("{$t['name']} — already exists, skipped");
        $skippedCount++;
        continue;
    }

    $body = $t['body'] . "\n\n" . $signature;

    $stmt = $pdo->prepare(
        'INSERT INTO email_templates (name, subject, body_html, created_by) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$t['name'], $t['subject'], $body, $ADMIN_USER_ID]);
    ok("{$t['name']} — id " . $pdo->lastInsertId());
    $inserted++;
}

line(str_repeat('─', 60));
line("Done. Inserted {$inserted}, skipped {$skippedCount} (already present).");
line();
