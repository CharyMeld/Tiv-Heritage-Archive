<?php
/**
 * ImageVariantGenerator - GD-based WebP + responsive raster variants for
 * uploaded content photos. Used both at upload time (see AdminController /
 * AdminHistoricalFigureController) and by the one-off batch script
 * bin/optimize-images.php for pre-existing uploads.
 *
 * Naming matches SeoHelper::variantUrl(): "{base}-{width}w.{ext}", flat in
 * the same directory as the original (uploads/images/ has no subfolders).
 */
class ImageVariantGenerator
{
    private const WIDTHS = [400, 800];

    /**
     * Generates WebP + original-format variants at each of self::WIDTHS for
     * the given source image. Skips a width if the source is already
     * narrower than it (never upscales). Idempotent: safe to call repeatedly.
     */
    public static function generate(string $sourcePath): void
    {
        if (!is_file($sourcePath)) {
            return;
        }

        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return;
        }

        [$srcWidth, $srcHeight, $type] = $info;
        $srcImage = self::load($sourcePath, $type);
        if (!$srcImage) {
            return;
        }

        $ext = self::extensionFor($type);
        $dir = dirname($sourcePath);
        $base = preg_replace('/\.[^.]+$/', '', basename($sourcePath));

        foreach (self::WIDTHS as $width) {
            if ($srcWidth <= $width) {
                continue;
            }
            $height = (int) round($srcHeight * ($width / $srcWidth));
            $resized = imagescale($srcImage, $width, $height, IMG_BICUBIC);
            if (!$resized) {
                continue;
            }

            self::save($resized, "{$dir}/{$base}-{$width}w.webp", 'webp');
            self::save($resized, "{$dir}/{$base}-{$width}w.{$ext}", $ext);

            imagedestroy($resized);
        }

        imagedestroy($srcImage);
    }

    private static function load(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default        => false,
        };
    }

    private static function extensionFor(int $type): string
    {
        return match ($type) {
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
            default        => 'jpg',
        };
    }

    private static function save($image, string $destPath, string $format): void
    {
        if (is_file($destPath)) {
            return;
        }
        match ($format) {
            'webp'  => imagewebp($image, $destPath, 82),
            'png'   => imagepng($image, $destPath, 6),
            'gif'   => imagegif($image, $destPath),
            default => imagejpeg($image, $destPath, 82),
        };
    }
}
