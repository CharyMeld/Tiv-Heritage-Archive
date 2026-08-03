<?php
/**
 * Seeds 100 numbered Tiv/English proverb pairs from
 * "TIV PROVERBS.docx" and "ENGLIS PROVERBS.docx"
 * (~/Desktop/Tiv-Archive-Learning-Videos/), matched by their shared
 * numbering (1-100).
 *
 * The source data is pre-extracted (via python-docx) into
 * database/seeds/data/proverb_pairs_1_100.json. Two entries required
 * a positional correction against a numbering typo in the Tiv
 * document (see notes on #59 and #78 in that file) — both are
 * flagged there rather than silently guessed.
 *
 * The Tiv-language explanatory paragraph that follows each numbered
 * proverb in the source document is preserved in `deeper_meaning`
 * (left in Tiv, not translated, to avoid introducing translation
 * errors beyond what was directly asked for).
 *
 * Checked against the 132 pre-existing tiv_proverbs rows for
 * near-exact duplicates (normalized text match) before writing
 * this script — none found.
 *
 * Idempotent — skips any pair whose english_translation already
 * exists (exact match) in tiv_proverbs.
 *
 * Note: tiv_proverbs has no draft/published workflow (unlike
 * historical_figures/timeline_events) — rows are live immediately.
 *
 * Run: php database/seeds/seed_proverbs_from_docx.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/Source.php';

$proverbModel = new TivProverb();
$sourceModel  = new Source();
$db           = Database::getInstance();

$dataFile = __DIR__ . '/data/proverb_pairs_1_100.json';
$pairs = json_decode(file_get_contents($dataFile), true);
if (!is_array($pairs)) {
    fwrite(STDERR, "Could not read/parse {$dataFile}\n");
    exit(1);
}

// Find-or-create a single source record for this submission.
$sourceTitle = 'Tiv Proverbs Collection (TIV PROVERBS.docx / ENGLIS PROVERBS.docx)';
$stmt = $db->prepare('SELECT id FROM sources WHERE title = ? LIMIT 1');
$stmt->execute([$sourceTitle]);
$row = $stmt->fetch();
if ($row) {
    $sourceId = (int) $row['id'];
} else {
    $sourceId = $sourceModel->create([
        'source_type' => 'community_submission',
        'title' => $sourceTitle,
        'notes' => 'Two paired documents (numbered 1-100, Tiv and English) supplied directly for import into the archive.',
    ]);
    echo "Created source -> id {$sourceId}\n";
}

$inserted = 0;
$skipped  = 0;

foreach ($pairs as $p) {
    $stmt = $db->prepare('SELECT id FROM tiv_proverbs WHERE english_translation = ? LIMIT 1');
    $stmt->execute([$p['english_translation']]);
    if ($stmt->fetch()) {
        echo "Skipping #{$p['num']} (already exists): {$p['english_translation']}\n";
        $skipped++;
        continue;
    }

    $id = $proverbModel->create([
        'tiv_text' => $p['tiv_text'],
        'english_translation' => $p['english_translation'],
        'deeper_meaning' => $p['deeper_meaning'] ?? null,
        'source_id' => $sourceId,
    ]);

    $note = !empty($p['corrected']) ? " [{$p['corrected']}]" : '';
    echo "Inserted #{$p['num']} -> id {$id}{$note}\n";
    $inserted++;
}

echo "\nDone. Inserted {$inserted}, skipped {$skipped} (of " . count($pairs) . " total).\n";
