<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Shrinks large uploaded images without visible quality loss (GD only, no extra packages).
 *
 * - Images larger than MAX_EDGE px on their longest side are scaled down (enough for full-screen
 *   banners on retina screens).
 * - JPEG / WebP are re-encoded at a high quality (QUALITY) — well above the point where blur or
 *   blocky artefacts appear — and JPEGs are saved progressive.
 * - PNG keeps transparency and is compressed losslessly; a large PNG *photo* without transparency
 *   is saved as JPEG only when that makes it at least 40% smaller.
 * - Phone photos are rotated upright using their EXIF orientation.
 * - The optimised file is kept only if it is actually smaller (or was resized); otherwise the
 *   original bytes are left untouched.
 */
class ImageOptimizer
{
    public const MAX_EDGE = 2400;
    public const QUALITY = 84;
    /** Files below this size that already fit MAX_EDGE are left alone. */
    public const MIN_BYTES = 200 * 1024;
    /** Refuse to decode absurdly large images (protects server memory). */
    public const MAX_PIXELS = 60_000_000;

    private const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Optimise the file in place. Returns stats when it changed the file, otherwise null.
     *
     * @return array{before:int, after:int, width:int, height:int, mime:string}|null
     */
    public static function optimize(string $path, ?int $maxEdge = null): ?array
    {
        $maxEdge = $maxEdge ?: self::MAX_EDGE;
        if (! is_file($path) || ! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $info = @getimagesize($path);
        if (! $info || ! in_array($info['mime'] ?? '', self::TYPES, true)) {
            return null;
        }

        [$width, $height] = $info;
        $mime = $info['mime'];
        $before = filesize($path) ?: 0;
        $tooLarge = max($width, $height) > $maxEdge;

        if (! $tooLarge && $before < self::MIN_BYTES) {
            return null;
        }
        // Already efficiently compressed JPEG / WebP at a sensible size: re-encoding would only
        // add generation loss, so leave it exactly as uploaded.
        if (! $tooLarge && $mime !== 'image/png' && $before / max(1, $width * $height) < 0.25) {
            return null;
        }
        if ($width * $height > self::MAX_PIXELS) {
            Log::info("Image optimizer skipped a {$width}x{$height} image (too many pixels).");

            return null;
        }

        $previousLimit = ini_get('memory_limit');
        @ini_set('memory_limit', '512M');

        try {
            $image = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($path),
                'image/png' => @imagecreatefrompng($path),
                'image/webp' => @imagecreatefromwebp($path),
            };
            if (! $image) {
                return null;
            }

            if ($mime === 'image/jpeg') {
                $image = self::applyExifOrientation($image, $path);
            }

            $w = imagesx($image);
            $h = imagesy($image);
            $scale = min(1, $maxEdge / max($w, $h));
            $resized = $scale < 1;
            if ($resized) {
                $nw = max(1, (int) round($w * $scale));
                $nh = max(1, (int) round($h * $scale));
                $canvas = imagecreatetruecolor($nw, $nh);
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagecopyresampled($canvas, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($image);
                $image = $canvas;
                [$w, $h] = [$nw, $nh];
            }

            [$bytes, $outMime] = self::encode($image, $mime, $before);
            imagedestroy($image);

            // Keep the new file only when it was resized or is meaningfully (10%+) smaller.
            if ($bytes === null || (! $resized && strlen($bytes) > $before * 0.9)) {
                return null;
            }

            file_put_contents($path, $bytes);
            clearstatcache(true, $path);

            return ['before' => $before, 'after' => strlen($bytes), 'width' => $w, 'height' => $h, 'mime' => $outMime];
        } catch (\Throwable $e) {
            Log::warning('Image optimizer failed: '.$e->getMessage());

            return null;
        } finally {
            @ini_set('memory_limit', (string) $previousLimit);
        }
    }

    /** @return array{0:?string,1:string} encoded bytes and mime */
    private static function encode(\GdImage $image, string $mime, int $originalBytes): array
    {
        if ($mime === 'image/webp') {
            return [self::capture(fn () => imagewebp($image, null, self::QUALITY)), 'image/webp'];
        }

        if ($mime === 'image/png') {
            imagesavealpha($image, true);
            $png = self::capture(fn () => imagepng($image, null, 9));

            // A big PNG photo without transparency: JPEG is far smaller at no visible cost.
            if ($png !== null && strlen($png) > 500 * 1024 && ! self::hasTransparency($image)) {
                $jpeg = self::jpeg($image);
                if ($jpeg !== null && strlen($jpeg) < strlen($png) * 0.6) {
                    return [$jpeg, 'image/jpeg'];
                }
            }

            return [$png, 'image/png'];
        }

        return [self::jpeg($image), 'image/jpeg'];
    }

    private static function jpeg(\GdImage $image): ?string
    {
        // Flatten onto white (JPEG has no alpha) and save progressive.
        $w = imagesx($image);
        $h = imagesy($image);
        $flat = imagecreatetruecolor($w, $h);
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $image, 0, 0, 0, 0, $w, $h);
        imageinterlace($flat, true);
        $bytes = self::capture(fn () => imagejpeg($flat, null, self::QUALITY));
        imagedestroy($flat);

        return $bytes;
    }

    private static function capture(callable $write): ?string
    {
        ob_start();
        $ok = $write();
        $bytes = ob_get_clean();

        return $ok && $bytes !== false && $bytes !== '' ? $bytes : null;
    }

    private static function hasTransparency(\GdImage $image): bool
    {
        $w = imagesx($image);
        $h = imagesy($image);
        // Sample a grid of pixels — enough to detect real transparency quickly.
        $stepX = max(1, (int) ($w / 60));
        $stepY = max(1, (int) ($h / 60));
        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };
        if ($rotated) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }
}
