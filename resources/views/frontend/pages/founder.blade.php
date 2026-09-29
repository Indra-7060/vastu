{{-- About the founder. Extra biography can be added in Admin → Page Settings → About the Founder. --}}
@php $pageTitle = 'Makrannd Sardeshmukh — Founder - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Makrannd Sardeshmukh, Founder & Director of Vastutathastu — specialist in Vedic Vastushastra, astrology, Building Biology, Geopathology and Energy Architecture.">
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

    <main class="vt-home">
      <section class="vt-split vt-founder" aria-labelledby="vt-founder-title">
        <div class="vt-split__media vt-split__media--contain">
          <img src="{{ asset('vastu/images/founder-makrannd-sardeshmukh.jpg') }}" alt="Makrannd Sardeshmukh, Founder of Vastutathastu">
        </div>
        <div class="vt-split__copy">
          <p class="vt-kicker">Founder &amp; Director</p>
          <h1 class="vt-h3" id="vt-founder-title">Makrannd Sardeshmukh</h1>
          <p class="vt-body">Founder &amp; Director of Vastutathastu. Specialist in Vedic Vastushastra and astrology, Building Biology, Geopathology, and Energy Architecture.</p>
          <p class="vt-body">For more than 22 years he has guided homes, workplaces and families with authentic Vedic wisdom and thoughtfully chosen sacred products.</p>
          <div class="vt-split__links"><a class="vt-link" href="{{ route('info', 'book-a-consultation') }}">Book a consultation</a></div>
        </div>
      </section>

      @if($page)
        <section class="vt-content-page vt-content-page--tight">
          <div class="vt-content-page__inner vt-rich-text">
            {!! $page->content !!}
          </div>
        </section>
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
  <script src="{{ asset('frontend/js/script.js?v=vastu-2') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-5"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
</body>

</html>
