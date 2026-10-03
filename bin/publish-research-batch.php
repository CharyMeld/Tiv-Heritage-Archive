<?php
/**
 * Publish every record of an approved research batch, applying the same rules as
 * the admin editor: each record must have at least one attached source and an
 * assessed evidence status, or nothing is published. Logs each status change to
 * activity_log, marks the batch 'approved', refreshes the AI/search index and the
 * Nigeria menu cache.
 *
 * Default is a DRY RUN (rolled back). --apply commits.
 *
 * Usage: php bin/publish-research-batch.php "<batch title>" [--apply]
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/config/security.php';
require BASE_PATH . '/core/Cache.php';
require BASE_PATH . '/services/ContentIndexer.php';
Cache::init(BASE_PATH . '/storage/cache');

$title = $argv[1] ?? null;
$apply = in_array('--apply', $argv, true);
if (!$title) { fwrite(STDERR, "Usage: php bin/publish-research-batch.php \"<batch title>\" [--apply]\n"); exit(1); }

$db = Database::getInstance();
$stmt = $db->prepare('SELECT id, status FROM research_batches WHERE title = ?');
$stmt->execute([$title]);
$batch = $stmt->fetch();
if (!$batch) { fwrite(STDERR, "[ABORT] No research batch titled \"{$title}\".\n"); exit(1); }
$b = (int) $batch['id'];

$tables = ['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods', 'timeline_events', 'historical_figures'];
// National people and events (shared with the Tiv collection) use status draft/published, not review_status.
$statusCol = fn(string $t) => in_array($t, ['timeline_events', 'historical_figures'], true) ? 'status' : 'review_status';
$nameCol = fn(string $t) => ['timeline_events' => 'title', 'historical_figures' => 'english_name'][$t] ?? 'name';
$problems = [];
$toPublish = [];
foreach ($tables as $t) {
    $rows = $db->query("SELECT t.id, t.{$nameCol($t)} AS name, t.evidence_status,
                               (SELECT COUNT(DISTINCT es.source_id) FROM entity_sources es WHERE es.entity_table = '{$t}' AND es.entity_id = t.id) AS n
                        FROM {$t} t WHERE t.research_batch_id = {$b} AND t.{$statusCol($t)} <> 'published'")->fetchAll();
    foreach ($rows as $r) {
        if ((int) $r['n'] === 0) $problems[] = "{$t}#{$r['id']} {$r['name']}: no source attached";
        if (in_array($r['evidence_status'], ['unverified', null], true)) $problems[] = "{$t}#{$r['id']} {$r['name']}: evidence not assessed";
        $toPublish[$t][] = $r;
    }
}
if ($problems) {
    fwrite(STDERR, "[ABORT] Nothing published:\n  " . implode("\n  ", $problems) . "\n");
    exit(1);
}

$counts = [];
$db->beginTransaction();
try {
    $log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                         VALUES (NULL, 'heritage_status_changed', ?, ?, ?, ?, 'cli', 'bin/publish-research-batch.php')");
    foreach ($toPublish as $t => $rows) {
        $col = $statusCol($t);
        $upd = $db->prepare("UPDATE {$t} SET {$col} = 'published' WHERE id = ? AND research_batch_id = ?");
        foreach ($rows as $r) {
            $upd->execute([$r['id'], $b]);
            $log->execute([$t, $r['id'], json_encode([$col => $col === 'status' ? 'draft' : 'in_review']), json_encode([$col => 'published', 'batch' => $title])]);
        }
        $counts[$t] = count($rows);
    }
    $db->prepare("UPDATE research_batches SET status = 'approved', reviewed_at = NOW() WHERE id = ?")->execute([$b]);
    $db->prepare("UPDATE entity_relations SET review_status = 'published' WHERE research_batch_id = ? AND source_id IS NOT NULL")->execute([$b]);
    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}

if ($apply) {
    $indexer = new ContentIndexer($db);
    foreach ($toPublish as $t => $rows) foreach ($rows as $r) $indexer->indexRecord($t, (int) $r['id']);
    // Existing (already published) records whose empty fields this batch filled.
    $summary = (string) $db->query("SELECT summary FROM research_batches WHERE id = {$b}")->fetchColumn();
    if (preg_match('/Fields filled by this batch: ([^\n]+)/', $summary, $fm)) {
        foreach (explode(';', $fm[1]) as $item) {
            [$t, $id] = explode('#', explode(':', $item, 2)[0]);
            $indexer->indexRecord($t, (int) $id);
        }
    }
    Cache::forget('nigeria_has_published');
}
echo ($apply ? '[APPLIED]' : '[DRY RUN - rolled back]') . " published from \"{$title}\": " . json_encode($counts) . "\n";
