<?php
/**
 * Detail Controller - Individual item pages
 */

require_once BASE_PATH . '/services/EntryQuality.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/KnowledgeLink.php';
require_once BASE_PATH . '/services/SeoHelper.php';

class DetailController extends Controller
{
    private Source $sourceModel;
    private KnowledgeLink $linkModel;

    public function __construct()
    {
        parent::__construct();
        $this->sourceModel = new Source();
        $this->linkModel   = new KnowledgeLink();
    }

    /**
     * Fetch source and knowledge links for an item
     */
    private function getContext(string $dbTable, int $id, ?int $sourceId): array
    {
        $source = ($sourceId) ? $this->sourceModel->find($sourceId) : null;
        $links  = $this->linkModel->getRelatedItems($dbTable, $id);
        return ['source' => $source, 'links' => $links];
    }

    /**
     * Builds the standard Archive > Category > Item breadcrumb trail
     * shared by every detail page.
     */
    private function breadcrumbFor(string $categoryLabel, string $categoryPath, string $itemLabel): array
    {
        return [
            ['label' => 'Archive', 'url' => url('archive')],
            ['label' => $categoryLabel, 'url' => url($categoryPath)],
            ['label' => $itemLabel, 'url' => null],
        ];
    }

    /**
     * 301-redirects to the canonical "{id}-{slug}" URL if the request's id
     * param doesn't already match it (e.g. bare numeric /word/482, a stale
     * slug after a rename, or a wrong slug). Returns true if a redirect was
     * issued (caller should stop processing).
     */
    private function enforceCanonicalSlug(string $prefix, string $requestedId, int $rowId, string $name): bool
    {
        $canonical = SeoHelper::canonicalSlugPath($prefix, $rowId, $name);
        if ($requestedId !== substr($canonical, strlen($prefix) + 1)) {
            redirect301(url($canonical));
            return true;
        }
        return false;
    }

    public function name(string $id): void
    {
        $model = new TivName();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        if ($this->enforceCanonicalSlug('name', $id, (int) $item['id'], $item['tiv_name'])) { return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_names', (int) $id, $item['source_id'] ?? null);

        $description = SeoHelper::describe(
            [$item['description'] ?? null, $item['origin_story'] ?? null],
            "{$item['tiv_name']} — a Tiv name meaning \"{$item['english_meaning']}\"."
        );
        $breadcrumb = $this->breadcrumbFor('Names', section_path('names'), $item['tiv_name']);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('name', (int) $id, $item['tiv_name']));

        $this->render('detail/name', [
            'noindex'     => !EntryQuality::isIndexable('tiv_names', $item), // thin records: see services/EntryQuality.php
            'title'       => $item['tiv_name'] . ' - Tiv Name',
            'description' => $description,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([
                [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CreativeWork',
                    'name'        => $item['tiv_name'],
                    'description' => $description,
                    'url'         => $canonicalUrl,
                    'inLanguage'  => 'tiv',
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'item'        => $item,
            'related'     => $model->getRelated((int) $id, 4),
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }

    public function proverb(string $id): void
    {
        $model = new TivProverb();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        $slugSource = SeoHelper::truncate($item['tiv_text'], 60);
        if ($this->enforceCanonicalSlug('proverb', $id, (int) $item['id'], $slugSource)) { return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_proverbs', (int) $id, $item['source_id'] ?? null);

        $description = SeoHelper::describe(
            [$item['deeper_meaning'] ?? null, $item['english_translation'] ?? null],
            'A traditional Tiv proverb with meaning and cultural context.'
        );
        $breadcrumb = $this->breadcrumbFor('Proverbs', section_path('proverbs'), $slugSource);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('proverb', (int) $id, $slugSource));

        $this->render('detail/proverb', [
            'noindex'     => !EntryQuality::isIndexable('tiv_proverbs', $item), // thin records: see services/EntryQuality.php
            'title'       => $item['tiv_text'] . ' - Tiv Proverb',
            'description' => $description,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([
                [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CreativeWork',
                    'name'        => $item['tiv_text'],
                    'description' => $description,
                    'url'         => $canonicalUrl,
                    'inLanguage'  => 'tiv',
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'item'        => $item,
            'related'     => $model->getRelated((int) $id, 3),
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }

    public function plant(string $id): void
    {
        $model = new TivPlant();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        if ($this->enforceCanonicalSlug('plant', $id, (int) $item['id'], $item['tiv_name'])) { return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_plants', (int) $id, $item['source_id'] ?? null);

        $description = SeoHelper::describe(
            [$item['description'] ?? null],
            "{$item['tiv_name']} — a Tiv plant, part of the Tiv Heritage Archive."
        );
        $ogImage    = SeoHelper::ogImage($item['image'] ?? null);
        $breadcrumb = $this->breadcrumbFor('Plants', section_path('plants'), $item['tiv_name']);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('plant', (int) $id, $item['tiv_name']));

        $this->render('detail/plant', [
            'noindex'     => !EntryQuality::isIndexable('tiv_plants', $item), // thin records: see services/EntryQuality.php
            'title'       => $item['tiv_name'] . ' - Tiv Plant',
            'description' => $description,
            'ogImage'     => $ogImage,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([
                [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CreativeWork',
                    'name'        => $item['tiv_name'],
                    'description' => $description,
                    'url'         => $canonicalUrl,
                    'image'       => $ogImage,
                    'inLanguage'  => 'tiv',
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'item'        => $item,
            'related'     => $model->getRelated((int) $id, 3),
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }

    public function festival(string $id): void
    {
        $model = new TivFestival();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        if ($this->enforceCanonicalSlug('festival', $id, (int) $item['id'], $item['tiv_name'])) { return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_festivals', (int) $id, $item['source_id'] ?? null);

        $description = SeoHelper::describe(
            [$item['description'] ?? null, $item['significance'] ?? null],
            "{$item['tiv_name']} — a Tiv festival, part of the Tiv Heritage Archive."
        );
        $ogImage    = SeoHelper::ogImage($item['image'] ?? null);
        $breadcrumb = $this->breadcrumbFor('Festivals', section_path('festivals'), $item['tiv_name']);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('festival', (int) $id, $item['tiv_name']));

        // Festivals recur on a traditional/agricultural calendar with no fixed
        // date we can put in a database — schema.org/Event requires a real
        // startDate, so marking these up as Event (rather than CreativeWork,
        // as used for every other archive item type) makes them permanently
        // "invalid" in Search Console. `timing` is descriptive text, not an
        // ISO date, so it goes in temporalCoverage, not startDate.
        $festivalSchema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CreativeWork',
            'name'        => $item['tiv_name'],
            'description' => $description,
            'url'         => $canonicalUrl,
            'image'       => $ogImage,
            'inLanguage'  => 'tiv',
        ];
        if (!empty($item['timing'])) {
            $festivalSchema['temporalCoverage'] = $item['timing'];
        }
        if (!empty($item['location'])) {
            $festivalSchema['contentLocation'] = ['@type' => 'Place', 'name' => $item['location']];
        }

        $this->render('detail/festival', [
            'noindex'     => !EntryQuality::isIndexable('tiv_festivals', $item), // thin records: see services/EntryQuality.php
            'title'       => $item['tiv_name'] . ' - Tiv Festival',
            'description' => $description,
            'ogImage'     => $ogImage,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([$festivalSchema, SeoHelper::breadcrumbListSchema($breadcrumb)]),
            'item'        => $item,
            'related'     => $model->getRelated((int) $id, 3),
            'gallery'     => $model->getGallery((int) $id),
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }

    public function food(string $id): void
    {
        $model = new TivFood();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        if ($this->enforceCanonicalSlug('food', $id, (int) $item['id'], $item['tiv_name'])) { return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_foods', (int) $id, $item['source_id'] ?? null);

        $description = SeoHelper::describe(
            [$item['description'] ?? null, $item['cultural_significance'] ?? null],
            "{$item['tiv_name']} — a Tiv food, part of the Tiv Heritage Archive."
        );
        $ogImage    = SeoHelper::ogImage($item['image'] ?? null);
        $breadcrumb = $this->breadcrumbFor('Foods', section_path('foods'), $item['tiv_name']);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('food', (int) $id, $item['tiv_name']));

        $this->render('detail/food', [
            'noindex'     => !EntryQuality::isIndexable('tiv_foods', $item), // thin records: see services/EntryQuality.php
            'title'       => $item['tiv_name'] . ' - Tiv Food',
            'description' => $description,
            'ogImage'     => $ogImage,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([
                [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CreativeWork',
                    'name'        => $item['tiv_name'],
                    'description' => $description,
                    'url'         => $canonicalUrl,
                    'image'       => $ogImage,
                    'inLanguage'  => 'tiv',
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'item'        => $item,
            'related'     => $model->getRelated((int) $id, 3),
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }

    public function animal(string $id): void
    {
        $model = new TivAnimal();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        $itemLabel = $item['tiv_name'] ?? $item['name'];
        if ($this->enforceCanonicalSlug('animal', $id, (int) $item['id'], $itemLabel)) { return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_animals', (int) $id, $item['source_id'] ?? null);

        $description = SeoHelper::describe(
            [$item['description'] ?? null, $item['cultural_use'] ?? null],
            "{$itemLabel} — a Tiv animal, part of the Tiv Heritage Archive."
        );
        $ogImage    = SeoHelper::ogImage($item['image'] ?? null);
        $breadcrumb = $this->breadcrumbFor('Animals', section_path('animals'), $itemLabel);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('animal', (int) $id, $itemLabel));

        $this->render('detail/animal', [
            'noindex'     => !EntryQuality::isIndexable('tiv_animals', $item), // thin records: see services/EntryQuality.php
            'title'       => $itemLabel . ' - Tiv Animal',
            'description' => $description,
            'ogImage'     => $ogImage,
            'breadcrumb'  => $breadcrumb,
            'jsonLd'      => SeoHelper::jsonLd([
                [
                    '@context'    => 'https://schema.org',
                    '@type'       => 'CreativeWork',
                    'name'        => $itemLabel,
                    'description' => $description,
                    'url'         => $canonicalUrl,
                    'image'       => $ogImage,
                    'inLanguage'  => 'tiv',
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'item'        => $item,
            'related'     => $model->getRelated((int) $id, 3),
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }

    public function word(string $id): void
    {
        $model   = new DailyWord();
        $item    = $model->find((int) $id);

        // Hidden (is_active = 0) entries are import fragments or retired words — not public.
        if (!$item || (int) $item['is_active'] !== 1) { $this->render('errors/404', ['title' => 'Not Found']); return; }
        if ($this->enforceCanonicalSlug('word', $id, (int) $item['id'], $item['tiv_word'])) { return; }

        $related = $model->getByPartOfSpeech($item['part_of_speech'], 5);
        $related = array_filter($related, fn($w) => $w['id'] != $id);
        $related = array_slice($related, 0, 4);

        $ctx = $this->getContext('daily_words', (int) $id, $item['source_id'] ?? null);

        $rootWord = !empty($item['root_word_id']) ? $model->find((int) $item['root_word_id']) : null;
        $derivedWords = $model->getDerivedWords((int) $id, 8);

        $wordRelations = array_filter(
            $ctx['links'],
            fn($link) => in_array($link['relation'], ['synonym', 'antonym', 'see_also'], true)
                && $link['table'] === 'daily_words'
        );

        $displayWord = SeoHelper::displayWord($item['tiv_word']);
        $description = SeoHelper::describe(
            [$item['figurative_meaning'] ?? null, $item['literal_meaning'] ?? null, $item['english_meaning'] ?? null],
            "Tiv word: {$displayWord}."
        );
        $breadcrumb = $this->breadcrumbFor('Dictionary', section_path('words'), $displayWord);
        $canonicalUrl = url(SeoHelper::canonicalSlugPath('word', (int) $id, $item['tiv_word']));

        $this->render('detail/word', [
            'title'         => $displayWord . ' - Tiv Word',
            'description'   => $description,
            'breadcrumb'    => $breadcrumb,
            'jsonLd'        => SeoHelper::jsonLd([
                [
                    '@context'         => 'https://schema.org',
                    '@type'            => 'DefinedTerm',
                    'name'             => $displayWord,
                    'description'      => $description,
                    'url'              => $canonicalUrl,
                    'inDefinedTermSet' => url(section_path('words')),
                    'inLanguage'       => 'tiv',
                ],
                SeoHelper::breadcrumbListSchema($breadcrumb),
            ]),
            'item'          => $item,
            'related'       => $related,
            'source'        => $ctx['source'],
            'links'         => $ctx['links'],
            'rootWord'      => $rootWord,
            'derivedWords'  => $derivedWords,
            'wordRelations' => $wordRelations,
            'currentPage'   => 'archive',
            // A bare word + meaning (no example or notes) is too thin to stand
            // as its own search result; keep it for visitors, not for Google.
            'noindex'       => !EntryQuality::isIndexable('daily_words', $item), // thin records: see services/EntryQuality.php
        ]);
    }
}
