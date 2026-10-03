<?php
/**
 * Jigawa batch 098 — change to an EXISTING record (NIGERIA_QC_JIGAWA.md; owner approved 2026-10-02).
 *
 *   A  Jigawa research progress: 27/27 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_JIGAWA.md). The row still read 'not started, 0/27'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_098_jigawa.php';
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
    echo "A  Jigawa research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-JIGAWA'")->fetch();
    if (!$row) throw new RuntimeException('Jigawa research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 27,
            'notes' => 'Batches 093–098 (2 Oct 2026): 3 extinct Bade-group languages (Auyokawa, Shira, Teshena; Blench Atlas) and Jigawa links for Bade, Kanuri, Warji, Uled Suliman Arabic and Duwai; 10 peoples linked to Jigawa (federal state profile, Wikipedia) with people–LGA links for 14 LGAs; 287 INEC wards; traditional institutions (Dutse, Hadejia, Gumel — new emir September 2026 —, Kazaure and Ringim emirates); culture and heritage (4 declared NCMM rock-art monuments at Birnin Kudu, the Birnin Kudu old settlement, National Museum Birnin Kudu, Baturiya Wetland); LGA profiles with headquarters for all 27 LGAs; Mangawa, Ngizimawa and Badawa as other names. Phase 4 QC run (NIGERIA_QC_JIGAWA.md, 0 problems). Open: peoples of 13 LGAs; Shira Glottocode; descriptions of the rock sites; Jigawa festivals; 20 headquarters resting on Statoids alone.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 098)'], JSON_UNESCAPED_UNICODE)]);
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
