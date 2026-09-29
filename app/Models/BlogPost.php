<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'news_type_id',
        'image',
        'banner_image',
        'author_name',
        'excerpt',
        'content',
        'comments_count',
        'is_featured',
        'is_active',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if (empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public function newsType()
    {
        return $this->belongsTo(NewsType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->active()->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->resolveMediaUrl($this->image, 'vastu/images/placeholder.jpg');
    }

    public function homeCardImageUrl(int $index = 0): string
    {
        // Fallback image when a post has no photo of its own.
        $fallbacks = ['vastu/images/placeholder.jpg'];
        $fallback = $fallbacks[$index % count($fallbacks)];

        return $this->resolveMediaUrl($this->image, $fallback);
    }

    public function getBannerImageUrlAttribute(): string
    {
        if ($this->banner_image) {
            return $this->resolveMediaUrl($this->banner_image, 'vastu/images/placeholder.jpg');
        }

        if ($this->image) {
            return $this->resolveMediaUrl($this->image, 'vastu/images/placeholder.jpg');
        }

        return asset('vastu/images/placeholder.jpg');
    }

    protected function resolveMediaUrl(?string $path, string $fallback): string
    {
        if (! $path) {
            return $this->assetIfExists($fallback);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'frontend/')) {
            return $this->assetIfExists($normalized, $fallback);
        }

        if (str_starts_with($path, '/')) {
            $local = public_path(ltrim($path, '/'));

            return is_file($local) ? url($path) : $this->assetIfExists($fallback);
        }

        $publicStorage = public_path('storage/'.$normalized);
        if (is_file($publicStorage)) {
            return asset('storage/'.$normalized);
        }

        $base = basename($normalized);
        $frontendPath = 'frontend/images/blogs/'.$base;
        if (is_file(public_path($frontendPath))) {
            return asset($frontendPath);
        }

        return $this->assetIfExists($fallback);
    }

    protected function assetIfExists(string $path, ?string $alternate = null): string
    {
        if (is_file(public_path($path))) {
            return asset($path);
        }

        if ($alternate && is_file(public_path($alternate))) {
            return asset($alternate);
        }

        return asset($path);
    }

    public function getFormattedDateAttribute(): string
    {
        return optional($this->published_at)->format('F d Y') ?? '';
    }

    public function getMetaLineAttribute(): string
    {
        $author = $this->author_name ?: 'Admin';
        $date = strtoupper(optional($this->published_at)->format('F d Y') ?? '');

        return 'BY '.strtoupper($author).($date ? ', '.$date : '');
    }
}
