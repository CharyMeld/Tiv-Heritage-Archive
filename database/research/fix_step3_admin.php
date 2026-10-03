<?php
/**
 * Owner-approved fixes from master-prompt Step 3 (NIGERIA_STEP3_ADMIN_INCONSISTENCIES.md),
 * 25 September 2026:
 *   A1  LGA "Ikot Abasi (Village)" (Akwa Ibom) renamed "Ikot Abasi", as in the Constitution's
 *       First Schedule and Statoids; the old address redirects (301).
 *   A2  Abuja recorded as the capital city of the Federal Capital Territory (Statoids' state
 *       table; Wikipedia), with the Constitution s.298 as context.
 *   A3  Anambra State dated from 27 August 1991 (the present state, when Enugu State was
 *       separated), per the Anambra State Government and NIPC; 3 February 1976 is kept as the
 *       creation of the earlier Anambra State (the existing change record is unchanged).
 *       Its one-line summary sentence is corrected to match.
 *   C   Eight spelling variants from Statoids' LGA table stored as searchable names.
 *   B   (Idosi-Osi) — owner chose to keep the Constitution's spelling: no change.
 *
 * Idempotent. Every change is written to activity_log. Default is a DRY RUN; --apply commits.
 * Usage: php database/research/fix_step3_admin.php [--apply]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/config/security.php';
require BASE_PATH . '/core/Cache.php';
require BASE_PATH . '/services/ContentIndexer.php';
Cache::init(BASE_PATH . '/storage/cache');

$apply = in_array('--apply', $argv, true);
$db = Database::getInstance();
$done = [];
$touched = [];
$srcId = function (string $url) use ($db): int {
    $st = $db->prepare('SELECT id FROM sources WHERE url = ? LIMIT 1');
    $st->execute([$url]);
    $id = (int) $st->fetchColumn();
    if (!$id) throw new RuntimeException("source not found: {$url}");
    return $id;
};
$unit = function (string $type, string $slug, ?string $parentSlug = null) use ($db): array {
    $sql = 'SELECT u.* FROM admin_units u' . ($parentSlug ? ' JOIN admin_units p ON p.id = u.parent_id AND p.slug = ?' : '') . ' WHERE u.unit_type = ? AND u.slug = ?';
    $st = $db->prepare($sql);
    $st->execute($parentSlug ? [$parentSlug, $type, $slug] : [$type, $slug]);
    $row = $st->fetch();
    if (!$row) throw new RuntimeException("unit {$type}:{$slug} not found");
    return $row;
};
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', 'fix_step3_admin.php')");
$logIt = fn(string $t, int $id, array $old, array $new) => $log->execute([$t, $id, json_encode($old, JSON_UNESCAPED_UNICODE),
    json_encode($new + ['by' => 'owner approval 2026-09-25 (Step 3)'], JSON_UNESCAPED_UNICODE)]);

$db->beginTransaction();
try {
    $CON = $srcId('https://nigeriarights.gov.ng/files/constitution.pdf');
    $STATOIDS_LGA = $srcId('https://www.statoids.com/yng.html');
    $STATOIDS_STATES = $srcId('https://www.statoids.com/ung.html');
    $WFCT = $srcId('https://en.wikipedia.org/wiki/Federal_Capital_Territory_(Nigeria)');
    $ANGOV = $srcId('https://anambrastate.gov.ng/history/');
    $NIPCAN = $srcId('https://www.nipc.gov.ng/nigeria-states/anambra-state/');

    // A1 — Ikot Abasi.
    $st = $db->prepare("SELECT u.* FROM admin_units u JOIN admin_units p ON p.id = u.parent_id AND p.slug = 'akwa-ibom'
                        WHERE u.unit_type = 'lga' AND u.name IN ('Ikot Abasi (Village)', 'Ikot Abasi')");
    $st->execute();
    $ik = $st->fetch();
    if (!$ik) throw new RuntimeException('Ikot Abasi LGA not found');
    if ($ik['name'] === 'Ikot Abasi') {
        $done[] = 'A1 Ikot Abasi: already correct';
    } else {
        $db->prepare("UPDATE admin_units SET name = 'Ikot Abasi', slug = 'ikot-abasi', summary = REPLACE(summary, 'Ikot Abasi (Village)', 'Ikot Abasi') WHERE id = ?")
           ->execute([$ik['id']]);
        $db->prepare('INSERT IGNORE INTO slug_redirects (entity_table, entity_id, old_path) VALUES (?, ?, ?)')
           ->execute(['admin_units', $ik['id'], "states/akwa-ibom/lgas/{$ik['slug']}"]);
        $logIt('admin_units', (int) $ik['id'], ['name' => $ik['name'], 'slug' => $ik['slug']], ['name' => 'Ikot Abasi', 'slug' => 'ikot-abasi']);
        $touched['admin_units'][] = (int) $ik['id'];
        $done[] = "A1 '{$ik['name']}' -> 'Ikot Abasi' (#{$ik['id']}; /states/akwa-ibom/lgas/{$ik['slug']} redirects)";
    }

    // A2 — Abuja as the FCT's capital city.
    $fct = $unit('federal_capital_territory', 'federal-capital-territory');
    if ($fct['capital_place_id']) {
        $done[] = 'A2 FCT capital: already set';
    } else {
        $st = $db->prepare("SELECT id FROM places WHERE slug = 'abuja' LIMIT 1");
        $st->execute();
        $pid = (int) $st->fetchColumn();
        if (!$pid) {
            $db->prepare("INSERT INTO places (place_type, name, slug, admin_unit_id, summary, status, evidence_status, review_status)
                          VALUES ('settlement', 'Abuja', 'abuja', ?, ?, 'existing', 'multiple_sources', 'published')")
               ->execute([$fct['id'], 'Abuja is the capital city of the Federal Capital Territory. The Constitution (s.298) makes the Federal Capital Territory, Abuja, the capital of the Federation and the seat of its government.']);
            $pid = (int) $db->lastInsertId();
            $es = $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance) VALUES ('places', ?, ?, ?, 'supports')");
            $es->execute([$pid, $STATOIDS_STATES, 'Capital of the Federal Capital Territory: Abuja (state table)']);
            $es->execute([$pid, $WFCT, 'Abuja, the capital city of Nigeria, is located in the Federal Capital Territory']);
            $es->execute([$pid, $CON, 's.298: the Federal Capital Territory, Abuja is the capital of the Federation']);
            $touched['places'][] = $pid;
        }
        $db->prepare('UPDATE admin_units SET capital_place_id = ? WHERE id = ?')->execute([$pid, $fct['id']]);
        $logIt('admin_units', (int) $fct['id'], ['capital_place_id' => null], ['capital_place_id' => $pid, 'capital' => 'Abuja']);
        $touched['admin_units'][] = (int) $fct['id'];
        $done[] = "A2 FCT capital set to Abuja (place #{$pid})";
    }

    // A3 — Anambra from 27 August 1991.
    $an = $unit('state', 'anambra');
    $text = '27 Aug 1991 (present state); the earlier Anambra State dated from 3 Feb 1976';
    if ($an['created_on'] === '1991-08-27') {
        $done[] = 'A3 Anambra: already 1991';
    } else {
        $db->prepare("UPDATE admin_units SET created_on = '1991-08-27', created_on_text = ?, created_precision = 'exact' WHERE id = ?")->execute([$text, $an['id']]);
        $has = $db->prepare("SELECT 1 FROM entity_sources WHERE entity_table = 'admin_units' AND entity_id = ? AND source_id = ? AND claim LIKE 'Present state created%'");
        $es = $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance) VALUES ('admin_units', ?, ?, ?, 'supports')");
        foreach ([$ANGOV, $NIPCAN] as $s) {
            $has->execute([$an['id'], $s]);
            if (!$has->fetchColumn()) $es->execute([$an['id'], $s, 'Present state created 27 August 1991']);
        }
        $logIt('admin_units', (int) $an['id'], ['created_on' => $an['created_on'], 'created_on_text' => $an['created_on_text']], ['created_on' => '1991-08-27', 'created_on_text' => $text]);
        $touched['admin_units'][] = (int) $an['id'];
        $done[] = "A3 Anambra created_on {$an['created_on']} -> 1991-08-27 (1976 kept in created_on_text and the existing change record)";
    }

    // A3 (summary) — the one-line summary repeated the 1976 date.
    $an = $unit('state', 'anambra');
    $oldSum = 'It was created on 3 February 1976 from East-Central State.';
    $newSum = 'The present state was created on 27 August 1991, when Enugu State was separated from the earlier Anambra State (created on 3 February 1976 from East-Central State).';
    if (str_contains((string) $an['summary'], $newSum)) {
        $done[] = 'A3 Anambra summary: already corrected';
    } elseif (substr_count((string) $an['summary'], $oldSum) === 1) {
        $db->prepare('UPDATE admin_units SET summary = ? WHERE id = ?')->execute([str_replace($oldSum, $newSum, $an['summary']), $an['id']]);
        $logIt('admin_units', (int) $an['id'], ['summary' => $an['summary']], ['summary_sentence' => $newSum]);
        $touched['admin_units'][] = (int) $an['id'];
        $done[] = 'A3 Anambra summary sentence corrected';
    } else {
        throw new RuntimeException('Anambra summary: expected sentence not found');
    }

    // C — spelling variants from Statoids' LGA table.
    $VARIANTS = [
        ['adamawa', 'girei', 'Girie', 'spelling_variant'],
        ['kano', 'nasarawa', 'Nassarawa', 'spelling_variant'],
        ['nasarawa', 'nasarawa', 'Nassarawa', 'spelling_variant'],
        ['nasarawa', 'nasarawa-eggon', 'Nassarawa Egon', 'spelling_variant'],
        ['rivers', 'emohua', 'Emuoha', 'spelling_variant'],
        ['yobe', 'bade', 'Barde', 'spelling_variant'],
        ['zamfara', 'kaura-namoda', 'Kauran Namoda', 'spelling_variant'],
        ['federal-capital-territory', 'abuja-municipal-area-council', 'AMAC', 'abbreviation'],
    ];
    $has = $db->prepare("SELECT 1 FROM entity_names WHERE entity_table = 'admin_units' AND entity_id = ? AND name = ?");
    $ins = $db->prepare("INSERT INTO entity_names (entity_table, entity_id, name, name_type, usage_notes, source_id) VALUES ('admin_units', ?, ?, ?, ?, ?)");
    foreach ($VARIANTS as [$parent, $slug, $name, $type]) {
        $st = $db->prepare("SELECT u.id FROM admin_units u JOIN admin_units p ON p.id = u.parent_id AND p.slug = ? WHERE u.slug = ? AND u.unit_type IN ('lga','other')");
        $st->execute([$parent, $slug]);
        $id = (int) $st->fetchColumn();
        if (!$id) throw new RuntimeException("LGA {$parent}/{$slug} not found");
        $has->execute([$id, $name]);
        if ($has->fetchColumn()) { $done[] = "C {$name}: already recorded"; continue; }
        $ins->execute([$id, $name, $type, $type === 'abbreviation' ? "Abbreviation used in Statoids' LGA table." : "Spelling in Statoids' LGA table.", $STATOIDS_LGA]);
        $logIt('admin_units', $id, [], ['added_name' => $name, 'name_type' => $type]);
        $touched['admin_units'][] = $id;
        $done[] = "C + {$name} ({$parent}/{$slug})";
    }

    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
if ($apply) {
    $indexer = new ContentIndexer($db);
    foreach ($touched as $t => $ids) foreach (array_unique($ids) as $id) {
        try { $indexer->indexRecord($t, $id); } catch (Throwable $e) { fwrite(STDERR, "[warn] reindex {$t}#{$id}: {$e->getMessage()}\n"); }
    }
    Cache::forget('nigeria_has_published');
}
echo ($apply ? '[APPLIED] ' : '[DRY RUN - rolled back] ') . implode("\n  ", $done) . "\n";
