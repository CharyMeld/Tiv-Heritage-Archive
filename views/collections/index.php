<?php
/**
 * Collections hub (CollectionController::index).
 * Variables: $letters [letter => count], $names [gender => count], $counts, $pages, $breadcrumb
 */
$genderLabels = ['male' => 'Male names', 'female' => 'Female names', 'unisex' => 'Unisex names'];
?>
<?php $this->partial('collection-styles'); ?>

<div class="page-banner">
    <div class="container">
        <div class="hf-breadcrumb-dark"><?php $this->partial('breadcrumb', ['breadcrumb' => $breadcrumb]); ?></div>
        <span class="page-banner-eyebrow">Read the archive in full</span>
        <h1 class="page-banner-title">Collections</h1>
        <p class="page-banner-sub">Every record in the archive, written out in full and grouped for reading</p>
    </div>
</div>

<div class="col-wrap">
    <div class="container col-container">
        <p class="col-intro">
            The archive's records are short on their own: a word and its meaning, a name and what it expresses, a proverb and its sense.
            These collection pages bring them together so they can be read the way a dictionary or anthology is read — every entry in full,
            with its examples, meanings and notes, and a link to the entry's own page with related records and sources.
        </p>

        <div class="col-hub">
            <section class="col-hub-section">
                <h2>Tiv–English Dictionary</h2>
                <p><?= number_format($counts['words']) ?> Tiv words with English meanings, parts of speech and, where recorded, pronunciation, example sentences and usage notes. Choose a letter:</p>
                <nav class="col-nav" aria-label="Dictionary letters">
                    <?php foreach ($letters as $l => $n): ?>
                    <a href="<?= url('collections/dictionary/' . $l) ?>" class="col-nav-link" title="<?= (int) $n ?> words"><?= e(strtoupper($l)) ?></a>
                    <?php endforeach; ?>
                </nav>
            </section>

            <section class="col-hub-section">
                <h2>Tiv Names and Their Meanings</h2>
                <p><?= number_format($counts['names']) ?> Tiv names with their English meanings, origins and the occasions they are given.</p>
                <nav class="col-nav" aria-label="Names">
                    <?php foreach ($genderLabels as $g => $label): if (empty($names[$g])) continue; ?>
                    <a href="<?= url('collections/names/' . $g) ?>" class="col-nav-link"><?= e($label) ?> (<?= (int) $names[$g] ?>)</a>
                    <?php endforeach; ?>
                </nav>
            </section>

            <section class="col-hub-section">
                <h2>Tiv Proverbs</h2>
                <p><?= number_format($counts['proverbs']) ?> proverbs with English translations, their deeper meanings and when they are used.</p>
                <nav class="col-nav" aria-label="Proverb parts">
                    <?php for ($i = 1; $i <= $pages['proverbs']; $i++): ?>
                    <a href="<?= url('collections/proverbs' . ($i > 1 ? '/' . $i : '')) ?>" class="col-nav-link">Part <?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </section>

            <section class="col-hub-section">
                <h2>Tiv Plants and Their Uses</h2>
                <p><?= number_format($counts['plants']) ?> plants with English and scientific names and their medicinal, food and ritual uses.</p>
                <nav class="col-nav" aria-label="Plant parts">
                    <?php for ($i = 1; $i <= $pages['plants']; $i++): ?>
                    <a href="<?= url('collections/plants' . ($i > 1 ? '/' . $i : '')) ?>" class="col-nav-link">Part <?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </section>

            <section class="col-hub-section">
                <h2>Animals, Foods and Festivals</h2>
                <p>Each of these collections fits on one page.</p>
                <nav class="col-nav" aria-label="Other collections">
                    <?php if ($counts['animals']): ?><a href="<?= url('collections/animals') ?>" class="col-nav-link">Animals (<?= (int) $counts['animals'] ?>)</a><?php endif; ?>
                    <?php if ($counts['foods']): ?><a href="<?= url('collections/foods') ?>" class="col-nav-link">Foods (<?= (int) $counts['foods'] ?>)</a><?php endif; ?>
                    <?php if ($counts['festivals']): ?><a href="<?= url('collections/festivals') ?>" class="col-nav-link">Festivals (<?= (int) $counts['festivals'] ?>)</a><?php endif; ?>
                </nav>
            </section>
        </div>
    </div>
</div>
