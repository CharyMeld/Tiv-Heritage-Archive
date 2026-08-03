#!/usr/bin/env php
<?php
/**
 * Publishing queue processor — intended to run every 15 minutes via cron.
 * For each due internal schedule, attempts to publish via the configured
 * platform client (SocialPublisher dispatches to Facebook or Instagram).
 * Any other platform is logged as unsupported rather than silently skipped.
 *
 * Each platform client is gated behind its own isConnected() check, so a
 * schedule for a platform with no real credentials yet deterministically
 * logs a graceful failure (e.g. "Instagram is not connected.") — this
 * proves the retry/logging pipeline works mechanically before real app
 * credentials exist. Once added via Settings → Test Connection, the same
 * script starts actually publishing with no code changes.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/MarketingSchedule.php';
require_once BASE_PATH . '/models/MarketingPublishAttempt.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingImage.php';
require_once BASE_PATH . '/services/SocialPublisher.php';

function logLine(string $text): void
{
    echo '[' . date('Y-m-d H:i:s') . "] {$text}\n";
}

$schedules = new MarketingSchedule();
$attempts = new MarketingPublishAttempt();
$posts = new MarketingPost();
$imageModel = new MarketingImage();

$due = $schedules->getDue();
logLine('Found ' . count($due) . ' due schedule(s).');

if (empty($due)) {
    exit(0);
}

foreach ($due as $schedule) {
    if (!in_array($schedule['platform'], ['facebook', 'instagram'], true)) {
        logLine("Schedule #{$schedule['id']}: platform \"{$schedule['platform']}\" has no publisher wired up yet, skipping.");
        continue;
    }

    $post = $posts->find((int) $schedule['post_id']);
    if (!$post) {
        logLine("Schedule #{$schedule['id']}: source post no longer exists, marking failed.");
        $schedules->markFailed((int) $schedule['id'], 'Source post no longer exists.');
        continue;
    }

    $image = $schedule['image_id'] ? $imageModel->find((int) $schedule['image_id']) : null;
    $result = SocialPublisher::publish($schedule, $post, $image);

    $attempts->logAttempt((int) $schedule['id'], [
        'http_status' => $result['http_status'] ?? null,
        'response_body' => $result['response_body'] ?? null,
        'error_message' => $result['error'] ?? null,
        'success' => $result['success'] ? 1 : 0,
    ]);

    if ($result['success']) {
        $schedules->markPublished((int) $schedule['id'], $result['permalink'] ?? null);
        $posts->update((int) $post['id'], ['status' => 'published']);
        logLine("Schedule #{$schedule['id']}: published successfully (" . ucfirst($schedule['platform']) . " post {$result['post_id']}).");
    } else {
        $schedules->markFailed((int) $schedule['id'], $result['error'] ?? 'Unknown error');
        logLine("Schedule #{$schedule['id']}: failed — {$result['error']}");
    }
}

logLine('Done.');
