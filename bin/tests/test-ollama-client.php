#!/usr/bin/env php
<?php
/**
 * CLI harness for services/OllamaClient.php. Run on the production VPS
 * (Ollama is bound to 127.0.0.1, not reachable from elsewhere):
 *   php bin/tests/test-ollama-client.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/services/OllamaClient.php';

$pass = 0;
$fail = 0;
function ok(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { echo "  \e[0;32m✔\e[0m {$label}\n"; $pass++; }
    else       { echo "  \e[0;31m✘\e[0m {$label}\n"; $fail++; }
}

echo "\n--- OllamaClient::isAvailable() ---\n";
$available = OllamaClient::isAvailable();
ok('Ollama daemon is reachable', $available === true);

echo "\n--- OllamaClient::listModels() ---\n";
$models = OllamaClient::listModels();
ok('lists at least one installed model', count($models) > 0);
ok('qwen2.5:7b-instruct is installed', in_array('qwen2.5:7b-instruct', $models));

echo "\n--- OllamaClient::generate() — real call ---\n";
$start = microtime(true);
$result = OllamaClient::generate(
    'You are a concise assistant. Respond in one short sentence only.',
    'Say something positive about learning a new language.'
);
$elapsed = round(microtime(true) - $start, 1);
ok("generate() returned a non-empty string (took {$elapsed}s)", is_string($result) && strlen($result) > 0);
echo "    → \"{$result}\"\n";

echo "\n--- OllamaClient::generate() — JSON-following behavior ---\n";
$jsonResult = OllamaClient::generate(
    'Respond ONLY with a single valid JSON object with exactly these keys: headline, caption. No markdown fences, no commentary.',
    'The Tiv word "sha" can mean "on" or "at".'
);
$stripped = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($jsonResult ?? ''));
$decoded = json_decode($stripped, true);
ok('model response is parseable as JSON (possibly after fence-stripping)', is_array($decoded) && isset($decoded['headline']));
echo "    → raw: " . substr($jsonResult ?? '', 0, 200) . "\n";

echo "\n--- Unreachable endpoint behaves gracefully (simulated failure) ---\n";
// OLLAMA_ENDPOINT is a constant and can't be swapped at runtime, so this
// exercises the same curl options OllamaClient uses directly against a
// closed port, confirming a fast failure rather than a hang.
$closedPortStart = microtime(true);
$testCh = curl_init('http://127.0.0.1:1'); // port 1 — nothing listens there
curl_setopt_array($testCh, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 2]);
$testResult = curl_exec($testCh);
$testErrno = curl_errno($testCh);
curl_close($testCh);
$closedPortElapsed = round(microtime(true) - $closedPortStart, 1);
ok("closed-port connection fails fast ({$closedPortElapsed}s) rather than hanging", $testErrno !== 0 && $closedPortElapsed < 10);

echo "\n" . str_repeat('─', 50) . "\n";
echo "PASS: {$pass}  FAIL: {$fail}\n\n";
exit($fail > 0 ? 1 : 0);
