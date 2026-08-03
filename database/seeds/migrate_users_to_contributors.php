<?php
/**
 * Migrates existing `users` rows into the Community Members module so
 * they can use the contributor portal without re-registering.
 *
 * For every user without an existing community_members row:
 *   - creates one (member_type='contributor', is_active=1, no
 *     application_id — there's no real application behind it)
 *   - upgrades role 'user' -> 'contributor' ONLY. Never touches
 *     'moderator'/'admin' accounts — they already have equal-or-greater
 *     access, and this must not silently downgrade anyone.
 *
 * Idempotent — skips any user who already has a community_members row.
 *
 * Run: php database/seeds/migrate_users_to_contributors.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/CommunityMember.php';

$db = Database::getInstance();
$memberModel = new CommunityMember();

$users = $db->query('SELECT id, name, email, role, created_at FROM users')->fetchAll(PDO::FETCH_ASSOC);

$created = 0;
$skipped = 0;
$roleUpgraded = 0;

foreach ($users as $user) {
    if ($memberModel->getByUserId((int) $user['id'])) {
        echo "Skipping (already migrated): {$user['email']}\n";
        $skipped++;
        continue;
    }

    $memberId = $memberModel->create([
        'user_id' => $user['id'],
        'application_id' => null,
        'full_name' => $user['name'],
        'email' => $user['email'],
        'member_type' => 'contributor',
        'is_active' => 1,
        'date_joined' => date('Y-m-d', strtotime($user['created_at'])),
    ]);

    $roleNote = '';
    if ($user['role'] === 'user') {
        $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute(['contributor', $user['id']]);
        $roleNote = ' (role upgraded: user -> contributor)';
        $roleUpgraded++;
    } else {
        $roleNote = " (role left as '{$user['role']}')";
    }

    echo "Migrated -> community_members id {$memberId}: {$user['email']}{$roleNote}\n";
    $created++;
}

echo "\nDone. Created {$created}, skipped {$skipped}, role-upgraded {$roleUpgraded}.\n";
