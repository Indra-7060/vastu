<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Client logos from the old website's "Client Logos" carousel (vastutathastu.com home page).
 * They go to Admin → Gallery → Client Logos and glide on the home page below "Our Clients".
 * Safe to run again: entries are matched by their image, and edits made in the admin are kept.
 *
 *   php artisan db:seed --class=ClientLogosSeeder
 */
class ClientLogosSeeder extends Seeder
{
    public function run(): void
    {
        $logos = [
            ['01-surabhi-beach-resort.jpg', 'Surabhi Beach Resort'],
            ['02-shivdatta-associates.jpg', 'Shivdatta Associates'],
            ['03-jps.jpg', ''],
            ['04-kool-homes.jpg', 'Kool Homes'],
            ['05-ranka-jewellers.jpg', 'Ranka Jewellers'],
            ['06-revell-creations.jpg', 'Revell Creations'],
            ['07-newton-software.jpg', 'Newton Software'],
            ['08-raviraj.jpg', 'Raviraj'],
            ['09-ekbote.jpg', 'Ekbote'],
            ['10-aura-beauty-world.jpg', 'Aura Beauty World'],
            ['11-elite.jpg', 'Elite'],
            ['12-novasix-technologies.png', 'NovaSix Technologies'],
            ['13-colosceum.jpg', 'Colosceum'],
            ['14-agro-hitech.jpg', 'Agro Hitech'],
            ['15-bj-industries.png', 'B.J. Industries'],
            ['16-mahendra-jewellers.jpg', 'Mahendra Jewellers'],
            ['17-shuddh-oke-kapur.jpg', 'Shuddh Oke Kapur'],
            ['18-esbee.jpg', 'Esbee'],
            ['19-prranish-perfume-store.jpg', 'Prranish — The Perfume Store'],
            ['20-kiosk-kaffee.jpg', 'Kiosk Kaffee'],
        ];

        $disk = Storage::disk('public');
        foreach ($logos as $i => [$file, $title]) {
            $path = 'banners/client-logos/'.$file;
            if (! $disk->exists($path)) {
                $disk->put($path, file_get_contents(database_path('seeders/gallery/client-logos/'.$file)));
            }

            if (Banner::query()->whereHas('images', fn ($q) => $q->where('image', $path))->exists()) {
                continue;
            }

            $banner = Banner::create([
                'section' => 'gallery_client_logos',
                'title' => $title,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
            $banner->images()->create(['image' => $path, 'is_active' => true, 'sort_order' => 0]);
        }
    }
}
