<?php
/**
 * Archive Controller
 */

require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/BibleVerse.php';
require_once BASE_PATH . '/models/ContentItem.php';
require_once BASE_PATH . '/services/SeoHelper.php';

class ArchiveController extends Controller
{
    /**
     * Archive index - show all categories
     */
    public function index(): void
    {
        $search = $this->get('q');

        if ($search) {
            $this->searchAll($search);
            return;
        }

        $nameModel     = new TivName();
        $proverbModel  = new TivProverb();
        $plantModel    = new TivPlant();
        $festivalModel = new TivFestival();
        $foodModel     = new TivFood();
        $wordModel     = new DailyWord();
        $animalModel   = new TivAnimal();
        $bibleModel    = new BibleVerse();
        $contentModel  = new ContentItem();

        // Cache counts for 1 hour — they change rarely
        $counts = Cache::remember('archive_counts', 3600, function () use (
            $nameModel, $proverbModel, $plantModel, $festivalModel, $foodModel, $wordModel, $animalModel, $bibleModel, $contentModel
        ) {
            return [
                'names'        => $nameModel->count(),
                'proverbs'     => $proverbModel->count(),
                'plants'       => $plantModel->count(),
                'festivals'    => $festivalModel->count(),
                'foods'        => $foodModel->count(),
                'words'        => $wordModel->countActive(),
                'animals'      => $animalModel->count(),
                'bible'        => $bibleModel->count(),
                'documents'    => $contentModel->countBySubcategory('archive', 'documents'),
                'audio'        => $contentModel->countBySubcategory('archive', 'audio'),
                'publications' => $contentModel->countBySubcategory('archive', 'publications'),
            ];
        });

        $categories = [
            'names' => [
                'label'       => 'Tiv Names',
                'description' => 'Traditional names and their meanings',
                'icon'        => '&#128100;',
                'count'       => $counts['names'],
                'url'         => url('archive/names'),
            ],
            'proverbs' => [
                'label'       => 'Proverbs',
                'description' => 'Ancient wisdom passed through generations',
                'icon'        => '&#128221;',
                'count'       => $counts['proverbs'],
                'url'         => url('archive/proverbs'),
            ],
            'plants' => [
                'label'       => 'Plants',
                'description' => 'Medicinal and sacred cultural plants',
                'icon'        => '&#127807;',
                'count'       => $counts['plants'],
                'url'         => url('archive/plants'),
            ],
            'festivals' => [
                'label'       => 'Festivals',
                'description' => 'Cultural celebrations and traditions',
                'icon'        => '&#127881;',
                'count'       => $counts['festivals'],
                'url'         => url('archive/festivals'),
            ],
            'foods' => [
                'label'       => 'Foods',
                'description' => 'Traditional cuisine and recipes',
                'icon'        => '&#127858;',
                'count'       => $counts['foods'],
                'url'         => url('archive/foods'),
            ],
            'words' => [
                'label'       => 'Dictionary',
                'description' => 'Tiv words and their translations',
                'icon'        => '&#128172;',
                'count'       => $counts['words'],
                'url'         => url('archive/words'),
            ],
            'animals' => [
                'label'       => 'Animals',
                'description' => 'Animals in Tiv culture and their significance',
                'icon'        => '&#128062;',
                'count'       => $counts['animals'],
                'url'         => url('archive/animals'),
            ],
            'bible' => [
                'label'       => 'Bible',
                'description' => 'Icighan Bibilo — English &amp; Tiv',
                'icon'        => '&#128214;',
                'count'       => $counts['bible'],
                'url'         => url('bible'),
            ],
            'documents' => [
                'label'       => 'Documents',
                'description' => 'Historical manuscripts and written records',
                'icon'        => '&#128196;',
                'count'       => $counts['documents'],
                'url'         => url('archive/documents'),
            ],
            'audio' => [
                'label'       => 'Audio',
                'description' => 'Recordings of Tiv songs, speeches and oral traditions',
                'icon'        => '&#127911;',
                'count'       => $counts['audio'],
                'url'         => url('archive/audio'),
            ],
            'publications' => [
                'label'       => 'Publications',
                'description' => 'Research publications about Tiv culture and history',
                'icon'        => '&#128218;',
                'count'       => $counts['publications'],
                'url'         => url('archive/publications'),
            ],
        ];

        // Cache recent items for 30 minutes
        $rows = Cache::remember('archive_rows', 1800, function () use (
            $nameModel, $proverbModel, $plantModel, $festivalModel, $foodModel, $wordModel, $animalModel, $bibleModel, $contentModel, $categories
        ) {
            return [
                'names'        => ['meta' => $categories['names'],        'items' => $nameModel->recent(12)],
                'proverbs'     => ['meta' => $categories['proverbs'],     'items' => $proverbModel->recent(12)],
                'plants'       => ['meta' => $categories['plants'],       'items' => $plantModel->recent(12)],
                'festivals'    => ['meta' => $categories['festivals'],    'items' => $festivalModel->recentWithCover(12)],
                'foods'        => ['meta' => $categories['foods'],        'items' => $foodModel->recent(12)],
                'words'        => ['meta' => $categories['words'],        'items' => $wordModel->recent(12)],
                'animals'      => ['meta' => $categories['animals'],      'items' => $animalModel->recent(12)],
                'bible'        => ['meta' => $categories['bible'],        'items' => $bibleModel->getDailyWindow(12)],
                'documents'    => ['meta' => $categories['documents'],    'items' => $contentModel->recentBySubcategory('archive', 'documents', 12)],
                'audio'        => ['meta' => $categories['audio'],        'items' => $contentModel->recentBySubcategory('archive', 'audio', 12)],
                'publications' => ['meta' => $categories['publications'], 'items' => $contentModel->recentBySubcategory('archive', 'publications', 12)],
            ];
        });

        $totalEntries = array_sum(array_column($categories, 'count'));

        $this->render('archive/index', [
            'title'        => 'Explore Tiv Culture',
            'description'  => 'Discover Tiv names, proverbs, plants, festivals, foods, dictionary entries and the Bible',
            'categories'   => $categories,
            'rows'         => $rows,
            'totalEntries' => $totalEntries,
            'currentPage'  => 'archive',
        ]);
    }

    /**
     * Browse specific category
     */
    public function category(string $category): void
    {
        $search = $this->get('q');
        $filter = $this->get('filter');

        switch ($category) {
            case 'names':
                $this->browseNames($search, $filter);
                break;
            case 'proverbs':
                $this->browseProverbs($search, $filter);
                break;
            case 'plants':
                $this->browsePlants($search, $filter);
                break;
            case 'festivals':
                $this->browseFestivals($search);
                break;
            case 'foods':
                $this->browseFoods($search);
                break;
            case 'words':
                $this->browseWords($search, $filter);
                break;
            case 'animals':
                $this->browseAnimals($search, $this->get('type'));
                break;
            case 'documents':
                $this->browseDocuments($search);
                break;
            case 'audio':
                $this->browseAudio($search);
                break;
            case 'publications':
                $this->browsePublications($search);
                break;
            case 'videos':
                $this->redirect(url('learn'));
                break;
            default:
                $this->redirect(url('archive'));
        }
    }

    private function browseDocuments(?string $search): void
    {
        $model = new ContentItem();
        $total = $model->countBySubcategory('archive', 'documents');
        $pagination = $this->paginate($total);

        $items = $search
            ? $model->searchInSubcategory('archive', 'documents', $search)
            : $model->getBySubcategory('archive', 'documents', $pagination['per_page'], $pagination['offset']);

        $this->render('archive/documents', [
            'title'           => 'Documents | Tiv Archive',
            'description'     => 'Historical manuscripts, written records, and official documents from the Tiv people.',
            'items'           => $items,
            'pagination'      => $pagination,
            'search'          => $search,
            'currentPage'     => 'archive',
            'currentCategory' => 'documents',
        ]);
    }

    private function browseAudio(?string $search): void
    {
        $model = new ContentItem();
        $total = $model->countBySubcategory('archive', 'audio');
        $pagination = $this->paginate($total);

        $items = $search
            ? $model->searchInSubcategory('archive', 'audio', $search)
            : $model->getBySubcategory('archive', 'audio', $pagination['per_page'], $pagination['offset']);

        $this->render('archive/audio', [
            'title'           => 'Audio Recordings | Tiv Archive',
            'description'     => 'Recordings of Tiv songs, speeches, oral traditions, and language samples.',
            'items'           => $items,
            'pagination'      => $pagination,
            'search'          => $search,
            'currentPage'     => 'archive',
            'currentCategory' => 'audio',
        ]);
    }

    private function browsePublications(?string $search): void
    {
        $model = new ContentItem();
        $total = $model->countBySubcategory('archive', 'publications');
        $pagination = $this->paginate($total);

        $items = $search
            ? $model->searchInSubcategory('archive', 'publications', $search)
            : $model->getBySubcategory('archive', 'publications', $pagination['per_page'], $pagination['offset']);

        $this->render('archive/publications', [
            'title'           => 'Research Publications | Tiv Archive',
            'description'     => 'Academic and community research publications about Tiv language, culture, and history.',
            'items'           => $items,
            'pagination'      => $pagination,
            'search'          => $search,
            'currentPage'     => 'archive',
            'currentCategory' => 'publications',
        ]);
    }

    private function archivePlaceholder(string $category, string $title, string $description, string $icon): void
    {
        $this->render('content/archive-placeholder', [
            'title'               => $title . ' | Tiv Archive',
            'description'         => $description,
            'category'            => $category,
            'categoryTitle'       => $title,
            'categoryDescription' => $description,
            'categoryIcon'        => $icon,
            'currentPage'         => 'archive',
            'currentCategory'     => $category,
        ]);
    }

    /**
     * Browse names
     */
    private function browseNames(?string $search, ?string $filter): void
    {
        $model = new TivName();
        $total = $model->count();
        $pagination = $this->paginate($total);

        if ($search) {
            $items = $model->search($search);
        } elseif ($filter && in_array($filter, ['male', 'female', 'unisex'])) {
            $items = $model->getByGender($filter, $pagination['per_page'], $pagination['offset']);
            $total = $model->countByGender($filter);
            $pagination = $this->paginate($total);
        } else {
            $items = $model->getAllAlphabetically($pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/names', [
            'title' => 'Tiv Names',
            'description' => 'Browse Tiv names with their meanings, origin stories, and cultural usage context.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'currentPage' => 'archive',
            'currentCategory' => 'names'
        ]);
    }

    /**
     * Browse proverbs
     */
    private function browseProverbs(?string $search, ?string $filter): void
    {
        $model = new TivProverb();
        $total = $model->count();
        $pagination = $this->paginate($total);

        if ($search) {
            $items = $model->search($search);
        } elseif ($filter) {
            $items = $model->getByCategory($filter, $pagination['per_page'], $pagination['offset']);
        } else {
            $items = $model->paginate($pagination['per_page'], $pagination['offset'], 'created_at', 'DESC');
        }

        $categories = $model->getCategories();

        $this->render('archive/proverbs', [
            'title' => 'Tiv Proverbs',
            'description' => 'Explore traditional Tiv proverbs with their English translations, deeper meanings, and cultural context.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'categories' => $categories,
            'currentPage' => 'archive',
            'currentCategory' => 'proverbs'
        ]);
    }

    /**
     * Browse plants
     */
    private function browsePlants(?string $search, ?string $filter): void
    {
        $model = new TivPlant();
        $total = $model->count();
        $pagination = $this->paginate($total);

        if ($search) {
            $items = $model->search($search);
        } elseif ($filter === 'medicinal') {
            $items = $model->getMedicinal($pagination['per_page'], $pagination['offset']);
        } elseif ($filter === 'edible') {
            $items = $model->getEdible($pagination['per_page'], $pagination['offset']);
        } elseif ($filter === 'ritual') {
            $items = $model->getRitual($pagination['per_page'], $pagination['offset']);
        } else {
            $items = $model->getAllAlphabetically($pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/plants', [
            'title' => 'Tiv Plants',
            'description' => 'Discover Tiv plants and their medicinal, food, and ritual uses in traditional Tiv life.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'currentPage' => 'archive',
            'currentCategory' => 'plants'
        ]);
    }

    /**
     * Browse festivals
     */
    private function browseFestivals(?string $search): void
    {
        $model = new TivFestival();
        $total = $model->count();
        $pagination = $this->paginate($total);

        if ($search) {
            $items = $model->search($search);
        } else {
            $items = $model->getAllAlphabeticallyWithCover($pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/festivals', [
            'title' => 'Tiv Festivals',
            'description' => 'Explore Tiv festivals — their significance, timing, and traditional activities.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'currentPage' => 'archive',
            'currentCategory' => 'festivals'
        ]);
    }

    /**
     * Browse foods
     */
    private function browseFoods(?string $search): void
    {
        $model = new TivFood();
        $total = $model->count();
        $pagination = $this->paginate($total);

        if ($search) {
            $items = $model->search($search);
        } else {
            $items = $model->getAllAlphabetically($pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/foods', [
            'title' => 'Tiv Foods',
            'description' => 'Browse traditional Tiv foods, ingredients, preparation methods, and cultural significance.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'currentPage' => 'archive',
            'currentCategory' => 'foods'
        ]);
    }

    /**
     * Browse words (dictionary)
     */
    private function browseWords(?string $search, ?string $filter): void
    {
        $model = new DailyWord();
        $total = $model->countActive();
        $pagination = $this->paginate($total);

        if ($search) {
            $items = $model->search($search);
        } elseif ($filter) {
            $items = $model->getByPartOfSpeech($filter);
        } else {
            $items = $model->getAllAlphabetically($pagination['per_page'], $pagination['offset']);
        }

        $this->render('archive/words', [
            'title' => 'Tiv Dictionary',
            'description' => 'Search the Tiv dictionary for word meanings, pronunciation, IPA, tone, and usage examples.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'currentPage' => 'archive',
            'currentCategory' => 'words'
        ]);
    }

    /**
     * Browse animals
     */
    private function browseAnimals(?string $search, ?string $type = null): void
    {
        $model = new TivAnimal();
        $validTypes = ['wild', 'domestic', 'pet'];
        $type = in_array($type, $validTypes) ? $type : null;

        if ($search) {
            $items = $model->search($search);
            $total = count($items);
        } elseif ($type) {
            $total = $model->countByType($type);
            $pagination = $this->paginate($total);
            $items = $model->getByType($type, $pagination['per_page'], $pagination['offset']);
        } else {
            $total = $model->count();
            $pagination = $this->paginate($total);
            $items = $model->getAllAlphabetically($pagination['per_page'], $pagination['offset']);
        }

        if (!isset($pagination)) {
            $pagination = $this->paginate($total);
        }

        $this->render('archive/animals', [
            'title' => 'Tiv Animals',
            'description' => 'Discover animals in Tiv culture, their Tiv names, and their significance.',
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'currentPage' => 'archive',
            'currentCategory' => 'animals'
        ]);
    }

    /**
     * Search all categories
     */
    private function searchAll(string $search): void
    {
        $results = [
            'names' => (new TivName())->search($search, [], 10),
            'proverbs' => (new TivProverb())->search($search, [], 10),
            'plants' => (new TivPlant())->search($search, [], 10),
            'festivals' => (new TivFestival())->search($search, [], 10),
            'foods' => (new TivFood())->search($search, [], 10),
            'words' => (new DailyWord())->search($search, [], 10),
            'animals' => (new TivAnimal())->search($search, [], 10),
        ];

        $totalResults = array_sum(array_map('count', $results));

        $this->render('archive/search', [
            'title' => 'Search Results: ' . $search,
            'search' => $search,
            'results' => $results,
            'totalResults' => $totalResults,
            'currentPage' => 'archive'
        ]);
    }
}
