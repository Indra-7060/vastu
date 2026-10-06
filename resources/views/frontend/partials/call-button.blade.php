{{-- Floating "Call us" button, bottom-left (mirrors the WhatsApp button on the right).
     Number: Admin → Settings → Site Details → contact phone. --}}
@php
  $callNumber = preg_replace('/[^\d+]/', '', (string) \App\Support\SiteSettings::get('contact_phone')) ?: '+919021900600';
  if (preg_match('/^\d{10}$/', $callNumber)) { $callNumber = '+91'.$callNumber; } // plain Indian number
@endphp
<a class="vt-call" href="tel:{{ $callNumber }}" aria-label="Call Vastutathastu">
  <span class="vt-call__icon"><svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z"/></svg></span>
  <span class="vt-call__label">Call us</span>
</a>
