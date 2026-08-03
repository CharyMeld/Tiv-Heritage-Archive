#!/usr/bin/env php
<?php
/**
 * Seeds one default per-platform caption-formatting prompt template
 * (category='general', platform=<platform>). These reformat an already
 * generated master post (headline/caption/cta/hashtags) into a
 * platform-appropriate style, rather than generating from the raw source
 * item again. Run locally or on the VPS:
 *   php database/seeds/seed_marketing_platform_templates.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';

function line(string $text = ''): void { echo $text . PHP_EOL; }
function ok(string $text): void        { line("  \e[0;32m✔\e[0m  {$text}"); }
function skip(string $text): void      { line("  \e[0;33m–\e[0m  {$text}"); }

$systemBase = 'You are a social media writer for the Tiv Heritage Archive rewriting an already-written '
    . 'piece of content into a platform-specific style. Do not invent new facts — only rephrase and '
    . 'restyle what is given below.';

$basePrompt = 'Original headline: {{headline}}. Original caption: {{caption}}. Call to action: {{cta}}. Hashtags: {{hashtags}}.';

$templates = [
    [
        'name' => 'Facebook — long educational (default)',
        'platform' => 'facebook',
        'system_prompt' => $systemBase . ' Facebook posts here are long-form and educational — 3-5 sentences, warm and informative, ending with the call to action.',
        'user_prompt_template' => $basePrompt . ' Rewrite this as a long, educational Facebook post.',
    ],
    [
        'name' => 'Instagram — visual (default)',
        'platform' => 'instagram',
        'system_prompt' => $systemBase . ' Instagram captions here are short, visual, and emoji-friendly, built around a strong hook in the first line, ending with a hashtag block.',
        'user_prompt_template' => $basePrompt . ' Rewrite this as a punchy, visual Instagram caption.',
    ],
    [
        'name' => 'X — short (default)',
        'platform' => 'x',
        'system_prompt' => $systemBase . ' X (Twitter) posts here must be 280 characters or fewer, punchy, and to the point — no filler.',
        'user_prompt_template' => $basePrompt . ' Rewrite this as a single X post, 280 characters maximum.',
    ],
    [
        'name' => 'LinkedIn — professional (default)',
        'platform' => 'linkedin',
        'system_prompt' => $systemBase . ' LinkedIn posts here are professional in tone, framed around cultural preservation and heritage value, minimal emoji.',
        'user_prompt_template' => $basePrompt . ' Rewrite this as a professional LinkedIn post about cultural heritage preservation.',
    ],
    [
        'name' => 'Telegram — markdown (default)',
        'platform' => 'telegram',
        'system_prompt' => $systemBase . ' Telegram posts here may use Markdown formatting (*bold*, _italic_) and can be a bit longer and more detailed than other platforms.',
        'user_prompt_template' => $basePrompt . ' Rewrite this as a Telegram post using Markdown formatting where it helps readability.',
    ],
    [
        'name' => 'WhatsApp — copy-ready (default)',
        'platform' => 'whatsapp',
        'system_prompt' => $systemBase . ' WhatsApp messages here are short, plain-text, and written so a person can copy-paste and send them directly to a group or contact.',
        'user_prompt_template' => $basePrompt . ' Rewrite this as a short, friendly WhatsApp message ready to copy and send.',
    ],
];

line();
line('Seeding marketing_prompt_templates — ' . count($templates) . ' default platform templates');
line(str_repeat('─', 60));

$model = new MarketingPromptTemplate();
$inserted = 0;
$skippedCount = 0;

foreach ($templates as $t) {
    $existing = $model->findDefaultFor('general', $t['platform']);
    if ($existing) {
        skip("{$t['name']} — a default already exists for this platform, skipped");
        $skippedCount++;
        continue;
    }

    $id = $model->create(array_merge($t, ['category' => 'general', 'is_default' => 1, 'is_active' => 1]));
    ok("{$t['name']} (id {$id})");
    $inserted++;
}

line(str_repeat('─', 60));
line("Done. Inserted {$inserted}, skipped {$skippedCount}.");
line();
