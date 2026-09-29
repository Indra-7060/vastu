<?php

namespace App\Support;

/**
 * Storefront categories linked from the Vastutathastu menu, homepage and footer.
 * Until the admin creates a category with one of these slugs, its page shows an
 * empty "No products found" state instead of a 404.
 */
class VastuCatalog
{
    /**
     * @return array<string, string> slug => label
     */
    public static function categories(): array
    {
        return [
            'rudraksh' => 'Rudraksh',
            'bracelet' => 'Bracelet',
            'maala' => 'Maala',
            'pendant' => 'Pendant',
            'yantra' => 'Yantra',
            'murti' => 'Murti',
            'tree' => 'Tree',
            'crystals' => 'Crystals',
            'gemstones' => 'Gemstones',
            'pooja-sahitya' => 'Pooja Sahitya',
            'agarbatti' => 'Agarbatti',
            'coins' => 'Coins',
            'brass' => 'Brass',
            'wind-chime' => 'Wind Chime',
            'cards' => 'Cards',
            'kundali-guidance' => 'Kundali Guidance',
        ];
    }

    public static function label(string $slug): ?string
    {
        return self::categories()[$slug] ?? null;
    }
}
