<?php
require_once BASE_PATH . '/models/MarketingUtmLink.php';
require_once BASE_PATH . '/models/MarketingLinkClick.php';

/**
 * Public, unauthenticated redirect + click-tracking endpoint. Every
 * generated caption/newsletter link points here (SITE_URL/go/{code})
 * instead of a raw content URL, so clicks are tracked even when the actual
 * sharing happens outside this app (e.g. an admin copy-pasting a caption
 * into Facebook by hand). Mirrors OutreachUnsubscribeController's public,
 * no-requireAdmin() pattern.
 */
class MarketingRedirectController extends Controller
{
    public function redirect(string $code): void
    {
        $links = new MarketingUtmLink();
        $link = $links->findByShortCode($code);

        if (!$link) {
            $this->render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $clicks = new MarketingLinkClick();
        $clicks->log(
            (int) $link['id'],
            Security::getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null
        );
        $links->incrementClicks((int) $link['id']);

        $destination = $link['destination_url'];
        $separator = str_contains($destination, '?') ? '&' : '?';
        $utmParams = http_build_query(array_filter([
            'utm_source' => $link['utm_source'],
            'utm_medium' => $link['utm_medium'],
            'utm_campaign' => $link['utm_campaign'],
            'utm_content' => $link['utm_content'],
        ]));

        header('Location: ' . $destination . $separator . $utmParams, true, 302);
        exit;
    }
}
