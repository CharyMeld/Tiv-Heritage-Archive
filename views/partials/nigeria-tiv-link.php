<?php
/**
 * Partial: link from a Tiv Heritage collection page to the national Tiv records
 * (Nigeria Heritage). Shows nothing until those records are published.
 */
require_once BASE_PATH . '/services/HeritagePublic.php';
$tivNational = HeritagePublic::tivNationalRecords();
?>
<?php if (!empty($tivNational['ethnic_groups'])): ?>
<div class="detail-section-modern">
    <span class="detail-section-label">&#127475;&#127468; Tiv in Nigeria Heritage</span>
    <p>See the <a href="<?= e($tivNational['ethnic_groups']['url']) ?>">Tiv people</a><?php if (!empty($tivNational['languages'])): ?>
        and the <a href="<?= e($tivNational['languages']['url']) ?>">Tiv language</a><?php endif; ?>
        in our Nigeria Heritage Archive: the states and local government areas where Tiv communities live, neighbouring peoples, population figures and sources.</p>
</div>
<?php endif; ?>
