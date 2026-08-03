#!/usr/bin/env php
<?php
/**
 * CLI harness for services/ContentGeneratorService.php. Generates one real
 * post per each of the 12 MARKETING_SOURCE_TYPES via the local Ollama model,
 * plus a deliberately-broken-template failure case. Run on the production
 * VPS (Ollama is loopback-only):
 *   php bin/tests/test-content-generator.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/services/ContentGeneratorService.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

$pass = 0;
$fail = 0;
function ok(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { echo "  \e[0;32m✔\e[0m {$label}\n"; $pass++; }
    else       { echo "  \e[0;31m✘\e[0m {$label}\n"; $fail++; }
}

$posts = new MarketingPost();
$createdPostIds = [];

echo "\n--- Generating one post per category (via real local Ollama calls) ---\n";
foreach (array_keys(MARKETING_SOURCE_TYPES) as $category) {
    $label = MARKETING_SOURCE_TYPES[$category]['label'];
    $start = microtime(true);
    try {
        $postId = ContentGeneratorService::generate('random', $category, null, null, null);
        $elapsed = round(microtime(true) - $start, 1);
        $post = $posts->find($postId);
        $valid = $post
            && !empty($post['headline'])
            && !empty($post['caption'])
            && !empty($post['meta_description'])
            && !empty($post['seo_title']);
        ok("{$label} ({$category}) generated a complete post in {$elapsed}s", $valid);
        if ($valid) {
            echo "      headline: \"{$post['headline']}\"\n";
            $createdPostIds[] = $postId;
        }
    } catch (\Throwable $e) {
        ok("{$label} ({$category}) generated a complete post", false);
        echo "      error: {$e->getMessage()}\n";
    }
}

echo "\n--- Selection modes ---\n";
try {
    $latestId = ContentGeneratorService::generate('latest', 'proverb', null, null, null);
    ok('latest mode works', $latestId > 0);
    $createdPostIds[] = $latestId;
} catch (\Throwable $e) {
    ok('latest mode works', false);
    echo "    error: {$e->getMessage()}\n";
}

try {
    $popularId = ContentGeneratorService::generate('most_popular', 'name', null, null, null);
    ok('most_popular mode falls back gracefully for a category with no view tracking', $popularId > 0);
    $createdPostIds[] = $popularId;
} catch (\Throwable $e) {
    ok('most_popular mode falls back gracefully', false);
    echo "    error: {$e->getMessage()}\n";
}

try {
    ContentGeneratorService::generate('specific_item', 'proverb', 999999999, null, null);
    ok('specific_item with a non-existent id throws', false);
} catch (\RuntimeException $e) {
    ok('specific_item with a non-existent id throws a clear error', str_contains($e->getMessage(), 'No content found'));
}

echo "\n--- Malformed-template failure path (no post row should be created) ---\n";
$templates = new MarketingPromptTemplate();
$logs = new MarketingGenerationLog();

$brokenTplId = $templates->create([
    'name' => 'TEST Broken Template',
    'category' => 'proverb',
    'platform' => 'any',
    'system_prompt' => 'You must respond with the single word BANANA and absolutely nothing else. Do not use JSON.',
    'user_prompt_template' => 'Respond with the word BANANA.',
    'is_default' => 0,
    'is_active' => 1,
]);

$postCountBefore = $posts->countAll();
$threw = false;
try {
    ContentGeneratorService::generate('random', 'proverb', null, $brokenTplId, null);
} catch (\RuntimeException $e) {
    $threw = true;
    ok('broken template throws a clear "could not be parsed" error', str_contains($e->getMessage(), 'could not be parsed'));
}
ok('a RuntimeException was actually thrown', $threw);
$postCountAfter = $posts->countAll();
ok('no post row was created on parse failure', $postCountBefore === $postCountAfter);

$failedLog = $logs->recentFailures(1);
ok('the failure was logged to marketing_generation_log with the raw response preserved',
    !empty($failedLog) && !empty($failedLog[0]['response_raw']));

echo "\n--- Cleanup ---\n";
$db = Database::getInstance();
foreach ($createdPostIds as $id) {
    $db->prepare('DELETE FROM marketing_generation_log WHERE post_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM marketing_posts WHERE id = ?')->execute([$id]);
}
$db->prepare('DELETE FROM marketing_generation_log WHERE prompt_template_id = ?')->execute([$brokenTplId]);
$db->prepare('DELETE FROM marketing_prompt_templates WHERE id = ?')->execute([$brokenTplId]);
echo "  cleaned up " . count($createdPostIds) . " test posts + 1 broken template.\n";

echo "\n" . str_repeat('─', 50) . "\n";
echo "PASS: {$pass}  FAIL: {$fail}\n\n";
exit($fail > 0 ? 1 : 0);
