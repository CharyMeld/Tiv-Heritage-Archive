<?php
$photo = !empty($member['profile_photo'])
    ? UPLOADS_URL . '/' . htmlspecialchars($member['profile_photo'])
    : null;

$initials = strtoupper(substr($member['full_name'], 0, 1));
$badge    = $member['member_type'] === 'researcher' ? '#4a7c59' : '#5C3A21';
$label    = ucfirst($member['member_type']);
?>

<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128101; Community</span>
        <h1 class="page-banner-title"><?= htmlspecialchars($member['full_name']) ?></h1>
        <p class="page-banner-sub"><?= $label ?> &middot; Tiv Heritage Archive</p>
    </div>
</div>

<section style="padding: 2rem 0 3rem;">
    <div class="container">
        <a href="<?= url('community') ?>" style="display:inline-block;margin-bottom:1.5rem;color:#5C3A21;font-size:.9rem;">&#8592; Back to Community Directory</a>

        <div style="display:flex;gap:2rem;flex-wrap:wrap;">
            <div style="flex:0 0 220px;">
                <div style="width:220px;height:220px;border-radius:12px;overflow:hidden;background:#f7f4ee;display:flex;align-items:center;justify-content:center;border:1px solid #e5e0d5;">
                    <?php if ($photo): ?>
                        <img src="<?= $photo ?>" alt="<?= htmlspecialchars($member['full_name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:96px;height:96px;border-radius:50%;background:#5C3A21;color:#fff;font-size:2.5rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $initials ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($member['is_featured'])): ?>
                    <p style="margin:.75rem 0 0;color:#C8A951;font-weight:600;font-size:.85rem;">&#11088; Featured Member</p>
                <?php endif; ?>
            </div>

            <div style="flex:1;min-width:280px;">
                <span style="display:inline-block;color:#fff;background:<?= $badge ?>;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;padding:.2rem .7rem;border-radius:20px;margin-bottom:.75rem;"><?= $label ?></span>

                <?php if (!empty($member['short_bio'])): ?>
                    <p style="color:#5a4a3a;line-height:1.6;margin:0 0 1.25rem;"><?= nl2br(htmlspecialchars($member['short_bio'])) ?></p>
                <?php endif; ?>

                <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.5rem;">
                    <?php if (!empty($member['area_of_interest'])): ?>
                        <span style="color:#7a6a5a;font-size:.9rem;">&#127919; <?= htmlspecialchars($member['area_of_interest']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($member['location'])): ?>
                        <span style="color:#7a6a5a;font-size:.9rem;">&#128205; <?= htmlspecialchars($member['location']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($member['date_joined'])): ?>
                        <span style="color:#7a6a5a;font-size:.9rem;">&#128197; Joined <?= date('F Y', strtotime($member['date_joined'])) ?></span>
                    <?php endif; ?>
                </div>

                <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">
                    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:.75rem 1.25rem;text-align:center;">
                        <div style="font-size:1.4rem;font-weight:700;color:#5C3A21;"><?= number_format($member['contributions_count'] ?? 0) ?></div>
                        <div style="color:#6b5a4a;font-size:.8rem;">Contributions</div>
                    </div>
                    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:.75rem 1.25rem;text-align:center;">
                        <div style="font-size:1.4rem;font-weight:700;color:#5C3A21;"><?= number_format($member['research_count'] ?? 0) ?></div>
                        <div style="color:#6b5a4a;font-size:.8rem;">Research</div>
                    </div>
                    <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:.75rem 1.25rem;text-align:center;">
                        <div style="font-size:1.4rem;font-weight:700;color:#5C3A21;"><?= number_format($member['suggestions_count'] ?? 0) ?></div>
                        <div style="color:#6b5a4a;font-size:.8rem;">Suggestions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
