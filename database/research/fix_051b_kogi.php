<?php
/**
 * Kogi batch 051b — correction to an EXISTING record (NIGERIA_BATCH_051B_REVIEW.md).
 *
 *   A  Yoruba language (batch 051): 'an Okun group of Kogi State (Yagba, Gbede and Ijumu)' -> 'an Okun
 *      group (Yagba, Gbede and Ijumu)'. The Atlas's grouping does not say the group is 'of Kogi State'
 *      (it places Yoruba in 'western LGAs in Kogi State' separately); the combination was my inference.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_051b_kogi.php';
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
    $OLD = 'an Okun group of Kogi State (Yagba, Gbede and Ijumu)';
    $NEW = 'an Okun group (Yagba, Gbede and Ijumu)';
    $row = $db->query("SELECT * FROM languages WHERE slug = 'yoruba'")->fetch();
    if (!$row) throw new RuntimeException('Yoruba language record not found');
    echo "A  Yoruba language:\n";
    if (str_contains((string) $row['description'], $OLD)) {
        $new = ['description' => str_replace($OLD, $NEW, $row['description'])];
        $db->prepare('UPDATE languages SET description = ? WHERE id = ?')->execute([$new['description'], $row['id']]);
        $log->execute(['languages', $row['id'], json_encode(['description' => $row['description']], JSON_UNESCAPED_UNICODE),
                       json_encode($new + ['by' => 'owner approval (batch 051b)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  languages#{$row['id']}: description\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
