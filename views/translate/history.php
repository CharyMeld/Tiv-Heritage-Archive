<section class="archive-section">
    <div class="container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Translation History</h1>
                <p class="page-subtitle">Your recent translations</p>
            </div>
            <a href="<?= url('translate') ?>" class="btn btn-primary">New Translation</a>
        </div>

        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <p>You haven't made any translations yet.</p>
                <a href="<?= url('translate') ?>" class="btn btn-primary">Start Translating</a>
            </div>
        <?php else: ?>

            <div class="history-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Original</th>
                            <th>Translation</th>
                            <th>Direction</th>
                            <th>Confidence</th>
                            <th>Match</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="history-source"><?= e(mb_strimwidth($log['source_text'], 0, 80, '…')) ?></td>
                            <td class="history-result"><?= e(mb_strimwidth($log['translated_text'], 0, 80, '…')) ?></td>
                            <td>
                                <span class="lang-badge">
                                    <?= strtoupper($log['source_language']) ?> → <?= strtoupper($log['target_language']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $conf = (int)$log['confidence_score'];
                                    $cls  = $conf >= 80 ? 'conf-high' : ($conf >= 50 ? 'conf-medium' : 'conf-low');
                                ?>
                                <span class="confidence-badge <?= $cls ?>"><?= $conf ?>%</span>
                            </td>
                            <td>
                                <span class="match-type-badge"><?= e(ucfirst(str_replace('_', ' ', $log['match_type']))) ?></span>
                            </td>
                            <td class="history-date"><?= date('M j, Y', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($paging['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($paging['has_prev']): ?>
                    <a href="?page=<?= $paging['current_page'] - 1 ?>" class="page-btn">&laquo; Previous</a>
                <?php endif; ?>
                <span class="page-info">Page <?= $paging['current_page'] ?> of <?= $paging['total_pages'] ?></span>
                <?php if ($paging['has_next']): ?>
                    <a href="?page=<?= $paging['current_page'] + 1 ?>" class="page-btn">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<style>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.history-table-wrap { overflow-x: auto; }
.history-source, .history-result { max-width: 260px; word-break: break-word; }
.history-date { white-space: nowrap; font-size: .85rem; color: #888; }
.lang-badge { background: #f0f0f0; padding: .2em .5em; border-radius: 4px; font-size: .78rem; font-weight: 600; }
.confidence-badge { font-size: .72rem; padding: .25em .6em; border-radius: 12px; font-weight: 600; }
.conf-high { background: #e8f5e9; color: #2e7d32; }
.conf-medium { background: #fff8e1; color: #f57f17; }
.conf-low { background: #fce4ec; color: #c62828; }
.match-type-badge { font-size: .72rem; background: #f0f0f0; color: #555; padding: .25em .6em; border-radius: 12px; }
.empty-state { text-align: center; padding: 4rem 1rem; color: #888; }
.empty-state p { margin-bottom: 1rem; }
</style>
