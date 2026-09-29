{{-- Crisp storefront line icons (inline SVG, colour follows the text via currentColor).
     Usage: @include('frontend.partials.vt-icon', ['name' => 'search', 'size' => 20]) --}}
@php
  $size = $size ?? 20;
  $stroke = $stroke ?? 1.5;
  $paths = [
      'search' => '<circle cx="10.5" cy="10.5" r="6.75"/><path d="m15.5 15.5 5.25 5.25"/>',
      'bag' => '<path d="M4.75 8.25h14.5l-1.1 12.5H5.85L4.75 8.25Z"/><path d="M8.75 8.25V6.5a3.25 3.25 0 0 1 6.5 0v1.75"/>',
      'user' => '<circle cx="12" cy="7.75" r="4"/><path d="M4.5 20.75c.6-3.9 3.6-6.25 7.5-6.25s6.9 2.35 7.5 6.25"/>',
      'heart' => '<path d="M12 20.25s-7.75-4.6-7.75-10.1A4.4 4.4 0 0 1 8.6 5.75c1.45 0 2.65.7 3.4 1.9.75-1.2 1.95-1.9 3.4-1.9a4.4 4.4 0 0 1 4.35 4.4c0 5.5-7.75 10.1-7.75 10.1Z"/>',
      'pin' => '<path d="M19 10.25c0 5.5-7 11-7 11s-7-5.5-7-11a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10.25" r="2.5"/>',
      'chevron-right' => '<path d="m9.5 5.5 6.5 6.5-6.5 6.5"/>',
      'chevron-left' => '<path d="m14.5 5.5-6.5 6.5 6.5 6.5"/>',
  ];
@endphp
<svg class="vt-icon vt-icon--{{ $name }}" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? '' !!}</svg>
