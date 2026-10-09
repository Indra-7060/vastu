@php $pageTitle = ($post->title ?? 'Journal') . ' - Vastutathastu'; @endphp
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
<title>{{ $pageTitle ?? ($post->title ?? 'Journal') . ' - Vastutathastu' }}</title>

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=226">
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
    <section class="blog-single-area pt120 pb-0">
      <div class="gap-60">
        <div class="container-fluid">
          <div class="blog-header pb60">
            <div class="text-center">
              <h3 class="main-title text-uppercase">{{ $post->title }}</h3>
              <div class="post-date pb-0 d-flex pt25 justify-content-center align-items-center gap-3">
                <p class="mb-0 name">BY {{ strtoupper($post->author_name ?: 'ADMIN') }}, {{ strtoupper(optional($post->published_at)->format('F d Y') ?? '') }}</p>
                @if($post->newsType)
                <p class="comment mb-0"><span class="number">{{ strtoupper($post->newsType->title) }}</span></p>
                @endif
              </div>
            </div>
          </div>
          <div class="blog-single-post">
            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="img-fluid w-100">
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-8 mx-auto">
            <div class="blog-post-content pt60">
              <div class="content-box journal-post-body">
                {!! $post->content !!}
              </div>
              <div class="blog-nav d-flex justify-content-between flex-wrap gap-3 mt60">
                @if($prevPost)
                <a href="{{ route('blog.single', $prevPost->slug) }}" class="su-btn-4-black su-left-right"><span class="su-text">? Previous</span></a>
                @else
                <span></span>
                @endif
                @if($nextPost)
                <a href="{{ route('blog.single', $nextPost->slug) }}" class="su-btn-4-black su-left-right"><span class="su-text">Next ?</span></a>
                @endif
              </div>
              @if(($relatedPosts ?? collect())->isNotEmpty())
              <div class="related-article mt90">
                <h4 class="mb40">Related Articles</h4>
                <div class="row g-4">
                  @foreach($relatedPosts as $related)
                  <div class="col-md-6">
                      <div class="for-blog position-relative">
                      <div class="thumb overflow-hidden mb20">
                        <a href="{{ route('blog.single', $related->slug) }}"><img src="{{ $related->image_url }}" alt="{{ $related->title }}" class="img-fluid w-100"></a>
                      </div>
                      <h5 class="title"><a href="{{ route('blog.single', $related->slug) }}">{{ $related->title }}</a></h5>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
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
  <!-- JS here -->
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

