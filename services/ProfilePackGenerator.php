<?php
require_once BASE_PATH . '/services/OllamaClient.php';
require_once BASE_PATH . '/services/HeritagePublic.php';
require_once BASE_PATH . '/services/SocialGraphicGenerator.php';
require_once BASE_PATH . '/models/MarketingProfilePost.php';
require_once BASE_PATH . '/models/MarketingUtmLink.php';

/**
 * Prepares the "Profile pack": ready-to-post Facebook items for the owner's
 * personal profile, built from published Nigeria Heritage records.
 *
 * Meta has no API for personal profiles (publish_actions was removed in 2018,
 * professional mode included), so nothing here posts anything. Each item holds
 * a first-person caption, a branded image and a "first comment" carrying the
 * tracked link (Facebook shows posts with links in the body to fewer people).
 */
class ProfilePackGenerator
{
    /** Minimum prose on a record before it is worth a post. */
    public const MIN_WORDS = 100;
    /** A record is not prepared again within this many days. */
    public const REUSE_AFTER_DAYS = 180;
    /** Suggested posting times (WAT) for 1, 2 or 3 posts a day. */
    public const SLOTS = [1 => ['19:00'], 2 => ['08:00', '19:00'], 3 => ['08:00', '13:00', '19:00']];
    /** How far ahead posts can be scheduled (days). The admin button looks this far for the next empty slot. */
    public const MAX_DAYS_AHEAD = 365;
    /** Source text sent to the model is cut to this many characters. */
    private const MAX_SOURCE_CHARS = 3000;

    /**
     * Fill every empty slot in the next $days days. Returns one line per item
     * for the log. $dryRun prepares captions but writes nothing. $limit caps how
     * many are prepared in this call (the admin button prepares one per request,
     * since each caption is a slow local-model call).
     */
    public static function fill(int $days = 7, int $perDay = 1, bool $dryRun = false, ?int $userId = null, ?int $limit = null): array
    {
        $times = self::SLOTS[max(1, min(3, $perDay))];
        $now = time();
        $slots = [];
        for ($d = 0; $d <= $days; $d++) {
            foreach ($times as $t) {
                $ts = strtotime(date('Y-m-d', strtotime("+{$d} day", $now)) . ' ' . $t);
                if ($ts > $now) $slots[] = date('Y-m-d H:i:s', $ts);
            }
        }
        $slots = array_slice($slots, 0, $days * count($times));
        if (!$slots) return [];

        $posts = new MarketingProfilePost();
        $taken = array_flip($posts->takenSlots($slots[0], end($slots)));
        $free = array_values(array_filter($slots, fn($s) => !isset($taken[$s])));
        if (!$free) return ['Every slot in the next ' . $days . ' days already has a post.'];
        if ($limit !== null) $free = array_slice($free, 0, $limit);

        $records = self::pickRecords(count($free));
        if (!$records) return ['No Nigeria Heritage record is available (all used in the last ' . self::REUSE_AFTER_DAYS . ' days).'];

        $out = [];
        foreach ($free as $i => $slot) {
            if (!isset($records[$i])) {
                $out[] = "{$slot}: no unused record left — slot left empty.";
                break;
            }
            [$table, $row] = $records[$i];
            try {
                $item = self::prepare($table, $row, $slot, $dryRun, $userId);
                $out[] = "{$slot}: {$row[HeritageRegistry::NAME_COLUMN[$table]]} ({$item['generated_by']})"
                    . ($dryRun ? "\n" . $item['caption'] . "\n" . $item['hashtags'] . "\n" : " → #{$item['id']}");
            } catch (\Throwable $e) {
                $out[] = "{$slot}: {$row[HeritageRegistry::NAME_COLUMN[$table]]} failed: " . $e->getMessage();
            }
        }
        return $out;
    }

    /**
     * Up to $n published, public records with enough prose, never-used ones first,
     * mixing record types so the week is not seven states in a row.
     * Returns [[table, row], ...].
     */
    public static function pickRecords(int $n): array
    {
        $used = (new MarketingProfilePost())->usedSince(date('Y-m-d H:i:s', strtotime('-' . self::REUSE_AFTER_DAYS . ' days')));

        $byTable = [];
        foreach (array_keys(HeritagePublic::SECTIONS) as $table) {
            foreach (HeritagePublic::published($table, '1 = 1', [], 't.id') as $row) {
                if (($row['sensitivity'] ?? 'public') !== 'public') continue;
                if ($table === 'admin_units' && $row['unit_type'] === 'ward') continue; // no page of its own
                if (isset($used[$table . ':' . $row['id']])) continue;
                if (HeritagePublic::proseWords($table, $row) < self::MIN_WORDS) continue;
                $byTable[$table][] = [$table, $row];
            }
        }
        foreach ($byTable as &$list) shuffle($list);
        unset($list);

        $picked = [];
        $names = []; // e.g. Tiv the people and Tiv the language: not in the same batch
        while (count($picked) < $n && $byTable) {
            $tables = array_keys($byTable);
            shuffle($tables);
            foreach ($tables as $table) {
                $candidate = array_shift($byTable[$table]);
                if (!$byTable[$table]) unset($byTable[$table]);
                $name = mb_strtolower($candidate[1][HeritageRegistry::NAME_COLUMN[$table]]);
                if (isset($names[$name])) continue;
                $names[$name] = true;
                $picked[] = $candidate;
                if (count($picked) >= $n) break;
            }
        }
        return $picked;
    }

    /** Build (and unless $dryRun, save) one profile post for a record. */
    public static function prepare(string $table, array $row, string $postAt, bool $dryRun = false, ?int $userId = null): array
    {
        $name = (string) $row[HeritageRegistry::NAME_COLUMN[$table]];
        $kind = self::kindLabel($table, $row);
        $source = self::sourceText($table, $row);

        $ai = self::aiCaption($name, $kind, $source);
        $content = $ai ?? self::fallbackCaption($name, $source);
        $content['generated_by'] = $ai ? 'ai' : 'fallback';

        $recordUrl = HeritagePublic::recordUrl($table, $row);
        if ($dryRun) return $content + ['record_url' => $recordUrl];

        $links = new MarketingUtmLink();
        $code = $links->generateUniqueCode();
        $linkId = $links->create([
            'platform' => 'facebook',
            'destination_url' => $recordUrl,
            'utm_source' => 'facebook_profile',
            'utm_medium' => 'social',
            'utm_campaign' => 'nigeria_heritage',
            'utm_content' => $table . '-' . $row['id'],
            'short_code' => $code,
            'created_by' => $userId,
        ]);
        $goUrl = url('go/' . $code);

        $imagePath = null;
        try {
            $file = SocialGraphicGenerator::generate('nigeria_heritage', 'portrait',
                ['name' => $name, 'kind' => $kind, 'image_text' => $content['image_text']], $goUrl);
            $imagePath = 'marketing_images/' . basename($file);
        } catch (\Throwable $e) {
            // A post without an image can still go out; the admin page says so.
        }

        $posts = new MarketingProfilePost();
        $id = $posts->create([
            'entity_table' => $table,
            'entity_id' => (int) $row['id'],
            'record_name' => mb_substr($name, 0, 255),
            'caption' => $content['caption'],
            'hashtags' => mb_substr($content['hashtags'], 0, 500),
            'first_comment' => "Read the full story of {$name}, with sources, on the Nigeria Heritage Archive: {$goUrl}",
            'image_path' => $imagePath,
            'utm_link_id' => $linkId,
            'post_at' => $postAt,
            'status' => 'ready',
            'generated_by' => $content['generated_by'],
            'model_name' => $ai ? OLLAMA_MODEL : null,
        ]);
        return $content + ['id' => $id];
    }

    /** "State", "Local Government Area, Taraba State", "Ethnic group" … */
    private static function kindLabel(string $table, array $row): string
    {
        if ($table === 'admin_units') {
            if ($row['unit_type'] === 'lga' && $row['parent_id']) {
                $db = Database::getInstance();
                $state = $db->query('SELECT name FROM admin_units WHERE id = ' . (int) $row['parent_id'])->fetchColumn();
                return 'Local Government Area' . ($state ? ', ' . $state . ' State' : '');
            }
            return match ($row['unit_type']) {
                'state' => 'State of Nigeria',
                'federal_capital_territory' => 'Federal Capital Territory',
                default => ucwords(str_replace('_', ' ', (string) $row['unit_type'])),
            };
        }
        return HeritagePublic::SECTIONS[$table][2];
    }

    /** The record's own published prose, with section headings, cut to MAX_SOURCE_CHARS. */
    private static function sourceText(string $table, array $row): string
    {
        $parts = [];
        $summary = $row[HeritagePublic::SUMMARY[$table] ?? 'summary'] ?? '';
        if (trim((string) $summary) !== '') $parts[] = trim($summary);
        foreach (HeritagePublic::PROSE[$table] ?? [] as $col => $heading) {
            if (trim((string) ($row[$col] ?? '')) !== '') $parts[] = $heading . ":\n" . trim($row[$col]);
        }
        $text = strip_tags(implode("\n\n", $parts));
        return mb_strlen($text) > self::MAX_SOURCE_CHARS ? mb_substr($text, 0, self::MAX_SOURCE_CHARS) . '…' : $text;
    }

    /** First-person caption from the local model, or null if it failed or broke the rules. */
    private static function aiCaption(string $name, string $kind, string $source): ?array
    {
        if (!OLLAMA_ENABLED) return null;

        $system = "You write Facebook posts for the personal profile of a Nigerian heritage researcher who builds the "
            . "Nigeria Heritage Archive, an online record of Nigeria's states, peoples, languages and history.\n"
            . "Rules:\n"
            . "- Use ONLY facts stated in the SOURCE TEXT. Never add dates, numbers, names or claims that are not in it.\n"
            . "- Keep each fact exactly as the text gives it: do not merge separate facts, and keep who said it and when "
            . "(e.g. \"in a 2016 interview he said…\").\n"
            . "- Write in the first person as the researcher (\"I\", \"my research\"), warm and conversational, in clear Nigerian English.\n"
            . "- Do not invent personal experiences: no visits, travels, meetings or memories.\n"
            . "- If the text says something is disputed, uncertain or based on oral tradition, say so.\n"
            . "- Never rank or compare ethnic groups, never take political sides.\n"
            . "- No links, no URLs, no emojis in the first line.\n"
            . "- The caption has FOUR short paragraphs separated by blank lines, 100 to 170 words in total:\n"
            . "  1. a one-line hook: a striking fact from the text, or a question;\n"
            . "  2. and 3. the story, 2 to 3 sentences each, retelling the most interesting facts from the text;\n"
            . "  4. one question inviting readers to share what they know, or where they are from.\n"
            . "- No hashtags inside the caption.\n"
            . "Respond ONLY with a JSON object with exactly these keys: "
            . "\"caption\" (the post), \"image_text\" (one sentence of at most 22 words for the post image, a fact from the text), "
            . "\"hashtags\" (3 to 5 space-separated hashtags such as #NigerianHistory). No markdown fences, no commentary.";
        $user = "RECORD: {$name} ({$kind})\n\nSOURCE TEXT:\n{$source}\n\n"
            . "Write the four-paragraph post (100 to 170 words) about {$name} now, as JSON.";

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $raw = OllamaClient::generate($system, $user, 0.4); // low: retelling, not inventing
            if ($raw === null) return null; // model down: no point retrying now
            $decoded = json_decode(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw)), true);
            if (!is_array($decoded)) continue;

            $caption = trim((string) ($decoded['caption'] ?? ''));
            $imageText = trim((string) ($decoded['image_text'] ?? ''));
            $hashtags = implode(' ', array_map(fn($t) => '#' . ltrim($t, '#'),
                preg_split('/[\s,]+/', (string) ($decoded['hashtags'] ?? ''), -1, PREG_SPLIT_NO_EMPTY)));
            $caption = trim(preg_replace('/(\s#[\p{L}\p{N}_]+)+\s*$/u', '', $caption)); // hashtags go in their own field
            $words = str_word_count($caption);
            if ($caption === '' || $words < 60 || $words > 260) continue;
            if (preg_match('#https?://|www\.|\.com\b#i', $caption . ' ' . $imageText)) continue;
            if (!self::numbersFromSource($caption . ' ' . $imageText, $source)) continue;

            if ($imageText === '' || str_word_count($imageText) > 30) $imageText = self::firstSentence($source);
            if (!preg_match('/#\w/u', $hashtags)) $hashtags = self::defaultHashtags($name);
            return ['caption' => $caption, 'image_text' => $imageText, 'hashtags' => $hashtags];
        }
        return null;
    }

    /**
     * Guard against invented figures: every number of two or more digits in the
     * caption (years, populations, counts) must appear in the source text.
     */
    private static function numbersFromSource(string $caption, string $source): bool
    {
        $plain = str_replace(',', '', $source);
        preg_match_all('/\d[\d,]*\d/', $caption, $m);
        foreach ($m[0] as $num) {
            if (!str_contains($plain, str_replace(',', '', $num))) return false;
        }
        return true;
    }

    /** Plain caption from the record's own text, used when the model is unavailable. */
    private static function fallbackCaption(string $name, string $source): array
    {
        // The summary (first part, no heading) usually restates the overview: skip it when there is more.
        $parts = explode("\n\n", $source);
        if (count($parts) > 1 && !preg_match('/^[^\n]*:\n/', $parts[0])) array_shift($parts);
        $body = preg_replace('/^[^\n]*:\n/m', '', implode("\n\n", $parts)); // drop section headings
        $sentences = preg_split('/(?<=[.!?])\s+/u', trim(preg_replace('/\s+/', ' ', $body)));
        $text = '';
        foreach ($sentences as $s) {
            if ($text !== '' && str_word_count($text . ' ' . $s) > 90) break;
            $text .= ($text === '' ? '' : ' ') . $s;
        }
        return [
            'caption' => "From my Nigeria Heritage research: {$name}.\n\n{$text}\n\n"
                . "What do you know about {$name}? Tell me in the comments.",
            'image_text' => self::firstSentence($source),
            'hashtags' => self::defaultHashtags($name),
        ];
    }

    private static function firstSentence(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', preg_replace('/^[^\n]*:\n/m', '', $text)));
        $first = preg_split('/(?<=[.!?])\s+/u', $text)[0] ?? $text;
        $words = preg_split('/\s+/', $first);
        return count($words) > 30 ? implode(' ', array_slice($words, 0, 28)) . '…' : $first;
    }

    private static function defaultHashtags(string $name): string
    {
        $tag = preg_replace('/[^\p{L}\p{N}]+/u', '', ucwords($name));
        return trim('#Nigeria #NigerianHistory #NigeriaHeritage' . ($tag !== '' ? ' #' . $tag : ''));
    }
}
