<?php
/**
 * Learn Controller - Tiv Language Learning Videos
 */

require_once BASE_PATH . '/models/LearningVideo.php';

class LearnController extends Controller
{
    private LearningVideo $videoModel;

    public function __construct()
    {
        parent::__construct();
        $this->videoModel = new LearningVideo();
    }

    /**
     * Learning videos index page
     */
    public function index(): void
    {
        $category = $this->get('category');
        $difficulty = $this->get('difficulty');

        $total = $this->videoModel->countActive();
        $pagination = $this->paginate($total);

        // Get videos based on filters
        if ($category) {
            $videos = $this->videoModel->getByCategory($category, $pagination['per_page']);
        } elseif ($difficulty) {
            $videos = $this->videoModel->getByDifficulty($difficulty, $pagination['per_page']);
        } else {
            $videos = $this->videoModel->getActive($pagination['per_page'], $pagination['offset']);
        }

        $categories = $this->videoModel->getCategories();

        $this->render('learn/index', [
            'title' => 'Learn Tiv Language',
            'videos' => $videos,
            'categories' => $categories,
            'currentCategory' => $category,
            'currentDifficulty' => $difficulty,
            'pagination' => $pagination,
            'currentPage' => 'learn'
        ]);
    }

    /**
     * Single video page
     */
    public function show(string $id): void
    {
        $video = $this->videoModel->find((int) $id);

        if (!$video || !$video['is_active']) {
            $this->flash('Video not found.', 'error');
            $this->redirect(url('learn'));
            return;
        }

        // Increment view count
        $this->videoModel->incrementViews((int) $id);

        // Get related videos from same category
        $relatedVideos = [];
        if ($video['category']) {
            $relatedVideos = $this->videoModel->getByCategory($video['category'], 4);
            // Remove current video from related
            $relatedVideos = array_filter($relatedVideos, fn($v) => $v['id'] != $id);
            $relatedVideos = array_slice($relatedVideos, 0, 3);
        }

        $videoUrl   = url('learn/' . $id);
        $ogImage    = 'https://img.youtube.com/vi/' . $video['youtube_id'] . '/hqdefault.jpg';
        $ogDesc     = !empty($video['description'])
                        ? mb_substr(strip_tags($video['description']), 0, 200)
                        : 'Watch this Tiv language lesson on the Tiv Heritage Archive — ' . ucfirst($video['difficulty'] ?? '') . ' level.';

        $this->render('learn/show', [
            'title'       => $video['title'] . ' — Learn Tiv Language',
            'description' => $ogDesc,
            'ogImage'     => $ogImage,
            'ogUrl'       => $videoUrl,
            'ogType'      => 'video.other',
            'video'       => $video,
            'relatedVideos' => $relatedVideos,
            'currentPage' => 'learn'
        ]);
    }
}
