<?php
/**
 * Taraba batch 043 — changes to EXISTING records (NIGERIA_BATCH_043_REVIEW.md; owner approved 2026-09-30).
 * Run after batch 043 has been imported (it uses the Southern Bantoid record).
 *
 *   A  Buru: placed in Southern Bantoid (Atlas 'South Bantoid: unclassified'; Glottolog) + buru1326.
 *   B  Mambila: Glottocode mamb1312 (Glottolog group); kept as ONE record (owner's choice), split noted.
 *   C  Gbaya: Glottocode nort2775 (Northwest Gbaya), marked probable in the text.
 *   D  Glottolog notes for Dirim, Joole and Kulung (Chadic) (no Glottocode recorded).
 *   E  Taraba research progress: status 'quality control in progress' (Phase 4 QC run).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_043_taraba.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 043)'], JSON_UNESCAPED_UNICODE)]);
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
    $sb = $db->query("SELECT id FROM languages WHERE slug = 'southern-bantoid'")->fetchColumn();
    if (!$sb) throw new RuntimeException('batch 043 has not been imported yet (Southern Bantoid missing)');
    $lang = function (string $slug) use ($db) {
        $r = $db->query("SELECT * FROM languages WHERE slug = " . $db->quote($slug))->fetch();
        if (!$r) throw new RuntimeException("language {$slug} not found");
        return $r;
    };
    $append = fn(array $row, string $s) => str_contains((string) $row['description'], $s) ? $row['description'] : trim($row['description'] . ' ' . $s);

    echo "A  Buru → Southern Bantoid:\n";
    $row = $lang('buru');
    $set('languages', $row, ['parent_id' => $row['parent_id'] ?? (int) $sb, 'glottocode' => 'buru1326', 'description' => $append($row,
        "Glottolog lists it as Buru (Nigeria) (buru1326), a dialect of Buru-Angwe (ISO bqw), and also places it in Southern Bantoid; the archive follows the Atlas in treating Buru as a language.")]);
    echo "B  Mambila (one record):\n";
    $row = $lang('mambila');
    $set('languages', $row, ['glottocode' => 'mamb1312', 'description' => $append($row,
        "Glottolog treats Mambila as a group (mamb1312) rather than a single language, dividing the Nigerian varieties between Western Mambila (nige1255, ISO mzk) and Donga Mambila (came1252, ISO mcu); the archive keeps one record, as the Atlas does.")]);
    echo "C  Gbaya:\n";
    $row = $lang('gbaya-taraba');
    $set('languages', $row, ['glottocode' => 'nort2775', 'description' => $append($row,
        "The only Gbaya language Glottolog lists for Nigeria is Northwest Gbaya (nort2775, ISO gya), spoken mainly in Cameroon and the Central African Republic; the match with the Atlas's Gbaya of Bali LGA is probable, not confirmed.")]);
    echo "D  Glottolog notes:\n";
    $row = $lang('dirim');
    $set('languages', $row, ['description' => $append($row,
        "Glottolog has no separate entry for Dirim; it lists a Dirim-Nnakenyare group (diri1260) within Dakoid, which agrees with the Atlas's classification.")]);
    $row = $lang('joole');
    $set('languages', $row, ['description' => $append($row,
        "Glottolog has no entry under this name; its nearest is Jaule, which it treats as a dialect of Dza in the same Jen group, but no source found equates the two.")]);
    $row = $lang('kulung-chadic');
    $set('languages', $row, ['description' => $append($row,
        "Glottolog has no entry for it; Glottolog's Kulung (Nigeria) (kulu1255) is the Jarawan Bantu Kulung, a separate language.")]);

    echo "E  Taraba research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-TARABA'")->fetch();
    if (!$row) throw new RuntimeException('Taraba research_progress row not found');
    $set('research_progress', $row, ['status' => 'qc_in_progress',
        'notes' => 'Batches 038–043 (26–30 Sep 2026): languages (Blench Atlas), 11 peoples, 168 INEC wards, traditional institutions (State Council of Chiefs, six first-class stools, Ukwe Takum disputed), culture and heritage, LGA profiles with headquarters, other names and classification. Phase 4 QC run (NIGERIA_QC_TARABA.md, 0 problems). Open: Glottocodes for Dampar, Dirim, Joole, Kulung (Chadic); Hausa and Fulfulde language records; Council of Chiefs links; LGA creation dates.']);
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
