#!/usr/bin/env php
<?php
/**
 * Prints one line per marketing image attached to a published schedule
 * that still exists on disk: "<file_path>\t<deletable>", where deletable
 * is 1 once the post has been published for 24+ hours, 0 otherwise. Used
 * by scripts/sync-marketing-images-to-local.sh (run from the local
 * machine) — every listed file gets copied to the local archive, but only
 * deletable=1 files get removed from the VPS afterward. The 24h grace
 * period keeps recently-published images live on the VPS so the Publishing
 * Queue's WhatsApp share button can still attach them; MIN(published_at)
 * across schedules covers images reused by more than one schedule row.
 * A path only disappears from this list once the file itself is gone, so
 * re-running the sync script is always safe (nothing to track).
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';

$db = Database::getInstance();
$stmt = $db->query(
    "SELECT mi.file_path, MIN(ms.published_at) <= NOW() - INTERVAL 24 HOUR AS deletable
     FROM marketing_images mi
     INNER JOIN marketing_schedules ms ON ms.image_id = mi.id
     WHERE ms.status = 'published'
     GROUP BY mi.file_path"
);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $full = UPLOADS_PATH . '/' . $row['file_path'];
    if (is_file($full)) {
        echo $row['file_path'] . "\t" . ((int) $row['deletable']) . "\n";
    }
}
