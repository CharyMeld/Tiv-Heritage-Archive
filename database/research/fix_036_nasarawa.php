<?php
/**
 * Nasarawa batch 036 — changes to EXISTING records (NIGERIA_BATCH_036_REVIEW.md; on approval).
 * Run after batch 036 has been imported (it uses the Jarawan record).
 *
 *   A  Mama: placed in the Jarawan branch (Atlas 'Bantu: Jarawan'; Glottolog) — parent_id was empty.
 *   B  Eloyi: Glottocode eloy1241 (Glottolog 'Ajiri', Benue-Congo Plateau); one sentence added.
 *   C  Idun: Glottocode idun1241 (Glottolog 'Dũya', Plateau > Koroic); one sentence added.
 *   D  Open classification dispute: Eloyi — Idomoid or Plateau.
 *   E  Nasarawa research progress: status 'quality control in progress' (Phase 4 QC run).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_036_nasarawa.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 036)'], JSON_UNESCAPED_UNICODE)]);
    $changes++;
    echo "  {$table}#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
};
$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            if ($r['entity_type'] === 'claim_disputes') { $db->prepare('DELETE FROM claim_disputes WHERE id = ?')->execute([$r['entity_id']]); $changes++; continue; }
            $old = json_decode($r['old_values'], true);
            $db->prepare("UPDATE {$r['entity_type']} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old))) . " WHERE id = ?")->execute([...array_values($old), $r['entity_id']]);
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }
    $jar = $db->query("SELECT id FROM languages WHERE slug = 'jarawan'")->fetchColumn();
    if (!$jar) throw new RuntimeException('batch 036 has not been imported yet (Jarawan missing)');
    $lang = function (string $slug) use ($db) {
        $r = $db->query("SELECT * FROM languages WHERE slug = " . $db->quote($slug))->fetch();
        if (!$r) throw new RuntimeException("language {$slug} not found");
        return $r;
    };
    $append = fn(array $row, string $s) => str_contains((string) $row['description'], $s) ? $row['description'] : trim($row['description'] . ' ' . $s);
    echo "A  Mama → Jarawan:\n";
    $row = $lang('mama');
    if ($row['parent_id'] === null) $set('languages', $row, ['parent_id' => (int) $jar]);
    echo "B  Eloyi:\n";
    $row = $lang('eloyi');
    $set('languages', $row, ['glottocode' => 'eloy1241', 'description' => $append($row,
        "Glottolog lists it under the name Ajiri (eloy1241) and places it directly under Benue-Congo Plateau, not in Idomoid; the archive keeps the Atlas's Idomoid placement and records the disagreement.")]);
    echo "C  Idun:\n";
    $row = $lang('idun');
    $set('languages', $row, ['glottocode' => 'idun1241', 'description' => $append($row,
        "Glottolog lists it as Dũya (idun1241), in the Koroic group of Plateau, matching the Atlas's placement in Koro.")]);
    echo "D  Dispute (Eloyi classification):\n";
    $DISPUTE = 'Eloyi (Afo): Idomoid or Plateau';
    $st = $db->prepare('SELECT id FROM claim_disputes WHERE topic = ?');
    $st->execute([$DISPUTE]);
    if (!$st->fetchColumn()) {
        $batch = $db->query("SELECT id FROM research_batches WHERE title LIKE 'Batch 036%' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
        $db->prepare("INSERT INTO claim_disputes (topic, nature, status, resolution_note, research_batch_id) VALUES (?, 'classification', 'open', ?, ?)")
           ->execute([$DISPUTE, "Blench's Atlas (2020): 'Benue–Congo: Plateau or Volta-Niger: Idomoid'. Glottolog 5.3: Benue-Congo Plateau (eloy1241, 'Ajiri'). The archive's record places Eloyi in Idomoid (batch 031).", $batch]);
        $id = (int) $db->lastInsertId();
        $log->execute(['claim_disputes', $id, json_encode(['created' => true]), json_encode(['topic' => $DISPUTE, 'by' => 'owner approval (batch 036)'])]);
        $changes++;
        echo "  dispute #{$id} created\n";
    }
    echo "E  Nasarawa research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-NASARAWA'")->fetch();
    if (!$row) throw new RuntimeException('Nasarawa research_progress row not found');
    $set('research_progress', $row, ['status' => 'qc_in_progress',
        'notes' => 'Batches 031–036 (26 Sep 2026): languages (Blench Atlas), 21 peoples with LGA links, 147 INEC wards, traditional institutions (part 1), culture and heritage (part 1), LGA profiles with headquarters, other names and classification. Phase 4 QC run (NIGERIA_QC_NASARAWA.md, 0 problems). Needs review: Karu headquarters. Open: Koro language, Council of Chiefs and 17 first-class stools, LGA creation dates.']);
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
