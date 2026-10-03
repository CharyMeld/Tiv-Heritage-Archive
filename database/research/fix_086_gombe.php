<?php
/**
 * Gombe batch 086 — change to an EXISTING record (NIGERIA_QC_GOMBE.md; owner approved 2026-10-02).
 *
 *   A  Gombe research progress: 11/11 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_GOMBE.md). The row still read 'not started, 0/11'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_086_gombe.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
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
    echo "A  Gombe research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-GOMBE'")->fetch();
    if (!$row) throw new RuntimeException('Gombe research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 11,
            'notes' => 'Batches 081–086 (2 Oct 2026): 10 new languages (Blench Atlas) plus Gombe links for 12 existing ones; 10 new peoples (federal state profile, Wikipedia, Atlas) plus 8 existing, with people–LGA links for all 11 LGAs; 114 INEC wards; traditional institutions (9 emirates — Gombe, Dukku, Funakaye, Deba, Akko, Pindiga, Gona, Nafada, Yamaltu — and 5 chiefdoms — Tangale, Dadiya, Kaltungo, Tula, Waja — from the state government list); culture and heritage (Tula Prison Yard and Mbormi/Burmi — NCMM proposed; National Museum Gombe; Emir\'s Palace; Dadin Kowa Dam; Muri Mountains; Pissi Tangale, Bai and Kamo festivals); LGA profiles with headquarters. Phase 4 QC run (NIGERIA_QC_GOMBE.md, 0 problems). Open: a current official list of rulers (Funakaye, Deba/Yamaltu); emirate grades and dates; Billiri and Dukku headquarters; peoples of table-only languages (Kushi, Loo, Moo, Kyak, Dera, Dikaka, Dza, Yuwar, Wurkun); festival details; LGA creation dates.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 086)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  research_progress#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
