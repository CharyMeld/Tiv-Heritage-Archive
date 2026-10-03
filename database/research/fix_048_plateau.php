<?php
/**
 * Plateau batch 048 — correction to an EXISTING record (NIGERIA_BATCH_048_REVIEW.md).
 *
 *   A  Youm people (batch 044b): remove the sentence 'Mikang LGA, where Ethnologue lists Youm, was
 *      created from Langtang.' No source was found for Mikang's origin (the state government's 2022
 *      booklet and Wikipedia give none); the statement was an unsourced inference.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_048_plateau.php';
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
    $SENT = ' Mikang LGA, where Ethnologue lists Youm, was created from Langtang.';
    $row = $db->query("SELECT * FROM ethnic_groups WHERE slug = 'youm'")->fetch();
    if (!$row) throw new RuntimeException('Youm record not found');
    echo "A  Youm:\n";
    if (str_contains((string) $row['description'], $SENT)) {
        $new = ['description' => str_replace($SENT, '', $row['description'])];
        $db->prepare('UPDATE ethnic_groups SET description = ? WHERE id = ?')->execute([$new['description'], $row['id']]);
        $log->execute(['ethnic_groups', $row['id'], json_encode(['description' => $row['description']], JSON_UNESCAPED_UNICODE),
                       json_encode($new + ['by' => 'owner approval (batch 048)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  ethnic_groups#{$row['id']}: description\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
