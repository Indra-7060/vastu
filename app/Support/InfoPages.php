<?php

namespace App\Support;

/**
 * Placeholder information pages linked from the footer (and a few header/home links).
 * Each one renders "No information available" until real content is written.
 */
class InfoPages
{
    /**
     * @return array<string, string> slug => title
     */
    public static function all(): array
    {
        return [
            // Guidance
            'vastu-consultation' => 'Vastu Consultation',
            'astrology' => 'Astrology',
            'numerology' => 'Numerology',
            'energy-analysis' => 'Energy Analysis',
            'book-appointment' => 'Book Appointment',
            'book-a-consultation' => 'Book a Consultation',
            // About
            'our-story' => 'Our Story',
            '22-years-of-guidance' => '22+ Years of Guidance',
            'testimonials' => 'Testimonials',
            'contact-us' => 'Contact Us',
            'stores' => 'Stores',
            'customer-support' => 'Customer Support',
            'coming-soon' => 'Coming Soon',
            // Social
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'facebook' => 'Facebook',
            // Legal
            'privacy-policy' => 'Privacy Policy',
            'terms-and-conditions' => 'Terms & Conditions',
            'shipping-and-returns' => 'Shipping & Returns',
        ];
    }

    public static function title(string $slug): ?string
    {
        return self::all()[$slug] ?? null;
    }
}
