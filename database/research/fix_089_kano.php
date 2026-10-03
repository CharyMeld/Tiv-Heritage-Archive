<?php
/**
 * Kano batch 089 — changes outside the batch's own records (NIGERIA_BATCH_089_REVIEW.md; on approval).
 * Run after batch 089 has been imported; revert BEFORE rolling the batch back (the dispute cites the batch).
 *
 *   A  Open dispute: the Kano emirship — Muhammadu Sanusi II (reinstated by the Kano State Government in 2024) or
 *      Aminu Ado Bayero (Emir from 2020; appeal pending at the Supreme Court). Positions as reported by Channels TV
 *      (2025), The Guardian (2026) and Wikipedia.
 *
 * Old values go to activity_log; --revert restores them. Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_089_kano.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            if ($r['entity_type'] === 'claim_disputes') { $db->prepare('DELETE FROM claim_disputes WHERE id = ?')->execute([$r['entity_id']]); $changes++; continue; }
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }
    echo "A  Dispute (Kano emirship):\n";
    $DISPUTE = 'Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero';
    $st = $db->prepare('SELECT id FROM claim_disputes WHERE topic = ?');
    $st->execute([$DISPUTE]);
    if (!$st->fetchColumn()) {
        $batch = $db->query("SELECT id FROM research_batches WHERE title LIKE 'Batch 089%' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: null;
        if (!$batch) throw new RuntimeException('batch 089 has not been imported yet');
        $db->prepare("INSERT INTO claim_disputes (topic, nature, status, resolution_note, research_batch_id) VALUES (?, 'other', 'open', ?, ?)")
           ->execute([$DISPUTE, "Kano State Government position: on 23 May 2024 it reinstated Muhammadu Sanusi II (Emir 2014–2020) as the 16th Emir under the Kano State Emirate Council (Repeal) Law 2024, which also removed Aminu Ado Bayero, the 15th Emir (from 9 March 2020), and the four first-class emirs of 2019 (Channels TV, 26 March 2025; Wikipedia). On 10 January 2025 the Court of Appeal set aside the Federal High Court judgment that had nullified the reinstatement, holding that court lacked jurisdiction over chieftaincy matters (Kano State Government, a party to the case), and in March 2025 it stayed actions against the reinstatement pending the Supreme Court (Channels TV). Bayero position: he has not accepted his removal and occupies a mini-palace at Nasarawa (Wikipedia); an appeal by Aminu Baba Dan Agundi, a senior emirate councillor, in his defence against the Kano State Government and House of Assembly is before the Supreme Court, which on 20 April 2026 adjourned it to 19 April 2027 (The Guardian). Not resolved as of October 2026.", $batch]);
        $id = (int) $db->lastInsertId();
        $log->execute(['claim_disputes', $id, json_encode(['created' => true]), json_encode(['topic' => $DISPUTE, 'by' => 'owner approval (batch 089)'])]);
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
