<?php
/**
 * Timeline Controller - Public browse/detail pages for historical events
 */

require_once BASE_PATH . '/models/TimelineEvent.php';
require_once BASE_PATH . '/models/KnowledgeLink.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/EntryQuality.php';

class TimelineController extends Controller
{
    private TimelineEvent $model;
    private KnowledgeLink $linkModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TimelineEvent();
        $this->linkModel = new KnowledgeLink();
    }

    /** GET timeline — filterable/searchable browse (era, century, decade, category, keyword) */
    public function index(): void
    {
        $search = $this->get('q');
        $era      = $this->get('era');
        $century  = $this->get('century');
        $decade   = $this->get('decade');
        $category = $this->get('category');

        $filters = ['status' => 'published'];
        if ($era)      $filters['era'] = $era;
        if ($century)  $filters['century'] = $century;
        if ($decade)   $filters['decade'] = $decade;
        if ($category) $filters['category'] = $category;

        if ($search !== null && $search !== '') {
            $items = $this->model->search($search, [], 100);
            // Keyword search returns an unpaginated relevance-ranked set;
            // still respect any active era/century/decade/category chips.
            $items = array_values(array_filter($items, function ($item) use ($filters) {
                if ($item['status'] !== 'published') return false;
                if (!empty($filters['era']) && $item['era'] !== $filters['era']) return false;
                if (!empty($filters['century']) && $item['century'] !== $filters['century']) return false;
                if (!empty($filters['decade']) && $item['decade'] !== $filters['decade']) return false;
                return true;
            }));
            if ($category) {
                $categoryIds = array_flip(array_map(fn($e) => $e['id'], $items));
                foreach (array_keys($categoryIds) as $id) {
                    if (!in_array($category, $this->model->getCategories((int) $id), true)) {
                        unset($categoryIds[$id]);
                    }
                }
                $items = array_values(array_filter($items, fn($e) => isset($categoryIds[$e['id']])));
            }
            $total = count($items);
            $pagination = $this->paginate($total, 15);
            $items = array_slice($items, $pagination['offset'], $pagination['per_page']);
        } else {
            $total = $this->model->countFiltered($filters);
            $pagination = $this->paginate($total, 15);
            $items = $this->model->filter($filters, $pagination['per_page'], $pagination['offset'], 'year', 'ASC');
        }

        // Attach each event's categories for the card display.
        foreach ($items as &$item) {
            $item['categories'] = $this->model->getCategories((int) $item['id']);
        }
        unset($item);

        $qsParts = [];
        foreach (['era' => $era, 'century' => $century, 'decade' => $decade, 'category' => $category, 'q' => $search] as $k => $v) {
            if ($v !== null && $v !== '') $qsParts[$k] = $v;
        }
        $baseUrl = url('timeline') . (count($qsParts) ? '?' . http_build_query($qsParts) : '');

        $this->render('archive/timeline', [
            'title'         => 'Timeline | Tiv Heritage Archive',
            'description'   => 'An interactive, searchable chronology of Tiv history from earliest origins to the present day.',
            'items'         => $items,
            'pagination'    => $pagination,
            'search'        => $search ?? '',
            'searchActionUrl' => url('timeline'),
            'searchPlaceholder' => 'Search events, figures, places…',
            'baseUrl'       => $baseUrl,
            'eras'          => TimelineEvent::ERAS,
            'eraCounts'     => $this->model->countByEra(),
            'categories'    => TimelineEvent::CATEGORIES,
            'categoryCounts' => $this->model->getCategoryCounts(),
            'centuries'     => $this->model->getDistinctCenturies(),
            'decades'       => $this->model->getDistinctDecades(),
            'activeEra'     => $era,
            'activeCentury' => $century,
            'activeDecade'  => $decade,
            'activeCategory' => $category,
            'breadcrumb'    => [['label' => 'Timeline', 'url' => null]],
            'currentPage'   => 'archive',
        ]);
    }

    /** GET timeline-event/{id} */
    public function show(string $id): void
    {
        $item = $this->model->find((int) $id);

        if (!$item || ($item['status'] !== 'published' && !has_role('moderator'))) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        // National records live at /nigeria/events/{slug}.
        if (!empty($item['national_slug'])) {
            redirect301(nigeria_url('events/' . $item['national_slug']));
            return;
        }

        $canonical = SeoHelper::canonicalSlugPath('timeline-event', (int) $item['id'], $item['title']);
        if ($id !== substr($canonical, strlen('timeline-event') + 1)) {
            redirect301(url($canonical));
            return;
        }

        $this->model->incrementViews((int) $id);

        $breadcrumb = [
            ['label' => 'Timeline', 'url' => url('timeline')],
            ['label' => $item['title'], 'url' => null],
        ];

        $description = $item['short_summary'] ?? $item['title'];
        $ogImage     = SeoHelper::ogImage($item['image'] ?? null);

        // These are historical facts, not attendable/scheduled happenings —
        // schema.org/Event (and Google's Event rich result) targets things
        // like concerts people can plan around, requiring fields (offers,
        // performer, organizer) that don't apply to history and marking
        // decades-old events "EventScheduled" is inaccurate. CreativeWork
        // matches every other archive item type and has no required fields,
        // while temporalCoverage/contentLocation still carry the date/place.
        $eventSchema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CreativeWork',
            'name'        => $item['title'],
            'description' => $description,
            'url'         => url($canonical),
            'image'       => $ogImage,
        ];
        $startDate = $item['start_date'] ?? ($item['year'] ? (string) $item['year'] : null);
        if (!empty($startDate)) {
            $eventSchema['temporalCoverage'] = !empty($item['end_date'])
                ? $startDate . '/' . $item['end_date']
                : $startDate;
        }
        if (!empty($item['location'])) {
            $eventSchema['contentLocation'] = ['@type' => 'Place', 'name' => $item['location']];
        }

        $this->render('detail/timeline-event', [
            'noindex'     => $item['status'] !== 'published' || !EntryQuality::isIndexable('timeline_events', $item), // see services/EntryQuality.php
            'title'       => $item['title'] . ' - Timeline | Tiv Heritage Archive',
            'description' => $description,
            'ogImage'     => $ogImage,
            'jsonLd'      => SeoHelper::jsonLd([$eventSchema, SeoHelper::breadcrumbListSchema($breadcrumb)]),
            'item'        => $item,
            'categories'  => $this->model->getCategories((int) $id),
            'gallery'     => $this->model->getGallery((int) $id),
            'sources'     => $this->model->getSources((int) $id),
            'neighbors'   => $this->model->getChronologicalNeighbors((int) $id),
            'links'       => $this->linkModel->getRelatedItems('timeline_events', (int) $id),
            'breadcrumb'  => $breadcrumb,
            'currentPage' => 'archive',
        ]);
    }
}
