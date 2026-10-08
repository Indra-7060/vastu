<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A customer review shown on the home page (Admin → Home Content → Customer Reviews). */
class CustomerReview extends Model
{
    protected $fillable = ['name', 'photo', 'rating', 'text', 'review_date', 'date_style', 'images', 'show_google', 'is_active', 'sort_order'];

    protected $casts = [
        'images' => 'array',
        'review_date' => 'date',
        'show_google' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'float',
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

    /** "12 Aug 2025", or Google-style "6 months ago" / "a year ago" when the admin chose "time ago". */
    public static function dateLabel($date, ?string $style): ?string
    {
        if (! $date) {
            return null;
        }
        if ($style !== 'ago') {
            return $date->format('j M Y');
        }
        if ($date->isToday()) {
            return 'today';
        }
        $label = $date->diffForHumans(['parts' => 1, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]);
        // Google style: "a year ago", "an hour ago", "a month ago"
        return preg_replace(['/^1 hour/', '/^1 (\w)/'], ['an hour', 'a $1'], $label);
    }

    public function getDateLabelAttribute(): ?string
    {
        return static::dateLabel($this->review_date, $this->date_style);
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
