<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'image',
        'mobile_image',
        'mobile_video',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function getIsVideoAttribute(): bool
    {
        return in_array(strtolower(pathinfo((string) $this->image, PATHINFO_EXTENSION)), [
            'mp4', 'webm', 'ogg', 'ogv', 'mov',
        ], true);
    }

    public function getVideoMimeTypeAttribute(): string
    {
        return self::mimeFor($this->image);
    }

    /** MIME type of the phone video (Home — Hero video slides). */
    public function getMobileVideoMimeTypeAttribute(): string
    {
        return self::mimeFor($this->mobile_video);
    }

    private static function mimeFor(?string $path): string
    {
        return match (strtolower(pathinfo((string) $path, PATHINFO_EXTENSION))) {
            'webm' => 'video/webm',
            'ogg', 'ogv' => 'video/ogg',
            'mov' => 'video/quicktime',
            default => 'video/mp4',
        };
    }
}
