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
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/font-awesome.css') }}">
  @include('frontend.partials.site-config')

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=72">
  <link rel="icon" type="image/png" href="{{ asset('vastu/images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('vastu/images/favicon.png') }}">
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
  <script src="{{ asset('frontend/js/script.js?v=vastu-2') }}?v=shared-header-1"></script>
  <script src="{{ asset('frontend/js/shell-menu-search.js') }}?v=2"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-5"></script>
  <script src="{{ asset('frontend/js/support-pages.js') }}?v=shell-5"></script>
</body>
</html>
