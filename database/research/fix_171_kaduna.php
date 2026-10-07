<?php
/**
 * Kaduna batch 171 — change to an EXISTING record (NIGERIA_QC_KADUNA.md; owner approval required before --apply on live).
 *
 *   A  Kaduna research progress: 23/23 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_KADUNA.md). The row still read 'not started, 0/23'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_171_kaduna.php';
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
    echo "A  Kaduna research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-KADUNA'")->fetch();
    if (!$row) throw new RuntimeException('Kaduna research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 23,
            'notes' => 'Batches 166–171 (7 Oct 2026): languages (166: 17 Kainji languages of the old Saminaka LGA, now Lere and Kauru; 166b: 27 Plateau languages of southern Kaduna, among them Tyap, Jju, Hyam, Gyong, Ikulu and Kamantan; INEC place-name matches linked as reported for LGAs created after the Atlas survey); peoples (166c: 36 new, own names with Hausa exonyms, e.g. Atyap/Kataf, Bajju/Kaje, Ham/Jaba; Ninzam and Nyankpa reused; peoples in 17 of 23 LGAs); 255 INEC wards; traditional institutions (Zazzau — Emir Ahmed Nuhu Bamalli, 19th since 1804; Jema\'a — Emir Muhammad Isa Muhammadu II; Birnin Gwari, reported; Atyap — Agwatyap III Dominic Gambo Yahaya; Agworok (Kagoro) — Dr Ufuwai Bonet; Kajju and Ham, reported); culture and heritage (NCMM declared 28–31 — Zaria walls, Kufena Hills, Lugard Footbridge, Maigana mosque; proposed 9, 10, 29, 30 — Arewa House, Wusasa church, Lugard Hall, Nok site; National Museum Kaduna; Afan, Ayet Atyap and Zaria durbar); LGA profiles with headquarters for all 23 LGAs. Phase 4 QC run (NIGERIA_QC_KADUNA.md, 0 problems). Open: peoples of six northern LGAs; Atuku and some Sanga groups; other Southern Kaduna stools; Ajuwa–Ajegha unclassified.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 171)'], JSON_UNESCAPED_UNICODE)]);
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
