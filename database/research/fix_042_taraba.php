<?php
/**
 * Taraba batch 042 — changes to EXISTING records (NIGERIA_BATCH_042_REVIEW.md; on approval).
 * Run after batch 042 has been imported.
 *
 *   A  Taraba research progress: all 16 LGAs researched (languages, peoples, wards, traditional
 *      institutions, culture and heritage, LGA profiles); status stays 'in progress' until Phase 4 QC.
 *   B  Yakoko Stone Burial Ground and Puje (batch 042): add that INEC lists a ward of the same name
 *      (Yakoko in Zing LGA; Puje in Wukari LGA), citing the INEC polling-unit directory (batch 039).
 *      The ward names support, but do not prove, where the sites are; admin units are not changed.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_042_taraba.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 042)'], JSON_UNESCAPED_UNICODE)]);
    $changes++;
    echo "  {$table}#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
};
$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            if ($r['entity_type'] === 'entity_sources') { $db->prepare('DELETE FROM entity_sources WHERE id = ?')->execute([$r['entity_id']]); $changes++; continue; }
            $old = json_decode($r['old_values'], true);
            $db->prepare("UPDATE {$r['entity_type']} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old))) . " WHERE id = ?")->execute([...array_values($old), $r['entity_id']]);
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }
    if (!$db->query("SELECT id FROM research_batches WHERE title LIKE 'Batch 042%' LIMIT 1")->fetchColumn()) throw new RuntimeException('batch 042 has not been imported yet');

    echo "A  Taraba research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-TARABA'")->fetch();
    if (!$row) throw new RuntimeException('Taraba research_progress row not found');
    $set('research_progress', $row, ['lgas_total' => 16, 'lgas_researched' => 16, 'lgas_needs_review' => 0, 'status' => 'in_progress',
        'notes' => 'Batches 038–042 (26 Sep 2026): languages (Blench Atlas), 11 peoples, 168 INEC wards, traditional institutions (State Council of Chiefs, six first-class stools, Ukwe Takum disputed), culture and heritage, LGA profiles with headquarters. Next: Phase 4 QC.']);

    echo "B  INEC ward names for Yakoko and Puje:\n";
    $src = $db->query("SELECT id FROM sources WHERE url = 'https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Taraba.pdf'")->fetchColumn();
    if (!$src) throw new RuntimeException('INEC Taraba polling-unit directory source not found');
    $ADD = [
        'yakoko-stone-burial-ground' => "INEC's directory of polling units lists a Yakoko ward in Zing LGA, which supports placing the site there.",
        'puje' => "INEC's directory of polling units lists a Puje ward in Wukari LGA; the sacred site is probably in or near it, but this is not confirmed.",
    ];
    foreach ($ADD as $slug => $sentence) {
        $st = $db->prepare('SELECT id, description FROM places WHERE slug = ?');
        $st->execute([$slug]);
        $p = $st->fetch();
        if (!$p) throw new RuntimeException("{$slug} not found");
        if (!str_contains($p['description'], "INEC's directory of polling units")) $set('places', $p, ['description' => rtrim($p['description']) . ' ' . $sentence]);
        $chk = $db->prepare("SELECT id FROM entity_sources WHERE entity_table = 'places' AND entity_id = ? AND source_id = ?");
        $chk->execute([$p['id'], $src]);
        if (!$chk->fetchColumn()) {
            $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance) VALUES ('places', ?, ?, ?, 'mentions')")
               ->execute([$p['id'], $src, $slug === 'puje' ? 'INEC ward Puje, Wukari LGA' : 'INEC ward Yakoko, Zing LGA']);
            $id = (int) $db->lastInsertId();
            $log->execute(['entity_sources', $id, json_encode(['created' => true]), json_encode(['place' => $p['id'], 'source' => $src, 'by' => 'owner approval (batch 042)'])]);
            $changes++;
            echo "  entity_sources#{$id}: INEC directory cited for {$slug}\n";
        }
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
