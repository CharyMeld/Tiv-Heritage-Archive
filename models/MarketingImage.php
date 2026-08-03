<?php
require_once BASE_PATH . '/core/Model.php';

class MarketingImage extends Model
{
    protected string $table = 'marketing_images';

    protected array $fillable = [
        'post_id', 'template_type', 'orientation', 'file_path', 'width', 'height', 'created_by',
    ];

    public const TEMPLATES = [
        'word_of_day'       => 'Word of the Day',
        'proverb_of_day'    => 'Proverb of the Day',
        'historical_figure' => 'Historical Figure',
        'festival'          => 'Festival',
        'food'              => 'Traditional Food',
        'plant'             => 'Plant',
        'animal'            => 'Animal',
        'timeline'          => 'Timeline',
        'quote'             => 'Quote',
    ];

    // Best-fit image template for each of the 12 archive source types, used
    // to auto-select a template when generating without manual admin input
    // (the cron autopilot, and as the picker's default selection). Four
    // source types (name/grammar/lesson/reference) have no dedicated
    // template — they reuse the closest existing layout, with the eyebrow
    // label overridden to name the actual content type.
    public const AUTO_TEMPLATE_FOR_SOURCE = [
        'word'              => 'word_of_day',
        'proverb'           => 'proverb_of_day',
        'name'              => 'word_of_day',
        'history'           => 'timeline',
        'historical_figure' => 'historical_figure',
        'festival'          => 'festival',
        'food'              => 'food',
        'plant'             => 'plant',
        'animal'            => 'animal',
        'grammar'           => 'quote',
        'lesson'            => 'quote',
        'reference'         => 'quote',
    ];

    public const ORIENTATIONS = [
        'square'    => ['label' => 'Square (1080×1080)',    'width' => 1080, 'height' => 1080],
        'portrait'  => ['label' => 'Portrait (1080×1350)',  'width' => 1080, 'height' => 1350],
        'landscape' => ['label' => 'Landscape (1200×630)',  'width' => 1200, 'height' => 630],
    ];

    public function getForPost(int $postId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE post_id = ? ORDER BY created_at DESC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function getAll(int $limit = 25, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, p.headline FROM {$this->table} i
             LEFT JOIN marketing_posts p ON p.id = i.post_id
             ORDER BY i.created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}
