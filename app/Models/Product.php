<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'sub_category_id',
        'material',
        'badge',
        'brand_id',
        'offer_id',
        'short_description',
        'features',
        'mrp',
        'selling_price',
        'max_unit_buy',
        'delivery_charge',
        'featured_image',
        'featured_image_2',
        'seo_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_featured',
        'is_todays_deal',
        'is_popular_accessory',
        'is_new_arrival',
        'show_size_guide',
        'size_guide_content',
        'size_guide_image',
        'highlights_image',
        'highlights_short_description',
        'highlights_items',
        'information_items',
        'specifications',
        'accessory_packages',
        'sort_order',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_todays_deal' => 'boolean',
        'is_popular_accessory' => 'boolean',
        'is_new_arrival' => 'boolean',
        'show_size_guide' => 'boolean',
        'highlights_items' => 'array',
        'information_items' => 'array',
        'specifications' => 'array',
        'accessory_packages' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            if ($product->wasChanged('featured_image') || $product->wasRecentlyCreated) {
                \App\Support\TileImage::make($product->featured_image);
            }
        });
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_color')->withPivot('quantity');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size')->withPivot('quantity');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFeaturedImageUrlAttribute(): string
    {
        return $this->resolveMediaUrl($this->featured_image, 'vastu/images/placeholder.jpg');
    }

    public function getHighlightsImageUrlAttribute(): string
    {
        if ($this->highlights_image) {
            return $this->resolveMediaUrl($this->highlights_image, 'vastu/images/placeholder.jpg');
        }

        return $this->featured_image_url;
    }

    public function highlightIconUrl(?string $path): string
    {
        if (! $path) {
            return asset('vastu/images/favicon.png');
        }

        return $this->resolveMediaUrl($path, 'vastu/images/favicon.png');
    }

    /**
     * @return array<int, array{key: string, label: string, mrp: float, price: float}>
     */
    public function accessoryPackages(): array
    {
        return collect($this->accessory_packages ?? [])
            ->filter(fn ($package) => filled($package['label'] ?? null) && isset($package['price']) && $package['price'] !== '')
            ->values()
            ->map(fn ($package, $index) => [
                'key' => (string) ($package['key'] ?? ('package-'.($index + 1))),
                'label' => (string) $package['label'],
                'mrp' => (float) ($package['mrp'] ?? $package['price']),
                'price' => (float) $package['price'],
            ])
            ->all();
    }

    public function usesAccessoryPackages(): bool
    {
        return $this->accessoryPackages() !== [];
    }

    /** Square crop of the main photo for tiles / cards (see App\Support\TileImage); falls back to the photo. */
    public function getTileImageUrlAttribute(): string
    {
        return \App\Support\TileImage::url($this->featured_image) ?? $this->featured_image_url;
    }

    public function getHoverImageUrlAttribute(): string
    {
        if ($this->featured_image_2) {
            return $this->resolveMediaUrl($this->featured_image_2, 'vastu/images/placeholder.jpg');
        }

        return $this->featured_image_url;
    }

    /** Public URL for any stored product image path (gallery images, hover images…). */
    public function imageUrl(?string $path): string
    {
        return $this->resolveMediaUrl($path, 'vastu/images/placeholder.jpg');
    }

    protected function resolveMediaUrl(?string $path, string $fallback): string
    {
        if (! $path) {
            return asset($fallback);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'frontend/')) {
            return asset($normalized);
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        return asset('storage/'.$normalized);
    }

    public function getFormattedPriceAttribute(): string
    {
        $price = (float) $this->selling_price > 0 ? $this->selling_price : $this->mrp;

        return \App\Support\Money::format($price);
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->offer) {
            return (int) $this->offer->discount_percent;
        }

        if ($this->mrp > 0 && $this->selling_price > 0 && $this->selling_price < $this->mrp) {
            return (int) round((($this->mrp - $this->selling_price) / $this->mrp) * 100);
        }

        return 0;
    }
}
