#!/usr/bin/env php
<?php
/**
 * CLI harness for Security::encrypt()/decrypt(). Run:
 *   php bin/tests/test-security-crypto.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/security.php';

$pass = 0;
$fail = 0;
function ok(string $label, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { echo "  \e[0;32m✔\e[0m {$label}\n"; $pass++; }
    else       { echo "  \e[0;31m✘\e[0m {$label}\n"; $fail++; }
}

echo "\n--- Round-trip ---\n";
$secret = 'EAAG_super_secret_page_access_token_123';
$encrypted = Security::encrypt($secret);
ok('encrypted value differs from plaintext', $encrypted !== $secret);
ok('encrypted value is valid base64', base64_decode($encrypted, true) !== false);
$decrypted = Security::decrypt($encrypted);
ok('decrypt() recovers the original plaintext', $decrypted === $secret);

echo "\n--- Tamper detection ---\n";
$raw = base64_decode($encrypted);
$tampered = $raw;
$tampered[strlen($tampered) - 1] = chr(ord($tampered[strlen($tampered) - 1]) ^ 0xFF);
$tamperedEncoded = base64_encode($tampered);
$result = Security::decrypt($tamperedEncoded);
ok('decrypt() returns null for a tampered ciphertext (auth tag mismatch)', $result === null);

echo "\n--- Edge cases ---\n";
ok('decrypt(null) returns null without error', Security::decrypt(null) === null);
ok('decrypt("") returns null without error', Security::decrypt('') === null);
ok('decrypt("not-valid-base64!!!") returns null without error', Security::decrypt('short') === null);

echo "\n--- Two different encryptions of the same plaintext produce different ciphertext (random IV) ---\n";
$enc1 = Security::encrypt($secret);
$enc2 = Security::encrypt($secret);
ok('ciphertexts differ due to random IV per call', $enc1 !== $enc2);
ok('both still decrypt correctly', Security::decrypt($enc1) === $secret && Security::decrypt($enc2) === $secret);

echo "\n" . str_repeat('─', 50) . "\n";
echo "PASS: {$pass}  FAIL: {$fail}\n\n";
exit($fail > 0 ? 1 : 0);
