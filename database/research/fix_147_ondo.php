<?php
/**
 * Ondo batch 147 — change to an EXISTING record (NIGERIA_QC_ONDO.md; owner approval required before --apply on live).
 *
 *   A  Ondo research progress: 18/18 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_ONDO.md). The row still read 'not started, 0/18'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_147_ondo.php';
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
    echo "A  Ondo research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-ONDO'")->fetch();
    if (!$row) throw new RuntimeException('Ondo research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 18,
            'notes' => 'Batches 142–147 (7 Oct 2026): languages and peoples (9 Atlas languages — Arigidi, Ahan, Akpes, Ukaan, Ehuẹun, Ukue, Uhami, Iyayu, Ịzọn — and the Ijoid branch; the Ijaw people; Yoruba linked to all 18 LGAs with the Akoko, Ikale, Ilaje, Owo, Idanre and Ose named); 203 INEC wards; traditional institutions (Akure Kingdom — Deji Aladetoyinbo, paramountcy disputed by the Iralepo of Isinkan; Owo Kingdom — Olowo Ogunoye III; Ondo Kingdom — Osemawe Kiladejo; Ugbo Kingdom — Olugbo Akinruntan; Idoani Confederacy — Alani, reported); culture and heritage (3 declared NCMM monuments — Iho Eleru, Igbara-Oke petroglyphs, Old Palace of the Deji; 3 proposed — Idanre Hill, Ashuba marble stone, Igbo Olodumare; the Olowo\'s palace; National Museums Akure and Owo; the Igogo and Ulefunta festivals); LGA profiles with headquarters for all 18 LGAs. Phase 4 QC run (NIGERIA_QC_ONDO.md, 0 problems). Open: sub-groups of five LGAs; Iho Eleru location and dates; other obaships (Owa-Ale of Idanre, Jegun of Ile-Oluji, Abodi of Ikale); undescribed monuments.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 147)'], JSON_UNESCAPED_UNICODE)]);
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
