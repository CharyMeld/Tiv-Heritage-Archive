<?php
/** Nigeria Heritage section home (see NigeriaController::home). */
$descriptions = [
    'admin_units'        => 'The 36 states, the FCT, LGAs and the provinces and regions that came before them.',
    'ethnic_groups'      => 'Nigeria’s peoples, where they live and what they have preserved.',
    'languages'          => 'Languages and dialects, where they are spoken and how they are written.',
    'places'             => 'Towns, heritage and archaeological sites, museums and archives.',
    'polities'           => 'Kingdoms, emirates, chiefdoms and traditional institutions.',
    'cultural_records'   => 'Festivals, food, dress, music, dance, crafts and traditional knowledge.',
    'historical_figures' => 'Historical and cultural figures, with their sources.',
    'timeline_events'    => 'Events in Nigeria’s history, from the earliest records to today.',
    'historical_periods' => 'The periods of Nigerian history.',
];
$icons = ['admin_units' => '&#127963;', 'ethnic_groups' => '&#128101;', 'languages' => '&#128483;', 'places' => '&#128205;',
          'polities' => '&#128081;', 'cultural_records' => '&#127917;', 'historical_figures' => '&#129332;',
          'timeline_events' => '&#128337;', 'historical_periods' => '&#9203;'];
?>
<div class="detail-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark"><?php $this->partial('breadcrumb'); ?></div>
        <span class="detail-banner-cat">&#127475;&#127468; A collection of the Tiv Heritage Archive</span>
        <h1 class="detail-banner-title">Nigeria Heritage Archive</h1>
        <p class="detail-banner-sub">The Historical, Cultural &amp; Living Memory of Nigeria</p>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container" style="max-width: 1100px;">
        <p class="ng-intro">
            The Nigeria Heritage Archive connects the country’s states, peoples, languages, places, events and cultural heritage into one
            searchable record. It grows from the <a class="ng-link" href="<?= url('/') ?>">Tiv Heritage Archive</a>, which remains its most deeply
            documented collection. Every entry is researched in small batches, carries its sources and an evidence status, and keeps oral
            tradition, scholarly interpretation and documented fact clearly apart.
        </p>

        <div class="ng-grid">
            <?php foreach (['admin_units', 'ethnic_groups', 'languages', 'places', 'polities', 'cultural_records', 'historical_figures', 'timeline_events', 'historical_periods'] as $t):
                $s = $sections[$t]; ?>
            <a class="ng-card" href="<?= nigeria_url($s['path']) ?>">
                <span class="ng-card-type"><?= $icons[$t] ?> Explore</span>
                <p class="ng-card-title"><?= e($s['label']) ?></p>
                <p class="ng-card-text"><?= e($descriptions[$t]) ?></p>
                <p class="ng-card-count"><?= $s['count'] ? (int) $s['count'] . ' documented' : 'Research in progress' ?></p>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($recent): ?>
        <h2 class="ng-group-title">Recently added or updated</h2>
        <div class="ng-grid">
            <?php foreach ($recent as $r): ?>
            <a class="ng-card" href="<?= e($r['url']) ?>">
                <span class="ng-card-type"><?= e($r['type']) ?></span>
                <p class="ng-card-title"><?= e($r['name']) ?></p>
                <?php if ($r['summary']): ?><p class="ng-card-text"><?= e(SeoHelper::truncate($r['summary'], 120)) ?></p><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="detail-section-modern">
            <span class="detail-section-label">How this archive is built</span>
            <div class="ng-prose">
                <p><strong>Sources first.</strong> Each record lists the publications, official documents, archives and community sources it rests on.</p>
                <p><strong>Evidence you can see.</strong> Every record shows whether it is verified, supported by a single source, an oral tradition, disputed or still needing corroboration — and uncertain information is never quietly upgraded.</p>
                <p><strong>History as it changed.</strong> Provinces, regions and states are kept with their dates, so you can follow how today’s Nigeria took shape.</p>
                <p>Have a correction or a source to add? <a class="ng-link" href="<?= url('suggestions') ?>">Send a suggestion</a>.</p>
            </div>
        </div>
    </div>
</div>
