<?php
/**
 * Partial: Team Section
 * Requires: $teamMembers array
 */
if (empty($teamMembers)) return;

$catLabels = ['developer' => 'Developers', 'researcher' => 'Researchers', 'contributor' => 'Contributors'];
$cats      = array_unique(array_column($teamMembers, 'category'));
?>

<section class="team-section" id="team">
    <div class="container">

        <div class="team-section-header">
            <h2>Meet the Team</h2>
            <p>The people preserving Tiv heritage for generations to come</p>
        </div>

        <!-- Filters -->
        <div class="team-filters">
            <button class="team-filter-btn active" data-filter="all">All</button>
            <?php foreach ($cats as $cat): ?>
            <button class="team-filter-btn" data-filter="<?= e($cat) ?>">
                <?= e($catLabels[$cat] ?? ucfirst($cat)) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Cards -->
        <div class="team-grid">
        <?php foreach ($teamMembers as $tm):
            $tmSocials = TeamMember::decodeSocials($tm['socials'] ?? null);
            $tmImg     = !empty($tm['image'])
                ? UPLOADS_URL . '/team/' . e($tm['image'])
                : 'https://ui-avatars.com/api/?name=' . urlencode($tm['name']) . '&background=5C3A21&color=fff&size=400';
        ?>
        <div class="team-card"
             data-category="<?= e($tm['category']) ?>"
             data-name="<?= e($tm['name']) ?>"
             data-role="<?= e($tm['role']) ?>"
             data-bio="<?= e($tm['full_bio'] ?? $tm['short_bio'] ?? '') ?>"
             data-socials="<?= e(json_encode($tmSocials)) ?>"
             data-img="<?= $tmImg ?>">
            <div class="team-card-bg" style="background-image:url('<?= $tmImg ?>');"></div>
            <div class="team-card-overlay"></div>
            <div class="team-card-body">
                <div class="team-card-name"><?= e($tm['name']) ?></div>
                <div class="team-card-role"><?= e($tm['role']) ?></div>
                <div class="team-card-bio"><?= e($tm['short_bio'] ?? '') ?></div>
                <button class="team-card-btn">View Profile</button>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Team Modal -->
<div class="team-modal-overlay" id="tcModal" role="dialog" aria-modal="true">
    <div class="team-modal-box">
        <button class="team-modal-close" id="tcModalClose" aria-label="Close">&times;</button>
        <img class="team-modal-img" id="tcModalImg" src="" alt="">
        <div class="team-modal-body">
            <div class="team-modal-name" id="tcModalName"></div>
            <div class="team-modal-role" id="tcModalRole"></div>
            <div class="team-modal-bio"  id="tcModalBio"></div>
            <div class="team-modal-socials" id="tcModalSocials"></div>
        </div>
    </div>
</div>

<script>
(function(){
    var PLATFORM_LABELS = {
        linkedin:'LinkedIn', twitter:'Twitter / X', facebook:'Facebook',
        instagram:'Instagram', tiktok:'TikTok', whatsapp:'WhatsApp',
        youtube:'YouTube', github:'GitHub', telegram:'Telegram', website:'Website'
    };

    /* Scroll fade-in */
    var cards = document.querySelectorAll('.team-card');
    var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e, i){
            if(e.isIntersecting){
                setTimeout(function(){ e.target.classList.add('tc-visible'); }, i * 110);
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    cards.forEach(function(c){ io.observe(c); });

    /* Filter */
    document.querySelectorAll('.team-filter-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.team-filter-btn').forEach(function(b){ b.classList.remove('active'); });
            this.classList.add('active');
            var f = this.dataset.filter;
            cards.forEach(function(c){
                c.style.display = (f === 'all' || c.dataset.category === f) ? '' : 'none';
            });
        });
    });

    /* Modal */
    var overlay  = document.getElementById('tcModal');
    var closeBtn = document.getElementById('tcModalClose');

    function openModal(card){
        var d = card.dataset;

        document.getElementById('tcModalName').textContent = d.name || '';
        document.getElementById('tcModalRole').textContent = d.role || '';
        document.getElementById('tcModalBio').textContent  = d.bio  || '';

        /* Photo */
        var img = document.getElementById('tcModalImg');
        img.src = d.img || ''; img.alt = d.name || '';

        /* Social links */
        var socialsEl = document.getElementById('tcModalSocials');
        socialsEl.innerHTML = '';
        var socialsData = {};
        try { socialsData = JSON.parse(d.socials || '{}'); } catch(err){}
        Object.keys(socialsData).forEach(function(platform){
            var url = socialsData[platform];
            if (!url) return;
            var a = document.createElement('a');
            a.href = url; a.target = '_blank'; a.rel = 'noopener noreferrer';
            a.textContent = PLATFORM_LABELS[platform] || platform;
            socialsEl.appendChild(a);
        });

        overlay.classList.add('tc-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(){
        overlay.classList.remove('tc-open');
        document.body.style.overflow = '';
    }

    cards.forEach(function(c){ c.addEventListener('click', function(){ openModal(c); }); });
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e){ if(e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeModal(); });
})();
</script>
