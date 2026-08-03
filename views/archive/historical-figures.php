<?php
/**
 * Historical Figures — landing page (category cards) or global scoped search results
 * Variables expected: $mode ('landing'|'search'), $cards (landing), $items/$pagination (search),
 * $search, $searchActionUrl, $searchPlaceholder, $breadcrumb, $sidebarData,
 * $activeCategorySlug, $activeSubcategorySlug
 */
$qs = function (array $overrides = []) use ($search) {
    $params = [];
    if (!empty($search)) { $params['q'] = $search; }
    $params = array_merge($params, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return http_build_query($params);
};
$baseUrl = url('historical-figures') . ($qs() ? '?' . $qs() : '');
?>
<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#129332; History</span>
        <h1 class="page-banner-title">&#129332; Historical Figures</h1>
        <p class="page-banner-sub">Discover the men and women who have shaped the history, culture, leadership, religion, education, and development of the Tiv people.</p>
        <?php $this->partial('historical-figures-search-box'); ?>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php $this->partial('breadcrumb'); ?>

        <div class="hf-browse-layout">
            <?php $this->partial('historical-figures-sidebar'); ?>

            <div class="hf-browse-main">
                <?php if ($mode === 'search'): ?>
                    <h2 class="hf-section-heading">Search Results<?= !empty($search) ? ' for &ldquo;' . e($search) . '&rdquo;' : '' ?></h2>
                    <?php $this->partial('historical-figures-card-grid', ['baseUrl' => $baseUrl, 'emptyMessage' => 'No historical figures matched your search.']); ?>
                <?php else: ?>
                    <div class="hf-category-grid">
                        <?php foreach ($cards as $card): ?>
                        <a href="<?= url('historical-figures/' . $card['slug']) ?>" class="hf-category-card">
                            <span class="hf-category-card-icon"><?= $card['icon'] ?></span>
                            <span class="hf-category-card-title"><?= e($card['label']) ?> <span class="hf-category-card-count">(<?= (int) $card['count'] ?>)</span></span>
                            <span class="hf-category-card-desc"><?= e($card['description']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
