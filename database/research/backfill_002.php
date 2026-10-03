<?php
/**
 * Backfill for migration 002 (owner approval 25 Sep 2026: "YES GO AHEAD").
 * Fills the new, empty columns using the approved rules; no existing text or value is changed.
 *
 *   1. Source type + tier (NIGERIA_STEP5 §4.1) by publisher/domain, with the tier overrides
 *      the Benue sample used; 6 pairs of sources that copy each other share a lineage_group.
 *      Sources that cannot be classified from their record are left empty and listed.
 *   2. Stable IDs (Step 4 §2, Step 5 §8): NG, NG-REGION-…, NG-ZONE-…, NG-STATE-…, NG-FCT,
 *      NG-LGA-<STATE>-<LGA>, ETH-…, LANG-…, POL-…, PLACE-…, PERIOD-…, EVT-…, PER-….
 *      Generated once from the current name; never regenerated.
 *   3. The six geopolitical zones as records (owner Q2), linked to their states by part_of.
 *      Zone records and those links are saved as DRAFTS: nothing new is shown publicly until
 *      the public-views step. The geopolitical_zone field is kept.
 *   4. evidence_level from evidence_status (Step 5 §1.2). Records outside the national layer
 *      (Tiv-collection people and events, evidence empty) are not mapped.
 *   5. One research_progress row per state and the FCT (Phase 5 table), status not_started.
 *
 * Idempotent (fills only NULL fields; creates only what is missing). Logged to activity_log.
 * Usage: php database/research/backfill_002.php [--apply] [--revert]
 *   --revert clears everything this script fills. Valid only before later steps write these fields.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply  = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$BATCH_TITLE = 'Backfill 002 — stable IDs, zones, evidence levels, source tiers';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_backfill', ?, ?, NULL, ?, 'cli', 'backfill_002.php')");
$logIt = fn(string $t, int $id, array $new) => $log->execute([$t, $id, json_encode($new + ['by' => 'owner approval 2026-09-25 (migration 002 backfill)'], JSON_UNESCAPED_UNICODE)]);
$q = fn(string $sql, array $p = []) => (function () use ($db, $sql, $p) { $st = $db->prepare($sql); $st->execute($p); return $st; })();

// Keep each record's updated_at as it was: the backfill is not an edit of the record.
$keepTs = function (string $t) use ($db): string {
    static $has = [];
    $has[$t] ??= (bool) $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '{$t}' AND column_name = 'updated_at'")->fetchColumn();
    return $has[$t] ? ', updated_at = updated_at' : '';
};
$SID_TABLES = ['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods', 'timeline_events', 'historical_figures'];
$EVIDENCE_TABLES = ['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods', 'entity_relations', 'entity_statistics'];

$db->beginTransaction();
try {
    $batchId = (int) $q('SELECT id FROM research_batches WHERE title = ?', [$BATCH_TITLE])->fetchColumn();

    // ------------------------------------------------------------------ revert
    if ($revert) {
        if ($batchId) {
            $zones = $q("SELECT id FROM admin_units WHERE unit_type = 'geopolitical_zone' AND research_batch_id = ?", [$batchId])->fetchAll(PDO::FETCH_COLUMN);
            $n = $q('DELETE FROM entity_relations WHERE research_batch_id = ?', [$batchId])->rowCount();
            $m = $q('DELETE FROM entity_sources WHERE research_batch_id = ?', [$batchId])->rowCount();
            $z = $q("DELETE FROM admin_units WHERE unit_type = 'geopolitical_zone' AND research_batch_id = ?", [$batchId])->rowCount();
            echo "zones deleted: {$z}; relations: {$n}; source links: {$m}\n";
            $q('DELETE FROM research_batches WHERE id = ?', [$batchId]);
        }
        echo 'research_progress rows deleted: ' . $q('DELETE FROM research_progress')->rowCount() . "\n";
        foreach ($SID_TABLES as $t) echo "{$t}.stable_id cleared: " . $q("UPDATE {$t} SET stable_id = NULL" . $keepTs($t) . " WHERE stable_id IS NOT NULL")->rowCount() . "\n";
        foreach ($EVIDENCE_TABLES as $t) echo "{$t}.evidence_level cleared: " . $q("UPDATE {$t} SET evidence_level = NULL" . $keepTs($t) . " WHERE evidence_level IS NOT NULL")->rowCount() . "\n";
        echo 'sources cleared: ' . $q('UPDATE sources SET source_kind = NULL, source_tier = NULL, lineage_group = NULL WHERE source_kind IS NOT NULL OR source_tier IS NOT NULL OR lineage_group IS NOT NULL')->rowCount() . "\n";
        $logIt('backfill_002', 0, ['reverted' => true]);
        $apply ? $db->commit() : $db->rollBack();
        echo $apply ? "REVERTED.\n" : "DRY RUN — nothing changed. Use --revert --apply.\n";
        exit(0);
    }

    if (!$batchId) {
        $q("INSERT INTO research_batches (title, scope, status, conducted_by, summary, started_at, completed_at)
            VALUES (?, 'Fills the empty columns added by migration 002 using the approved Step 5 rules. No existing text or value is changed.', 'approved', 'claude', '', NOW(), NOW())", [$BATCH_TITLE]);
        $batchId = (int) $db->lastInsertId();
    }

    // ------------------------------------------------------------------ 1. sources
    $DOMAIN = [ // domain suffix => [kind, tier]
        'nigeriarights.gov.ng' => ['legislation', 1],
        'wikipedia.org' => ['encyclopedia', 3], 'britannica.com' => ['encyclopedia', 3], 'encyclopedia.com' => ['encyclopedia', 3],
        'blackpast.org' => ['encyclopedia', 3], 'nigerianwiki.com' => ['encyclopedia', 3],
        'citypopulation.de' => ['reference_database', 3], 'statoids.com' => ['reference_database', 3], 'worldstatesmen.org' => ['reference_database', 3],
        'glottolog.org' => ['linguistic_database', 2], 'sil.org' => ['linguistic_database', 2], 'ethnologue.com' => ['linguistic_database', 2],
        'dailytrust.com' => ['news', 3], 'vanguardngr.com' => ['news', 3], 'thisdaylive.com' => ['news', 3], 'punchng.com' => ['news', 3],
        'guardian.ng' => ['news', 3], 'blueprint.ng' => ['news', 3], 'thenigerianvoice.com' => ['news', 3], 'premiumtimesng.com' => ['news', 3],
        'iambenue.com' => ['community_organisation', 4], 'mutuk.org' => ['community_organisation', 4], 'nksteducationdepartment.org.ng' => ['community_organisation', 4],
        'firstclassnigeria.com' => ['website', 5], 'academia.edu' => ['website', 5],
        'doi.org' => ['journal_article', 2], 'ajol.info' => ['journal_article', 2], 'cscanada.net' => ['journal_article', 2], 'revistes.ub.edu' => ['journal_article', 2],
        'thebrpi.org' => ['journal_article', 2], 'gsconlinepress.com' => ['journal_article', 2],
        'core.tdar.org' => ['research_report', 2], 'rogerblench.info' => ['academic_book', 2], 'books.google.com' => ['academic_book', 2],
        'euaa.europa.eu' => ['government_publication', 3],
        'gov.ng' => ['official_website', 1], // federal and state government sites (after the more specific entries above)
    ];
    $BY_URL = [ // exact overrides
        'https://bsum.edu.ng/w3/brief_history.php' => ['official_website', 1],
        'https://www.encyclopedia.com/humanities/encyclopedias-almanacs-transcripts-and-maps/tiv' => ['encyclopedia', 2],
    ];
    $NOTES = [
        'encyclopedia.com/humanities/encyclopedias-almanacs-transcripts-and-maps/tiv' => 'Tier raised to 2: scholarly author (Bohannan), as in the Benue sample.',
        'euaa.europa.eu' => 'Tier lowered to 3: a foreign agency\'s summary, secondary for Nigerian administration.',
        'academia.edu' => 'Tier 5: author not verified.',
    ];
    $BY_JOURNAL = ['/journal|jour\.|africa, vol|ijotil|majop/i' => ['journal_article', 2], '/ethnographic survey|international african institute/i' => ['academic_book', 2]];
    $BY_OLDTYPE = ['community_submission' => ['community_submission', 4], 'news' => ['news', 3], 'oral_tradition' => ['oral_history', 4], 'interview' => ['oral_history', 4]];
    $LINEAGE = [ // pairs found to copy each other in batch reviews 014–020
        'LIN-KANO' => ['https://kanostate.gov.ng/history/', 'https://en.wikipedia.org/wiki/Kano_State'],
        'LIN-BAYELSA' => ['https://bayelsastate.gov.ng/about/', 'https://en.wikipedia.org/wiki/Bayelsa_State'],
        'LIN-BORNO' => ['https://bornostate.gov.ng/about', 'https://en.wikipedia.org/wiki/Borno_State'],
        'LIN-EDO' => ['http://www.edostate.gov.ng/2016/02/08/history-of-edo-state-from-edo-state-website/', 'https://en.wikipedia.org/wiki/Edo_State'],
        'LIN-KWARA' => ['https://www.nipc.gov.ng/nigeria-states/kwara-state/', 'https://en.wikipedia.org/wiki/Kwara_State'],
        'LIN-BAUCHI' => ['https://home.bauchistate.gov.ng/history/', 'https://en.wikipedia.org/wiki/Bauchi_State'],
    ];

    $classified = 0; $unclassified = [];
    $upd = $db->prepare('UPDATE sources SET source_kind = ?, source_tier = ? WHERE id = ? AND source_kind IS NULL');
    $overrides = [];
    foreach ($q('SELECT * FROM sources WHERE source_kind IS NULL ORDER BY id')->fetchAll() as $s) {
        $url = trim((string) $s['url']);
        if ($url === '' && preg_match('#https?://\S+#', (string) $s['notes'], $m)) $url = $m[0];
        $host = strtolower(preg_replace('#^www\.#', '', (string) parse_url($url, PHP_URL_HOST)));
        $hit = $BY_URL[$url] ?? null;
        if (!$hit && $host) foreach ($DOMAIN as $d => $v) if ($host === $d || str_ends_with($host, '.' . $d)) { $hit = $v; break; }
        if (!$hit && $host === 'bsum.edu.ng' && str_contains($url, '/journals/')) $hit = ['journal_article', 2];
        if (!$hit && !$host) foreach ($BY_JOURNAL as $re => $v) if (preg_match($re, $s['title'] . ' ' . $s['publisher'] . ' ' . $s['notes'])) { $hit = $v; break; }
        if (!$hit) $hit = $BY_OLDTYPE[$s['source_type']] ?? null;
        if (!$hit) { $unclassified[] = "#{$s['id']} [{$s['source_type']}] " . mb_substr($s['title'], 0, 60) . ($url ? " <{$host}>" : ' (no URL)'); continue; }
        foreach ($NOTES as $k => $n) if (str_contains($url, $k)) $overrides[$s['id']] = $n;
        $upd->execute([$hit[0], $hit[1], $s['id']]);
        $classified += $upd->rowCount();
    }
    $lin = 0;
    foreach ($LINEAGE as $g => $urls) foreach ($urls as $u) {
        $lin += $q('UPDATE sources SET lineage_group = ? WHERE url = ? AND lineage_group IS NULL', [$g, $u])->rowCount();
    }
    $logIt('sources', 0, ['classified' => $classified, 'lineage_set' => $lin, 'tier_notes' => $overrides, 'lineage_groups' => $LINEAGE]);

    // ------------------------------------------------------------------ 2. zones as records
    $ZONES = ['North Central', 'North East', 'North West', 'South East', 'South South', 'South West'];
    $nigeria = (int) $q("SELECT id FROM admin_units WHERE unit_type = 'country' AND slug = 'nigeria'")->fetchColumn();
    $WSTATES = (int) $q("SELECT id FROM sources WHERE url = 'https://en.wikipedia.org/wiki/States_of_Nigeria'")->fetchColumn();
    $EUAA = (int) $q("SELECT id FROM sources WHERE url = 'https://www.euaa.europa.eu/country-guidance-nigeria/general-remarks-0'")->fetchColumn();
    if (!$nigeria || !$WSTATES || !$EUAA) throw new RuntimeException('Nigeria record or zone sources not found');
    $zonesNew = 0; $linksNew = 0;
    foreach ($ZONES as $z) {
        $slug = strtolower(str_replace(' ', '-', $z));
        $states = $q("SELECT id, name FROM admin_units WHERE unit_type IN ('state','federal_capital_territory') AND status = 'current' AND geopolitical_zone = ? ORDER BY name", [$z])->fetchAll();
        $names = array_map(fn($r) => preg_replace('/ State$/', '', $r['name']), $states);
        usort($names, fn($a, $b) => ($a === 'Federal Capital Territory') <=> ($b === 'Federal Capital Territory') ?: strcmp($a, $b)); // FCT last
        $list = count($names) > 1 ? implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names) : ($names[0] ?? '');
        $zid = (int) $q("SELECT id FROM admin_units WHERE unit_type = 'geopolitical_zone' AND slug = ?", [$slug])->fetchColumn();
        if (!$zid) {
            $q("INSERT INTO admin_units (unit_type, parent_id, name, official_name, slug, geopolitical_zone, status, summary, evidence_status, review_status, research_batch_id)
                VALUES ('geopolitical_zone', ?, ?, ?, ?, ?, 'current', ?, 'multiple_sources', 'draft', ?)",
               [$nigeria, $z, "{$z} geopolitical zone", $slug, $z,
                "{$z} is one of Nigeria's six geopolitical zones. It comprises {$list}. The zones are not defined in the 1999 Constitution.",
                $batchId]);
            $zid = (int) $db->lastInsertId();
            foreach ([$WSTATES, $EUAA] as $sid) {
                $q("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance, research_batch_id) VALUES ('admin_units', ?, ?, 'Zone membership', 'supports', ?)", [$zid, $sid, $batchId]);
            }
            $zonesNew++;
            $logIt('admin_units', $zid, ['created' => 'geopolitical_zone', 'name' => $z, 'review_status' => 'draft']);
        }
        foreach ($states as $st) {
            $exists = $q("SELECT 1 FROM entity_relations WHERE from_table = 'admin_units' AND from_id = ? AND relation_type = 'part_of' AND to_table = 'admin_units' AND to_id = ?", [$st['id'], $zid])->fetchColumn();
            if ($exists) continue;
            $q("INSERT INTO entity_relations (from_table, from_id, relation_type, to_table, to_id, date_precision, notes, source_id, evidence_status, review_status, research_batch_id)
                VALUES ('admin_units', ?, 'part_of', 'admin_units', ?, 'unknown', 'Zone membership from Wikipedia (States of Nigeria) and EUAA; when the zones were adopted is a recorded research gap.', ?, 'multiple_sources', 'draft', ?)",
               [$st['id'], $zid, $WSTATES, $batchId]);
            $linksNew++;
        }
    }
    $logIt('admin_units', 0, ['zones_created' => $zonesNew, 'state_zone_links' => $linksNew]);

    // ------------------------------------------------------------------ 3. stable IDs
    $norm = function (string $s): string {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        return trim(preg_replace('/[^A-Z0-9]+/', '-', strtoupper($s)), '-');
    };
    $cut = fn(string $id) => strlen($id) <= 80 ? $id : rtrim(substr($id, 0, strrpos(substr($id, 0, 80), '-') ?: 80), '-');
    $proposed = []; // table => [id => sid]
    $units = $q('SELECT id, unit_type, parent_id, name, slug FROM admin_units')->fetchAll(PDO::FETCH_UNIQUE);
    $stateKey = function (array $u) use ($norm): string {
        return $u['unit_type'] === 'federal_capital_territory' ? 'FCT' : $norm(preg_replace('/ State$/', '', $u['name']));
    };
    foreach ($units as $id => $u) {
        $p = $units[$u['parent_id']] ?? null;
        $sid = match ($u['unit_type']) {
            'country' => 'NG',
            'region' => 'NG-REGION-' . $norm(preg_replace('/ Region$/', '', $u['name'])),
            'geopolitical_zone' => 'NG-ZONE-' . $norm($u['name']),
            'state' => 'NG-STATE-' . $stateKey($u),
            'federal_capital_territory' => 'NG-FCT',
            'lga', 'other' => $p && in_array($p['unit_type'], ['state', 'federal_capital_territory'], true) ? 'NG-LGA-' . $stateKey($p) . '-' . $norm($u['name']) : null,
            default => null,
        };
        if ($sid) $proposed['admin_units'][$id] = $cut($sid);
    }
    $PREFIX = ['places' => 'PLACE', 'ethnic_groups' => 'ETH', 'languages' => 'LANG', 'polities' => 'POL', 'cultural_records' => 'CULT',
               'historical_periods' => 'PERIOD', 'timeline_events' => 'EVT', 'historical_figures' => 'PER'];
    $NAMECOL = ['timeline_events' => 'title', 'historical_figures' => 'english_name'];
    foreach ($PREFIX as $t => $pre) {
        $col = $NAMECOL[$t] ?? 'name';
        foreach ($q("SELECT id, {$col} AS n FROM {$t}")->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $n) {
            $proposed[$t][$id] = $cut($pre . '-' . $norm((string) $n));
        }
    }
    // Same-name places (and any other clash): add the state, then the record id, so every ID is unique.
    $placeState = $q("SELECT p.id, COALESCE(NULLIF(s.name,''), '') FROM places p LEFT JOIN admin_units u ON u.id = p.admin_unit_id
                      LEFT JOIN admin_units s ON s.id = IF(u.unit_type IN ('state','federal_capital_territory'), u.id, u.parent_id)")->fetchAll(PDO::FETCH_KEY_PAIR);
    $clashes = [];
    foreach ($proposed as $t => &$ids) {
        $count = array_count_values($ids);
        foreach ($ids as $id => &$sid) if ($count[$sid] > 1) {
            $old = $sid;
            if ($t === 'places' && !empty($placeState[$id])) $sid = $cut('PLACE-' . $norm(preg_replace('/ State$/', '', $placeState[$id])) . '-' . substr($sid, 6));
            $clashes[] = "{$t}#{$id} {$old} -> {$sid}";
        }
        unset($sid);
        $count = array_count_values($ids);
        foreach ($ids as $id => &$sid) if ($count[$sid] > 1) { $clashes[] = "{$t}#{$id} {$sid} -> {$sid}-{$id}"; $sid = $cut($sid) . '-' . $id; }
        unset($sid);
    }
    unset($ids);
    $sidSet = 0;
    foreach ($proposed as $t => $ids) {
        $taken = array_flip($q("SELECT stable_id FROM {$t} WHERE stable_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN));
        $st = $db->prepare("UPDATE {$t} SET stable_id = ?" . $keepTs($t) . " WHERE id = ? AND stable_id IS NULL");
        foreach ($ids as $id => $sid) {
            if (isset($taken[$sid])) continue;
            $st->execute([$sid, $id]); $sidSet += $st->rowCount();
        }
    }
    $logIt('stable_id', 0, ['set' => $sidSet, 'clashes_resolved' => $clashes]);

    // ------------------------------------------------------------------ 4. evidence levels
    $tier = $q('SELECT id, source_tier, COALESCE(lineage_group, CONCAT("S", id)) AS lin, url FROM sources')->fetchAll(PDO::FETCH_UNIQUE);
    $grade = function (?string $status, array $srcIds, bool $censusFigure = false) use ($tier): ?string {
        $tiers = []; $lins = [];
        foreach (array_unique($srcIds) as $s) if (isset($tier[$s])) {
            $t = $tier[$s]['source_tier'];
            // Census figures reproduced by City Population come from the NPC (Tier 1), as in the Benue sample.
            if ($censusFigure && str_contains((string) $tier[$s]['url'], 'citypopulation.de')) $t = 1;
            if ($t !== null) $tiers[] = (int) $t;
            $lins[$tier[$s]['lin']] = true;
        }
        $best = $tiers ? min($tiers) : 99;
        return match ($status) {
            'verified' => 'verified',
            'well_documented', 'scholarly_interpretation' => 'well_documented',
            'multiple_sources' => $best <= 2 && (count($lins) >= 2 || count($srcIds) === 1) ? 'verified' : 'well_documented',
            'single_reliable_source' => $best <= 2 ? 'well_documented' : 'reported',
            'community_source', 'oral_tradition' => 'reported',
            'disputed' => 'disputed',
            'needs_corroboration' => 'needs_corroboration',
            'unverified' => 'uncertain',
            default => null, // 'outdated' and empty are not mapped automatically
        };
    };
    $evid = [];
    $links = [];
    foreach ($q("SELECT entity_table, entity_id, source_id FROM entity_sources WHERE stance = 'supports'")->fetchAll() as $r) $links[$r['entity_table']][$r['entity_id']][] = (int) $r['source_id'];
    foreach (['admin_units', 'places', 'ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods'] as $t) {
        $st = $db->prepare("UPDATE {$t} SET evidence_level = ?" . $keepTs($t) . " WHERE id = ? AND evidence_level IS NULL");
        foreach ($q("SELECT id, evidence_status FROM {$t} WHERE evidence_level IS NULL AND evidence_status IS NOT NULL")->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $es) {
            $lv = $grade($es, $links[$t][$id] ?? []);
            if ($lv === null) continue;
            $st->execute([$lv, $id]);
            $evid[$t][$es . ' -> ' . $lv] = ($evid[$t][$es . ' -> ' . $lv] ?? 0) + 1;
        }
    }
    // Relations and statistics carry one linked source; the notes name any others (graded as in the Benue sample).
    foreach (['entity_relations', 'entity_statistics'] as $t) {
        $cols = $t === 'entity_statistics' ? ", method = 'census' AS census" : ', 0 AS census';
        $st = $db->prepare("UPDATE {$t} SET evidence_level = ?" . $keepTs($t) . " WHERE id = ? AND evidence_level IS NULL");
        foreach ($q("SELECT id, evidence_status, source_id {$cols} FROM {$t} WHERE evidence_level IS NULL AND evidence_status IS NOT NULL")->fetchAll() as $r) {
            $lv = $grade($r['evidence_status'], $r['source_id'] ? [(int) $r['source_id']] : [], (bool) $r['census']);
            if ($lv === null) continue;
            $st->execute([$lv, $r['id']]);
            $evid[$t][$r['evidence_status'] . ' -> ' . $lv] = ($evid[$t][$r['evidence_status'] . ' -> ' . $lv] ?? 0) + 1;
        }
    }
    $logIt('evidence_level', 0, ['mapped' => $evid]);

    // ------------------------------------------------------------------ 5. research progress rows
    $prog = 0;
    foreach ($q("SELECT id, research_batch_id FROM admin_units WHERE unit_type IN ('state','federal_capital_territory') AND status = 'current'")->fetchAll() as $s) {
        $lgas = (int) $q("SELECT COUNT(*) FROM admin_units WHERE parent_id = ? AND unit_type IN ('lga','other') AND status = 'current'", [$s['id']])->fetchColumn();
        $srcs = (int) $q("SELECT COUNT(DISTINCT source_id) FROM entity_sources WHERE entity_table = 'admin_units'
                          AND entity_id IN (SELECT id FROM admin_units WHERE id = ? OR parent_id = ?)", [$s['id'], $s['id']])->fetchColumn();
        $prog += $q("INSERT IGNORE INTO research_progress (admin_unit_id, lgas_total, sources_count, status, notes)
                     VALUES (?, ?, ?, 'not_started', 'State-level history published (batches 001–022). LGA-by-LGA research under the master brief not started.')",
                    [$s['id'], $lgas, $srcs])->rowCount();
    }
    $logIt('research_progress', 0, ['rows' => $prog]);

    $q('UPDATE research_batches SET summary = ? WHERE id = ?', ["Sources classified: {$classified}; lineage links: {$lin}; zones created: {$zonesNew} (draft); state-zone links: {$linksNew} (draft); stable IDs set: {$sidSet}; research_progress rows: {$prog}.", $batchId]);

    // ------------------------------------------------------------------ report
    echo "Batch #{$batchId}\n";
    echo "1. Sources classified: {$classified}; lineage links: {$lin}; left unclassified: " . count($unclassified) . "\n";
    foreach ($overrides as $id => $n) echo "     tier note #{$id}: {$n}\n";
    foreach ($unclassified as $u) echo "     {$u}\n";
    echo "2. Zones created: {$zonesNew} (draft); state→zone links: {$linksNew} (draft)\n";
    echo "3. Stable IDs set: {$sidSet}; name clashes resolved: " . count($clashes) . "\n";
    foreach ($clashes as $c) echo "     {$c}\n";
    echo "4. Evidence levels:\n";
    foreach ($evid as $t => $m) { ksort($m); foreach ($m as $k => $n) echo "     {$t}: {$k} = {$n}\n"; }
    echo "5. research_progress rows: {$prog}\n";

    $apply ? $db->commit() : $db->rollBack();
    echo $apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply to commit.\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
