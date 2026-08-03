<?php
/**
 * Timeline — browse page (era chips, century/decade/category filters, keyword search)
 * Variables: see TimelineController::index()
 */
?>
<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128197; History</span>
        <h1 class="page-banner-title">&#128337; Timeline</h1>
        <p class="page-banner-sub">An interactive, searchable chronology of Tiv history — from earliest origins to the present day.</p>
        <?php $this->partial('timeline-search-box'); ?>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container" style="max-width: 900px;">
        <?php $this->partial('breadcrumb'); ?>

        <?php $this->partial('timeline-filter-panel', ['formAction' => url('timeline')]); ?>

        <?php $this->partial('timeline-list'); ?>
    </div>
</div>
