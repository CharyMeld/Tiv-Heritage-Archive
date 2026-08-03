#!/usr/bin/env php
<?php
/**
 * CLI harness for services/NewsletterGeneratorService.php. Runs locally
 * (works even without Ollama — generateIntro() degrades to a fixed
 * fallback sentence when the model is unreachable) and on production
 * (where it also exercises the real AI-generated intro). Run:
 *   php bin/tests/test-newsletter-generator.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/services/NewsletterGeneratorService.php';

$pass = 0;
$fail = 0;
function ok(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { echo "  \e[0;32m✔\e[0m {$label}\n"; $pass++; }
    else       { echo "  \e[0;31m✘\e[0m {$label}\n"; $fail++; }
}

foreach (['weekly_summary', 'new_words', 'featured_items', 'top_five'] as $type) {
    $result = NewsletterGeneratorService::generate($type);
    ok("{$type}: has non-empty subject", !empty($result['subject']));
    ok("{$type}: HTML body contains the branded shell", str_contains($result['html_body'], (defined('SITE_NAME') ? SITE_NAME : '')));
    ok("{$type}: text body is non-empty", strlen(trim($result['text_body'])) > 0);
    echo "      subject: \"{$result['subject']}\"\n";
}

echo "\n--- Unknown type throws ---\n";
try {
    NewsletterGeneratorService::generate('nonexistent');
    ok('unknown type throws', false);
} catch (\RuntimeException $e) {
    ok('unknown type throws a clear error', str_contains($e->getMessage(), 'Unknown newsletter type'));
}

echo "\n" . str_repeat('─', 50) . "\n";
echo "PASS: {$pass}  FAIL: {$fail}\n\n";
exit($fail > 0 ? 1 : 0);
