<?php
$categoryMeta = [
    'noun'                => ['label' => 'Nouns',              'color' => '#7a4a2a', 'bg' => '#f3ede6'],
    'pronoun'              => ['label' => 'Pronouns',           'color' => '#2d5a9e', 'bg' => '#eff4ff'],
    'verb'                 => ['label' => 'Verbs',              'color' => '#5C3A21', 'bg' => '#fdf8f0'],
    'adjective'            => ['label' => 'Adjectives',         'color' => '#4a7c59', 'bg' => '#edf7f0'],
    'sentence_structure'   => ['label' => 'Sentence Structure', 'color' => '#8b3a62', 'bg' => '#fdf4f8'],
    'question_formation'   => ['label' => 'Question Formation', 'color' => '#C8A951', 'bg' => '#fbf6e8'],
];
?>
<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#2d1b0e;margin:0;">&#128220; Grammar Manager</h1>
            <p style="font-size:.83rem;color:#7a6a5a;margin:.25rem 0 0;">Nouns · Pronouns · Verbs · Adjectives · Sentence Structure · Question Formation — curated rules with worked Tiv/English examples</p>
        </div>
        <a href="<?= url('admin/grammar/create') ?>" class="btn btn-primary" style="white-space:nowrap;">+ Add Rule</a>
    </div>

    <!-- Summary chips -->
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <?php foreach ($categoryMeta as $cat => $meta): ?>
        <span style="padding:.35rem .85rem;border-radius:20px;font-size:.8rem;font-weight:600;background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>;"><?= $meta['label'] ?> <strong><?= $counts[$cat] ?></strong></span>
        <?php endforeach; ?>
    </div>

    <?php foreach ($categoryMeta as $cat => $meta):
        $rules = $grouped[$cat]; ?>
    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div class="admin-card-header" style="border-left:4px solid <?= $meta['color'] ?>;">
            <h2 class="admin-card-title" style="color:<?= $meta['color'] ?>;">
                <?= $meta['label'] ?>
                <span style="font-weight:400;font-size:.8rem;color:#7a6a5a;margin-left:.4rem;">(<?= count($rules) ?>)</span>
            </h2>
            <a href="<?= url('admin/grammar/create?category=' . $cat) ?>"
               style="font-size:.82rem;color:<?= $meta['color'] ?>;text-decoration:none;font-weight:600;">+ Add</a>
        </div>

        <?php if (empty($rules)): ?>
        <div class="admin-card-body" style="color:#9a8a7a;font-size:.85rem;padding:1rem 1.2rem;">No rules yet.</div>
        <?php else: ?>
        <div class="admin-card-body" style="padding:0;">
            <?php foreach ($rules as $i => $r): ?>
            <div style="padding:.9rem 1.2rem;<?= $i > 0 ? 'border-top:1px solid #f0ece5;' : '' ?>display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;">
                <div style="min-width:0;">
                    <p style="margin:0 0 .25rem;font-weight:700;color:#2d1b0e;font-size:.92rem;"><?= htmlspecialchars($r['title']) ?></p>
                    <p style="margin:0;font-size:.8rem;color:#7a6a5a;"><?= htmlspecialchars($r['summary']) ?></p>
                    <p style="margin:.35rem 0 0;font-size:.74rem;color:#9a8a7a;"><?= count($r['examples']) ?> example<?= count($r['examples']) === 1 ? '' : 's' ?> · sort <?= (int) $r['sort_order'] ?></p>
                </div>
                <div style="flex-shrink:0;white-space:nowrap;">
                    <a href="<?= url('admin/grammar/' . $r['id'] . '/edit') ?>"
                       style="font-size:.8rem;color:#5C3A21;font-weight:600;text-decoration:none;margin-right:.6rem;">Edit</a>
                    <form method="POST" action="<?= url('admin/grammar/' . $r['id'] . '/delete') ?>"
                          style="display:inline;"
                          onsubmit="return confirm('Delete &quot;<?= htmlspecialchars(addslashes($r['title'])) ?>&quot;?')">
                        <?= csrf_field() ?>
                        <button type="submit" style="background:none;border:none;font-size:.8rem;color:#c0392b;cursor:pointer;font-weight:600;padding:0;">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:1rem;padding:1rem;background:#f7f4ee;border-radius:8px;font-size:.82rem;color:#5a4a3a;line-height:1.65;">
        Grammar rules power the public <a href="<?= url('language/grammar') ?>" style="color:#5C3A21;font-weight:600;">Language &rsaquo; Grammar</a> page. Each rule's examples are stored as JSON (Tiv/English/note triples) and rendered as a table on the public page.
    </div>

</div>
</div>
