<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Data for the header mega menu (desktop flyouts) and the mobile menu:
 * each Vastutathastu category with its types, materials and a few featured products.
 */
class MegaMenu
{
    private static ?array $cache = null;

    /**
     * @return array{services: Collection, categories: Collection, byslug: array<string, array>, picks: Collection}
     */
    public static function data(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $categories = Category::query()
            ->active()
            ->with(['subCategories' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $bySlug = [];
        foreach ($categories as $category) {
            $products = Product::query()
                ->active()
                ->where('category_id', $category->id)
                ->orderByDesc('is_featured')
                ->orderByDesc('is_new_arrival')
                ->orderBy('sort_order')
                ->take(3)
                ->get();

            $bySlug[$category->slug] = [
                'category' => $category,
                'types' => $category->subCategories,
                'materials' => Product::query()->active()->where('category_id', $category->id)
                    ->whereNotNull('material')->distinct()->orderBy('material')->pluck('material')->take(6),
                'products' => $products,
            ];
        }

        // Services menu (same groups as the old vastutathastu.com menu); only live products are linked.
        $serviceGroups = [
            ['title' => 'Astrology Consultancy', 'page' => 'astrology', 'items' => ['muhurt', 'matchmaking', 'direct-kundali-consultancy', 'online-kundali-consultancy']],
            ['title' => 'Numerology Consultancy', 'page' => 'numerology', 'items' => ['bracelet-according-numerology', 'vehicle-number', 'company-logo-selection', 'commercial-numerology-guidance', 'personal-numerology-guidance']],
            ['title' => 'Vastushastra Consultancy', 'page' => 'vastu-consultation', 'items' => ['vastushastra-consultancy']],
        ];
        $serviceProducts = Product::query()->active()
            ->whereIn('slug', collect($serviceGroups)->pluck('items')->flatten()->all())
            ->get(['id', 'slug', 'title'])->keyBy('slug');
        $services = collect($serviceGroups)->map(fn ($g) => $g + [
            'products' => collect($g['items'])->map(fn ($slug) => $serviceProducts->get($slug))->filter()->values(),
        ]);

        return self::$cache = [
            'services' => $services,
            'categories' => $categories,
            'byslug' => $bySlug,
            // "Top picks" in the Shop menu: products marked Featured in Admin → Products.
            'picks' => Product::query()->active()
                ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id')
                ->take(3)->get(),
        ];
    }
}
