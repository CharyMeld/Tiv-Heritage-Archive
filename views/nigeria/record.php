<?php
/** Nigeria Heritage record page — any national table (see NigeriaController::record). */
$temporal = $item['temporal_status'] ?? 'historical';
$nature = $item['nature'] ?? null;
?>
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark"><?php $this->partial('breadcrumb'); ?></div>
        <span class="detail-banner-cat">&#127475;&#127468; <?= e($typeLabel) ?></span>
        <h1 class="detail-banner-title"><?= e($name) ?></h1>
        <?php if (!empty($item['endonym']) || !empty($item['local_name']) || !empty($item['official_name'])): ?>
        <p class="detail-banner-sub"><?= e($item['endonym'] ?? $item['local_name'] ?? $item['official_name']) ?></p>
        <?php endif; ?>
        <div class="detail-banner-badges"><?= HeritagePublic::evidenceBadge($item['evidence_status'] ?? null, $item['evidence_level'] ?? null) ?></div>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container" style="max-width: 820px;">

        <?php if ($preview): ?>
        <div class="ng-notice ng-notice-preview"><strong>Preview.</strong> This record is not published; only editors can see this page and it is hidden from search engines.</div>
        <?php endif; ?>

        <?php if (in_array($nature, ['oral_tradition', 'folklore'], true) || ($item['evidence_status'] ?? '') === 'oral_tradition'): ?>
        <div class="ng-notice"><strong>Oral tradition.</strong> This is preserved as a cultural record of what communities recount. It is not presented as proven historical fact.</div>
        <?php endif; ?>
        <?php if ($table === 'timeline_events' && $temporal !== 'historical'): ?>
        <div class="ng-notice ng-notice-future"><strong><?= e(HeritageRegistry::TEMPORAL[$temporal] ?? $temporal) ?>.</strong>
            <?= $temporal === 'current' ? 'This describes a current situation and may change.' : 'This has not happened as a completed event; it is recorded as ' . e(strtolower(HeritageRegistry::TEMPORAL[$temporal] ?? $temporal)) . ', as stated by the sources below.' ?></div>
        <?php endif; ?>
        <?php if (($item['evidence_status'] ?? '') === 'disputed' || ($item['evidence_level'] ?? '') === 'disputed'): ?>
        <div class="ng-notice"><strong>Disputed.</strong> Sources disagree on parts of this record. Where accounts differ, they are noted rather than resolved.</div>
        <?php endif; ?>

        <?php if (!empty($item['image'])): ?>
        <div style="margin-bottom:1.5rem;border-radius:.75rem;overflow:hidden;">
            <?= SeoHelper::heroPicture($item['image'], $imageCredit['title'] ?? $name, 'width:100%;max-height:460px;object-fit:cover;display:block;', 'fetchpriority="high"') ?>
        </div>
        <?php if (!empty($imageCredit)): ?>
        <p class="ng-image-credit" style="margin:-1rem 0 1.5rem;font-size:.8rem;color:var(--text-muted,#6b7280);">
            <?= e($imageCredit['title']) ?>.
            Photo<?= $imageCredit['creator'] ? ': ' . e($imageCredit['creator']) : '' ?>,
            <?php if ($imageCredit['licence_url']): ?><a href="<?= e($imageCredit['licence_url']) ?>" rel="license noopener" target="_blank"><?= e($imageCredit['licence']) ?></a><?php else: ?><?= e($imageCredit['licence'] ?? 'licence not recorded') ?><?php endif; ?><?php if ($imageCredit['source_url']): ?>, via <a href="<?= e($imageCredit['source_url']) ?>" rel="noopener" target="_blank"><?= e($imageCredit['source_label']) ?></a><?php endif; ?>.
        </p>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($summary): ?><p class="ng-intro"><?= e($summary) ?></p><?php endif; ?>

        <?php if ($facts): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Key facts</span>
            <dl class="ng-facts">
                <?php foreach ($facts as $label => $value): ?>
                <div><dt><?= e($label) ?></dt>
                    <dd><?= is_array($value) ? '<a class="ng-link" href="' . e($value['url']) . '">' . e($value['name']) . '</a>' : e($value) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php endif; ?>

        <?php foreach ($prose as $col => $label): ?>
        <div class="detail-section-modern ng-prose">
            <span class="detail-section-label"><?= e($label) ?></span>
            <?= HeritagePublic::paragraphs($item[$col]) ?>
        </div>
        <?php endforeach; ?>

        <?php if ($history): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Administrative history</span>
            <ul class="ng-list">
                <?php foreach ($history as $c): ?>
                <?php
                $u = fn($x) => $x ? '<a class="ng-link" href="' . e($x['url']) . '">' . e($x['name']) . '</a>' : null;
                $from = $u($c['from']); $to = $u($c['to']);
                $sentence = match ($c['change_type']) {
                    'created_from', 'split_into' => $to && $from ? "{$to} was created from {$from}" : null,
                    'merged_into'    => $from && $to ? "{$from} was merged into {$to}" : null,
                    'renamed'        => 'Renamed from ' . e($c['old_value'] ?: '?') . ' to ' . e($c['new_value'] ?: '?'),
                    'capital_change' => 'Capital moved from ' . e($c['old_value'] ?: '?') . ' to ' . e($c['new_value'] ?: '?'),
                    default          => null,
                };
                $sentence ??= e(ucfirst(str_replace('_', ' ', $c['change_type']))) . (($from || $to) ? ': ' . implode(' → ', array_filter([$from, $to])) : '');
                ?>
                <li><strong><?= e(HeritagePublic::dateLabel(null, $c['effective_text'], $c['date_precision'], $c['effective_date']) ?? 'Date not recorded') ?></strong> —
                    <?= $sentence ?>
                    <span class="ng-meta">· <?= e(mb_strtolower(HeritageRegistry::EVIDENCE[$c['evidence_status']] ?? '')) ?></span>
                    <?= $c['legal_instrument'] ? '<div class="ng-meta">' . e($c['legal_instrument']) . '</div>' : '' ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php foreach ($children as $heading => $rows): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label"><?= e($heading) ?> (<?= count($rows) ?>)</span>
            <ul class="ng-list">
                <?php foreach ($rows as $c): ?>
                <li><?php if ($c['url']): ?><a class="ng-link" href="<?= e($c['url']) ?>"><?= e($c['name']) ?></a><?php else: ?><?= e($c['name']) ?><?php endif; ?><?= $c['note'] ? ' <span class="ng-meta">· ' . e($c['note']) . '</span>' : '' ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if (str_starts_with($heading, 'Wards')): ?>
            <p class="ng-meta" style="margin:.5rem 0 0;">From INEC's Directory of Polling Units (revised January 2015). INEC notes that the directory is not a legal or administrative document for boundary or political claims.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($tivLinks)): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">From the Tiv Heritage collection</span>
            <p class="ng-meta">The Tiv Heritage Archive, the original collection of this site, documents <?= $table === 'languages' ? 'the Tiv language' : 'Tiv history and culture' ?> in depth:</p>
            <ul class="ng-list">
                <?php foreach ($tivLinks as $c): ?>
                <li><a class="ng-link" href="<?= e($c['url']) ?>"><?= e($c['name']) ?></a><?= $c['note'] ? ' <span class="ng-meta">· ' . e($c['note']) . '</span>' : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($attributes)): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Details</span>
            <?php $kinds = HeritageRegistry::CULTURAL_ATTRIBUTES; $byKind = [];
            foreach ($attributes as $a) $byKind[$kinds[$a['attribute']] ?? ucfirst(str_replace('_', ' ', $a['attribute']))][] = $a; ?>
            <dl class="ng-facts">
                <?php foreach ($byKind as $kind => $rows): ?>
                <div><dt><?= e($kind) ?></dt><dd><?= implode('; ', array_map(fn($a) => e($a['value']) . ($a['local_value'] ? ' (<em>' . e($a['local_value']) . '</em>)' : ''), $rows)) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        </div>
        <?php endif; ?>

        <?php if ($relations): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Connections</span>
            <ul class="ng-list">
                <?php foreach ($relations as $reads => $rows): ?>
                <li><span class="ng-meta"><?= e(ucfirst($name . ' ' . $reads)) ?>:</span><br>
                    <?php foreach ($rows as $i => $r): ?>
                        <?= $i ? ' · ' : '' ?><a class="ng-link" href="<?= e($r['other']['url']) ?>"><?= e($r['other']['name']) ?></a>
                        <?php $when = trim((HeritagePublic::dateLabel($r['valid_from_year'], $r['valid_from_text'], $r['date_precision']) ?? '') . (($r['valid_to_year'] || $r['valid_to_text']) ? '–' . HeritagePublic::dateLabel($r['valid_to_year'], $r['valid_to_text'], $r['date_precision']) : '')); ?>
                        <?php $how = implode(', ', array_filter([
                            HeritagePublic::SETTLEMENT_PUBLIC[$r['settlement_status'] ?? ''] ?? null,
                            !empty($r['speaker_role']) && $r['speaker_role'] !== 'unknown' ? mb_strtolower(HeritageRegistry::SPEAKER_ROLE[$r['speaker_role']] ?? '') : null,
                            trim($r['role'] . ' ' . $when) ?: null])); ?>
                        <?php if ($how): ?><span class="ng-meta">(<?= e($how) ?>)</span><?php endif; ?>
                    <?php endforeach; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ($names): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Other names</span>
            <ul class="ng-list">
                <?php foreach ($names as $n): ?>
                <li><strong><?= e($n['name']) ?></strong>
                    <span class="ng-meta">· <?= e(str_replace('_', ' ', $n['name_type'])) ?><?= $n['language_name'] ? ' · ' . e($n['language_name']) : '' ?>
                    <?= $n['valid_from_year'] || $n['valid_to_year'] ? ' · ' . e($n['valid_from_year'] ?: '?') . '–' . e($n['valid_to_year'] ?: '') : '' ?></span>
                    <?= $n['usage_notes'] ? '<div class="ng-meta">' . e($n['usage_notes']) . '</div>' : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ($statistics): ?>
        <div class="detail-section-modern">
            <span class="detail-section-label">Figures</span>
            <p class="ng-muted" style="margin-top:0;">Each figure is shown with its year and source. Where sources differ, all figures are listed.</p>
            <table class="ng-table">
                <tr><th>Measure</th><th>Figure</th><th>Year</th><th>Source</th></tr>
                <?php foreach ($statistics as $st): ?>
                <tr><td><?= e(ucfirst(str_replace(['_', 'km2'], [' ', 'km²'], $st['metric']))) ?></td>
                    <td><?= number_format((int) $st['value_low']) ?><?= $st['value_high'] ? '–' . number_format((int) $st['value_high']) : '' ?>
                        <?= $st['method'] || !empty($st['evidence_level']) ? '<span class="ng-meta">(' . e(implode(' · ', array_filter([$st['method'],
                            !empty($st['evidence_level']) ? mb_strtolower(HeritageRegistry::EVIDENCE_LEVEL[$st['evidence_level']] ?? '') : null]))) . ')</span>' : '' ?></td>
                    <td><?= $st['reference_year'] ? (int) $st['reference_year'] : '—' ?></td>
                    <td><a class="ng-link" href="<?= url('references/' . (int) $st['source_ref']) ?>"><?= e($st['source_title'] ?: 'Source') ?></a></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <div class="detail-section-modern" id="sources">
            <span class="detail-section-label">Sources</span>
            <?php if ($sources): ?>
            <ol class="ng-sources">
                <?php foreach ($sources as $s): ?>
                <li><a class="ng-link" href="<?= url('references/' . (int) $s['id']) ?>"><?= e($s['title'] ?: $s['contributor_name'] ?: 'Source') ?></a>
                    <?php
                    $bits = array_filter([$s['author'], $s['organisation'] ?? null, $s['publisher'], $s['publication_date'] ?? null]);
                    echo $bits ? ' — ' . e(implode(', ', $bits)) : '';
                    ?>
                    <?= !empty($s['source_tier']) ? ' <span class="ng-meta">· ' . e(HeritagePublic::SOURCE_TIER_LABEL[(int) $s['source_tier']] ?? '') . '</span>' : '' ?>
                    <?= !empty($s['url']) ? ' · <a href="' . e($s['url']) . '" rel="nofollow noopener" target="_blank">link</a>' : '' ?>
                    <?= !empty($s['access_date']) ? '<span class="ng-meta"> (accessed ' . e(date('j M Y', strtotime($s['access_date']))) . ')</span>' : '' ?>
                    <?= $s['claims'] ? '<div class="ng-meta">Supports: ' . e(implode('; ', $s['claims'])) . '</div>' : '' ?>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php else: ?>
            <p class="ng-muted" style="margin:0;">No sources have been attached to this record yet.</p>
            <?php endif; ?>
            <details class="ng-muted" style="margin:.9rem 0 0;">
                <summary style="cursor:pointer;">How evidence is graded</summary>
                <ul style="margin:.4rem 0 0;padding-left:1.2rem;">
                    <?php foreach (HeritagePublic::EVIDENCE_LEVEL_EXPLAIN as $lv => $text): ?>
                    <li><strong><?= e(HeritageRegistry::EVIDENCE_LEVEL[$lv]) ?>:</strong> <?= e($text) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin:.4rem 0 0;">Sources are marked official, academic, reference, community or general web. Sources that copy each other count as one.
                    Oral traditions are recorded as traditions, not as proven fact.</p>
            </details>
            <p class="ng-muted" style="margin:.9rem 0 0;">
                <?php if (!empty($item['stable_id'])): ?>Archive ID: <code><?= e($item['stable_id']) ?></code> · <?php endif; ?>
                Last updated <?= e(date('j F Y', strtotime($item['updated_at'] ?? 'now'))) ?>.
                Spotted an error or have a source to add? <a class="ng-link" href="<?= url('suggestions') ?>">Tell us</a>.
            </p>
        </div>

        <p style="text-align:center;margin-top:1rem;"><a class="ng-link" href="<?= nigeria_url() ?>">&larr; Nigeria Heritage Archive</a></p>
    </div>
</div>
