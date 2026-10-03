<?php
/**
 * Batch 031 — change to an EXISTING record (NIGERIA_BATCH_031_REVIEW.md; on approval).
 * Basa-Makurdi (Benue): placed in the Kainji branch created by batch 031. Blench's Atlas (2020) lists
 * it as sub-entry 40.c of the Basa-Gurara–Basa-Benue–Basa-Makurdi cluster, Kainji: Western Kainji:
 * Kamuku–Basa group. Closes the Benue QC classification check. No Glottocode is set (none found).
 * Old value to activity_log; --revert restores. Idempotent. Default DRY RUN; --apply commits.
 */
define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_031_basa_makurdi.php';
$db->beginTransaction();
try {
    $row = $db->query("SELECT * FROM languages WHERE slug = 'basa-makurdi'")->fetch();
    if (!$row) throw new RuntimeException('Basa-Makurdi not found');
    if ($revert) {
        $old = $db->query("SELECT old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC LIMIT 1")->fetchColumn();
        if ($old) {
            $db->prepare('UPDATE languages SET parent_id = ? WHERE id = ?')->execute([json_decode($old, true)['parent_id'], $row['id']]);
            $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        }
        $apply ? $db->commit() : $db->rollBack();
        echo ($old ? '1 restored. ' : '0 restored. ') . ($apply ? "REVERTED.\n" : "DRY RUN.\n");
        exit(0);
    }
    $kainji = $db->query("SELECT id FROM languages WHERE slug = 'kainji'")->fetchColumn();
    if (!$kainji) throw new RuntimeException('batch 031 has not been imported yet (Kainji missing)');
    $n = 0;
    if ($row['parent_id'] === null) {
        $db->prepare('UPDATE languages SET parent_id = ? WHERE id = ?')->execute([$kainji, $row['id']]);
        $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                      VALUES (NULL, 'heritage_record_corrected', 'languages', ?, ?, ?, 'cli', ?)")
           ->execute([$row['id'], json_encode(['parent_id' => null]), json_encode(['parent_id' => (int) $kainji, 'by' => 'owner approval (batch 031)']), $AGENT]);
        $n = 1;
        echo "  Basa-Makurdi → Kainji\n";
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$n} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
