<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Product search shared by the header search panel and the /shop?q= results page.
 *
 * Product names are transliterated from Hindi/Marathi, so the same word is spelled many ways
 * (Kapur / Kapoor, Rudraksh / Rudraksha, Mala / Maala, Shri / Shree). Every word is reduced to a
 * "sounds-like" key before comparing, so any of those spellings finds the product. A few common
 * English words are mapped to their Indian names (camphor → kapur, incense → agarbatti …).
 */
class ProductSearch
{
    /** English / alternative words => words used in product names. */
    private const SYNONYMS = [
        'camphor' => ['kapur'],
        'incense' => ['agarbatti', 'dhoop'],
        'idol' => ['murti'],
        'statue' => ['murti'],
        'rosary' => ['mala'],
        'beads' => ['mala'],
        'necklace' => ['pendant', 'mala'],
        'lamp' => ['diya'],
        'coin' => ['coins'],
        'tree' => ['tree'],
    ];

    /** @var array<int, array{id:int, title:string, category:string, material:string, sort:int, name:string, all:string}>|null */
    private static ?array $index = null;

    /** "Sounds-like" key of a single word. */
    public static function key(string $word): string
    {
        $w = mb_strtolower($word);
        $w = preg_replace('/[^a-z0-9]+/', '', $w) ?? '';
        if ($w === '') {
            return '';
        }
        $w = strtr($w, ['aa' => 'a', 'ee' => 'i', 'oo' => 'u', 'ph' => 'f', 'sh' => 's', 'ck' => 'k', 'q' => 'k', 'w' => 'v', 'z' => 'j', 'y' => 'i']);
        $raw = $w;
        $w = preg_replace('/(.)\1+/', '$1', $w) ?? $w;   // double letters
        if (strlen($w) < 2 && strlen($raw) >= 3) {
            return $raw;                                  // e.g. "zzz": don't shrink to one letter
        }
        if (strlen($w) > 3 && str_ends_with($w, 'a')) {  // rudraksha → rudraksh, mala → mal
            $w = substr($w, 0, -1);
        }

        return $w;
    }

    /** Keys of every word in a text, joined by spaces (with a leading space for word-start checks). */
    public static function keys(string $text): string
    {
        $words = preg_split('/[^a-z0-9]+/i', mb_strtolower($text)) ?: [];

        return ' '.implode(' ', array_filter(array_map([self::class, 'key'], $words))).' ';
    }

    /**
     * Product ids matching the query, best match first, with scores.
     *
     * @return Collection<int, array{id:int, score:int}>
     */
    public static function rank(string $query): Collection
    {
        $words = array_values(array_filter(preg_split('/\s+/', trim(mb_strtolower($query))) ?: []));
        if (! $words) {
            return collect();
        }
        $phrase = trim(self::keys($query));

        // Every query word must match (the word itself or one of its synonyms).
        $alternatives = array_map(function (string $word) {
            $keys = [self::key($word)];
            foreach (self::SYNONYMS[$word] ?? [] as $syn) {
                $keys[] = self::key($syn);
            }

            return array_values(array_unique(array_filter($keys)));
        }, $words);
        $alternatives = array_values(array_filter($alternatives));
        if (! $alternatives) {
            return collect();
        }

        $results = [];
        foreach (self::index() as $row) {
            $score = 0;
            foreach ($alternatives as $keys) {
                $best = 0;
                foreach ($keys as $k) {
                    $short = strlen($k) < 3;
                    if (str_contains($row['name'], ' '.$k)) {
                        $best = max($best, 60);                  // a word in the name starts with it
                    } elseif (! $short && str_contains($row['name'], $k)) {
                        $best = max($best, 40);
                    } elseif (str_contains($row['category'], ' '.$k)) {
                        $best = max($best, 35);
                    } elseif (str_contains($row['material'], ' '.$k)) {
                        $best = max($best, 20);
                    } elseif (! $short && str_contains($row['all'], $k)) {
                        $best = max($best, 8);                   // description / features
                    }
                }
                if ($best === 0) {
                    continue 2;                                   // this word doesn't match: skip product
                }
                $score += $best;
            }
            $name = trim($row['name']);
            if ($phrase !== '' && $name === $phrase) {
                $score += 1000;
            } elseif ($phrase !== '' && str_starts_with($name, $phrase)) {
                $score += 500;
            } elseif ($phrase !== '' && str_contains($row['name'], ' '.$phrase)) {
                $score += 300;
            }
            $results[] = ['id' => $row['id'], 'score' => $score, 'sort' => $row['sort']];
        }

        usort($results, fn ($a, $b) => [$b['score'], $a['sort']] <=> [$a['score'], $b['sort']]);

        return collect($results)->map(fn ($r) => ['id' => $r['id'], 'score' => $r['score']]);
    }

    /** Searchable text of every active product (built once per request). */
    private static function index(): array
    {
        if (self::$index !== null) {
            return self::$index;
        }

        return self::$index = Product::query()
            ->active()
            ->with(['category:id,title', 'subCategory:id,title'])
            ->get(['id', 'title', 'category_id', 'sub_category_id', 'material', 'short_description', 'features', 'sort_order'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'sort' => (int) $p->sort_order,
                'name' => self::keys($p->title),
                'category' => self::keys(trim(optional($p->category)->title.' '.optional($p->subCategory)->title)),
                'material' => self::keys((string) $p->material),
                'all' => self::keys($p->title.' '.$p->material.' '.optional($p->category)->title.' '.$p->short_description.' '.$p->features),
            ])
            ->all();
    }
}
