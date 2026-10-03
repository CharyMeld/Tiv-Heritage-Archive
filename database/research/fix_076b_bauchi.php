<?php
/**
 * Bauchi batch 076b — change to an EXISTING record (NIGERIA_BATCH_076B_REVIEW.md; owner instruction 2026-10-01:
 * "the system requires keeping both variations").
 *
 *   A  Bauchi State description: the state government's list of main ethnic groups names the 'Sayawa'; the
 *      people's own name is Zaar (Blench's Atlas, which notes the Saya terms are now considered derogatory).
 *      Both are kept: 'Sayawa' -> 'Sayawa (Zaar)'.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_076b_bauchi.php';
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
    echo "A  Bauchi State description:\n";
    $row = $db->query("SELECT id, description FROM admin_units WHERE stable_id = 'NG-STATE-BAUCHI'")->fetch();
    if (!$row) throw new RuntimeException('Bauchi State record not found');
    $from = 'Gerawa, Sayawa, Jarawa';
    $to = 'Gerawa, Sayawa (Zaar), Jarawa';
    if (str_contains((string) $row['description'], $to)) {
        echo "  already done\n";
    } elseif (substr_count((string) $row['description'], $from) !== 1) {
        throw new RuntimeException("expected exactly one '{$from}' in the description");
    } else {
        $new = str_replace($from, $to, $row['description']);
        $db->prepare('UPDATE admin_units SET description = ? WHERE id = ?')->execute([$new, $row['id']]);
        $log->execute(['admin_units', $row['id'], json_encode(['description' => $row['description']], JSON_UNESCAPED_UNICODE),
                       json_encode(['description' => '(Sayawa -> Sayawa (Zaar))', 'by' => 'owner instruction (batch 076b)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  admin_units#{$row['id']}: description\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
