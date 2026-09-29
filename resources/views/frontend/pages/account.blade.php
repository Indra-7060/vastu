@php
  $pageTitle = ($accountTitle ?? 'Account') . ' - Vastutathastu';
  $accountPage = $accountPage ?? 'overview';
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
<body class="customer-account-page" data-account-page="{{ $accountPage }}">
  <noscript>This account area requires JavaScript.</noscript>

  <div class="account-page-frame">
    @include('frontend.partials.site-header')

    <div id="account-app" class="account-app" data-account-page="{{ $accountPage }}"></div>

    @include('frontend.partials.site-footer')
  </div>

  <script>
    window.PP_ACCOUNT_ROUTES = {
      home: @json(route('home')),
      collection: @json(route('collection')),
      cart: @json(route('cart')),
      blog: @json(route('blog')),
      about: @json(route('about')),
      support: @json(route('support')),
      accountBase: @json(url('/account')),
      login: @json(route('login')),
      signup: @json(route('signup')),
      logout: @json(route('logout')),
      checkEmail: @json(route('check.email')),
      profile: @json(route('account.api.profile')),
      addressesStore: @json(route('account.api.addresses.store')),
      addressesUpdate: @json(url('/account-api/addresses')),
      addressesDestroy: @json(url('/account-api/addresses')),
      addressesDefault: @json(url('/account-api/addresses')),
      wishlistStore: @json(route('account.api.wishlist.store')),
      wishlistDestroy: @json(url('/account-api/wishlist')),
      reviewsDestroy: @json(url('/account-api/reviews')),
      notificationsRead: @json(url('/account-api/notifications/read')),
      notificationsDestroy: @json(url('/account-api/notifications')),
      assetBase: @json($assetBase),
      logo: @json(asset('vastu/images/logo.svg').'?v=2'),
      csrf: @json(csrf_token())
    };
    window.PP_ACCOUNT_USER = @json($accountUser ?? null);
    window.PP_ACCOUNT_DATA = @json($accountBootstrap ?? null);
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
  <script src="{{ asset('frontend/js/account-data.js') }}"></script>
  <script src="{{ asset('frontend/js/account-dashboard.js') }}?v=notify-1"></script>
  <script src="{{ asset('frontend/js/account-pages-laravel.js') }}"></script>
</body>
</html>
