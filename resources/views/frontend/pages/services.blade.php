{{-- Services: the Services menu groups with each service as a card (services and prices: Admin → Products). --}}
@php
  $pageTitle = 'Services - Vastutathastu';
  $intros = [
      'astrology' => 'Muhurt, matchmaking and kundali guidance — in person or online.',
      'numerology' => 'Numerology guidance for you, your business, your vehicle and your brand.',
      'vastu-consultation' => 'Vastu guidance for homes, offices and commercial spaces.',
  ];
@endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Vastu, astrology and numerology consultations with Makrannd Sardeshmukh — muhurt, matchmaking, kundali, numerology guidance and Vastushastra consultancy.">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=212">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body class="vt-page vt-page--services">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-services">
      <section class="vt-contact__hero vt-services__hero">
        <nav class="vt-contact__crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">›</span><span>Services</span></nav>
        <h1 class="vt-contact__title">Services</h1>
        <p class="vt-contact__intro">Personal Vastu, astrology and numerology consultations with Makrannd Sardeshmukh.</p>
        @if($groups->count() > 1)
          <nav class="vt-gallery__tabs vt-services__jump" aria-label="Service groups">
            @foreach($groups as $group)
              <a class="vt-gallery__tab" href="#{{ $group['anchor'] }}">{{ $group['title'] }}</a>
            @endforeach
          </nav>
        @endif
      </section>

      @forelse($groups as $group)
        <section class="vt-services__group" id="{{ $group['anchor'] }}" aria-labelledby="{{ $group['anchor'] }}-title">
          <header class="vt-services__head">
            <h2 class="vt-services__title" id="{{ $group['anchor'] }}-title">{{ $group['title'] }}</h2>
            @if(!empty($intros[$group['anchor']]))<p class="vt-services__intro">{{ $intros[$group['anchor']] }}</p>@endif
          </header>
          <ul class="vt-services__grid">
            @foreach($group['cards'] as $service)
              @php $price = (float) $service->selling_price > 0 ? (float) $service->selling_price : (float) $service->mrp; @endphp
              <li>
                <a class="vt-service" href="{{ route('shop.single', $service->slug) }}">
                  <span class="vt-service__img"><img src="{{ $service->tile_image_url }}" alt="{{ $service->title }}" loading="lazy" width="400" height="400"></span>
                  <span class="vt-service__body">
                    <span class="vt-service__name">{{ $service->title }}</span>
                    @if($service->short_description)<span class="vt-service__text">{{ \Illuminate\Support\Str::limit(strip_tags($service->short_description), 110) }}</span>@endif
                    <span class="vt-service__foot">
                      <span class="vt-service__price">{{ $price > 0 ? '₹ '.number_format($price) : 'Price on request' }}</span>
                      <span class="vt-service__cta">View &amp; book <span aria-hidden="true">→</span></span>
                    </span>
                  </span>
                </a>
              </li>
            @endforeach
          </ul>
        </section>
      @empty
        <div class="vt-content-page__inner">
          @include('frontend.partials.vastu-empty-state', ['title' => 'Services coming soon', 'text' => 'Our consultation services will be listed here soon. Please call us to book in the meantime.', 'icon' => 'info'])
        </div>
      @endforelse
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
  @include('frontend.partials.cart-script')
</body>

</html>
