<?php
/**
 * Full-text collection pages: /collections/...
 *
 * Most archive records are too short to stand as pages of their own (see
 * services/EntryQuality.php), so search engines and AdSense saw thousands of thin
 * pages. These pages present the same records in full, grouped the way a reader
 * would look for them (dictionary by letter, names by gender, proverbs and plants
 * in numbered parts, animals/foods/festivals whole), each one a substantial page.
 * Every entry keeps its anchor (#e{id}) and a link to its own record page.
 */

require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/EntryQuality.php';

class CollectionController extends Controller
{
    /** A collection page is indexable once its entries carry this much text. */
    private const MIN_PAGE_WORDS = 300;

    private const WORDS_PER_PAGE    = 150;
    private const PROVERBS_PER_PAGE = 40;
    private const PLANTS_PER_PAGE   = 40;

    private const GENDERS = ['male' => 'Male', 'female' => 'Female', 'unisex' => 'Unisex'];

    /* ─────────────────────────── Hub ─────────────────────────── */

    public function index(): void
    {
        $db = \Database::getInstance();

        $letters = self::dictionaryLetters();
        $names   = $db->query("SELECT gender, COUNT(*) n FROM tiv_names GROUP BY gender")->fetchAll(\PDO::FETCH_KEY_PAIR);
        $counts  = [
            'words'     => array_sum($letters),
            'names'     => array_sum($names),
            'proverbs'  => (int) $db->query("SELECT COUNT(*) FROM tiv_proverbs")->fetchColumn(),
            'plants'    => (int) $db->query("SELECT COUNT(*) FROM tiv_plants")->fetchColumn(),
            'animals'   => (int) $db->query("SELECT COUNT(*) FROM tiv_animals")->fetchColumn(),
            'foods'     => (int) $db->query("SELECT COUNT(*) FROM tiv_foods")->fetchColumn(),
            'festivals' => (int) $db->query("SELECT COUNT(*) FROM tiv_festivals")->fetchColumn(),
        ];

        $breadcrumb = [
            ['label' => 'Archive', 'url' => url('archive')],
            ['label' => 'Collections', 'url' => null],
        ];

        $this->render('collections/index', [
            'title'       => 'Collections — Full Text | ' . SITE_NAME,
            'description' => 'Read the whole Tiv Heritage Archive in full: the Tiv–English dictionary letter by letter, Tiv names, proverbs, plants, animals, foods and festivals.',
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([SeoHelper::breadcrumbListSchema($breadcrumb)]),
            'letters'     => $letters,
            'names'       => $names,
            'counts'      => $counts,
            'pages'       => [
                'proverbs' => (int) ceil($counts['proverbs'] / self::PROVERBS_PER_PAGE),
                'plants'   => (int) ceil($counts['plants'] / self::PLANTS_PER_PAGE),
            ],
            'currentPage' => 'archive',
        ]);
    }

    /* ─────────────────────────── Dictionary ─────────────────────────── */

    public function dictionary(string $letter, string $page = '1'): void
    {
        $this->respond(self::buildDictionary($letter, $page));
    }

    private static function buildDictionary(string $letter, string $page): ?array
    {
        $letter  = strtolower($letter);
        $letters = self::dictionaryLetters();
        if (!preg_match('/^[a-z]$/', $letter) || empty($letters[$letter])) {
            return null;
        }
        $total = $letters[$letter];
        [$pageNo, $pages] = self::pageNumber($page, $total, self::WORDS_PER_PAGE);
        if ($pageNo === null) {
            return null;
        }

        $stmt = \Database::getInstance()->prepare(
            "SELECT id, tiv_word, english_meaning, alternate_meaning, part_of_speech, pronunciation, tone,
                    example_tiv, example_english, literal_meaning, figurative_meaning, usage_notes, dialect_region
             FROM daily_words
             WHERE is_active = 1 AND LOWER(LEFT(TRIM(tiv_word), 1)) = ?
             ORDER BY tiv_word, id
             LIMIT " . self::WORDS_PER_PAGE . " OFFSET " . (($pageNo - 1) * self::WORDS_PER_PAGE)
        );
        $stmt->execute([$letter]);

        $entries = [];
        foreach ($stmt->fetchAll() as $r) {
            $chips = array_filter([ucfirst((string) $r['part_of_speech']), $r['pronunciation'] ? '/' . $r['pronunciation'] . '/' : null,
                                   $r['tone'] ? 'Tone: ' . $r['tone'] : null, $r['dialect_region'] ? 'Dialect: ' . $r['dialect_region'] : null]);
            $entries[] = self::entry((int) $r['id'], 'word', $r['tiv_word'], $r['english_meaning'], $chips, [
                'Also means'         => $r['alternate_meaning'],
                'Example'            => self::example($r['example_tiv'], $r['example_english']),
                'Literal meaning'    => $r['literal_meaning'],
                'Figurative meaning' => $r['figurative_meaning'],
                'Usage'              => $r['usage_notes'],
            ]);
        }

        $upper = strtoupper($letter);
        $part  = $pages > 1 ? " (part {$pageNo} of {$pages})" : '';
        $intro = "Every Tiv word in the archive's dictionary that begins with “{$upper}” — {$total} " . ($total === 1 ? 'entry' : 'entries')
               . ", in alphabetical order{$part}. Each entry gives the English meaning and part of speech, and, where the archive records them, "
               . 'the pronunciation, an example sentence in Tiv with its English translation, and notes on usage. '
               . 'Select a word to open its own page with related words and sources.';

        $nav = [];
        foreach ($letters as $l => $n) {
            $nav[] = ['label' => strtoupper($l), 'url' => url("collections/dictionary/{$l}"), 'active' => $l === $letter];
        }

        return ([
            'heading'    => "Tiv Dictionary: {$upper}",
            'eyebrow'    => 'Tiv–English dictionary',
            'title'      => "Tiv Words Beginning with {$upper}" . ($pageNo > 1 ? " — Part {$pageNo}" : '') . ' | Tiv–English Dictionary',
            'description'=> "Tiv–English dictionary: {$total} Tiv words beginning with {$upper}, with meanings, parts of speech and example sentences.",
            'intro'      => $intro,
            'entries'    => $entries,
            'crumb'      => ['Dictionary', 'words', "Letter {$upper}"],
            'path'       => "collections/dictionary/{$letter}",
            'pageNo'     => $pageNo,
            'pages'      => $pages,
            'nav'        => $nav,
            'navLabel'   => 'Letters',
        ]);
    }

    /* ─────────────────────────── Names ─────────────────────────── */

    public function names(string $gender = 'all'): void
    {
        $this->respond(self::buildNames($gender));
    }

    private static function buildNames(string $gender): ?array
    {
        $gender = strtolower($gender);
        $all    = $gender === 'all';
        if (!$all && !isset(self::GENDERS[$gender])) {
            return null;
        }
        $stmt = \Database::getInstance()->prepare(
            "SELECT id, tiv_name, english_meaning, gender, pronunciation, description, origin_story, usage_context
             FROM tiv_names" . ($all ? '' : ' WHERE gender = ?') . " ORDER BY tiv_name, id"
        );
        $stmt->execute($all ? [] : [$gender]);

        $entries = [];
        foreach ($stmt->fetchAll() as $r) {
            $chips = array_filter([$all ? (self::GENDERS[$r['gender']] ?? null) : null, $r['pronunciation'] ? '/' . $r['pronunciation'] . '/' : null]);
            $entries[] = self::entry((int) $r['id'], 'name', $r['tiv_name'], $r['english_meaning'], $chips, [
                'About the name' => $r['description'],
                'Origin'         => $r['origin_story'],
                'When it is used'=> $r['usage_context'],
            ]);
        }
        if (!$entries) {
            return null;
        }

        $label = $all ? '' : self::GENDERS[$gender];
        $count = count($entries);
        $intro = $all
            ? "All {$count} Tiv names in the archive, in alphabetical order, with their English meanings and whether each is given to boys, girls or both. "
              . 'Where the archive records it, each name also carries a note on what it expresses, the story behind it and the occasions when it is given.'
            : "All {$count} " . strtolower($label) . ' Tiv names in the archive, in alphabetical order, with their English meanings. '
              . 'Where the archive records it, each name also carries a note on what it expresses, the story behind it and the occasions when it is given.';

        $nav = [['label' => 'All names', 'url' => url('collections/names'), 'active' => $all]];
        foreach (self::GENDERS as $g => $l) {
            $nav[] = ['label' => $l, 'url' => url("collections/names/{$g}"), 'active' => $g === $gender];
        }

        return ([
            'heading'     => $all ? 'Tiv Names and Their Meanings' : "{$label} Tiv Names and Their Meanings",
            'eyebrow'     => 'Tiv names',
            'title'       => ($all ? 'Tiv Names and Their Meanings' : "{$label} Tiv Names and Their Meanings") . ' | ' . SITE_NAME,
            'description' => $all
                ? "{$count} Tiv names with their English meanings, origins and the occasions they are given."
                : "{$count} {$label} Tiv names with their English meanings, origins and the occasions they are given.",
            'intro'       => $intro,
            'entries'     => $entries,
            'crumb'       => ['Names', 'names', $all ? null : "{$label} names"],
            'path'        => $all ? 'collections/names' : "collections/names/{$gender}",
            'pageNo'      => 1,
            'pages'       => 1,
            'nav'         => $nav,
            'navLabel'    => 'Names',
            // Gender pages repeat entries of the all-names page: kept for readers, not indexed.
            'filtered'    => !$all,
        ]);
    }

    /* ─────────────────────────── Proverbs ─────────────────────────── */

    public function proverbs(string $page = '1'): void
    {
        $this->respond(self::buildProverbs($page));
    }

    private static function buildProverbs(string $page): ?array
    {
        $db    = \Database::getInstance();
        $total = (int) $db->query("SELECT COUNT(*) FROM tiv_proverbs")->fetchColumn();
        [$pageNo, $pages] = self::pageNumber($page, $total, self::PROVERBS_PER_PAGE);
        if ($pageNo === null) {
            return null;
        }
        $rows = $db->query(
            "SELECT id, tiv_text, english_translation, deeper_meaning, usage_context, category
             FROM tiv_proverbs ORDER BY tiv_text, id
             LIMIT " . self::PROVERBS_PER_PAGE . " OFFSET " . (($pageNo - 1) * self::PROVERBS_PER_PAGE)
        )->fetchAll();

        $entries = [];
        foreach ($rows as $r) {
            $chips = array_filter([$r['category'] ? ucfirst($r['category']) : null]);
            $entries[] = self::entry((int) $r['id'], 'proverb', '“' . $r['tiv_text'] . '”', $r['english_translation'], $chips, [
                'Meaning'          => $r['deeper_meaning'],
                'When it is said'  => $r['usage_context'],
            ], true);
        }

        $intro = "Tiv proverbs from the archive with their English translations — part {$pageNo} of {$pages}, {$total} proverbs in all, in alphabetical order of the Tiv text. "
               . 'Where the archive records them, each proverb is followed by its deeper meaning and the situations in which it is used.';

        return ([
            'heading'     => "Tiv Proverbs and Their Meanings — Part {$pageNo}",
            'eyebrow'     => 'Tiv proverbs',
            'title'       => "Tiv Proverbs and Their Meanings — Part {$pageNo} of {$pages} | " . SITE_NAME,
            'description' => "Tiv proverbs with English translations and explanations (part {$pageNo} of {$pages}).",
            'intro'       => $intro,
            'entries'     => $entries,
            'crumb'       => ['Proverbs', 'proverbs', "Part {$pageNo}"],
            'path'        => 'collections/proverbs',
            'pageNo'      => $pageNo,
            'pages'       => $pages,
            'nav'         => [],
            'navLabel'    => '',
        ]);
    }

    /* ─────────────────────────── Plants ─────────────────────────── */

    public function plants(string $page = '1'): void
    {
        $this->respond(self::buildPlants($page));
    }

    private static function buildPlants(string $page): ?array
    {
        $db    = \Database::getInstance();
        $total = (int) $db->query("SELECT COUNT(*) FROM tiv_plants")->fetchColumn();
        [$pageNo, $pages] = self::pageNumber($page, $total, self::PLANTS_PER_PAGE);
        if ($pageNo === null) {
            return null;
        }
        $rows = $db->query(
            "SELECT id, tiv_name, english_name, scientific_name, description, medicinal_uses, food_uses, ritual_uses, cultivation
             FROM tiv_plants ORDER BY tiv_name, id
             LIMIT " . self::PLANTS_PER_PAGE . " OFFSET " . (($pageNo - 1) * self::PLANTS_PER_PAGE)
        )->fetchAll();

        $entries = [];
        foreach ($rows as $r) {
            $chips = array_filter([$r['scientific_name'] ? $r['scientific_name'] : null]);
            $entries[] = self::entry((int) $r['id'], 'plant', $r['tiv_name'], $r['english_name'], $chips, [
                'Description'    => $r['description'],
                'Medicinal uses' => $r['medicinal_uses'],
                'As food'        => $r['food_uses'],
                'Ritual uses'    => $r['ritual_uses'],
                'Cultivation'    => $r['cultivation'],
            ]);
        }

        $first = $rows ? $rows[0]['tiv_name'] : '';
        $last  = $rows ? $rows[count($rows) - 1]['tiv_name'] : '';
        $intro = "Plants known by Tiv names — part {$pageNo} of {$pages} ({$first} to {$last}), {$total} plants in all. "
               . 'Each entry gives the English and scientific names where they are known, a description, and what the archive records of the plant’s medicinal, food and ritual uses and how it is grown.';

        return ([
            'heading'     => "Tiv Plants and Their Uses — Part {$pageNo}",
            'eyebrow'     => 'Tiv plants',
            'title'       => "Tiv Plants and Their Uses — Part {$pageNo} of {$pages} | " . SITE_NAME,
            'description' => "Tiv plant names with English and scientific names and their medicinal, food and ritual uses (part {$pageNo} of {$pages}).",
            'intro'       => $intro,
            'entries'     => $entries,
            'crumb'       => ['Plants', 'plants', "Part {$pageNo}"],
            'path'        => 'collections/plants',
            'pageNo'      => $pageNo,
            'pages'       => $pages,
            'nav'         => [],
            'navLabel'    => '',
        ]);
    }

    /* ─────────────────────────── Animals / Foods / Festivals ─────────────────────────── */

    public function animals(): void
    {
        $this->respond(self::buildAnimals());
    }

    private static function buildAnimals(): ?array
    {
        $rows = \Database::getInstance()->query(
            "SELECT id, tiv_name, name, animal_type, description, cultural_use, symbolic_meaning
             FROM tiv_animals ORDER BY COALESCE(NULLIF(tiv_name, ''), name), id"
        )->fetchAll();
        $entries = [];
        foreach ($rows as $r) {
            $entries[] = self::entry((int) $r['id'], 'animal', $r['tiv_name'] ?: $r['name'], $r['tiv_name'] ? $r['name'] : '',
                array_filter([$r['animal_type'] ? ucfirst($r['animal_type']) : null]), [
                'Description'      => $r['description'],
                'In Tiv life'      => $r['cultural_use'],
                'Symbolic meaning' => $r['symbolic_meaning'],
            ]);
        }
        return self::single('animals', 'Animals in Tiv Culture', 'Tiv animals', $entries,
            'All ' . count($entries) . ' animals recorded in the archive, with their Tiv and English names, a description, and what the archive records of their place in Tiv life and their symbolic meaning.',
            'Animals in Tiv culture: Tiv and English names, descriptions, cultural uses and symbolic meanings.',
            ['Animals', 'animals']);
    }

    public function foods(): void
    {
        $this->respond(self::buildFoods());
    }

    private static function buildFoods(): ?array
    {
        $rows = \Database::getInstance()->query(
            "SELECT id, tiv_name, english_name, category, description, ingredients, preparation_method, serving_suggestions, cultural_significance
             FROM tiv_foods ORDER BY tiv_name, id"
        )->fetchAll();
        $entries = [];
        foreach ($rows as $r) {
            $entries[] = self::entry((int) $r['id'], 'food', $r['tiv_name'], $r['english_name'],
                array_filter([$r['category'] ? ucfirst($r['category']) : null]), [
                'Description'           => $r['description'],
                'Ingredients'           => $r['ingredients'],
                'How it is prepared'    => $r['preparation_method'],
                'How it is served'      => $r['serving_suggestions'],
                'Cultural significance' => $r['cultural_significance'],
            ]);
        }
        return self::single('foods', 'Traditional Tiv Foods', 'Tiv foods', $entries,
            'All ' . count($entries) . ' traditional Tiv foods in the archive, each with its ingredients, how it is prepared and served, and its place in Tiv life.',
            'Traditional Tiv foods with ingredients, preparation, serving and cultural significance.',
            ['Foods', 'foods']);
    }

    public function festivals(): void
    {
        $this->respond(self::buildFestivals());
    }

    private static function buildFestivals(): ?array
    {
        $rows = \Database::getInstance()->query(
            "SELECT id, tiv_name, english_name, festival_type, description, significance, timing, duration, activities, location
             FROM tiv_festivals ORDER BY tiv_name, id"
        )->fetchAll();
        $entries = [];
        foreach ($rows as $r) {
            $entries[] = self::entry((int) $r['id'], 'festival', $r['tiv_name'], $r['english_name'],
                array_filter([$r['festival_type'] ? ucfirst($r['festival_type']) : null, $r['timing'] ?: null, $r['duration'] ?: null]), [
                'Description'  => $r['description'],
                'Significance' => $r['significance'],
                'Activities'   => $r['activities'],
                'Where'        => $r['location'],
            ]);
        }
        return self::single('festivals', 'Tiv Festivals', 'Tiv festivals', $entries,
            'All ' . count($entries) . ' Tiv festivals in the archive, with when and where they take place, what happens during them and what they mean.',
            'Tiv festivals: timing, location, activities and significance.',
            ['Festivals', 'festivals']);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** [letter => active word count] for letters a–z that have words. */
    private static function dictionaryLetters(): array
    {
        return Cache::remember('collections_dictionary_letters', 3600, function () {
            $rows = \Database::getInstance()->query(
                "SELECT LOWER(LEFT(TRIM(tiv_word), 1)) l, COUNT(*) n FROM daily_words
                 WHERE is_active = 1 GROUP BY l ORDER BY l"
            )->fetchAll(\PDO::FETCH_KEY_PAIR);
            return array_filter(array_map('intval', $rows), fn($n, $l) => preg_match('/^[a-z]$/', (string) $l) && $n > 0, ARRAY_FILTER_USE_BOTH);
        });
    }

    /** [page, pages] for a 1-based page string, or [null, pages] when out of range. */
    private static function pageNumber(string $page, int $total, int $perPage): array
    {
        $pages = max(1, (int) ceil($total / $perPage));
        if (!ctype_digit($page) || (int) $page < 1 || (int) $page > $pages || $total === 0) {
            return [null, $pages];
        }
        return [(int) $page, $pages];
    }

    private static function example(?string $tiv, ?string $english): ?string
    {
        $tiv     = trim((string) $tiv);
        $english = trim((string) $english);
        if ($tiv === '' && $english === '') return null;
        if ($tiv === '') return $english;
        return $english === '' ? $tiv : "{$tiv} — “{$english}”";
    }

    private static function entry(int $id, string $prefix, string $title, ?string $subtitle, array $chips, array $fields, bool $italicTitle = false): array
    {
        $plainTitle = trim($title, '“”');
        $fields = array_filter(array_map(fn($v) => trim((string) $v), $fields), fn($v) => $v !== '');
        return [
            'id'       => $id,
            'anchor'   => 'e' . $id,
            'title'    => $title,
            'italic'   => $italicTitle,
            'subtitle' => trim((string) $subtitle),
            'chips'    => array_values($chips),
            'fields'   => $fields,
            'url'      => url(SeoHelper::canonicalSlugPath($prefix, $id, $plainTitle)),
        ];
    }

    private static function single(string $key, string $heading, string $eyebrow, array $entries, string $intro, string $description, array $crumb): ?array
    {
        if (!$entries) {
            return null;
        }
        return ([
            'heading'     => $heading,
            'eyebrow'     => $eyebrow,
            'title'       => $heading . ' | ' . SITE_NAME,
            'description' => $description,
            'intro'       => $intro,
            'entries'     => $entries,
            'crumb'       => [$crumb[0], $crumb[1], null],
            'path'        => "collections/{$key}",
            'pageNo'      => 1,
            'pages'       => 1,
            'nav'         => [],
            'navLabel'    => '',
        ]);
    }

    /** Words of text a built page carries (intro plus every entry). */
    private static function words(array $p): int
    {
        $words = str_word_count($p['intro']);
        foreach ($p['entries'] as $e) {
            $words += count(preg_split('/\s+/u', trim($e['title'] . ' ' . $e['subtitle'] . ' ' . implode(' ', $e['fields'])), -1, PREG_SPLIT_NO_EMPTY));
        }
        return $words;
    }

    private function respond(?array $p): void
    {
        if ($p === null) {
            $this->notFound();
            return;
        }
        $words = self::words($p);

        $pageUrl = function (int $n) use ($p): string {
            $base = $p['path'];
            if (in_array($base, ['collections/proverbs', 'collections/plants'], true) || str_starts_with($base, 'collections/dictionary/')) {
                return url($n === 1 ? $base : "{$base}/{$n}");
            }
            return url($base);
        };

        $breadcrumb = [['label' => 'Archive', 'url' => url('archive')]];
        $breadcrumb[] = ['label' => $p['crumb'][0], 'url' => $p['crumb'][2] === null ? null : url(section_path($p['crumb'][1]))];
        if ($p['crumb'][2] !== null) {
            $breadcrumb[] = ['label' => $p['crumb'][2], 'url' => null];
        }
        $canonical = $pageUrl($p['pageNo']);

        $this->render('collections/show', [
            'title'       => $p['title'],
            'description' => $p['description'],
            'noindex'     => $words < self::MIN_PAGE_WORDS || !empty($p['filtered']),
            'ogUrl'       => $canonical,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([
                [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CollectionPage',
                    'name'        => $p['heading'],
                    'description' => $p['description'],
                    'url'         => $canonical,
                    'inLanguage'  => ['en', 'tiv'],
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'heading'     => $p['heading'],
            'eyebrow'     => $p['eyebrow'],
            'intro'       => $p['intro'],
            'entries'     => $p['entries'],
            'pageNo'      => $p['pageNo'],
            'pages'       => $p['pages'],
            'prevUrl'     => $p['pageNo'] > 1 ? $pageUrl($p['pageNo'] - 1) : null,
            'nextUrl'     => $p['pageNo'] < $p['pages'] ? $pageUrl($p['pageNo'] + 1) : null,
            'pageUrl'     => $pageUrl,
            'nav'         => $p['nav'],
            'navLabel'    => $p['navLabel'],
            'searchUrl'   => url('archive/' . $p['crumb'][1]),
            'searchLabel' => [
                'words' => 'Search the dictionary…', 'names' => 'Search names…', 'proverbs' => 'Search proverbs…',
                'plants' => 'Search plants…', 'animals' => 'Search animals…', 'foods' => 'Search foods…', 'festivals' => 'Search festivals…',
            ][$p['crumb'][1]] ?? 'Search…',
            'currentPage' => 'archive',
        ]);
    }

    private function notFound(): void
    {
        $this->render('errors/404', ['title' => 'Not Found', 'noindex' => true]);
    }

    /* ─────────────────────────── Sitemap ─────────────────────────── */

    /**
     * Collection URLs for SitemapController: every page that passes the same
     * MIN_PAGE_WORDS rule the page itself applies, so no listed URL is noindex.
     */
    public static function sitemapPaths(): array
    {
        $candidates = [];
        foreach (self::dictionaryLetters() as $l => $n) {
            for ($i = 1, $pages = (int) ceil($n / self::WORDS_PER_PAGE); $i <= $pages; $i++) {
                $candidates[] = [fn() => self::buildDictionary($l, (string) $i), "collections/dictionary/{$l}" . ($i > 1 ? "/{$i}" : '')];
            }
        }
        $candidates[] = [fn() => self::buildNames('all'), 'collections/names'];
        $db = \Database::getInstance();
        foreach (['proverbs' => ['tiv_proverbs', self::PROVERBS_PER_PAGE], 'plants' => ['tiv_plants', self::PLANTS_PER_PAGE]] as $key => [$table, $per]) {
            $pages = (int) ceil((int) $db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn() / $per);
            for ($i = 1; $i <= $pages; $i++) {
                $builder = $key === 'proverbs' ? fn() => self::buildProverbs((string) $i) : fn() => self::buildPlants((string) $i);
                $candidates[] = [$builder, "collections/{$key}" . ($i > 1 ? "/{$i}" : '')];
            }
        }
        $candidates[] = [fn() => self::buildAnimals(), 'collections/animals'];
        $candidates[] = [fn() => self::buildFoods(), 'collections/foods'];
        $candidates[] = [fn() => self::buildFestivals(), 'collections/festivals'];

        $paths = ['collections'];
        foreach ($candidates as [$build, $path]) {
            $p = $build();
            if ($p !== null && self::words($p) >= self::MIN_PAGE_WORDS) {
                $paths[] = $path;
            }
        }
        return $paths;
    }
}
