<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Privacy Policy, Terms & Conditions and Shipping & Returns — the client's own wording from the old
 * vastutathastu.com policy pages (lightly tidied). Fills a page only while it is still empty, so text
 * edited later in Admin → Settings → Web Settings → Policies is never overwritten.
 *
 *   php artisan db:seed --class=PolicyPagesSeeder
 */
class PolicyPagesSeeder extends Seeder
{
    public function run(): void
    {
        $email = '<a href="mailto:vastubalaji@gmail.com">vastubalaji@gmail.com</a>';

        $pages = [
            'privacy-policy' => ['Privacy Policy', <<<HTML
<p>At Vastu Tathastu, we value the privacy and security of your personal information. This Privacy Policy explains how we collect, use and protect the information you provide while using our website and services.</p>
<h3>Information we collect</h3>
<p>We collect personal information such as your name, email address, phone number, delivery address and order details when you use our services, request a consultation or make a purchase on our website. Online payments are handled securely by our payment partner; we do not store your card or bank details.</p>
<h3>How we use your information</h3>
<p>Your information is used to provide our products and services, process your orders and payments, and contact you about your orders, consultations, updates and offers. We may also use it to improve our services.</p>
<h3>Data protection</h3>
<p>We use appropriate security measures to protect your personal information. We do not share your data with third parties except where needed to deliver our services (such as payment processing and delivery) or where required by law.</p>
<h3>Cookies</h3>
<p>We may use cookies to improve your experience, understand how our website is used and personalise content. You can disable cookies in your browser settings.</p>
<h3>Your rights</h3>
<p>You have the right to access, update or delete your personal information. Please contact us with any questions about your data.</p>
<h3>Changes to this policy</h3>
<p>We may update this Privacy Policy from time to time. Any changes will be posted on this page.</p>
<p>For more information, please contact us at {$email}.</p>
HTML],
            'terms-and-conditions' => ['Terms & Conditions', <<<HTML
<p>By using Vastutathastu’s website and services, you agree to the following terms.</p>
<h3>Services</h3>
<p>We provide Vedic Vastu, Astrology, Numerology and related consulting services, along with Vastu and spiritual products.</p>
<h3>Orders</h3>
<p>All orders are subject to acceptance and availability. Please check your order carefully before purchasing.</p>
<h3>No returns</h3>
<p>We do not offer returns on any products or services once purchased.</p>
<h3>Shipping</h3>
<p>Orders are processed within 2–3 business days, and delivery typically takes 5–7 business days. Please see our <a href="shipping-and-returns">Shipping &amp; Returns</a> policy for details.</p>
<h3>Limitation of liability</h3>
<p>Vastutathastu is not responsible for any damages resulting from the use of our services or products.</p>
<p>For any questions about these terms, please contact us at {$email}.</p>
HTML],
            'shipping-and-returns' => ['Shipping & Returns', <<<HTML
<p>At Vastutathastu, we aim to give you a smooth shopping experience. Please read our shipping and returns policy below.</p>
<h3>Processing time</h3>
<p>Orders are processed within 2–3 business days after confirmation. Custom products may take longer.</p>
<h3>Shipping methods</h3>
<p>We offer standard and expedited shipping options. Shipping charges, if any, are shown at checkout.</p>
<h3>Delivery time</h3>
<p>Delivery times depend on your location. Domestic orders usually arrive within 5–7 business days.</p>
<h3>Shipping address</h3>
<p>Please make sure your shipping address is correct before completing your purchase. We are not responsible for delays or problems caused by an incorrect address.</p>
<h3>Returns</h3>
<p>We do not offer returns on any products. Please make sure your order is correct before purchasing.</p>
<p>If you have any questions or need help, please contact us at {$email}.</p>
HTML],
        ];

        foreach ($pages as $slug => [$title, $html]) {
            $page = Page::firstOrNew(['slug' => $slug]);
            if (trim(strip_tags((string) $page->content)) !== '') {
                continue; // already written in the admin — keep it
            }
            $page->fill(['title' => $page->title ?: $title, 'content' => trim($html), 'is_active' => true])->save();
        }
    }
}
