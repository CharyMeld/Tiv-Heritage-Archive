<?php
/**
 * Adamawa batch 062 — changes to EXISTING records (NIGERIA_BATCH_062_REVIEW.md; owner approved 2026-10-01).
 * Run after batch 062 has been imported (it uses the Atlantic record).
 *
 *   A  Fulfulde: placed in Atlantic (Atlas 'Atlantic (Northern branch, Senegal group)'); classification sentence.
 *   B  Adamawa research progress: 21/21 LGAs researched, status 'quality control in progress' (Phase 4 QC
 *      run, NIGERIA_QC_ADAMAWA.md). The row still read 'not started, 0/21'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_062_adamawa.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 062)'], JSON_UNESCAPED_UNICODE)]);
    $changes++;
    echo "  {$table}#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
};
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
    $at = $db->query("SELECT id FROM languages WHERE slug = 'atlantic' AND lang_type = 'branch'")->fetchColumn();
    if (!$at) throw new RuntimeException('batch 062 has not been imported yet (Atlantic missing)');

    echo "A  Fulfulde → Atlantic:\n";
    $row = $db->query("SELECT * FROM languages WHERE slug = 'fulfulde'")->fetch();
    if (!$row) throw new RuntimeException('language fulfulde not found');
    $s = "Blench's Atlas classes it in the Northern branch (Senegal group) of Atlantic; Glottolog places the Fula languages in North-Central Atlantic (nort3146), under Fula-Sereer (peul1234), within Atlantic-Congo.";
    $set('languages', $row, ['parent_id' => $row['parent_id'] ?? (int) $at,
        'description' => str_contains((string) $row['description'], $s) ? $row['description'] : trim($row['description'] . ' ' . $s)]);

    echo "B  Adamawa research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-ADAMAWA'")->fetch();
    if (!$row) throw new RuntimeException('Adamawa research_progress row not found');
    $set('research_progress', $row, ['status' => 'qc_in_progress', 'lgas_researched' => 21,
        'notes' => 'Batches 057–062 (30 Sep – 1 Oct 2026): 50 languages and the Biu–Mandara and Atlantic branches (Blench Atlas; 11 existing languages linked), 29 peoples plus Fulani, Chamba, Mumuye, Yandang and Jibu with people–LGA links for all 21 LGAs (state festival and tourist tables, Wikipedia), 226 INEC wards, traditional institutions (Council of Chiefs; Adamawa Emirate, Hama Bachama, Gangwari Ganye, Mubi Emirate; the seven traditional states of December 2024), culture and heritage (Sukur, UNESCO and declared No. 5; four NCMM museums; two proposed monuments; nine state tourist sites; 43 festivals), LGA profiles with headquarters, other names. Phase 4 QC run (NIGERIA_QC_ADAMAWA.md, 0 problems). Open: an official list of LGA headquarters; LGA creation dates; rulers of Gombi, Yungur and Maiha; Mubi Emirate grade; festival rites; Gumti Park; Bagale people; Glottocodes for Joole and Mukta.']);
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
