<?php

namespace App\Support;

/**
 * Gallery categories (header Gallery menu + /gallery tabs).
 * Each category's photos are managed in Admin → Gallery (one Sections & Images section per category).
 */
class GalleryCategories
{
    public static function all(): array
    {
        return [
            'our-product-users' => ['label' => 'Our Product Users', 'section' => 'gallery_product_users'],
            'awards' => ['label' => 'Awards', 'section' => 'gallery_awards'],
            'celebrity' => ['label' => 'Celebrity', 'section' => 'gallery_celebrity'],
            'others' => ['label' => 'Others', 'section' => 'gallery_others'],
        ];
    }

    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    public static function find(?string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /** [['label', 'slug', 'url'], ...] for menus and tabs. */
    public static function links(): array
    {
        $links = [];
        foreach (self::all() as $slug => $meta) {
            $links[] = ['label' => $meta['label'], 'slug' => $slug, 'url' => route('gallery', $slug)];
        }

        return $links;
    }
}
