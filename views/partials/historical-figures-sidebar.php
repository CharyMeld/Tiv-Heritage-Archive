<?php
/**
 * Partial: Historical Figures category tree sidebar
 * Variables expected: $sidebarData (tree from HistoricalFigureController::buildSidebarData()),
 * $activeCategorySlug, $activeSubcategorySlug (either may be null)
 */
?>
<aside class="hf-sidebar">
    <button type="button" class="hf-sidebar-toggle" id="hfSidebarToggle" aria-expanded="false" aria-controls="hfSidebarTree">
        &#9776; Browse Categories
    </button>
    <nav class="hf-sidebar-tree" id="hfSidebarTree" aria-label="Historical Figures categories">
        <a href="<?= url('historical-figures') ?>" class="hf-sidebar-root <?= !$activeCategorySlug ? 'hf-sidebar-active' : '' ?>">
            Historical Figures
        </a>
        <ul class="hf-sidebar-list">
            <?php foreach ($sidebarData as $cat): ?>
            <li class="hf-sidebar-item">
                <a href="<?= url('historical-figures/' . $cat['slug']) ?>"
                   class="hf-sidebar-link <?= $activeCategorySlug === $cat['slug'] ? 'hf-sidebar-active' : '' ?>">
                    <span class="hf-sidebar-icon"><?= $cat['icon'] ?></span>
                    <span class="hf-sidebar-label"><?= e($cat['label']) ?></span>
                    <span class="hf-sidebar-count"><?= (int) $cat['count'] ?></span>
                </a>
                <?php if (!empty($cat['subcategories'])): ?>
                <ul class="hf-sidebar-sublist">
                    <?php foreach ($cat['subcategories'] as $sub): ?>
                    <li>
                        <a href="<?= url('historical-figures/' . $cat['slug'] . '/' . $sub['slug']) ?>"
                           class="hf-sidebar-sublink <?= $activeSubcategorySlug === $sub['slug'] ? 'hf-sidebar-active' : '' ?>">
                            <span class="hf-sidebar-icon"><?= $sub['icon'] ?></span>
                            <span class="hf-sidebar-label"><?= e($sub['label']) ?></span>
                            <span class="hf-sidebar-count"><?= (int) $sub['count'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>

<script>
(function () {
    var BP = 1025;
    function isDesktop() { return window.innerWidth >= BP; }

    var toggle = document.getElementById('hfSidebarToggle');
    var tree = document.getElementById('hfSidebarTree');
    if (!toggle || !tree) return;

    toggle.addEventListener('click', function () {
        if (isDesktop()) return;
        var isOpen = tree.classList.toggle('hf-sidebar-tree--open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    window.addEventListener('resize', function () {
        if (isDesktop()) {
            tree.classList.remove('hf-sidebar-tree--open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
