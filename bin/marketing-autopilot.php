#!/usr/bin/env php
<?php
/**
 * Full hands-off marketing pipeline — intended to replace
 * marketing-generate-drafts.php in cron once an admin no longer wants to
 * manually review/approve every post. Runs every 4 hours (6x/day):
 *
 *   1. Generate a post (random category) via local Ollama.
 *   2. Auto-approve it (no human review gate).
 *   3. Generate captions for all 6 social platforms.
 *   4. Auto-select a matching image template and render the branded image.
 *   5. Create a tracked SITE_URL/go/{code} link back to the source content,
 *      embedded in every caption and printed on the image itself.
 *   6. Schedule the post for immediate publishing to Facebook, and — when
 *      an image was generated (step 4) — Instagram too, since Instagram has
 *      no text-only post type. Both are picked up by the next run of
 *      marketing-process-schedule.php (every 15 min).
 *
 * Cron (Africa/Lagos / WAT): 7am, 11am, 3pm, 7pm, 11pm, 3am
 * Cron (server UTC, WAT - 1h): 0 6,10,14,18,22,2 * * *
 *
 * Every step is independently try/caught and logged: a failure in image
 * generation or a secondary platform's caption does not stop the pipeline
 * from still scheduling the Facebook post.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/security.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/ContentGeneratorService.php';
require_once BASE_PATH . '/services/SocialCaptionFormatter.php';
require_once BASE_PATH . '/services/SocialGraphicGenerator.php';
require_once BASE_PATH . '/services/MarketingLinkBuilder.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/models/MarketingImage.php';
require_once BASE_PATH . '/models/MarketingSchedule.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

// Attributed to the Administrator account (id 1) — the same convention
// already used for other system-initiated rows (e.g. Facebook settings).
const AUTOPILOT_USER_ID = 1;

function logLine(string $text): void
{
    echo '[' . date('Y-m-d H:i:s') . "] {$text}\n";
}

if (!OLLAMA_ENABLED) {
    logLine('OLLAMA_ENABLED is false — nothing to do.');
    exit(0);
}

if (!OllamaClient::isAvailable()) {
    logLine('Ollama is not reachable — skipping today\'s autopilot run.');
    exit(1);
}

$posts = new MarketingPost();
$images = new MarketingImage();
$schedules = new MarketingSchedule();

// 1. Generate
try {
    $postId = ContentGeneratorService::generate('random', null, null, null, AUTOPILOT_USER_ID);
    $post = $posts->find($postId);
    logLine("Generated post #{$postId} (\"{$post['headline']}\", category: {$post['source_type']}).");
} catch (\Throwable $e) {
    logLine('Generation failed: ' . $e->getMessage());
    exit(1);
}

// 2. Auto-approve
$posts->approve($postId, AUTOPILOT_USER_ID);
logLine("Post #{$postId} auto-approved.");

// 3. Captions for every platform (Facebook is required for publishing below;
// the rest are generated so they're ready for copy/paste or future platforms).
foreach (MarketingPostCaption::PLATFORMS as $platform => $label) {
    try {
        SocialCaptionFormatter::generateForPlatform($post, $platform, null, AUTOPILOT_USER_ID);
        logLine("Caption generated for {$label}.");
    } catch (\Throwable $e) {
        logLine("Caption generation failed for {$label}: " . $e->getMessage());
    }
}

// 4. Image — auto-select the best-fit template for this source type.
$imageId = null;
try {
    $item = $post['source_snapshot'] ? (json_decode($post['source_snapshot'], true) ?: []) : [];
    $item['id'] = $item['id'] ?? $post['source_id'] ?? null;
    $item['headline'] = $post['headline'];
    $item['caption'] = $post['caption'];

    $templateType = MarketingImage::AUTO_TEMPLATE_FOR_SOURCE[$post['source_type']] ?? 'quote';
    // These 4 source types have no dedicated template and reuse a
    // repurposed layout — swap the eyebrow so the card names the real
    // content type instead of showing the borrowed template's label.
    if (in_array($post['source_type'], ['name', 'grammar', 'lesson', 'reference'], true)) {
        $item['eyebrow_override'] = strtoupper(MarketingPost::SOURCE_TYPES[$post['source_type']] ?? '');
    }

    $link = MarketingLinkBuilder::linkFor($postId, (string) $post['source_type'], $item, 'facebook', AUTOPILOT_USER_ID);
    $ctaUrl = url('go/' . $link['short_code']);

    $orientation = 'square';
    $path = SocialGraphicGenerator::generate($templateType, $orientation, $item, $ctaUrl);
    $dims = MarketingImage::ORIENTATIONS[$orientation];

    $imageId = $images->create([
        'post_id' => $postId,
        'template_type' => $templateType,
        'orientation' => $orientation,
        'file_path' => 'marketing_images/' . basename($path),
        'width' => $dims['width'],
        'height' => $dims['height'],
        'created_by' => AUTOPILOT_USER_ID,
    ]);
    logLine("Image #{$imageId} generated ({$templateType}/{$orientation}).");
} catch (\Throwable $e) {
    logLine('Image generation failed (continuing without an image): ' . $e->getMessage());
}

// 5 & 6. Schedule for immediate publishing — the next
// marketing-process-schedule.php run (every 15 min) will pick these up.
$scheduleId = $schedules->create([
    'post_id' => $postId,
    'platform' => 'facebook',
    'image_id' => $imageId,
    'scheduled_at' => date('Y-m-d H:i:s'),
    'status' => 'pending',
    'created_by' => AUTOPILOT_USER_ID,
]);
$posts->update($postId, ['status' => 'scheduled']);
logLine("Schedule #{$scheduleId} created for immediate Facebook publishing.");

if ($imageId) {
    $igScheduleId = $schedules->create([
        'post_id' => $postId,
        'platform' => 'instagram',
        'image_id' => $imageId,
        'scheduled_at' => date('Y-m-d H:i:s'),
        'status' => 'pending',
        'created_by' => AUTOPILOT_USER_ID,
    ]);
    logLine("Schedule #{$igScheduleId} created for immediate Instagram publishing.");
} else {
    logLine('No image was generated for this post, so Instagram was skipped (Instagram requires an image).');
}

logLine('Autopilot run complete.');
