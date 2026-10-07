<?php
/**
 * Niger batch 165 — change to an EXISTING record (NIGERIA_QC_NIGER.md; owner approval required before --apply on live).
 *
 *   A  Niger research progress: 25/25 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_NIGER.md). The row still read 'not started, 0/25'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_165_niger.php';
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
    echo "A  Niger research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-NIGER'")->fetch();
    if (!$row) throw new RuntimeException('Niger research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 25,
            'notes' => 'Batches 160–165 (7 Oct 2026): languages (33 Atlas languages, mostly Kainji, the Songhai branch and Gbagyi Nkwa; Nupe, Gbagyi, Gbari, Kakanda, Gwandara and Busa linked to their LGAs); peoples (Kambari, Kamuku, Adara, Dibo, Hun-Saare, Pangu, Hùngwəryə, Reshe; peoples linked in all 25 LGAs); 274 INEC wards; traditional institutions (Bida Emirate — Etsu Nupe Yahaya Abubakar; Borgu — Emir Dantoro Kitoro IV; Kontagora — Sarkin Sudan Mu\'azu II, reported; Suleja — Emir Awwal Ibrahim, reported); culture and heritage (NCMM declared 48–51 — Tsoede\'s tomb, Mai Jimina\'s house, the Zungeru Government House ruins, the Etsu Nupe\'s katamba; proposed 67–69 and 88 — the Zungeru landscape, the Dabo Mosque at Gulu, All Saints Zungeru, Zuma Rock; National Museum Minna; Kainji National Park; Gurara Waterfalls; Nupe Day); LGA profiles with headquarters for all 25 LGAs (Muya kept, Munya noted). Phase 4 QC run (NIGERIA_QC_NIGER.md, 0 problems; 9 languages without a Glottocode). Open: own vs federal-profile names for five peoples; the Agaie, Lapai and Kagara emirates; Kontagora and Suleja rulers need 2025–26 sources; Gwagwade\'s location; undescribed monuments.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 165)'], JSON_UNESCAPED_UNICODE)]);
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
