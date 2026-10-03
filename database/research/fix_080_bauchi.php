<?php
/**
 * Bauchi batch 080 — change to an EXISTING record (NIGERIA_QC_BAUCHI.md; owner approved 2026-10-02).
 *
 *   A  Bauchi research progress: 20/20 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_BAUCHI.md). The row still read 'not started, 0/20'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_080_bauchi.php';
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
    echo "A  Bauchi research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-BAUCHI'")->fetch();
    if (!$row) throw new RuntimeException('Bauchi research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 20,
            'notes' => 'Batches 075–080 (1–2 Oct 2026): 47 languages (Blench Atlas), peoples with people–LGA links for 18 of 20 LGAs (federal state profile, Wikipedia, Atlas; Zaar recorded with Sayawa as exonym), 212 INEC wards, traditional institutions (19 emirates incl. 13 created by the 2025 law, and the Zaar Chiefdom), culture and heritage (7 declared NCMM monuments, Tafawa Balewa mausoleum and Kirfin Sama — proposed; National Museum Bauchi; Yankari and Wikki; Sumu; Lame-Burra; Bauchi Durbar), LGA profiles with headquarters (Tafawa Balewa: Bununu since 2011). Phase 4 QC run (NIGERIA_QC_BAUCHI.md, 0 problems). Open: peoples of Katagum and Shira; festivals beyond the Durbar; descriptions of the declared monuments; Ari Emirate\'s LGA and the new emirs; Damlanci classification; LGA creation dates.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 080)'], JSON_UNESCAPED_UNICODE)]);
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
