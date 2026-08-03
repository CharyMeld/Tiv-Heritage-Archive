#!/usr/bin/env php
<?php
/**
 * Outreach Targets Seeder — first real batch for the Influential People module
 * Run: php database/seeds/seed_outreach_targets.php
 *
 * Every record here is a real, publicly-documented person/organization found
 * via web research on 2026-07-30 (see OUTREACH_PLAN.md). No email was
 * invented — where a public email wasn't confirmed, it's left null and the
 * notes explain what's known and what still needs manual follow-up.
 *
 * Connects with the app's web-user DB credentials directly, since CLI
 * connections via config/database.php's Database::getInstance() are
 * currently misconfigured (root@127.0.0.1 with an empty password, which
 * this MySQL instance rejects) — a pre-existing issue, not touched here.
 *
 * The password is read from the DB_CLI_PASS environment variable rather
 * than hardcoded, so it's never written to disk or committed to the repo:
 *   DB_CLI_PASS='...' php database/seeds/seed_outreach_targets.php
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

// Only insert into columns that actually exist on this table — the model's
// $fillable list includes a few columns (source_url, source_notes,
// gdpr_basis, consent_date) that may not have a matching migration applied.
$existingColumns = array_column($pdo->query('DESCRIBE influential_people')->fetchAll(), 'Field');

$targets = [
    [
        'name' => 'D.T. Karshima',
        'title' => 'Linguist / Author, New Tiv-English Dictionary (2013)',
        'category' => 'academic',
        'organization' => null,
        'email' => null,
        'website' => null,
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Published "Standardising Tiv orthography: The nasality question" (JOLAN, 2012) and a New Tiv-English Dictionary (2013) — direct topical match for the archive\'s dictionary. No public contact email located in initial research; needs manual lookup via ResearchGate/Academia.edu or JOLAN\'s author listings.',
        'source_notes' => 'Found via web search of published Tiv linguistics papers, 2026-07-30.',
    ],
    [
        'name' => 'Benue State University — Department of Linguistics',
        'title' => 'Department',
        'category' => 'academic',
        'organization' => 'Benue State University, Makurdi',
        'email' => null,
        'website' => 'https://bsum.edu.ng/w3/linguistics.php',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Offers Tiv as a language elective, publishes Tiv orthography research. Also hosts the IJOTIL journal (separate entry below). Site was unreachable during a follow-up check (2026-07-30) — may be transient. No specific HOD name/email confirmed; needs manual follow-up via the department page or general university contact.',
        'source_notes' => 'Found via web search, 2026-07-30.',
    ],
    [
        'name' => 'International Journal of the Tiv Language, Education, History and Culture (IJOTIL)',
        'title' => 'Academic journal',
        'category' => 'academic',
        'organization' => 'Benue State University',
        'email' => null,
        'website' => 'https://www.bsum.edu.ng/journals/ijotil/',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'BSU-published academic journal literally focused on Tiv language/education/history/culture — very high-value topical match for citation/partnership. No specific editor name/email confirmed in initial research; high priority for manual follow-up.',
        'source_notes' => 'Found via web search, 2026-07-30.',
    ],
    [
        'name' => 'Comrade Titus Agbecha',
        'title' => 'President General, Mzough U Tiv Worldwide',
        'category' => 'ngo',
        'organization' => 'Mzough U Tiv (MUT)',
        'email' => null,
        'website' => null,
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'President General of the main worldwide Tiv socio-cultural union, as reported by Blueprint Newspapers. No personal contact confirmed; needs manual lookup via MUT\'s official channels or affiliated diaspora branches (e.g. MUTUK).',
        'source_notes' => 'Found via Blueprint Newspapers article, 2026-07-30.',
    ],
    [
        'name' => 'Mzough U Tiv UK (MUTUK)',
        'title' => 'UK diaspora branch',
        'category' => 'diaspora',
        'organization' => 'Mzough U Tiv (MUT)',
        'email' => null,
        'website' => 'https://www.mutuk.org/contact',
        'country' => 'United Kingdom',
        'state_region' => null,
        'notes' => 'UK diaspora branch of the main Tiv cultural union. Confirmed public contact form at mutuk.org/contact — use the form directly rather than a guessed email address.',
        'source_notes' => 'Confirmed via mutuk.org, 2026-07-30.',
    ],
    [
        'name' => 'NKST Church — Abuja Branch (Universal Reformed Christian Church)',
        'title' => 'Church branch office',
        'category' => 'clergy',
        'organization' => 'NKST (Universal Reformed Christian Church), HQ Mkar, Benue State',
        'email' => 'info@nkstabuja.org',
        'phone' => '08137673156',
        'website' => 'https://nkstabuja.org/',
        'country' => 'Nigeria',
        'state_region' => 'FCT Abuja',
        'notes' => 'NKST is overwhelmingly Tiv (160,000+ members, HQ at Mkar, Gboko LGA, Benue State) — highly relevant given the archive\'s Tiv Bible translation feature. This is the Abuja branch\'s confirmed public contact; national HQ contact not yet confirmed.',
        'source_notes' => 'Email/phone confirmed via nkstabuja.org, 2026-07-30.',
    ],
    [
        'name' => 'Rapizo (Raphael Akanaba)',
        'title' => 'Musician',
        'category' => 'musician',
        'organization' => null,
        'email' => null,
        'website' => null,
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Contemporary musician blending Tiv folk music with modern styles, from Benue State (featured on onebenue.com "Top Benue Musicians"). No direct contact found in initial research; needs manual lookup via social media or management.',
        'source_notes' => 'Found via onebenue.com feature, 2026-07-30.',
    ],
];

line();
line("Seeding influential_people — " . count($targets) . " researched targets");
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
