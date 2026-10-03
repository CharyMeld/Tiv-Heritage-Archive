<?php
/**
 * Nasarawa batch 037 — changes to EXISTING records (NIGERIA_BATCH_037_REVIEW.md; on approval).
 * Run after batch 037 has been imported.
 *
 *   A  Research gap #472 'The other first-class stools' (batch 033): resolved by batch 037
 *      (all 22 stools of the Daily Trust 2012 list now recorded).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_037_nasarawa.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 037)'], JSON_UNESCAPED_UNICODE)]);
    $changes++;
    echo "  {$table}#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
};
$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            $old = json_decode($r['old_values'], true);
            $db->prepare("UPDATE {$r['entity_type']} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old))) . " WHERE id = ?")->execute([...array_values($old), $r['entity_id']]);
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }
    echo "A  Gap #472:\n";
    $row = $db->query("SELECT * FROM research_gaps WHERE id = 472 AND topic = 'The other first-class stools'")->fetch();
    if (!$row) throw new RuntimeException('gap #472 not found');
    $set('research_gaps', $row, ['status' => 'resolved']);
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
