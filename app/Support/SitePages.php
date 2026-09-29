<?php

namespace App\Support;

/**
 * Pages whose content is written in Admin → Settings → Web Settings.
 * about-us and founder add to /about and /founder; the rest are the /info/{slug} pages
 * linked from the footer (they show "No information available" until content is added).
 */
class SitePages
{
    /**
     * @return array<string, array<string, string>> group label => [slug => title]
     */
    public static function groups(): array
    {
        return [
            'Main pages' => [
                'about-us' => 'About Us',
                'founder' => 'About the Founder',
            ],
            'Guidance' => [
                'vastu-consultation' => 'Vastu Consultation',
                'astrology' => 'Astrology',
                'numerology' => 'Numerology',
                'energy-analysis' => 'Energy Analysis',
                'book-appointment' => 'Book Appointment',
            ],
            'About' => [
                'our-story' => 'Our Story',
                '22-years-of-guidance' => '22+ Years of Guidance',
                'testimonials' => 'Testimonials',
                'contact-us' => 'Contact Us',
                'customer-support' => 'Customer Support',
                'coming-soon' => 'Coming Soon',
            ],
            'Policies' => [
                'privacy-policy' => 'Privacy Policy',
                'terms-and-conditions' => 'Terms & Conditions',
                'shipping-and-returns' => 'Shipping & Returns',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return array_merge(...array_values(self::groups()));
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function label(string $slug): string
    {
        return self::all()[$slug] ?? $slug;
    }

    public static function isValid(string $slug): bool
    {
        return array_key_exists($slug, self::all());
    }

    public static function url(string $slug): string
    {
        return match ($slug) {
            'about-us' => route('about'),
            'founder' => route('founder'),
            default => route('info', $slug),
        };
    }
}
