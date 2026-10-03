<?php
/**
 * Borno batch 068 — change to an EXISTING record (NIGERIA_BATCH_068_REVIEW.md; owner approved 2026-10-01).
 *
 *   A  Borno research progress: 27/27 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_BORNO.md). The row still read 'not started, 0/27'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_068_borno.php';
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
    echo "A  Borno research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-BORNO'")->fetch();
    if (!$row) throw new RuntimeException('Borno research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 27,
            'notes' => 'Batches 063–068 (1 Oct 2026): 18 languages and the Saharan and Semitic branches (Blench Atlas; 8 existing languages linked), 14 peoples plus Kanuri, Hausa, Fulani, Marghi and others, with people–LGA links for 21 of 27 LGAs (federal state profile, Wikipedia), 312 INEC wards, traditional institutions (Borno, Dikwa, Bama, Biu, Gwoza, Askira, Uba, Damboa and Shani emirates), culture and heritage (Rabeh\'s Fort, declared No. 13; two proposed monuments; National Museum Maiduguri; Kukawa; Chad Basin National Park; Sambisa; Kanem-Borno Cultural Summit), LGA profiles with headquarters, other names. Phase 4 QC run (NIGERIA_QC_BORNO.md, 0 problems). Open: peoples of Abadam, Gubio, Mafa, Magumeri, Marte and Mobbar; festivals; emirate grades and council; Bayo and Ngala headquarters; LGA creation dates.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 068)'], JSON_UNESCAPED_UNICODE)]);
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
