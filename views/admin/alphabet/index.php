<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#2d1b0e;margin:0;">&#127279; Alphabet Manager</h1>
            <p style="font-size:.83rem;color:#7a6a5a;margin:.25rem 0 0;">Plain Letters · Vowels · Consonants · Digraphs · Tonal — edit entries and upload audio pronunciations</p>
        </div>
        <a href="<?= url('admin/alphabet/create') ?>" class="btn btn-primary" style="white-space:nowrap;">+ Add Entry</a>
    </div>

    <!-- Summary chips -->
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <span style="padding:.35rem .85rem;border-radius:20px;font-size:.8rem;font-weight:600;background:#f3ede6;color:#7a4a2a;">Plain Letters <strong><?= $counts['plain'] ?></strong></span>
        <span style="padding:.35rem .85rem;border-radius:20px;font-size:.8rem;font-weight:600;background:#eff4ff;color:#2d5a9e;">Vowels <strong><?= $counts['vowel'] ?></strong></span>
        <span style="padding:.35rem .85rem;border-radius:20px;font-size:.8rem;font-weight:600;background:#fdf8f0;color:#5C3A21;">Consonants <strong><?= $counts['consonant'] ?></strong></span>
        <span style="padding:.35rem .85rem;border-radius:20px;font-size:.8rem;font-weight:600;background:#edf7f0;color:#4a7c59;">Digraphs <strong><?= $counts['digraph'] ?></strong></span>
        <span style="padding:.35rem .85rem;border-radius:20px;font-size:.8rem;font-weight:600;background:#fdf4f8;color:#8b3a62;">Tonal <strong><?= $counts['tonal'] ?></strong></span>
    </div>

    <!-- ══ PLAIN LETTERS ═══════════════════════════════════════════════════ -->
    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div class="admin-card-header" style="border-left:4px solid #7a4a2a;">
            <h2 class="admin-card-title" style="color:#7a4a2a;">
                Plain Letters
                <span style="font-weight:400;font-size:.8rem;color:#7a6a5a;margin-left:.4rem;">(<?= count($grouped['plain']) ?>)</span>
            </h2>
            <a href="<?= url('admin/alphabet/create?type=plain') ?>"
               style="font-size:.82rem;color:#7a4a2a;text-decoration:none;font-weight:600;">+ Add</a>
        </div>

        <?php if (empty($grouped['plain'])): ?>
        <div class="admin-card-body" style="color:#9a8a7a;font-size:.85rem;padding:1rem 1.2rem;">No plain letters yet. <a href="<?= url('admin/alphabet/create?type=plain') ?>" style="color:#7a4a2a;font-weight:600;">Add the first one</a>.</div>
        <?php else: ?>
        <div class="admin-card-body" style="padding:1rem 1.2rem;">
            <!-- Grid of letter pairs -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.6rem;margin-bottom:1rem;">
            <?php foreach ($grouped['plain'] as $e): ?>
                <div style="border:1px solid #e8e0d5;border-radius:8px;padding:.6rem .75rem;background:#fdfaf7;display:flex;flex-direction:column;gap:.25rem;">
                    <div style="display:flex;align-items:baseline;gap:.4rem;justify-content:center;">
                        <span style="font-size:1.7rem;font-weight:800;color:#7a4a2a;line-height:1;"><?= htmlspecialchars($e['letter']) ?></span>
                        <?php if ($e['english_letter']): ?>
                        <span style="font-size:.72rem;color:#9a8a7a;">/ <?= htmlspecialchars($e['english_letter']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;gap:.4rem;justify-content:center;margin-top:.2rem;">
                        <a href="<?= url('admin/alphabet/' . $e['id'] . '/edit') ?>"
                           style="font-size:.73rem;color:#5C3A21;font-weight:600;text-decoration:none;">Edit</a>
                        <span style="color:#ccc;font-size:.73rem;">|</span>
                        <form method="POST" action="<?= url('admin/alphabet/' . $e['id'] . '/delete') ?>"
                              style="display:inline;"
                              onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($e['letter'])) ?>?')">
                            <?= csrf_field() ?>
                            <button type="submit" style="background:none;border:none;font-size:.73rem;color:#c0392b;cursor:pointer;font-weight:600;padding:0;">Del</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php
    $sections = [
        'vowel'     => ['label'=>'Vowels',      'color'=>'#2d5a9e', 'bg'=>'#eff4ff'],
        'consonant' => ['label'=>'Consonants',  'color'=>'#5C3A21', 'bg'=>'#fdf8f0'],
        'digraph'   => ['label'=>'Digraphs',    'color'=>'#4a7c59', 'bg'=>'#edf7f0'],
        'tonal'     => ['label'=>'Tonal Marks', 'color'=>'#8b3a62', 'bg'=>'#fdf4f8'],
    ];
    foreach ($sections as $type => $meta):
        $entries = $grouped[$type];
    ?>
    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div class="admin-card-header" style="border-left:4px solid <?= $meta['color'] ?>;">
            <h2 class="admin-card-title" style="color:<?= $meta['color'] ?>;">
                <?= $meta['label'] ?>
                <span style="font-weight:400;font-size:.8rem;color:#7a6a5a;margin-left:.4rem;">(<?= count($entries) ?>)</span>
            </h2>
            <a href="<?= url('admin/alphabet/create?type=' . $type) ?>"
               style="font-size:.82rem;color:<?= $meta['color'] ?>;text-decoration:none;font-weight:600;">+ Add</a>
        </div>

        <?php if (empty($entries)): ?>
        <div class="admin-card-body" style="color:#9a8a7a;font-size:.85rem;padding:1rem 1.2rem;">No entries yet.</div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                <thead>
                    <tr style="background:#f7f4ee;text-align:left;">
                        <th style="padding:.6rem 1rem;font-weight:600;color:#5a4a3a;width:90px;">Letter</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;width:80px;">IPA</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;">Sounds Like</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;width:100px;">Tiv Word</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;">English</th>
                        <th style="padding:.6rem .75rem;font-weight:600;color:#5a4a3a;text-align:center;width:70px;">Audio</th>
                        <th style="padding:.6rem 1rem;font-weight:600;color:#5a4a3a;text-align:right;width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $i => $e): ?>
                    <tr style="border-top:1px solid #f0ece5;<?= $i%2 ? 'background:#fdfcfb;' : '' ?>">
                        <td style="padding:.6rem 1rem;">
                            <span style="font-size:1.2rem;font-weight:800;color:<?= $meta['color'] ?>;"><?= htmlspecialchars($e['letter']) ?></span>
                        </td>
                        <td style="padding:.6rem .75rem;font-family:monospace;color:#7a6a5a;font-size:.79rem;"><?= htmlspecialchars($e['ipa']) ?></td>
                        <td style="padding:.6rem .75rem;color:#5a4a3a;"><?= htmlspecialchars($e['sound_desc']) ?></td>
                        <td style="padding:.6rem .75rem;"><strong style="color:<?= $meta['color'] ?>;"><?= htmlspecialchars($e['tiv_example']) ?></strong></td>
                        <td style="padding:.6rem .75rem;color:#7a6a5a;font-style:italic;">"<?= htmlspecialchars($e['english_meaning']) ?>"</td>
                        <td style="padding:.6rem .75rem;text-align:center;">
                            <?php if (!empty($e['audio_file'])): ?>
                                <audio controls style="height:28px;width:90px;display:block;margin:0 auto;">
                                    <source src="<?= UPLOADS_URL . '/' . htmlspecialchars($e['audio_file']) ?>">
                                </audio>
                            <?php else: ?>
                                <span style="color:#ccc;font-size:.75rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">
                            <a href="<?= url('admin/alphabet/' . $e['id'] . '/edit') ?>"
                               style="font-size:.8rem;color:#5C3A21;font-weight:600;text-decoration:none;margin-right:.6rem;">Edit</a>
                            <form method="POST" action="<?= url('admin/alphabet/' . $e['id'] . '/delete') ?>"
                                  style="display:inline;"
                                  onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($e['letter'])) ?>?')">
                                <?= csrf_field() ?>
                                <button type="submit" style="background:none;border:none;font-size:.8rem;color:#c0392b;cursor:pointer;font-weight:600;padding:0;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:1rem;padding:1rem;background:#f7f4ee;border-radius:8px;font-size:.82rem;color:#5a4a3a;line-height:1.65;">
        <strong>Plain Letters</strong> appear above all other sections on the public alphabet page — use them for the basic Tiv–English letter pairing (A a / A a, B b / B b, etc.).<br>
        <strong>Audio:</strong> Upload an MP3/OGG/WAV file per letter to let learners hear the correct pronunciation on the public alphabet page.
    </div>

</div>
</div>
