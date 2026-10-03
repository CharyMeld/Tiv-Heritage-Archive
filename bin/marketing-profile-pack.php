#!/usr/bin/env php
<?php
/**
 * Prepares the owner's "Profile pack": ready-to-post Facebook items for the
 * personal profile, from published Nigeria Heritage records. Nothing is
 * posted (Meta has no API for personal profiles) — the owner copies each item
 * from Admin → Marketing → Profile Pack. See services/ProfilePackGenerator.php.
 *
 * Fills every empty slot in the coming days, so re-running is harmless.
 *
 *   php bin/marketing-profile-pack.php [--days=7] [--per-day=1] [--dry-run]
 *
 * Cron (server UTC, WAT - 1h), Sundays 05:00 WAT:
 *   0 4 * * 0 sudo -u www-data /usr/bin/php /var/www/tiv/bin/marketing-profile-pack.php >> /var/www/tiv/storage/logs/marketing-cron.log 2>&1
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/services/ProfilePackGenerator.php';

// Attributed to the Administrator account (id 1), as in marketing-autopilot.php.
const PROFILE_PACK_USER_ID = 1;

$opts = getopt('', ['days::', 'per-day::', 'dry-run']);
$days = max(1, min(ProfilePackGenerator::MAX_DAYS_AHEAD, (int) ($opts['days'] ?? 7)));
$perDay = max(1, min(3, (int) ($opts['per-day'] ?? 1)));
$dryRun = isset($opts['dry-run']);

echo '[' . date('Y-m-d H:i:s') . "] Profile pack: {$days} days × {$perDay}/day" . ($dryRun ? ' (dry run)' : '') . "\n";
if (!OllamaClient::isAvailable()) {
    echo "Ollama is not reachable — captions will use the plain fallback text.\n";
}
foreach (ProfilePackGenerator::fill($days, $perDay, $dryRun, PROFILE_PACK_USER_ID) as $line) {
    echo "  {$line}\n";
}
echo '[' . date('Y-m-d H:i:s') . "] Profile pack done.\n";
