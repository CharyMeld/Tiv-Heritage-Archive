<?php

require_once BASE_PATH . '/models/LearningVideo.php';
require_once BASE_PATH . '/models/VideoComment.php';
require_once BASE_PATH . '/models/VideoReaction.php';

class LearnController extends Controller
{
    private LearningVideo $videoModel;
    private VideoComment  $commentModel;
    private VideoReaction $reactionModel;

    public function __construct()
    {
        parent::__construct();
        $this->videoModel    = new LearningVideo();
        $this->commentModel  = new VideoComment();
        $this->reactionModel = new VideoReaction();
    }

    public function index(): void
    {
        $category   = $this->get('category');
        $difficulty = $this->get('difficulty');

        $total      = $this->videoModel->countActive();
        $pagination = $this->paginate($total);

        if ($category) {
            $videos = $this->videoModel->getByCategory($category, $pagination['per_page']);
        } elseif ($difficulty) {
            $videos = $this->videoModel->getByDifficulty($difficulty, $pagination['per_page']);
        } else {
            $videos = $this->videoModel->getActive($pagination['per_page'], $pagination['offset']);
        }

        $categories = $this->videoModel->getCategories();

        $this->render('learn/index', [
            'title'             => 'Learn Tiv Language',
            'videos'            => $videos,
            'categories'        => $categories,
            'currentCategory'   => $category,
            'currentDifficulty' => $difficulty,
            'pagination'        => $pagination,
            'currentPage'       => 'learn'
        ]);
    }

    public function show(string $id): void
    {
        $video = $this->videoModel->find((int) $id);

        if (!$video || !$video['is_active']) {
            $this->flash('Video not found.', 'error');
            $this->redirect(url('learn'));
            return;
        }

        $relatedVideos = [];
        if ($video['category']) {
            $relatedVideos = $this->videoModel->getByCategory($video['category'], 4);
            $relatedVideos = array_filter($relatedVideos, fn($v) => $v['id'] != $id);
            $relatedVideos = array_slice($relatedVideos, 0, 3);
        }

        $videoUrl = url('learn/' . $id);
        $ogImage  = 'https://img.youtube.com/vi/' . $video['youtube_id'] . '/hqdefault.jpg';
        $ogDesc   = !empty($video['description'])
                      ? mb_substr(strip_tags($video['description']), 0, 200)
                      : 'Watch this Tiv language lesson on the Tiv Heritage Archive — ' . ucfirst($video['difficulty'] ?? '') . ' level.';

        $comments      = $this->commentModel->getForVideo((int) $id);
        $commentCount  = count($comments);
        $reactionCount = $this->reactionModel->countForVideo((int) $id);
        $hasReacted    = $this->reactionModel->hasReacted((int) $id, $this->reactionIdentifier());

        $this->render('learn/show', [
            'title'         => $video['title'] . ' — Learn Tiv Language',
            'description'   => $ogDesc,
            'ogImage'       => $ogImage,
            'ogUrl'         => $videoUrl,
            'ogType'        => 'video.other',
            'video'         => $video,
            'relatedVideos' => $relatedVideos,
            'comments'      => $comments,
            'commentCount'  => $commentCount,
            'reactionCount' => $reactionCount,
            'hasReacted'    => $hasReacted,
            'currentPage'   => 'learn'
        ]);
    }

    /** AJAX — increment view count on first actual play */
    public function recordView(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $input['_token'] ?? '';

        if (!Security::validateCSRFToken($token)) {
            $this->json(['ok' => false, 'error' => 'invalid token'], 403);
            return;
        }

        $video = $this->videoModel->find((int) $id);
        if (!$video || !$video['is_active']) {
            $this->json(['ok' => false], 404);
            return;
        }

        $this->videoModel->incrementViews((int) $id);
        $this->json(['ok' => true]);
    }

    /** AJAX — post a comment */
    public function postComment(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $input['_token'] ?? '';

        if (!Security::validateCSRFToken($token)) {
            $this->json(['ok' => false, 'error' => 'invalid token'], 403);
            return;
        }

        $video = $this->videoModel->find((int) $id);
        if (!$video || !$video['is_active']) {
            $this->json(['ok' => false, 'error' => 'video not found'], 404);
            return;
        }

        $body = trim($input['body'] ?? '');
        if (mb_strlen($body) < 2) {
            $this->json(['ok' => false, 'error' => 'Comment is too short.'], 422);
            return;
        }
        if (mb_strlen($body) > 2000) {
            $this->json(['ok' => false, 'error' => 'Comment is too long (max 2000 characters).'], 422);
            return;
        }

        $userId    = $this->user ? (int) $this->user['id'] : null;
        $guestName = null;
        $userName  = '';

        if ($userId) {
            $userName = $this->user['name'];
        } else {
            $guestName = trim($input['name'] ?? '');
            if (mb_strlen($guestName) < 2) {
                $this->json(['ok' => false, 'error' => 'Please enter your name.'], 422);
                return;
            }
            $guestName = mb_substr($guestName, 0, 100);
            $userName  = $guestName;
        }

        $commentId = $this->commentModel->add((int) $id, $userId, $guestName, $body);

        $this->json([
            'ok'        => true,
            'comment'   => [
                'id'         => $commentId,
                'name'       => $userName,
                'body'       => $body,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }

    /** AJAX — toggle like reaction */
    public function postReaction(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $input['_token'] ?? '';

        if (!Security::validateCSRFToken($token)) {
            $this->json(['ok' => false, 'error' => 'invalid token'], 403);
            return;
        }

        $video = $this->videoModel->find((int) $id);
        if (!$video || !$video['is_active']) {
            $this->json(['ok' => false], 404);
            return;
        }

        $liked = $this->reactionModel->toggle((int) $id, $this->reactionIdentifier());
        $count = $this->reactionModel->countForVideo((int) $id);

        $this->json(['ok' => true, 'liked' => $liked, 'count' => $count]);
    }

    /** Unique identifier per user or IP for reaction deduplication */
    private function reactionIdentifier(): string
    {
        if ($this->user) {
            return 'u' . $this->user['id'];
        }
        return 'i' . md5(Security::getClientIP());
    }
}
