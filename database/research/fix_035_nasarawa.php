<?php
/**
 * Nasarawa batch 035 — changes to EXISTING records (NIGERIA_BATCH_035_REVIEW.md; on approval).
 * Run after batch 035 has been imported.
 *
 *   A  Nasarawa research progress: all 13 LGAs now researched (languages, peoples, wards, institutions,
 *      culture and heritage, LGA profiles with headquarters); status stays 'in progress' until the
 *      Phase 4 QC; 1 LGA needing review (Karu headquarters).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_035_nasarawa.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 035)'], JSON_UNESCAPED_UNICODE)]);
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
    echo "A  Nasarawa research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-NASARAWA'")->fetch();
    if (!$row) throw new RuntimeException('Nasarawa research_progress row not found');
    $set('research_progress', $row, ['lgas_total' => 13, 'lgas_researched' => 13, 'lgas_needs_review' => 1, 'status' => 'in_progress',
        'notes' => 'Batches 031–035 (26 Sep 2026): languages (Blench Atlas), 21 peoples with LGA links, 147 INEC wards, traditional institutions (part 1), culture and heritage (part 1), LGA profiles with headquarters. Needs review: Karu headquarters (Karu, New Karu or Mararaba). Next: Phase 4 QC.']);
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
