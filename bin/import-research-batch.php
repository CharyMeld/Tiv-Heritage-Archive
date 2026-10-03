<?php
/**
 * Import a prepared Nigeria Heritage research batch (JSON from database/research/*.py).
 *
 * Everything is inserted in ONE transaction, as review_status 'in_review' (nothing is
 * published) under a new research_batches row with status 'awaiting_review'. Sources
 * already in the archive (same URL) are reused. Refuses to run twice for the same batch.
 *
 * Default is a DRY RUN (rolled back). --apply commits.
 *
 * Usage: php bin/import-research-batch.php <batch.json> "<batch title>" [--apply]
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/services/StableId.php';

[$file, $title] = [$argv[1] ?? null, $argv[2] ?? null];
$apply = in_array('--apply', $argv, true);
if (!$file || !$title || !is_file($file)) {
    fwrite(STDERR, "Usage: php bin/import-research-batch.php <batch.json> \"<batch title>\" [--apply]\n");
    exit(1);
}
$data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
$db = Database::getInstance();

$exists = $db->prepare('SELECT id FROM research_batches WHERE title = ?');
$exists->execute([$title]);
if ($exists->fetchColumn()) {
    fwrite(STDERR, "[ABORT] A research batch titled \"{$title}\" already exists. Nothing changed.\n");
    exit(1);
}

// A unit's parent is either another unit in this batch (its key) or an existing
// published record, written '@admin_units:<unit_type>:<slug>' (e.g. '@admin_units:state:benue').
// LGA names repeat across states (Obi, Bassa, Surulere, …), so an LGA may be written
// '@admin_units:lga:<state slug>/<lga slug>' (e.g. '@admin_units:lga:benue/obi'); a bare
// slug that matches more than one unit is refused rather than guessed.
$existingParent = function (?string $ref) use ($db): ?int {
    if (!$ref || !str_starts_with($ref, '@admin_units:')) return null;
    [, $type, $slug] = explode(':', $ref, 3);
    if (str_contains($slug, '/')) {
        [$parentSlug, $slug] = explode('/', $slug, 2);
        $st = $db->prepare("SELECT u.id FROM admin_units u JOIN admin_units p ON p.id = u.parent_id
                            WHERE u.unit_type = ? AND u.slug = ? AND p.slug = ? AND p.unit_type IN ('state', 'federal_capital_territory')");
        $st->execute([$type, $slug, $parentSlug]);
    } else {
        $st = $db->prepare('SELECT id FROM admin_units WHERE unit_type = ? AND slug = ?');
        $st->execute([$type, $slug]);
    }
    $ids = $st->fetchAll(PDO::FETCH_COLUMN);
    if (!$ids) { fwrite(STDERR, "[ABORT] Parent {$ref} not found. Nothing changed.\n"); exit(1); }
    if (count($ids) > 1) { fwrite(STDERR, "[ABORT] {$ref} matches " . count($ids) . " units; write it as @admin_units:{$type}:<state>/{$slug}. Nothing changed.\n"); exit(1); }
    return (int) $ids[0];
};
// An existing national record (ethnic group, language, …) or Tiv collection person,
// written '@<table>:<slug>' (e.g. '@languages:tivoid', '@historical_figures:makir-zakpe-1').
// Never created or changed here.
$existingRecord = function (string $ref) use ($db): ?array {
    if (!preg_match('/^@(ethnic_groups|languages|polities|cultural_records|historical_periods|places|historical_figures):([a-z0-9-]+)$/', $ref, $m)) return null;
    $st = $db->prepare("SELECT id FROM {$m[1]} WHERE slug = ? LIMIT 1");
    $st->execute([$m[2]]);
    $id = $st->fetchColumn();
    if (!$id) { fwrite(STDERR, "[ABORT] Existing record {$ref} not found. Nothing changed.\n"); exit(1); }
    return [$m[1], (int) $id];
};
// Never overwrite: abort if a unit with the same slug already exists under the same parent and type.
$slugTaken = $db->prepare('SELECT COUNT(*) FROM admin_units WHERE unit_type = ? AND slug = ? AND parent_id <=> ?');
foreach ($data['units'] as $u) {
    $slugTaken->execute([$u['unit_type'], $u['slug'], $existingParent($u['parent'] ?? null)]);
    if ($slugTaken->fetchColumn()) {
        fwrite(STDERR, "[ABORT] admin_units already has {$u['unit_type']} '{$u['slug']}' under the same parent (existing records are never overwritten). Nothing changed.\n");
        exit(1);
    }
}

$counts = array_fill_keys(['sources_new', 'sources_reused', 'units', 'places', 'records', 'entity_sources', 'names', 'changes', 'relations', 'statistics', 'gaps'], 0);
$RECORD_TABLES = ['ethnic_groups', 'languages', 'polities', 'cultural_records', 'historical_periods'];
// National people and events share their tables with the Tiv collection: they use `status`
// (draft/published) rather than review_status, live at /nigeria/people|events/{national_slug},
// and are tagged to the 'nigeria' collection only so they never appear in the Tiv lists
// (core/Model::collectionScope()).
$NATIONAL_TABLES = ['timeline_events', 'historical_figures'];
$nigeriaCollection = (int) $db->query("SELECT id FROM collections WHERE code = 'nigeria'")->fetchColumn();
if (!$nigeriaCollection) { fwrite(STDERR, "[ABORT] No 'nigeria' collection. Nothing changed.\n"); exit(1); }
$db->beginTransaction();
try {
    $db->prepare("INSERT INTO research_batches (title, scope, status, conducted_by, started_at, completed_at, summary, unresolved_notes)
                  VALUES (?, ?, 'awaiting_review', 'claude', NOW(), NOW(), ?, ?)")
       ->execute([$title, $data['scope'] ?? 'Nigeria, the 36 states and the FCT: capitals, creation dates, predecessor units, geopolitical zones.',
                  sprintf('%d administrative units, %d capitals, %d other records, %d administrative changes, %d relations, %d statistics, %d other names.', count($data['units']), count($data['places']), count($data['records'] ?? []), count($data['changes']), count($data['relations'] ?? []), count($data['statistics'] ?? []), count($data['names'])),
                  implode("\n\n", array_map(fn($g) => "{$g[0]}: {$g[1]}", $data['gaps']))]);
    $batch = (int) $db->lastInsertId();

    // Sources: reuse by URL, otherwise insert.
    $src = [];
    $findSrc = $db->prepare('SELECT id FROM sources WHERE url = ? LIMIT 1');
    $cols = ['source_type', 'title', 'author', 'contributor_name', 'organisation', 'publisher', 'publication_date', 'publication_details', 'url', 'archive_reference', 'notes', 'verification_status',
             'source_kind', 'source_tier', 'lineage_group']; // the last three: migration 002 (Step 5 source type, tier, copy group)
    $insSrc = $db->prepare('INSERT INTO sources (' . implode(',', $cols) . ', access_date) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ', ?)');
    foreach ($data['sources'] as $key => $s) {
        $findSrc->execute([$s['url']]);
        if ($id = $findSrc->fetchColumn()) { $src[$key] = (int) $id; $data['sources'][$key]['_reused'] = true; $counts['sources_reused']++; continue; }
        $insSrc->execute([...array_map(fn($c) => $s[$c] ?? null, $cols), date('Y-m-d')]);
        $src[$key] = (int) $db->lastInsertId();
        $counts['sources_new']++;
    }

    // Record exactly which source rows this batch created (sources have no batch column),
    // so a rollback can delete those ids and nothing else.
    $newIds = [];
    foreach ($data['sources'] as $key => $s) if (!empty($src[$key]) && ($s['_reused'] ?? false) === false) $newIds[] = $src[$key];
    $db->prepare("UPDATE research_batches SET summary = CONCAT(summary, ?) WHERE id = ?")
       ->execute(["\nSources created by this batch (ids): " . implode(',', $newIds), $batch]);

    $insES = $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance, research_batch_id) VALUES (?, ?, ?, ?, 'supports', ?)");
    $attach = function (string $table, int $id, array $pairs) use ($insES, $src, $batch, &$counts) {
        foreach ($pairs as [$key, $claim]) { $insES->execute([$table, $id, $src[$key], $claim, $batch]); $counts['entity_sources']++; }
    };

    // Units: parents before children (the country first).
    $unit = [];
    $insUnit = $db->prepare("INSERT INTO admin_units (unit_type, parent_id, name, official_name, slug, official_code, geopolitical_zone, status, created_on,
                                 created_precision, ended_on, ended_precision, summary, evidence_status, evidence_level, review_status, research_batch_id)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_review', ?)");
    $ordered = $data['units'];
    $rank = fn($u) => ($u['parent'] === null || str_starts_with((string) $u['parent'], '@')) ? 0 : 1;
    usort($ordered, fn($a, $b) => $rank($a) <=> $rank($b));
    foreach ($ordered as $u) {
        $parentId = $u['parent'] === null ? null : ($existingParent($u['parent']) ?? $unit[$u['parent']]);
        $insUnit->execute([$u['unit_type'], $parentId, $u['name'], $u['official_name'] ?? null, $u['slug'], $u['official_code'] ?? null,
                           $u['geopolitical_zone'] ?? null, $u['status'], $u['created_on'] ?? null, $u['created_precision'] ?? 'unknown',
                           $u['ended_on'] ?? null, $u['ended_precision'] ?? 'unknown', $u['summary'] ?? null, $u['evidence'], $u['level'] ?? null, $batch]);
        $unit[$u['key']] = (int) $db->lastInsertId();
        $attach('admin_units', $unit[$u['key']], $u['srcs']);
        $counts['units']++;
    }

    // Capitals.
    $place = [];
    $insPlace = $db->prepare("INSERT INTO places (place_type, name, slug, admin_unit_id, summary, evidence_status, review_status, research_batch_id)
                              VALUES ('settlement', ?, ?, ?, ?, ?, 'in_review', ?)");
    foreach ($data['places'] as $p) {
        $insPlace->execute([$p['name'], $p['slug'], $unit[$p['unit']], $p['summary'], $p['evidence'], $batch]);
        $place[$p['key']] = (int) $db->lastInsertId();
        $attach('places', $place[$p['key']], $p['srcs']);
        $counts['places']++;
    }
    $setCap = $db->prepare('UPDATE admin_units SET capital_place_id = ? WHERE id = ?');
    foreach ($data['units'] as $u) {
        if (!empty($u['capital'])) $setCap->execute([$place[$u['capital']], $unit[$u['key']]]);
    }

    // Other records (ethnic groups, languages, …): fields as given; a field value
    // '@key:<record key>' refers to a record created earlier in this batch.
    $record = [];
    foreach ($data['records'] ?? [] as $r) {
        // 'places' too: heritage sites, museums, monuments (capitals come through 'places' above).
        if (!in_array($r['table'], [...$RECORD_TABLES, ...$NATIONAL_TABLES, 'places'], true)) throw new RuntimeException("unsupported table {$r['table']}");
        $national = in_array($r['table'], $NATIONAL_TABLES, true);
        $f = $r['fields'];
        foreach ($f as $k => $v) if (is_string($v) && str_starts_with($v, '@key:')) $f[$k] = $record[substr($v, 5)][1];
        foreach ($f as $k => $v) if (is_string($v) && str_starts_with($v, '@src:')) $f[$k] = $src[substr($v, 5)];
        foreach ($f as $k => $v) if (is_string($v) && ($ex = $existingRecord($v))) $f[$k] = $ex[1];
        foreach ($f as $k => $v) if (is_string($v) && str_starts_with($v, '@admin_units:')) $f[$k] = $existingParent($v); // e.g. a place's LGA
        if ($national) {
            if (empty($f['national_slug'])) throw new RuntimeException("{$r['table']} record {$r['key']} needs a national_slug");
            $f += ['slug' => $f['national_slug']];
            $exists = $db->prepare("SELECT COUNT(*) FROM {$r['table']} WHERE national_slug = ? OR slug = ?");
            $exists->execute([$f['national_slug'], $f['slug']]);
            if ($exists->fetchColumn()) throw new RuntimeException("{$r['table']} already has slug '{$f['national_slug']}' (never overwritten)");
            $f += ['evidence_status' => $r['evidence'], 'status' => 'draft', 'research_batch_id' => $batch] + (isset($r['level']) ? ['evidence_level' => $r['level']] : []);
        } else {
            $exists = $db->prepare("SELECT COUNT(*) FROM {$r['table']} WHERE slug = ?");
            $exists->execute([$f['slug']]);
            if ($exists->fetchColumn()) throw new RuntimeException("{$r['table']} already has slug '{$f['slug']}' (never overwritten)");
            $f += ['evidence_status' => $r['evidence'], 'review_status' => 'in_review', 'research_batch_id' => $batch] + (isset($r['level']) ? ['evidence_level' => $r['level']] : []);
        }
        $cols = array_keys($f);
        $db->prepare("INSERT INTO {$r['table']} (" . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')
           ->execute(array_values($f));
        $record[$r['key']] = [$r['table'], (int) $db->lastInsertId()];
        if ($national) {
            $db->prepare("INSERT INTO collection_items (collection_id, entity_table, entity_id) VALUES (?, ?, ?)")
               ->execute([$nigeriaCollection, $r['table'], $record[$r['key']][1]]);
        }
        $attach($r['table'], $record[$r['key']][1], $r['srcs']);
        $counts['records']++;
    }
    // Enrich existing records: fill fields that are EMPTY only (never overwrite); the
    // filled fields are recorded in the batch summary so a rollback can empty them again.
    $filled = [];
    foreach ($data['updates'] ?? [] as $up) {
        // '@admin_units:<type>:<slug>' or an existing national record '@<table>:<slug>'.
        if (str_starts_with($up['ref'], '@admin_units:')) {
            $tbl = 'admin_units';
            $cur = $db->prepare("SELECT * FROM {$tbl} WHERE id = ?");
            $cur->execute([$existingParent($up['ref'])]); // also '@admin_units:lga:<state>/<lga>'
        } else {
            if (!preg_match('/^@(ethnic_groups|languages|places):([a-z0-9-]+)$/', $up['ref'], $um)) throw new RuntimeException("unsupported update target {$up['ref']}");
            $tbl = $um[1];
            $cur = $db->prepare("SELECT * FROM {$tbl} WHERE slug = ? LIMIT 1");
            $cur->execute([$um[2]]);
        }
        $row = $cur->fetch();
        if (!$row) throw new RuntimeException("update target {$up['ref']} not found");
        foreach ($up['fields'] as $k => $v) {
            if (trim((string) ($row[$k] ?? '')) !== '') throw new RuntimeException("{$up['ref']}.{$k} already has content — existing records are never overwritten");
            if (is_string($v) && str_starts_with($v, '@src:')) $up['fields'][$k] = $src[substr($v, 5)]; // e.g. vitality_source_id
            if (is_string($v) && str_starts_with($v, '@key:')) $up['fields'][$k] = $record[substr($v, 5)][1]; // e.g. headquarters_place_id
            if (is_string($v) && ($ex = $existingRecord($v))) $up['fields'][$k] = $ex[1];
        }
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($up['fields'])));
        $db->prepare("UPDATE {$tbl} SET {$sets} WHERE id = ?")->execute([...array_values($up['fields']), $row['id']]);
        $attach($tbl, (int) $row['id'], $up['srcs']);
        $filled[] = "{$tbl}#{$row['id']}:" . implode(',', array_keys($up['fields']));
        $counts['updates'] = ($counts['updates'] ?? 0) + 1;
    }
    if ($filled) {
        $db->prepare("UPDATE research_batches SET summary = CONCAT(summary, ?) WHERE id = ?")
           ->execute(["\nFields filled by this batch: " . implode(';', $filled), $batch]);
    }

    // A reference to any record: '@admin_units:<type>:<slug>' (existing) or a batch key.
    $ref = function (string $x) use ($existingParent, $existingRecord, $unit, $record): array {
        if (str_starts_with($x, '@admin_units:')) return ['admin_units', $existingParent($x)];
        if ($ex = $existingRecord($x)) return $ex;
        if (isset($record[$x])) return $record[$x];
        if (isset($unit[$x])) return ['admin_units', $unit[$x]];
        throw new RuntimeException("unknown reference {$x}");
    };

    // Other names.
    $insName = $db->prepare('INSERT INTO entity_names (entity_table, entity_id, name, name_type, valid_from_year, valid_to_year, usage_notes, source_id, research_batch_id)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($data['names'] as $n) {
        [$table, $id] = isset($n['record']) ? $ref($n['record']) : (isset($n['place']) ? ['places', $place[$n['place']]] : ['admin_units', $unit[$n['unit']]]);
        $insName->execute([$table, $id, $n['name'], $n['name_type'], $n['valid_from_year'] ?? null, $n['valid_to_year'] ?? null,
                           $n['usage_notes'] ?? null, $src[$n['srcs'][0]], $batch]);
        $counts['names']++;
    }

    // Administrative changes: one source per row (the first); the others are named in the notes.
    $insChange = $db->prepare('INSERT INTO admin_unit_changes (change_type, from_unit_id, to_unit_id, old_value, new_value, effective_date, effective_text,
                                   date_precision, notes, source_id, evidence_status, research_batch_id)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($data['changes'] as $c) {
        $others = array_slice($c['srcs'], 1);
        $notes = trim(($others ? 'Also supported by: ' . implode(', ', array_map(fn($k) => $data['sources'][$k]['title'] . ' (' . ($data['sources'][$k]['organisation'] ?? '') . ')', $others)) . '. ' : '')
                      . ($c['notes'] ?? '')) ?: null;
        $insChange->execute([$c['change_type'], $c['from_unit'] ? $unit[$c['from_unit']] : null, $unit[$c['to_unit']],
                             $c['old_value'] ?? null, $c['new_value'] ?? null, $c['effective_date'] ?? null, $c['effective_text'] ?? null,
                             $c['precision'], $notes, $src[$c['srcs'][0]], $c['evidence'], $batch]);
        $counts['changes']++;
    }

    // Relations between records (existing or new), each with its source and evidence.
    // Optional migration-002 fields: settlement_status, location_type, speaker_role, level (evidence_level).
    $insRel = $db->prepare("INSERT INTO entity_relations (from_table, from_id, relation_type, to_table, to_id, valid_from_year, valid_to_year, valid_to_text,
                                date_precision, role, notes, source_id, evidence_status, settlement_status, location_type, speaker_role, evidence_level,
                                review_status, research_batch_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_review', ?)");
    foreach ($data['relations'] ?? [] as $r) {
        [$ft, $fi] = $ref($r['from']); [$tt, $ti] = $ref($r['to']);
        $insRel->execute([$ft, $fi, $r['type'], $tt, $ti, $r['valid_from_year'] ?? null, $r['valid_to_year'] ?? null, $r['valid_to_text'] ?? null,
                          $r['precision'] ?? 'unknown', $r['role'] ?? null, $r['notes'] ?? null, $src[$r['source']], $r['evidence'],
                          $r['settlement_status'] ?? null, $r['location_type'] ?? null, $r['speaker_role'] ?? null, $r['level'] ?? null, $batch]);
        $counts['relations']++;
    }
    // Statistics: always with a source; conflicting figures are kept side by side.
    $insStat = $db->prepare("INSERT INTO entity_statistics (entity_table, entity_id, metric, value_low, value_high, reference_year, method, notes, source_id, evidence_status, evidence_level, research_batch_id)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($data['statistics'] ?? [] as $s) {
        [$t, $i] = $ref($s['record']);
        $insStat->execute([$t, $i, $s['metric'], $s['value_low'], $s['value_high'] ?? null, $s['reference_year'] ?? null, $s['method'] ?? null,
                           $s['notes'] ?? null, $src[$s['source']], $s['evidence'], $s['level'] ?? null, $batch]);
        $counts['statistics']++;
    }

    $insGap = $db->prepare("INSERT INTO research_gaps (research_batch_id, topic, description, status) VALUES (?, ?, ?, 'open')");
    foreach ($data['gaps'] as [$topic, $desc]) { $insGap->execute([$batch, $topic, $desc]); $counts['gaps']++; }

    // Permanent IDs for the new units, places and records (StableId; never regenerated).
    foreach (['admin_units', 'places', ...$RECORD_TABLES, ...$NATIONAL_TABLES] as $t) {
        foreach ($db->query("SELECT id FROM {$t} WHERE research_batch_id = {$batch} AND stable_id IS NULL")->fetchAll(PDO::FETCH_COLUMN) as $newId) {
            StableId::assign($db, $t, (int) $newId);
        }
    }
    foreach (['admin_units', ...$RECORD_TABLES] as $t) if ($db->query("SELECT COUNT(*) FROM {$t} WHERE review_status = 'published' AND research_batch_id = {$batch}")->fetchColumn()) {
        throw new RuntimeException('import must not publish anything');
    }
    foreach ($NATIONAL_TABLES as $t) if ($db->query("SELECT COUNT(*) FROM {$t} WHERE status = 'published' AND research_batch_id = {$batch}")->fetchColumn()) {
        throw new RuntimeException('import must not publish anything');
    }
    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
echo ($apply ? '[APPLIED]' : '[DRY RUN - rolled back]') . " batch \"{$title}\": " . json_encode($counts) . "\n";
