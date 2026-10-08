@php $pageTitle = 'Order - Vastutathastu'; @endphp
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
<title>{{ $pageTitle ?? 'Order - Vastutathastu' }}</title>

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=217">
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
    <section class="page-title pt120">
      <div class="container">
        <div class="row">
          <div class="col-xxl-8 mx-auto">
            <div class="breadcrumb-list text-center">
              <ul>
                <li class="breadcrumb-list list-inline-item"><a href="{{ route('cart') }}">SHOPPING CART</a></li>
                <li class="breadcrumb-list list-inline-item"><a href="#"><i class="far fa-angle-right"></i></a></li>
                <li class="breadcrumb-list list-inline-item"><a href="{{ route('checkout') }}">CHECKOUT</a></li>
                <li class="breadcrumb-list list-inline-item"><a href="#"><i class="far fa-angle-right"></i></a></li>
                <li class="breadcrumb-list list-inline-item active">
                  <span>ORDER COMPLETE</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Order start-->
    <section class="shop-checkout pt100 pb90">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-6">
            <div class="section-title text-center mb20">
              <div class="icon mb25">
                <svg width="70" height="70" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M58.2422 44.0234C60.5076 44.0234 62.3438 45.8596 62.3438 48.125C62.3438 50.3891 60.5062 52.2266 58.2422 52.2266H56.4648L56.4389 52.3346C58.2559 52.7543 59.6094 54.3826 59.6094 56.3281C59.6094 58.5922 57.7719 60.4297 55.5078 60.4297H52.7734C55.0389 60.4297 56.875 62.2658 56.875 64.5312C56.875 66.7967 55.0389 68.6328 52.7734 68.6328C51.1433 68.6328 39.6252 68.6328 38.0707 68.6328C30.5826 68.6328 28.592 67.7141 21.3281 65.8984V38.5547C21.4728 38.4413 43.2031 34.4698 43.2031 21.3281V19.9609C43.2031 17.6955 45.0393 15.8594 47.3047 15.8594C49.5674 15.8594 51.4021 17.6914 51.4062 19.9541C51.4336 19.9541 52.2895 28.5852 48.6719 35.8203H60.9766C63.242 35.8203 65.0781 37.6564 65.0781 39.9219C65.0781 42.1859 63.2406 44.0234 60.9766 44.0234H58.2422Z" fill="#FEF7F3"/>
                  <path d="M21.3281 38.5547V65.8984C21.3281 67.4092 20.1045 68.6328 18.5938 68.6328H4.92188V35.8203H18.5938C20.1045 35.8203 21.3281 37.0439 21.3281 38.5547Z" fill="#E6E6E6"/>
                  <path d="M48.6719 7.65625V1.36719C48.6719 0.612227 48.0596 0 47.3047 0C46.5497 0 45.9375 0.612227 45.9375 1.36719V7.65625C45.9375 8.41121 46.5497 9.02344 47.3047 9.02344C48.0596 9.02344 48.6719 8.41121 48.6719 7.65625Z" fill="#1D1D1D"/>
                  <path d="M15.8594 42.6562C15.1047 42.6562 14.4922 43.2688 14.4922 44.0234C14.4922 44.7781 15.1047 45.3906 15.8594 45.3906C16.6141 45.3906 17.2266 44.7781 17.2266 44.0234C17.2266 43.2688 16.6141 42.6562 15.8594 42.6562Z" fill="#1D1D1D"/>
                  <path d="M60.9766 34.4531H50.7702C52.3503 30.4058 52.9899 25.642 52.8665 21.4014C52.843 20.5922 52.812 20.1242 52.77 19.8296C52.6993 16.8755 50.2737 14.4922 47.3047 14.4922C44.2892 14.4922 41.8359 16.9455 41.8359 19.9609V21.3281C41.8359 29.975 30.7325 34.8333 22.353 36.9167C21.7194 35.4683 20.2732 34.4531 18.5938 34.4531H4.92188C4.16691 34.4531 3.55469 35.0654 3.55469 35.8203V68.6328C3.55469 69.3878 4.16691 70 4.92188 70H18.5938C20.262 70 21.6999 68.9983 22.34 67.5652C23.0338 67.7432 23.6779 67.9114 24.2725 68.0667C29.046 69.3134 31.6755 70 38.0707 70H52.7734C55.7889 70 58.2422 67.5467 58.2422 64.5312C58.2422 63.4069 57.9008 62.361 57.3166 61.4909C59.4286 60.7522 60.9766 58.7342 60.9766 56.3281C60.9766 55.2079 60.6435 54.1585 60.0611 53.2845C62.1638 52.5446 63.7109 50.5312 63.7109 48.125C63.7109 47.0006 63.3696 45.9547 62.7854 45.0846C64.8974 44.346 66.4453 42.328 66.4453 39.9219C66.4453 36.9064 63.992 34.4531 60.9766 34.4531ZM19.9609 65.8984C19.9609 66.6523 19.3476 67.2656 18.5938 67.2656H6.28906V37.1875H18.5938C19.3476 37.1875 19.9609 37.8008 19.9609 38.5547V65.8984ZM60.9766 42.6562C58.0441 42.6562 57.345 42.6562 54.1406 42.6562C53.3857 42.6562 52.7734 43.2685 52.7734 44.0234C52.7734 44.7784 53.3857 45.3906 54.1406 45.3906H58.2422C59.7499 45.3906 60.9766 46.6173 60.9766 48.125C60.9766 49.6352 59.7524 50.8594 58.2422 50.8594H51.4062C50.6513 50.8594 50.0391 51.4716 50.0391 52.2266C50.0391 52.9815 50.6513 53.5938 51.4062 53.5938H55.5078C56.9976 53.5938 58.2422 54.7839 58.2422 56.3281C58.2422 57.8383 57.018 59.0625 55.5078 59.0625C52.5753 59.0625 51.8763 59.0625 48.6719 59.0625C47.9169 59.0625 47.3047 59.6747 47.3047 60.4297C47.3047 61.1846 47.9169 61.7969 48.6719 61.7969H52.7734C54.2812 61.7969 55.5078 63.0235 55.5078 64.5312C55.5078 66.039 54.2812 67.2656 52.7734 67.2656H38.0707C32.0268 67.2656 29.6662 66.6492 24.9635 65.421C24.2745 65.2411 23.5189 65.0438 22.6953 64.8338V39.6519C32.5894 37.2951 44.5703 31.5425 44.5703 21.3281V19.9609C44.5703 18.4532 45.797 17.2266 47.3047 17.2266C48.8097 17.2266 50.0362 18.4513 50.0391 19.9567V19.9609C50.0391 20.5551 50.7806 27.8473 47.807 34.4531H43.2031C42.4482 34.4531 41.8359 35.0654 41.8359 35.8203C41.8359 36.5753 42.4482 37.1875 43.2031 37.1875C44.1578 37.1875 59.4182 37.1875 60.9766 37.1875C62.4843 37.1875 63.7109 38.4141 63.7109 39.9219C63.7109 41.4321 62.4868 42.6562 60.9766 42.6562Z" fill="#1D1D1D"/>
                  <path d="M15.8594 48.125C15.1044 48.125 14.4922 48.7372 14.4922 49.4922V60.4297C14.4922 61.1846 15.1044 61.7969 15.8594 61.7969C16.6143 61.7969 17.2266 61.1846 17.2266 60.4297V49.4922C17.2266 48.7372 16.6143 48.125 15.8594 48.125Z" fill="#1D1D1D"/>
                  <path d="M29.5312 18.5938C29.5312 19.3487 30.1435 19.9609 30.8984 19.9609H36.3672C37.1221 19.9609 37.7344 19.3487 37.7344 18.5938C37.7344 17.8388 37.1221 17.2266 36.3672 17.2266H30.8984C30.1435 17.2266 29.5312 17.8388 29.5312 18.5938Z" fill="#1D1D1D"/>
                  <path d="M56.875 18.5938C56.875 19.3487 57.4872 19.9609 58.2422 19.9609H63.7109C64.4659 19.9609 65.0781 19.3487 65.0781 18.5938C65.0781 17.8388 64.4659 17.2266 63.7109 17.2266H58.2422C57.4872 17.2266 56.875 17.8388 56.875 18.5938Z" fill="#1D1D1D"/>
                  <path d="M57.9386 6.02641L54.0723 9.89268C53.5384 10.4266 53.5384 11.2923 54.0723 11.8263C54.6065 12.3602 55.4717 12.36 56.0059 11.8263L59.8722 7.96002C60.4061 7.42614 60.4061 6.56043 59.8722 6.02641C59.3381 5.49266 58.4727 5.49266 57.9386 6.02641Z" fill="#1D1D1D"/>
                  <path d="M40.5372 11.8263C41.0711 11.2924 41.0711 10.4267 40.5372 9.89268L36.6709 6.02641C36.1369 5.49266 35.2715 5.49266 34.7373 6.02641C34.2034 6.5603 34.2034 7.426 34.7373 7.96002L38.6036 11.8263C39.1379 12.3602 40.0032 12.36 40.5372 11.8263Z" fill="#1D1D1D"/>
                </svg>
              </div>
              <h2 class="title mb15">ORDER RECEIVED</h2>
              <div class="text">Thank you. Your order has been received.</div>
              @if(session('vt_order_placed') === $placedOrder->order_number)
                {{-- Straight from checkout: gently take the customer back to the home page. --}}
                <div class="vt-order-next" data-vt-order-next data-seconds="10" data-home="{{ route('home') }}" role="status">
                  <p class="vt-order-next__text">Taking you back to the home page in <span class="vt-order-next__count notranslate" translate="no" data-vt-order-count>10</span> seconds for your next shopping.</p>
                  <div class="vt-order-next__actions">
                    <a class="vt-btn vt-btn--solid" href="{{ route('home') }}">Continue shopping now</a>
                    <button type="button" class="vt-btn vt-btn--outline" data-vt-order-stay>Stay on this page</button>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
        <div class="row mt15">
          <div class="col-lg-8 mx-auto">
            <div class="order_lists text-center">
              <ul class="d-sm-flex align-items-center justify-content-between mx-sm-auto">
                <li class="mb-3 mb-sm-0">
                  <p class="text">Order number:</p>
                  <h5 class="sub-title">{{ $placedOrder->order_number ?? '—' }}</h5>
                </li>
                <li class="mb-3 mb-sm-0">
                  <p class="text">Date</p>
                  <h5 class="sub-title">{{ optional(optional($placedOrder)->ordered_at)->format('F j, Y') ?? now()->format('F j, Y') }}</h5>
                </li>
                <li class="mb-3 mb-sm-0">
                  <p class="text">Total</p>
                  <h5 class="sub-title">₹{{ $placedOrder ? number_format((float) $placedOrder->payable_amount, 2) : '0.00' }}</h5>
                </li>
                <li>
                  <p class="text">Payment Method</p>
                  <h5 class="sub-title">{{ $placedOrder ? strtoupper((string) $placedOrder->payment_mode) : 'Razorpay' }}</h5>
                </li>
              </ul>
            </div>
            <div class="checkout_sidebar pb20">
              <div class="order-widget">
                <h4 class="title text-uppercase mb20">ORDER DETAILS</h4>
                <h6 class="bb1">PRODUCT <span class="float-end">TOTAL</span></h6>
                @forelse(($placedOrder?->items ?? collect()) as $item)
                  <div class="prd-item d-flex mb-2 mt30 mb20">
                    <div class="img flex-shrink-0">
                      <img class="float-start mr15" src="{{ \App\Support\Media::url($item->product_image) }}" alt="{{ $item->product_title }}" width="70" height="100" style="object-fit:cover;">
                    </div>
                    <div class="cart_dtls flex-grow-1">
                      <span class="prd-title d-block mb5">{{ $item->product_title }} <small class="float-end">₹{{ number_format((float) $item->total_price, 2) }}</small></span>
                      @if($item->color || $item->size)
                        <span class="prd-qntt d-block">
                          @if($item->color)Colour: {{ $item->color }}@endif
                          @if($item->color && $item->size) · @endif
                          @if($item->size)Size: {{ $item->size }}@endif
                        </span>
                      @endif
                      @if($item->package_label)
                        <div>Pack: {{ $item->package_label }}</div>
                      @endif
                      <span class="prd-qntt d-block">Quantity: {{ $item->quantity }}</span>
                    </div>
                  </div>
                @empty
                  <p class="text-muted mt-3">No order details found.</p>
                @endforelse
                <div class="checkout-price-table cart-total-widget p-0 border-0">
                  <ul>
                    <li class="bb1 d-flex align-items-center justify-content-between"><span class="text">Subtotal</span> <span class="value">₹{{ $placedOrder ? number_format((float) $placedOrder->subtotal, 2) : '0.00' }}</span></li>
                    @if($placedOrder && (float) $placedOrder->discount_amount > 0)
                      <li class="bb1 d-flex align-items-center justify-content-between"><span class="text">Discount{{ $placedOrder->coupon_code ? ' ('.$placedOrder->coupon_code.')' : '' }}</span> <span class="value">-₹{{ number_format((float) $placedOrder->discount_amount, 2) }}</span></li>
                    @endif
                    <li class="bb1 d-flex align-items-center justify-content-between"><span class="text">Shipping</span> <span class="value">₹{{ $placedOrder ? number_format((float) $placedOrder->delivery_charge, 2) : '0.00' }}</span></li>
                    <li class="d-flex align-items-center justify-content-between text-end"><span class="text2">Total</span><span class="text2">₹{{ $placedOrder ? number_format((float) $placedOrder->payable_amount, 2) : '0.00' }}</span></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Order End-->

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
<script src="{{ asset('frontend/js/script.js?v=vastu-4') }}"></script>
<script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
<script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
@include('frontend.partials.cart-script')
  <script>
    // Order complete → home page after a short, visible countdown (can be stopped or skipped).
    (function () {
      var box = document.querySelector('[data-vt-order-next]');
      if (!box) return;
      var left = parseInt(box.getAttribute('data-seconds'), 10) || 10;
      var textEl = box.querySelector('.vt-order-next__text');
      var L = window.vtLang;
      var localized = L && L.lang !== 'en' && L.say('orderNext', '0');
      if (localized) { textEl.classList.add('notranslate'); textEl.setAttribute('translate', 'no'); }   // written below per language
      var show = function (n) {
        if (localized) textEl.innerHTML = L.say('orderNext', String(n));
        else box.querySelector('[data-vt-order-count]').textContent = String(n);
      };
      show(left);
      var timer = setInterval(function () {
        left -= 1;
        if (left <= 0) { clearInterval(timer); window.location.href = box.getAttribute('data-home'); return; }
        show(left);
      }, 1000);
      box.querySelector('[data-vt-order-stay]').addEventListener('click', function () {
        clearInterval(timer);
        box.classList.add('is-stopped');
      });
    })();
  </script>
</body>

</html>

