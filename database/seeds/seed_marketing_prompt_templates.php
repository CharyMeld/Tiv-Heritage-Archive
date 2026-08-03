#!/usr/bin/env php
<?php
/**
 * Seeds one default "master content" prompt template per content category
 * (platform='any'), matching the example prompts from the original feature
 * spec. Run locally or on the VPS:
 *   php database/seeds/seed_marketing_prompt_templates.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';

function line(string $text = ''): void { echo $text . PHP_EOL; }
function ok(string $text): void        { line("  \e[0;32m✔\e[0m  {$text}"); }
function skip(string $text): void      { line("  \e[0;33m–\e[0m  {$text}"); }

$systemBase = 'You are a warm, knowledgeable social media writer for the Tiv Heritage Archive, '
    . 'a platform preserving Tiv language, history, and culture. Write in clear, engaging English '
    . 'suitable for a general audience who may know nothing about Tiv culture yet.';

$templates = [
    [
        'name' => 'Word of the Day — default',
        'category' => 'word',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Create a Facebook post explaining this Tiv word in a simple educational style. '
            . 'Tiv word: {{tiv_word}}. English meaning: {{english_meaning}}. Alternate meaning: {{alternate_meaning}}.',
    ],
    [
        'name' => 'Proverb — default',
        'category' => 'proverb',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Explain the meaning of this Tiv proverb and encourage readers to discover more on the archive. '
            . 'Proverb: {{tiv_text}}. English translation: {{english_translation}}. Deeper meaning: {{deeper_meaning}}.',
    ],
    [
        'name' => 'Name — default',
        'category' => 'name',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Explain the meaning and cultural significance of this Tiv name. '
            . 'Name: {{tiv_name}}. English meaning: {{english_meaning}}. Origin story: {{origin_story}}.',
    ],
    [
        'name' => 'Timeline Event — default',
        'category' => 'history',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Summarize this historical event in an engaging storytelling style. '
            . 'Title: {{title}}. Summary: {{short_summary}}. Details: {{description}}.',
    ],
    [
        'name' => 'Historical Figure — default',
        'category' => 'historical_figure',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Introduce this historical figure from Tiv history in an engaging way. '
            . 'Name: {{english_name}}. Summary: {{short_summary}}. Biography: {{biography}}.',
    ],
    [
        'name' => 'Festival — default',
        'category' => 'festival',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Explain this Tiv festival and invite people to learn more. '
            . 'Festival: {{tiv_name}}. Description: {{description}}. Significance: {{significance}}.',
    ],
    [
        'name' => 'Traditional Food — default',
        'category' => 'food',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Describe this traditional Tiv food and its importance. '
            . 'Food: {{tiv_name}}. Description: {{description}}.',
    ],
    [
        'name' => 'Plant — default',
        'category' => 'plant',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Teach people about this plant and its role in Tiv culture. '
            . 'Plant: {{tiv_name}}. Description: {{description}}.',
    ],
    [
        'name' => 'Animal — default',
        'category' => 'animal',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Teach people about this animal in Tiv culture. '
            . 'Animal: {{name}}. Description: {{description}}.',
    ],
    [
        'name' => 'Grammar Rule — default',
        'category' => 'grammar',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Turn this Tiv grammar rule into a simple, friendly language-learning tip. '
            . 'Rule: {{title}}. Summary: {{summary}}. Explanation: {{explanation}}.',
    ],
    [
        'name' => 'Learning Video — default',
        'category' => 'lesson',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Write a post promoting this Tiv-language learning video. '
            . 'Title: {{title}}. Description: {{description}}.',
    ],
    [
        'name' => 'Source / Reference — default',
        'category' => 'reference',
        'platform' => 'any',
        'system_prompt' => $systemBase,
        'user_prompt_template' => 'Write a post highlighting this reference source used in the archive, and why citing real sources matters for preserving Tiv history. '
            . 'Title: {{title}}. Notes: {{notes}}.',
    ],
];

line();
line('Seeding marketing_prompt_templates — ' . count($templates) . ' default templates');
line(str_repeat('─', 60));

$model = new MarketingPromptTemplate();
$inserted = 0;
$skippedCount = 0;

foreach ($templates as $t) {
    $existing = $model->findDefaultFor($t['category'], $t['platform']);
    if ($existing) {
        skip("{$t['name']} — a default already exists for this category+platform, skipped");
        $skippedCount++;
        continue;
    }

    $id = $model->create(array_merge($t, ['is_default' => 1, 'is_active' => 1]));
    ok("{$t['name']} (id {$id})");
    $inserted++;
}

line(str_repeat('─', 60));
line("Done. Inserted {$inserted}, skipped {$skippedCount}.");
line();
