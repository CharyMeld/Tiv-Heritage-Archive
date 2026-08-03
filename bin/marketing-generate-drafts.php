#!/usr/bin/env php
<?php
/**
 * Daily automated draft generation — intended to run once per day via cron.
 * Picks one random category, generates a draft post via the local Ollama
 * model, and leaves it as status='draft' for an administrator to review and
 * approve. Never auto-publishes anything — this only ever creates a draft.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/ContentGeneratorService.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

function logLine(string $text): void
{
    echo '[' . date('Y-m-d H:i:s') . "] {$text}\n";
}

if (!OLLAMA_ENABLED) {
    logLine('OLLAMA_ENABLED is false — nothing to do.');
    exit(0);
}

if (!OllamaClient::isAvailable()) {
    logLine('Ollama is not reachable — skipping today\'s draft generation.');
    exit(1);
}

try {
    $postId = ContentGeneratorService::generate('random', null, null, null, null);
    $posts = new MarketingPost();
    $post = $posts->find($postId);
    logLine("Generated draft post #{$postId} (\"{$post['headline']}\", category: {$post['source_type']}).");
    exit(0);
} catch (\Throwable $e) {
    logLine('Generation failed: ' . $e->getMessage());
    exit(1);
}
