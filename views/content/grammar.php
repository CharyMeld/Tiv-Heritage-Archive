<?php
/* ── Tiv Grammar page data ─────────────────────────────────────────── */
$categoryMeta = [
    'noun'                => ['icon' => '&#128214;', 'title' => 'Nouns',              'desc' => 'People, places, things — and how Tiv forms plurals.'],
    'pronoun'             => ['icon' => '&#128100;', 'title' => 'Pronouns',            'desc' => 'The Tiv subject-pronoun paradigm.'],
    'verb'                => ['icon' => '&#128292;', 'title' => 'Verbs',               'desc' => 'Verb types, transitivity, and tense.'],
    'adjective'           => ['icon' => '&#127912;', 'title' => 'Adjectives',          'desc' => 'Possessive adjectives and comparative forms.'],
    'sentence_structure'  => ['icon' => '&#128279;', 'title' => 'Sentence Structure',  'desc' => 'Word order, pronoun substitution, and negation.'],
    'question_formation'  => ['icon' => '&#10067;',  'title' => 'Question Formation',  'desc' => 'Question words and worked question/answer pairs.'],
];

if (!function_exists('tiv_grammar_slug')) {
    function tiv_grammar_slug(string $text): string
    {
        return preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($text))) ?: 'x';
    }
}
if (!function_exists('tiv_grammar_examples_table')) {
    function tiv_grammar_examples_table(array $examples): void
    {
        if (empty($examples)) return;
        $hasNotes = false;
        foreach ($examples as $ex) {
            if (!empty($ex['note'])) { $hasNotes = true; break; }
        }
        echo '<div class="ws-table-wrap"><table class="ws-table"><thead><tr><th>Tiv</th><th>English</th>';
        if ($hasNotes) echo '<th>Note</th>';
        echo '</tr></thead><tbody>';
        foreach ($examples as $ex) {
            echo '<tr><td>' . htmlspecialchars($ex['tiv'] ?? '') . '</td><td>' . htmlspecialchars($ex['english'] ?? '') . '</td>';
            if ($hasNotes) {
                $note = trim((string)($ex['note'] ?? ''));
                echo '<td>' . ($note !== '' ? htmlspecialchars($note) : '<span class="ws-empty">—</span>') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}
?>

<style>
/* ── Grammar page styles (shares the accordion pattern from the Alphabet page) ── */
.grammar-banner { background: linear-gradient(135deg, #5C3A21 0%, #3d2610 100%); }

.acc { display: flex; flex-direction: column; gap: 1.1rem; margin: 1.5rem 0 2.5rem; }

.acc-card {
    background: #fff;
    border: 1px solid #e5e0d5;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(45,27,14,.06);
    overflow: hidden;
    transition: box-shadow .25s;
}
.acc-card.is-open { box-shadow: 0 8px 28px rgba(45,27,14,.12); }

.acc-card-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    color: inherit;
}
.acc-card-header:hover { background: #faf8f5; }
.acc-card-header:focus-visible { outline: none; box-shadow: inset 0 0 0 3px #C8A95199; }
.acc-card-icon {
    flex-shrink: 0;
    width: 46px; height: 46px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    background: #fdf8f0;
}
.acc-card-heading { flex: 1; min-width: 0; }
.acc-card-title { font-size: 1.08rem; font-weight: 700; color: #2d1b0e; margin: 0 0 .2rem; }
.acc-card-desc { font-size: .82rem; color: #7a6a5a; margin: 0; line-height: 1.5; }
.acc-card-count {
    flex-shrink: 0;
    font-size: .72rem;
    font-weight: 700;
    color: #5C3A21;
    background: #fdf8f0;
    border: 1px solid #e5d5b8;
    border-radius: 20px;
    padding: .3rem .7rem;
    white-space: nowrap;
}
.acc-chevron { flex-shrink: 0; width: 22px; height: 22px; color: #9a8a7a; transition: transform .3s ease; }
.acc-card.is-open > .acc-card-header .acc-chevron { transform: rotate(180deg); color: #5C3A21; }

.acc-card-body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .35s ease; }
.acc-card.is-open > .acc-card-body { grid-template-rows: 1fr; }
.acc-card-body-inner { overflow: hidden; min-height: 0; }
.acc-card-body-pad { padding: 0 1.5rem 1.75rem; }

.acc-sub { display: flex; flex-direction: column; gap: .6rem; }
.acc-sub-item { border: 1px solid #e9e3d6; border-radius: 12px; overflow: hidden; background: #fcfbf8; }
.acc-sub-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .85rem 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    color: inherit;
}
.acc-sub-header:hover { background: #f5f0e6; }
.acc-sub-header:focus-visible { outline: none; box-shadow: inset 0 0 0 3px #C8A95199; }
.acc-sub-title-wrap { flex: 1; min-width: 0; }
.acc-sub-title { font-size: .9rem; font-weight: 700; color: #2d1b0e; margin: 0; }
.acc-sub-summary { font-size: .78rem; color: #7a6a5a; margin: .15rem 0 0; }
.acc-sub-badge { flex-shrink: 0; font-size: .68rem; color: #9a8a7a; white-space: nowrap; }
.acc-sub-chev { flex-shrink: 0; width: 16px; height: 16px; color: #9a8a7a; transition: transform .3s ease; }
.acc-sub-item.is-open .acc-sub-chev { transform: rotate(180deg); color: #5C3A21; }
.acc-sub-body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .3s ease; }
.acc-sub-item.is-open .acc-sub-body { grid-template-rows: 1fr; }
.acc-sub-body-inner { overflow: hidden; min-height: 0; }
.acc-sub-body-pad { padding: 0 1.1rem 1.1rem; }

.rule-explanation { font-size: .87rem; color: #3a2a1a; line-height: 1.7; margin: 0 0 1rem; }
.rule-source-note {
    margin-top: .9rem;
    font-size: .76rem;
    color: #8a7a6a;
    font-style: italic;
    border-top: 1px dashed #e5e0d5;
    padding-top: .6rem;
}

.ws-table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid #ece6d8; }
.ws-table { width: 100%; min-width: 380px; border-collapse: collapse; font-size: .8rem; }
.ws-table th, .ws-table td { padding: .5rem .7rem; text-align: left; border-bottom: 1px solid #f0ebe0; }
.ws-table thead th { background: #faf7f2; font-weight: 700; color: #5C3A21; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; }
.ws-table tbody tr:last-child td { border-bottom: none; }
.ws-table tbody tr:hover { background: #fbf9f4; }
.ws-table td:first-child { font-weight: 600; color: #5C3A21; white-space: nowrap; }
.ws-empty { color: #c9beac; }

@media (max-width: 700px) {
    .acc-card-header { padding: 1rem 1.1rem; gap: .7rem; }
    .acc-card-body-pad { padding: 0 1.1rem 1.4rem; }
    .acc-card-icon { width: 38px; height: 38px; font-size: 1.15rem; border-radius: 11px; }
    .acc-card-title { font-size: .96rem; }
    .acc-card-desc { display: none; }
    .acc-card-count { font-size: .66rem; padding: .25rem .55rem; }
}
</style>

<script>
(function () {
    function toggleTopCard(header) {
        var card = header.closest('.acc-card');
        var open = !card.classList.contains('is-open');
        card.classList.toggle('is-open', open);
        header.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function toggleSubItem(header) {
        var item  = header.closest('.acc-sub-item');
        var group = item.closest('.acc-sub');
        var open  = !item.classList.contains('is-open');

        if (group) {
            group.querySelectorAll(':scope > .acc-sub-item.is-open').forEach(function (sibling) {
                if (sibling !== item) {
                    sibling.classList.remove('is-open');
                    var sibHeader = sibling.querySelector(':scope > .acc-sub-header');
                    if (sibHeader) sibHeader.setAttribute('aria-expanded', 'false');
                }
            });
        }

        item.classList.toggle('is-open', open);
        header.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    document.addEventListener('click', function (e) {
        var topHeader = e.target.closest('.acc-card-header');
        if (topHeader) { toggleTopCard(topHeader); return; }

        var subHeader = e.target.closest('.acc-sub-header');
        if (subHeader) { toggleSubItem(subHeader); return; }
    });
})();
</script>

<!-- Breadcrumb -->
<div style="background:#faf8f5;border-bottom:1px solid #e5e0d5;padding:.6rem 0;">
    <div class="container" style="font-size:.83rem;color:#7a6a5a;">
        <a href="<?= url('/') ?>" style="color:#5C3A21;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url('language') ?>" style="color:#5C3A21;text-decoration:none;">Language</a>
        <span style="margin:0 .4rem;">›</span>
        <span>Grammar</span>
    </div>
</div>

<!-- Banner -->
<div class="contribute-banner grammar-banner">
    <div class="container">
        <h1 class="page-banner-title">Tiv Grammar</h1>
        <p class="page-banner-sub">Curated rules for nouns, pronouns, verbs, adjectives, sentence structure, and question formation</p>
        <?php if (is_moderator()): ?>
        <a href="<?= url('admin/grammar') ?>"
           style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.9rem;padding:.45rem .9rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);border-radius:6px;color:#fff;font-size:.8rem;font-weight:600;text-decoration:none;transition:background .15s;"
           onmouseover="this.style.background='rgba(255,255,255,.25)'"
           onmouseout="this.style.background='rgba(255,255,255,.15)'">
            &#9998; Edit Grammar Rules
        </a>
        <?php endif; ?>
    </div>
</div>

<section style="padding:2rem 0 4rem;">
    <div class="container">

        <div class="acc">
            <?php foreach ($categoryMeta as $cat => $meta):
                $rules = $grouped[$cat] ?? []; ?>
            <div class="acc-card" id="acc-<?= $cat ?>">
                <button class="acc-card-header" aria-expanded="false" aria-controls="acc-<?= $cat ?>-body">
                    <span class="acc-card-icon"><?= $meta['icon'] ?></span>
                    <span class="acc-card-heading">
                        <p class="acc-card-title"><?= $meta['title'] ?></p>
                        <p class="acc-card-desc"><?= $meta['desc'] ?></p>
                    </span>
                    <span class="acc-card-count"><?= count($rules) ?> rule<?= count($rules) === 1 ? '' : 's' ?></span>
                    <svg class="acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="acc-card-body" id="acc-<?= $cat ?>-body">
                    <div class="acc-card-body-inner"><div class="acc-card-body-pad">

                        <?php if (empty($rules)): ?>
                        <p style="font-size:.85rem;color:#9a8a7a;">No rules in this category yet.</p>
                        <?php else: ?>
                        <div class="acc-sub">
                            <?php foreach ($rules as $rule):
                                $ruleId = 'acc-sub-' . $cat . '-' . tiv_grammar_slug($rule['title']); ?>
                            <div class="acc-sub-item" id="<?= $ruleId ?>">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="<?= $ruleId ?>-body">
                                    <span class="acc-sub-title-wrap">
                                        <p class="acc-sub-title"><?= htmlspecialchars($rule['title']) ?></p>
                                        <?php if (!empty($rule['summary'])): ?>
                                        <p class="acc-sub-summary"><?= htmlspecialchars($rule['summary']) ?></p>
                                        <?php endif; ?>
                                    </span>
                                    <span class="acc-sub-badge"><?= count($rule['examples']) ?> example<?= count($rule['examples']) === 1 ? '' : 's' ?></span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="<?= $ruleId ?>-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <?php if (!empty($rule['explanation'])): ?>
                                        <p class="rule-explanation"><?= nl2br(htmlspecialchars($rule['explanation'])) ?></p>
                                        <?php endif; ?>
                                        <?php tiv_grammar_examples_table($rule['examples']); ?>
                                        <?php if (!empty($rule['source_note'])): ?>
                                        <p class="rule-source-note"><?= htmlspecialchars($rule['source_note']) ?></p>
                                        <?php endif; ?>
                                    </div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTAs -->
        <div style="margin-top:1rem;padding-top:2rem;border-top:1px solid #e5e0d5;display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="<?= url('language/alphabet') ?>" class="btn btn-primary">&#127279; Tiv Alphabet</a>
            <a href="<?= url(section_path('words')) ?>" class="btn btn-secondary">&#128218; Browse Dictionary</a>
            <a href="<?= url('learn') ?>" class="btn btn-secondary">&#127979; Language Lessons</a>
            <a href="<?= url('translate') ?>" class="btn btn-secondary">&#127760; Translator</a>
        </div>

    </div>
</section>
