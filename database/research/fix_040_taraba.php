<?php
/**
 * Taraba batch 040 — changes outside the batch's own records (NIGERIA_BATCH_040_REVIEW.md; on approval).
 * Run after batch 040 has been imported.
 *
 *   A  Open dispute: the Ukwe Takum — the Kuteb's stool, or a first-class stool rotating among the
 *      Chamba, Jukun and Kuteb (2024 law). Positions as reported by Tribune and Channels (2024).
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_040_taraba.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$set = function (string $table, array $row, array $new) use ($db, $log, &$changes) {
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if (!$new) return;
    $old = array_intersect_key($row, $new);
    $db->prepare("UPDATE {$table} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . " WHERE id = ?")->execute([...array_values($new), $row['id']]);
    $log->execute([$table, $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 040)'], JSON_UNESCAPED_UNICODE)]);
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
    echo "A  Dispute (Ukwe Takum):\n";
    $DISPUTE = 'Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb)';
    $st = $db->prepare('SELECT id FROM claim_disputes WHERE topic = ?');
    $st->execute([$DISPUTE]);
    if (!$st->fetchColumn()) {
        $batch = $db->query("SELECT id FROM research_batches WHERE title LIKE 'Batch 040%' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
        if (!$batch) throw new RuntimeException('batch 040 has not been imported yet');
        $db->prepare("INSERT INTO claim_disputes (topic, nature, status, resolution_note, research_batch_id) VALUES (?, 'other', 'open', ?, ?)")
           ->execute([$DISPUTE, "Succession disputed since the death of Ukwe Ali Kufang in 1996 (Channels TV, 2024). A 2024 law (One Rotational 1st Class Chief and Three 3rd Class Chiefs) makes the stool rotate among the Chamba, Jukun and Kuteb; supported at the public hearing by the State Council of Chiefs, the Jukun Takum and the Chamba Takum; opposed by the Kuteb Yatso, Kuteb Youths and the Ukwe Takum Royal Palace, who regard the stool as the Kuteb's exclusive preserve (Tribune, 2024). Kuteb account: first Ukwe appointed 1914 (Wikipedia, Takum). Chamba context: the British replaced the Chamba chief at Takum (Fardon and Furniss 2021).", $batch]);
        $id = (int) $db->lastInsertId();
        $log->execute(['claim_disputes', $id, json_encode(['created' => true]), json_encode(['topic' => $DISPUTE, 'by' => 'owner approval (batch 040)'])]);
        $changes++;
        echo "  dispute #{$id} created\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
