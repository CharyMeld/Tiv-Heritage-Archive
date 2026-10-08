<?php
/** Nigeria Heritage listing page (see NigeriaController::listing). */
$nameCol = HeritageRegistry::NAME_COLUMN[$table];
$summaryCol = HeritagePublic::SUMMARY[$table] ?? 'summary';
?>
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark"><?php $this->partial('breadcrumb'); ?></div>
        <span class="detail-banner-cat">&#127475;&#127468; Nigeria Heritage Archive</span>
        <h1 class="detail-banner-title"><?= e($heading) ?></h1>
        <?php if ($count): ?><div class="detail-banner-badges"><span class="detail-banner-badge"><?= (int) $count ?> documented</span></div><?php endif; ?>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container" style="max-width: 1100px;">
        <?php if ($intro): ?><p class="ng-intro"><?= e($intro) ?></p><?php endif; ?>

        <?php if (!$count): ?>
        <div class="detail-section-modern">
            <p style="margin:0;">Research for this section is in progress. Records are added in small batches, each checked against cited sources before it is published — accuracy before volume.</p>
            <p class="ng-muted" style="margin:.8rem 0 0;">Meanwhile, explore the <a class="ng-link" href="<?= url('/') ?>">Tiv Heritage Archive</a>.</p>
        </div>
        <?php endif; ?>

        <?php foreach ($groups as $group => $rows): ?>
            <?php if ($group !== ''): ?><h2 class="ng-group-title"><?php if (!empty($groupLinks[$group])): ?><a class="ng-link" href="<?= e($groupLinks[$group]) ?>"><?= e($group) ?></a><?php else: ?><?= e($group) ?><?php endif; ?></h2><?php endif; ?>
            <div class="ng-grid">
                <?php foreach ($rows as $r): ?>
                <a class="ng-card" href="<?= e(HeritagePublic::recordUrl($table, $r)) ?>">
                    <?php if ($table === 'timeline_events'): ?>
                    <span class="ng-card-type"><?= e($r['event_date'] ?: ($r['year'] ?? '')) ?><?= $r['temporal_status'] !== 'historical' ? ' · ' . e(HeritageRegistry::TEMPORAL[$r['temporal_status']]) : '' ?></span>
                    <?php elseif ($table === 'cultural_records' && in_array($r['nature'], ['oral_tradition', 'folklore'], true)): ?>
                    <span class="ng-card-type">Oral tradition</span>
                    <?php endif; ?>
                    <p class="ng-card-title"><?= e($r[$nameCol]) ?></p>
                    <?php if (!empty($r[$summaryCol])): ?><p class="ng-card-text"><?= e(SeoHelper::truncate($r[$summaryCol], 140)) ?></p><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <p class="ng-muted" style="margin-top:1rem;">Every record in the Nigeria Heritage Archive lists its sources and an evidence level. Oral traditions are kept as cultural records, and planned or announced events are kept apart from what has happened.</p>
    </div>
</div>
