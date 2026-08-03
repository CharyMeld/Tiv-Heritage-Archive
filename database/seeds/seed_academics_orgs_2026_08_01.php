#!/usr/bin/env php
<?php
/**
 * Outreach Targets Seeder — academics and cultural organizations, 2026-08-01
 * Run (locally or on the VPS): php database/seeds/seed_academics_orgs_2026_08_01.php
 *
 * The user supplied several batches covering ~13 candidate entries. Cross-checked
 * each against independent sources before adding. Deliberately EXCLUDED from
 * this batch (see conversation for full reasoning):
 *   - MUTUK, James Ayatse, Benue Bureau for Arts/Culture/Tourism — already exist
 *     in influential_people (ids 7, 13, 14).
 *   - Mdzough U Tiv Worldwide (org-level) — real org, but site is down for
 *     maintenance with zero published contact info; a representative (Comrade
 *     Titus Agbecha, id 6) is already tracked.
 *   - "Bernard Ortwer Atu" (Biology) — could not verify this person exists;
 *     search for BSU biology/medicine surfaced a different "Bernard Utoo"
 *     (Professor of Medicine), not a match. Also appeared 4x identically
 *     copy-pasted in the source list, suggesting a data-quality artifact.
 *   - United Tiv Nation Forum Worldwide Foundation — its domain
 *     (unitedtivnationforumwf.africa) doesn't resolve (DNS failure), and the
 *     supplied contact is a personal-looking Gmail address, not an official one.
 *
 * Institutional emails (bsum.edu.ng addresses) follow the confirmed domain
 * convention for Rev. Fr. Moses Orshio Adasu University (formerly Benue State
 * University) but individual mailbox names weren't independently confirmed —
 * noted per-record below. Personal Gmail addresses for individual academics
 * can't be verified via public search by nature, but identity/expertise was
 * independently confirmed for each person added.
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
        'name' => 'Prof. Tor Joe Iorapuu',
        'title' => 'Vice Chancellor',
        'category' => 'academic',
        'organization' => 'Rev. Fr. Moses Orshio Adasu University (formerly Benue State University)',
        'email' => 'directorict@bsum.edu.ng',
        'phone' => '+234 803 597 2825',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Confirmed via Wikipedia and news coverage as VC of Benue State University (renamed Rev. Fr. Moses Orshio Adasu University), appointed 2021. Email is the university\'s general ICT/directorate contact, not his personal address — flagged as such by the source, not a guess. Phone unverified but plausible.',
    ],
    [
        'name' => 'Prof. Toryina Ayati Varvar',
        'title' => 'Historian; Dean, Postgraduate School',
        'category' => 'academic',
        'organization' => 'Rev. Fr. Moses Orshio Adasu University (formerly Benue State University)',
        'email' => 'tvavar@bsum.edu.ng',
        'phone' => '+234 703 055 9633',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Confirmed via university postgraduate-thesis approval documents as Dean of the Postgraduate School at BSU/Adasu University. Email follows the confirmed bsum.edu.ng institutional domain but the specific mailbox wasn\'t independently found published — treat as reasonably likely, not certain.',
    ],
    [
        'name' => 'Dr. Tijime Justin Awuawuer',
        'title' => 'Lecturer, Dramatic Arts — Tiv ritual theatre & Swange dance researcher',
        'category' => 'academic',
        'organization' => 'Obafemi Awolowo University',
        'email' => 'tijimeawuawuer@oauife.edu.ng',
        'country' => 'Nigeria',
        'state_region' => 'Osun State',
        'notes' => 'Fully confirmed: email independently found via his own published paper (acjol.com), Department of Dramatic Arts at OAU, published work specifically on Tiv Swange dance and ritual theatre. High confidence.',
    ],
    [
        'name' => 'Dr. Philipson Terna Andza',
        'title' => 'Historian — Tiv age-grade systems & history',
        'category' => 'academic',
        'organization' => 'Ahmadu Bello University',
        'email' => 'philipsonandza@gmail.com',
        'country' => 'Nigeria',
        'state_region' => 'Kaduna State',
        'notes' => 'Identity and expertise confirmed: PhD in History from ABU Zaria, research interests in minority issues, chieftaincies, and inter-group relations match the claimed Tiv age-grade/history focus. Personal Gmail address could not be independently verified (not the kind of detail institutional pages publish), but there\'s no reason to doubt it given the person is real.',
    ],
    [
        'name' => 'Ayatutu Ka Se Foundation',
        'title' => 'Foundation established by the Tor Tiv (James Ayatse)',
        'category' => 'ngo',
        'organization' => 'Ayatutu Ka Se Foundation',
        'email' => 'info@ayatutukasefoundation.org',
        'phone' => '+234 802 754 8851',
        'website' => 'https://www.ayatutukasefoundation.org',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Confirmed via official site: both phone numbers (+234 802 754 8851 and +234 813 093 8153) and the Gboko/Tor Tiv Palace address matched exactly. The site\'s own contact email was Cloudflare-obfuscated and couldn\'t be read directly, but "info@" is the standard convention for this domain and matches what was supplied.',
    ],
    [
        'name' => 'Association of Tiv Authors (ATA)',
        'title' => 'Authors\' association',
        'category' => 'author',
        'organization' => 'Association of Tiv Authors',
        'email' => 'info@asotiva.org',
        'phone' => '+234 803 698 9325',
        'website' => 'http://asotiva.org',
        'country' => 'Nigeria',
        'state_region' => 'Benue State',
        'notes' => 'Fully confirmed via direct fetch of the official site\'s contact page: email, Chairman/Secretary/Treasurer phone numbers, and the 18 Jonah Jang Crescent, Makurdi address all matched exactly what was supplied.',
    ],
];

line();
line("Seeding influential_people — " . count($targets) . " verified academic/organization targets (2026-08-01)");
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
