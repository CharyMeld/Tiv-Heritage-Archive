<?php
/**
 * Entry quality gate for single-record pages (word, name, proverb, plant, food,
 * festival, animal, historical figure, timeline event).
 *
 * Most archive records carry a few words of their own text (median: 5 words for a
 * dictionary entry, 5 for a name), so a page per record is "thin" by Google's
 * standards and was the reason AdSense rejected the site for low value content.
 * A record page is indexable — listed in the sitemap, without noindex, eligible for
 * ads — only when the record's own descriptive fields reach MIN_WORDS. Thinner
 * records stay fully readable (and crawlable, noindex,follow); their text reaches
 * search through the full-text collection pages (CollectionController) instead.
 *
 * The same rule decides the page's robots tag and its sitemap entry, so the sitemap
 * never lists a page that marks itself noindex.
 */

if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

class EntryQuality
{
    /** Own words a record needs for its page to be indexable. */
    public const MIN_WORDS = 150;

    /** Descriptive fields per table: the record's own text, not labels or related lists. */
    public const FIELDS = [
        'daily_words'        => ['tiv_word', 'english_meaning', 'alternate_meaning', 'example_tiv', 'example_english',
                                 'literal_meaning', 'figurative_meaning', 'usage_notes'],
        'tiv_names'          => ['tiv_name', 'english_meaning', 'description', 'origin_story', 'usage_context'],
        'tiv_proverbs'       => ['tiv_text', 'english_translation', 'deeper_meaning', 'usage_context'],
        'tiv_plants'         => ['tiv_name', 'english_name', 'scientific_name', 'description', 'medicinal_uses',
                                 'food_uses', 'ritual_uses', 'cultivation'],
        'tiv_foods'          => ['tiv_name', 'english_name', 'description', 'ingredients', 'preparation_method',
                                 'serving_suggestions', 'cultural_significance'],
        'tiv_festivals'      => ['tiv_name', 'english_name', 'description', 'significance', 'timing', 'duration',
                                 'activities', 'location'],
        'tiv_animals'        => ['tiv_name', 'name', 'description', 'cultural_use', 'symbolic_meaning'],
        'historical_figures' => ['short_summary', 'biography', 'early_life', 'education', 'career',
                                 'leadership_service', 'achievements', 'historical_significance', 'legacy'],
        'timeline_events'    => ['short_summary', 'description', 'historical_significance', 'causes', 'consequences'],
    ];

    /** Word count of a record's own descriptive text. */
    public static function words(string $table, array $row): int
    {
        $text = [];
        foreach (self::FIELDS[$table] ?? [] as $field) {
            $value = trim(strip_tags((string) ($row[$field] ?? '')));
            if ($value !== '') {
                $text[] = $value;
            }
        }
        if (!$text) {
            return 0;
        }
        // Unicode-aware: Tiv text uses letters such as ô that str_word_count() splits on.
        return count(preg_split('/\s+/u', implode(' ', $text), -1, PREG_SPLIT_NO_EMPTY));
    }

    public static function isIndexable(string $table, array $row): bool
    {
        return self::words($table, $row) >= self::MIN_WORDS;
    }

    /** Comma-separated column list for SELECTs that feed isIndexable(). */
    public static function columns(string $table): string
    {
        return implode(', ', self::FIELDS[$table]);
    }
}
