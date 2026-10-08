<?php
$h = fn($v) => htmlspecialchars((string) $v);
$isEdit = $item !== null;
$base = "admin/heritage/{$e['key']}";
$formAction = $isEdit ? url("{$base}/{$item['id']}/edit") : url("{$base}/create");
$oldInput = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);
$val = fn($field, $default = '') => $oldInput[$field] ?? $item[$field] ?? $default;
$precision = HeritageRegistry::PRECISION;
$evidence = HeritageRegistry::EVIDENCE;
$levels = HeritageRegistry::EVIDENCE_LEVEL;
$tierShort = fn($t) => $t ? 'Tier ' . (int) $t : 'tier not set';
$select = function (string $name, array $options, $selected = '', string $empty = null) use ($h) {
    $out = "<select name=\"{$name}\" class=\"form-select\">";
    if ($empty !== null) $out .= '<option value="">' . $h($empty) . '</option>';
    foreach ($options as $k => $label) {
        $out .= '<option value="' . $h($k) . '"' . ((string) $selected === (string) $k ? ' selected' : '') . '>' . $h($label) . '</option>';
    }
    return $out . '</select>';
};
$del = fn(string $kind, $rowId) => '<form method="POST" action="' . url("{$base}/{$item['id']}/{$kind}/{$rowId}/delete") . '" style="display:inline;" onsubmit="return confirm(\'Remove this?\')">'
    . csrf_field() . '<button type="submit" style="background:none;border:none;color:#c0392b;cursor:pointer;font-size:.78rem;font-weight:600;padding:0;">Remove</button></form>';
$sectionHead = 'font-size:1rem;font-weight:700;color:#5C3A21;margin:0 0 .6rem;';
$muted = 'color:#9a8a7a;font-size:.8rem;';
?>
<div class="admin-wrapper">
<div class="admin-content" style="max-width:980px;">

    <div style="margin-bottom:1rem;display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="<?= url($base) ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">&larr; <?= $h($e['plural']) ?></a>
        <a href="<?= url('admin/heritage') ?>" style="color:#5C3A21;text-decoration:none;font-size:.9rem;">Dashboard</a>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-error" style="margin-bottom:1rem;"><ul style="margin:0;padding-left:1.2rem;">
        <?php foreach ($errors as $err): ?><li><?= $h($err) ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>

    <?php if (!empty($e['readonly'])): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= $h($item[$e['title_field']]) ?></h2>
            <span style="font-size:.78rem;color:#7a6a5a;"><?= $e['icon'] ?> <?= $h($e['label']) ?> #<?= (int) $item['id'] ?> · <?= $h($item['status']) ?>
                <?php if (!empty($stableId)): ?> · <code><?= $h($stableId) ?></code><?php endif; ?></span>
        </div>
        <div class="admin-card-body">
            <p style="<?= $muted ?>margin-top:0;">
                Public address: <a href="<?= $h($publicUrl) ?>" target="_blank"><?= $h($publicUrl) ?></a>
                <?php if (!empty($e['edit_url'])): ?> · <a href="<?= url(sprintf($e['edit_url'], $item['id'])) ?>">Edit the record itself</a><?php endif; ?>
            </p>
            <form method="POST" action="<?= url("{$base}/{$item['id']}/collections") ?>">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:.9rem 1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Collections</label>
                        <label style="display:block;font-size:.88rem;"><input type="checkbox" name="in_tiv" value="1" <?= in_array('tiv', $collections, true) ? 'checked' : '' ?>> Tiv Heritage Archive</label>
                        <label style="display:block;font-size:.88rem;"><input type="checkbox" name="in_nigeria" value="1" <?= in_array('nigeria', $collections, true) ? 'checked' : '' ?>> Nigeria Heritage Archive</label>
                        <small style="<?= $muted ?>">In the Tiv collection it keeps its Tiv address and shows on Tiv pages. Nigeria only: it moves to a /nigeria/ address and leaves the Tiv pages.</small>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">National URL slug (Nigeria-only records)</label>
                        <input type="text" name="national_slug" class="form-input" value="<?= $h($item['national_slug'] ?? '') ?>" placeholder="Made from the name if empty">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Evidence status</label>
                        <?= $select('evidence_status', $evidence, $item['evidence_status'] ?? '', 'Not yet assessed') ?>
                        <small style="<?= $muted ?>">Search engines only index national pages with an assessed status, a source and at least 300 words.</small>
                    </div>
                </div>
                <div style="margin-top:1rem;"><button type="submit" class="btn btn-primary">Save collections</button></div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= $isEdit ? 'Edit: ' . $h($item[$e['title_field']]) : 'Add ' . $h($e['label']) ?></h2>
            <span style="font-size:.78rem;color:#7a6a5a;"><?= $e['icon'] ?> <?= $h($e['label']) ?><?= $isEdit ? ' #' . (int) $item['id'] : '' ?>
                <?php if (!empty($stableId)): ?> · <code title="Permanent ID: never changes, even if the name does"><?= $h($stableId) ?></code><?php endif; ?></span>
        </div>
        <div class="admin-card-body">
            <p style="<?= $muted ?>margin-top:0;">Enter only what the sources support. Leave unknown fields empty rather than estimating.</p>
            <form method="POST" action="<?= $formAction ?>">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:.9rem 1rem;">
                <?php foreach ($e['fields'] as $name => $f):
                    $wide = $f['type'] === 'textarea';
                    $v = $val($name, $f['default'] ?? '');
                ?>
                    <div class="form-group" style="margin:0;<?= $wide ? 'grid-column:1/-1;' : '' ?>">
                        <label class="form-label<?= !empty($f['required']) ? ' required' : '' ?>"><?= $h($f['label']) ?></label>
                        <?php if ($f['type'] === 'textarea'): ?>
                            <textarea name="<?= $name ?>" class="form-textarea" rows="<?= (int) ($f['rows'] ?? 4) ?>"><?= $h($v) ?></textarea>
                        <?php elseif ($f['type'] === 'select'):
                            // A stored value missing from the list is shown, never silently replaced by the first option.
                            $opts = ($v !== '' && $v !== null && !array_key_exists($v, $f['options'])) ? $f['options'] + [$v => "{$v} (unrecognised — choose a value)"] : $f['options']; ?>
                            <?= $select($name, $opts, $v, empty($f['required']) && !isset($f['options']['']) ? '—' : null) ?>
                        <?php elseif ($f['type'] === 'fk'): ?>
                            <?= $select($name, $fkOptions[$name] ?? [], $v, '— none —') ?>
                        <?php else: ?>
                            <input type="<?= $f['type'] === 'number' ? 'number' : ($f['type'] === 'date' ? 'date' : 'text') ?>"
                                   name="<?= $name ?>" class="form-input" value="<?= $h($f['type'] === 'date' ? substr((string) $v, 0, 10) : $v) ?>"
                                   <?= isset($f['step']) ? 'step="' . $h($f['step']) . '"' : '' ?>
                                   <?= isset($f['maxlength']) ? 'maxlength="' . (int) $f['maxlength'] . '"' : '' ?>
                                   <?= !empty($f['required']) ? 'required' : '' ?>>
                        <?php endif; ?>
                        <?php if (!empty($f['help'])): ?><small style="<?= $muted ?>"><?= $h($f['help']) ?></small><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($e['no_slug'])): ?>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">URL slug</label>
                        <input type="text" name="slug" class="form-input" value="<?= $h($val('slug')) ?>" placeholder="Made from the name if empty">
                        <small style="<?= $muted ?>">Changing it on a published record keeps the old address as a redirect.</small>
                    </div>
                <?php endif; ?>
                </div>
                <div style="margin-top:1.2rem;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Save' ?></button>
                    <?php if (isset($e['fields']['review_status'])): ?>
                    <span style="<?= $muted ?>">Publishing needs at least one attached source; stronger evidence statuses need sources too.</span>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

<?php if ($isEdit && empty($e['no_knowledge'])): ?>
    <div id="knowledge"></div>

    <!-- Sources -->
    <div class="admin-card" style="margin-top:1.2rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#128218; Sources (<?= count($sources) ?>)</h3>
            <?php if (!$sources): ?><p style="<?= $muted ?>">No source attached yet. This record cannot be published without one.</p><?php endif; ?>
            <?php foreach ($sources as $s): ?>
            <div style="border-top:1px solid #f0ece5;padding:.45rem 0;font-size:.84rem;">
                <strong><?= $h($s['title'] ?: $s['contributor_name'] ?: 'Source #' . $s['source_id']) ?></strong>
                <?= $s['author'] ? ' — ' . $h($s['author']) : '' ?>
                <span style="<?= $muted ?>">(<?= $h($s['source_kind'] ? HeritageRegistry::SOURCE_KIND[$s['source_kind']][0] ?? $s['source_kind'] : (Source::$typeLabels[$s['source_type']] ?? $s['source_type'])) ?>,
                    <?= $h($tierShort($s['source_tier'])) ?><?= $s['lineage_group'] ? ', copy group ' . $h($s['lineage_group']) : '' ?>, <?= $h($s['stance']) ?>)</span>
                <?php if ($s['claim']): ?><div style="<?= $muted ?>">Claim: <?= $h($s['claim']) ?></div><?php endif; ?>
                <?php if ($s['page_section']): ?><div style="<?= $muted ?>">Page/section: <?= $h($s['page_section']) ?></div><?php endif; ?>
                <?= $del('sources', $s['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Attach a source</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/sources") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <div style="grid-column:1/-1;"><?= $select('source_id', $allSources, '', '— choose a source —') ?>
                        <small style="<?= $muted ?>">Not listed? <a href="<?= url('admin/sources/create') ?>" target="_blank">Add it to Sources</a> first, then reload.</small></div>
                    <input type="text" name="claim" class="form-input" placeholder="Which statement it supports">
                    <input type="text" name="page_section" class="form-input" placeholder="Page / section">
                    <?= $select('stance', ['supports' => 'Supports', 'contradicts' => 'Contradicts', 'mentions' => 'Mentions'], 'supports') ?>
                    <?= $select('research_batch_id', $allBatches, '', '— research batch —') ?>
                    <textarea name="quote" class="form-textarea" rows="2" placeholder="Short quote (optional)" style="grid-column:1/-1;"></textarea>
                    <div><button class="btn btn-secondary">Attach source</button></div>
                </form>
            </details>
        </div>
    </div>

    <!-- Names -->
    <div class="admin-card" style="margin-top:1rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#127991; Other names (<?= count($names) ?>)</h3>
            <?php foreach ($names as $n): ?>
            <div style="border-top:1px solid #f0ece5;padding:.4rem 0;font-size:.84rem;">
                <strong><?= $h($n['name']) ?></strong> <span style="<?= $muted ?>"><?= $h(str_replace('_', ' ', $n['name_type'])) ?>
                <?= $n['valid_from_year'] || $n['valid_to_year'] ? '· ' . $h($n['valid_from_year'] ?: '?') . '–' . $h($n['valid_to_year'] ?: '') : '' ?></span>
                <?php if ($n['usage_notes']): ?><div style="<?= $muted ?>"><?= $h($n['usage_notes']) ?></div><?php endif; ?>
                <?= $del('names', $n['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Add a name</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/names") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <input type="text" name="name" class="form-input" placeholder="Name" required>
                    <?= $select('name_type', ['alternative' => 'Alternative', 'historical' => 'Historical', 'endonym' => 'Endonym', 'exonym' => 'Exonym', 'colonial' => 'Colonial', 'spelling_variant' => 'Spelling variant', 'abbreviation' => 'Abbreviation', 'official' => 'Official', 'other' => 'Other'], 'alternative') ?>
                    <?= $select('language_id', $allLanguages, '', '— language —') ?>
                    <input type="number" name="valid_from_year" class="form-input" placeholder="Used from (year)">
                    <input type="number" name="valid_to_year" class="form-input" placeholder="Used until (year)">
                    <?= $select('source_id', $allSources, '', '— source —') ?>
                    <input type="text" name="usage_notes" class="form-input" placeholder="Usage notes (e.g. considered derogatory)" style="grid-column:1/-1;">
                    <div><button class="btn btn-secondary">Add name</button></div>
                </form>
            </details>
        </div>
    </div>

    <!-- Statistics -->
    <div class="admin-card" style="margin-top:1rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#128202; Statistics (<?= count($statistics) ?>)</h3>
            <p style="<?= $muted ?>margin-top:0;">Every figure needs its source and year. Conflicting figures are kept side by side, never overwritten.</p>
            <?php foreach ($statistics as $st): ?>
            <div style="border-top:1px solid #f0ece5;padding:.4rem 0;font-size:.84rem;">
                <strong><?= $h(str_replace('_', ' ', $st['metric'])) ?>:</strong>
                <?= number_format((int) $st['value_low']) ?><?= $st['value_high'] ? '–' . number_format((int) $st['value_high']) : '' ?>
                <span style="<?= $muted ?>"><?= $st['reference_year'] ? '(' . (int) $st['reference_year'] . ')' : '' ?> <?= $h($st['method']) ?> · <?= $h($st['source_title']) ?> · <?= $h($evidence[$st['evidence_status']] ?? '') ?><?= !empty($st['evidence_level']) ? ' · level: ' . $h($levels[$st['evidence_level']] ?? '') : '' ?></span>
                <?= $del('statistics', $st['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Add a statistic</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/statistics") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <?= $select('metric', ['population' => 'Population', 'speakers' => 'Speakers', 'first_language_speakers' => 'First-language speakers', 'second_language_speakers' => 'Second-language speakers', 'area_km2' => 'Area (km²)', 'households' => 'Households', 'other' => 'Other'], 'population') ?>
                    <input type="number" name="value_low" class="form-input" placeholder="Value (or lower bound)" min="0" required>
                    <input type="number" name="value_high" class="form-input" placeholder="Upper bound (if a range)" min="0">
                    <input type="number" name="reference_year" class="form-input" placeholder="Reference year">
                    <?= $select('method', ['census' => 'Census', 'projection' => 'Projection', 'estimate' => 'Estimate', 'survey' => 'Survey', 'other' => 'Other'], '', '— method —') ?>
                    <?= $select('evidence_status', $evidence, 'single_reliable_source') ?>
                    <?= $select('evidence_level', $levels, '', '— evidence level —') ?>
                    <div style="grid-column:1/-1;"><?= $select('source_id', $allSources, '', '— source (required) —') ?></div>
                    <input type="text" name="notes" class="form-input" placeholder="Notes" style="grid-column:1/-1;">
                    <div><button class="btn btn-secondary">Add statistic</button></div>
                </form>
            </details>
        </div>
    </div>

    <!-- Relations -->
    <div class="admin-card" style="margin-top:1rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#127760; Relationships (<?= count($relations) ?>)</h3>
            <?php foreach ($relations as $r):
                $otherKey = HeritageRegistry::keyForTable($r['other_table']);
                $otherLabel = HeritageRegistry::LINKABLE[$r['other_table']] ?? $r['other_table'];
            ?>
            <div style="border-top:1px solid #f0ece5;padding:.4rem 0;font-size:.84rem;">
                <em><?= $h($item[$e['title_field']]) ?></em> <?= $h($r['reads']) ?>
                <strong><?php if ($otherKey): ?><a href="<?= url("admin/heritage/{$otherKey}/{$r['other_id']}/edit") ?>" style="color:#5C3A21;"><?= $h($r['other_name']) ?></a><?php else: ?><?= $h($r['other_name']) ?><?php endif; ?></strong>
                <span style="<?= $muted ?>">(<?= $h($otherLabel) ?>)
                <?= $r['valid_from_year'] || $r['valid_to_year'] || $r['valid_from_text'] ? '· ' . $h($r['valid_from_text'] ?: $r['valid_from_year'] ?: '?') . '–' . $h($r['valid_to_text'] ?: $r['valid_to_year'] ?: '') : '' ?>
                <?= $r['role'] ? '· ' . $h($r['role']) : '' ?>
                <?= $r['settlement_status'] ? '· ' . $h(HeritageRegistry::SETTLEMENT[$r['settlement_status']] ?? '') : '' ?>
                <?= $r['location_type'] ? '· ' . $h(HeritageRegistry::LOCATION_TYPE[$r['location_type']] ?? '') : '' ?>
                <?= $r['speaker_role'] ? '· ' . $h(HeritageRegistry::SPEAKER_ROLE[$r['speaker_role']] ?? '') : '' ?>
                · <?= $h($evidence[$r['evidence_status']] ?? '') ?><?= $r['evidence_level'] ? ' / ' . $h($levels[$r['evidence_level']] ?? '') : '' ?>
                <?= ($r['sensitivity'] ?? 'public') !== 'public' ? '· ' . $h(HeritageRegistry::SENSITIVITY[$r['sensitivity']] ?? '') : '' ?>
                · <?= $h($r['review_status']) ?><?= $r['source_id'] ? '' : ' · no source' ?></span>
                <?= $del('relations', $r['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Add a relationship</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/relations") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <?= $select('direction', ['outgoing' => 'This record …', 'incoming' => '… this record (reverse)'], 'outgoing') ?>
                    <select name="relation_type" class="form-select" required>
                        <option value="">— relationship —</option>
                        <?php foreach ($relationTypes as $rt): ?>
                        <option value="<?= $h($rt['code']) ?>" title="<?= $h($rt['description']) ?>"><?= $h($rt['label']) ?> / <?= $h($rt['inverse_label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="target" class="form-select" required style="grid-column:1/-1;">
                        <option value="">— related record —</option>
                        <?php foreach ($targets as $group => $opts): ?>
                        <optgroup label="<?= $h($group) ?>">
                            <?php foreach ($opts as $k => $label): if ($k === $e['table'] . ':' . $item['id']) continue; ?>
                            <option value="<?= $h($k) ?>"><?= $h($label) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="valid_from_year" class="form-input" placeholder="From (year)">
                    <input type="text" name="valid_from_text" class="form-input" placeholder="From (as stated)">
                    <input type="number" name="valid_to_year" class="form-input" placeholder="Until (year)">
                    <input type="text" name="valid_to_text" class="form-input" placeholder="Until (as stated)">
                    <?= $select('date_precision', $precision, 'unknown') ?>
                    <input type="text" name="role" class="form-input" placeholder="Role (e.g. 5th Tor Tiv)">
                    <?= $select('settlement_status', HeritageRegistry::SETTLEMENT, '', '— settlement status (group ↔ place) —') ?>
                    <?= $select('location_type', HeritageRegistry::LOCATION_TYPE, '', '— location type —') ?>
                    <?= $select('speaker_role', HeritageRegistry::SPEAKER_ROLE, '', '— speaker role (language ↔ place) —') ?>
                    <?= $select('evidence_status', $evidence, 'unverified') ?>
                    <?= $select('evidence_level', $levels, '', '— evidence level —') ?>
                    <?= $select('sensitivity', HeritageRegistry::SENSITIVITY, 'public') ?>
                    <?= $select('research_batch_id', $allBatches, '', '— research batch —') ?>
                    <div style="grid-column:1/-1;"><?= $select('source_id', $allSources, '', '— source —') ?></div>
                    <input type="text" name="notes" class="form-input" placeholder="Notes" style="grid-column:1/-1;">
                    <label style="font-size:.84rem;"><input type="checkbox" name="publish" value="1"> Publish now (only with a source, and only if Public)</label>
                    <div><button class="btn btn-secondary">Add relationship</button></div>
                </form>
            </details>
        </div>
    </div>

    <?php if ($e['table'] === 'admin_units'): ?>
    <!-- Administrative history -->
    <div class="admin-card" style="margin-top:1rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#128220; Administrative history (<?= count($changes) ?>)</h3>
            <?php foreach ($changes as $c): ?>
            <div style="border-top:1px solid #f0ece5;padding:.4rem 0;font-size:.84rem;">
                <strong><?= $h($c['effective_text'] ?: $c['effective_date'] ?: 'Date unknown') ?></strong> —
                <?= $h(str_replace('_', ' ', $c['change_type'])) ?>:
                <?= $h($c['from_name'] ?? '—') ?> &rarr; <?= $h($c['to_name'] ?? '—') ?>
                <?= $c['old_value'] || $c['new_value'] ? '<span style="' . $muted . '">(' . $h($c['old_value']) . ' &rarr; ' . $h($c['new_value']) . ')</span>' : '' ?>
                <span style="<?= $muted ?>"><?= $c['legal_instrument'] ? '· ' . $h($c['legal_instrument']) : '' ?> · <?= $h($evidence[$c['evidence_status']] ?? '') ?><?= $c['source_id'] ? '' : ' · no source' ?></span>
                <?= $del('changes', $c['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Record a change</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/changes") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <?= $select('change_type', ['created' => 'Created', 'created_from' => 'Created from (other unit)', 'split_into' => 'Split into (other unit)', 'merged_into' => 'Merged into (other unit)', 'renamed' => 'Renamed', 'boundary_change' => 'Boundary change', 'capital_change' => 'Capital change', 'upgraded' => 'Upgraded (from other unit)', 'abolished' => 'Abolished', 'other' => 'Other'], 'created_from') ?>
                    <?= $select('other_unit_id', $allUnits, '', '— other unit (if any) —') ?>
                    <input type="text" name="old_value" class="form-input" placeholder="Old value (e.g. previous name/capital)">
                    <input type="text" name="new_value" class="form-input" placeholder="New value">
                    <input type="date" name="effective_date" class="form-input">
                    <input type="text" name="effective_text" class="form-input" placeholder="Date as stated">
                    <?= $select('date_precision', $precision, 'unknown') ?>
                    <input type="text" name="legal_instrument" class="form-input" placeholder="Legal instrument (e.g. Decree No. 12 of 1976)">
                    <?= $select('evidence_status', $evidence, 'unverified') ?>
                    <?= $select('research_batch_id', $allBatches, '', '— research batch —') ?>
                    <div style="grid-column:1/-1;"><?= $select('source_id', $allSources, '', '— source —') ?></div>
                    <input type="text" name="notes" class="form-input" placeholder="Notes" style="grid-column:1/-1;">
                    <div><button class="btn btn-secondary">Record change</button></div>
                </form>
            </details>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($isEdit && !empty($e['panels'])): ?>
    <div id="panels"></div>
    <?php $pdel = fn(string $kind, $rowId) => '<form method="POST" action="' . url("{$base}/{$item['id']}/panel/{$kind}/{$rowId}/delete") . '" style="display:inline;" onsubmit="return confirm(\'Remove this?\')">'
        . csrf_field() . '<button type="submit" style="background:none;border:none;color:#c0392b;cursor:pointer;font-size:.78rem;font-weight:600;padding:0;">Remove</button></form>'; ?>

    <?php if (in_array('claim_sources', $e['panels'], true)): ?>
    <div class="admin-card" style="margin-top:1.2rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#128218; Sources for this claim (<?= count($claimSources) ?>)</h3>
            <p style="<?= $muted ?>margin-top:0;">About: <strong><?= $h($claimSubject) ?></strong><?= $claimObject ? ' → <strong>' . $h($claimObject) . '</strong>' : '' ?>.
                Verified needs a Tier 1 source, or two independent sources (sources in the same copy group count as one).</p>
            <?php foreach ($claimSources as $cs): ?>
            <div style="border-top:1px solid #f0ece5;padding:.45rem 0;font-size:.84rem;">
                <strong><?= $h($cs['title'] ?: 'Source #' . $cs['source_id']) ?></strong><?= $cs['author'] ? ' — ' . $h($cs['author']) : '' ?>
                <span style="<?= $muted ?>">(<?= $h($tierShort($cs['source_tier'])) ?><?= $cs['lineage_group'] ? ', copy group ' . $h($cs['lineage_group']) : '' ?>, <?= $h($cs['stance']) ?>)</span>
                <?php if ($cs['page_section']): ?><div style="<?= $muted ?>">Page/section: <?= $h($cs['page_section']) ?></div><?php endif; ?>
                <?php if ($cs['quote']): ?><div style="<?= $muted ?>">“<?= $h($cs['quote']) ?>”</div><?php endif; ?>
                <?= $pdel('claim-sources', $cs['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Attach a source</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/panel/claim-sources") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <div style="grid-column:1/-1;"><?= $select('source_id', $panelSources, '', '— choose a source —') ?></div>
                    <?= $select('stance', ['supports' => 'Supports', 'contradicts' => 'Contradicts', 'mentions' => 'Mentions'], 'supports') ?>
                    <input type="text" name="page_section" class="form-input" placeholder="Page / section">
                    <input type="date" name="accessed_on" class="form-input" title="Accessed on">
                    <?= $select('research_batch_id', $panelBatches, '', '— research batch —') ?>
                    <textarea name="quote" class="form-textarea" rows="2" placeholder="Short quote (optional)" style="grid-column:1/-1;"></textarea>
                    <div><button class="btn btn-secondary">Attach source</button></div>
                </form>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <?php if (in_array('attributes', $e['panels'], true)): ?>
    <div class="admin-card" style="margin-top:1.2rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#129530; Details (<?= count($attributes) ?>)</h3>
            <p style="<?= $muted ?>margin-top:0;">Ingredients, materials, instruments, occasions and other details, each with its source where possible.</p>
            <?php foreach ($attributes as $a): ?>
            <div style="border-top:1px solid #f0ece5;padding:.4rem 0;font-size:.84rem;">
                <span style="<?= $muted ?>"><?= $h($attributeKinds[$a['attribute']] ?? $a['attribute']) ?>:</span>
                <strong><?= $h($a['value']) ?></strong><?= $a['local_value'] ? ' (' . $h($a['local_value']) . ')' : '' ?>
                <span style="<?= $muted ?>"><?= $a['source_title'] ? '· ' . $h($a['source_title']) : '· no source' ?></span>
                <?= $pdel('attributes', $a['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Add a detail</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/panel/attributes") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <?= $select('attribute', $attributeKinds, 'ingredient') ?>
                    <input type="text" name="value" class="form-input" placeholder="Value (English)" required>
                    <input type="text" name="local_value" class="form-input" placeholder="Local term">
                    <input type="number" name="sort_order" class="form-input" placeholder="Order (for steps)" value="0">
                    <div style="grid-column:1/-1;"><?= $select('source_id', $panelSources, '', '— source —') ?></div>
                    <?= $select('research_batch_id', $panelBatches, '', '— research batch —') ?>
                    <div><button class="btn btn-secondary">Add detail</button></div>
                </form>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <?php if (in_array('media_links', $e['panels'], true)): ?>
    <div class="admin-card" style="margin-top:1.2rem;">
        <div class="admin-card-body">
            <h3 style="<?= $sectionHead ?>">&#128279; Records this item shows or documents (<?= count($mediaLinks) ?>)</h3>
            <?php foreach ($mediaLinks as $l): $k = HeritageRegistry::keyForTable($l['entity_table']); ?>
            <div style="border-top:1px solid #f0ece5;padding:.4rem 0;font-size:.84rem;">
                <?= $h(ucfirst($l['role'])) ?>
                <strong><?php if ($k): ?><a href="<?= url("admin/heritage/{$k}/{$l['entity_id']}/edit") ?>" style="color:#5C3A21;"><?= $h($l['name']) ?></a><?php else: ?><?= $h($l['name']) ?><?php endif; ?></strong>
                <span style="<?= $muted ?>">(<?= $h(HeritageRegistry::LINKABLE[$l['entity_table']] ?? $l['entity_table']) ?>)</span>
                <?= $pdel('media-links', $l['id']) ?>
            </div>
            <?php endforeach; ?>
            <details style="margin-top:.6rem;">
                <summary style="cursor:pointer;font-weight:600;color:#5C3A21;font-size:.86rem;">+ Link to a record</summary>
                <form method="POST" action="<?= url("{$base}/{$item['id']}/panel/media-links") ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;margin-top:.6rem;">
                    <?= csrf_field() ?>
                    <?= $select('role', ['depicts' => 'Depicts', 'records' => 'Records', 'documents' => 'Documents'], 'depicts') ?>
                    <select name="target" class="form-select" required style="grid-column:1/-1;">
                        <option value="">— record —</option>
                        <?php foreach ($mediaTargets as $group => $opts): ?>
                        <optgroup label="<?= $h($group) ?>"><?php foreach ($opts as $k2 => $label): ?><option value="<?= $h($k2) ?>"><?= $h($label) ?></option><?php endforeach; ?></optgroup>
                        <?php endforeach; ?>
                    </select>
                    <div><button class="btn btn-secondary">Link</button></div>
                </form>
            </details>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($isEdit && empty($e['readonly'])): ?>
    <form method="POST" action="<?= url("{$base}/{$item['id']}/delete") ?>" style="margin-top:1.5rem;text-align:right;"
          onsubmit="return confirm('Delete this record and all its sources, names, statistics and relationships? This cannot be undone.')">
        <?= csrf_field() ?>
        <button type="submit" style="background:none;border:1px solid #e3c4c0;color:#c0392b;border-radius:6px;padding:.35rem .8rem;cursor:pointer;font-size:.8rem;">Delete record (admin only)</button>
    </form>
<?php endif; ?>
</div>
</div>
