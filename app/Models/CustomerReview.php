<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A customer review shown on the home page (Admin → Home Content → Customer Reviews). */
class CustomerReview extends Model
{
    protected $fillable = ['name', 'photo', 'rating', 'text', 'review_date', 'images', 'show_google', 'is_active', 'sort_order'];

    protected $casts = [
        'images' => 'array',
        'review_date' => 'date',
        'show_google' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('review_date')->orderByDesc('id');
    }

    /** Public URL of a stored file (uploads live in public/storage; the starter photos in public/vastu). */
    public static function fileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'vastu/') ? asset($path) : asset('storage/'.ltrim($path, '/'));
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return static::fileUrl($this->photo);
    }

    /** @return array<int, string> */
    public function getImageUrlsAttribute(): array
    {
        return array_values(array_filter(array_map([static::class, 'fileUrl'], (array) $this->images)));
    }
}
