<?php
/**
 * Phase 4 quality control for one state (master brief, Phase 4). READ-ONLY: runs SELECTs only
 * and prints a Markdown report. Fixes it suggests are proposed separately for approval.
 *
 * Checks: duplicates · ethnic-name aliases · geographic consistency · source quality ·
 * unsupported claims · contradictory claims · missing LGAs / coverage · classification problems.
 *
 * Usage: php database/research/qc_state.php <state slug> [> report.md]      e.g.  benue
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
ini_set('display_errors', '1'); // a CLI report: show errors instead of exiting silently

$slug = $argv[1] ?? null;
if (!$slug) { fwrite(STDERR, "Usage: php database/research/qc_state.php <state slug>\n"); exit(1); }
$db = Database::getInstance();
$q = function (string $sql, array $p = []) use ($db) { $s = $db->prepare($sql); $s->execute($p); return $s->fetchAll(); };
$one = fn(string $sql, array $p = []) => $q($sql, $p)[0] ?? null;

$state = $one("SELECT * FROM admin_units WHERE unit_type IN ('state','federal_capital_territory') AND slug = ? AND status = 'current'", [$slug]);
if (!$state) { fwrite(STDERR, "State '{$slug}' not found\n"); exit(1); }
$sid = (int) $state['id'];
$lgas = $q("SELECT * FROM admin_units WHERE parent_id = ? AND unit_type IN ('lga','other') AND status = 'current' ORDER BY name", [$sid]);
$lgaIds = array_map(fn($l) => (int) $l['id'], $lgas);
$unitIds = array_merge([$sid], $lgaIds);
$in = fn(array $ids) => $ids ? implode(',', array_map('intval', $ids)) : '0';

// Records connected to the state: groups/languages/polities/cultural records/places linked to it or its LGAs.
$rel = $q("SELECT r.*, CASE WHEN r.to_table='admin_units' THEN r.to_id ELSE r.from_id END AS unit_id FROM entity_relations r
           WHERE (r.to_table='admin_units' AND r.to_id IN ({$in($unitIds)})) OR (r.from_table='admin_units' AND r.from_id IN ({$in($unitIds)}))");
$linked = [];
foreach ($rel as $r) {
    [$t, $i] = $r['to_table'] === 'admin_units' && in_array((int) $r['to_id'], $unitIds, true) ? [$r['from_table'], $r['from_id']] : [$r['to_table'], $r['to_id']];
    if ($t !== 'admin_units') $linked[$t][(int) $i] = true;
}
foreach ($q("SELECT id FROM places WHERE admin_unit_id IN ({$in($unitIds)})") as $p) $linked['places'][(int) $p['id']] = true;
// Records linked to linked records (e.g. dialects of a language, festivals of a people, councils of titles).
foreach (['ethnic_groups', 'languages', 'polities'] as $t) {
    foreach (array_keys($linked[$t] ?? []) as $i) {
        foreach ($q("SELECT from_table, from_id, to_table, to_id FROM entity_relations WHERE (from_table = ? AND from_id = ?) OR (to_table = ? AND to_id = ?)", [$t, $i, $t, $i]) as $r) {
            foreach ([[$r['from_table'], $r['from_id']], [$r['to_table'], $r['to_id']]] as [$tt, $ii]) {
                if (in_array($tt, ['cultural_records', 'polities', 'places'], true)) $linked[$tt][(int) $ii] = true;
            }
        }
    }
}
foreach (array_keys($linked['languages'] ?? []) as $i) foreach ($q("SELECT id FROM languages WHERE parent_id = ?", [$i]) as $c) {
    $linked['languages'][(int) $c['id']] = true;
    foreach ($q("SELECT id FROM languages WHERE parent_id = ?", [$c['id']]) as $g) $linked['languages'][(int) $g['id']] = true;
}
$nameCol = ['ethnic_groups' => 'name', 'languages' => 'name', 'polities' => 'name', 'cultural_records' => 'name', 'places' => 'name', 'admin_units' => 'name'];
$rows = [];
foreach ($linked as $t => $ids) $rows[$t] = $q("SELECT * FROM {$t} WHERE id IN ({$in(array_keys($ids))}) ORDER BY name");
$rows['admin_units'] = $q("SELECT * FROM admin_units WHERE id IN ({$in($unitIds)})");
$findings = [];
$add = function (string $check, string $severity, string $text) use (&$findings) { $findings[$check][] = [$severity, $text]; };
$label = fn(string $t, array $r) => "{$r['name']} (" . str_replace('_', ' ', rtrim($t, 's')) . " #{$r['id']}" . (!empty($r['stable_id']) ? ", {$r['stable_id']}" : '') . ')';

// 1. Duplicates (same normalised name in a table; name equal to another record's alias).
foreach ($rows as $t => $list) {
    $by = [];
    foreach ($list as $r) $by[mb_strtolower(trim($r['name']))][] = $r;
    foreach ($by as $n => $dups) if (count($dups) > 1) {
        $what = $t === 'admin_units' ? 'unit' : $t;
        $add('Duplicates', 'check', "Same name in {$what}: " . implode('; ', array_map(fn($r) => $label($t, $r), $dups)));
    }
    foreach ($list as $r) {
        $alias = $q("SELECT n.entity_id FROM entity_names n WHERE n.entity_table = ? AND n.entity_id <> ? AND LOWER(n.name) = LOWER(?)", [$t, $r['id'], $r['name']]);
        foreach ($alias as $a) $add('Duplicates', 'check', "{$label($t, $r)} has the same name as an alias of {$t} #{$a['entity_id']}.");
    }
}
// Wards: duplicate names inside one LGA.
foreach ($q("SELECT MIN(p.name) lga, MIN(u.name) name, COUNT(*) c FROM admin_units u JOIN admin_units p ON p.id = u.parent_id WHERE u.unit_type = 'ward' AND p.parent_id = ? GROUP BY p.id, LOWER(u.name) HAVING c > 1", [$sid]) as $d) {
    $add('Duplicates', 'check', "Ward name '{$d['name']}' appears {$d['c']} times in {$d['lga']}.");
}

// 2. Ethnic-name aliases.
foreach (['ethnic_groups', 'languages'] as $t) foreach ($rows[$t] ?? [] as $r) {
    if (($r['lang_type'] ?? 'language') === 'dialect') continue;
    $names = $q("SELECT name, name_type FROM entity_names WHERE entity_table = ? AND entity_id = ?", [$t, $r['id']]);
    if (!$names) $add('Ethnic-name aliases', 'info', "{$label($t, $r)} has no other names recorded.");
    foreach ($names as $n) {
        $other = $q("SELECT id, name FROM {$t} WHERE id <> ? AND LOWER(name) = LOWER(?)", [$r['id'], $n['name']]);
        foreach ($other as $o) $add('Ethnic-name aliases', 'problem', "Alias '{$n['name']}' of {$label($t, $r)} is also the name of {$t} #{$o['id']} ({$o['name']}).");
    }
}

// 3. Geographic consistency.
$wardCount = (int) $one("SELECT COUNT(*) c FROM admin_units u JOIN admin_units p ON p.id = u.parent_id WHERE u.unit_type = 'ward' AND p.parent_id = ?", [$sid])['c'];
foreach ($lgas as $l) {
    $w = (int) $one("SELECT COUNT(*) c FROM admin_units WHERE unit_type = 'ward' AND parent_id = ?", [$l['id']])['c'];
    if ($wardCount && !$w) $add('Geographic consistency', 'problem', "{$l['name']} has no wards although the state's wards are loaded.");
}
foreach ($q("SELECT u.id, u.name, p.name parent, p.unit_type ptype FROM admin_units u JOIN admin_units p ON p.id = u.parent_id WHERE u.unit_type = 'ward' AND p.unit_type <> 'lga' AND p.parent_id = ?", [$sid]) as $w) {
    $add('Geographic consistency', 'problem', "Ward {$w['name']} is under {$w['parent']} ({$w['ptype']}), not an LGA.");
}
foreach ($rows['places'] ?? [] as $p) {
    if ($p['admin_unit_id'] && !in_array((int) $p['admin_unit_id'], $unitIds, true)) {
        $u = $one("SELECT name FROM admin_units WHERE id = ?", [$p['admin_unit_id']]);
        $add('Geographic consistency', 'info', "{$label('places', $p)} is located in {$u['name']}, outside this state (linked through a related record).");
    }
    if ($p['latitude'] !== null && !$p['coords_source_id']) $add('Geographic consistency', 'problem', "{$label('places', $p)} has coordinates without a source.");
}
// LGAs claimed by more than one traditional council (part_of polities).
$councils = [];
foreach ($q("SELECT r.from_id lga, r.relation_type, r.evidence_status, p.name council FROM entity_relations r JOIN polities p ON p.id = r.to_id
             WHERE r.from_table = 'admin_units' AND r.to_table = 'polities' AND r.from_id IN ({$in($lgaIds)})") as $c) {
    $councils[$c['lga']][] = $c;
}
foreach ($lgas as $l) {
    $cs = $councils[$l['id']] ?? [];
    $firm = array_filter($cs, fn($c) => $c['relation_type'] === 'part_of');
    if (count($firm) > 1) $add('Geographic consistency', 'problem', "{$l['name']} is 'part of' more than one traditional area: " . implode(', ', array_column($firm, 'council')) . '.');
    if (!$cs) $add('Geographic consistency', 'info', "{$l['name']} is not linked to any traditional council or intermediate area.");
}

// 4. Source quality.
foreach ($rows as $t => $list) foreach ($list as $r) {
    if ($t === 'admin_units' && $r['unit_type'] === 'ward') continue;
    $src = $q("SELECT s.id, s.source_tier, s.source_kind, s.title FROM entity_sources es JOIN sources s ON s.id = es.source_id WHERE es.entity_table = ? AND es.entity_id = ?", [$t, $r['id']]);
    $pub = ($r['review_status'] ?? '') === 'published';
    if (!$src) { $add('Source quality', $pub ? 'problem' : 'info', "{$label($t, $r)} has no attached source" . ($pub ? ' but is published' : '') . '.'); continue; }
    $tiers = array_filter(array_map(fn($s) => $s['source_tier'], $src), fn($x) => $x !== null);
    if (!$tiers) $add('Source quality', 'check', "{$label($t, $r)}: none of its sources has a tier.");
    elseif (min($tiers) >= 4) $add('Source quality', 'check', "{$label($t, $r)} rests only on community or general-web sources (best tier " . min($tiers) . ').');
}
foreach ($rel as $r) if (!$r['source_id']) $add('Source quality', 'problem', "Relation #{$r['id']} ({$r['relation_type']}) has no source.");
$untiered = $q("SELECT DISTINCT s.id, s.title FROM sources s JOIN entity_sources es ON es.source_id = s.id
                WHERE s.source_tier IS NULL AND ((es.entity_table='admin_units' AND es.entity_id IN ({$in($unitIds)})))");
foreach ($untiered as $s) $add('Source quality', 'check', "Source #{$s['id']} '{$s['title']}' (cited for this state) has no tier.");

// 5. Unsupported claims: evidence level stronger than the attached sources allow (Step 5 rules).
foreach ($rows as $t => $list) foreach ($list as $r) {
    if (!in_array($r['evidence_level'] ?? null, ['verified', 'well_documented', 'reported'], true)) continue;
    if ($t === 'admin_units' && $r['unit_type'] === 'ward') continue;
    $src = $q("SELECT s.id, s.source_tier, COALESCE(s.lineage_group, CONCAT('S', s.id)) lin FROM entity_sources es JOIN sources s ON s.id = es.source_id
               WHERE es.entity_table = ? AND es.entity_id = ? AND es.stance <> 'contradicts'", [$t, $r['id']]);
    if (!$src) { $add('Unsupported claims', 'problem', "{$label($t, $r)} is '{$r['evidence_level']}' with no source."); continue; }
    $tiers = array_map('intval', array_filter(array_column($src, 'source_tier'), fn($x) => $x !== null));
    $lins = count(array_unique(array_column($src, 'lin')));
    if ($r['evidence_level'] === 'verified' && !in_array(1, $tiers, true) && $lins < 2) {
        $add('Unsupported claims', 'problem', "{$label($t, $r)} is 'verified' with one non-official source lineage.");
    }
}
foreach ($rel as $r) {
    if ($r['evidence_level'] === null) $add('Unsupported claims', 'info', "Relation #{$r['id']} ({$r['relation_type']}) has no six-level evidence level.");
    if ($r['evidence_status'] === 'disputed' || $r['evidence_level'] === 'disputed' || $r['relation_type'] === 'disputed_relationship') {
        $has = $one("SELECT COUNT(*) c FROM claim_disputes")['c'];
        if (!$has) $add('Unsupported claims', 'problem', "Relation #{$r['id']} is disputed but no dispute record exists.");
    }
}

// 6. Contradictory claims.
foreach ($q("SELECT st.entity_table, st.entity_id, st.metric, st.reference_year, COUNT(DISTINCT st.value_low) n, GROUP_CONCAT(DISTINCT st.value_low) vals
             FROM entity_statistics st WHERE st.entity_table = 'admin_units' AND st.entity_id IN ({$in($unitIds)}) AND st.metric <> 'other'
             GROUP BY st.entity_table, st.entity_id, st.metric, st.reference_year HAVING n > 1") as $c) {
    $u = $one("SELECT name FROM admin_units WHERE id = ?", [$c['entity_id']]);
    $add('Contradictory claims', 'info', "{$u['name']}: {$c['n']} different {$c['metric']} figures for " . ($c['reference_year'] ?: 'no year') . " ({$c['vals']}). All are kept side by side, as the rules require.");
}
foreach ($lgas as $l) {
    $core = $q("SELECT g.name FROM entity_relations r JOIN ethnic_groups g ON g.id = r.from_id WHERE r.from_table = 'ethnic_groups' AND r.to_table = 'admin_units' AND r.to_id = ? AND r.settlement_status = 'indigenous_core'", [$l['id']]);
    if (count($core) > 1) $add('Contradictory claims', 'check', "{$l['name']}: more than one group marked 'indigenous (core homeland)': " . implode(', ', array_column($core, 'name')) . '. Consider "shared".');
}
foreach ($q("SELECT id, topic, status FROM claim_disputes WHERE status = 'open'") as $d) $add('Contradictory claims', 'info', "Open dispute #{$d['id']}: {$d['topic']}.");

// 7. Missing LGAs and coverage.
// LGAs per state (Constitution, First Schedule; FCT: 6 area councils).
$expected = ['abia' => 17, 'adamawa' => 21, 'akwa-ibom' => 31, 'anambra' => 21, 'bauchi' => 20, 'bayelsa' => 8, 'benue' => 23, 'borno' => 27,
             'cross-river' => 18, 'delta' => 25, 'ebonyi' => 13, 'edo' => 18, 'ekiti' => 16, 'enugu' => 17, 'gombe' => 11, 'imo' => 27,
             'jigawa' => 27, 'kaduna' => 23, 'kano' => 44, 'katsina' => 34, 'kebbi' => 21, 'kogi' => 21, 'kwara' => 16, 'lagos' => 20,
             'nasarawa' => 13, 'niger' => 25, 'ogun' => 20, 'ondo' => 18, 'osun' => 30, 'oyo' => 33, 'plateau' => 17, 'rivers' => 23,
             'sokoto' => 23, 'taraba' => 16, 'yobe' => 17, 'zamfara' => 14, 'federal-capital-territory' => 6][$slug] ?? null;
if ($expected !== null && count($lgas) !== $expected) $add('Missing LGAs / coverage', 'problem', "The state has " . count($lgas) . " current LGAs; {$expected} expected.");
foreach ($lgas as $l) {
    $g = (int) $one("SELECT COUNT(*) c FROM entity_relations WHERE from_table = 'ethnic_groups' AND to_table = 'admin_units' AND to_id = ?", [$l['id']])['c'];
    if (!$g) $add('Missing LGAs / coverage', 'check', "{$l['name']} has no ethnic group linked to it.");
    $words = count(preg_split('/\s+/', trim(strip_tags(($l['summary'] ?? '') . ' ' . ($l['description'] ?? '') . ' ' . ($l['geography_notes'] ?? ''))), -1, PREG_SPLIT_NO_EMPTY));
    if ($words < 100) $add('Missing LGAs / coverage', 'info', "{$l['name']} has only {$words} words of text (no LGA history yet).");
    if (!$l['headquarters_place_id']) $none[] = $l['name'];
}
if (!empty($none)) $add('Missing LGAs / coverage', 'info', count($none) . " of " . count($lgas) . " LGAs have no headquarters recorded.");

// 8. Classification problems.
foreach ($rows['languages'] ?? [] as $r) {
    if (!$r['glottocode'] && in_array($r['lang_type'], ['language', 'dialect', 'variety'], true)) $add('Classification problems', 'check', "{$label('languages', $r)} has no Glottocode.");
    if ($r['lang_type'] === 'language' && !$r['parent_id']) $add('Classification problems', 'check', "{$label('languages', $r)} is not placed in a family or branch.");
}
foreach ($rows['ethnic_groups'] ?? [] as $r) {
    if (!$one("SELECT 1 x FROM entity_relations WHERE from_table = 'ethnic_groups' AND from_id = ? AND relation_type = 'speaks'", [$r['id']])) {
        $add('Classification problems', 'info', "{$label('ethnic_groups', $r)} has no 'speaks' link to a language.");
    }
}
foreach ($q("SELECT id, topic FROM research_gaps WHERE status IN ('open','in_progress') AND (topic LIKE '%classif%' OR topic LIKE '%dialect%' OR topic LIKE '%language or%')") as $g) {
    $add('Classification problems', 'info', "Open gap #{$g['id']}: {$g['topic']}.");
}

// Report
$sev = ['problem' => 0, 'check' => 1, 'info' => 2];
$counts = [];
echo "# Phase 4 quality control — {$state['name']}\n\n";
echo "Read-only check run " . date('j F Y, H:i') . ". Nothing was changed.\n\n";
echo "**Scope:** the state, its " . count($lgas) . " LGAs and {$wardCount} wards, and every record linked to them: ";
echo implode(', ', array_map(fn($t) => count($rows[$t] ?? []) . ' ' . str_replace('_', ' ', $t), ['ethnic_groups', 'languages', 'polities', 'cultural_records', 'places'])) . ", " . count($rel) . " links.\n\n";
echo "Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.\n\n";
echo "| Check | Problems | Checks | Info |\n|---|---|---|---|\n";
$checks = ['Duplicates', 'Ethnic-name aliases', 'Geographic consistency', 'Source quality', 'Unsupported claims', 'Contradictory claims', 'Missing LGAs / coverage', 'Classification problems'];
foreach ($checks as $c) {
    $f = $findings[$c] ?? [];
    $n = array_count_values(array_column($f, 0));
    echo "| {$c} | " . ($n['problem'] ?? 0) . " | " . ($n['check'] ?? 0) . " | " . ($n['info'] ?? 0) . " |\n";
}
foreach ($checks as $c) {
    echo "\n## {$c}\n\n";
    $f = $findings[$c] ?? [];
    if (!$f) { echo "No findings.\n"; continue; }
    usort($f, fn($a, $b) => $sev[$a[0]] <=> $sev[$b[0]]);
    foreach ($f as [$s, $t]) echo "- **{$s}**: {$t}\n";
}
