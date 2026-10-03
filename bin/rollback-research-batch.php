<?php
/**
 * Remove one imported research batch — exactly the rows tagged with its
 * research_batch_id, plus the source rows it recorded as created (and only if nothing
 * outside the batch cites them). Refuses if any of the batch's records is published
 * (unpublish first), so a rollback can never silently take down public pages.
 *
 * Default is a DRY RUN (rolled back). --apply commits.
 *
 * Usage: php bin/rollback-research-batch.php "<batch title>" [--apply]
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$title = $argv[1] ?? null;
$apply = in_array('--apply', $argv, true);
if (!$title) { fwrite(STDERR, "Usage: php bin/rollback-research-batch.php \"<batch title>\" [--apply]\n"); exit(1); }

$db = Database::getInstance();
$stmt = $db->prepare('SELECT id, summary FROM research_batches WHERE title = ?');
$stmt->execute([$title]);
$batch = $stmt->fetch();
if (!$batch) { fwrite(STDERR, "[ABORT] No research batch titled \"{$title}\". Nothing changed.\n"); exit(1); }
$b = (int) $batch['id'];

$tables = ['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods', 'timeline_events', 'historical_figures'];
$statusCol = fn(string $t) => in_array($t, ['timeline_events', 'historical_figures'], true) ? 'status' : 'review_status';
foreach ($tables as $t) {
    if ((int) $db->query("SELECT COUNT(*) FROM {$t} WHERE research_batch_id = {$b} AND {$statusCol($t)} = 'published'")->fetchColumn()) {
        fwrite(STDERR, "[ABORT] {$t} has published records from this batch; unpublish them first. Nothing changed.\n");
        exit(1);
    }
}
$sourceIds = preg_match('/Sources created by this batch \(ids\): ([\d,]+)/', (string) $batch['summary'], $m)
    ? array_map('intval', explode(',', $m[1])) : [];

$n = [];
$db->beginTransaction();
try {
    foreach (['entity_sources', 'entity_names', 'entity_statistics', 'entity_relations', 'admin_unit_changes', 'research_gaps'] as $t) {
        $n[$t] = $db->exec("DELETE FROM {$t} WHERE research_batch_id = {$b}");
    }
    $db->exec("UPDATE admin_units SET capital_place_id = NULL WHERE research_batch_id = {$b}");
    // National people/events: their 'nigeria' collection tags go with them.
    foreach (['timeline_events', 'historical_figures'] as $t) {
        $n["collection_items:{$t}"] = $db->exec("DELETE ci FROM collection_items ci JOIN {$t} x ON x.id = ci.entity_id WHERE ci.entity_table = '{$t}' AND x.research_batch_id = {$b}");
    }
    foreach (array_reverse($tables) as $t) {
        if (in_array($t, ['admin_units', 'languages', 'ethnic_groups'], true)) $db->exec("UPDATE {$t} SET parent_id = NULL WHERE research_batch_id = {$b}");
        $n[$t] = $db->exec("DELETE FROM {$t} WHERE research_batch_id = {$b}");
    }
    // Empty again the fields this batch filled on existing records (they were empty before).
    // Done before deleting sources: a filled field may point to one (e.g. vitality_source_id).
    if (preg_match('/Fields filled by this batch: ([^\n]+)/', (string) $batch['summary'], $fm)) {
        foreach (explode(';', $fm[1]) as $item) {
            [$ref, $fields] = explode(':', $item, 2);
            [$t, $id] = explode('#', $ref);
            $cols = array_filter(explode(',', $fields), fn($c) => preg_match('/^[a-z_]+$/', $c));
            if (in_array($t, ['admin_units', 'places', 'ethnic_groups', 'languages'], true) && $cols) {
                $n['fields_emptied'] = ($n['fields_emptied'] ?? 0) + $db->exec("UPDATE {$t} SET " . implode(', ', array_map(fn($c) => "{$c} = NULL", $cols)) . ' WHERE id = ' . (int) $id);
            }
        }
    }
    $n['sources'] = 0;
    foreach ($sourceIds as $sid) {
        $cited = (int) $db->query("SELECT (SELECT COUNT(*) FROM entity_sources WHERE source_id = {$sid})
                                   + (SELECT COUNT(*) FROM entity_statistics WHERE source_id = {$sid})
                                   + (SELECT COUNT(*) FROM entity_relations WHERE source_id = {$sid})
                                   + (SELECT COUNT(*) FROM admin_unit_changes WHERE source_id = {$sid})
                                   + (SELECT COUNT(*) FROM entity_names WHERE source_id = {$sid})
                                   + (SELECT COUNT(*) FROM languages WHERE vitality_source_id = {$sid})
                                   + (SELECT COUNT(*) FROM places WHERE coords_source_id = {$sid})
                                   + (SELECT COUNT(*) FROM admin_units WHERE coords_source_id = {$sid})")->fetchColumn();
        if ($cited === 0) $n['sources'] += $db->exec("DELETE FROM sources WHERE id = {$sid}");
    }
    $db->exec("DELETE FROM research_batches WHERE id = {$b}");
    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
echo ($apply ? '[APPLIED]' : '[DRY RUN - rolled back]') . " rollback of \"{$title}\": " . json_encode(array_filter($n)) . "\n";
