<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\TileImage;
use Illuminate\Console\Command;

/** php artisan vastu:tile-images — (re)build the square tile crops of every product's main photo. */
class MakeTileImages extends Command
{
    protected $signature = 'vastu:tile-images';

    protected $description = 'Build square tile crops so products look a similar size in listing tiles';

    public function handle(): int
    {
        $counts = ['cropped' => 0, 'skipped' => 0, 'error' => 0];
        Product::query()->whereNotNull('featured_image')->orderBy('id')->each(function (Product $p) use (&$counts) {
            $result = TileImage::make($p->featured_image);
            $counts[$result]++;
            $this->line(str_pad($result, 8).' '.$p->slug);
        });
        $this->info("Cropped {$counts['cropped']}, kept as is {$counts['skipped']}, errors {$counts['error']}.");

        return self::SUCCESS;
    }
}
