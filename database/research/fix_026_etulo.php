<?php
/**
 * Benue batch 026 — changes to EXISTING links (NIGERIA_BATCH_026_REVIEW.md; on the owner's
 * approval). Run after batch 026 has been imported (it names its source).
 *
 *   Etulo → Buruku and Etulo → Katsina-Ala: needs corroboration → multiple sources / verified
 *   (Wikipedia and A Grammar of Etulo, 2025 — two independent sources, one academic); settlement
 *   status indigenous (shared area): the Etulo homeland lies within these Tiv-majority LGAs.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 * Usage: php database/research/fix_026_etulo.php [--apply] [--revert]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_026_etulo.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', 'entity_relations', ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            $old = json_decode($r['old_values'], true);
            $db->prepare("UPDATE entity_relations SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old))) . " WHERE id = ?")
               ->execute([...array_values($old), $r['entity_id']]);
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }
    if (!$db->query("SELECT 1 FROM sources WHERE url = 'https://books.openbookpublishers.com/10.11647/obp.0467/ch1.xhtml'")->fetchColumn()) {
        throw new RuntimeException('batch 026 has not been imported yet');
    }
    $note = 'Corroborated by A Grammar of Etulo (2025), which places nine Etulo clans in Buruku and five in Katsina-Ala (batch 026).';
    foreach (['BURUKU', 'KATSINA-ALA'] as $lga) {
        $st = $db->prepare("SELECT r.* FROM entity_relations r JOIN ethnic_groups g ON g.id = r.from_id JOIN admin_units u ON u.id = r.to_id
                            WHERE r.from_table = 'ethnic_groups' AND g.slug = 'etulo' AND r.relation_type = 'present_in' AND r.to_table = 'admin_units' AND u.stable_id = ?");
        $st->execute(["NG-LGA-BENUE-{$lga}"]);
        $rows = $st->fetchAll();
        if (count($rows) !== 1) throw new RuntimeException("expected one Etulo → {$lga} link, found " . count($rows));
        $row = $rows[0];
        $new = ['evidence_status' => 'multiple_sources', 'evidence_level' => 'verified', 'settlement_status' => 'indigenous_shared',
                'notes' => str_contains((string) $row['notes'], $note) ? $row['notes'] : trim($row['notes'] . ' ' . $note)];
        $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
        if (!$new) continue;
        $old = array_intersect_key($row, $new);
        $db->prepare("UPDATE entity_relations SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")
           ->execute([...array_values($new), $row['id']]);
        $log->execute([$row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 026)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  #{$row['id']} Etulo → {$lga}: " . implode(', ', array_keys($new)) . "\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
