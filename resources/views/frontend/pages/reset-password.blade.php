@php $pageTitle = 'Reset Password - Vastutathastu'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $pageTitle }}</title>
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  @include('frontend.partials.site-config')

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=209">
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>
<body class="pp-auth-page">
  <div class="wrapper ovh">
    <div id="page">
      <div class="text-center py-4">
        <a href="{{ route('home') }}"><img src="{{ asset('vastu/images/logo.svg') }}?v=2" alt="Vastutathastu" style="max-height:56px;"></a>
      </div>
      <main class="body_content_wrapper">
        <section class="registration-section pt40 pb80">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-lg-5 col-md-7">
                <div class="section-title text-center mb-4">
                  <h2 class="title">Create new password</h2>
                  <p class="sub-title mb-0">Choose a new password for your account, then sign in.</p>
                </div>
                <div class="pp-auth-card">
                  <form id="customer-reset-form" method="POST" action="{{ route('customer.password.update') }}" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="auth-form-alert alert d-none mb-3" role="alert" hidden></div>
                    @if($errors->any())
                      <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                    @endif
                    <div class="form-floating mb-3">
                      <input type="email" name="email" id="reset-email" class="form-control shadow-none" placeholder="Email address" value="{{ old('email', $email ?? '') }}" autocomplete="email" maxlength="255">
                      <label for="reset-email">Email address</label>
                      <div class="field-error text-danger mt-1"></div>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="password" name="password" id="reset-password" class="form-control shadow-none" placeholder="New password" autocomplete="new-password" minlength="6">
                      <label for="reset-password">New password</label>
                      <div class="field-error text-danger mt-1"></div>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="password" name="password_confirmation" id="reset-password-confirmation" class="form-control shadow-none" placeholder="Confirm password" autocomplete="new-password" minlength="6">
                      <label for="reset-password-confirmation">Confirm password</label>
                      <div class="field-error text-danger mt-1"></div>
                    </div>
                    <button type="submit" class="su-btn-4 su-btn-16-black w-100 su-left-right">
                      <span class="mr10 su-text d-inline-block">Update password</span>
                    </button>
                    <p class="text-center mt-4 mb-0">
                      <a href="{{ route('login') }}">Back to login</a>
                    </p>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
      @include('frontend.partials.site-footer')
    </div>
  </div>
  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/customer-auth.js') }}?v=pw-loader-1"></script>
  @include('frontend.partials.cart-script')
</body>
</html>
