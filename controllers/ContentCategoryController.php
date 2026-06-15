<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/ContentItem.php';

class ContentCategoryController extends Controller
{
    private array $sections = [
        'language' => [
            'title'       => 'Language',
            'icon'        => '&#128172;',
            'description' => 'Explore the Tiv language through our dictionary, alphabet, structured lessons, and translation tools.',
            'color'       => '#5C3A21',
            'subcategories' => [
                'dictionary' => [
                    'label'       => 'Dictionary',
                    'icon'        => '&#128218;',
                    'description' => 'Explore thousands of Tiv words with English meanings, parts of speech, and usage examples.',
                    'redirect'    => 'archive/words',
                ],
                'alphabet' => [
                    'label'       => 'Alphabet',
                    'icon'        => '&#127279;',
                    'description' => 'Learn the Tiv alphabet, consonants, vowels, and tonal markers that form the language foundation.',
                    'placeholder' => true,
                ],
                'lessons' => [
                    'label'       => 'Lessons',
                    'icon'        => '&#127979;',
                    'description' => 'Step-by-step lessons for learning Tiv from beginner to advanced levels.',
                    'redirect'    => 'learn',
                ],
                'translation' => [
                    'label'       => 'Translation',
                    'icon'        => '&#127760;',
                    'description' => 'Translate words and phrases between English and Tiv using our translation engine.',
                    'redirect'    => 'translate',
                ],
            ],
        ],
        'literature' => [
            'title'       => 'Literature',
            'icon'        => '&#128212;',
            'description' => 'Discover the rich literary tradition of the Tiv people through folktales, proverbs, stories, and poems.',
            'color'       => '#7B4F2E',
            'subcategories' => [
                'folktales' => [
                    'label'       => 'Folktales',
                    'icon'        => '&#127919;',
                    'description' => 'Traditional Tiv folktales passed down through generations, preserving moral lessons and cultural values.',
                    'placeholder' => true,
                ],
                'proverbs' => [
                    'label'       => 'Proverbs',
                    'icon'        => '&#128221;',
                    'description' => 'Ancient Tiv proverbs carrying deep wisdom about life, relationships, and nature.',
                    'redirect'    => 'archive/proverbs',
                ],
                'stories' => [
                    'label'       => 'Stories',
                    'icon'        => '&#128196;',
                    'description' => 'Historical and contemporary stories written in or about Tiv language and culture.',
                    'placeholder' => true,
                ],
                'poems' => [
                    'label'       => 'Poems',
                    'icon'        => '&#128145;',
                    'description' => 'Tiv poetry spanning traditional oral verse and modern written poetry celebrating identity.',
                    'placeholder' => true,
                ],
            ],
        ],
        'culture' => [
            'title'       => 'Culture',
            'icon'        => '&#127981;',
            'description' => 'Explore Tiv cultural traditions, festivals, attire, and social customs that define Tiv identity.',
            'color'       => '#8B5E3C',
            'subcategories' => [
                'traditions' => [
                    'label'       => 'Traditions',
                    'icon'        => '&#127981;',
                    'description' => 'Documented Tiv cultural practices and customs passed down through generations.',
                    'placeholder' => true,
                ],
                'festivals' => [
                    'label'       => 'Festivals',
                    'icon'        => '&#127881;',
                    'description' => 'Vibrant Tiv festivals celebrating harvests, rites of passage, and community gatherings.',
                    'redirect'    => 'archive/festivals',
                ],
                'attire' => [
                    'label'       => 'Attire',
                    'icon'        => '&#128255;',
                    'description' => 'Traditional Tiv clothing, fabric patterns, and ceremonial dress expressing cultural identity.',
                    'placeholder' => true,
                ],
                'marriage-customs' => [
                    'label'       => 'Marriage Customs',
                    'icon'        => '&#128149;',
                    'description' => 'Tiv marriage rites, bride price traditions, ceremonies, and family customs.',
                    'placeholder' => true,
                ],
            ],
        ],
        'history' => [
            'title'       => 'History',
            'icon'        => '&#128336;',
            'description' => 'Trace the history of the Tiv people from their origins through migration, notable figures, and key events.',
            'color'       => '#6B4226',
            'subcategories' => [
                'origins' => [
                    'label'       => 'Origins',
                    'icon'        => '&#127758;',
                    'description' => 'Documented and oral accounts of the Tiv people\'s origins and ethnic roots.',
                    'placeholder' => true,
                ],
                'migration' => [
                    'label'       => 'Migration',
                    'icon'        => '&#128667;',
                    'description' => 'Historical movements of the Tiv people across the Benue Valley and beyond.',
                    'placeholder' => true,
                ],
                'historical-figures' => [
                    'label'       => 'Historical Figures',
                    'icon'        => '&#129332;',
                    'description' => 'Notable Tiv leaders, warriors, scholars, and cultural icons who shaped Tiv history.',
                    'placeholder' => true,
                ],
                'timeline' => [
                    'label'       => 'Timeline',
                    'icon'        => '&#128337;',
                    'description' => 'A chronological record of key events and milestones in Tiv history.',
                    'placeholder' => true,
                ],
            ],
        ],
    ];

    public function section(string $section): void
    {
        if (!isset($this->sections[$section])) {
            $this->redirect(url('/'));
            return;
        }

        $config = $this->sections[$section];

        $this->render('content/section', [
            'title'       => $config['title'] . ' | Tiv Heritage Archive',
            'description' => $config['description'],
            'section'     => $section,
            'config'      => $config,
            'currentPage' => $section,
        ]);
    }

    public function subcategory(string $section, string $sub): void
    {
        if (!isset($this->sections[$section])) {
            $this->redirect(url('/'));
            return;
        }

        $sConfig = $this->sections[$section];
        $subKey  = strtolower($sub);

        if (!isset($sConfig['subcategories'][$subKey])) {
            $this->redirect(url($section));
            return;
        }

        $subConfig = $sConfig['subcategories'][$subKey];

        if (!empty($subConfig['redirect'])) {
            $this->redirect(url($subConfig['redirect']));
            return;
        }

        // Alphabet gets its own dedicated view
        if ($section === 'language' && $subKey === 'alphabet') {
            $model = new ContentItem();
            $extra = $model->getBySubcategory('language', 'alphabet', 20, 0);
            $this->render('content/alphabet', [
                'title'         => 'Tiv Alphabet | Tiv Heritage Archive',
                'description'   => 'The Tiv alphabet — vowels, consonants, digraphs, and tonal markers.',
                'section'       => $section,
                'sectionConfig' => $sConfig,
                'sub'           => $subKey,
                'subConfig'     => $subConfig,
                'extraItems'    => $extra,
                'currentPage'   => 'language',
            ]);
            return;
        }

        // Show real content listing from content_items table
        $model  = new ContentItem();
        $search = $this->get('q');
        $total  = $model->countBySubcategory($section, $subKey);
        $pagination = $this->paginate($total, 12);

        $items = $search
            ? $model->searchInSubcategory($section, $subKey, $search)
            : $model->getBySubcategory($section, $subKey, 12, $pagination['offset']);

        $this->render('content/items', [
            'title'         => $subConfig['label'] . ' | Tiv Heritage Archive',
            'description'   => $subConfig['description'],
            'section'       => $section,
            'sectionConfig' => $sConfig,
            'sub'           => $subKey,
            'subConfig'     => $subConfig,
            'items'         => $items,
            'pagination'    => $pagination,
            'search'        => $search,
            'currentPage'   => $section,
        ]);
    }

    public function itemDetail(string $id): void
    {
        $model = new ContentItem();
        $item  = $model->find((int) $id);

        if (!$item || $item['status'] !== 'published') {
            $this->redirect(url('/'));
            return;
        }

        $model->incrementViews((int) $id);

        $sections = $this->sections;
        $sConfig  = $sections[$item['section']] ?? [];
        $subKey   = $item['subcategory'];
        $subConfig = $sConfig['subcategories'][$subKey] ?? ['label' => ContentItem::subcategoryLabel($subKey), 'icon' => '&#128196;'];

        $this->render('content/item-detail', [
            'title'       => $item['title'] . ' | Tiv Heritage Archive',
            'description' => $item['excerpt'] ?? '',
            'item'        => $item,
            'section'     => $item['section'],
            'sectionConfig' => $sConfig,
            'sub'         => $subKey,
            'subConfig'   => $subConfig,
            'currentPage' => $item['section'],
        ]);
    }
}
