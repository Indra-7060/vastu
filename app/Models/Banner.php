<?php

namespace App\Models;

use App\Support\BannerSections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'section',
        'subtitle',
        'description',
        'button_text',
        'button_link',
        'button_text_2',
        'button_link_2',
        'is_active',
        'text_in_image',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'text_in_image' => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(BannerImage::class)->orderBy('sort_order');
    }

    public function getSectionLabelAttribute(): string
    {
        return BannerSections::label($this->section);
    }

    public function getSectionPageAttribute(): string
    {
        return BannerSections::page($this->section);
    }

    public function getCoverImageAttribute(): ?string
    {
        $first = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $first?->image;
    }
}
