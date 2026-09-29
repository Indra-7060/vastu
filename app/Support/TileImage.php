<?php

namespace App\Support;

/**
 * Square "tile crops" of product photos for listing tiles, carousels, menus and search.
 *
 * Product photos are taken at different distances, so a small bead can look tiny in one tile and
 * huge in the next. For photos shot on a plain background this finds the product, then crops a
 * square around it so the product fills about the same share of every tile. Busy lifestyle photos
 * (hands, cloth, scenes) and photos where the product already fills the frame are left as they
 * are. The original photo is never changed; the product page and viewer still show it in full.
 */
class TileImage
{
    /** Share of the tile the product should fill (longest side). */
    private const FILL = 0.74;
    private const SIZE = 800;
    private const DIR = 'products/tiles';

    /** Public URL of the tile crop for a stored photo, or null when there is none. */
    public static function url(?string $path): ?string
    {
        $tile = self::tilePath($path);

        return $tile && is_file(public_path('storage/'.$tile)) ? asset('storage/'.$tile) : null;
    }

    public static function tilePath(?string $path): ?string
    {
        if (! $path || str_starts_with($path, 'http')) {
            return null;
        }

        return self::DIR.'/'.substr(sha1($path), 0, 16).'.jpg';
    }

    /**
     * Create (or refresh) the tile crop for a stored photo.
     * Returns 'cropped', 'skipped' (photo is fine as is) or 'error'.
     */
    public static function make(?string $path): string
    {
        $tile = self::tilePath($path);
        $src = $path ? public_path('storage/'.ltrim($path, '/')) : null;
        if (! $tile || ! $src || ! is_file($src)) {
            return 'error';
        }
        $dest = public_path('storage/'.$tile);
        @unlink($dest);

        $info = @getimagesize($src);
        if (! $info) {
            return 'error';
        }
        $img = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($src),
            'image/png' => @imagecreatefrompng($src),
            'image/webp' => @imagecreatefromwebp($src),
            default => false,
        };
        if (! $img) {
            return 'error';
        }
        [$w, $h] = [imagesx($img), imagesy($img)];

        // Analyse a small copy.
        $aw = 160;
        $ah = max(1, (int) round($h * $aw / $w));
        $small = imagecreatetruecolor($aw, $ah);
        imagefill($small, 0, 0, imagecolorallocate($small, 255, 255, 255));
        imagecopyresampled($small, $img, 0, 0, 0, 0, $aw, $ah, $w, $h);

        // Background = average colour of the outer border; it must be fairly uniform.
        $border = [];
        for ($x = 0; $x < $aw; $x++) { $border[] = self::rgb($small, $x, 0); $border[] = self::rgb($small, $x, $ah - 1); }
        for ($y = 0; $y < $ah; $y++) { $border[] = self::rgb($small, 0, $y); $border[] = self::rgb($small, $aw - 1, $y); }
        $bg = [0, 0, 0];
        foreach ($border as $c) { $bg[0] += $c[0]; $bg[1] += $c[1]; $bg[2] += $c[2]; }
        $n = count($border);
        $bg = [$bg[0] / $n, $bg[1] / $n, $bg[2] / $n];
        $spread = 0;
        foreach ($border as $c) { $spread += abs($c[0] - $bg[0]) + abs($c[1] - $bg[1]) + abs($c[2] - $bg[2]); }
        $spread /= $n;
        // A grey / white studio background may be a soft gradient (uneven but colourless);
        // a coloured, uneven border means a lifestyle photo (hand, cloth, scene) — leave those alone.
        $bgSat = self::sat($bg);
        $greyBackdrop = $bgSat < 0.12;
        if ($spread > ($greyBackdrop ? 80 : 36)) {
            imagedestroy($small); imagedestroy($img);

            return 'skipped';
        }

        // Product = pixels clearly different from the background.
        $minX = $aw; $minY = $ah; $maxX = -1; $maxY = -1;
        for ($y = 0; $y < $ah; $y++) {
            for ($x = 0; $x < $aw; $x++) {
                $c = self::rgb($small, $x, $y);
                $dist = abs($c[0] - $bg[0]) + abs($c[1] - $bg[1]) + abs($c[2] - $bg[2]);
                // Product = clearly different from the backdrop; on grey gradients use colour strength,
                // so the gradient's light/dark areas don't count as product.
                $isProduct = $greyBackdrop
                    ? (self::sat($c) > 0.22 && $dist > 40) || $dist > 150
                    : $dist > 70;
                if ($isProduct) {
                    $minX = min($minX, $x); $maxX = max($maxX, $x);
                    $minY = min($minY, $y); $maxY = max($maxY, $y);
                }
            }
        }
        imagedestroy($small);
        if ($maxX < 0) {
            imagedestroy($img);

            return 'skipped';
        }

        // Back to full-size coordinates.
        $sx = $w / $aw; $sy = $h / $ah;
        $bx0 = $minX * $sx; $bx1 = ($maxX + 1) * $sx;
        $by0 = $minY * $sy; $by1 = ($maxY + 1) * $sy;
        $objSide = max($bx1 - $bx0, $by1 - $by0);
        if ($objSide >= min($w, $h) * 0.7) {
            imagedestroy($img);

            return 'skipped';   // product already fills the frame
        }

        $side = (int) round($objSide / self::FILL);
        $cx = ($bx0 + $bx1) / 2; $cy = ($by0 + $by1) / 2;
        $x0 = (int) round($cx - $side / 2); $y0 = (int) round($cy - $side / 2);

        // Square canvas filled with the background colour (covers any part outside the photo).
        $out = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagefill($out, 0, 0, imagecolorallocate($out, (int) $bg[0], (int) $bg[1], (int) $bg[2]));
        $scale = self::SIZE / $side;
        $srcX = max(0, $x0); $srcY = max(0, $y0);
        $srcX1 = min($w, $x0 + $side); $srcY1 = min($h, $y0 + $side);
        imagecopyresampled(
            $out, $img,
            (int) round(($srcX - $x0) * $scale), (int) round(($srcY - $y0) * $scale),
            $srcX, $srcY,
            (int) round(($srcX1 - $srcX) * $scale), (int) round(($srcY1 - $srcY) * $scale),
            $srcX1 - $srcX, $srcY1 - $srcY
        );
        imagedestroy($img);

        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }
        imageinterlace($out, true);
        $ok = imagejpeg($out, $dest, 86);
        imagedestroy($out);

        return $ok ? 'cropped' : 'error';
    }

    /** Colour strength (HSL-style saturation, 0–1). */
    private static function sat(array $c): float
    {
        $max = max($c) / 255;
        $min = min($c) / 255;
        $l = ($max + $min) / 2;
        if ($max === $min) {
            return 0.0;
        }

        return ($max - $min) / (1 - abs(2 * $l - 1) + 1e-6);
    }

    private static function rgb(\GdImage $img, int $x, int $y): array
    {
        $c = imagecolorat($img, $x, $y);

        return [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF];
    }
}
