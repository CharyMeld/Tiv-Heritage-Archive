<?php
/**
 * Benue batch 030 — changes to EXISTING records (NIGERIA_BATCH_030_REVIEW.md; on approval).
 * Run after batch 030 has been imported (it uses the Upper Cross record).
 *
 *   A  Oring language: placed in the Upper Cross branch (Glottolog) — parent_id was empty.
 *   B  Research gap #51 "Tiv dialects" (batch 006): resolved by batch 029 (Glottolog dialects).
 *   C  Benue research progress: all 23 LGAs now researched (profiles, headquarters, wards, peoples);
 *      status 'quality control', 1 LGA needing review (Ukum headquarters).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_030_benue.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 030)'], JSON_UNESCAPED_UNICODE)]);
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
    $uc = $db->query("SELECT id FROM languages WHERE slug = 'upper-cross'")->fetchColumn();
    if (!$uc) throw new RuntimeException('batch 030 has not been imported yet (Upper Cross missing)');
    echo "A  Oring → Upper Cross:\n";
    $row = $db->query("SELECT * FROM languages WHERE slug = 'oring'")->fetch();
    if (!$row) throw new RuntimeException('Oring not found');
    if ($row['parent_id'] === null) $set('languages', $row, ['parent_id' => (int) $uc]);
    echo "B  Gap #51 (Tiv dialects):\n";
    $row = $db->query("SELECT * FROM research_gaps WHERE id = 51 AND topic = 'Tiv dialects'")->fetch();
    if ($row) $set('research_gaps', $row, ['status' => 'resolved']);
    echo "C  Benue research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-BENUE'")->fetch();
    $set('research_progress', $row, ['lgas_total' => 23, 'lgas_researched' => 23, 'lgas_needs_review' => 1, 'status' => 'qc_in_progress',
        'notes' => 'Batches 023–030 (26 Sep 2026): peoples and institutions, 276 INEC wards, culture, heritage, dialects, LGA profiles with headquarters. Phase 4 QC run (NIGERIA_QC_BENUE.md). Needs review: Ukum headquarters (Zaki Biam or Sankera). Print sources needed: 2016 chieftaincy law, Tiv clan map.']);
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
