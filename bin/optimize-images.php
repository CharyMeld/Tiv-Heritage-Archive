<?php
/**
 * One-off/re-runnable batch job: generates WebP + responsive (400w/800w)
 * variants for every existing image in uploads/images/.
 *
 * deploy.sh excludes uploads/* from rsync, so this must be run directly on
 * the server against the live uploads directory:
 *
 *   php bin/optimize-images.php
 *
 * Idempotent - already-generated variants are skipped, so it's safe to
 * re-run after new images are added outside the admin upload flow (e.g. a
 * bulk import) without reprocessing everything.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/services/ImageVariantGenerator.php';

$dir = BASE_PATH . '/uploads/images';
if (!is_dir($dir)) {
    fwrite(STDERR, "No such directory: {$dir}\n");
    exit(1);
}

$files = glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
$total = count($files);
$done  = 0;

foreach ($files as $path) {
    $base = basename($path);
    // Skip files that are themselves already-generated variants.
    if (preg_match('/-(400|800)w\.(jpg|jpeg|png|gif|webp)$/i', $base)) {
        continue;
    }
    ImageVariantGenerator::generate($path);
    $done++;
    echo "[{$done}/{$total}] {$base}\n";
}

echo "Done. Processed {$done} source images.\n";
