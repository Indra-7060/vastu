{{-- World of Vastutathastu: content comes from Admin → Page Settings → About Us (see FrontendController@about). --}}
@php $pageTitle = ($page->title ?? 'World of Vastutathastu').' - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="keywords" content="vastu, vastushastra, rudraksha, yantra, mala, crystal tree, astrology, numerology, puja essentials">
  <meta name="description" content="Vastutathastu unites Vedic Vastushastra, astrology and numerology with authentic sacred products for harmonious homes, workplaces and lives.">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=72">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/png" href="{{ asset('vastu/images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('vastu/images/favicon.png') }}">
</head>

<body class="vt-page">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-content-page">
      <div class="vt-content-page__inner">
        @if($page)
          <p class="vt-kicker">World of Vastutathastu</p>
          <h1 class="vt-h2">{{ $page->title }}</h1>
          <div class="vt-rich-text">
            {!! $page->content !!}
          </div>
        @else
          <h1 class="vt-h2">World of Vastutathastu</h1>
          @include('frontend.partials.vastu-empty-state', ['title' => 'No content found', 'text' => 'Stories, guidance and updates from Vastutathastu will appear here soon. Please check back later.'])
        @endif
      </div>
    </main>

    @include('frontend.partials.site-footer')
  </div>

  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-2') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-5"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
</body>

</html>
