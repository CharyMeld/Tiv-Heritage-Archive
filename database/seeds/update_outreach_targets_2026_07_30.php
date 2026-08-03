#!/usr/bin/env php
<?php
/**
 * One-off update: fills in confirmed contact details found in a follow-up
 * research pass on the batch from seed_outreach_targets.php.
 * Run: php database/seeds/update_outreach_targets_2026_07_30.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

$db = Database::getInstance();

$updates = [
    [
        'name' => 'Benue State University — Department of Linguistics',
        'title' => 'HOD (Languages & Linguistics): Dr. Vanessa Adzer',
        'email' => 'cihom@bsum.edu.ng',
        'notes' => "HOD confirmed via BSU's own Heads of Departments page (bsum.edu.ng/w3/hods.php): Dr. Vanessa Adzer, Languages & Linguistics. Email is a role-based departmental address, not necessarily a direct personal one.",
    ],
    [
        'name' => 'Mzough U Tiv UK (MUTUK)',
        'email' => 'info@mutuk.org',
        'notes' => 'Confirmed via mutuk.org/contact, 2026-07-30 follow-up research.',
    ],
    [
        'name' => 'Comrade Titus Agbecha',
        'website' => 'https://mdzoughutiv.org/',
        'notes' => 'President General of Mdzough U Tiv Worldwide, as reported by Blueprint Newspapers. Official site (mdzoughutiv.org) confirmed but currently shows a maintenance/coming-soon page with no contact info. No personal email found; re-check the official site once it is live, or try affiliated diaspora branches.',
    ],
    [
        'name' => 'Rapizo (Raphael Akanaba)',
        'notes' => "Contemporary Tiv folk-fusion musician, Benue State (per onebenue.com). Follow-up research (2026-07-30) found an Instagram (@raphaelmusic), a Linktree, and a possible personal site (mynameisraphael.com) — but none confirmed to actually belong to this specific artist (the site was built by an Italian production company, suggesting a possible name-match mix-up), so no email added. Needs direct manual verification via his own official channels before any contact attempt.",
    ],
];

foreach ($updates as $u) {
    $name = $u['name'];
    unset($u['name']);
    $sets = [];
    $params = [];
    foreach ($u as $col => $val) {
        $sets[] = "{$col} = ?";
        $params[] = $val;
    }
    $params[] = $name;
    $sql = 'UPDATE influential_people SET ' . implode(', ', $sets) . ' WHERE name = ?';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo ($stmt->rowCount() ? 'Updated: ' : 'No match: ') . $name . PHP_EOL;
}
