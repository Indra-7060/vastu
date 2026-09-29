<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Default homepage / site sections (Admin → Home Content → Sections & Images).
 * Creates each section once — existing banners are never overwritten, so admin edits are kept.
 *
 *   php artisan db:seed --class=VastuHomeContentSeeder
 */
class VastuHomeContentSeeder extends Seeder
{
    public function run(): void
    {
        // Copy the default media from public/vastu into the uploads folder (public/storage/banners/vastu).
        // Each banner gets its own file, because deleting a banner in admin deletes its files.
        $target = public_path('storage/banners/vastu');
        File::ensureDirectoryExists($target);
        $files = [
            'hero.mp4' => public_path('vastu/video/hero.mp4'),
            'hero-still.jpg' => public_path('vastu/images/hero-poster.jpg'),
            'shop-banner.jpg' => public_path('vastu/images/hero-poster.jpg'),
            'spotlight-7-chakra-crystal-tree.jpg' => public_path('vastu/images/feature-7-chakra-crystal-tree.jpg'),
            'founder-makrannd-sardeshmukh.jpg' => public_path('vastu/images/founder-makrannd-sardeshmukh.jpg'),
            'hero-7-chakra-crystal-tree.jpg' => public_path('vastu/images/feature-7-chakra-crystal-tree.jpg'),
            'hero-founder.jpg' => public_path('vastu/images/founder-makrannd-sardeshmukh.jpg'),
        ];
        foreach ($files as $name => $source) {
            if (is_file($source) && ! is_file($target.'/'.$name)) {
                File::copy($source, $target.'/'.$name);
            }
        }

        $sections = [
            ['section' => 'announcement_bar', 'title' => 'Complimentary guidance on select orders', 'sort_order' => 1],
            ['section' => 'announcement_bar', 'title' => 'Pan-India delivery available', 'sort_order' => 2],
            ['section' => 'announcement_bar', 'title' => 'Authentic, thoughtfully sourced products', 'sort_order' => 3],
            ['section' => 'announcement_bar', 'title' => 'Expert placement guidance available', 'sort_order' => 4],
            ['section' => 'announcement_bar', 'title' => 'Secure checkout • Trusted support', 'sort_order' => 5],
            [
                'section' => 'home_hero',
                'subtitle' => 'Trusted Vedic Guidance',
                'title' => 'Sacred Living by Vastutathastu',
                'sort_order' => 1,
                'button_text' => 'Shop now', 'button_link' => '/checkout',
                'button_text_2' => 'Book a consultation', 'button_link_2' => '/info/book-a-consultation',
                'media' => ['image' => 'banners/vastu/hero.mp4', 'mobile_image' => 'banners/vastu/hero-still.jpg'],
            ],
            [
                'section' => 'home_hero',
                'subtitle' => 'Balance, Beautifully Crafted',
                'title' => '7 Chakra Crystal Tree',
                'button_text' => 'Shop now', 'button_link' => '/product/7-chakra-tree',
                'sort_order' => 2,
                'media' => ['image' => 'banners/vastu/hero-7-chakra-crystal-tree.jpg'],
            ],
            [
                'section' => 'home_hero',
                'subtitle' => '22+ Years of Vedic Wisdom',
                'title' => 'Meet Makrannd Sardeshmukh',
                'button_text' => 'About the founder', 'button_link' => '/founder',
                'button_text_2' => 'Book a consultation', 'button_link_2' => '/info/book-a-consultation',
                'sort_order' => 3,
                'media' => ['image' => 'banners/vastu/hero-founder.jpg'],
            ],
            [
                'section' => 'home_intro',
                'title' => 'Trusted Vedic Guidance for 22+ Years',
                'description' => 'Vastutathastu unites Vedic Vastushastra, astrology and numerology with authentic sacred products to help homes, workplaces and lives move into greater harmony.',
            ],
            [
                'section' => 'home_spotlight',
                'title' => '7 Chakra Crystal Tree',
                'description' => 'Balance your space with Vastutathastu’s 7 Chakra Crystal Tree, crafted with natural crystal chips representing the seven chakras. A vibrant décor piece for desks, meditation corners, living spaces, and meaningful gifting. Price: ₹810.',
                'button_text' => 'Shop now', 'button_link' => '/product/7-chakra-tree',
                'media' => ['image' => 'banners/vastu/spotlight-7-chakra-crystal-tree.jpg'],
            ],
            [
                'section' => 'home_founder',
                'title' => 'Meet Makrannd Sardeshmukh',
                'description' => 'Founder & Director of Vastutathastu. Specialist in Vedic Vastushastra and astrology, Building Biology, Geopathology, and Energy Architecture.',
                'button_text' => 'About the founder', 'button_link' => '/founder',
                'media' => ['image' => 'banners/vastu/founder-makrannd-sardeshmukh.jpg'],
            ],
            [
                'section' => 'instagram_intro',
                'subtitle' => 'On Instagram',
                'title' => 'Moments of mindful living',
                'description' => 'Follow @vastutathastumakrannd_official for Vastu tips, energised product rituals and glimpses from our consultations.',
                'button_text' => 'Follow on Instagram', 'button_link' => 'https://www.instagram.com/vastutathastumakrannd_official/',
            ],
            ['section' => 'instagram_post', 'title' => 'Instagram post', 'button_link' => 'https://www.instagram.com/p/DdqcmR7qca_/', 'sort_order' => 1],
            ['section' => 'instagram_post', 'title' => 'Instagram post', 'button_link' => 'https://www.instagram.com/p/DdoP_hFKXeN/', 'sort_order' => 2],
            ['section' => 'instagram_post', 'title' => 'Instagram reel', 'button_link' => 'https://www.instagram.com/reel/Dd0QFamvuTD/', 'sort_order' => 3],
            [
                'section' => 'consultation_cta',
                'subtitle' => 'Personal Vedic Guidance',
                'title' => 'Create harmony in every space.',
                'description' => 'Book a personal consultation for Vastu, astrology, numerology, and sacred-product guidance.',
                'button_text' => 'BOOK A CONSULTATION', 'button_link' => '/info/book-a-consultation',
            ],
            [
                'section' => 'shop_banner',
                'title' => 'New Arrivals banner',
                'media' => ['image' => 'banners/vastu/shop-banner.jpg'],
            ],
        ];

        foreach ($sections as $row) {
            $media = $row['media'] ?? null;
            unset($row['media']);

            // Instagram posts are identified by their link; everything else by its title.
            $match = $row['section'] === 'instagram_post' ? ['button_link' => $row['button_link']] : ['title' => $row['title']];
            if (Banner::where('section', $row['section'])->where($match)->exists()) {
                continue;
            }

            $banner = Banner::create($row + ['is_active' => true, 'sort_order' => $row['sort_order'] ?? 0]);

            if ($media) {
                $banner->images()->create($media + ['is_active' => true, 'sort_order' => 1]);
            }
        }
    }
}
