<?php
/**
 * Partial: Timeline era chips + advanced filters (century, decade, category)
 * Variables expected: $eras, $eraCounts, $activeEra, $centuries, $activeCentury,
 * $decades, $activeDecade, $categories, $categoryCounts, $activeCategory,
 * $search, $formAction
 */
$eraShortLabel = fn(string $era) => trim(preg_replace('/\s*\(.*\)$/', '', $era));
$eraCarry = function (?string $era) use ($activeCentury, $activeDecade, $activeCategory, $search) {
    $params = array_filter([
        'era' => $era, 'century' => $activeCentury, 'decade' => $activeDecade,
        'category' => $activeCategory, 'q' => $search,
    ], fn($v) => $v !== null && $v !== '');
    return url('timeline') . (count($params) ? '?' . http_build_query($params) : '');
};
?>
<div class="tl-era-chips">
    <a href="<?= e($eraCarry(null)) ?>" class="tl-era-chip<?= !$activeEra ? ' is-active' : '' ?>">All Eras</a>
    <?php foreach ($eras as $era): ?>
    <a href="<?= e($eraCarry($era)) ?>" class="tl-era-chip<?= $activeEra === $era ? ' is-active' : '' ?>">
        <?= e($eraShortLabel($era)) ?> <span class="tl-era-chip-count">(<?= (int) ($eraCounts[$era] ?? 0) ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<details class="hf-filter-panel" <?= ($activeCentury || $activeDecade || $activeCategory) ? 'open' : '' ?>>
    <summary class="hf-filter-summary">&#128269; Century, Decade &amp; Category</summary>
    <form method="GET" action="<?= e($formAction) ?>" class="hf-filter-form">
        <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
        <?php if (!empty($activeEra)): ?><input type="hidden" name="era" value="<?= e($activeEra) ?>"><?php endif; ?>

        <div class="hf-filter-grid">
            <div class="form-group">
                <label class="form-label">Century</label>
                <select name="century" class="form-select">
                    <option value="">All Centuries</option>
                    <?php foreach ($centuries as $c): ?>
                    <option value="<?= e($c) ?>" <?= $activeCentury === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Decade</label>
                <select name="decade" class="form-select">
                    <option value="">All Decades</option>
                    <?php foreach ($decades as $d): ?>
                    <option value="<?= e($d) ?>" <?= $activeDecade === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $activeCategory === $cat ? 'selected' : '' ?>><?= e($cat) ?> (<?= (int) ($categoryCounts[$cat] ?? 0) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:.75rem; margin-top:.5rem;">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="<?= e($eraCarry($activeEra)) ?>" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</details>
