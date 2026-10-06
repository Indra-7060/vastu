{{-- Official-style brand icons in their own colours (Instagram, YouTube, Facebook).
     Usage: @include('frontend.partials.brand-icon', ['name' => 'instagram', 'size' => 28]) --}}
@php
  $size = $size ?? 28;
  $uid = 'vtb'.substr(md5(uniqid('', true)), 0, 6);
@endphp
@switch($name)
  @case('instagram')
    <svg class="vt-bicon vt-bicon--instagram" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <defs>
        <radialGradient id="{{ $uid }}a" cx="0.28" cy="1.08" r="1.25">
          <stop offset="0" stop-color="#ffdd55"/><stop offset=".12" stop-color="#ffdd55"/>
          <stop offset=".5" stop-color="#ff543e"/><stop offset="1" stop-color="#c837ab"/>
        </radialGradient>
        <radialGradient id="{{ $uid }}b" cx="-0.12" cy="0.07" r="0.6">
          <stop offset="0" stop-color="#3771c8"/><stop offset=".13" stop-color="#3771c8"/>
          <stop offset="1" stop-color="#6600ff" stop-opacity="0"/>
        </radialGradient>
      </defs>
      <rect width="24" height="24" rx="6" fill="url(#{{ $uid }}a)"/>
      <rect width="24" height="24" rx="6" fill="url(#{{ $uid }}b)"/>
      <path fill="#fff" fill-rule="evenodd" d="M8.4 4.6h7.2a3.8 3.8 0 0 1 3.8 3.8v7.2a3.8 3.8 0 0 1-3.8 3.8H8.4a3.8 3.8 0 0 1-3.8-3.8V8.4a3.8 3.8 0 0 1 3.8-3.8Zm0 1.6a2.2 2.2 0 0 0-2.2 2.2v7.2a2.2 2.2 0 0 0 2.2 2.2h7.2a2.2 2.2 0 0 0 2.2-2.2V8.4a2.2 2.2 0 0 0-2.2-2.2H8.4Zm3.6 2.25a3.55 3.55 0 1 1 0 7.1 3.55 3.55 0 0 1 0-7.1Zm0 1.6a1.95 1.95 0 1 0 0 3.9 1.95 1.95 0 0 0 0-3.9Zm4.05-2.55a.95.95 0 1 1 0 1.9.95.95 0 0 1 0-1.9Z"/>
    </svg>
    @break
  @case('youtube')
    <svg class="vt-bicon vt-bicon--youtube" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <rect width="24" height="24" rx="6" fill="#ff0000"/>
      <path fill="#fff" d="M9.5 7.6v8.8l7.4-4.4Z"/>
    </svg>
    @break
  @case('facebook')
    <svg class="vt-bicon vt-bicon--facebook" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <rect width="24" height="24" rx="6" fill="#1877f2"/>
      <path fill="#fff" d="M13.1 24v-8.5h2.85l.43-3.3H13.1v-2.1c0-.96.27-1.61 1.64-1.61h1.75V5.53c-.3-.04-1.34-.13-2.55-.13-2.52 0-4.24 1.54-4.24 4.36v2.44H6.85v3.3H9.7V24Z"/>
    </svg>
    @break
@endswitch
