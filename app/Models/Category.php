<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'image',
        'short_description',
        'page_heading',
        'page_description',
        'product_sections',
        'has_color',
        'has_size',
        'show_on_home',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'product_sections' => 'array',
        'has_color' => 'boolean',
        'has_size' => 'boolean',
        'show_on_home' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->title);
            }
        });
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return asset('vastu/images/placeholder.jpg');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'frontend/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnHome($query)
    {
        return $query->where('show_on_home', true);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Clean frontend URL: /accessories, /collection, /shirts
     */
    public function frontendUrl(array $query = []): string
    {
        $params = array_filter(['q' => $query['q'] ?? null]);

        if ($this->slug === 'collection') {
            return route('collection', $params);
        }

        return route('category', array_filter([
            'categorySlug' => $this->slug,
        ] + $params));
    }
}
