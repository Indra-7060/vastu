<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Latest posts from the client's Instagram account (official Instagram API — no scraping).
 *
 * Setup: put the account's long-lived access token in .env as INSTAGRAM_ACCESS_TOKEN.
 * - Posts are fetched at most every INSTAGRAM_CACHE_MINUTES (default 30), so new uploads
 *   appear on the homepage automatically.
 * - Each post's photo (or reel cover) is copied to public/storage/banners/instagram/feed/,
 *   because Instagram's own image links expire after a few days.
 * - Long-lived tokens last 60 days; this class refreshes the token every 7 days and keeps
 *   the newest one in storage/app/instagram-token.json, so it never runs out.
 * - If Instagram can't be reached, the last good set of posts keeps showing; with no token
 *   at all, the homepage falls back to the posts added in Admin → Sections & Images.
 */
class InstagramFeed
{
    private const TOKEN_FILE = 'instagram-token.json';
    private const CACHE_KEY = 'vt.instagram.feed';
    private const LAST_GOOD_KEY = 'vt.instagram.feed.last-good';

    public static function enabled(): bool
    {
        return (bool) static::token();
    }

    /**
     * Newest posts: [['url', 'image', 'caption', 'kind' => 'Post'|'Reel', 'date' => Carbon], ...]
     * Returns null when the live feed isn't configured or has never loaded.
     */
    public static function latest(?int $limit = null): ?array
    {
        if (! static::enabled()) {
            return null;
        }

        $limit = $limit ?: max(1, config('services.instagram.limit', 3));
        $minutes = max(5, config('services.instagram.cache_minutes', 30));

        $posts = Cache::remember(self::CACHE_KEY.'.'.$limit, now()->addMinutes($minutes), function () use ($limit) {
            $fresh = static::fetch($limit);
            if ($fresh) {
                Cache::forever(self::LAST_GOOD_KEY.'.'.$limit, $fresh);
            }

            return $fresh ?: false;
        });

        return $posts ?: Cache::get(self::LAST_GOOD_KEY.'.'.$limit);
    }

    /** Clear the cached feed (e.g. after changing the token). */
    public static function flush(): void
    {
        foreach ([3, 4, 6, 9, 12] as $n) {
            Cache::forget(self::CACHE_KEY.'.'.$n);
        }
    }

    protected static function fetch(int $limit): ?array
    {
        $token = static::token();
        if (! $token) {
            return null;
        }

        try {
            $res = Http::timeout(8)->get('https://graph.instagram.com/me/media', [
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp',
                'limit' => $limit,
                'access_token' => $token,
            ]);

            if (! $res->successful()) {
                report(new \RuntimeException('Instagram feed request failed: HTTP '.$res->status().' '.Str::limit($res->body(), 300)));

                return null;
            }

            $posts = [];
            foreach (array_slice($res->json('data') ?? [], 0, $limit) as $item) {
                $remote = ($item['media_type'] ?? '') === 'VIDEO' ? ($item['thumbnail_url'] ?? null) : ($item['media_url'] ?? null);
                $image = $remote ? static::storeImage((string) $item['id'], $remote) : null;
                if (! $image || empty($item['permalink'])) {
                    continue;
                }

                $posts[] = [
                    'url' => $item['permalink'],
                    'image' => $image,
                    'caption' => static::shortCaption($item['caption'] ?? ''),
                    'kind' => ($item['media_type'] ?? '') === 'VIDEO' ? 'Reel' : 'Post',
                    'date' => isset($item['timestamp']) ? \Illuminate\Support\Carbon::parse($item['timestamp']) : null,
                ];
            }

            static::refreshTokenIfDue($token);

            return $posts ?: null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Copy the post image to our own storage once (Instagram's CDN links expire). Returns its public URL. */
    protected static function storeImage(string $id, string $remoteUrl): ?string
    {
        $path = 'banners/instagram/feed/'.preg_replace('/[^0-9A-Za-z_-]/', '', $id).'.jpg';
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            try {
                $img = Http::timeout(15)->get($remoteUrl);
                if (! $img->successful() || ! str_starts_with((string) $img->header('Content-Type'), 'image/')) {
                    return null;
                }
                $disk->put($path, $img->body());
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        }

        return SiteBanners::url($path);
    }

    /** First line of the caption, without hashtags, kept short for the card title. */
    protected static function shortCaption(string $caption): string
    {
        $line = trim(strtok(str_replace("\r", '', $caption), "\n") ?: '');
        $line = trim(preg_replace('/(^|\s)#\S+/u', '', $line));

        return Str::limit($line, 60);
    }

    /** Current token: the auto-refreshed one if present, otherwise the one from .env. */
    protected static function token(): ?string
    {
        $env = config('services.instagram.access_token');
        if (! $env) {
            return null;
        }

        $saved = static::savedToken();

        // Use the refreshed token only while it belongs to the token set in .env.
        return ($saved && ($saved['source'] ?? null) === hash('sha256', $env)) ? $saved['token'] : $env;
    }

    protected static function savedToken(): ?array
    {
        try {
            $raw = Storage::disk('local')->get(self::TOKEN_FILE);

            return $raw ? json_decode($raw, true) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** Long-lived tokens expire after 60 days; refresh once a week so it never lapses. */
    protected static function refreshTokenIfDue(string $token): void
    {
        $saved = static::savedToken();
        $last = isset($saved['refreshed_at']) ? \Illuminate\Support\Carbon::parse($saved['refreshed_at']) : null;
        if ($last && $last->gt(now()->subDays(7)) && ($saved['token'] ?? null) === $token) {
            return;
        }

        try {
            $res = Http::timeout(8)->get('https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $token,
            ]);
            if ($res->successful() && $res->json('access_token')) {
                Storage::disk('local')->put(self::TOKEN_FILE, json_encode([
                    'token' => $res->json('access_token'),
                    'source' => hash('sha256', (string) config('services.instagram.access_token')),
                    'refreshed_at' => now()->toIso8601String(),
                    'expires_in' => $res->json('expires_in'),
                ]));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
