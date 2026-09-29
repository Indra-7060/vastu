<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Re-download the images shown in the homepage Instagram section (Home — Instagram Posts).
Artisan::command('instagram:refresh', function () {
    $banners = \App\Models\Banner::where('section', 'instagram_post')->with('images')->get();
    foreach ($banners as $banner) {
        $path = \App\Support\Instagram::downloadThumbnail((string) $banner->button_link);
        if (! $path) {
            $this->warn("Could not fetch image for {$banner->button_link}");
            continue;
        }
        $image = $banner->images->first();
        $image ? $image->update(['image' => $path, 'is_active' => true])
               : $banner->images()->create(['image' => $path, 'is_active' => true, 'sort_order' => 1]);
        $this->info("Saved {$path} for {$banner->button_link}");
    }
})->purpose('Download the post images for the homepage Instagram section');
