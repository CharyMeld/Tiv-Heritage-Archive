<!-- Page Banner -->
<div class="page-banner">
    <div class="container">
        <a href="<?= url('profile') ?>" class="detail-banner-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Profile
        </a>
        <h1 class="page-banner-title">My Submissions</h1>
        <p class="page-banner-sub">All your contributions to the archive</p>
    </div>
</div>

<div class="detail-body-wrap">
    <div class="container">
        <?php if (empty($submissions)): ?>
        <div style="text-align: center; padding: 4rem 1rem;">
            <p style="font-size: 3rem; margin-bottom: 1rem;">&#128196;</p>
            <h3 style="font-family: var(--font-heading); color: var(--color-primary); margin-bottom: 0.5rem;">No submissions yet</h3>
            <p style="color: var(--color-text-muted); margin-bottom: 2rem;">You haven't submitted any contributions yet.</p>
            <a href="<?= url('contribute') ?>" class="btn btn-primary">Submit Your First Contribution</a>
        </div>
        <?php else: ?>

        <div class="admin-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Tiv Term</th>
                        <th>English Meaning</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td>
                            <span style="font-family: var(--font-ui); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted);">
                                <?= ucfirst(e($submission['category'])) ?>
                            </span>
                        </td>
                        <td><strong><?= e($submission['tiv_term']) ?></strong></td>
                        <td style="color: var(--color-text-muted);"><?= e($submission['english_meaning']) ?></td>
                        <td>
                            <span class="badge badge-<?= e($submission['status']) ?>">
                                <?= ucfirst(e($submission['status'])) ?>
                            </span>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--color-text-muted);">
                            <?= date('M j, Y', strtotime($submission['created_at'])) ?>
                        </td>
                    </tr>
                    <?php if ($submission['reviewer_notes']): ?>
                    <tr>
                        <td colspan="5" style="background: rgba(92,58,33,0.04); padding: 0.6rem 1rem; font-size: 0.85rem; border-top: none;">
                            <strong style="color: var(--color-primary);">Reviewer notes:</strong>
                            <span style="color: var(--color-text-muted);"><?= e($submission['reviewer_notes']) ?></span>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= pagination($pagination, url('profile/submissions')) ?>

        <?php endif; ?>

        <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?= url('profile') ?>" class="btn btn-secondary">&larr; Back to Profile</a>
            <a href="<?= url('contribute') ?>" class="btn btn-primary">Submit New Contribution</a>
        </div>
    </div>
</div>
