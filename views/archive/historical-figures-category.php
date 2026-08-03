<?php
/**
 * Historical Figures — category page (subcategory cards for Traditional Leadership)
 * or figure list (all other categories, and any Traditional Leadership subcategory)
 * Variables expected: $mode ('subcategories'|'list'), $category, $categoryIcon, $categorySlug,
 * $subCards (subcategories mode), $subcategory/$items/$pagination/$filters/$periods (list mode),
 * $search, $baseUrl, $searchActionUrl, $searchPlaceholder, $breadcrumb, $sidebarData,
 * $activeCategorySlug, $activeSubcategorySlug
 */
$qs = function (array $overrides = []) use ($search, $filters) {
    $params = $filters ?? [];
    unset($params['status'], $params['category'], $params['subcategory']);
    if (!empty($search)) { $params['q'] = $search; }
    $params = array_merge($params, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '' && !is_array($v));
    return http_build_query($params);
};
$fullBaseUrl = $baseUrl . ($qs() ? '?' . $qs() : '');
?>
<div class="page-banner">
    <div class="container">
        <span class="page-banner-eyebrow"><?= $categoryIcon ?> Historical Figures</span>
        <h1 class="page-banner-title"><?= $categoryIcon ?> <?= e($mode === 'list' && !empty($subcategory) ? $subcategory : $category) ?></h1>
        <?php if ($mode === 'subcategories'): ?>
        <p class="page-banner-sub"><?= e($description ?? '') ?></p>
        <?php endif; ?>
        <?php $this->partial('historical-figures-search-box'); ?>
    </div>
</div>

<div style="padding: 1rem 0 2rem;">
    <div class="container">
        <?php $this->partial('breadcrumb'); ?>

        <div class="hf-browse-layout">
            <?php $this->partial('historical-figures-sidebar'); ?>

            <div class="hf-browse-main">
                <?php if ($mode === 'subcategories'): ?>
                    <div class="hf-category-grid hf-subcategory-grid">
                        <?php foreach ($subCards as $card): ?>
                        <a href="<?= url('historical-figures/' . $categorySlug . '/' . $card['slug']) ?>" class="hf-category-card hf-subcategory-card">
                            <span class="hf-category-card-icon"><?= $card['icon'] ?></span>
                            <span class="hf-category-card-title"><?= e($card['label']) ?> <span class="hf-category-card-count">(<?= (int) $card['count'] ?>)</span></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php $this->partial('historical-figures-filter-panel', ['formAction' => $baseUrl]); ?>
                    <?php $this->partial('historical-figures-card-grid', ['baseUrl' => $fullBaseUrl, 'emptyMessage' => 'No historical figures found in this ' . (!empty($subcategory) ? 'subcategory' : 'category') . '.']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
