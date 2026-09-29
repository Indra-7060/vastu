<?php

namespace App\Http\Middleware;

use App\Support\ImageOptimizer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optimises every image uploaded on the site (admin banners, products, categories, journal,
 * stores, coupons, customer avatars, review photos …) before any controller stores it.
 */
class OptimizeUploadedImages
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->files->count() > 0) {
            $done = [];
            foreach ($this->files($request->allFiles()) as $file) {
                if (! $file->isValid() || ! str_starts_with((string) $file->getMimeType(), 'image/')) {
                    continue;
                }
                // The same temp file can appear under several input names — optimise it once.
                if (isset($done[$file->getRealPath()])) {
                    continue;
                }
                $done[$file->getRealPath()] = true;
                $stats = ImageOptimizer::optimize($file->getRealPath());
                if ($stats) {
                    Log::info(sprintf(
                        'Optimised upload "%s": %s KB → %s KB (%dx%d, %s)',
                        $file->getClientOriginalName(),
                        number_format($stats['before'] / 1024),
                        number_format($stats['after'] / 1024),
                        $stats['width'],
                        $stats['height'],
                        $stats['mime']
                    ));
                }
            }
        }

        return $next($request);
    }

    /** @return iterable<UploadedFile> */
    private function files(array $files): iterable
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                yield from $this->files($file);
            } elseif ($file instanceof UploadedFile) {
                yield $file;
            }
        }
    }
}
