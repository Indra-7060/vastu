<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Gallery photos from the old website's Gallery page (vastutathastu.com/celebrities).
 * Named photos go to Celebrity (captioned with the names the old site gave them); the unnamed ones go
 * to Others without a caption, so the client can name them and move them in Admin → Gallery.
 * Safe to run again: entries are matched by their image, and edits made in the admin are kept.
 *
 *   php artisan db:seed --class=GalleryDemoSeeder
 */
class GalleryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $photos = [
            'gallery_celebrity' => [
                ['celebrity-17-udit-narayan.jpg', 'Udit Narayan'],
                ['celebrity-18-vishwajeet-joshi.jpg', 'Vishwajeet Joshi'],
                ['celebrity-20-salil-kulkarni-sandeep-khare.jpg', 'Salil Kulkarni & Sandeep Khare'],
                ['celebrity-21-pravin-tarde.jpg', 'Pravin Tarde'],
                ['celebrity-22-madhav-abhyankar.jpg', 'Madhav Abhyankar'],
                ['celebrity-23-hridaynath-mangeshkar-2.jpg', 'Hridaynath Mangeshkar'],
                ['celebrity-24-sandeep-patil.jpg', 'Sandeep Patil'],
                ['celebrity-25-gajendra-ahire.jpg', 'Gajendra Ahire'],
                ['celebrity-26-dhananjay-mahadik.jpg', 'Dhananjay Mahadik'],
                ['celebrity-27-ajay-atul.jpg', 'Ajay–Atul'],
                ['celebrity-28-radha-mangeshkar.jpg', 'Radha Mangeshkar'],
                ['celebrity-29-ankush-chaudhary.jpg', 'Ankush Chaudhary'],
                ['celebrity-30-asha-bhosle-2.jpg', 'Asha Bhosle'],
                ['celebrity-31-asha-bhosle.jpg', 'Asha Bhosle'],
                ['celebrity-32-akshaya-hardik.jpg', 'Akshaya & Hardik'],
                ['celebrity-42-yogesh-deshpande.jpg', 'Yogesh Deshpande'],
            ],
            'gallery_others' => [
                ['others-01.jpg', ''],
                ['others-02.jpg', ''],
                ['others-03.jpg', ''],
                ['others-04.jpg', ''],
                ['others-05.jpg', ''],
                ['others-06.jpg', ''],
                ['others-07.jpg', ''],
                ['others-08.jpg', ''],
                ['others-09.jpg', ''],
                ['others-10.jpg', ''],
                ['others-11.jpg', ''],
                ['others-12.jpg', ''],
                ['others-13.jpg', ''],
                ['others-14.jpg', ''],
                ['others-15.jpg', ''],
                ['others-16.jpg', ''],
                ['others-19.jpg', ''],
                ['others-33.jpg', ''],
                ['others-34.jpg', ''],
                ['others-35.jpg', ''],
                ['others-36.jpg', ''],
                ['others-37.jpg', ''],
                ['others-38.jpg', ''],
                ['others-39.jpg', ''],
                ['others-40.jpg', ''],
                ['others-41.jpg', ''],
                ['others-43.jpg', ''],
                ['others-44.jpg', ''],
                ['others-45.jpg', ''],
                ['others-46.jpg', ''],
                ['others-47.jpg', ''],
            ],
        ];

        $disk = Storage::disk('public');
        foreach ($photos as $section => $items) {
            foreach ($items as $i => [$file, $title]) {
                $path = 'banners/gallery/'.$file;
                if (! $disk->exists($path)) {
                    $disk->put($path, file_get_contents(database_path('seeders/gallery/old-site/'.$file)));
                }

                // Already imported (possibly renamed or moved in the admin): leave it as it is.
                if (Banner::query()->whereHas('images', fn ($q) => $q->where('image', $path))->exists()) {
                    continue;
                }

                $banner = Banner::create([
                    'section' => $section,
                    'title' => $title,
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]);
                $banner->images()->create(['image' => $path, 'is_active' => true, 'sort_order' => 0]);
            }
        }
    }
}
