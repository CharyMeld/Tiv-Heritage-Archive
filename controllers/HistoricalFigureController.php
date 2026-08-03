<?php
/**
 * Historical Figure Controller - Public browse/detail pages
 */

require_once BASE_PATH . '/models/HistoricalFigure.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/KnowledgeLink.php';
require_once BASE_PATH . '/services/SeoHelper.php';

class HistoricalFigureController extends Controller
{
    private HistoricalFigure $model;
    private Source $sourceModel;
    private KnowledgeLink $linkModel;

    private const FILTER_KEYS = [
        'category', 'subcategory', 'clan', 'district', 'local_government', 'state',
        'gender', 'occupation', 'religion', 'historical_period', 'title', 'birth_year', 'death_year',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model = new HistoricalFigure();
        $this->sourceModel = new Source();
        $this->linkModel = new KnowledgeLink();
    }

    /** GET historical-figures — landing (category cards) or global scoped search */
    public function index(): void
    {
        // Backward-compat: old flat query-string style ?category=Political+Leaders[&subcategory=...]
        $legacyCategory = $this->get('category');
        if ($legacyCategory && in_array($legacyCategory, HistoricalFigure::CATEGORIES, true)) {
            $target = 'historical-figures/' . HistoricalFigure::slugify($legacyCategory);
            $legacySub = $this->get('subcategory');
            if ($legacyCategory === 'Traditional Leadership' && $legacySub) {
                $groupLabel = HistoricalFigure::subcategoryGroupLabelFor($legacySub);
                if ($groupLabel) $target .= '/' . HistoricalFigure::slugify($groupLabel);
            }
            $carry = $_GET;
            unset($carry['category'], $carry['subcategory'], $carry['url']);
            $qs = http_build_query($carry);
            $this->redirect(url($target) . ($qs ? '?' . $qs : ''));
            return;
        }

        $search = $this->get('q');

        if ($search !== null && $search !== '') {
            $total = $this->model->countSearchScoped($search, []);
            $pagination = $this->paginate($total, 12);
            $items = $this->model->searchScoped($search, [], $pagination['per_page'], $pagination['offset']);

            $this->render('archive/historical-figures', [
                'title'                  => 'Search Results | Historical Figures | Tiv Heritage Archive',
                'description'            => 'Search results across all historical figures.',
                'mode'                   => 'search',
                'items'                  => $items,
                'pagination'             => $pagination,
                'search'                 => $search,
                'baseUrl'                => url('historical-figures'),
                'searchActionUrl'        => url('historical-figures'),
                'searchPlaceholder'      => 'Search all historical figures…',
                'breadcrumb'             => [['label' => 'Historical Figures', 'url' => null]],
                'sidebarData'            => $this->buildSidebarData(),
                'activeCategorySlug'     => null,
                'activeSubcategorySlug' => null,
                'currentPage'            => 'archive',
            ]);
            return;
        }

        $counts = Cache::remember('hf_category_counts', 3600, fn() => $this->model->countByCategory());
        $cards = array_map(fn($cat) => [
            'label'       => $cat,
            'slug'        => HistoricalFigure::slugify($cat),
            'icon'        => HistoricalFigure::CATEGORY_ICONS[$cat],
            'description' => HistoricalFigure::CATEGORY_DESCRIPTIONS[$cat],
            'count'       => $counts[$cat],
        ], HistoricalFigure::CATEGORIES);

        $this->render('archive/historical-figures', [
            'title'                  => "\u{1F934} Historical Figures | Tiv Heritage Archive",
            'description'            => 'Discover the men and women who have shaped the history, culture, leadership, religion, education, and development of the Tiv people.',
            'mode'                   => 'landing',
            'cards'                  => $cards,
            'search'                 => '',
            'searchActionUrl'        => url('historical-figures'),
            'searchPlaceholder'      => 'Search all historical figures…',
            'breadcrumb'             => [['label' => 'Historical Figures', 'url' => null]],
            'sidebarData'            => $this->buildSidebarData(),
            'activeCategorySlug'     => null,
            'activeSubcategorySlug' => null,
            'currentPage'            => 'archive',
        ]);
    }

    /** GET historical-figures/{category} — subcategory cards (Traditional Leadership) or figure list */
    public function category(string $category): void
    {
        $categorySlug = $category;
        $categoryName = HistoricalFigure::categoryFromSlug($categorySlug);
        if (!$categoryName) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $search = $this->get('q');
        $breadcrumb = [
            ['label' => 'Historical Figures', 'url' => url('historical-figures')],
            ['label' => $categoryName, 'url' => null],
        ];

        if ($categoryName === 'Traditional Leadership' && ($search === null || $search === '')) {
            $subCounts = $this->model->countBySubcategoryGroup();
            $subCards = [];
            foreach (HistoricalFigure::TRADITIONAL_SUBCATEGORY_GROUPS as $label => $values) {
                $subCards[] = [
                    'label' => $label,
                    'slug'  => HistoricalFigure::slugify($label),
                    'icon'  => HistoricalFigure::SUBCATEGORY_ICONS[$label],
                    'count' => $subCounts[$label],
                ];
            }
            $this->render('archive/historical-figures-category', [
                'title'                  => $categoryName . ' | Historical Figures | Tiv Heritage Archive',
                'description'            => HistoricalFigure::CATEGORY_DESCRIPTIONS[$categoryName],
                'mode'                   => 'subcategories',
                'category'               => $categoryName,
                'categoryIcon'           => HistoricalFigure::CATEGORY_ICONS[$categoryName],
                'categorySlug'           => $categorySlug,
                'subCards'               => $subCards,
                'search'                 => '',
                'searchActionUrl'        => url('historical-figures/' . $categorySlug),
                'searchPlaceholder'      => 'Search in ' . $categoryName . '…',
                'breadcrumb'             => $breadcrumb,
                'sidebarData'            => $this->buildSidebarData(),
                'activeCategorySlug'     => $categorySlug,
                'activeSubcategorySlug' => null,
                'currentPage'            => 'archive',
            ]);
            return;
        }

        $filters = ['status' => 'published', 'category' => $categoryName];
        foreach (self::FILTER_KEYS as $key) {
            if (in_array($key, ['category', 'subcategory'], true)) continue;
            $val = $this->get($key);
            if ($val !== null && $val !== '') $filters[$key] = $val;
        }

        if ($search !== null && $search !== '') {
            $scope = ['category' => $categoryName];
            $total = $this->model->countSearchScoped($search, $scope);
            $pagination = $this->paginate($total, 12);
            $items = $this->model->searchScoped($search, $scope, $pagination['per_page'], $pagination['offset']);
        } else {
            $total = $this->model->countFiltered($filters);
            $pagination = $this->paginate($total, 12);
            $items = $this->model->filter($filters, $pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/historical-figures-category', [
            'title'                  => $categoryName . ' | Historical Figures | Tiv Heritage Archive',
            'description'            => HistoricalFigure::CATEGORY_DESCRIPTIONS[$categoryName],
            'mode'                   => 'list',
            'category'               => $categoryName,
            'categoryIcon'           => HistoricalFigure::CATEGORY_ICONS[$categoryName],
            'categorySlug'           => $categorySlug,
            'subcategory'            => null,
            'items'                  => $items,
            'pagination'             => $pagination,
            'search'                 => $search,
            'filters'                => $filters,
            'periods'                => HistoricalFigure::HISTORICAL_PERIODS,
            'baseUrl'                => url('historical-figures/' . $categorySlug),
            'searchActionUrl'        => url('historical-figures/' . $categorySlug),
            'searchPlaceholder'      => 'Search in ' . $categoryName . '…',
            'breadcrumb'             => $breadcrumb,
            'sidebarData'            => $this->buildSidebarData(),
            'activeCategorySlug'     => $categorySlug,
            'activeSubcategorySlug' => null,
            'currentPage'            => 'archive',
        ]);
    }

    /** GET historical-figures/{category}/{subcategory} — figure list within a Traditional Leadership subcategory */
    public function subcategory(string $category, string $subcategory): void
    {
        $categorySlug = $category;
        $subcategorySlug = $subcategory;
        $categoryName = HistoricalFigure::categoryFromSlug($categorySlug);

        if (!$categoryName || $categoryName !== 'Traditional Leadership') {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }
        $group = HistoricalFigure::subcategoryGroupFromSlug($subcategorySlug);
        if (!$group) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $search = $this->get('q');
        $filters = ['status' => 'published', 'category' => $categoryName, 'subcategory' => $group['values']];
        foreach (self::FILTER_KEYS as $key) {
            if (in_array($key, ['category', 'subcategory'], true)) continue;
            $val = $this->get($key);
            if ($val !== null && $val !== '') $filters[$key] = $val;
        }

        if ($search !== null && $search !== '') {
            $scope = ['category' => $categoryName, 'subcategory' => $group['values']];
            $total = $this->model->countSearchScoped($search, $scope);
            $pagination = $this->paginate($total, 12);
            $items = $this->model->searchScoped($search, $scope, $pagination['per_page'], $pagination['offset']);
        } else {
            $total = $this->model->countFiltered($filters);
            $pagination = $this->paginate($total, 12);
            $items = $this->model->filter($filters, $pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/historical-figures-category', [
            'title'                  => $group['label'] . ' | ' . $categoryName . ' | Historical Figures | Tiv Heritage Archive',
            'description'            => HistoricalFigure::CATEGORY_DESCRIPTIONS[$categoryName],
            'mode'                   => 'list',
            'category'               => $categoryName,
            'categoryIcon'           => HistoricalFigure::CATEGORY_ICONS[$categoryName],
            'categorySlug'           => $categorySlug,
            'subcategory'            => $group['label'],
            'items'                  => $items,
            'pagination'             => $pagination,
            'search'                 => $search,
            'filters'                => $filters,
            'periods'                => HistoricalFigure::HISTORICAL_PERIODS,
            'baseUrl'                => url('historical-figures/' . $categorySlug . '/' . $subcategorySlug),
            'searchActionUrl'        => url('historical-figures/' . $categorySlug . '/' . $subcategorySlug),
            'searchPlaceholder'      => 'Search in ' . $group['label'] . '…',
            'breadcrumb'             => [
                ['label' => 'Historical Figures', 'url' => url('historical-figures')],
                ['label' => $categoryName, 'url' => url('historical-figures/' . $categorySlug)],
                ['label' => $group['label'], 'url' => null],
            ],
            'sidebarData'            => $this->buildSidebarData(),
            'activeCategorySlug'     => $categorySlug,
            'activeSubcategorySlug' => $subcategorySlug,
            'currentPage'            => 'archive',
        ]);
    }

    /** GET historical-figure/{id} */
    public function show(string $id): void
    {
        $item = $this->model->find((int) $id);

        if (!$item || ($item['status'] !== 'published' && !has_role('moderator'))) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $canonical = SeoHelper::canonicalSlugPath('historical-figure', (int) $item['id'], $item['english_name']);
        if ($id !== substr($canonical, strlen('historical-figure') + 1)) {
            redirect301(url($canonical));
            return;
        }

        $this->model->incrementViews((int) $id);

        $categorySlug = HistoricalFigure::slugify($item['category']);
        $breadcrumb = [
            ['label' => 'Historical Figures', 'url' => url('historical-figures')],
            ['label' => $item['category'], 'url' => url('historical-figures/' . $categorySlug)],
        ];
        if (!empty($item['subcategory'])) {
            $groupLabel = HistoricalFigure::subcategoryGroupLabelFor($item['subcategory']);
            $groupSlug = HistoricalFigure::slugify($groupLabel);
            $breadcrumb[] = ['label' => $groupLabel, 'url' => url('historical-figures/' . $categorySlug . '/' . $groupSlug)];
        }
        $breadcrumb[] = ['label' => $item['english_name'], 'url' => null];

        $source = !empty($item['source_id']) ? $this->sourceModel->find((int) $item['source_id']) : null;
        $links = $this->linkModel->getRelatedItems('historical_figures', (int) $id);

        $description = SeoHelper::describe(
            [$item['short_summary'] ?? null, $item['biography'] ?? null],
            "{$item['english_name']} — a notable figure in Tiv history."
        );
        $ogImage = SeoHelper::ogImage($item['image'] ?? null);

        $personSchema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => $item['english_name'],
            'description' => $description,
            'url'         => url($canonical),
            'image'       => $ogImage,
        ];
        if (!empty($item['title'])) {
            $personSchema['jobTitle'] = $item['title'];
        }
        if (!empty($item['place_of_birth'])) {
            $personSchema['birthPlace'] = $item['place_of_birth'];
        }
        if (!empty($item['birth_year'])) {
            $personSchema['birthDate'] = (string) $item['birth_year'];
        }
        if (!empty($item['death_year'])) {
            $personSchema['deathDate'] = (string) $item['death_year'];
        }

        $this->render('detail/historical-figure', [
            'title'       => $item['english_name'] . ' - Historical Figure',
            'description' => $description,
            'ogImage'     => $ogImage,
            'jsonLd'      => SeoHelper::jsonLd([$personSchema, SeoHelper::breadcrumbListSchema($breadcrumb)]),
            'item'        => $item,
            'gallery'     => $this->model->getGallery((int) $id),
            'related'     => $this->model->getRelated((int) $id, 4),
            'source'      => $source,
            'links'       => $links,
            'breadcrumb'  => $breadcrumb,
            'currentPage' => 'archive',
        ]);
    }

    /** Cached tree of all categories (with Traditional Leadership's subcategory groups nested) for the sidebar */
    private function buildSidebarData(): array
    {
        return Cache::remember('hf_sidebar_tree', 3600, function () {
            $catCounts = $this->model->countByCategory();
            $subCounts = $this->model->countBySubcategoryGroup();
            $tree = [];
            foreach (HistoricalFigure::CATEGORIES as $cat) {
                $entry = [
                    'label'         => $cat,
                    'slug'          => HistoricalFigure::slugify($cat),
                    'icon'          => HistoricalFigure::CATEGORY_ICONS[$cat],
                    'count'         => $catCounts[$cat],
                    'subcategories' => [],
                ];
                if ($cat === 'Traditional Leadership') {
                    foreach (HistoricalFigure::TRADITIONAL_SUBCATEGORY_GROUPS as $label => $values) {
                        $entry['subcategories'][] = [
                            'label' => $label,
                            'slug'  => HistoricalFigure::slugify($label),
                            'icon'  => HistoricalFigure::SUBCATEGORY_ICONS[$label],
                            'count' => $subCounts[$label],
                        ];
                    }
                }
                $tree[] = $entry;
            }
            return $tree;
        });
    }
}
