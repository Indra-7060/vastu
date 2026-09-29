{{-- Friendly empty state for pages whose products/content have not been added yet.
     Optional: $title, $text, $kicker, $icon ('bag' | 'info'). --}}
<div class="vt-empty-state" role="status">
  @if(($icon ?? 'bag') === 'info')
    <svg class="vt-empty-state__icon" width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
      <circle cx="28" cy="28" r="27" stroke="currentColor" stroke-opacity=".35"/>
      <circle cx="28" cy="19.5" r="1.6" fill="currentColor"/>
      <path d="M28 25v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
  @else
    <svg class="vt-empty-state__icon" width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
      <circle cx="28" cy="28" r="27" stroke="currentColor" stroke-opacity=".35"/>
      <path d="M18 23h20l-2 16H20l-2-16Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
      <path d="M23 23v-2a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    </svg>
  @endif
  @if(!empty($kicker))
    <p class="vt-kicker">{{ $kicker }}</p>
  @endif
  <h2 class="vt-empty-state__title">{{ $title ?? 'No products found' }}</h2>
  <p class="vt-empty-state__text">{{ $text ?? 'New products will appear here as soon as they are added. Please check back soon.' }}</p>
  <div class="vt-empty-state__actions">
    <a class="vt-btn vt-btn--solid" href="{{ route('home') }}">Back to home</a>
    <a class="vt-btn vt-btn--outline" href="{{ route('shop') }}">Explore the shop</a>
  </div>
</div>
