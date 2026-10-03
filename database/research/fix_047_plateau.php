<?php
/**
 * Plateau batch 047 — correction to an EXISTING source (NIGERIA_BATCH_047_REVIEW.md). Run BEFORE the
 * batch 047 import, so that the import reuses the corrected source by URL.
 *
 *   A  Source #559 'National Monuments' (NCMM): its URL /national-monuments/ is the NCMM museum contacts
 *      directory, not the list of declared monuments. Point it at /list-of-national-monuments/ and
 *      restate its note; the conclusion drawn from it (no declared monument in Benue or Taraba) still
 *      holds on the real list (checked 2026-09-30).
 *
 * Old values go to activity_log; --revert restores them (run it AFTER rolling back batch 047).
 * Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_047_plateau.php';
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
    $OLD = 'https://museum.ng/national-monuments-in-nigeria/national-monuments/';
    $NEW = 'https://museum.ng/national-monuments-in-nigeria/list-of-national-monuments/';
    $row = $db->query("SELECT * FROM sources WHERE id = 559")->fetch();
    if (!$row) throw new RuntimeException('source #559 not found');
    $clash = $db->prepare('SELECT id FROM sources WHERE url = ? AND id <> 559');
    $clash->execute([$NEW]);
    if ($c = $clash->fetchColumn()) throw new RuntimeException("another source (#{$c}) already has the list URL — import was run before this fix?");
    echo "A  Source #559:\n";
    $new = [
        'url' => $NEW,
        'title' => 'List of National Monuments',
        'notes' => 'The NCMM list of the 65 declared national monuments (S/No. 1–65). No Benue, Nasarawa or Taraba monument is on it; Plateau has Nos. 61–63, the Stone Causeways at Batura, Forof and Tading, Bokkos (checked 2026-09-30). Corrected by fix_047: this source first pointed at /national-monuments/, which is the NCMM museum contacts directory.',
    ];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        if ($row['url'] !== $OLD && isset($new['url'])) throw new RuntimeException("unexpected URL on #559: {$row['url']}");
        $db->prepare('UPDATE sources SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = 559')->execute(array_values($new));
        $log->execute(['sources', 559, json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 047)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  sources#559: " . implode(', ', array_keys($new)) . "\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
