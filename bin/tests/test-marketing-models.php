#!/usr/bin/env php
<?php
/**
 * CLI harness for the AI Content Marketing Engine's M0 models.
 * Boots config+db directly (no HTTP), exercises create/find/update/delete
 * on every new table, and cleans up after itself. Run:
 *   php bin/tests/test-marketing-models.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/models/MarketingImage.php';
require_once BASE_PATH . '/models/MarketingSchedule.php';
require_once BASE_PATH . '/models/MarketingPublishAttempt.php';
require_once BASE_PATH . '/models/MarketingNewsletterIssue.php';
require_once BASE_PATH . '/models/MarketingUtmLink.php';
require_once BASE_PATH . '/models/MarketingLinkClick.php';
require_once BASE_PATH . '/models/MarketingFacebookSetting.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivAnimal.php';

$pass = 0;
$fail = 0;
function ok(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { echo "  \e[0;32m✔\e[0m {$label}\n"; $pass++; }
    else       { echo "  \e[0;31m✘\e[0m {$label}\n"; $fail++; }
}

echo "\n--- MarketingPromptTemplate ---\n";
// A real default for (word, any) may already exist in a live system —
// remember it so it can be restored, rather than assuming a pristine table.
$templates = new MarketingPromptTemplate();
$originalDefault = $templates->findDefaultFor('word', 'any');

$tplId = $templates->create([
    'name' => 'TEST Word Template',
    'category' => 'word',
    'platform' => 'any',
    'system_prompt' => 'You are a helpful assistant.',
    'user_prompt_template' => 'Write about {{tiv_word}}.',
    'is_default' => 0, // set via setDefault() below, matching real app behavior
    'is_active' => 1,
]);
ok('created template', $tplId > 0);
$found = $templates->find($tplId);
ok('found template with correct name', $found['name'] === 'TEST Word Template');

$templates->setDefault($tplId);
$default = $templates->findDefaultFor('word', 'any');
ok('findDefaultFor resolves the default', $default && (int)$default['id'] === $tplId);

// Restore whichever template (if any) was the real default before this test ran.
if ($originalDefault) {
    $templates->setDefault((int) $originalDefault['id']);
}

echo "\n--- MarketingPost ---\n";
$posts = new MarketingPost();
$postId = $posts->create([
    'source_type' => 'word',
    'source_id' => 1,
    'selection_mode' => 'random',
    'prompt_template_id' => $tplId,
    'headline' => 'TEST Headline',
    'caption' => 'TEST caption text.',
    'status' => 'draft',
    'model_name' => 'qwen2.5:7b-instruct',
]);
ok('created post', $postId > 0);
$posts->approve($postId, 1);
$approved = $posts->find($postId);
ok('approve() set status', $approved['status'] === 'approved');
$counts = $posts->countByStatus();
ok('countByStatus returns an array keyed by every status', count($counts) === count(MarketingPost::STATUSES));

echo "\n--- MarketingPostCaption ---\n";
$captions = new MarketingPostCaption();
$capId = $captions->upsertForPlatform($postId, 'x', ['caption_text' => 'Short test caption', 'char_count' => 19]);
ok('upsert created a caption row', $capId > 0);
$capId2 = $captions->upsertForPlatform($postId, 'x', ['caption_text' => 'Updated caption', 'char_count' => 15]);
ok('upsert updates in place (same id)', $capId2 === $capId);
$forPost = $captions->getForPost($postId);
ok('getForPost returns keyed-by-platform', isset($forPost['x']) && $forPost['x']['caption_text'] === 'Updated caption');

echo "\n--- MarketingImage ---\n";
$images = new MarketingImage();
$imgId = $images->create([
    'post_id' => $postId,
    'template_type' => 'word_of_day',
    'orientation' => 'square',
    'file_path' => 'marketing_images/test.png',
    'width' => 1080,
    'height' => 1080,
]);
ok('created image', $imgId > 0);
ok('getForPost finds it', count($images->getForPost($postId)) === 1);

echo "\n--- MarketingSchedule + MarketingPublishAttempt ---\n";
$schedules = new MarketingSchedule();
$schedId = $schedules->create([
    'post_id' => $postId,
    'platform' => 'facebook',
    'image_id' => $imgId,
    'scheduled_at' => date('Y-m-d H:i:s', strtotime('-1 minute')),
    'status' => 'pending',
]);
ok('created schedule', $schedId > 0);
$due = $schedules->getDue();
ok('getDue() finds the past-due schedule', in_array($schedId, array_column($due, 'id')));
$schedules->markFailed($schedId, 'Facebook is not connected.');
$failed = $schedules->find($schedId);
ok('markFailed set status + incremented attempts', $failed['status'] === 'failed' && (int)$failed['attempts'] === 1);

$attempts = new MarketingPublishAttempt();
$attemptId = $attempts->logAttempt($schedId, ['success' => 0, 'error_message' => 'Facebook is not connected.']);
ok('logged publish attempt', $attemptId > 0);
$attemptId2 = $attempts->logAttempt($schedId, ['success' => 0, 'error_message' => 'Still not connected.']);
$forSched = $attempts->getForSchedule($schedId);
$attemptNumbers = array_map('intval', array_column($forSched, 'attempt_number'));
sort($attemptNumbers);
ok('attempt_number increments across attempts', $attemptNumbers === [1, 2]);

echo "\n--- MarketingNewsletterIssue ---\n";
$newsletters = new MarketingNewsletterIssue();
$nlId = $newsletters->create([
    'issue_type' => 'weekly_summary',
    'issue_date' => date('Y-m-d'),
    'subject' => 'TEST Weekly Summary',
    'html_body' => '<p>Hello</p>',
    'text_body' => 'Hello',
    'status' => 'draft',
]);
ok('created newsletter issue', $nlId > 0);
$newsletters->approve($nlId);
ok('approve() worked', $newsletters->find($nlId)['status'] === 'approved');
ok('latest() returns the most recent issue', $newsletters->latest()['id'] == $nlId);

echo "\n--- MarketingUtmLink + MarketingLinkClick ---\n";
$links = new MarketingUtmLink();
$code = $links->generateUniqueCode();
ok('generated a unique short code', strlen($code) === 7);
$linkId = $links->create([
    'post_id' => $postId,
    'platform' => 'facebook',
    'destination_url' => SITE_URL . '/word/1-test',
    'utm_source' => 'facebook',
    'utm_medium' => 'social',
    'utm_campaign' => 'test-campaign',
    'short_code' => $code,
]);
ok('created UTM link', $linkId > 0);
$byCode = $links->findByShortCode($code);
ok('findByShortCode resolves it', $byCode && (int)$byCode['id'] === $linkId);
$links->incrementClicks($linkId);

$clicks = new MarketingLinkClick();
$clickId = $clicks->log($linkId, '127.0.0.1', 'TestAgent/1.0', 'https://facebook.com');
ok('logged a click', $clickId > 0);
ok('totalForLink counts it', $clicks->totalForLink($linkId) === 1);
$afterClick = $links->find($linkId);
ok('click_count incremented on the link', (int)$afterClick['click_count'] === 1);

echo "\n--- MarketingFacebookSetting (singleton) ---\n";
// This is a real singleton row (id=1) that may hold genuine live credentials
// in a running system — snapshot the exact current state and restore it
// afterward, rather than assuming/forcing "disconnected" as a starting or
// ending point (which would clobber a real admin's actual configuration).
$fbSettings = new MarketingFacebookSetting();
$originalRow = $fbSettings->get();
ok('get() returns/creates the singleton row', (int)$originalRow['id'] === 1);

$fbSettings->markError('Invalid credentials (test)');
ok('markError() sets status to error', $fbSettings->get()['connection_status'] === 'error');
$fbSettings->markConnected();
ok('markConnected() flips to connected', $fbSettings->isConnected() === true);

// Restore the exact original row (all columns, not just a hardcoded
// "disconnected" default) so a real admin's live config is untouched.
$fbSettings->save([
    'app_id' => $originalRow['app_id'],
    'app_secret_encrypted' => $originalRow['app_secret_encrypted'],
    'page_id' => $originalRow['page_id'],
    'page_access_token_encrypted' => $originalRow['page_access_token_encrypted'],
    'connection_status' => $originalRow['connection_status'],
    'last_checked_at' => $originalRow['last_checked_at'],
    'last_error' => $originalRow['last_error'],
]);
$restored = $fbSettings->get();
ok('original row fully restored after test', $restored['connection_status'] === $originalRow['connection_status']
    && $restored['app_id'] === $originalRow['app_id']
    && $restored['page_id'] === $originalRow['page_id']);

echo "\n--- MarketingGenerationLog ---\n";
// successRate() aggregates over the whole table, which may already contain
// real entries in a live system — assert on the DELTA this test itself
// causes, not on an absolute percentage that assumes a pristine table.
$logs = new MarketingGenerationLog();
$before = $logs->successRate(7);
$logId = $logs->log([
    'post_id' => $postId,
    'purpose' => 'post_generation',
    'model_name' => 'qwen2.5:7b-instruct',
    'request_prompt' => 'test prompt',
    'response_raw' => '{"headline":"test"}',
    'success' => 1,
    'duration_ms' => 4200,
]);
ok('logged a generation event', $logId > 0);
$after = $logs->successRate(7);
ok('successRate total increments by exactly 1', $after['total'] === $before['total'] + 1);
ok('successRate succeeded count increments by exactly 1', $after['succeeded'] === $before['succeeded'] + 1);

echo "\n--- Model::getRandom() base method ---\n";
$proverbs = new TivProverb();
$randomProverb = $proverbs->getRandom();
ok('TivProverb::getRandom() (existing override) still works', $randomProverb !== null);

$animals = new TivAnimal();
$randomAnimal = $animals->getRandom();
ok('TivAnimal::getRandom() via new base method works (no override needed)', $randomAnimal !== null);

$words = new DailyWord();
$randomWord = $words->getRandom();
ok('DailyWord::getRandom() (existing override) still works', $randomWord !== null);

echo "\n--- Cleanup ---\n";
$db = Database::getInstance();
$db->prepare('DELETE FROM marketing_link_clicks WHERE id = ?')->execute([$clickId]);
$db->prepare('DELETE FROM marketing_utm_links WHERE id = ?')->execute([$linkId]);
$db->prepare('DELETE FROM marketing_newsletter_issues WHERE id = ?')->execute([$nlId]);
$db->prepare('DELETE FROM marketing_publish_attempts WHERE schedule_id = ?')->execute([$schedId]);
$db->prepare('DELETE FROM marketing_schedules WHERE id = ?')->execute([$schedId]);
$db->prepare('DELETE FROM marketing_images WHERE id = ?')->execute([$imgId]);
$db->prepare('DELETE FROM marketing_post_captions WHERE post_id = ?')->execute([$postId]);
$db->prepare('DELETE FROM marketing_posts WHERE id = ?')->execute([$postId]);
$db->prepare('DELETE FROM marketing_prompt_templates WHERE id = ?')->execute([$tplId]);
$db->prepare('DELETE FROM marketing_generation_log WHERE id = ?')->execute([$logId]);
echo "  cleaned up all test rows.\n";

echo "\n" . str_repeat('─', 50) . "\n";
echo "PASS: {$pass}  FAIL: {$fail}\n\n";
exit($fail > 0 ? 1 : 0);
