<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Contact Us page: address, phone, email, map and an enquiry form (saved to Admin → Contact List). */
class ContactPageController extends Controller
{
    public const SUBJECTS = [
        'Vastu Consultation',
        'Astrology',
        'Numerology',
        'Product enquiry',
        'Order support',
        'Other',
    ];

    public function show()
    {
        return view('frontend.pages.contact', ['subjects' => self::SUBJECTS]);
    }

    public function submit(Request $request)
    {
        // Spam trap: a hidden field real visitors never fill.
        if (filled($request->input('website'))) {
            return $this->done($request);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'city' => ['nullable', 'string', 'max:80'],
            'subject' => ['nullable', 'string', 'in:'.implode(',', self::SUBJECTS)],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
            'message.min' => 'Please tell us a little more about your enquiry.',
        ]);

        $contact = ContactMessage::create([
            'name' => trim($data['name']),
            'phone' => trim($data['phone']),
            'email' => trim((string) ($data['email'] ?? '')),
            'city' => trim((string) ($data['city'] ?? '')) ?: null,
            'subject' => $data['subject'] ?? null,
            'message' => trim($data['message']),
        ]);

        $admin = config('mail.admin_address') ?: env('MAIL_ADMIN_ADDRESS');
        if ($admin) {
            try {
                Mail::to($admin)->send(new ContactMessageReceived($contact));
            } catch (\Throwable $e) {
                Log::warning('Contact form email failed: '.$e->getMessage(), ['contact' => $contact->id]);
            }
        }

        return $this->done($request);
    }

    private function done(Request $request)
    {
        $message = 'Thank you! We have received your message and will get back to you shortly.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'message' => $message])
            : redirect()->route('contact')->with('contact_sent', $message);
    }
}
