<?php
/**
 * Partial: Historical Figures advanced filter panel (collapsed by default)
 * Variables expected: $filters (as $f), $periods, $formAction, $search
 */
$f = $filters ?? [];
$hasActiveFilters = array_diff_key($f, ['status' => 1, 'category' => 1, 'subcategory' => 1]);
?>
<details class="hf-filter-panel" <?= $hasActiveFilters ? 'open' : '' ?>>
    <summary class="hf-filter-summary">&#128269; Advanced Filters</summary>
    <form method="GET" action="<?= e($formAction) ?>" class="hf-filter-form">
        <?php if (!empty($search)): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>

        <div class="hf-filter-grid">
            <div class="form-group">
                <label class="form-label">Historical Period</label>
                <select name="historical_period" class="form-select">
                    <option value="">All Periods</option>
                    <?php foreach ($periods as $period): ?>
                    <option value="<?= e($period) ?>" <?= ($f['historical_period'] ?? '') === $period ? 'selected' : '' ?>><?= e($period) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Any</option>
                    <option value="male" <?= ($f['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= ($f['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Clan</label>
                <input type="text" name="clan" class="form-input" value="<?= e($f['clan'] ?? '') ?>" placeholder="e.g. Kunav">
            </div>

            <div class="form-group">
                <label class="form-label">District</label>
                <input type="text" name="district" class="form-input" value="<?= e($f['district'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Local Government</label>
                <input type="text" name="local_government" class="form-input" value="<?= e($f['local_government'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-input" value="<?= e($f['state'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Religion</label>
                <input type="text" name="religion" class="form-input" value="<?= e($f['religion'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Occupation</label>
                <input type="text" name="occupation" class="form-input" value="<?= e($f['occupation'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Traditional Title</label>
                <input type="text" name="title" class="form-input" value="<?= e($f['title'] ?? '') ?>" placeholder="e.g. Tor Tiv">
            </div>

            <div class="form-group">
                <label class="form-label">Birth Year</label>
                <input type="number" name="birth_year" class="form-input" value="<?= e($f['birth_year'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Death Year</label>
                <input type="number" name="death_year" class="form-input" value="<?= e($f['death_year'] ?? '') ?>">
            </div>
        </div>

        <div style="display:flex; gap:.75rem; margin-top:.5rem;">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="<?= e($formAction) ?>" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</details>
