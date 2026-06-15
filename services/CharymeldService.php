<?php
/**
 * CharymeldService — Anthropic Claude API wrapper for the Charymeld AI assistant.
 * Sends the user's message + live database context and returns a grounded response.
 */
class CharymeldService
{
    private string $apiKey;
    private string $model;
    private string $endpoint = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = defined('ANTHROPIC_API_KEY') ? ANTHROPIC_API_KEY : '';
        $this->model  = defined('CHARYMELD_MODEL')   ? CHARYMELD_MODEL   : 'claude-haiku-4-5-20251001';
    }

    /**
     * Send a message to Claude and return the reply text, or null on failure.
     *
     * @param string $message     The user's current message
     * @param string $context     Database context built from search results
     * @param array  $history     Prior turns: [['role'=>'user','content'=>'...'], ...]
     */
    public function chat(string $message, string $context = '', array $history = []): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        // Build messages: keep last 6 turns (3 exchanges) for context efficiency
        $messages = [];
        foreach (array_slice($history, -6) as $turn) {
            if (!empty($turn['role']) && !empty($turn['content'])) {
                $messages[] = ['role' => $turn['role'], 'content' => (string) $turn['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $payload = [
            'model'      => $this->model,
            'max_tokens' => defined('CHARYMELD_MAX_TOKENS') ? CHARYMELD_MAX_TOKENS : 768,
            'system'     => $this->systemPrompt($context),
            'messages'   => $messages,
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: '          . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT        => defined('CHARYMELD_TIMEOUT') ? CHARYMELD_TIMEOUT : 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $httpCode !== 200) {
            error_log("Charymeld API error: HTTP {$httpCode} | cURL: {$curlErr} | Body: {$raw}");
            return null;
        }

        $data = json_decode($raw, true);
        return $data['content'][0]['text'] ?? null;
    }

    private function systemPrompt(string $context): string
    {
        $prompt = <<<SYSTEM
You are Tiv AI, the official AI cultural assistant of the Tiv Heritage Archive — a digital platform dedicated to preserving the Tiv language, culture, and heritage of the Tiv people of Nigeria and Cameroon.

Your personality:
- Warm, wise, and deeply respectful of Tiv culture
- Enthusiastic about sharing Tiv heritage with the world
- Conversational and engaging — a cultural guide, not just a reference tool
- You only use Tiv words or phrases that are confirmed in the DATABASE CONTEXT — never invent or assume Tiv expressions

STRICT RULES — follow these exactly:
1. The LIVE ARCHIVE DATABASE CONTEXT section below is your ONLY source for specific Tiv words, name meanings, proverbs, animals, foods, plants, and festivals. Do not invent or guess any of these.
2. For word lookups always provide: Tiv word → English meaning → pronunciation (if known) → example sentence (if available). Use ONLY what is in the DATABASE CONTEXT.
3. If the DATABASE CONTEXT does not contain the specific word, name, proverb, food, plant, animal, or festival being asked about, respond with: "I don't have that specific entry in the archive yet. You can help grow the archive by contributing at the Contribute page!"
4. Do NOT make up Tiv words, meanings, translations, or cultural facts. Only state what is explicitly in the DATABASE CONTEXT.
5. For broad questions about Tiv history, geography, or general culture (not specific vocabulary/names/proverbs), you may draw on general knowledge — but always label such information clearly as "(general knowledge)".
6. Keep responses under 220 words unless the question genuinely requires more detail.
7. Politely redirect clearly off-topic questions back to Tiv culture.

SYSTEM;

        if (!empty($context)) {
            $prompt .= "\n\nLIVE ARCHIVE DATABASE CONTEXT (use this as your only factual source for specific items):\n"
                     . $context;
        } else {
            $prompt .= "\n\nLIVE ARCHIVE DATABASE CONTEXT: No matching entries found in the archive for this query.\n"
                     . "If the user is asking about a specific Tiv word, name, proverb, food, plant, animal, or festival, "
                     . "tell them it is not yet in the archive and invite them to contribute it. "
                     . "Do NOT invent information.";
        }

        return $prompt;
    }
}
