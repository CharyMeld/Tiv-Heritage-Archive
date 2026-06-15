#!/usr/bin/env php
<?php
/**
 * Team Members Seeder
 * Run: php database/seed_team.php
 *
 * Seeds the team_members table and prints a full outcome report.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

$db = Database::getInstance();

// ── Helpers ────────────────────────────────────────────────────────────────

function line(string $text = ''): void  { echo $text . PHP_EOL; }
function head(string $text): void       { line(); line("  \e[1;34m» {$text}\e[0m"); line(str_repeat('─', 60)); }
function ok(string $text): void         { line("  \e[0;32m✔\e[0m  {$text}"); }
function skip(string $text): void       { line("  \e[0;33m–\e[0m  {$text}"); }
function info(string $text): void       { line("  \e[0;36m·\e[0m  {$text}"); }
function fail(string $text): void       { line("  \e[0;31m✘\e[0m  {$text}"); }
function bar(int $pct, int $width = 30): string {
    $filled = (int) round($pct / 100 * $width);
    return '[' . str_repeat('█', $filled) . str_repeat('░', $width - $filled) . '] ' . $pct . '%';
}

// ── Content stats helper ────────────────────────────────────────────────────

function contentStats(PDO $db, ?int $userId): array
{
    $tables = [
        'Names'     => ['tiv_names',      'created_by'],
        'Proverbs'  => ['tiv_proverbs',   'created_by'],
        'Plants'    => ['tiv_plants',      'created_by'],
        'Festivals' => ['tiv_festivals',   'created_by'],
        'Foods'     => ['tiv_foods',       'created_by'],
        'Words'     => ['daily_words',     'created_by'],
        'Videos'    => ['learning_videos', 'created_by'],
    ];

    $breakdown   = [];
    $userTotal   = 0;
    $grandTotal  = 0;

    foreach ($tables as $label => [$table, $col]) {
        $grand = (int) $db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        $grandTotal += $grand;

        if ($userId !== null) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$col} = ?");
            $stmt->execute([$userId]);
            $byUser = (int) $stmt->fetchColumn();
        } else {
            $byUser = 0;
        }

        $userTotal += $byUser;
        $breakdown[$label] = ['user' => $byUser, 'total' => $grand];
    }

    $pct = $grandTotal > 0 ? (int) round($userTotal / $grandTotal * 100) : 0;

    return compact('breakdown', 'userTotal', 'grandTotal', 'pct');
}

// ── Pre-query DB stats to build honest, data-driven contribution highlights ─

function buildContributions(PDO $db, ?int $userId): array
{
    if ($userId === null) return [];

    $stats = contentStats($db, $userId);
    $bd    = $stats['breakdown'];
    $items = [];

    foreach ($bd as $label => $counts) {
        if ($counts['user'] > 0) {
            $items[] = "Added {$counts['user']} {$label}";
        }
    }

    return $items;
}

// ── Seed data ──────────────────────────────────────────────────────────────

// Resolve Charles's user_id first so contributions can be built from real DB data
$charlesUserId = null;
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute(['admin@tivarchive.com']);
$charlesUser = $stmt->fetch();
if ($charlesUser) {
    $charlesUserId = (int) $charlesUser['id'];
}

$seeds = [
    [
        'user_lookup_email' => 'admin@tivarchive.com',
        'name'         => 'Charles Ihungwa Ikyese',
        'role'         => 'Lead Developer',
        'category'     => 'developer',
        'short_bio'    => 'Designed and built the Tiv Archive platform — architecture, backend, database, and frontend.',
        'full_bio'     => 'Charles Ikyese is the lead developer of the Tiv Archive. He designed and built the entire platform — from database schema and backend logic to the frontend UI and admin dashboard. His work is driven by a deep commitment to preserving Tiv cultural heritage through technology.',
        'contributions' => buildContributions($db, $charlesUserId),
        'sort_order' => 0,
        'is_active'  => 1,
    ],
];

// ── Run seeder ─────────────────────────────────────────────────────────────

line();
line("  \e[1;37m╔══════════════════════════════════════════════════════╗\e[0m");
line("  \e[1;37m║         TIV ARCHIVE — TEAM MEMBERS SEEDER           ║\e[0m");
line("  \e[1;37m╚══════════════════════════════════════════════════════╝\e[0m");

$inserted = 0;
$skipped  = 0;
$results  = [];

head('Seeding team_members');

foreach ($seeds as $seed) {
    // Resolve user account
    $userId = null;
    if (!empty($seed['user_lookup_email'])) {
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$seed['user_lookup_email']]);
        $user = $stmt->fetch();
        if ($user) {
            $userId = (int) $user['id'];
            info("Linked to user: {$user['name']} (ID {$userId})");
        } else {
            fail("User not found: {$seed['user_lookup_email']}");
        }
    }

    // Check if already exists
    $stmt = $db->prepare("SELECT id FROM team_members WHERE name = ? LIMIT 1");
    $stmt->execute([$seed['name']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing record
        $stmt = $db->prepare("
            UPDATE team_members SET
                user_id       = ?,
                role          = ?,
                category      = ?,
                short_bio     = ?,
                full_bio      = ?,
                contributions = ?,
                sort_order    = ?,
                is_active     = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $userId,
            $seed['role'],
            $seed['category'],
            $seed['short_bio'],
            $seed['full_bio'],
            json_encode($seed['contributions']),
            $seed['sort_order'],
            $seed['is_active'],
            $existing['id'],
        ]);
        skip("Already exists — updated: {$seed['name']} (ID {$existing['id']})");
        $skipped++;
        $memberId = (int) $existing['id'];
    } else {
        // Insert new record
        $stmt = $db->prepare("
            INSERT INTO team_members
                (user_id, name, role, category, short_bio, full_bio, contributions, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $seed['name'],
            $seed['role'],
            $seed['category'],
            $seed['short_bio'],
            $seed['full_bio'],
            json_encode($seed['contributions']),
            $seed['sort_order'],
            $seed['is_active'],
        ]);
        $memberId = (int) $db->lastInsertId();
        ok("Inserted: {$seed['name']} (ID {$memberId})");
        $inserted++;
    }

    $results[] = ['seed' => $seed, 'id' => $memberId, 'user_id' => $userId];
}

// ── Contribution report ────────────────────────────────────────────────────

head('Contribution Report');

// Grand totals across all tables
$grandStmt = $db->query("
    SELECT
      (SELECT COUNT(*) FROM tiv_names)       +
      (SELECT COUNT(*) FROM tiv_proverbs)    +
      (SELECT COUNT(*) FROM tiv_plants)      +
      (SELECT COUNT(*) FROM tiv_festivals)   +
      (SELECT COUNT(*) FROM tiv_foods)       +
      (SELECT COUNT(*) FROM daily_words)     +
      (SELECT COUNT(*) FROM learning_videos) AS total
");
$grandTotal = (int) $grandStmt->fetchColumn();
info("Total content in archive: {$grandTotal} items");

line();

foreach ($results as $r) {
    $seed   = $r['seed'];
    $uid    = $r['user_id'];
    $stats  = contentStats($db, $uid);

    line("  \e[1;37m{$seed['name']}\e[0m  ·  {$seed['role']}  ·  " . ucfirst($seed['category']));
    line();

    // Contribution bar
    line("  Contribution Rating:");
    line("  " . bar($stats['pct']));
    line("  {$stats['userTotal']} of {$stats['grandTotal']} total items ({$stats['pct']}%)");
    line();

    // Per-category breakdown
    line("  Category Breakdown:");
    $labelPad = max(array_map('strlen', array_keys($stats['breakdown'])));
    foreach ($stats['breakdown'] as $cat => $counts) {
        $catBar = $counts['total'] > 0
            ? bar((int) round($counts['user'] / $counts['total'] * 100), 20)
            : '[' . str_repeat('░', 20) . ']   0%';
        $label = str_pad($cat, $labelPad);
        $byUser = str_pad($counts['user'], 4, ' ', STR_PAD_LEFT);
        line("  \e[0;36m{$label}\e[0m  {$byUser} / {$counts['total']}  {$catBar}");
    }
    line();

    // Manual highlights
    if (!empty($seed['contributions'])) {
        line("  Highlights:");
        foreach ($seed['contributions'] as $c) {
            line("    \e[0;33m✦\e[0m {$c}");
        }
    }

    line();
    line(str_repeat('─', 60));
}

// ── Full team summary ──────────────────────────────────────────────────────

head('Team Members in Database');

$all = $db->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC")->fetchAll();

$colW = [4, 30, 28, 14, 8, 8];
$hdr  = sprintf(
    "  \e[1m%-{$colW[0]}s  %-{$colW[1]}s  %-{$colW[2]}s  %-{$colW[3]}s  %{$colW[4]}s  %-{$colW[5]}s\e[0m",
    'ID', 'Name', 'Role', 'Category', 'User', 'Active'
);
line($hdr);
line('  ' . str_repeat('─', array_sum($colW) + count($colW) * 2));

foreach ($all as $m) {
    $active = $m['is_active'] ? "\e[0;32mYes\e[0m" : "\e[0;31mNo\e[0m ";
    line(sprintf(
        "  %-{$colW[0]}s  %-{$colW[1]}s  %-{$colW[2]}s  %-{$colW[3]}s  %{$colW[4]}s  %s",
        $m['id'],
        mb_substr($m['name'], 0, $colW[1]),
        mb_substr($m['role'], 0, $colW[2]),
        $m['category'],
        $m['user_id'] ?? '—',
        $active
    ));
}

// ── Summary ────────────────────────────────────────────────────────────────

head('Summary');
ok("Inserted : {$inserted}");
skip("Updated  : {$skipped}");
info("Total    : " . count($all) . " member(s) in team_members table");
line();
line("  \e[1;32mSeeder complete.\e[0m");
line();
