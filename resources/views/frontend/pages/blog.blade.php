@php $pageTitle = 'Journal - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="keywords" content="vastu, vastushastra, rudraksha, yantra, mala, crystal tree, astrology, numerology, puja essentials">
<meta name="description" content="Vastutathastu unites Vedic Vastushastra, astrology and numerology with authentic sacred products for harmonious homes, workplaces and lives.">
<!-- css file -->
<link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
<link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
<link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
<link rel="stylesheet" href="{{ asset('frontend/css/journal.css') }}?v=vastu-2">

<!-- Title -->
<title>{{ $pageTitle ?? 'Journal - Vastutathastu' }}</title>

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=220">
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body>

  <!-- header-area -->
  
  <!-- header-area-end -->

  
<div class="wrapper ovh">
  <div class="preloader"></div>
  
  <!-- header-area -->
  @include('frontend.partials.site-header')
  <!-- header-area-end -->

  <main class="body_content_wrapper position-relative">
    <!-- blog-list-area-start -->
    <section class="blog-list-area pt120 pb-0">
      <div class="container">
        @include('frontend.partials.blog-list-body')
      </div>
    </section>
    <!-- blog-list-area-end -->

    <!-- Instagram Feed -->

    <!-- feature-area-start -->
    <!-- feature-area-end -->
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </main>
  @include('frontend.partials.site-footer')
</div>
<!-- Wrapper End --> 
<script src="{{ asset('frontend/js/jquery.js') }}"></script> 
<script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script> 
<script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script> 
<script src="{{ asset('frontend/js/mmenu.js') }}"></script> 
<script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.odometer.min.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.appear.js') }}"></script>
<script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('frontend/js/jarallax.js') }}"></script>
<script src="{{ asset('frontend/js/wow.min.js') }}"></script>
<!-- Custom script for all pages --> 
<script src="{{ asset('frontend/js/script.js?v=vastu-4') }}"></script>
<script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
<script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
</body>

</html>

