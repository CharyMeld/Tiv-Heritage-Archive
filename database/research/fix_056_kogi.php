<?php
/**
 * Kogi batch 056 — change to an EXISTING record (NIGERIA_BATCH_056_REVIEW.md; owner approved 2026-09-30).
 *
 *   A  Kogi research progress: 21/21 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_KOGI.md). The row still read 'not started, 0/21'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_056_kogi.php';
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
    echo "A  Kogi research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-KOGI'")->fetch();
    if (!$row) throw new RuntimeException('Kogi research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 21,
            'notes' => 'Batches 051–056 (30 Sep 2026): 8 languages and the Yoruboid, Edoid and Ayere–Ahan branches (Blench Atlas; Ebira and Basa-Benue linked), 8 peoples plus Ebira, Basa, Gbagyi and Idoma with people–LGA links for all 21 LGAs (federal profile, Wikipedia), 239 INEC wards, traditional institutions (state council; Attah Igala, Ohinoyi of Ebiraland, Ohimege-Igu, Olu of Magongo first class; Obaro of Kabba; Etsu Bassa-Nge), culture and heritage (Lokoja museum, Idah tumulus, four proposed monuments, Mount Patti, Ovia-Osese, Owiya Osese), LGA profiles with headquarters, other names. Phase 4 QC run (NIGERIA_QC_KOGI.md, 0 problems). Open: Igala councils and other Okun stools; grades of the Obaro and Etsu; Igala and Ebira festivals; an official list of LGA headquarters; LGA creation dates.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 056)'], JSON_UNESCAPED_UNICODE)]);
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
