<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\BannerImage;
use Illuminate\Support\Collection;

/**
 * Storefront access to admin-managed banners (Admin → Home Content → Sections & Images).
 * Only active banners are returned; a section with no active banner is not rendered.
 */
class SiteBanners
{
    /** @var array<string, Collection> */
    private static array $cache = [];

    /** All active banners of a section, in display order. */
    public static function all(string $section): Collection
    {
        return self::$cache[$section] ??= Banner::query()
            ->where('section', $section)
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    /** The first active banner of a section, or null when the section is switched off. */
    public static function first(string $section): ?Banner
    {
        return self::all($section)->first();
    }

    public static function media(?Banner $banner): ?BannerImage
    {
        return $banner?->images->first();
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') ? $path : asset('storage/'.ltrim($path, '/'));
    }

    /** Resolve admin-entered links ("/shop", "shop", full URLs). */
    public static function link(?string $link, string $fallback = '#'): string
    {
        $link = trim((string) $link);
        if ($link === '') {
            return $fallback;
        }

        return preg_match('#^(https?:)?//|^mailto:|^tel:|^\##i', $link) ? $link : url('/'.ltrim($link, '/'));
    }
}
