<?php
/**
 * Benue batch 023 — changes to EXISTING links (shown in NIGERIA_BATCH_023_REVIEW.md; applied
 * only on the owner's approval). Run after batch 023 has been imported (it uses its sources).
 *
 *   A  Idoma → Ado, Agatu, Apa, Ogbadibo, Ohimini: needs corroboration → multiple sources /
 *      well documented. Ethnologue (via Wikipedia) is now corroborated by I am Benue's list of
 *      the seven Idoma LGAs. Settlement status: indigenous core.
 *   B  Idoma → Okpokwu, Oturkpo (already verified): settlement status indigenous core.
 *   C  Idoma → Obi: note only (Obi is the Igede area; I am Benue's seven exclude it). Unchanged level.
 *   D  Igede → Oju: indigenous core. Igede → Obi: needs corroboration → multiple sources /
 *      well documented (Wikipedia + the Igede Youth Coalition, Idoma Voice 2017); indigenous core.
 *   E  Tiv → its 14 Benue LGAs: settlement status indigenous core (as in the approved Benue sample).
 *   F  A dispute record: whether the Igede (Oju, Obi) come under the Idoma Area Traditional Council.
 *
 * Only the named fields change; old values are written to activity_log, and --revert restores them.
 * Idempotent. Default is a DRY RUN; --apply commits.
 * Usage: php database/research/fix_023_benue_links.php [--apply] [--revert]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_023_benue_links.php';
$BATCH_TITLE = "Batch 023 — Benue: the Idoma LGAs, the Och'Idoma and the Idoma Area Traditional Council";
$DISPUTE = "Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma";

$db->beginTransaction();
try {
    if ($revert) {
        $rows = $db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll();
        $n = 0;
        foreach ($rows as $r) {
            $old = json_decode($r['old_values'], true);
            if ($r['entity_type'] === 'claim_disputes') { $db->prepare('DELETE FROM claim_disputes WHERE id = ?')->execute([$r['entity_id']]); $n++; continue; }
            $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old)));
            $db->prepare("UPDATE entity_relations SET {$sets} WHERE id = ?")->execute([...array_values($old), $r['entity_id']]);
            $n++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$n} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }

    $batch = (int) $db->query("SELECT id FROM research_batches WHERE title = " . $db->quote($BATCH_TITLE))->fetchColumn();
    if (!$batch) throw new RuntimeException('batch 023 has not been imported yet');
    $link = function (string $group, string $lga) use ($db): array {
        $st = $db->prepare("SELECT r.* FROM entity_relations r JOIN ethnic_groups g ON g.id = r.from_id JOIN admin_units u ON u.id = r.to_id
                            WHERE r.from_table = 'ethnic_groups' AND g.slug = ? AND r.relation_type = 'present_in'
                              AND r.to_table = 'admin_units' AND u.stable_id = ?");
        $st->execute([$group, 'NG-LGA-BENUE-' . strtoupper($lga)]);
        $rows = $st->fetchAll();
        if (count($rows) !== 1) throw new RuntimeException("expected one {$group} → {$lga} link, found " . count($rows));
        return $rows[0];
    };
    $log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                         VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
    $changes = 0;
    $set = function (array $row, array $new) use ($db, $log, &$changes) {
        $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
        if (!$new) return;
        $old = array_intersect_key($row, $new);
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new)));
        $db->prepare("UPDATE entity_relations SET {$sets} WHERE id = ?")->execute([...array_values($new), $row['id']]);
        $log->execute(['entity_relations', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 023)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  #{$row['id']}: " . implode(', ', array_map(fn($k) => "{$k} " . ($k === 'notes' ? '(+ note)' : "'" . ($old[$k] ?? 'NULL') . "' → '{$new[$k]}'"), array_keys($new))) . "\n";
    };
    // Appends a note once; if it is already there the notes stay as they are (no change).
    $addNote = fn(array $row, string $note) => str_contains((string) $row['notes'], $note) ? (string) $row['notes'] : trim((string) $row['notes']) . ' ' . $note;

    echo "A  Idoma, five LGAs now corroborated:\n";
    foreach (['ado', 'agatu', 'apa', 'ogbadibo', 'ohimini'] as $l) {
        $r = $link('idoma', $l);
        $set($r, ['evidence_status' => 'multiple_sources', 'evidence_level' => 'well_documented', 'settlement_status' => 'indigenous_core',
                  'notes' => $addNote($r, "Corroborated by I am Benue, which lists it among the seven Idoma LGAs (batch 023).")]);
    }
    echo "B  Idoma, Okpokwu and Oturkpo:\n";
    foreach (['okpokwu', 'oturkpo'] as $l) {
        $r = $link('idoma', $l);
        $set($r, ['settlement_status' => 'indigenous_core', 'notes' => $addNote($r, "Also one of the seven Idoma LGAs listed by I am Benue (batch 023).")]);
    }
    echo "C  Idoma, Obi (note only):\n";
    $r = $link('idoma', 'obi');
    $set($r, ['notes' => $addNote($r, "I am Benue's seven Idoma LGAs do not include Obi, which the Benue State Government and Igede voices describe as the Igede area; its place under the Idoma council is disputed (batch 023).")]);
    echo "D  Igede, Oju and Obi:\n";
    $r = $link('igede', 'oju');
    $set($r, ['settlement_status' => 'indigenous_core']);
    $r = $link('igede', 'obi');
    $set($r, ['evidence_status' => 'multiple_sources', 'evidence_level' => 'well_documented', 'settlement_status' => 'indigenous_core',
              'notes' => $addNote($r, "Corroborated by the Igede Youth Coalition, which names Obi and Oju as the main Igede LGAs (Idoma Voice, 2017; batch 023).")]);
    echo "E  Tiv, fourteen LGAs:\n";
    foreach (['buruku', 'gboko', 'guma', 'gwer-east', 'gwer-west', 'katsina-ala', 'konshisha', 'kwande', 'logo', 'makurdi', 'tarka', 'ukum', 'ushongo', 'vandeikya'] as $l) {
        $set($link('tiv', $l), ['settlement_status' => 'indigenous_core']);
    }
    echo "F  Dispute record:\n";
    $st = $db->prepare('SELECT id FROM claim_disputes WHERE topic = ?');
    $st->execute([$DISPUTE]);
    if (!$st->fetchColumn()) {
        $db->prepare("INSERT INTO claim_disputes (topic, nature, status, research_batch_id) VALUES (?, 'classification', 'open', ?)")->execute([$DISPUTE, $batch]);
        $id = (int) $db->lastInsertId();
        $log->execute(['claim_disputes', $id, json_encode(['created' => true]), json_encode(['topic' => $DISPUTE, 'by' => 'owner approval (batch 023)'])]);
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
