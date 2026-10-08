<?php

namespace App\Support;

use App\Models\CustomerReview;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Customer reviews for the home page, written by the admin in Admin → Home Content → Customer Reviews.
 * The rating badge (rating, count, "View all reviews" link) comes from Admin → Settings → Site Details → Reviews badge.
 *
 * Each review: name, photo (URL|null), rating 1–5 (one decimal, e.g. 4.5), text, date (Carbon|null), images (URLs), google (bool).
 */
class Reviews
{
    /** Rating summary for the badge: ['rating' => 4.9, 'count' => 120, 'url' => '...']. */
    public static function summary(): array
    {
        $fallback = config('reviews.summary');
        $rating = (float) str_replace(',', '.', SiteSettings::get('reviews_rating'));
        $count = (int) preg_replace('/\D+/', '', SiteSettings::get('reviews_count'));

        return [
            'rating' => $rating > 0 ? min(5, $rating) : $fallback['rating'],
            'count' => $count > 0 ? $count : $fallback['count'],
            'url' => trim(SiteSettings::get('reviews_url')),
        ];
    }

    /** All visible reviews, in the admin's display order. */
    public static function all(): array
    {
        try {
            if (! Schema::hasTable('customer_reviews')) {
                return [];
            }

            return CustomerReview::query()->active()->ordered()->get()
                ->filter(fn ($r) => trim((string) $r->text) !== '')
                ->map(fn (CustomerReview $r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'photo' => $r->photo_url,
                    'rating' => round(max(1, min(5, (float) $r->rating)), 1),
                    'text' => $r->text,
                    'date' => $r->review_date,
                    'images' => $r->image_urls,
                    'google' => $r->show_google,
                ])->values()->all();
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }
}
