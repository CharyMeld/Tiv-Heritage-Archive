<?php
/**
 * Yobe batch 074 — change to an EXISTING record (NIGERIA_QC_YOBE.md; owner approved 2026-10-01).
 *
 *   A  Yobe research progress: 17/17 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_YOBE.md). The row still read 'not started, 0/17'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_074_yobe.php';
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
    echo "A  Yobe research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-YOBE'")->fetch();
    if (!$row) throw new RuntimeException('Yobe research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 17,
            'notes' => 'Batches 069–074 (1 Oct 2026): 10 languages (Blench Atlas; Kanuri and Shuwa Arabic linked), 6 peoples plus Kanuri, Fulani, Hausa, Shuwa Arabs, Bura and Marghi with people–LGA links for 16 of 17 LGAs (federal state profile, Wikipedia), 178 INEC wards, traditional institutions (12 emirates: Damaturu, Fika, Bade, Potiskum, Ngazargamu, Tikau, Gudi, Gujba, Nguru, Yusufari, Fune, Jajere), culture and heritage (Ngazargamu, Dufuna, Goya Gorge, Dagona — NCMM proposed; National Museum Damaturu; Hadejia-Nguru Wetlands; Bade fishing festival), LGA profiles with headquarters. Phase 4 QC run (NIGERIA_QC_YOBE.md, 0 problems). Open: peoples of Tarmua; festivals; emirate rulers and grades; Nangere and Yunusari headquarters; LGA creation dates.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 074)'], JSON_UNESCAPED_UNICODE)]);
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
