<div class="contribute-banner">
    <div class="container">
        <span class="page-banner-eyebrow">&#128101; Community</span>
        <h1 class="page-banner-title">Community Directory</h1>
        <p class="page-banner-sub">Meet the contributors and researchers preserving Tiv heritage</p>
    </div>
</div>

<section style="padding: 2rem 0 3rem;">
    <div class="container">

        <!-- Stats bar -->
        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:2rem;">
            <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1rem 1.5rem;flex:1;min-width:160px;text-align:center;">
                <div style="font-size:2rem;font-weight:700;color:#5C3A21;"><?= number_format($stats['total'] ?? 0) ?></div>
                <div style="color:#6b5a4a;font-size:.9rem;">Total Members</div>
            </div>
            <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1rem 1.5rem;flex:1;min-width:160px;text-align:center;">
                <div style="font-size:2rem;font-weight:700;color:#5C3A21;"><?= number_format($stats['contributors'] ?? 0) ?></div>
                <div style="color:#6b5a4a;font-size:.9rem;">Contributors</div>
            </div>
            <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1rem 1.5rem;flex:1;min-width:160px;text-align:center;">
                <div style="font-size:2rem;font-weight:700;color:#5C3A21;"><?= number_format($stats['researchers'] ?? 0) ?></div>
                <div style="color:#6b5a4a;font-size:.9rem;">Researchers</div>
            </div>
            <div style="flex:1;min-width:160px;display:flex;align-items:center;justify-content:center;">
                <a href="<?= url('community/join') ?>" class="btn btn-primary" style="white-space:nowrap;">
                    &#43; Join the Community
                </a>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div style="display:flex;gap:.5rem;border-bottom:2px solid #e5e0d5;margin-bottom:2rem;">
            <button class="community-tab active" data-tab="contributors" onclick="switchTab('contributors',this)">
                Contributors (<?= count($contributors) ?>)
            </button>
            <button class="community-tab" data-tab="researchers" onclick="switchTab('researchers',this)">
                Researchers (<?= count($researchers) ?>)
            </button>
        </div>

        <!-- Contributors Tab -->
        <div id="tab-contributors" class="community-tab-content">
            <?php if (empty($contributors)): ?>
                <div class="archive-empty-state">
                    <p>No contributors yet. <a href="<?= url('community/join') ?>">Be the first!</a></p>
                </div>
            <?php else: ?>
                <div class="community-grid">
                    <?php foreach ($contributors as $member): ?>
                        <?= include_member_card($member) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Researchers Tab -->
        <div id="tab-researchers" class="community-tab-content" style="display:none;">
            <?php if (empty($researchers)): ?>
                <div class="archive-empty-state">
                    <p>No researchers yet. <a href="<?= url('community/join') ?>">Apply to join!</a></p>
                </div>
            <?php else: ?>
                <div class="community-grid">
                    <?php foreach ($researchers as $member): ?>
                        <?= include_member_card($member) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php
function include_member_card(array $m): string {
    $photo = !empty($m['profile_photo'])
        ? UPLOADS_URL . '/' . htmlspecialchars($m['profile_photo'])
        : null;

    $initials = strtoupper(substr($m['full_name'], 0, 1));
    $badge    = $m['member_type'] === 'researcher' ? '#4a7c59' : '#5C3A21';
    $label    = ucfirst($m['member_type']);

    ob_start();
    ?>
    <a class="community-card<?= $m['is_featured'] ? ' community-card--featured' : '' ?>" href="<?= url('community/member/' . $m['id']) ?>">
        <div class="community-card-photo">
            <?php if ($photo): ?>
                <img src="<?= $photo ?>" alt="<?= htmlspecialchars($m['full_name']) ?>" loading="lazy">
            <?php else: ?>
                <div class="community-card-initials"><?= $initials ?></div>
            <?php endif; ?>
            <?php if ($m['is_featured']): ?>
                <span class="community-featured-badge" title="Featured Member">&#11088;</span>
            <?php endif; ?>
        </div>
        <div class="community-card-body">
            <h3 class="community-card-name"><?= htmlspecialchars($m['full_name']) ?></h3>
            <span class="community-card-type" style="background:<?= $badge ?>;"><?= $label ?></span>
            <?php if (!empty($m['short_bio'])): ?>
                <p class="community-card-bio"><?= htmlspecialchars(mb_substr($m['short_bio'], 0, 120)) ?><?= mb_strlen($m['short_bio']) > 120 ? '…' : '' ?></p>
            <?php endif; ?>
            <div class="community-card-meta">
                <?php if (!empty($m['area_of_interest'])): ?>
                    <span>&#127919; <?= htmlspecialchars($m['area_of_interest']) ?></span>
                <?php endif; ?>
                <?php if (!empty($m['location'])): ?>
                    <span>&#128205; <?= htmlspecialchars($m['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($m['date_joined'])): ?>
                    <span>&#128197; Joined <?= date('M Y', strtotime($m['date_joined'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <?php
    return ob_get_clean();
}
?>

<style>
.community-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.5rem;
}
.community-card {
    display: block;
    background: #fff;
    border: 1px solid #e5e0d5;
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
    color: inherit;
    text-decoration: none;
}
.community-card:hover {
    box-shadow: 0 4px 20px rgba(92,58,33,.12);
    transform: translateY(-2px);
}
.community-card--featured {
    border-color: #C8A951;
    box-shadow: 0 0 0 2px rgba(200,169,81,.25);
}
.community-card-photo {
    position: relative;
    height: 180px;
    background: #f7f4ee;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.community-card-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.community-card-initials {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #5C3A21;
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
.community-featured-badge {
    position: absolute;
    top: .5rem;
    right: .5rem;
    font-size: 1.2rem;
}
.community-card-body {
    padding: 1rem;
}
.community-card-name {
    font-size: 1.05rem;
    font-weight: 600;
    color: #2d1b0e;
    margin: 0 0 .3rem;
}
.community-card-type {
    display: inline-block;
    color: #fff;
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: .15rem .6rem;
    border-radius: 20px;
    margin-bottom: .5rem;
}
.community-card-bio {
    font-size: .85rem;
    color: #5a4a3a;
    line-height: 1.5;
    margin: .4rem 0 .6rem;
}
.community-card-meta {
    display: flex;
    flex-direction: column;
    gap: .2rem;
}
.community-card-meta span {
    font-size: .78rem;
    color: #7a6a5a;
}
.community-tab {
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    padding: .6rem 1.2rem;
    font-size: .95rem;
    cursor: pointer;
    color: #5a4a3a;
    font-weight: 500;
    transition: color .2s, border-color .2s;
    margin-bottom: -2px;
}
.community-tab.active {
    color: #5C3A21;
    border-bottom-color: #5C3A21;
    font-weight: 700;
}
</style>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.community-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.community-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = 'block';
    btn.classList.add('active');
}
</script>
