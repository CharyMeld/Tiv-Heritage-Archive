#!/usr/bin/env php
<?php
/**
 * Outreach Targets Seeder — Benue State Government leadership, 2026-08-01
 * Run (locally or on the VPS): php database/seeds/seed_benue_govt_2026_08_01.php
 *
 * The user supplied a candidate list of 9 officials with role-pattern emails
 * (cos@, ssg@, hos@, pps@, cps@, accountantgeneral@, attorneygeneral@,
 * deputygovernor@benuestate.gov.ng). Cross-checked against the official
 * https://benuestate.gov.ng/team/ roster: 7 of 9 were guessed generic
 * addresses that don't match the real personalized ones the government
 * actually publishes (only the Governor's happened to be correct by
 * coincidence). The real addresses below come from that team page.
 *
 * The Acting Head of Service (Dr. Ihu Eunice Ogbenyi, appointed June 2026 —
 * confirmed via Daily Post and the government's own X account) is NOT
 * included: no email for her is published anywhere yet, and per the
 * project's standing rule, a wrong guess would bounce or misdirect rather
 * than just fail loudly, so she's omitted until a real address surfaces.
 * "contact@benuestate.gov.ng" is likewise omitted — not found on the
 * official site (only info@ is confirmed), and redundant with it anyway.
 *
 * Connects as root with an empty password. On the VPS, root MySQL access
 * is auth_socket-based (matches the connecting OS user, not a password) and
 * only works over the unix socket — TCP (127.0.0.1, what
 * Database::getInstance() uses under CLI) gets "Access denied" there, which
 * is what the two 2026-07-30 seed scripts' comments were describing. Local
 * dev (XAMPP) is the opposite: it has no matching unix socket path, so it
 * needs TCP. Try the socket first (production), fall back to TCP (local).
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/security.php';

try {
    $pdo = new PDO(
        'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=tiv_archive;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=tiv_archive;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

function line(string $text = ''): void { echo $text . PHP_EOL; }
function ok(string $text): void        { line("  \e[0;32m✔\e[0m  {$text}"); }
function skip(string $text): void      { line("  \e[0;33m–\e[0m  {$text}"); }

$existingColumns = array_column($pdo->query('DESCRIBE influential_people')->fetchAll(), 'Field');

$targets = [
    [
        'name' => 'Hyacinth Iormem Alia',
        'title' => 'Executive Governor of Benue State',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'governoralia@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Confirmed via official team roster (benuestate.gov.ng/team/), 2026-08-01. Matches the address the user originally supplied.',
    ],
    [
        'name' => 'Sam Ode',
        'title' => 'Deputy Governor of Benue State',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'ode.sam@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Real address from the official team roster. User-supplied "deputygovernor@benuestate.gov.ng" was an unverified guess and does not appear on the official site.',
    ],
    [
        'name' => 'Deborah Aber',
        'title' => 'Secretary to the State Government (SSG)',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'aber.deborah@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Official roster lists her as "Aber Serumun Deborah" — the user\'s "Deaconess Selemun Aber" is a name-order/spelling variant of the same person, confirmed as Benue\'s first female SSG via press coverage. User-supplied "ssg@benuestate.gov.ng" was an unverified guess.',
    ],
    [
        'name' => 'Moses Atagher',
        'title' => 'Chief of Staff',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'atagher.moses@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Real address from the official team roster (user gave "Barr. Moses Ityorumun Atagher" — fuller name variant, same person). User-supplied "cos@benuestate.gov.ng" was an unverified guess.',
    ],
    [
        'name' => 'Theresa Nyitse',
        'title' => 'Accountant General',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'nyitse.theresa@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Real address from the official team roster. User-supplied "accountantgeneral@benuestate.gov.ng" was an unverified guess.',
    ],
    [
        'name' => 'Fidelis Mnyim',
        'title' => 'Attorney General & Commissioner for Justice',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'mnyim.fidelis@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Real address from the official team roster. User-supplied "attorneygeneral@benuestate.gov.ng" was an unverified guess.',
    ],
    [
        'name' => 'Emmanuel Chenge',
        'title' => 'Principal Private Secretary (PPS)',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'echenge@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Real address from the official team roster. User-supplied "pps@benuestate.gov.ng" was an unverified guess.',
    ],
    [
        'name' => 'Tersoo Kula',
        'title' => 'Chief Press Secretary (CPS)',
        'category' => 'politician',
        'organization' => 'Benue State Government',
        'email' => 'kula.cps@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Official roster lists him as "Kula Tersoo" (name order swapped) — same person. User-supplied "cps@benuestate.gov.ng" was an unverified guess.',
    ],
    [
        'name' => 'Benue State Government (General Contact)',
        'title' => 'General Enquiries',
        'category' => 'other',
        'organization' => 'Benue State Government',
        'email' => 'info@benuestate.gov.ng',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Confirmed general contact address on the official Contact page (benuestate.gov.ng/contact/). "contact@benuestate.gov.ng", also supplied by the user, was not found anywhere on the official site and is omitted.',
    ],
];

line();
line("Seeding influential_people — " . count($targets) . " Benue State Government targets (verified 2026-08-01)");
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
    $data['unsubscribe_token'] = Security::generateToken(32);

    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    $sql = sprintf(
        'INSERT INTO influential_people (%s) VALUES (%s)',
        implode(', ', $fields),
        implode(', ', $placeholders)
    );
    $pdo->prepare($sql)->execute(array_values($data));
    ok("{$t['name']} ({$t['category']}) — {$t['email']}");
    $inserted++;
}

line(str_repeat('─', 60));
line("Done. Inserted {$inserted}, skipped {$skippedCount} (already present).");
line();
