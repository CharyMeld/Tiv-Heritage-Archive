<?php
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/models/MarketingUtmLink.php';

/**
 * Resolves the real public detail-page URL for a generated post's source
 * item, and hands out a tracked SITE_URL/go/{code} link for it (reusing an
 * existing link per post+platform rather than minting a new one each time
 * a caption/image is regenerated).
 */
class MarketingLinkBuilder
{
    // Maps source_type => the route prefix used by SeoHelper::canonicalSlugPath()
    // for that content type's public detail page (confirmed against each
    // controller's actual route in index.php).
    private const ROUTE_PREFIX = [
        'word'              => 'word',
        'proverb'           => 'proverb',
        'name'              => 'name',
        'history'           => 'timeline-event',
        'historical_figure' => 'historical-figure',
        'festival'          => 'festival',
        'food'              => 'food',
        'plant'             => 'plant',
        'animal'            => 'animal',
    ];

    /**
     * Grammar rules have no individual permalink (they're accordion
     * sections on one page); lessons and references use a plain /{id}
     * route with no slug.
     */
    public static function destinationUrl(string $sourceType, array $item): string
    {
        $id = (int) ($item['id'] ?? 0);

        if ($sourceType === 'lesson') {
            return url('learn/' . $id);
        }
        if ($sourceType === 'reference') {
            return url('references/' . $id);
        }
        if ($sourceType === 'grammar') {
            return url('language/grammar');
        }
        if (isset(self::ROUTE_PREFIX[$sourceType]) && $id > 0) {
            $titleField = MARKETING_SOURCE_TYPES[$sourceType]['title_field'] ?? '';
            $name = (string) ($item[$titleField] ?? '');
            return url(SeoHelper::canonicalSlugPath(self::ROUTE_PREFIX[$sourceType], $id, $name));
        }

        return SITE_URL;
    }

    /**
     * Returns ['short_code' => ..., 'go_url' => SITE_URL/go/{code}] for the
     * given post+platform, reusing the existing link if one was already
     * created for this exact combination (so regenerating a caption/image
     * doesn't fragment click analytics across multiple short codes).
     */
    public static function linkFor(int $postId, string $sourceType, array $item, string $platform, ?int $userId): array
    {
        $links = new MarketingUtmLink();
        $existing = $links->findByPostAndPlatform($postId, $platform);
        if ($existing) {
            return $existing;
        }

        $code = $links->generateUniqueCode();
        $links->create([
            'post_id' => $postId,
            'platform' => $platform,
            'destination_url' => self::destinationUrl($sourceType, $item),
            'utm_source' => $platform,
            'utm_medium' => 'social',
            'utm_campaign' => $sourceType,
            'short_code' => $code,
            'created_by' => $userId,
        ]);

        return $links->findByShortCode($code);
    }
}
