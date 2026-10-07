@php $pageTitle = 'Checkout - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="keywords" content="vastu, vastushastra, rudraksha, yantra, mala, crystal tree, astrology, numerology, puja essentials">
  <meta name="description" content="Vastutathastu unites Vedic Vastushastra, astrology and numerology with authentic sacred products for harmonious homes, workplaces and lives.">
  <!-- css file -->
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
<link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
<link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
<link rel="stylesheet" href="{{ asset('frontend/css/journal.css') }}?v=vastu-2">

  <!-- Title -->
  <title>{{ $pageTitle ?? 'Checkout - Vastutathastu' }}</title>
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=203">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="{{ asset('vastu/css/checkout.css') }}?v=3">
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body class="vt-co-body">

  <!-- header-area -->
  
  <!-- header-area-end -->

  
<div class="wrapper ovh">
  <div class="preloader"></div>
  
  <!-- header-area -->
  @include('frontend.partials.site-header')
  <!-- header-area-end -->

  <main class="body_content_wrapper position-relative vt-co-page">
    {{-- Steps: Shopping cart › Checkout › Order complete --}}
    <nav class="vt-co-steps" aria-label="Checkout progress">
      <a href="{{ route('cart') }}" class="vt-co-steps__item">Shopping cart</a>
      <svg class="vt-co-steps__sep" width="14" height="14" viewBox="0 0 14 14" aria-hidden="true"><path d="M5.25 3.5 8.75 7l-3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span class="vt-co-steps__item is-current" aria-current="step">Checkout</span>
      <svg class="vt-co-steps__sep" width="14" height="14" viewBox="0 0 14 14" aria-hidden="true"><path d="M5.25 3.5 8.75 7l-3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span class="vt-co-steps__item is-disabled">Order complete</span>
    </nav>

    <section class="vt-co">
      @if(session('error'))
        <div class="alert alert-warning vt-co__flash">{{ session('error') }}</div>
      @endif
      <div class="vt-co__grid" id="pp-checkout-app"
        data-place-url="{{ route('checkout.place') }}"
        data-verify-url="{{ route('checkout.verify') }}"
        data-check-email-url="{{ route('check.email') }}"
        data-login-url="{{ route('login') }}"
        data-logged-in="{{ !empty($isLoggedIn) ? '1' : '0' }}"
        data-razorpay-key="{{ $razorpayKey ?? '' }}">

        {{-- Billing details --}}
        <div class="vt-co__billing">
          <h1 class="vt-co__title">Billing details</h1>
          @if(!empty($isLoggedIn))
            <p class="vt-co__lead">Signed in — your details are filled in below.</p>
          @else
            <p class="vt-co__lead">Guest checkout — we’ll create your account if this email is new. If it’s already registered, please <a href="{{ route('login') }}">log in</a>.</p>
          @endif

          <form class="vt-co-form" id="pp-checkout-form" method="post" novalidate>
            @csrf
            <div class="vt-co-form__row">
              <div class="vt-co-field">
                <label for="pp_first_name">First Name *</label>
                <input name="first_name" id="pp_first_name" class="form-control" type="text" required autocomplete="given-name" value="{{ old('first_name', $checkoutUser['first_name'] ?? '') }}">
                <div class="invalid-feedback" data-error-for="first_name"></div>
              </div>
              <div class="vt-co-field">
                <label for="pp_last_name">Last Name *</label>
                <input name="last_name" id="pp_last_name" class="form-control" type="text" required autocomplete="family-name" value="{{ old('last_name', $checkoutUser['last_name'] ?? '') }}">
                <div class="invalid-feedback" data-error-for="last_name"></div>
              </div>
            </div>
            <div class="vt-co-field">
              <label for="pp_company">Company name (optional)</label>
              <input name="company" id="pp_company" class="form-control" type="text" autocomplete="organization" value="{{ old('company') }}">
            </div>
            <div class="vt-co-field">
              <label for="pp_country">Country / Region *</label>
              <input type="hidden" name="country" value="India">
              <input id="pp_country" class="form-control" type="text" value="India" readonly tabindex="-1" aria-readonly="true">
              <div class="invalid-feedback" data-error-for="country"></div>
            </div>
            <div class="vt-co-field">
              <label for="pp_address1">House number and street name *</label>
              <input name="address_line1" id="pp_address1" class="form-control" type="text" required autocomplete="address-line1" value="{{ old('address_line1', $checkoutAddress['address_line1'] ?? '') }}">
              <div class="invalid-feedback" data-error-for="address_line1"></div>
            </div>
            <div class="vt-co-field">
              <label for="pp_address2">Apartment, suite, unit, etc. (optional)</label>
              <input name="address_line2" id="pp_address2" class="form-control" type="text" autocomplete="address-line2" value="{{ old('address_line2', $checkoutAddress['address_line2'] ?? '') }}">
            </div>
            <div class="vt-co-field">
              <label for="pp_city">Town / City *</label>
              <input name="city" id="pp_city" class="form-control" type="text" required autocomplete="address-level2" value="{{ old('city', $checkoutAddress['city'] ?? '') }}">
              <div class="invalid-feedback" data-error-for="city"></div>
            </div>
            <div class="vt-co-field">
              <label for="pp_state">State *</label>
              <input name="state" id="pp_state" class="form-control" type="text" required autocomplete="address-level1" value="{{ old('state', $checkoutAddress['state'] ?? '') }}">
              <div class="invalid-feedback" data-error-for="state"></div>
            </div>
            <div class="vt-co-field">
              <label for="pp_pincode">Zip / PIN code *</label>
              <input name="pincode" id="pp_pincode" class="form-control" type="text" required inputmode="numeric" autocomplete="postal-code" value="{{ old('pincode', $checkoutAddress['pincode'] ?? '') }}">
              <div class="invalid-feedback" data-error-for="pincode"></div>
            </div>
            <div class="vt-co-field">
              <label for="pp_phone">Phone *</label>
              <input name="phone" id="pp_phone" class="form-control" type="tel" required autocomplete="tel" value="{{ old('phone', $checkoutUser['phone'] ?? '') }}">
              <div class="invalid-feedback" data-error-for="phone"></div>
            </div>
            <div class="vt-co-field">
              <label for="pp_email">Email Address *</label>
              <input name="email" id="pp_email" class="form-control" type="email" required autocomplete="email" value="{{ old('email', $checkoutUser['email'] ?? '') }}" @if(!empty($isLoggedIn)) readonly @endif>
              <div class="invalid-feedback" data-error-for="email"></div>
              <div class="form-text" id="pp-email-status"></div>
            </div>

            <h2 class="vt-co__subtitle">Additional information</h2>
            <div class="vt-co-field">
              <label for="pp_notes">Order Notes (optional)</label>
              <textarea name="notes" id="pp_notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
            </div>
          </form>
        </div>

        {{-- Your order --}}
        <aside class="vt-co__summary" aria-labelledby="vt-co-order-title">
          <div class="vt-co-card">
            <h2 class="vt-co-card__title" id="vt-co-order-title">Your order</h2>
            <a href="{{ route('cart') }}" class="vt-co-card__edit">Edit order</a>

            <ul class="vt-co-items">
              @forelse(($cartItems ?? collect()) as $item)
                <li class="vt-co-item">
                  <span class="vt-co-item__img"><img src="{{ $item['image'] }}" alt="" width="64" height="64" loading="lazy"></span>
                  <span class="vt-co-item__info">
                    <span class="vt-co-item__name" data-vt-orig="{{ $item['title'] }}">{{ $item['title'] }}</span>
                    <span class="vt-co-item__meta">Quantity: {{ $item['quantity'] }}</span>
                    @if(!empty($item['size']))<span class="vt-co-item__meta">Size: {{ $item['size'] }}</span>@endif
                    @if(!empty($item['color']))<span class="vt-co-item__meta">Colour: {{ $item['color'] }}</span>@endif
                    @if(!empty($item['package_label']))<span class="vt-co-item__meta">Pack: {{ $item['package_label'] }}</span>@endif
                  </span>
                  <span class="vt-co-item__price">{{ $item['line_total_formatted'] }}</span>
                </li>
              @empty
                <li class="vt-co-item vt-co-item--empty">Your cart is empty.</li>
              @endforelse
            </ul>

            <div class="vt-co-coupon" data-coupon-box>
              <div class="vt-co-coupon__row">
                <input class="form-control" type="text" name="coupon_code" data-coupon-input placeholder="Coupon code" value="{{ $discount['code'] ?? '' }}" {{ !empty($discount['code'] ?? null) ? 'readonly' : '' }} aria-label="Coupon code">
                <button class="vt-co-coupon__btn" type="button" data-coupon-apply {{ !empty($discount['code'] ?? null) ? 'hidden' : '' }}>Apply</button>
                <button class="vt-co-coupon__btn vt-co-coupon__btn--ghost" type="button" data-coupon-remove {{ empty($discount['code'] ?? null) ? 'hidden' : '' }}>Remove</button>
              </div>
              <div class="vt-co-coupon__msg" data-coupon-message @if(empty($discount['message'] ?? null)) hidden @endif>{{ $discount['message'] ?? '' }}</div>
            </div>

            <dl class="vt-co-totals">
              <div class="vt-co-totals__row"><dt>Subtotal</dt><dd data-cart-subtotal>{{ $cartSubtotalFormatted ?? '₹ 0.00' }}</dd></div>
              <div class="vt-co-totals__row" data-cart-discount-row @if(empty($discount['code'] ?? null) || ((float) ($discount['amount'] ?? 0) <= 0 && empty($discount['free_shipping'] ?? null))) hidden @endif>
                <dt data-cart-discount-label>Discount{{ !empty($discount['code'] ?? null) ? ' ('.$discount['code'].')' : '' }}</dt>
                <dd data-cart-discount>-{{ $discount['amount_formatted'] ?? '₹ 0.00' }}</dd>
              </div>
              <div class="vt-co-totals__row"><dt>Shipping</dt><dd data-cart-shipping>{{ $shipping['amount_formatted'] ?? 'Free' }}</dd></div>
            </dl>
            <div class="vt-co-total">
              <span>Total</span>
              <span data-cart-total>{{ $cartTotalFormatted ?? $cartSubtotalFormatted ?? '₹ 0.00' }}</span>
            </div>
            @if(!($shipping['is_free'] ?? true))
              <p class="vt-co-note" data-cart-shipping-message>Free shipping on orders of ₹ {{ number_format((float) $shipping['free_shipping_threshold'], 0) }} or more.</p>
            @else
              <p class="vt-co-note" data-cart-shipping-message>Free shipping applied.</p>
            @endif

            <div class="vt-co-pay">
              <label class="vt-co-pay__option" for="pp_pay_razorpay">
                <input id="pp_pay_razorpay" name="payment_method" type="radio" value="razorpay" checked>
                <span>Razorpay</span>
              </label>
              <p class="vt-co-pay__text">Pay securely by card, UPI, netbanking, or wallet via Razorpay.</p>
            </div>

            <div id="pp-checkout-alert" class="alert alert-danger vt-co__alert" hidden></div>

            <button type="button" id="pp-place-order" class="vt-co-place">Place order</button>
          </div>
        </aside>
      </div>
    </section>

    @include('frontend.partials.site-footer')

    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </main>
  <!-- main-area-end -->
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
<script src="{{ asset('frontend/js/script.js?v=vastu-3') }}"></script>
<script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
<script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
<script src="{{ asset('frontend/js/checkout.js') }}?v=vastu-2"></script>
</body>

</html>

