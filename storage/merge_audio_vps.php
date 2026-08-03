#!/usr/bin/env php
<?php
/**
 * merge_audio_vps.php — run once on the VPS after uploading alphabet-videos/
 *
 * For each tiv_alphabet entry that has an audio_file linked in the DB,
 * this script finds the matching silent MP4 and merges the audio into it.
 *
 * Usage (from this directory on the VPS):
 *   php merge_audio_vps.php [--dry-run]
 *
 * Output: overwrites each silent MP4 in alphabet-videos/ with the audio version.
 */

$dry_run = in_array('--dry-run', $argv ?? []);

// ── Paths ─────────────────────────────────────────────────────────────────
$base        = dirname(__DIR__);          // /path/to/Tiv-Heritage-Archive
$videos_dir  = __DIR__ . '/alphabet-videos';
$uploads_dir = $base . '/uploads';       // where audio_file paths are rooted

// ── DB connection ─────────────────────────────────────────────────────────
$db_config_file = $base . '/config/database.php';

// Read credentials from database.php without bootstrapping the full app
$db_cfg = parse_ini_string(
    preg_replace(
        ['/^\s*<\?php\s*/', '/\$[a-z_]+\[.+?\]\s*=\s*/', '/;/', '/\s*\?\>\s*$/'],
        ['', '', '', ''],
        file_get_contents($db_config_file)
    )
);

// Fallback: hardcode if parse_ini_string fails (common with PHP config files)
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=tiv_archive;charset=utf8mb4',
        'tivuser',
        'Tiv@Archive2026!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Try unix socket as fallback
    $pdo = new PDO(
        'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=tiv_archive;charset=utf8mb4',
        'tivuser',
        'Tiv@Archive2026!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

// ── Query entries with audio ───────────────────────────────────────────────
$rows = $pdo->query(
    "SELECT id, type, letter, english_letter, audio_file
     FROM tiv_alphabet
     WHERE audio_file IS NOT NULL AND audio_file != ''
     ORDER BY sort_order, id"
)->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "No entries with audio_file found in tiv_alphabet. Nothing to do.\n";
    exit(0);
}

echo "Found " . count($rows) . " entries with audio.\n";
if ($dry_run) echo "(DRY RUN — no files will be modified)\n";
echo "\n";

// ── Helper: build expected video filename from DB row ─────────────────────
function video_filename(array $row): string {
    // Try english_letter first (e.g. "A a", "GB gb"), then letter field
    $src = trim($row['english_letter'] ?: $row['letter']);

    // For tonal type we use type + tonal description stored in letter
    // The letter column might be "á" or "à" etc.
    // Build the same safe string the Python script used:
    $safe = str_replace([' ', '/', '(', ')'], ['_', '', '', ''], $src);
    $safe = trim($safe, '_');

    return $row['type'] . '_' . $safe . '.mp4';
}

// ── Process each entry ────────────────────────────────────────────────────
$ok = $skip = $err = 0;

foreach ($rows as $row) {
    $video_file  = video_filename($row);
    $silent_path = $videos_dir . '/' . $video_file;
    $audio_path  = $uploads_dir . '/' . ltrim($row['audio_file'], '/');

    echo "[ID:{$row['id']}] {$row['type']} — {$row['letter']}\n";
    echo "  Video : $video_file\n";
    echo "  Audio : {$row['audio_file']}\n";

    if (!file_exists($silent_path)) {
        echo "  SKIP  : video file not found at $silent_path\n\n";
        $skip++;
        continue;
    }
    if (!file_exists($audio_path)) {
        echo "  SKIP  : audio file not found at $audio_path\n\n";
        $skip++;
        continue;
    }

    $tmp_out = $silent_path . '.tmp.mp4';

    $cmd = implode(' ', [
        'ffmpeg', '-y',
        '-i',  escapeshellarg($silent_path),
        '-i',  escapeshellarg($audio_path),
        '-c:v', 'copy',
        '-c:a', 'aac',
        '-shortest',
        '-movflags', '+faststart',
        escapeshellarg($tmp_out),
        '2>&1'
    ]);

    if ($dry_run) {
        echo "  DRY   : would run: $cmd\n\n";
        $ok++;
        continue;
    }

    $output     = shell_exec($cmd);
    $exit_clean = file_exists($tmp_out) && filesize($tmp_out) > 1000;

    if ($exit_clean) {
        rename($tmp_out, $silent_path);
        $kb = round(filesize($silent_path) / 1024);
        echo "  OK    : merged ({$kb} KB)\n\n";
        $ok++;
    } else {
        @unlink($tmp_out);
        echo "  ERROR : ffmpeg failed\n";
        echo "          " . substr($output, -300) . "\n\n";
        $err++;
    }
}

// ── Summary ───────────────────────────────────────────────────────────────
echo str_repeat('=', 50) . "\n";
echo "Merged : $ok\n";
echo "Skipped: $skip\n";
echo "Errors : $err\n";
