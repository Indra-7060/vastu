<?php

namespace App\Support;

class Media
{
    public static function url(?string $path, string $fallback = 'vastu/images/placeholder.jpg'): string
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
}
