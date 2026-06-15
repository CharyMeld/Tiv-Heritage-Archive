<?php
/**
 * NLLB Translator
 *
 * Wraps the Hugging Face Inference API for the
 * facebook/nllb-200-distilled-600M model.
 *
 * Language codes used:
 *   English → eng_Latn
 *   Tiv     → tiv_Latn
 *
 * Returns null on any failure so callers can degrade gracefully.
 */

class NllbTranslator
{
    // NLLB language code map (internal name → NLLB BCP-47)
    private const LANG_CODES = [
        'english' => 'eng_Latn',
        'tiv'     => 'tiv_Latn',
    ];

    private string $apiKey;
    private string $endpoint;
    private int    $timeout;
    private bool   $enabled;

    public function __construct()
    {
        $this->apiKey   = defined('NLLB_API_KEY')   ? NLLB_API_KEY   : '';
        $this->endpoint = defined('NLLB_ENDPOINT')  ? NLLB_ENDPOINT  : '';
        $this->timeout  = defined('NLLB_TIMEOUT')   ? NLLB_TIMEOUT   : 10;
        $this->enabled  = defined('NLLB_ENABLED')   ? NLLB_ENABLED   : false;
    }

    /**
     * Whether NLLB is configured and ready to use.
     */
    public function isAvailable(): bool
    {
        return $this->enabled && $this->apiKey !== '' && $this->endpoint !== '';
    }

    /**
     * Translate $text from $sourceLang to $targetLang via NLLB.
     *
     * @param string $text       The text to translate (may contain PHRS0…PHRSn placeholders)
     * @param string $sourceLang 'tiv' or 'english'
     * @param string $targetLang 'tiv' or 'english'
     * @return string|null       Translated text, or null on any error
     */
    public function translate(string $text, string $sourceLang, string $targetLang): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $srcCode = self::LANG_CODES[$sourceLang] ?? null;
        $tgtCode = self::LANG_CODES[$targetLang] ?? null;

        if ($srcCode === null || $tgtCode === null) {
            return null;
        }

        // wait_for_model=true tells HF to block until the model is loaded
        // instead of returning 503 — avoids the retry/sleep dance entirely.
        $payload = json_encode([
            'inputs'     => $text,
            'parameters' => [
                'src_lang' => $srcCode,
                'tgt_lang' => $tgtCode,
            ],
            'options' => [
                'wait_for_model' => true,
            ],
        ]);

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);

        // HF returns: [{"translation_text": "..."}]
        if (isset($data[0]['translation_text'])) {
            return trim($data[0]['translation_text']);
        }

        // Older HF format: {"generated_text": "..."}
        if (isset($data['generated_text'])) {
            return trim($data['generated_text']);
        }

        return null;
    }
}
