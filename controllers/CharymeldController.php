<?php
/**
 * CharymeldController — Handles the Charymeld AI assistant chat endpoint.
 *
 * POST /charymeld/chat
 *   body: { message, history[], csrf_token }
 *   response: { reply } | { error }
 */

require_once BASE_PATH . '/services/CharymeldService.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';

class CharymeldController extends Controller
{
    /** POST /charymeld/chat */
    public function chat(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // Feature flag
        if (!defined('CHARYMELD_ENABLED') || !CHARYMELD_ENABLED) {
            $this->json(['error' => 'Charymeld is not yet activated. Add your API key in config.php.'], 503);
            return;
        }

        // CSRF check
        $token = $this->post('csrf_token', '');
        if (empty($token) || !Security::validateCSRFToken($token)) {
            $this->json(['error' => 'Invalid request token.'], 403);
            return;
        }

        // Rate limit: 25 requests per hour per session
        if (!$this->checkRateLimit()) {
            $this->json(['error' => 'You\'ve sent too many messages. Please wait a few minutes.'], 429);
            return;
        }

        $message = trim($this->post('message', ''));
        if ($message === '') {
            $this->json(['error' => 'Please type a message.'], 400);
            return;
        }
        if (mb_strlen($message) > 800) {
            $this->json(['error' => 'Message is too long (max 800 characters).'], 400);
            return;
        }

        // Decode conversation history sent by the client
        $rawHistory = $this->post('history', '[]');
        $history    = is_string($rawHistory) ? (json_decode($rawHistory, true) ?? []) : [];

        // Build live database context
        $context = $this->buildContext($message);

        // Call Claude
        $service  = new CharymeldService();
        $reply    = $service->chat($message, $context, $history);

        if ($reply === null) {
            $this->json(['error' => 'Charymeld is temporarily unavailable. Please try again shortly.'], 503);
            return;
        }

        $this->json(['reply' => $reply]);
    }

    /* ─────────────────────────────────────────────────────
       Context builder — searches all content tables and
       returns a structured string for the system prompt
    ───────────────────────────────────────────────────── */

    private function buildContext(string $query): string
    {
        $keywords = $this->extractKeywords($query);
        if (empty($keywords)) {
            return '';
        }

        $context = [];

        try {
            // ── Vocabulary (daily_words) ──────────────────
            $words = $this->multiSearch(new DailyWord(), $keywords, ['tiv_word', 'english_meaning', 'example_tiv'], 6);
            if ($words) {
                $context[] = '=== TIV WORDS ===';
                foreach ($words as $w) {
                    $line = "• {$w['tiv_word']} = {$w['english_meaning']}";
                    if (!empty($w['pronunciation']))   $line .= "  (/{$w['pronunciation']}/)";
                    if (!empty($w['part_of_speech']))  $line .= "  [{$w['part_of_speech']}]";
                    if (!empty($w['example_tiv']))     $line .= "\n  e.g. \"{$w['example_tiv']}\"";
                    if (!empty($w['example_english'])) $line .= " → \"{$w['example_english']}\"";
                    $context[] = $line;
                }
            }

            // ── Names ─────────────────────────────────────
            $names = $this->multiSearch(new TivName(), $keywords, ['tiv_name', 'english_meaning', 'description', 'usage_context'], 5);
            if ($names) {
                $context[] = "\n=== TIV NAMES ===";
                foreach ($names as $n) {
                    $line = "• {$n['tiv_name']}";
                    if (!empty($n['gender']))        $line .= " ({$n['gender']})";
                    $line .= ": {$n['english_meaning']}";
                    if (!empty($n['description']))   $line .= "\n  {$n['description']}";
                    if (!empty($n['usage_context'])) $line .= "\n  Context: {$n['usage_context']}";
                    $context[] = $line;
                }
            }

            // ── Proverbs ──────────────────────────────────
            $proverbs = $this->multiSearch(new TivProverb(), $keywords, ['tiv_text', 'english_translation', 'deeper_meaning', 'usage_context'], 4);
            if ($proverbs) {
                $context[] = "\n=== TIV PROVERBS ===";
                foreach ($proverbs as $p) {
                    $line = "• \"{$p['tiv_text']}\"\n  → {$p['english_translation']}";
                    if (!empty($p['deeper_meaning'])) $line .= "\n  Insight: {$p['deeper_meaning']}";
                    if (!empty($p['usage_context']))  $line .= "\n  Usage: {$p['usage_context']}";
                    $context[] = $line;
                }
            }

            // ── Animals ───────────────────────────────────
            $animals = $this->multiSearch(new TivAnimal(), $keywords, ['name', 'tiv_name', 'description', 'cultural_use'], 3);
            if ($animals) {
                $context[] = "\n=== TIV ANIMALS ===";
                foreach ($animals as $a) {
                    $line = "• {$a['tiv_name']} ({$a['name']}): {$a['description']}";
                    if (!empty($a['cultural_use'])) $line .= "\n  Cultural role: {$a['cultural_use']}";
                    $context[] = $line;
                }
            }

            // ── Foods ─────────────────────────────────────
            $foods = $this->multiSearch(new TivFood(), $keywords, ['tiv_name', 'english_name', 'description', 'cultural_significance'], 3);
            if ($foods) {
                $context[] = "\n=== TIV FOODS ===";
                foreach ($foods as $f) {
                    $line = "• {$f['tiv_name']} ({$f['english_name']}): {$f['description']}";
                    if (!empty($f['cultural_significance'])) $line .= "\n  Cultural significance: {$f['cultural_significance']}";
                    $context[] = $line;
                }
            }

            // ── Plants ────────────────────────────────────
            $plants = $this->multiSearch(new TivPlant(), $keywords, ['tiv_name', 'english_name', 'description', 'medicinal_uses', 'ritual_uses'], 3);
            if ($plants) {
                $context[] = "\n=== TIV PLANTS ===";
                foreach ($plants as $pl) {
                    $line = "• {$pl['tiv_name']} ({$pl['english_name']}): {$pl['description']}";
                    if (!empty($pl['medicinal_uses'])) $line .= "\n  Medicinal: {$pl['medicinal_uses']}";
                    if (!empty($pl['ritual_uses']))    $line .= "\n  Ritual: {$pl['ritual_uses']}";
                    $context[] = $line;
                }
            }

            // ── Festivals ─────────────────────────────────
            $festivals = $this->multiSearch(new TivFestival(), $keywords, ['tiv_name', 'english_name', 'description', 'significance', 'activities'], 3);
            if ($festivals) {
                $context[] = "\n=== TIV FESTIVALS ===";
                foreach ($festivals as $fe) {
                    $line = "• {$fe['tiv_name']} ({$fe['english_name']}): {$fe['description']}";
                    if (!empty($fe['significance'])) $line .= "\n  Significance: {$fe['significance']}";
                    if (!empty($fe['activities']))   $line .= "\n  Activities: {$fe['activities']}";
                    $context[] = $line;
                }
            }

        } catch (Throwable $e) {
            error_log('Tiv AI context error: ' . $e->getMessage());
        }

        return implode("\n", $context);
    }

    /**
     * Search a model for multiple keywords individually and merge unique results.
     * This ensures both FULLTEXT and LIKE fallback work correctly — LIKE with a
     * multi-word phrase matches nothing, but LIKE with one word at a time works.
     */
    private function multiSearch(Model $model, array $keywords, array $fields, int $limit): array
    {
        $seen    = [];
        $results = [];

        // Try each keyword separately so LIKE "%word%" always finds single-word matches
        foreach (array_slice($keywords, 0, 5) as $kw) {
            if (count($results) >= $limit) break;
            foreach ($model->search($kw, $fields, $limit) as $row) {
                $id = $row['id'] ?? serialize($row);
                if (!isset($seen[$id])) {
                    $seen[$id]  = true;
                    $results[]  = $row;
                }
                if (count($results) >= $limit) break;
            }
        }

        return $results;
    }

    /**
     * Strip stop words and return meaningful keywords from the user query.
     */
    private function extractKeywords(string $query): array
    {
        static $stopWords = [
            'what', 'is', 'are', 'the', 'a', 'an', 'of', 'in', 'on', 'at',
            'how', 'does', 'do', 'can', 'tell', 'me', 'about', 'tiv', 'word',
            'mean', 'means', 'meaning', 'please', 'who', 'when', 'where',
            'why', 'which', 'with', 'for', 'to', 'from', 'and', 'or', 'its',
            'their', 'has', 'have', 'had', 'was', 'were', 'be', 'been',
        ];

        $words    = preg_split('/[\s\?\.,!;:]+/u', mb_strtolower(trim($query)), -1, PREG_SPLIT_NO_EMPTY);
        $keywords = array_filter($words, fn($w) => mb_strlen($w) > 2 && !in_array($w, $stopWords));
        return array_values(array_unique($keywords));
    }

    /**
     * Session-based rate limiter: 25 messages per rolling 60-minute window.
     */
    private function checkRateLimit(): bool
    {
        $key    = 'charymeld_rate';
        $window = 3600; // 1 hour
        $limit  = 25;
        $now    = time();

        $timestamps = $_SESSION[$key] ?? [];
        // Drop entries outside the window
        $timestamps = array_values(array_filter($timestamps, fn($t) => ($now - $t) < $window));

        if (count($timestamps) >= $limit) {
            return false;
        }

        $timestamps[]    = $now;
        $_SESSION[$key]  = $timestamps;
        return true;
    }
}
