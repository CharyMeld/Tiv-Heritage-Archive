<?php
/**
 * Benue batch 024 — changes to EXISTING records (NIGERIA_BATCH_024_REVIEW.md; applied only on
 * the owner's approval). Run after batch 024 has been imported (it uses its Daily Trust source).
 *
 *   A  Abakpa summary: it says no source describes them; I am Benue now does.
 *   B  Hausa → Makurdi link: needs corroboration → multiple sources / verified (Wikipedia and
 *      Daily Trust 2012, two independent Tier 3 sources); settlement status significant
 *      contemporary settlement (Hausa settlers from Kano since the railway-bridge era, at Wadata).
 *   C  Benue research progress: not started → in progress (LGA-level research began with batch 023).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 * Usage: php database/research/fix_024_benue.php [--apply] [--revert]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_024_benue.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")
       ->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 024)'], JSON_UNESCAPED_UNICODE)]);
    $changes++;
    echo "  {$table}#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
};

$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            $old = json_decode($r['old_values'], true);
            $db->prepare("UPDATE {$r['entity_type']} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old))) . " WHERE id = ?")
               ->execute([...array_values($old), $r['entity_id']]);
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }

    $dt = (int) $db->query("SELECT id FROM sources WHERE url = 'https://dailytrust.com/the-hausa-of-makurdi/'")->fetchColumn();
    if (!$dt) throw new RuntimeException('batch 024 has not been imported yet (Daily Trust source missing)');

    echo "A  Abakpa summary:\n";
    $row = $db->query("SELECT * FROM ethnic_groups WHERE slug = 'abakpa'")->fetch();
    $set('ethnic_groups', $row, ['summary' => 'The Abakpa, also spelt Abakwa, are one of the peoples of Benue State. I am Benue places them mainly at Abakwa, near Tyo-Wanye in Buruku LGA; little else about them has been documented.']);

    echo "B  Hausa → Makurdi:\n";
    $row = $db->query("SELECT r.* FROM entity_relations r JOIN ethnic_groups g ON g.id = r.from_id JOIN admin_units u ON u.id = r.to_id
                       WHERE r.from_table = 'ethnic_groups' AND g.slug = 'hausa' AND r.relation_type = 'present_in'
                         AND r.to_table = 'admin_units' AND u.stable_id = 'NG-LGA-BENUE-MAKURDI'")->fetch();
    if (!$row) throw new RuntimeException('Hausa → Makurdi link not found');
    $note = 'Corroborated by Daily Trust (2012): Hausa settlers from Kano since the building of the railway bridge, centred on Wadata (batch 024).';
    $set('entity_relations', $row, ['evidence_status' => 'multiple_sources', 'evidence_level' => 'verified', 'settlement_status' => 'significant_contemporary',
                                    'notes' => str_contains((string) $row['notes'], $note) ? $row['notes'] : trim($row['notes'] . ' ' . $note)]);

    echo "C  Benue research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-BENUE'")->fetch();
    if (!$row) throw new RuntimeException('Benue research_progress row not found');
    $set('research_progress', $row, ['status' => 'in_progress',
        'notes' => 'LGA-level research under the master brief began 25 Sep 2026: batch 023 (Idoma LGAs and institutions), batch 024 (INEC wards, Abakpa).']);

    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
