<?php
require_once BASE_PATH . '/models/MarketingPostCaption.php';
require_once BASE_PATH . '/services/FacebookGraphClient.php';
require_once BASE_PATH . '/services/InstagramGraphClient.php';

/**
 * Dispatches a single schedule row to the right platform client. Shared by
 * bin/marketing-process-schedule.php (cron) and
 * AdminMarketingFacebookController::retry() (manual "Publish Now"/"Retry")
 * so the two never drift on how a platform's post actually gets built.
 */
class SocialPublisher
{
    public static function publish(array $schedule, array $post, ?array $image): array
    {
        $captionModel = new MarketingPostCaption();
        $caption = $captionModel->getForPost((int) $schedule['post_id'])[$schedule['platform']] ?? null;
        $message = $caption['caption_text'] ?? $post['caption'] ?? '';
        $imageUrl = $image ? UPLOADS_URL . '/' . $image['file_path'] : null;

        switch ($schedule['platform']) {
            case 'facebook':
                $client = new FacebookGraphClient();
                if ($imageUrl) {
                    return $client->uploadPhoto(['image_url' => $imageUrl, 'caption' => $message]);
                }
                return $client->publishPost(['message' => $message]);

            case 'instagram':
                if (!$imageUrl) {
                    return ['success' => false, 'error' => 'Instagram requires an image and this post has none.'];
                }
                $client = new InstagramGraphClient();
                return $client->publishPhoto(['image_url' => $imageUrl, 'caption' => $message]);

            default:
                return ['success' => false, 'error' => "No publisher wired up for platform \"{$schedule['platform']}\" yet."];
        }
    }
}
