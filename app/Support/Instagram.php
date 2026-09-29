<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Helpers for the homepage Instagram section. Admins may paste either a post / reel URL
 * or Instagram's full "Embed" code; only the canonical permalink is stored.
 */
class Instagram
{
    public const PROFILE_URL = 'https://www.instagram.com/vastutathastumakrannd_official/';

    /** Canonical permalink (https://www.instagram.com/{p|reel|tv}/{code}/) or null when none is found. */
    public static function permalink(?string $input): ?string
    {
        $input = html_entity_decode(trim((string) $input), ENT_QUOTES | ENT_HTML5);
        if ($input === '') {
            return null;
        }

        // Prefer the permalink attribute of the embed code, then any instagram.com/p|reel|tv link.
        if (preg_match('~data-instgrm-permalink=["\']([^"\']+)~i', $input, $m)) {
            $input = $m[1];
        }

        if (preg_match('~instagram\.com/(?:[A-Za-z0-9_.]+/)?(p|reel|reels|tv)/([A-Za-z0-9_-]+)~i', $input, $m)) {
            $type = strtolower($m[1]) === 'reels' ? 'reel' : strtolower($m[1]);

            return 'https://www.instagram.com/'.$type.'/'.$m[2].'/';
        }

        return null;
    }

    /** "Reel" or "Post" for labels. */
    public static function kind(?string $permalink): string
    {
        return str_contains((string) $permalink, '/reel/') || str_contains((string) $permalink, '/tv/') ? 'Reel' : 'Post';
    }

    /** The short code of a permalink (e.g. DdqcmR7qca_). */
    public static function code(?string $permalink): ?string
    {
        return preg_match('~/(?:p|reel|tv)/([A-Za-z0-9_-]+)~', (string) $permalink, $m) ? $m[1] : null;
    }

    /**
     * Download the post's own image (photo or reel cover) into public storage so the homepage
     * can show just the picture. Returns the storage path (banners/instagram/CODE.jpg) or null.
     */
    public static function downloadThumbnail(string $permalink): ?string
    {
        $code = self::code($permalink);
        if (! $code) {
            return null;
        }

        $candidates = [];
        try {
            // 1) Full-size photo (posts)
            $candidates[] = rtrim($permalink, '/').'/media/?size=l';
            // 2) Link-preview image (works for posts and reels)
            $page = Http::timeout(15)->withHeaders(['User-Agent' => 'facebookexternalhit/1.1'])->get($permalink);
            if ($page->ok() && preg_match('~<meta property="og:image" content="([^"]+)"~', $page->body(), $m)) {
                $candidates[] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
            }
        } catch (\Throwable $e) {
            Log::info('Instagram preview lookup failed: '.$e->getMessage());
        }

        foreach ($candidates as $url) {
            try {
                $res = Http::timeout(20)->withHeaders(['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36'])->get($url);
                $type = (string) $res->header('Content-Type');
                if ($res->ok() && str_starts_with($type, 'image/') && strlen($res->body()) > 2000) {
                    $path = 'banners/instagram/'.$code.'.jpg';
                    Storage::disk('public')->put($path, $res->body());

                    return $path;
                }
            } catch (\Throwable $e) {
                Log::info('Instagram image download failed: '.$e->getMessage());
            }
        }

        return null;
    }
}
