<?php
/**
 * bin/backfill-embeddings.php — embed every archive_search_index row that doesn't
 * have a vector yet, in batches, logging progress. Safe to re-run any time (only
 * touches rows where embedding IS NULL) — e.g. after adding new content, or to
 * catch rows ContentIndexer's best-effort inline embedding missed.
 *
 * Usage: php bin/backfill-embeddings.php [batch_size]
 */
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/EmbeddingSearch.php';

$batchSize = isset($argv[1]) ? (int) $argv[1] : 200;

$db = Database::getInstance();
$es = new EmbeddingSearch($db);

$totalOk = 0;
$totalFailed = 0;
$start = microtime(true);

while (true) {
    $remaining = (int) $db->query("SELECT COUNT(*) FROM archive_search_index WHERE embedding IS NULL")->fetchColumn();
    if ($remaining === 0) {
        break;
    }

    [$ok, $failed] = $es->backfillMissing($batchSize);
    $totalOk += $ok;
    $totalFailed += $failed;

    if ($ok === 0 && $failed === 0) {
        // Nothing processed this pass despite remaining > 0 — avoid an infinite loop.
        break;
    }

    $elapsed = microtime(true) - $start;
    printf(
        "[%s] batch: ok=%d failed=%d | totals: ok=%d failed=%d | %d remaining | %.1fs elapsed\n",
        date('H:i:s'), $ok, $failed, $totalOk, $totalFailed, max(0, $remaining - $ok - $failed), $elapsed
    );
    flush();
}

printf("Done. Embedded %d rows, %d failed, in %.1fs.\n", $totalOk, $totalFailed, microtime(true) - $start);
