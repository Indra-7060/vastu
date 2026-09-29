{{-- Floating WhatsApp chat button (number and message: Admin → Settings → Site Details). --}}
@php
  $waNumber = preg_replace('/\D+/', '', \App\Support\SiteSettings::get('contact_whatsapp'));
  if (strlen($waNumber) === 10) { $waNumber = '91'.$waNumber; } // plain Indian mobile number
  $waText = \App\Support\SiteSettings::get('whatsapp_message');
@endphp
@if($waNumber)
<a class="vt-wa" href="https://wa.me/{{ $waNumber }}{{ $waText ? '?text='.rawurlencode($waText) : '' }}" target="_blank" rel="noopener" aria-label="Chat with Vastutathastu on WhatsApp">
  <svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16.003 3C8.832 3 3 8.832 3 16.003c0 2.293.6 4.533 1.74 6.51L3 29l6.66-1.707A12.95 12.95 0 0 0 16.003 29C23.172 29 29 23.168 29 16.003 29 8.832 23.172 3 16.003 3zm0 23.8a10.78 10.78 0 0 1-5.5-1.51l-.394-.234-3.953 1.013 1.054-3.853-.257-.395A10.8 10.8 0 1 1 16.003 26.8zm5.93-8.087c-.325-.163-1.922-.948-2.22-1.056-.297-.108-.514-.163-.73.163-.217.325-.838 1.056-1.027 1.273-.19.217-.379.244-.704.081-.325-.163-1.372-.506-2.614-1.613-.966-.862-1.618-1.926-1.808-2.251-.19-.325-.02-.5.143-.662.146-.146.325-.379.487-.568.163-.19.217-.325.325-.542.108-.217.054-.406-.027-.568-.081-.163-.73-1.76-1-2.41-.264-.633-.532-.547-.73-.557l-.622-.011a1.194 1.194 0 0 0-.866.406c-.298.325-1.137 1.11-1.137 2.708 0 1.598 1.164 3.142 1.326 3.359.163.217 2.29 3.497 5.548 4.904.775.335 1.38.535 1.852.685.778.247 1.486.212 2.046.129.624-.093 1.922-.786 2.193-1.545.27-.758.27-1.408.19-1.544-.082-.136-.298-.217-.623-.38z"/></svg>
</a>
@endif

{{-- Floating call button (bottom-left). Number: Admin → Settings → Site Details → Phone. --}}
@php $callNumber = trim(\App\Support\SiteSettings::get('contact_phone')); @endphp
@if($callNumber)
<a class="vt-call" href="tel:{{ preg_replace('/[^\d+]/', '', $callNumber) }}" aria-label="Call Vastutathastu on {{ $callNumber }}" title="Call {{ $callNumber }}">
  <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z"/></svg>
</a>
@endif
