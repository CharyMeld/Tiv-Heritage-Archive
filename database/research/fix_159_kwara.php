<?php
/**
 * Kwara batch 159 — change to an EXISTING record (NIGERIA_QC_KWARA.md; owner approval required before --apply on live).
 *
 *   A  Kwara research progress: 16/16 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_KWARA.md). The row still read 'not started, 0/16'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_159_kwara.php';
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
    echo "A  Kwara research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-KWARA'")->fetch();
    if (!$row) throw new RuntimeException('Kwara research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 16,
            'notes' => 'Batches 154–159 (7 Oct 2026): languages and peoples (Gur and Mande branches; Baatọnum, Busa and Bokobaru; the Bariba and Busa peoples; Nupe in Edu and Pategi; Yoruba — Igbomina, Ibolo, Ekiti — in all 16 LGAs; Fulani and Hausa in Asa and Kaiama); 193 INEC wards; traditional institutions (Ilorin Emirate — Emir Sulu-Gambari; Offa — Olofa Gbadamosi; Pategi Emirate — Etsu Bologi II, reported; Lafiagi Emirate — Emir Kawu, reported); culture and heritage (5 declared NCMM monuments — the Dayspring relics at Jebba, the WAFF forts at Okuta and Yashikera, the stone figures at Ofaro and Ijara; 4 proposed — Esie stone images, Alimi Mosque, Queen Elizabeth Girls School, Oya grove at Iraa; National Museums Esie and Ilorin; the Pategi Regatta and Onimoka); LGA profiles with headquarters for all 16 LGAs. Phase 4 QC run (NIGERIA_QC_KWARA.md, 0 problems). Open: Sorko; the people of Moro, Ekiti and the Ilorin LGAs; 2025–26 sources for the Etsu Pategi and Emir of Lafiagi; other thrones (Kaiama, Shonga, Omu-Aran); undescribed monuments.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 159)'], JSON_UNESCAPED_UNICODE)]);
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
