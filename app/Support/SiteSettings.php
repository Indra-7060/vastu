<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Editable site-wide details (Admin → Site Details): contact details, social links, footer text
 * and the standard sections shown on every product page. Each field falls back to its default.
 */
class SiteSettings
{
    private static ?array $values = null;

    /**
     * @return array<string, array{label: string, hint: string, fields: array<string, array{label: string, type: string, default: string, hint?: string}>}>
     */
    public static function groups(): array
    {
        return [
            'contact' => [
                'label' => 'Contact details',
                'hint' => 'Shown in the footer. Leave a field empty to hide it.',
                'fields' => [
                    'contact_phone' => ['label' => 'Phone', 'type' => 'text', 'default' => '', 'hint' => 'e.g. +91 98765 43210'],
                    'contact_whatsapp' => ['label' => 'WhatsApp number', 'type' => 'text', 'default' => '919075566319', 'hint' => 'With country code (91 for India), e.g. 919075566319. Used by the green WhatsApp button on every page and the footer link. Leave empty to hide the button.'],
                    'whatsapp_message' => ['label' => 'WhatsApp opening message', 'type' => 'text', 'default' => 'Hello Vastutathastu, I would like to know more.', 'hint' => 'Typed into the chat for the customer when they tap the button. Leave empty for a blank chat.'],
                    'contact_email' => ['label' => 'Email', 'type' => 'email', 'default' => ''],
                    'footer_location' => ['label' => 'Location', 'type' => 'text', 'default' => 'Nashik, Maharashtra, India'],
                ],
            ],
            'social' => [
                'label' => 'Social links',
                'hint' => 'Full profile links. Empty links are hidden from the footer.',
                'fields' => [
                    'social_instagram' => ['label' => 'Instagram', 'type' => 'url', 'default' => Instagram::PROFILE_URL],
                    'social_youtube' => ['label' => 'YouTube', 'type' => 'url', 'default' => ''],
                    'social_facebook' => ['label' => 'Facebook', 'type' => 'url', 'default' => 'https://www.facebook.com/VastutathastuMakranndofficial/'],
                ],
            ],
            'footer' => [
                'label' => 'Footer text',
                'hint' => 'Text in the footer on every page.',
                'fields' => [
                    'footer_about' => ['label' => 'About line (under the logo)', 'type' => 'textarea', 'default' => 'Authentic Vedic wisdom and sacred products for harmonious homes, workplaces, and lives.'],
                    'footer_tagline' => ['label' => 'Tagline (under the location)', 'type' => 'text', 'default' => 'Guidance • Products • Consultations'],
                    'newsletter_title' => ['label' => 'Newsletter heading', 'type' => 'text', 'default' => 'Sacred guidance, delivered.'],
                    'newsletter_text' => ['label' => 'Newsletter text', 'type' => 'textarea', 'default' => 'Receive new product updates, Vedic insights, and practical guidance.'],
                ],
            ],
            'product_page' => [
                'label' => 'Product page — standard sections',
                'hint' => 'The last two sections on every product page. Write one paragraph per line. Leave the text empty to hide a section.',
                'fields' => [
                    'pdp_shipping_title' => ['label' => 'Shipping section title', 'type' => 'text', 'default' => 'Shipping & returns'],
                    'pdp_shipping_text' => ['label' => 'Shipping section text', 'type' => 'textarea', 'default' => 'Pan-India delivery. Orders are packed with care and dispatched within 1–2 business days; delivery usually takes 3–7 business days after dispatch.', 'hint' => 'The shipping charge from Settings → Shipping and a link to the Shipping & Returns page are added automatically.'],
                    'pdp_consult_title' => ['label' => 'Consultation section title', 'type' => 'text', 'default' => 'Book a consultation'],
                    'pdp_consult_text' => ['label' => 'Consultation section text', 'type' => 'textarea', 'default' => 'Get personal guidance on choosing and placing :product for your home or workplace.', 'hint' => 'Write :product to insert the product name. A "Book a consultation" button is added below.'],
                ],
            ],
        ];
    }

    /** @return array<string, array{label: string, type: string, default: string}> */
    public static function fields(): array
    {
        return array_merge(...array_values(array_map(fn ($g) => $g['fields'], self::groups())));
    }

    public static function get(string $key): string
    {
        $default = self::fields()[$key]['default'] ?? '';
        $values = self::load();

        return array_key_exists($key, $values) ? (string) $values[$key] : $default;
    }

    /** @param array<string, ?string> $values */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! array_key_exists($key, self::fields())) {
                continue;
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => trim((string) $value)]);
        }
        self::$values = null;
    }

    private static function load(): array
    {
        if (self::$values === null) {
            try {
                self::$values = SiteSetting::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) { // table not migrated yet
                self::$values = [];
            }
        }

        return self::$values;
    }
}
