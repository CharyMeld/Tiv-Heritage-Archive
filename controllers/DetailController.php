<?php
/**
 * Detail Controller - Individual item pages
 */

require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivAnimal.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/KnowledgeLink.php';

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

    public function name(string $id): void
    {
        $model = new TivName();
        $item  = $model->find((int) $id);

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_names', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/name', [
            'title'       => $item['tiv_name'] . ' - Tiv Name',
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

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_proverbs', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/proverb', [
            'title'       => 'Tiv Proverb',
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

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_plants', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/plant', [
            'title'       => $item['tiv_name'] . ' - Tiv Plant',
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

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_festivals', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/festival', [
            'title'       => $item['tiv_name'] . ' - Tiv Festival',
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

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_foods', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/food', [
            'title'       => $item['tiv_name'] . ' - Tiv Food',
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

        $model->incrementViews((int) $id);
        $ctx = $this->getContext('tiv_animals', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/animal', [
            'title'       => ($item['tiv_name'] ?? $item['name']) . ' - Tiv Animal',
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

        if (!$item) { $this->render('errors/404', ['title' => 'Not Found']); return; }

        $related = $model->getByPartOfSpeech($item['part_of_speech'], 5);
        $related = array_filter($related, fn($w) => $w['id'] != $id);
        $related = array_slice($related, 0, 4);

        $ctx = $this->getContext('daily_words', (int) $id, $item['source_id'] ?? null);

        $this->render('detail/word', [
            'title'       => $item['tiv_word'] . ' - Tiv Word',
            'item'        => $item,
            'related'     => $related,
            'source'      => $ctx['source'],
            'links'       => $ctx['links'],
            'currentPage' => 'archive',
        ]);
    }
}
