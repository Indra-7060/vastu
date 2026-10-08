@php
  $pageTitle = ($supportTitle ?? 'Support') . ' - Vastutathastu';
  $supportPage = $supportPage ?? 'faqs';
  $assetBase = asset('frontend');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="{{ $pageTitle }}">
  <title>{{ $pageTitle }}</title>
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/mmenu.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/font-awesome.css') }}">
  @include('frontend.partials.site-config')

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=214">
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>
<body class="support-page" data-support-page="{{ $supportPage }}">
  <noscript>This page requires JavaScript.</noscript>

  <div class="support-page-frame">
    @include('frontend.partials.site-header')

    <div id="support-app" class="support-app" data-support-page="{{ $supportPage }}"></div>

    @include('frontend.partials.site-footer')
  </div>

  <script>
    window.PP_SUPPORT_ROUTES = {
      home: @json(route('home')),
      collection: @json(route('collection')),
      cart: @json(route('cart')),
      blog: @json(route('blog')),
      about: @json(route('about')),
      supportBase: @json(url('/support')),
      accountOverview: @json(url('/account/overview')),
      accountWishlist: @json(url('/account/wishlist')),
      login: @json(route('login')),
      assetBase: @json($assetBase),
      logo: @json(asset('vastu/images/logo.svg').'?v=2')
    };
  </script>

  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-4') }}?v=shared-header-1"></script>
  <script src="{{ asset('frontend/js/shell-menu-search.js') }}?v=2"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/support-pages.js') }}?v=shell-5"></script>
</body>
</html>
