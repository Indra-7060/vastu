@php $pageTitle = 'Sign Up - Vastutathastu'; @endphp
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
<title>{{ $pageTitle ?? 'Sign Up - Vastutathastu' }}</title>

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=198">
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
  <!-- main-area -->
  <main class="body_content_wrapper position-relative">

    <section class="registration-section pt120 pb80">
      <div class="container">
        <div class="row pb15">
          <div class="col-lg-6 mx-auto">
            <div class="section-title text-center">
              <h2 class="title wow fadeInUp" data-wow-delay=".2s">Create account</h2>
              <p class="sub-title wow fadeInUp" data-wow-delay=".4s">Enter your information below to proceed. If you already have an account, please log in instead.</p>
            </div>
          </div>
        </div>
        <div class="blog-single-area">
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <div class="blog-post-content wow fadeInUp" data-wow-delay=".6s">
                <div class="content-box">
                  <div class="review-box">
                    <form id="customer-register-form" method="POST" action="{{ route('signup.submit') }}" novalidate>
                      @csrf
                      <div class="auth-form-alert alert alert-danger d-none mb-3" role="alert" hidden></div>
                      @if($errors->any())
                        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                      @endif
                      <div class="row g-4">
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="text" name="first_name" id="register-first-name" class="form-control shadow-none" placeholder="First Name *" value="{{ old('first_name') }}" required autocomplete="given-name">
                            <label for="register-first-name">First Name *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="text" name="last_name" id="register-last-name" class="form-control shadow-none" placeholder="Last Name *" value="{{ old('last_name') }}" autocomplete="family-name">
                            <label for="register-last-name">Last Name *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="email" name="email" id="register-email" class="form-control shadow-none" placeholder="Email *" value="{{ old('email') }}" required autocomplete="email">
                            <label for="register-email">Email *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="password" name="password" id="register-password" class="form-control shadow-none" placeholder="Password *" required minlength="6" autocomplete="new-password">
                            <label for="register-password">Password *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-floating">
                            <input type="password" name="password_confirmation" id="register-password-confirmation" class="form-control shadow-none" placeholder="Confirm Password *" required minlength="6" autocomplete="new-password">
                            <label for="register-password-confirmation">Confirm Password *</label>
                            <div class="field-error text-danger mt-1"></div>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <button type="submit" class="su-btn-4 su-btn-16-black w-100 su-left-right">
                            <span class="mr10 su-text d-inline-block">CREATE ACCOUNT</span>
                            <span class="su-arrow-angle">
                              <svg class="su-arrow-svg-top-right" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10.00 10.00">
                                <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
                                <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
                              </svg>
                            </span>
                          </button>
                          <p class="text-center mt20 mb-0">Already have an account? <a href="{{ route('login') }}">LOGIN</a></p>
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
<script src="{{ asset('frontend/js/script.js?v=vastu-3') }}"></script>
<script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
<script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
<script src="{{ asset('frontend/js/customer-auth.js') }}?v=pw-loader-1"></script>
</body>
</html>

