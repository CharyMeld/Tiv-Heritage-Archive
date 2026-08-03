<?php
/**
 * Partial: Timeline keyword search box
 * Variables expected: $search, $searchActionUrl, $searchPlaceholder
 */
?>
<div class="page-banner-search">
    <form action="<?= e($searchActionUrl) ?>" method="GET">
        <div class="explore-search-wrap">
            <svg class="explore-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" name="q" class="explore-search-input" placeholder="<?= e($searchPlaceholder ?? 'Search…') ?>" value="<?= e($search ?? '') ?>" autocomplete="off">
            <button type="submit" class="explore-search-btn">Search</button>
        </div>
    </form>
</div>
