<?php
/**
 * Publishes the six geopolitical zone records created as drafts by backfill_002.php
 * (owner decision Q2: zones as records), for the public-views step. Each zone page lists
 * its states and cites Wikipedia "States of Nigeria" and EUAA. The pages are short, so the
 * indexability gate serves them noindex and keeps them out of the sitemap.
 * The state→zone part_of links stay drafts (the zone page lists its states from the zone field).
 * Also corrects the North Central summary's grammar ("… and the Federal Capital Territory").
 *
 * Idempotent; logged to activity_log. Default is a DRY RUN; --apply commits; --revert unpublishes.
 * Usage: php database/research/publish_zones_002.php [--apply] [--revert]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/config/security.php';
require BASE_PATH . '/core/Cache.php';
require BASE_PATH . '/services/ContentIndexer.php';
Cache::init(BASE_PATH . '/storage/cache');

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
[$from, $to] = $revert ? ['published', 'draft'] : ['draft', 'published'];

$zones = $db->query("SELECT u.id, u.name, u.stable_id,
                            (SELECT COUNT(DISTINCT es.source_id) FROM entity_sources es WHERE es.entity_table = 'admin_units' AND es.entity_id = u.id) AS sources
                     FROM admin_units u WHERE u.unit_type = 'geopolitical_zone' ORDER BY u.name")->fetchAll();
if (count($zones) !== 6) { fwrite(STDERR, '[ERROR] expected 6 zone records, found ' . count($zones) . " — nothing changed.\n"); exit(1); }

$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_status_changed', 'admin_units', ?, ?, ?, 'cli', 'publish_zones_002.php')");
$db->beginTransaction();
try {
    $changed = 0;
    foreach ($zones as $z) {
        if (!$revert && (int) $z['sources'] === 0) throw new RuntimeException("{$z['name']} has no source");
        $st = $db->prepare("UPDATE admin_units SET review_status = ? WHERE id = ? AND review_status = ? AND sensitivity = 'public'");
        $st->execute([$to, $z['id'], $from]);
        if ($st->rowCount()) {
            $changed++;
            $log->execute([$z['id'], json_encode(['review_status' => $from]),
                json_encode(['review_status' => $to, 'by' => 'owner approval 2026-09-25 (public views step)'])]);
        }
        echo "{$z['stable_id']}  {$z['name']}  sources={$z['sources']}  -> {$to}\n";
    }
    // Grammar of the North Central summary written by backfill_002 ("… and the Federal Capital Territory").
    if (!$revert) {
        $st = $db->prepare("UPDATE admin_units SET summary = REPLACE(summary, ' and Federal Capital Territory.', ' and the Federal Capital Territory.')
                            WHERE unit_type = 'geopolitical_zone' AND summary LIKE '% and Federal Capital Territory.%'");
        $st->execute();
        if ($st->rowCount()) {
            $changed += $st->rowCount();
            $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                          SELECT NULL, 'heritage_record_corrected', 'admin_units', id, '{\"summary\":\"… Plateau and Federal Capital Territory.\"}',
                                 '{\"summary\":\"… Plateau and the Federal Capital Territory.\"}', 'cli', 'publish_zones_002.php'
                          FROM admin_units WHERE stable_id = 'NG-ZONE-NORTH-CENTRAL'")->execute();
            echo "North Central summary: '… and the Federal Capital Territory.'\n";
        }
    }
    $apply ? $db->commit() : $db->rollBack();
    if ($apply) {
        $indexer = new ContentIndexer($db);
        foreach ($zones as $z) $indexer->indexRecord('admin_units', (int) $z['id']);
        Cache::forget('nigeria_has_published');
    }
    echo "{$changed} changed. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
