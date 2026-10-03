<?php
/**
 * Taraba batch 041 — changes outside the batch's own records (NIGERIA_BATCH_041_REVIEW.md; on approval).
 * Run after batch 041 has been imported.
 *
 *   A  Aku Uka of Wukari (polities#78): add the 2022 succession (Daily Trust, 2022) to the description
 *      and cite the article. Daily Trust's '25th' follows the count in which Kuvyon II was the 24th
 *      (Wikipedia gives '27th (or 24th)'); its 'January 2021' for his death is not used (October 2021:
 *      Channels, Vanguard, ThisDay, Wikipedia).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_041_taraba.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 041)'], JSON_UNESCAPED_UNICODE)]);
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
    $batch = $db->query("SELECT id FROM research_batches WHERE title LIKE 'Batch 041%' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
    if (!$batch) throw new RuntimeException('batch 041 has not been imported yet');
    $src = $db->query("SELECT id FROM sources WHERE url = 'https://dailytrust.com/new-vista-as-25th-aku-uka-of-wukari-mounts-throne/'")->fetchColumn();
    if (!$src) throw new RuntimeException('Daily Trust 2022 source missing (import batch 041 first)');

    echo "A  Aku Uka of Wukari (2022 succession):\n";
    $row = $db->query("SELECT id, description FROM polities WHERE slug = 'aku-uka-of-wukari'")->fetch();
    if (!$row) throw new RuntimeException('aku-uka-of-wukari not found');
    $ADD = "His successor, Manu Ishaku Adda Ali of the Ba Ma ruling house, was chosen by the kingmakers from among princes of the two ruling houses, Ba Ma and Ba Gya, and crowned in the Jukun manner (Pankan) at Bye Vyi on 28 January 2022, after which he performed rites at the Jukun sacred site of Puje, at Bye Vyi and at Kuntsa (Daily Trust, 2022). Daily Trust calls him the 25th Aku Uka; Wikipedia counts Kuvyon II as the 27th, or the 24th, Aku Uka, so the numbering depends on the list used.";
    if (!str_contains($row['description'], 'Manu Ishaku Adda Ali')) $set('polities', $row, ['description' => rtrim($row['description']) . "\n\n" . $ADD]);
    $st = $db->prepare("SELECT id FROM entity_sources WHERE entity_table = 'polities' AND entity_id = ? AND source_id = ?");
    $st->execute([$row['id'], $src]);
    if (!$st->fetchColumn()) {
        $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance) VALUES ('polities', ?, ?, ?, 'supports')")
           ->execute([$row['id'], $src, 'Succession of Manu Ishaku Adda Ali (crowned 28 January 2022); ruling houses; rites at Puje']);
        $id = (int) $db->lastInsertId();
        $log->execute(['entity_sources', $id, json_encode(['created' => true]), json_encode(['polity' => $row['id'], 'source' => $src, 'by' => 'owner approval (batch 041)'])]);
        $changes++;
        echo "  entity_sources#{$id}: Daily Trust 2022 cited\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
