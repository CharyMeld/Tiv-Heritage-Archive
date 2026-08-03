#!/usr/bin/env php
<?php
/**
 * Outreach Targets Seeder — second real batch (Tier 2 + Tier 4 of OUTREACH_PLAN.md)
 * Run: php database/seeds/seed_outreach_targets_2026_07_30_b.php
 *
 * Every record here is a real, publicly-documented person/organization found
 * via web research on 2026-07-30 (see OUTREACH_PLAN.md, Tiers 2 & 4). No
 * email was invented — where a public email wasn't confirmed with high
 * confidence, it's left null and the notes explain what's known and what
 * still needs manual follow-up. (One near-miss: Oye Taiwo's email domain
 * was reported as "mail1.ui.edu.ng" by search results but no specific
 * address was confirmed, so it was deliberately left null rather than
 * guessed — a wrong guess would bounce or misdirect.)
 *
 * Same connection pattern as seed_outreach_targets.php — see that file for
 * why this bypasses Database::getInstance() and reads DB_CLI_PASS from env:
 *   DB_CLI_PASS='...' php database/seeds/seed_outreach_targets_2026_07_30_b.php
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

$existingColumns = array_column($pdo->query('DESCRIBE influential_people')->fetchAll(), 'Field');

$targets = [
    // ── Tier 2: academics & diaspora ────────────────────────────────────
    [
        'name' => 'Prof. Oye Taiwo',
        'title' => 'Professor of Linguistics and African Languages, University of Ibadan',
        'category' => 'academic',
        'organization' => 'University of Ibadan — Department of Linguistics and African Languages',
        'email' => null,
        'website' => 'https://www.researchgate.net/profile/Oye-Taiwo',
        'country' => 'Nigeria',
        'state_region' => 'Oyo State',
        'notes' => 'Published "Argument Movement in the Tiv Language" (Lingue e Linguaggi), analyzing Tiv as a syntactically ergative language — direct academic engagement with Tiv grammar. A university mail domain (mail1.ui.edu.ng) surfaced in search results but no specific address was confirmed, so email was left blank rather than guessed. Needs manual lookup via ResearchGate or UI\'s Linguistics dept. page.',
        'source_notes' => 'Found via web search of published Tiv syntax papers, 2026-07-30.',
    ],
    [
        'name' => 'University of Jos — Department of Linguistics and Nigerian Languages',
        'title' => 'Department',
        'category' => 'academic',
        'organization' => 'University of Jos',
        'email' => null,
        'website' => 'https://www.unijos.edu.ng/department-linguistics-and-nigerian-languages',
        'country' => 'Nigeria',
        'state_region' => 'Plateau State',
        'notes' => 'Department has published Tiv-focused research (e.g. Ignatius Iornenge Usar, "English and Hausa Loan Words in Tiv," AJLLS journal). No specific staff contact email confirmed in initial research; needs manual follow-up via the department page or general university contact.',
        'source_notes' => 'Found via web search of Tiv linguistics papers, 2026-07-30.',
    ],
    [
        'name' => 'Mutual Union of the Tiv in America (MUTA)',
        'title' => 'US diaspora association',
        'category' => 'diaspora',
        'organization' => 'MUTA Inc.',
        'email' => null,
        'website' => 'https://muta.org/',
        'country' => 'United States',
        'state_region' => 'Georgia',
        'notes' => 'Founded 1993 in Grand Rapids, Michigan — the original Tiv diaspora association in the US, now based in Powder Springs, GA, with chapters across the US and Canada. muta.org failed a live DNS check during verification (2026-07-30) — may be temporarily down; try again before emailing, or use their Facebook page (facebook.com/mutainc) as a fallback contact channel. No direct personal email confirmed.',
        'source_notes' => 'Found via web search (GiveGab/Candid nonprofit profiles, Facebook), 2026-07-30.',
    ],
    [
        'name' => 'HRM Prof. James Ayatse (Tor Tiv V)',
        'title' => 'Paramount Ruler of the Tiv Nation (Tor Tiv), Chairman of the Tiv Traditional Council',
        'category' => 'traditional_leader',
        'organization' => 'Tiv Traditional Council (Ijirtamen), Gboko, Benue State',
        'email' => null,
        'website' => null,
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Elected Tor Tiv (paramount ruler of the Tiv people worldwide) December 2016, crowned March 2017. No official council website or direct contact found in initial research — highest-profile Tiv cultural figure, but outreach would need to go through the Benue State Government protocol office or the Tiv Traditional Council\'s physical office in Gboko rather than email.',
        'source_notes' => 'Found via web search (Wikipedia, Nigerian news coverage), 2026-07-30.',
    ],
    // ── Tier 4: directories & local institutions ────────────────────────
    [
        'name' => 'Benue State Bureau for Arts, Culture, and Tourism (BACT)',
        'title' => 'State government bureau',
        'category' => 'other',
        'organization' => 'Benue State Government',
        'email' => 'kreativ.benue@gmail.com',
        'phone' => '+2348065491970',
        'website' => 'https://bact.benuestate.gov.ng/',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Unified cultural governance body for Benue State (established 2023), successor to the former Ministry of Information, Culture and Tourism\'s culture functions. Confirmed public contact email and phone via bact.benuestate.gov.ng.',
        'source_notes' => 'Confirmed via bact.benuestate.gov.ng contact page, 2026-07-30.',
    ],
    [
        'name' => 'National Institute for Nigerian Languages (NINLAN)',
        'title' => 'Inter-University Centre for Nigerian Language Studies',
        'category' => 'academic',
        'organization' => 'National Universities Commission (NUC)',
        'email' => 'info@ninlan.edu.ng',
        'phone' => '+2348169863579',
        'website' => 'https://www.ninlan.edu.ng/',
        'country' => 'Nigeria',
        'state_region' => 'Abia State',
        'notes' => 'Autonomous, NUC-regulated institute dedicated to Nigerian language research and development — direct topical match for the archive\'s language-documentation mission. Confirmed public contact email and phone via ninlan.edu.ng. Located in Aba, Abia State.',
        'source_notes' => 'Confirmed via ninlan.edu.ng contact page, 2026-07-30.',
    ],
    [
        'name' => 'National Institute for Cultural Orientation (NICO)',
        'title' => 'Federal cultural education & heritage agency',
        'category' => 'other',
        'organization' => 'Federal Government of Nigeria',
        'email' => 'info@nico.gov.ng',
        'phone' => null,
        'website' => 'https://nico.gov.ng/',
        'country' => 'Nigeria',
        'state_region' => 'FCT Abuja',
        'notes' => 'Nigeria\'s premier federal agency for cultural education, training, and heritage promotion. Confirmed general contact email via nico.gov.ng/contact-us/; the same page also lists per-zone emails (North-Central zone would cover Benue State) if a more targeted contact is wanted later.',
        'source_notes' => 'Confirmed via nico.gov.ng/contact-us/, 2026-07-30.',
    ],
];

line();
line("Seeding influential_people — " . count($targets) . " researched targets (Tier 2 + Tier 4)");
line(str_repeat('─', 60));

$inserted = 0;
$skippedCount = 0;
foreach ($targets as $t) {
    $stmt = $pdo->prepare('SELECT id FROM influential_people WHERE name = ? LIMIT 1');
    $stmt->execute([$t['name']]);
    if ($stmt->fetch()) {
        skip("{$t['name']} — already exists, skipped");
        $skippedCount++;
        continue;
    }

    $data = array_intersect_key($t, array_flip($existingColumns));
    $data['contact_status'] = 'not_contacted';
    $data['consent_status'] = 'unknown';
    if (in_array('gdpr_basis', $existingColumns, true)) {
        $data['gdpr_basis'] = 'legitimate_interest';
    }

    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    $sql = sprintf(
        'INSERT INTO influential_people (%s) VALUES (%s)',
        implode(', ', $fields),
        implode(', ', $placeholders)
    );
    $pdo->prepare($sql)->execute(array_values($data));
    ok("{$t['name']} ({$t['category']})");
    $inserted++;
}

line(str_repeat('─', 60));
line("Done. Inserted {$inserted}, skipped {$skippedCount} (already present).");
line();
