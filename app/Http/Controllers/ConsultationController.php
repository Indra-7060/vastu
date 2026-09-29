<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Book a consultation" enquiries (pop-up form on every page).
 * Saved as consultations → Admin → Consultations.
 */
class ConsultationController extends Controller
{
    public const INTERESTS = ['Vastu consultation', 'Astrology', 'Numerology', 'Product guidance', 'Other'];

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9 ()-]{7,20}$/'],
            'email' => ['required', 'email', 'max:255'],
            'interest' => ['required', Rule::in(self::INTERESTS)],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Please enter a valid phone / WhatsApp number.',
        ]);

        Consultation::create([
            'user_id' => optional($request->user())->isCustomer() ? $request->user()->id : null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'interest' => $data['interest'],
            'message' => trim((string) ($data['message'] ?? '')) ?: null,
            'status' => 'new',
        ]);

        $message = 'Thank you! Our team will contact you shortly to schedule your consultation.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
