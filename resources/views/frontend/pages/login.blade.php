@php $pageTitle = 'Login - Vastutathastu'; @endphp
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
<link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-2">
<link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-2">
<link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">

<!-- Title -->
<title>{{ $pageTitle ?? 'Vastutathastu' }}</title>

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=72">
  <link rel="icon" type="image/png" href="{{ asset('vastu/images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('vastu/images/favicon.png') }}">
</head>

<body>

  <!-- header-area -->
  
  <!-- header-area-end -->

  
<div class="wrapper ovh">
  <div class="preloader"></div>
  
  <!-- header-area -->
  @include('frontend.partials.site-header')
  <!-- header-area-end -->
  <!-- main-area -->
  <main class="body_content_wrapper position-relative">

    <section class="registration-section pt120 pb80">
      <div class="container">
        <div class="row pb15">
          <div class="col-lg-6 mx-auto">
            <div class="section-title text-center">
              <h2 class="title wow fadeInUp" data-wow-delay=".2s">Log in</h2>
              <p class="sub-title wow fadeInUp" data-wow-delay=".4s">Log in and enjoy a personalised experience. If you don’t have an account, please create one instead.</p>
            </div>
          </div>
        </div>
        <div class="blog-single-area">
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <div class="blog-post-content wow fadeInUp" data-wow-delay=".6s">
                <div class="content-box">
                  <div class="review-box">
                    <form id="customer-login-form" method="POST" action="{{ route('login.submit') }}" novalidate>
                      @csrf
                      <div class="auth-form-alert alert alert-danger d-none mb-3" role="alert" hidden></div>
                      @if(session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                      @endif
                      @if($errors->any())
                        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                      @endif
                      <div class="row g-4">
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="email" name="email" id="login-email" class="form-control shadow-none" placeholder="Email *" value="{{ old('email') }}" required autocomplete="email">
                            <label for="login-email">Email *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="password" name="password" id="login-password" class="form-control shadow-none" placeholder="Password *" required minlength="6" autocomplete="current-password">
                            <label for="login-password">Password *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-check mb-2">
                            <input class="form-check-input shadow-none" type="checkbox" name="remember" value="1" id="login-remember">
                            <label class="form-check-label" for="login-remember">Remember me</label>
                          </div>
                          <p class="mb-3"><a href="{{ route('customer.password.request') }}">Forgot password?</a></p>
                          <button type="submit" class="su-btn-4 su-btn-16-black w-100 su-left-right">
                            <span class="mr10 su-text d-inline-block">LOGIN</span>
                            <span class="su-arrow-angle">
                              <svg class="su-arrow-svg-top-right" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10.00 10.00">
                                <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
                                <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
                              </svg>
                            </span>
                          </button>
                          <p class="text-center mt20 mb-0">Don't have an account? <a href="{{ route('signup') }}">CREATE ACCOUNT</a></p>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  @include('frontend.partials.site-footer')

  </main>
  <!-- main-area-end -->
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
<script src="{{ asset('frontend/js/jquery.countdown.js') }}"></script>
<script src="{{ asset('frontend/js/jarallax.js') }}"></script>
<script src="{{ asset('frontend/js/wow.min.js') }}"></script>
<script src="{{ asset('frontend/js/script.js?v=vastu-2') }}"></script>
<script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-5"></script>
<script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
<script src="{{ asset('frontend/js/customer-auth.js') }}?v=csrf-refresh-1"></script>
</body>
</html>
