<?php
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/models/MarketingPost.php';
require_once BASE_PATH . '/models/MarketingPromptTemplate.php';
require_once BASE_PATH . '/models/MarketingGenerationLog.php';

/**
 * Orchestrates picking a source archive item, building a prompt from a
 * template, calling the local Ollama model, and persisting the result as a
 * MarketingPost. No external AI API is used anywhere in this pipeline.
 */
class ContentGeneratorService
{
    /**
     * Resolve which archive item to generate content from.
     * Returns ['source_type' => string, 'item' => array, 'popularity_fallback' => bool].
     * Throws RuntimeException on any resolution failure (bad category, no rows, etc.).
     */
    public static function pickSource(string $mode, ?string $category = null, ?int $itemId = null): array
    {
        $registry = MARKETING_SOURCE_TYPES;

        if ($mode === 'random') {
            $category = array_rand($registry);
        }

        if ($category === null || !isset($registry[$category])) {
            throw new RuntimeException('A valid category is required for this selection mode.');
        }

        $config = $registry[$category];
        $model = self::modelFor($config['model']);
        $popularityFallback = false;

        switch ($mode) {
            case 'random':
            case 'specific_category':
                $item = $model->getRandom($config['random_where'] ?? '');
                break;

            case 'latest':
                $rows = $model->recent(1);
                $item = $rows[0] ?? null;
                break;

            case 'most_popular':
                if (!empty($config['has_views'])) {
                    $column = $config['view_column'] ?? 'view_count';
                    $rows = $model->mostViewedBy($column, 1);
                    $item = $rows[0] ?? null;
                } else {
                    // No popularity data exists for this category — degrade
                    // honestly to "latest" rather than pretending we have it.
                    $rows = $model->recent(1);
                    $item = $rows[0] ?? null;
                    $popularityFallback = true;
                }
                break;

            case 'specific_item':
                if ($itemId === null) {
                    throw new RuntimeException('An item id is required for "Specific Item" mode.');
                }
                $item = $model->find($itemId);
                break;

            default:
                throw new RuntimeException("Unknown selection mode: {$mode}");
        }

        if (!$item) {
            throw new RuntimeException("No content found for category \"{$category}\" — the table may be empty.");
        }

        return [
            'source_type' => $category,
            'item' => $item,
            'popularity_fallback' => $popularityFallback,
        ];
    }

    /**
     * Token-replace {{field}} placeholders in the template with the picked
     * item's actual column values, and append a hard JSON-format
     * instruction to the system prompt (small local models frequently need
     * to be told explicitly, and told again, not to add commentary).
     */
    public static function buildPrompt(array $template, array $item, string $sourceType): array
    {
        $label = MARKETING_SOURCE_TYPES[$sourceType]['label'] ?? $sourceType;

        $userPrompt = $template['user_prompt_template'];
        foreach ($item as $field => $value) {
            $userPrompt = str_replace('{{' . $field . '}}', (string) $value, $userPrompt);
        }

        $jsonInstruction = "\n\nRespond ONLY with a single valid JSON object with exactly these keys: "
            . "headline, caption, cta, hashtags, seo_title, meta_description, og_description, twitter_description. "
            . "No markdown code fences, no commentary before or after the JSON. "
            . "\"hashtags\" should be a single string of space-separated hashtags (e.g. \"#Tiv #Culture\"). "
            . "This content is about a Tiv {$label}.";

        $systemPrompt = trim(($template['system_prompt'] ?? '') . $jsonInstruction);

        return ['system' => $systemPrompt, 'user' => $userPrompt];
    }

    /**
     * Parse the model's raw response into the structured fields a
     * marketing_posts row needs. Returns null if the response isn't valid,
     * parseable JSON with at least a headline and caption — deliberately no
     * lossy fallback parsing, since silently saving garbage is worse than a
     * clear "generation failed, see log" message.
     */
    public static function parseAIOutput(string $raw): ?array
    {
        $stripped = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
        $decoded = json_decode($stripped, true);

        if (!is_array($decoded) || empty($decoded['headline'])) {
            return null;
        }

        // The model occasionally returns otherwise-well-formed JSON with an
        // empty "caption" (having put the substance into meta_description
        // instead) — fall back rather than discarding an otherwise-good
        // response. Only truly fail if the model gave us nothing usable
        // as body text anywhere.
        $caption = $decoded['caption'] ?? '';
        if (empty(trim((string) $caption))) {
            $caption = $decoded['og_description'] ?? $decoded['meta_description'] ?? '';
        }
        if (empty(trim((string) $caption))) {
            return null;
        }

        return [
            'headline'            => (string) $decoded['headline'],
            'caption'             => (string) $caption,
            'cta'                 => (string) ($decoded['cta'] ?? ''),
            'hashtags'            => (string) ($decoded['hashtags'] ?? ''),
            'seo_title'           => (string) ($decoded['seo_title'] ?? ''),
            'meta_description'    => (string) ($decoded['meta_description'] ?? ''),
            'og_description'      => (string) ($decoded['og_description'] ?? ''),
            'twitter_description' => (string) ($decoded['twitter_description'] ?? ''),
        ];
    }

    /**
     * Full pipeline: pick source → build prompt → call Ollama → parse →
     * backfill SEO fields → persist. Returns the new marketing_posts id.
     * Throws RuntimeException on any failure; the failure (including the
     * raw AI response, if any) is always logged to marketing_generation_log
     * first so it's debuggable from the admin UI.
     */
    public static function generate(
        string $mode,
        ?string $category,
        ?int $itemId,
        ?int $templateId,
        ?int $userId
    ): int {
        $picked = self::pickSource($mode, $category, $itemId);
        $sourceType = $picked['source_type'];
        $item = $picked['item'];

        $templates = new MarketingPromptTemplate();
        $template = $templateId ? $templates->find($templateId) : $templates->findDefaultFor($sourceType, 'any');
        if (!$template) {
            throw new RuntimeException("No prompt template found for category \"{$sourceType}\" — create one first.");
        }

        $prompt = self::buildPrompt($template, $item, $sourceType);

        $logs = new MarketingGenerationLog();
        $start = microtime(true);
        $raw = OllamaClient::generate($prompt['system'], $prompt['user']);
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        if ($raw === null) {
            $logs->log([
                'purpose' => 'post_generation',
                'model_name' => OLLAMA_MODEL,
                'request_prompt' => $prompt['user'],
                'response_raw' => null,
                'success' => 0,
                'error_message' => 'Ollama did not respond (unreachable or timed out).',
                'duration_ms' => $durationMs,
            ]);
            throw new RuntimeException('The local AI model did not respond. Check that Ollama is running.');
        }

        $parsed = self::parseAIOutput($raw);

        if ($parsed === null) {
            $logs->log([
                'purpose' => 'post_generation',
                'model_name' => OLLAMA_MODEL,
                'request_prompt' => $prompt['user'],
                'response_raw' => $raw,
                'success' => 0,
                'error_message' => 'Response could not be parsed as the expected JSON structure.',
                'duration_ms' => $durationMs,
            ]);
            throw new RuntimeException('The AI response could not be parsed — see the generation log for the raw output.');
        }

        $itemText = implode(' ', array_filter(array_map(
            fn($f) => $item[$f] ?? null,
            MARKETING_SOURCE_TYPES[$sourceType]['text_fields'] ?? []
        )));

        $seoTitle = $parsed['seo_title'] ?: $parsed['headline'];
        $metaDescription = SeoHelper::describe([$parsed['meta_description'], $itemText], $parsed['caption'], 155);
        $ogDescription = SeoHelper::describe([$parsed['og_description'], $parsed['meta_description'], $itemText], $parsed['caption'], 200);
        $twitterDescription = SeoHelper::describe([$parsed['twitter_description'], $parsed['meta_description'], $itemText], $parsed['caption'], 200);

        $posts = new MarketingPost();
        $postId = $posts->create([
            'source_type' => $sourceType,
            'source_id' => $item['id'] ?? null,
            'source_snapshot' => json_encode($item),
            'selection_mode' => $mode,
            'prompt_template_id' => $template['id'],
            'headline' => SeoHelper::truncate($parsed['headline'], 255),
            'caption' => $parsed['caption'],
            'cta' => SeoHelper::truncate($parsed['cta'], 255),
            'hashtags' => SeoHelper::truncate($parsed['hashtags'], 500),
            'seo_title' => SeoHelper::truncate($seoTitle, 255),
            'meta_description' => $metaDescription,
            'og_description' => $ogDescription,
            'twitter_description' => $twitterDescription,
            'status' => 'draft',
            'model_name' => OLLAMA_MODEL,
            'created_by' => $userId,
        ]);

        $logs->log([
            'post_id' => $postId,
            'prompt_template_id' => $template['id'],
            'purpose' => 'post_generation',
            'model_name' => OLLAMA_MODEL,
            'request_prompt' => $prompt['user'],
            'response_raw' => $raw,
            'success' => 1,
            'duration_ms' => $durationMs,
        ]);

        return $postId;
    }

    private static function modelFor(string $modelClass): Model
    {
        require_once BASE_PATH . "/models/{$modelClass}.php";
        return new $modelClass();
    }
}
