{{-- Information page: content from Admin → Settings → Web Settings, otherwise a placeholder. --}}
@php $pageTitle = $title.' - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @unless($page)<meta name="robots" content="noindex">@endunless
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
        <h1 class="vt-h2 text-center">{{ $title }}</h1>
        @if($page)
          <div class="vt-rich-text vt-info-content">{!! $page->content !!}</div>
        @else
          @include('frontend.partials.vastu-empty-state', [
              'title' => 'No information available',
              'text' => 'Information about '.$title.' will be added here soon. Please check back later.',
              'icon' => 'info',
          ])
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
