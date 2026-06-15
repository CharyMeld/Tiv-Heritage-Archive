<?php
/**
 * API Controller
 */

require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivPlant.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivFood.php';
require_once BASE_PATH . '/models/DailyWord.php';

class ApiController extends Controller
{
    /**
     * Search API endpoint
     */
    public function search(): void
    {
        ApiAuth::requireApiKey();
        $query = $this->get('q', '');
        $category = $this->get('category', '');

        if (strlen($query) < 2) {
            $this->json([
                'success' => false,
                'message' => 'Query must be at least 2 characters'
            ]);
            return;
        }

        $results = [];

        if ($category) {
            // Search specific category
            $results = $this->searchCategory($category, $query);
        } else {
            // Search all categories
            $results = array_merge(
                $this->searchCategory('names', $query),
                $this->searchCategory('proverbs', $query),
                $this->searchCategory('plants', $query),
                $this->searchCategory('festivals', $query),
                $this->searchCategory('foods', $query),
                $this->searchCategory('words', $query)
            );
        }

        // Limit results
        $results = array_slice($results, 0, 20);

        $this->json([
            'success' => true,
            'query' => $query,
            'count' => count($results),
            'results' => $results
        ]);
    }

    /**
     * Daily word API endpoint
     */
    public function dailyWord(): void
    {
        ApiAuth::requireApiKey();
        $model = new DailyWord();
        $word = $model->getToday();

        if (!$word) {
            $this->json([
                'success' => false,
                'message' => 'No word available'
            ], 404);
            return;
        }

        $this->json([
            'success' => true,
            'word' => [
                'id' => $word['id'],
                'tiv_word' => $word['tiv_word'],
                'english_meaning' => $word['english_meaning'],
                'part_of_speech' => $word['part_of_speech'],
                'pronunciation' => $word['pronunciation'],
                'example_tiv' => $word['example_tiv'],
                'example_english' => $word['example_english'],
                'url' => url('word/' . $word['id'])
            ]
        ]);
    }

    /**
     * Random proverb API endpoint
     */
    public function randomProverb(): void
    {
        ApiAuth::requireApiKey();
        $model = new TivProverb();
        $proverb = $model->getRandom();

        if (!$proverb) {
            $this->json([
                'success' => false,
                'message' => 'No proverbs available'
            ], 404);
            return;
        }

        $this->json([
            'success' => true,
            'proverb' => [
                'id' => $proverb['id'],
                'tiv_text' => $proverb['tiv_text'],
                'english_translation' => $proverb['english_translation'],
                'deeper_meaning' => $proverb['deeper_meaning'],
                'url' => url('proverb/' . $proverb['id'])
            ]
        ]);
    }

    /**
     * Search a specific category
     */
    private function searchCategory(string $category, string $query): array
    {
        $results = [];

        switch ($category) {
            case 'names':
                $model = new TivName();
                $items = $model->search($query, 10);
                foreach ($items as $item) {
                    $results[] = [
                        'title' => $item['tiv_name'],
                        'subtitle' => $item['english_meaning'],
                        'category' => 'Name',
                        'url' => url('name/' . $item['id'])
                    ];
                }
                break;

            case 'proverbs':
                $model = new TivProverb();
                $items = $model->search($query, 10);
                foreach ($items as $item) {
                    $results[] = [
                        'title' => mb_substr($item['tiv_text'], 0, 50) . (mb_strlen($item['tiv_text']) > 50 ? '...' : ''),
                        'subtitle' => mb_substr($item['english_translation'], 0, 50) . '...',
                        'category' => 'Proverb',
                        'url' => url('proverb/' . $item['id'])
                    ];
                }
                break;

            case 'plants':
                $model = new TivPlant();
                $items = $model->search($query, 10);
                foreach ($items as $item) {
                    $results[] = [
                        'title' => $item['tiv_name'],
                        'subtitle' => $item['english_name'] ?? $item['description'],
                        'category' => 'Plant',
                        'url' => url('plant/' . $item['id'])
                    ];
                }
                break;

            case 'festivals':
                $model = new TivFestival();
                $items = $model->search($query, 10);
                foreach ($items as $item) {
                    $results[] = [
                        'title' => $item['tiv_name'],
                        'subtitle' => $item['english_name'] ?? mb_substr($item['description'], 0, 50) . '...',
                        'category' => 'Festival',
                        'url' => url('festival/' . $item['id'])
                    ];
                }
                break;

            case 'foods':
                $model = new TivFood();
                $items = $model->search($query, 10);
                foreach ($items as $item) {
                    $results[] = [
                        'title' => $item['tiv_name'],
                        'subtitle' => $item['english_name'] ?? mb_substr($item['description'], 0, 50) . '...',
                        'category' => 'Food',
                        'url' => url('food/' . $item['id'])
                    ];
                }
                break;

            case 'words':
                $model = new DailyWord();
                $items = $model->search($query, 10);
                foreach ($items as $item) {
                    $results[] = [
                        'title' => $item['tiv_word'],
                        'subtitle' => $item['english_meaning'] . ' (' . $item['part_of_speech'] . ')',
                        'category' => 'Word',
                        'url' => url('word/' . $item['id'])
                    ];
                }
                break;
        }

        return $results;
    }
}
