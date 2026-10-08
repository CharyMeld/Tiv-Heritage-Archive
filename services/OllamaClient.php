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
        return self::chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], $temperature);
    }

    /**
     * Send an arbitrary multi-turn chat completion request — system/user/assistant
     * messages passed straight through to Ollama's /api/chat, which natively
     * supports full conversation history. Used by Charymeld for conversational
     * (multi-turn) replies; generate() above remains the single-shot form used
     * by the marketing content pipeline. Same "return null, never throw" contract.
     *
     * @param array<int, array{role:string, content:string}> $messages
     */
    public static function chat(array $messages, float $temperature = 0.7, int $numPredict = 700): ?string
    {
        if (!OLLAMA_ENABLED) {
            return null;
        }

        $payload = [
            'model' => OLLAMA_MODEL,
            'messages' => $messages,
            'stream' => false,
            'keep_alive' => -1, // never unload — must be a bare number, not a string: Ollama's API rejects "-1" (missing duration unit) while accepting the JSON number -1
            'options' => [
                'temperature' => $temperature,
                'num_predict' => $numPredict,
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
     * Get a vector embedding for a piece of text, via a dedicated embedding model
     * (e.g. nomic-embed-text — NOT the chat model). Returns the raw float array, or
     * null on any failure. Same "return null, never throw" contract as chat().
     *
     * @return float[]|null
     */
    public static function embed(string $text, string $model = EMBEDDING_MODEL): ?array
    {
        if (!OLLAMA_ENABLED) {
            return null;
        }

        // keep_alive: -1 — same reasoning as chat()'s: without it Ollama unloads the
        // model after its default idle timeout, so the next call after any quiet
        // period eats a ~4s cold-load penalty. Cheap to keep resident; it's a small
        // (~270MB) model.
        $payload = ['model' => $model, 'input' => $text, 'keep_alive' => -1];
        $raw = self::post('/api/embed', $payload, EMBEDDING_TIMEOUT);
        if ($raw === null) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['embeddings'][0]) || !is_array($decoded['embeddings'][0])) {
            return null;
        }

        return $decoded['embeddings'][0];
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
