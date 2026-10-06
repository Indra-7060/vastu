{{-- Shared storefront header (Vastutathastu). Pass $vtHeaderOverlay = true to lay it over a full-bleed hero. --}}
@php
  $vtLoggedIn = auth()->check() && optional(auth()->user())->isCustomer();
  $vtUnread = $vtLoggedIn ? auth()->user()->unreadNotifications()->count() : 0;
  $vtOverlay = !empty($vtHeaderOverlay);
  $megaMenu = $megaMenu ?? \App\Support\MegaMenu::data();
  // Main menu (PRIME-style single bar, left of the logo). Products and Services open full-width
  // dropdowns on hover. Blogs is in the footer.
  $vtNav = [
      ['label' => 'Products', 'url' => route('shop'), 'mega' => 'new'],
      ['label' => 'Services', 'url' => route('services'), 'mega' => 'services'],
      ['label' => 'Gallery', 'url' => route('gallery'), 'mega' => null],
      ['label' => 'Contact us', 'url' => route('contact'), 'mega' => null],
  ];
@endphp
<div id="page">
  <script>
    // Scroll-reveal motion is enabled before content paints (vastu.js animates it in).
    // If vastu.js never runs, the fallback timer shows everything again.
    // Homepage refresh opens on "Shop by Category" (vastu.js); stop the browser restoring the old position first.
    (function () {
      var nav = window.performance && performance.getEntriesByType ? performance.getEntriesByType('navigation')[0] : null;
      var isHome = document.body.classList.contains('vt-page--home');
      // Refreshing a browsing page (category, shop, product, blog…) starts again from the homepage's
      // "Shop by Category" animation. Pages where the visitor is mid-task keep a normal refresh.
      var keep = /\/(cart|checkout|account|account-api|order|orders|login|signup|register|forgot-password|reset-password|password|dev)(\/|$)/i;
      if (nav && nav.type === 'reload' && !isHome && !keep.test(location.pathname)) {
        try { sessionStorage.setItem('vtReloadHome', '1'); } catch (e) {}
        location.replace(@json(route('home')));
        return;
      }
      var cameFromRefresh = false;
      try { cameFromRefresh = sessionStorage.getItem('vtReloadHome') === '1'; sessionStorage.removeItem('vtReloadHome'); } catch (e) {}
      if (nav && (nav.type === 'reload' || cameFromRefresh) && isHome && !location.hash && 'scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
        window.vtHomeReload = true;
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('vt-fab-hold');
        window.addEventListener('pagehide', function () { history.scrollRestoration = 'auto'; });
      }
    })();
    (function (d) {
      if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      d.classList.add('vt-motion');
      setTimeout(function () { if (!window.vtMotionReady) d.classList.remove('vt-motion'); }, 3000);
    })(document.documentElement);
  </script>
  <header class="vt-header vt-hdr{{ $vtOverlay ? ' vt-header--overlay vt-hdr--overlay' : ' vt-hdr--solid' }}" data-vt-header>
    <div class="vt-hdr__bar">
      <a class="vt-hdr__toggle vt-menu-toggle menubar" href="#menu" aria-label="Open menu">
        <span class="vt-burger" aria-hidden="true"><i></i><i></i><i></i></span>
      </a>

      <nav class="vt-hdr__nav" aria-label="Primary">
        <ul class="vt-hdr__links">
          @foreach($vtNav as $item)
            <li class="vt-nav__item"@if($item['mega']) data-vt-mega @endif>
              <a class="vt-hdr__link{{ url()->current() === $item['url'] ? ' is-active' : '' }}" href="{{ $item['url'] }}"@if(url()->current() === $item['url']) aria-current="page"@endif @if($item['mega']) aria-haspopup="true" aria-expanded="false"@endif>{{ $item['label'] }}@if($item['mega'])<svg class="vt-hdr__caret" width="10" height="10" viewBox="0 0 10 10" aria-hidden="true"><path d="M2 3.5 5 6.5 8 3.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>@endif</a>
              @if($item['mega'])
                @include('frontend.partials.vastu-mega-panel', ['item' => $item, 'megaMenu' => $megaMenu])
              @endif
            </li>
          @endforeach
        </ul>
      </nav>

      <a class="vt-hdr__logo notranslate" translate="no" href="{{ route('home') }}" aria-label="Vastutathastu home">
        <img src="{{ asset('vastu/images/logo.svg') }}?v=2" width="341" height="95" alt="Vastutathastu — The Trusted Brand">
      </a>

      <div class="vt-hdr__actions">
        <a class="vt-hdr__icon signin-cart-btn{{ $vtLoggedIn ? ' is-logged-in' : '' }}" href="{{ $vtLoggedIn ? url($vtUnread ? '/account/notifications' : '/account/overview') : route('login') }}" aria-label="{{ $vtLoggedIn ? 'My account'.($vtUnread ? ' ('.$vtUnread.' new notifications)' : '') : 'Login' }}">
          @include('frontend.partials.vt-icon', ['name' => 'user', 'size' => 22])
          @if($vtUnread)<span class="vt-notify-dot" aria-hidden="true">{{ $vtUnread > 9 ? '9+' : $vtUnread }}</span>@endif
        </a>
        <span class="site-wishlist-entry">
          <a class="vt-hdr__icon wishlist-panel-btn" href="{{ url('/account/wishlist') }}" aria-label="Open wishlist">@include('frontend.partials.vt-icon', ['name' => 'heart', 'size' => 22])</a>
        </span>
        <button type="button" class="vt-hdr__icon cart-search-btn" aria-label="Search">@include('frontend.partials.vt-icon', ['name' => 'search', 'size' => 22])</button>
        <a href="{{ route('cart') }}" class="vt-hdr__icon cart-filter-btn site-cart-link" aria-label="Shopping cart">
          @include('frontend.partials.vt-icon', ['name' => 'bag', 'size' => 22])
          <span class="vt-count" data-cart-count hidden>0</span>
        </a>
        {{-- Language: English (default) / हिंदी / मराठी — see vastu/js/lang.js --}}
        <div class="vt-lang notranslate" translate="no" data-vt-lang>
          <button type="button" class="vt-lang__btn" aria-haspopup="true" aria-expanded="false" aria-controls="vt-lang-menu" data-vt-lang-btn>
            <span class="vt-lang__long" data-vt-lang-long>English</span>
            <span class="vt-lang__short" data-vt-lang-short>EN</span>
            <svg class="vt-lang__chev" viewBox="0 0 12 12" width="10" height="10" aria-hidden="true"><path d="M2 4.5l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.4"/></svg>
            <span class="vt-sr-only">— change language</span>
          </button>
          <ul class="vt-lang__menu" id="vt-lang-menu" role="menu" hidden>
            <li role="none"><button type="button" role="menuitemradio" aria-checked="true" data-lang="en" lang="en"><span>English</span></button></li>
            <li role="none"><button type="button" role="menuitemradio" aria-checked="false" data-lang="hi" lang="hi"><span>हिंदी</span><small>Hindi</small></button></li>
            <li role="none"><button type="button" role="menuitemradio" aria-checked="false" data-lang="mr" lang="mr"><span>मराठी</span><small>Marathi</small></button></li>
          </ul>
        </div>
      </div>
    </div>
  </header>
  @unless($vtOverlay)<div class="vt-hdr-spacer" aria-hidden="true"></div>@endunless
  <div class="vt-mega-backdrop" data-vt-mega-backdrop aria-hidden="true"></div>

  @include('frontend.partials.search-panel')
  @include('frontend.partials.mobile-menu')
  @include('frontend.partials.consultation-modal')
</div>
<script src="{{ asset('vastu/js/vastu.js') }}?v=39" defer></script>
  <div id="vt-gt" class="vt-gt" aria-hidden="true"></div>
  {{-- Not deferred: window.vtLang must exist before cart.js runs (the menu itself waits for the page). --}}
  <script src="{{ asset('vastu/js/lang.js') }}?v=16"></script>
