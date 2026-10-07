<?php

namespace App\Support;

/**
 * Editable storefront sections (Admin → Home Content → Sections & Images).
 * A section is shown on the website only while it has an active banner, so admins
 * can add, edit, hide or delete each one independently.
 *
 * 'media'  => 'none' | 'image' | 'image_or_video'
 * 'multi'  => several active banners are shown (e.g. announcement messages)
 * 'fields' => which banner fields the storefront uses (shown as guidance in the form)
 */
class BannerSections
{
    public static function all(): array
    {
        return [
            'announcement_bar' => [
                'label' => 'Announcement Bar',
                'page' => 'All pages',
                'hint' => 'One short message per banner, shown in the rotating strip at the very top of every page. Add several banners for several messages.',
                'fields' => ['Title' => 'The message text'],
                'media' => 'none',
                'multi' => true,
            ],
            'home_hero' => [
                'label' => 'Home — Hero',
                'page' => 'Home',
                'hint' => 'Full-screen slideshow at the top of the homepage. Each banner is one slide; slides change every 5 seconds (ordered by Display order). Slides are pictures only — design the text and “Shop now” button into the image — and clicking a slide opens its link.',
                'fields' => ['Link when the picture is clicked' => 'e.g. /rudraksh or /shop or a product page', 'Desktop image / video' => 'Wide, 1920×1080', 'Mobile image' => 'Tall, 1080×1920 — shown on phones (for a video: also its loading still)'],
                'media' => 'image_or_video',
                'multi' => true,
            ],
            'home_intro' => [
                'label' => 'Home — Introduction',
                'page' => 'Home',
                'hint' => 'Heading and short paragraph directly below the hero ("Trusted Vedic Guidance…").',
                'fields' => ['Title' => 'Heading', 'Description' => 'Paragraph'],
                'media' => 'none',
                'multi' => false,
            ],
            'home_spotlight' => [
                'label' => 'Home — Spotlight',
                'page' => 'Home',
                'hint' => 'Large image with text beside it, below "Shop by Category" (e.g. a featured product).',
                'fields' => ['Title' => 'Heading', 'Description' => 'Paragraph', 'Button 1' => 'Link below the text', 'Media' => 'Image (portrait or square, 1200px+)'],
                'media' => 'image',
                'multi' => false,
            ],
            'home_founder' => [
                'label' => 'Home — Founder',
                'page' => 'Home',
                'hint' => 'Founder portrait with introduction, below the product carousel.',
                'fields' => ['Title' => 'Heading', 'Description' => 'Paragraph', 'Button 1' => 'Link below the text', 'Media' => 'Portrait image'],
                'media' => 'image',
                'multi' => false,
            ],
            'instagram_intro' => [
                'label' => 'Home — Instagram Heading',
                'page' => 'Home (above the footer)',
                'hint' => 'Heading of the Instagram section after "Wisdom for Harmonious Living". Put your Instagram profile link in Button 1 link.',
                'fields' => ['Subtitle' => 'Small label above the heading', 'Title' => 'Heading', 'Description' => 'Short line under the heading', 'Button 1' => 'Button text + Instagram profile link'],
                'media' => 'none',
                'multi' => false,
            ],
            'instagram_post' => [
                'label' => 'Home — Instagram Posts',
                'page' => 'Home (above the footer)',
                'hint' => 'One Instagram post or reel per banner (3 look best). In Instagram open the post → ••• → Embed → Copy embed code, and paste it (or just the post link) in the "Instagram post" box. The post\'s image is fetched automatically and shown on the homepage with an Instagram icon. Hide or delete a banner to remove it; Display order sets the order.',
                'fields' => ['Instagram post' => 'Post / reel link or the full embed code', 'Title' => 'Optional name for this list (not shown on the website)', 'Image' => 'Optional — the post\'s own image is downloaded automatically; upload one only to use a different cover'],
                'media' => 'image',
                'multi' => true,
            ],
            'consultation_cta' => [
                'label' => 'Consultation Banner',
                'page' => 'All pages (above footer)',
                'hint' => 'The "Create harmony in every space" strip shown above the footer on every page.',
                'fields' => ['Subtitle' => 'Small label above the heading', 'Title' => 'Heading', 'Description' => 'Paragraph', 'Button 1' => 'Button'],
                'media' => 'none',
                'multi' => false,
            ],
            'gallery_product_users' => [
                'label' => 'Gallery — Our Product Users',
                'page' => 'Gallery (/gallery/our-product-users)',
                'hint' => 'Photos of customers with their Vastutathastu products. One photo per entry; Display order sets the order. The tab shows "Photos coming soon" until a photo is added.',
                'fields' => ['Title' => 'Photo title, e.g. the product shown', 'Description' => 'Short line under the title (optional)', 'Media' => 'Photo (any shape, 1200px+ recommended)'],
                'media' => 'image',
                'multi' => true,
            ],
            'gallery_awards' => [
                'label' => 'Gallery — Awards',
                'page' => 'Gallery (/gallery/awards)',
                'hint' => 'Awards and recognitions. One photo per entry; Display order sets the order. The tab shows "Photos coming soon" until a photo is added.',
                'fields' => ['Title' => 'Award name', 'Description' => 'What the award was given for / who gave it', 'Media' => 'Photo (any shape, 1200px+ recommended)'],
                'media' => 'image',
                'multi' => true,
            ],
            'gallery_celebrity' => [
                'label' => 'Gallery — Celebrity',
                'page' => 'Gallery (/gallery/celebrity)',
                'hint' => 'Makrannd Sardeshmukh with celebrities (photos from the old website\'s Gallery). Title = the celebrity\'s name. One photo per entry; Display order sets the order. The tab shows "Photos coming soon" until a photo is added.',
                'fields' => ['Title' => 'Celebrity name', 'Description' => 'Visit, event or consultation details', 'Media' => 'Photo (any shape, 1200px+ recommended)'],
                'media' => 'image',
                'multi' => true,
            ],
            'gallery_others' => [
                'label' => 'Gallery — Others',
                'page' => 'Gallery (/gallery/others)',
                'hint' => 'Events, visits, seminars and other moments. Title is optional; leave it empty to show the photo without a caption. One photo per entry; Display order sets the order. The tab shows "Photos coming soon" until a photo is added.',
                'fields' => ['Title' => 'Photo title', 'Description' => 'Short line under the title (optional)', 'Media' => 'Photo (any shape, 1200px+ recommended)'],
                'media' => 'image',
                'multi' => true,
            ],
            'shop_banner' => [
                'label' => 'New Arrivals Page Banner',
                'page' => 'Shop / New Arrivals',
                'hint' => 'Wide image at the top of the New Arrivals (/shop) page. Category pages use their own category image.',
                'fields' => ['Title' => 'For admin reference', 'Desktop image' => 'Wide image (1920×600 or larger)', 'Mobile image' => 'Optional, for phones (about 1080×1080)'],
                'media' => 'image',
                'multi' => false,
            ],
            'journal_page_banner' => [
                'label' => 'Journal Page Banner',
                'page' => 'Journal / Blog',
                'hint' => 'Heading, intro text and image at the top of the /blog journal page.',
                'fields' => ['Subtitle' => 'Heading', 'Description' => 'Intro text', 'Desktop image' => 'Wide image', 'Mobile image' => 'Optional, for phones'],
                'media' => 'image',
                'multi' => false,
            ],
        ];
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::all() as $key => $meta) {
            $options[$key] = $meta['label'].' ('.$meta['page'].')';
        }

        return $options;
    }

    public static function label(string $section): string
    {
        return self::all()[$section]['label'] ?? $section;
    }

    public static function page(string $section): string
    {
        return self::all()[$section]['page'] ?? '—';
    }

    public static function media(string $section): string
    {
        return self::all()[$section]['media'] ?? 'image';
    }

    /** Gallery sections have their own admin page (Admin → Gallery). */
    public static function isGallery(?string $section): bool
    {
        return str_starts_with((string) $section, 'gallery_');
    }

    /** Admin list a banner of this section belongs to (Gallery page or Sections & Images). */
    public static function listUrl(?string $section): string
    {
        return self::isGallery($section)
            ? route('admin.banners.index', ['group' => 'gallery']).'#section-'.$section
            : route('admin.banners.index');
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }
}
