{{-- Store locator (Admin → Stores). --}}
@php $pageTitle = 'Stores - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Visit a Vastutathastu store or consultation centre near you for Vastu guidance and authentic sacred products.">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=225">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body class="vt-page">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-stores">
      <section class="vt-stores__head">
        <p class="vt-kicker">Visit us</p>
        <h1 class="vt-h2">Find a Vastutathastu store</h1>
        <p class="vt-body">Explore energised products in person and meet our consultants for Vastu, astrology and numerology guidance.</p>
      </section>

      @if($stores->isEmpty())
        <div class="vt-content-page__inner">
          @include('frontend.partials.vastu-empty-state', [
              'title' => 'No stores listed yet',
              'text' => 'Our store locations will be listed here soon. Please check back later.',
              'icon' => 'info',
          ])
        </div>
      @else
        <div class="vt-stores__bar" role="group" aria-label="Filter stores by city">
          <div class="vt-stores__chips">
            <button type="button" class="vt-chip is-active" data-vt-city="" aria-pressed="true">All cities</button>
            @foreach($cities as $city)
              <button type="button" class="vt-chip" data-vt-city="{{ $city }}" aria-pressed="false">{{ $city }}</button>
            @endforeach
          </div>
          <p class="vt-stores__count" aria-live="polite"><span data-vt-store-count>{{ $stores->count() }}</span> {{ \Illuminate\Support\Str::plural('store', $stores->count()) }}</p>
        </div>

        <div class="vt-stores__grid">
          @foreach($stores as $store)
            <article class="vt-store" data-vt-store data-city="{{ $store->city }}">
              @if($store->image_url)
                <div class="vt-store__media"><img src="{{ $store->image_url }}" alt="{{ $store->name }}" loading="lazy"></div>
              @endif
              <div class="vt-store__body">
                <p class="vt-store__type">{{ $store->type }}</p>
                <h2 class="vt-store__name">{{ $store->name }}</h2>
                <ul class="vt-store__facts">
                  <li>
                    @include('frontend.partials.vt-icon', ['name' => 'pin', 'size' => 18])
                    <span>{{ $store->full_address }}</span>
                  </li>
                  @if($store->opening_hours)
                  <li>
                    <svg class="vt-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8.75"/><path d="M12 7.5V12l3 2"/></svg>
                    <span>{{ $store->opening_hours }}</span>
                  </li>
                  @endif
                  @if($store->phone)
                  <li>
                    <svg class="vt-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5.5 3.75h3l1.5 4-2 1.25a11 11 0 0 0 5 5L14.25 12l4 1.5v3a2 2 0 0 1-2 2A13.5 13.5 0 0 1 3.5 5.75a2 2 0 0 1 2-2Z"/></svg>
                    <a href="tel:{{ $store->tel }}">{{ $store->phone }}</a>
                  </li>
                  @endif
                  @if($store->email)
                  <li>
                    <svg class="vt-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="1.5"/><path d="m4 7 8 6 8-6"/></svg>
                    <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                  </li>
                  @endif
                </ul>
                @if($store->service_list)
                  <ul class="vt-store__services" aria-label="Services">
                    @foreach($store->service_list as $service)
                      <li>{{ $service }}</li>
                    @endforeach
                  </ul>
                @endif
                <div class="vt-store__actions">
                  <a class="vt-btn vt-btn--solid" href="{{ $store->directions_url }}" target="_blank" rel="noopener">Get directions</a>
                  @if($store->tel)
                    <a class="vt-btn vt-btn--ghost" href="tel:{{ $store->tel }}">Call store</a>
                  @endif
                </div>
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </main>

    @include('frontend.partials.site-footer')
  </div>

  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-4') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  <script>
    // City filter chips
    (function () {
      var chips = document.querySelectorAll('[data-vt-city]');
      var stores = document.querySelectorAll('[data-vt-store]');
      var count = document.querySelector('[data-vt-store-count]');
      chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
          var city = chip.getAttribute('data-vt-city');
          var shown = 0;
          chips.forEach(function (c) {
            var on = c === chip;
            c.classList.toggle('is-active', on);
            c.setAttribute('aria-pressed', on ? 'true' : 'false');
          });
          stores.forEach(function (store) {
            var match = !city || store.getAttribute('data-city') === city;
            store.hidden = !match;
            if (match) shown++;
          });
          if (count) count.textContent = shown;
        });
      });
    })();
  </script>
  @include('frontend.partials.cart-script')
</body>

</html>
