<?php
/**
 * OllamaClient — thin HTTP client for a locally-hosted Ollama instance.
 *
 * No external AI API is used anywhere in this service. Ollama runs on the
 * same host (127.0.0.1) as a systemd service, never exposed externally.
 */
class OllamaClient
{
    /**
     * Send a chat completion request. Returns the assistant's reply text,
     * or null on any failure (timeout, connection refused, malformed
     * response) — this method never throws, matching the "return null,
     * never throw" convention already used by this app's other optional
     * external-service clients.
     */
    public static function generate(string $systemPrompt, string $userPrompt, float $temperature = 0.7): ?string
    {
        if (!OLLAMA_ENABLED) {
            return null;
        }

        $payload = [
            'model' => OLLAMA_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'stream' => false,
            'keep_alive' => -1, // never unload — must be a bare number, not a string: Ollama's API rejects "-1" (missing duration unit) while accepting the JSON number -1
            'options' => [
                'temperature' => $temperature,
                'num_predict' => 700,
            ],
        ];

        $raw = self::post('/api/chat', $payload, OLLAMA_TIMEOUT);
        if ($raw === null) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['message']['content'])) {
            return null;
        }

        return $decoded['message']['content'];
    }

    /**
     * Lightweight check that the Ollama daemon is reachable and responding.
     * Decoupled from generate()'s much longer timeout — this is meant for a
     * quick "Test Connection" button, not for gating actual generation calls.
     */
    public static function isAvailable(): bool
    {
        if (!OLLAMA_ENABLED) {
            return false;
        }

        $raw = self::get('/api/tags', 3);
        return $raw !== null;
    }

    /**
     * List locally installed model names (for a settings-page dropdown).
     */
    public static function listModels(): array
    {
        $raw = self::get('/api/tags', 3);
        if ($raw === null) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['models'])) {
            return [];
        }

        return array_map(fn($m) => $m['name'], $decoded['models']);
    }

    private static function post(string $path, array $payload, int $timeout): ?string
    {
        $ch = curl_init(rtrim(OLLAMA_ENDPOINT, '/') . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno !== 0 || $httpCode !== 200 || $response === false) {
            return null;
        }

        return $response;
    }

    private static function get(string $path, int $timeout): ?string
    {
        $ch = curl_init(rtrim(OLLAMA_ENDPOINT, '/') . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno !== 0 || $httpCode !== 200 || $response === false) {
            return null;
        }

        return $response;
    }
}
