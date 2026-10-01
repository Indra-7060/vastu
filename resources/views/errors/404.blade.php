<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <meta name="description" content="Page not found - Vastutathastu">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=117">
  <title>404 - Page Not Found | Vastutathastu</title>
  <link rel="icon" type="image/png" href="{{ asset('vastu/images/favicon.png') }}">
</head>

<body class="vt-page">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-empty" aria-labelledby="vt-empty-title">
      <p class="vt-empty__code">404</p>
      <h1 class="vt-h2" id="vt-empty-title">Page Not Found</h1>
      <p class="vt-body">This page isn’t available yet. New products and content will appear here as soon as they are added.</p>
      <div class="vt-empty__actions">
        <a class="vt-btn vt-btn--solid" href="{{ route('home') }}">Back to home</a>
        <a class="vt-btn vt-btn--outline" href="{{ route('shop') }}">Shop all</a>
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
  <script src="{{ asset('frontend/js/script.js?v=vastu-3') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
</body>

</html>
