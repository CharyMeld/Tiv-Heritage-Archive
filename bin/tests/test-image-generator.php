#!/usr/bin/env php
<?php
/**
 * CLI harness for services/SocialGraphicGenerator.php. Generates all 9
 * template types x 3 orientations (27 files) with realistic sample data,
 * asserts dimensions via getimagesize(), and checks the missing-font path
 * fails clearly. Run:
 *   php bin/tests/test-image-generator.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/services/SocialGraphicGenerator.php';

$pass = 0;
$fail = 0;
function ok(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { echo "  \e[0;32m✔\e[0m {$label}\n"; $pass++; }
    else       { echo "  \e[0;31m✘\e[0m {$label}\n"; $fail++; }
}

$sampleData = [
    'word_of_day'       => ['tiv_word' => 'Sha', 'english_meaning' => 'On, at, upon', 'alternate_meaning' => 'A versatile Tiv preposition used to indicate location or position relative to something.'],
    'proverb_of_day'    => ['tiv_text' => 'Bua ka i i̱ wa ken ku je, ka i̱ due ken ku je.', 'english_translation' => 'What you put into the pot is what comes out of it — your actions determine your outcomes.'],
    'historical_figure' => ['english_name' => 'J.S. Tarka', 'title' => 'Politician & Middle Belt Leader', 'short_summary' => 'A prominent Tiv politician who championed the creation of the Middle Belt region and Benue State.'],
    'festival'          => ['tiv_name' => 'Kwagh-hir', 'english_name' => 'The Storytelling Theatre', 'description' => 'A traditional Tiv performance art combining puppetry, music, and storytelling to pass on cultural values.'],
    'food'              => ['tiv_name' => 'Ruam', 'english_name' => 'Pounded Yam', 'description' => 'A staple Tiv food made from pounded yam, traditionally served with a variety of soups.'],
    'plant'             => ['tiv_name' => 'Anyiin', 'scientific_name' => 'Vitex doniana', 'description' => 'A tree valued in Tiv culture for its edible fruit and medicinal bark.'],
    'animal'            => ['name' => 'Genet', 'tiv_name' => 'Ivom', 'description' => 'A small nocturnal mammal that holds symbolic significance in Tiv folklore.'],
    'timeline'          => ['title' => 'Tiv Migration Across the Benue', 'short_summary' => 'Oral tradition traces Tiv ancestral migration southward toward the Benue River valley.'],
    'quote'             => ['caption' => 'Preserving our language today means our children can speak it tomorrow.'],
];

$generatedFiles = [];

echo "\n--- Generating all 9 templates x 3 orientations ---\n";
foreach (SocialGraphicGenerator::templateTypes() as $type) {
    foreach (['square', 'portrait', 'landscape'] as $orientation) {
        try {
            $path = SocialGraphicGenerator::generate($type, $orientation, $sampleData[$type]);
            $info = @getimagesize($path);
            $expected = ['square' => [1080, 1080], 'portrait' => [1080, 1350], 'landscape' => [1200, 630]][$orientation];
            $valid = $info && $info[0] === $expected[0] && $info[1] === $expected[1] && filesize($path) > 1000;
            ok("{$type} / {$orientation} — {$info[0]}x{$info[1]}", $valid);
            $generatedFiles[] = $path;
        } catch (\Throwable $e) {
            ok("{$type} / {$orientation}", false);
            echo "      error: {$e->getMessage()}\n";
        }
    }
}

echo "\n--- Error handling ---\n";
try {
    SocialGraphicGenerator::generate('nonexistent_template', 'square', []);
    ok('unknown template type throws', false);
} catch (\RuntimeException $e) {
    ok('unknown template type throws a clear error', str_contains($e->getMessage(), 'Unknown image template'));
}

try {
    SocialGraphicGenerator::generate('word_of_day', 'huge', []);
    ok('unknown orientation throws', false);
} catch (\RuntimeException $e) {
    ok('unknown orientation throws a clear error', str_contains($e->getMessage(), 'Unknown orientation'));
}

echo "\n--- Cleanup ---\n";
foreach ($generatedFiles as $f) {
    @unlink($f);
}
echo '  removed ' . count($generatedFiles) . " generated test images.\n";

echo "\n" . str_repeat('─', 50) . "\n";
echo "PASS: {$pass}  FAIL: {$fail}\n\n";
exit($fail > 0 ? 1 : 0);
