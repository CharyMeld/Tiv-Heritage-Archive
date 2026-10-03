<?php
/**
 * Owner-approved follow-up to fix_010 (2026-09-25): the law-history paragraph that fix_010
 * put on all six Tiv intermediate area pages is moved to the Tiv Traditional Council page,
 * and each area page keeps one short sentence. No fact is removed from the archive; the
 * six near-identical paragraphs become one, and the Lobi page (which fix_010 had pushed
 * just past the 300-word indexing rule with that shared paragraph) is noindex again.
 *
 * Replaces exact text only (else nothing changes); links Daily Trust to the council page
 * with batch 010's id (so its rollback removes the link); logs to activity_log. Idempotent.
 * Default is a DRY RUN; --apply commits. --revert undoes it exactly (the owner did not ask for
 * this change: reverted 2026-09-25), restoring the paragraph on the six area pages.
 * Usage: php database/research/fix_010b_move_law_history.php [--revert] [--apply]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/config/security.php';
require BASE_PATH . '/core/Cache.php';
require BASE_PATH . '/services/ContentIndexer.php';
Cache::init(BASE_PATH . '/storage/cache');

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$BATCH = 'Batch 010 — The Tor Tiv and the Tiv intermediate areas';

$HISTORY = 'According to Daily Trust, a first Benue chieftaincy law, passed on 7 April 2015, '
         . 'provided for intermediate area traditional councils for Jechira, Jemgbagh, Kwande, Sankera, Gwer and Lobi. I am Benue reports that the appointments made under it, '
         . 'gazetted on 7 May 2015, were revoked later that year by Governor Samuel Ortom. The councils now rest on the Benue State Council of Chiefs and Traditional Councils Law, 2016, '
         . 'an amended law signed under Ortom. Under it the six Tiv first-class chiefs were crowned, with four Idoma and Igede chiefs, at IBB Square in Makurdi on 14 May 2018. '
         . 'I am Benue credits the introduction of this tier to Tor Tiv IV, Alfred Akawe Torkula.';
$AREA_OLD = 'Each intermediate area is headed by a first-class chief with the title Tor. ' . $HISTORY;
$AREA_NEW = 'Each intermediate area is headed by a first-class chief with the title Tor. Its council rests on the Benue State Council of Chiefs and Traditional Councils Law, 2016, '
          . 'and the six Tiv first-class chiefs were crowned on 14 May 2018. The history of the law is given on the page of the Tiv Traditional Council.';
$TTC_END = 'grouped into six intermediate areas (see Connections).';
$TTC_NEW_END = "grouped into six intermediate areas (see Connections). Each is headed by a first-class chief with the title Tor.\n\n" . $HISTORY;

$db = Database::getInstance();
$done = [];
$touched = [];
$db->beginTransaction();
try {
    $st = $db->prepare('SELECT id FROM research_batches WHERE title = ?');
    $st->execute([$BATCH]);
    $batchId = (int) $st->fetchColumn();
    if (!$batchId) throw new RuntimeException("{$BATCH} not found");

    $get = $db->prepare('SELECT * FROM polities WHERE slug = ?');
    $set = $db->prepare('UPDATE polities SET description = ? WHERE id = ?');
    $log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                         VALUES (NULL, 'heritage_record_corrected', 'polities', ?, ?, ?, 'cli', 'fix_010b_move_law_history.php')");
    $replace = function (string $slug, string $old, string $new) use ($get, $set, $log, &$done, &$touched) {
        $get->execute([$slug]);
        $row = $get->fetch();
        if (!$row) throw new RuntimeException("polity {$slug} not found");
        // One text may contain the other (the council page's new ending extends its old one):
        // test the longer first, so neither direction runs twice or skips wrongly.
        $pending = strlen($old) > strlen($new) ? str_contains($row['description'], $old) : !str_contains($row['description'], $new);
        if (!$pending) { $done[] = "{$slug}: already done"; return; }
        if (substr_count($row['description'], $old) !== 1) throw new RuntimeException("{$slug}: expected text not found exactly once (run fix_010 first)");
        $set->execute([str_replace($old, $new, $row['description']), $row['id']]);
        $log->execute([$row['id'], json_encode(['description' => $old], JSON_UNESCAPED_UNICODE),
                       json_encode(['description' => $new, 'by' => 'owner approval 2026-09-25'], JSON_UNESCAPED_UNICODE)]);
        $touched[$row['id']] = true;
        $done[] = "{$slug}: updated";
    };

    $pair = fn(string $old, string $new) => $revert ? [$new, $old] : [$old, $new];
    $replace('tiv-traditional-council', ...$pair($TTC_END, $TTC_NEW_END));
    foreach (['jemgbagh', 'jechira', 'kwande', 'sankera', 'lobi', 'gwer'] as $a) $replace("{$a}-intermediate-area", ...$pair($AREA_OLD, $AREA_NEW));

    // The council page now carries the Daily Trust claims.
    $get->execute(['tiv-traditional-council']);
    $ttc = (int) $get->fetch()['id'];
    $dt = (int) $db->query("SELECT id FROM sources WHERE url = 'https://dailytrust.com/abu-king-shuluwa-from-the-soapbox-to-tiv-royalty/' LIMIT 1")->fetchColumn();
    if (!$dt) throw new RuntimeException('Daily Trust source not found');
    $has = $db->prepare("SELECT 1 FROM entity_sources WHERE entity_table = 'polities' AND entity_id = ? AND source_id = ?");
    if ($revert) {
        $del = $db->prepare("DELETE FROM entity_sources WHERE entity_table = 'polities' AND entity_id = ? AND source_id = ? AND research_batch_id = ? AND claim = ?");
        $del->execute([$ttc, $dt, $batchId, '2015 law providing for the intermediate area traditional councils']);
        if ($del->rowCount()) $done[] = 'tiv-traditional-council: Daily Trust link removed';
    }
    $has->execute([$ttc, $dt]);
    if (!$revert && !$has->fetchColumn()) {
        $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance, research_batch_id) VALUES ('polities', ?, ?, ?, 'supports', ?)")
           ->execute([$ttc, $dt, '2015 law providing for the intermediate area traditional councils', $batchId]);
        $done[] = 'tiv-traditional-council ← Daily Trust';
    }

    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
if ($apply) {
    $indexer = new ContentIndexer($db);
    foreach (array_keys($touched) as $id) $indexer->indexRecord('polities', $id);
}
echo ($apply ? '[APPLIED] ' : '[DRY RUN - rolled back] ') . implode("\n  ", $done) . "\n";
