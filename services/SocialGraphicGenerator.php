<?php
/**
 * SocialGraphicGenerator — pure GD branded social graphic renderer.
 *
 * services/ImageVariantGenerator.php only resizes/re-encodes existing
 * uploads and has no text/canvas drawing capability, so this is a new,
 * separate service. Uses the DejaVu fonts already installed system-wide
 * (confirmed present on both dev and production, used by the Python
 * fpdf2 PDF generation scripts) rather than bundling new font assets.
 */
class SocialGraphicGenerator
{
    private const FONT_BOLD = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
    private const FONT_REGULAR = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

    // Same palette as OutreachMailer::wrapHtml(), for visual consistency
    // between marketing graphics and outreach emails.
    private const COLOR_BROWN = [0x5C, 0x3A, 0x21];
    private const COLOR_GOLD  = [0xC8, 0xA9, 0x51];
    private const COLOR_CREAM = [0xF7, 0xF4, 0xEE];
    private const COLOR_DARK  = [0x2D, 0x1B, 0x0E];
    private const COLOR_WHITE = [0xFF, 0xFF, 0xFF];

    private const ORIENTATIONS = [
        'square'    => [1080, 1080],
        'portrait'  => [1080, 1350],
        'landscape' => [1200, 630],
    ];

    /**
     * Maps each of the 9 template types to the eyebrow label shown at the
     * top and which source fields become the title/subtitle/body.
     */
    private const TEMPLATE_FIELD_MAP = [
        'word_of_day'       => ['eyebrow' => 'WORD OF THE DAY',    'title' => 'tiv_word',    'subtitle' => 'english_meaning', 'body' => 'alternate_meaning'],
        'proverb_of_day'    => ['eyebrow' => 'PROVERB OF THE DAY', 'title' => 'tiv_text',    'subtitle' => null,              'body' => 'english_translation'],
        'historical_figure' => ['eyebrow' => 'HISTORICAL FIGURE',  'title' => 'english_name','subtitle' => 'title',           'body' => 'short_summary'],
        'festival'          => ['eyebrow' => 'FESTIVAL',           'title' => 'tiv_name',    'subtitle' => 'english_name',    'body' => 'description'],
        'food'              => ['eyebrow' => 'TRADITIONAL FOOD',   'title' => 'tiv_name',    'subtitle' => 'english_name',    'body' => 'description'],
        'plant'             => ['eyebrow' => 'PLANT',              'title' => 'tiv_name',    'subtitle' => 'scientific_name', 'body' => 'description'],
        'animal'            => ['eyebrow' => 'ANIMAL',             'title' => 'name',        'subtitle' => 'tiv_name',        'body' => 'description'],
        'timeline'          => ['eyebrow' => 'FROM TIV HISTORY',   'title' => 'title',       'subtitle' => null,              'body' => 'short_summary'],
        'quote'             => ['eyebrow' => 'TIV WISDOM',         'title' => null,          'subtitle' => null,              'body' => 'caption'],
    ];

    /**
     * Renders a branded PNG for the given template type + orientation from
     * an arbitrary source item (a row from any content model, or a
     * marketing_posts row for the "quote" template). Returns the absolute
     * file path of the generated image.
     */
    public static function generate(string $templateType, string $orientation, array $item, ?string $ctaUrl = null): string
    {
        if (!isset(self::TEMPLATE_FIELD_MAP[$templateType])) {
            throw new RuntimeException("Unknown image template: {$templateType}");
        }
        if (!isset(self::ORIENTATIONS[$orientation])) {
            throw new RuntimeException("Unknown orientation: {$orientation}");
        }
        if (!file_exists(self::FONT_BOLD) || !file_exists(self::FONT_REGULAR)) {
            throw new RuntimeException('Required font files are missing on this server (expected DejaVu Sans under /usr/share/fonts).');
        }

        [$width, $height] = self::ORIENTATIONS[$orientation];
        $map = self::TEMPLATE_FIELD_MAP[$templateType];

        $eyebrow = !empty($item['eyebrow_override']) ? (string) $item['eyebrow_override'] : $map['eyebrow'];
        $title = $map['title'] ? (string) ($item[$map['title']] ?? '') : '';
        $subtitle = $map['subtitle'] ? (string) ($item[$map['subtitle']] ?? '') : '';
        $body = (string) ($item[$map['body']] ?? '');

        // The mapped source field is frequently empty for older/incomplete
        // archive rows (e.g. most daily_words have no alternate_meaning) —
        // fall back to the post's own AI-generated headline/caption, which
        // is always guaranteed non-empty, rather than shipping a blank card.
        if ($title === '' && !empty($item['headline'])) {
            $title = (string) $item['headline'];
        }
        if ($body === '' && !empty($item['caption'])) {
            $body = (string) $item['caption'];
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagesavealpha($canvas, true);
        $cream = self::allocate($canvas, self::COLOR_CREAM);
        imagefill($canvas, 0, 0, $cream);

        self::drawHeaderBand($canvas, $width, $eyebrow);
        self::drawFooterBand($canvas, $width, $height, $ctaUrl);
        self::drawBody($canvas, $width, $height, $title, $subtitle, $body);

        $dir = UPLOADS_PATH . '/marketing_images';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $filename = $templateType . '-' . $orientation . '-' . uniqid('', true) . '.png';
        $destPath = $dir . '/' . $filename;

        imagepng($canvas, $destPath, 6);
        imagedestroy($canvas);

        return $destPath;
    }

    private static function drawHeaderBand($canvas, int $width, string $eyebrow): void
    {
        $brown = self::allocate($canvas, self::COLOR_BROWN);
        $gold = self::allocate($canvas, self::COLOR_GOLD);
        imagefilledrectangle($canvas, 0, 0, $width, 90, $brown);
        imagettftext($canvas, 20, 0, 40, 55, $gold, self::FONT_BOLD, $eyebrow);

        // Logo, top-right, composited with alpha preserved.
        $logoPath = BASE_PATH . '/assets/images/logo.png';
        if (file_exists($logoPath)) {
            $logo = imagecreatefrompng($logoPath);
            $logoSize = 56;
            $resized = imagecreatetruecolor($logoSize, $logoSize);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $logoSize, $logoSize, $transparent);
            imagecopyresampled($resized, $logo, 0, 0, 0, 0, $logoSize, $logoSize, imagesx($logo), imagesy($logo));
            imagecopy($canvas, $resized, $width - $logoSize - 30, 17, 0, 0, $logoSize, $logoSize);
            imagedestroy($logo);
            imagedestroy($resized);
        }
    }

    private static function drawFooterBand($canvas, int $width, int $height, ?string $ctaUrl = null): void
    {
        $brown = self::allocate($canvas, self::COLOR_BROWN);
        $cream = self::allocate($canvas, self::COLOR_CREAM);
        $footerHeight = 60;
        imagefilledrectangle($canvas, 0, $height - $footerHeight, $width, $height, $brown);
        // Prefer a specific trackable link (SITE_URL/go/{code}) over the bare
        // domain so viewers — including anyone who just screenshots the
        // image — have an actual callback URL to the source content.
        $siteUrl = defined('SITE_URL') ? preg_replace('#^https?://#', '', SITE_URL) : 'tivheritage.com';
        $footerText = $ctaUrl ? preg_replace('#^https?://#', '', $ctaUrl) : $siteUrl;
        $size = 15;
        $box = imagettfbbox($size, 0, self::FONT_BOLD, $footerText);
        $textWidth = abs($box[4] - $box[0]);
        $x = (int) round(($width - $textWidth) / 2);
        imagettftext($canvas, $size, 0, $x, $height - 22, $cream, self::FONT_BOLD, $footerText);
    }

    /**
     * Renders the title/subtitle/body block centered — both horizontally
     * and, as a group, vertically within the space between the header and
     * footer bands. The previous version pinned everything to a fixed top
     * y, which left large dead space under short bodies and looked
     * unbalanced; measuring the full block's height first and centering it
     * (plus a short gold divider under the title) gives it an actual
     * designed, poster-like feel instead.
     */
    private static function drawBody($canvas, int $width, int $height, string $title, string $subtitle, string $body): void
    {
        $dark = self::allocate($canvas, self::COLOR_DARK);
        $brownText = self::allocate($canvas, self::COLOR_BROWN);
        $gold = self::allocate($canvas, self::COLOR_GOLD);
        $padding = $width >= 1080 ? 90 : 70;
        $maxWidth = $width - ($padding * 2);
        $centerX = (int) round($width / 2);

        $titleSize = $width >= 1080 ? 48 : 36;
        $subtitleSize = $width >= 1080 ? 26 : 22;
        $bodySize = $width >= 1080 ? 27 : 22;
        $titleLH = (int) round($titleSize * 1.22);
        $subtitleLH = (int) round($subtitleSize * 1.3);
        $bodyLH = (int) round($bodySize * 1.55);
        // Space reserved under the title for the gold rule — sized off the
        // title font so the rule sits clear of both the title's descenders
        // above it and the next line's ascenders below it. DejaVu's ascent
        // runs close to the full font size (measured via imagettfbbox, not
        // the ~0.75x commonly assumed), so this needs to be generous or the
        // rule visibly cuts through the subtitle/body text below it.
        $dividerGap = $titleSize + 24;
        $subtitleGap = 22;

        $headerHeight = 90;
        $footerHeight = 60;
        $verticalBreathingRoom = $width >= 1080 ? 60 : 40;
        $contentTop = $headerHeight + $verticalBreathingRoom;
        $contentBottom = $height - $footerHeight - $verticalBreathingRoom;
        $available = $contentBottom - $contentTop;

        $titleLines = $title !== '' ? self::wrapLines(self::FONT_BOLD, $titleSize, $maxWidth, $title) : [];
        $subtitleLines = $subtitle !== '' ? self::wrapLines(self::FONT_REGULAR, $subtitleSize, $maxWidth, $subtitle) : [];
        $bodyLines = $body !== '' ? self::wrapLines(self::FONT_REGULAR, $bodySize, $maxWidth, $body) : [];

        $fixedHeight = ($titleLines ? count($titleLines) * $titleLH + $dividerGap : 0)
            + ($subtitleLines ? count($subtitleLines) * $subtitleLH + $subtitleGap : 0);
        $maxBodyLines = $bodyLines ? max(1, (int) floor(($available - $fixedHeight) / $bodyLH)) : 0;

        if ($bodyLines && count($bodyLines) > $maxBodyLines) {
            $bodyLines = array_slice($bodyLines, 0, $maxBodyLines);
            $lastIndex = count($bodyLines) - 1;
            $bodyLines[$lastIndex] = rtrim($bodyLines[$lastIndex], " .,;:-") . '…';
        }

        $totalHeight = $fixedHeight + count($bodyLines) * $bodyLH;
        $y = $contentTop + max(0, (int) round(($available - $totalHeight) / 2));

        if ($titleLines) {
            $y = self::drawCenteredLines($canvas, $titleLines, self::FONT_BOLD, $titleSize, $dark, $centerX, $y, $titleLH);
            $ruleWidth = 100;
            $ruleY = $y + (int) round($dividerGap * 0.2);
            imagefilledrectangle($canvas, $centerX - (int) ($ruleWidth / 2), $ruleY, $centerX + (int) ($ruleWidth / 2), $ruleY + 4, $gold);
            $y += $dividerGap;
        }

        if ($subtitleLines) {
            $y = self::drawCenteredLines($canvas, $subtitleLines, self::FONT_REGULAR, $subtitleSize, $brownText, $centerX, $y, $subtitleLH);
            $y += $subtitleGap;
        }

        if ($bodyLines) {
            self::drawCenteredLines($canvas, $bodyLines, self::FONT_REGULAR, $bodySize, $dark, $centerX, $y, $bodyLH);
        }
    }

    /**
     * Word-wraps $text to fit $maxWidth (measured via imagettfbbox — GD has
     * no built-in word-wrap) and returns the resulting lines without
     * drawing anything, so callers can measure a block's total height
     * before deciding where to start drawing it.
     */
    private static function wrapLines(string $font, int $size, int $maxWidth, string $text): array
    {
        $words = preg_split('/\s+/', trim($text));
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            $box = imagettfbbox($size, 0, $font, $candidate);
            $lineWidth = abs($box[4] - $box[0]);
            if ($lineWidth > $maxWidth && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * Draws each line horizontally centered on $centerX, returning the y
     * position after the last line.
     */
    private static function drawCenteredLines($canvas, array $lines, string $font, int $size, $color, int $centerX, int $y, int $lineHeight): int
    {
        foreach ($lines as $i => $line) {
            $box = imagettfbbox($size, 0, $font, $line);
            $lineWidth = abs($box[4] - $box[0]);
            $x = $centerX - (int) round($lineWidth / 2);
            imagettftext($canvas, $size, 0, $x, $y + ($i * $lineHeight), $color, $font, $line);
        }

        return $y + (count($lines) * $lineHeight);
    }

    private static function allocate($canvas, array $rgb)
    {
        return imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
    }

    public static function templateTypes(): array
    {
        return array_keys(self::TEMPLATE_FIELD_MAP);
    }
}
