<?php
/**
 * Lagos batch 116 — changes to EXISTING records (NIGERIA_QC_LAGOS.md; owner approved 2026-10-02).
 *
 *   A  Lagos research progress: 20/20 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_LAGOS.md). The row still read 'not started, 0/20'.
 *   B  Kingdom of Lagos (polity, batch 113): seat = Iga Idunganran (place, batch 114), the Oba's palace on Lagos
 *      Island (Wikipedia; NCMM declared monument No. 45). Batch 114 could not set it: the importer's updates take only
 *      administrative units. Filled only while the seat is empty.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_116_lagos.php';
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
    echo "A  Lagos research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-LAGOS'")->fetch();
    if (!$row) throw new RuntimeException('Lagos research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 20,
            'notes' => 'Batches 111–116 (2 Oct 2026): languages and peoples (new Kwa branch, Gun language and Ogu people; Yoruba linked to 13 LGAs with the Awori, Eko, Ijebu, Egba and Egbado in the notes; Ogu in Badagry; Hausa, Igbo, Fulani and Nupe as migrant communities); 245 INEC wards; traditional institutions (Kingdom of Lagos, Badagry Kingdom, Olojo of Ojo); culture and heritage (4 declared NCMM monuments — Ilojo Bar (demolished 2016), Iga Idunganran, Water House, Old Secretariat; 11 proposed; National Museum Lagos; Vlekete slave market; the Eyo festival and Zangbeto); LGA profiles for all 20 LGAs, with headquarters for 17. Phase 4 QC run (NIGERIA_QC_LAGOS.md, 0 problems). Open: peoples of 6 LGAs; headquarters of Ajeromi-Ifelodun, Oshodi-Isolo and Lagos Mainland; the next Akran of Badagry; the Ayangburen of Ikorodu and other obaships; the Shitta-Bey Mosque\'s monument status.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 116)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  research_progress#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
    }
    echo "B  Kingdom of Lagos seat:\n";
    $pol = $db->query("SELECT id, seat_place_id FROM polities WHERE slug = 'kingdom-of-lagos'")->fetch();
    $iga = $db->query("SELECT id FROM places WHERE slug = 'iga-idunganran'")->fetchColumn();
    if (!$pol || !$iga) throw new RuntimeException('kingdom-of-lagos or iga-idunganran not found');
    if ($pol['seat_place_id'] === null) {
        $db->prepare('UPDATE polities SET seat_place_id = ? WHERE id = ?')->execute([$iga, $pol['id']]);
        $log->execute(['polities', $pol['id'], json_encode(['seat_place_id' => null]), json_encode(['seat_place_id' => (int) $iga, 'by' => 'owner approval (batch 116)'])]);
        $changes++;
        echo "  polities#{$pol['id']}: seat_place_id → places#{$iga}\n";
    } elseif ((int) $pol['seat_place_id'] !== (int) $iga) {
        echo "  polities#{$pol['id']}: seat already set to places#{$pol['seat_place_id']} — left unchanged\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
