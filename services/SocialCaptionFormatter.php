<?php
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/MarketingLinkBuilder.php';
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

/**
 * Rewrites an already-generated master post (headline/caption/cta/hashtags)
 * into a platform-specific style. One Ollama call per platform, generated
 * lazily on demand — not all 6 up front — so a single admin action doesn't
 * block on ~6 sequential model calls, and so quality per platform doesn't
 * degrade from asking one small model to produce 6 stylistically distinct
 * variants in a single response.
 */
class SocialCaptionFormatter
{
    private const X_CHAR_LIMIT = 280;

    public static function generateForPlatform(array $post, string $platform, ?int $templateId, ?int $userId): int
    {
        if (!isset(MarketingPostCaption::PLATFORMS[$platform])) {
            throw new RuntimeException("Unknown platform: {$platform}");
        }

        $templates = new MarketingPromptTemplate();
        $template = $templateId ? $templates->find($templateId) : $templates->findDefaultFor('general', $platform);
        if (!$template) {
            throw new RuntimeException("No prompt template found for platform \"{$platform}\" — create one first.");
        }

        $userPrompt = $template['user_prompt_template'];
        $tokens = [
            'headline' => $post['headline'] ?? '',
            'caption'  => $post['caption'] ?? '',
            'cta'      => $post['cta'] ?? '',
            'hashtags' => $post['hashtags'] ?? '',
        ];
        foreach ($tokens as $field => $value) {
            $userPrompt = str_replace('{{' . $field . '}}', (string) $value, $userPrompt);
        }

        $systemPrompt = trim(($template['system_prompt'] ?? '')
            . "\n\nRespond ONLY with a single valid JSON object with exactly these keys: caption, hashtags. "
            . "No markdown code fences, no commentary before or after the JSON.");

        $logs = new MarketingGenerationLog();
        $start = microtime(true);
        $raw = OllamaClient::generate($systemPrompt, $userPrompt);
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        if ($raw === null) {
            $logs->log([
                'post_id' => $post['id'],
                'prompt_template_id' => $template['id'],
                'purpose' => 'platform_caption',
                'model_name' => OLLAMA_MODEL,
                'request_prompt' => $userPrompt,
                'success' => 0,
                'error_message' => 'Ollama did not respond (unreachable or timed out).',
                'duration_ms' => $durationMs,
            ]);
            throw new RuntimeException('The local AI model did not respond. Check that Ollama is running.');
        }

        $stripped = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
        $decoded = json_decode($stripped, true);

        $captionText = null;
        $hashtags = '';

        if (is_array($decoded) && !empty(trim((string) ($decoded['caption'] ?? '')))) {
            $captionText = (string) $decoded['caption'];
            $hashtags = (string) ($decoded['hashtags'] ?? '');
        } else {
            // The model quite reliably falls back to a plain "caption: ..."
            // label instead of JSON on longer-form prompts (observed
            // consistently for the Facebook template specifically). This is
            // a well-defined, deterministic prefix pattern — not a fuzzy
            // guess — so it's worth recovering rather than discarding.
            if (preg_match('/^\s*caption\s*:\s*(.+?)(?:\n\s*hashtags\s*:\s*(.+))?$/is', $stripped, $m)) {
                // Strip any trailing "hashtags" fragment the model started
                // but didn't finish (e.g. cut off with no colon/content).
                $captionText = trim(preg_replace('/\n?\s*hashtags\s*:?\s*$/i', '', trim($m[1])));
                $hashtags = trim($m[2] ?? '');
            }
        }

        if ($captionText === null || $captionText === '') {
            $logs->log([
                'post_id' => $post['id'],
                'prompt_template_id' => $template['id'],
                'purpose' => 'platform_caption',
                'model_name' => OLLAMA_MODEL,
                'request_prompt' => $userPrompt,
                'response_raw' => $raw,
                'success' => 0,
                'error_message' => 'Response could not be parsed as JSON or as a recognizable "caption: ..." fallback.',
                'duration_ms' => $durationMs,
            ]);
            throw new RuntimeException('The AI response could not be parsed — see the generation log for the raw output.');
        }

        // Every caption gets a real callback link to the source content on
        // the site — without this, a generated post has no way back to
        // tivheritage.com no matter how far it's shared. Reuses the same
        // short code across regenerations so click analytics stay unified.
        $item = $post['source_snapshot'] ? (json_decode($post['source_snapshot'], true) ?: []) : [];
        $item['id'] = $item['id'] ?? $post['source_id'] ?? null;
        $link = MarketingLinkBuilder::linkFor((int) $post['id'], (string) ($post['source_type'] ?? ''), $item, $platform, $userId);
        $goUrl = url('go/' . $link['short_code']);
        $linkSuffix = "\n\n" . $goUrl;

        // Safety net: this app never publishes over an API limit, no matter
        // what the model produced — truncate the caption body (not the
        // link) so the callback URL always survives.
        if ($platform === 'x') {
            $maxBodyLen = max(0, self::X_CHAR_LIMIT - mb_strlen($linkSuffix));
            if (mb_strlen($captionText) > $maxBodyLen) {
                $captionText = SeoHelper::truncate($captionText, $maxBodyLen);
            }
        }
        $captionText .= $linkSuffix;

        $captions = new MarketingPostCaption();
        $captionId = $captions->upsertForPlatform((int) $post['id'], $platform, [
            'caption_text' => $captionText,
            'hashtags' => $hashtags,
            'char_count' => mb_strlen($captionText),
            'prompt_template_id' => $template['id'],
        ]);

        $logs->log([
            'post_id' => $post['id'],
            'prompt_template_id' => $template['id'],
            'purpose' => 'platform_caption',
            'model_name' => OLLAMA_MODEL,
            'request_prompt' => $userPrompt,
            'response_raw' => $raw,
            'success' => 1,
            'duration_ms' => $durationMs,
        ]);

        return $captionId;
    }
}
