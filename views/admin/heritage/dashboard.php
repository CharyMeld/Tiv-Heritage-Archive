<?php
$h = fn($v) => htmlspecialchars((string) $v);
$muted = 'color:#9a8a7a;font-size:.8rem;';
$card = 'background:#fff;border:1px solid #efe8de;border-radius:10px;padding:.9rem 1rem;';
$entities = HeritageRegistry::entities();
$typeName = fn($k) => $entities[$k]['label'] ?? $k;
$statesDone = count(array_filter($states, fn($s) => $s['review_status'] === 'published' && $s['sources'] > 0));
$totalRecords = array_sum(array_column($counts, 'total'));
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:.75rem;margin-bottom:1.2rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#2d1b0e;margin:0;">&#127475;&#127468; Nigeria Heritage — Research Dashboard</h1>
            <p style="<?= $muted ?>margin:.25rem 0 0;">Accuracy before volume. Every record is added in a small, sourced research batch and reviewed before publishing.</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="<?= url('admin/heritage/research-batches/create') ?>" class="btn btn-primary">+ New research batch</a>
            <a href="<?= url('admin/heritage/research-gaps/create') ?>" class="btn btn-secondary">+ Record a gap</a>
        </div>
    </div>

    <!-- Records by type -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.8rem;margin-bottom:1.2rem;">
        <?php foreach ($counts as $key => $c): ?>
        <a href="<?= url("admin/heritage/{$key}") ?>" style="<?= $card ?>text-decoration:none;color:inherit;display:block;">
            <div style="font-size:.82rem;color:#7a6a5a;"><?= $c['icon'] ?> <?= $h($c['label']) ?></div>
            <div style="font-size:1.5rem;font-weight:700;color:#2d1b0e;"><?= (int) $c['total'] ?></div>
            <div style="<?= $muted ?>"><?= (int) $c['published'] ?> published · <?= (int) $c['in_review'] ?> in review
                <?php if ($c['attention']): ?> · <span style="color:#a0522d;"><?= (int) $c['attention'] ?> need attention</span><?php endif; ?></div>
        </a>
        <?php endforeach; ?>
        <div style="<?= $card ?>">
            <div style="font-size:.82rem;color:#7a6a5a;">&#128218; Sources</div>
            <div style="font-size:1.5rem;font-weight:700;color:#2d1b0e;"><?= $linkedSourceCount ?></div>
            <div style="<?= $muted ?>">cited by national records · <?= $sourceCount ?> in the archive · <a href="<?= url('admin/sources') ?>">manage</a></div>
        </div>
        <div style="<?= $card ?>">
            <div style="font-size:.82rem;color:#7a6a5a;">&#127760; Relationships</div>
            <div style="font-size:1.5rem;font-weight:700;color:#2d1b0e;"><?= $relationCount ?></div>
            <div style="<?= $muted ?>">links between records</div>
        </div>
    </div>

    <?php if (!empty($newCounts)): ?>
    <!-- Cultural-layer records (migration 002) -->
    <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">Cultural layer, claims &amp; field work</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.8rem;margin-bottom:1.2rem;">
        <?php foreach ($newCounts as $key => $c): ?>
        <a href="<?= url("admin/heritage/{$key}") ?>" style="<?= $card ?>text-decoration:none;color:inherit;display:block;">
            <div style="font-size:.82rem;color:#7a6a5a;"><?= $c['icon'] ?> <?= $h($c['label']) ?></div>
            <div style="font-size:1.5rem;font-weight:700;color:#2d1b0e;"><?= (int) $c['total'] ?></div>
            <?php if (isset($c['open'])): ?><div style="<?= $muted ?>"><?= (int) $c['open'] ?> open</div><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:1rem;">

        <!-- States coverage -->
        <div style="<?= $card ?>">
            <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">States &amp; FCT — <?= $statesDone ?> / 37 complete</h3>
            <p style="<?= $muted ?>margin-top:0;">Complete = published with at least one source. The state list itself is research batch 1.</p>
            <?php if (!$states): ?>
                <p style="<?= $muted ?>">No states recorded yet.</p>
            <?php else: ?>
            <table style="width:100%;font-size:.82rem;border-collapse:collapse;">
                <tr style="text-align:left;color:#7a6a5a;"><th>State</th><th>Status</th><th>Evidence</th><th style="text-align:right;">Sources</th><th style="text-align:right;">LGAs</th><th>LGA research</th></tr>
                <?php foreach ($states as $s): ?>
                <tr style="border-top:1px solid #f3eee7;">
                    <td><a href="<?= url("admin/heritage/admin-units/{$s['id']}/edit") ?>" style="color:#5C3A21;"><?= $h($s['name']) ?></a></td>
                    <td><?= $h(HeritageRegistry::REVIEW[$s['review_status']] ?? $s['review_status']) ?></td>
                    <td><?= $h(HeritageRegistry::EVIDENCE[$s['evidence_status']] ?? $s['evidence_status']) ?></td>
                    <td style="text-align:right;"><?= (int) $s['sources'] ?></td>
                    <td style="text-align:right;"><?= (int) $s['lgas'] ?></td>
                    <td><?php if ($s['progress_id']): ?><a href="<?= url("admin/heritage/research-progress/{$s['progress_id']}/edit") ?>" style="color:#5C3A21;">
                        <?= $h(HeritageRegistry::get('research-progress')['fields']['status']['options'][$s['progress_status']] ?? $s['progress_status']) ?></a>
                        <?= $s['lgas_researched'] ? '<span style="' . $muted . '">(' . (int) $s['lgas_researched'] . ' researched, ' . (int) $s['lgas_verified'] . ' verified)</span>' : '' ?>
                        <?php else: ?><span style="<?= $muted ?>">—</span><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>

        <!-- Research batches -->
        <div style="<?= $card ?>">
            <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">Research batches</h3>
            <p style="<?= $muted ?>margin-top:0;">
                <?php foreach (['planned' => 'planned', 'in_progress' => 'in progress', 'awaiting_review' => 'awaiting review', 'approved' => 'approved', 'rejected' => 'rejected'] as $k => $label): ?>
                    <?= (int) ($batchStatus[$k] ?? 0) ?> <?= $label ?> ·
                <?php endforeach; ?>
                <a href="<?= url('admin/heritage/research-batches') ?>">all</a>
            </p>
            <?php foreach ($batches as $b): ?>
            <div style="border-top:1px solid #f3eee7;padding:.35rem 0;font-size:.84rem;">
                <a href="<?= url("admin/heritage/research-batches/{$b['id']}/edit") ?>" style="color:#5C3A21;font-weight:600;"><?= $h($b['title']) ?></a>
                <span style="<?= $muted ?>">· <?= $h(str_replace('_', ' ', $b['status'])) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (!$batches): ?><p style="<?= $muted ?>">No batches yet.</p><?php endif; ?>
        </div>

        <!-- Needs attention -->
        <div style="<?= $card ?>">
            <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">Needs attention</h3>
            <p style="<?= $muted ?>margin-top:0;">Awaiting review, disputed, needing corroboration, unverified or outdated.</p>
            <?php foreach ($attention as $a): ?>
            <div style="border-top:1px solid #f3eee7;padding:.35rem 0;font-size:.84rem;">
                <a href="<?= url("admin/heritage/{$a['type_key']}/{$a['id']}/edit") ?>" style="color:#5C3A21;"><?= $h($a['name']) ?></a>
                <span style="<?= $muted ?>">· <?= $h($typeName($a['type_key'])) ?> · <?= $h(HeritageRegistry::EVIDENCE[$a['evidence_status']] ?? '') ?> · <?= $h(HeritageRegistry::REVIEW[$a['review_status']] ?? '') ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (!$attention): ?><p style="<?= $muted ?>">Nothing flagged.</p><?php endif; ?>
        </div>

        <!-- Gaps -->
        <div style="<?= $card ?>">
            <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">Open research gaps (<?= $gapCount ?>)</h3>
            <p style="<?= $muted ?>margin-top:0;">What could not be verified — recorded instead of guessed. <a href="<?= url('admin/heritage/research-gaps') ?>">all gaps</a></p>
            <?php foreach ($gaps as $g): ?>
            <div style="border-top:1px solid #f3eee7;padding:.35rem 0;font-size:.84rem;">
                <a href="<?= url("admin/heritage/research-gaps/{$g['id']}/edit") ?>" style="color:#5C3A21;"><?= $h($g['topic']) ?></a>
                <span style="<?= $muted ?>">· <?= $h(str_replace('_', ' ', $g['status'])) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (!$gaps): ?><p style="<?= $muted ?>">No open gaps.</p><?php endif; ?>
        </div>

        <!-- Duplicates -->
        <div style="<?= $card ?>">
            <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">Duplicate candidates (<?= count($duplicates) ?>)</h3>
            <p style="<?= $muted ?>margin-top:0;">Same name within a record type. Never merged automatically — review each one.</p>
            <?php foreach ($duplicates as $d):
                $alt = str_starts_with($d['type_key'], 'alt:');
                $key = $alt ? HeritageRegistry::keyForTable(substr($d['type_key'], 4)) : $d['type_key'];
            ?>
            <div style="border-top:1px solid #f3eee7;padding:.35rem 0;font-size:.84rem;">
                "<?= $h($d['norm']) ?>" <span style="<?= $muted ?>">· <?= $h($typeName($key)) ?><?= $alt ? ' (alternative name)' : '' ?> ·</span>
                <?php foreach (explode(',', $d['ids']) as $id): ?>
                    <?php if ($key): ?><a href="<?= url("admin/heritage/{$key}/{$id}/edit") ?>" style="color:#5C3A21;">#<?= (int) $id ?></a><?php else: ?>#<?= (int) $id ?><?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <?php if (!$duplicates): ?><p style="<?= $muted ?>">None found.</p><?php endif; ?>
        </div>

        <!-- Published without sources -->
        <div style="<?= $card ?>">
            <h3 style="margin:0 0 .5rem;font-size:1rem;color:#5C3A21;">Published without a source (<?= count($unsourcedPublished) ?>)</h3>
            <p style="<?= $muted ?>margin-top:0;">Should always be empty; the editor blocks this, but imports could bypass it.</p>
            <?php foreach ($unsourcedPublished as $u): ?>
            <div style="border-top:1px solid #f3eee7;padding:.35rem 0;font-size:.84rem;">
                <a href="<?= url("admin/heritage/{$u['type_key']}/{$u['id']}/edit") ?>" style="color:#a33;"><?= $h($u['name']) ?></a>
                <span style="<?= $muted ?>">· <?= $h($typeName($u['type_key'])) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (!$unsourcedPublished): ?><p style="<?= $muted ?>">&#10003; None.</p><?php endif; ?>
        </div>
    </div>

    <p style="<?= $muted ?>margin-top:1.2rem;"><?= $totalRecords ?> national records in total. Public pages under /nigeria/ arrive in the next step; nothing here is visible to visitors yet.</p>
</div>
</div>
