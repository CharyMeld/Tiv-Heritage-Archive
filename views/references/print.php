<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<style>
    /* Self-contained: no styles.css, no script.js on this page, so it never
       picks up the site's content-protection behavior (see script.js and
       the @media print rule in styles.css, both scoped to body.protected). */
    * { box-sizing: border-box; }
    body {
        font-family: Georgia, 'Times New Roman', serif;
        color: #1f1f1f;
        max-width: 700px;
        margin: 2rem auto;
        padding: 0 1.5rem;
        line-height: 1.6;
    }
    .print-toolbar { text-align: right; margin-bottom: 1.5rem; }
    .print-toolbar button {
        font-family: 'Segoe UI', system-ui, sans-serif;
        font-size: .9rem;
        padding: .6rem 1.1rem;
        border: 2px solid #5C3A21;
        background: #5C3A21;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
    }
    h1 { font-size: 1.5rem; margin: 0 0 .25rem; }
    .type-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6B5744; }
    .citation-block {
        margin: 1.5rem 0;
        padding: 1rem 1.25rem;
        border-left: 4px solid #C8A951;
        background: #F7F4EE;
        font-style: italic;
    }
    dl { display: grid; grid-template-columns: max-content 1fr; gap: .35rem 1rem; margin: 1.5rem 0; }
    dt { font-weight: 700; color: #6B5744; }
    dd { margin: 0; }
    .footer-note { margin-top: 2.5rem; font-size: .8rem; color: #6B5744; border-top: 1px solid #D4C4B0; padding-top: 1rem; }
    @media print {
        .print-toolbar { display: none; }
        body { margin: 0; max-width: none; }
    }
</style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()">&#128424;&#65039; Print this page</button>
    </div>

    <div class="type-label"><?= e(Source::$typeLabels[$source['source_type']] ?? ucfirst($source['source_type'])) ?></div>
    <h1><?= e($source['title'] ?: 'Untitled source') ?></h1>

    <div class="citation-block"><?= e($defaultCitation) ?></div>

    <?php
    $facts = [
        'Author' => $source['author'],
        'Contributor' => $source['contributor_name'],
        'Publisher' => $source['publisher'],
        'Year' => $source['year_recorded'],
        'Location' => $source['location'],
        'ISBN' => $source['isbn'],
        'DOI' => $source['doi'],
        'URL' => $source['url'],
        'Access Date' => $source['access_date'],
    ];
    $facts = array_filter($facts, fn($v) => !empty($v));
    ?>
    <?php if ($facts): ?>
    <dl>
        <?php foreach ($facts as $label => $value): ?>
        <dt><?= e($label) ?></dt>
        <dd><?= e($value) ?></dd>
        <?php endforeach; ?>
    </dl>
    <?php endif; ?>

    <?php if (!empty($source['notes'])): ?>
    <p><?= nl2br(e($source['notes'])) ?></p>
    <?php endif; ?>

    <p class="footer-note">
        Tiv Heritage Archive &middot; <?= e($canonicalUrl) ?>
    </p>
</body>
</html>
