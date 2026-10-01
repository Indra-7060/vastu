<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Customer reviews for the storefront (config/reviews.php).
 *
 * Uses live Google reviews when GOOGLE_PLACES_API_KEY + GOOGLE_PLACE_ID are set,
 * otherwise the sample reviews. Each review: name, rating, text, date (Carbon), photo (URL|null).
 */
class Reviews
{
    /** Rating summary for the badge: ['rating' => 4.9, 'count' => 120, 'url' => '...']. */
    public static function summary(): array
    {
        $fallback = config('reviews.summary');
        $live = static::google();

        return $live ? array_merge($fallback, array_filter($live['summary'])) : $fallback;
    }

    /** The best, most recent reviews (highest rating first, newest first within a rating). */
    public static function top(int $limit = 3): array
    {
        $live = static::google();
        $items = $live['reviews'] ?? [];

        if (! $items) {
            $items = array_map(fn ($r) => array_merge($r, [
                'photo' => ! empty($r['photo']) ? asset($r['photo']) : null,
            ]), config('reviews.sample', []));
        }

        $items = array_map(fn ($r) => array_merge($r, [
            'date' => ! empty($r['date']) ? Carbon::parse($r['date']) : null,
            'rating' => max(1, min(5, (int) round($r['rating'] ?? 5))),
        ]), array_filter($items, fn ($r) => trim((string) ($r['text'] ?? '')) !== ''));

        usort($items, fn ($a, $b) => [$b['rating'], $b['date']?->timestamp ?? 0] <=> [$a['rating'], $a['date']?->timestamp ?? 0]);

        return array_slice($items, 0, $limit);
    }

    /** Live Google data (cached), or null when not configured / unavailable. */
    protected static function google(): ?array
    {
        $key = config('reviews.google.api_key');
        $place = config('reviews.google.place_id');
        if (! $key || ! $place) {
            return null;
        }

        $ttl = now()->addHours(max(1, config('reviews.google.cache_hours', 12)));

        // A failed lookup is cached as false too, so a Google outage never slows every page.
        $data = Cache::remember('vt.google-reviews.'.md5($place), $ttl, function () use ($key, $place) {
            try {
                $res = Http::timeout(6)->withHeaders([
                    'X-Goog-Api-Key' => $key,
                    'X-Goog-FieldMask' => 'rating,userRatingCount,googleMapsUri,reviews',
                ])->get('https://places.googleapis.com/v1/places/'.rawurlencode($place));

                if (! $res->successful()) {
                    return false;
                }

                $data = $res->json();
                $reviews = array_map(fn ($r) => [
                    'name' => $r['authorAttribution']['displayName'] ?? 'Google user',
                    'rating' => $r['rating'] ?? 5,
                    'text' => $r['originalText']['text'] ?? $r['text']['text'] ?? '',
                    'date' => $r['publishTime'] ?? null,
                    'photo' => $r['authorAttribution']['photoUri'] ?? null,
                ], $data['reviews'] ?? []);

                return [
                    'summary' => [
                        'rating' => $data['rating'] ?? null,
                        'count' => $data['userRatingCount'] ?? null,
                        'url' => $data['googleMapsUri'] ?? null,
                    ],
                    'reviews' => $reviews,
                ];
            } catch (Throwable $e) {
                report($e);

                return false;
            }
        });

        return $data ?: null;
    }
}
