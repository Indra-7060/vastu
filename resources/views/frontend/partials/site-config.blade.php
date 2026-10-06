@once('pp-site-config')
@php
  $ppCategories = ($menuCategories ?? collect())->map(function ($c) {
      return [
          'title' => $c->title,
          'slug' => $c->slug,
          'url' => $c->frontendUrl(),
      ];
  })->values();

  $ppAuthUser = auth()->user();
  $ppIsCustomer = $ppAuthUser && method_exists($ppAuthUser, 'isCustomer') && $ppAuthUser->isCustomer();
  $ppUserPayload = $ppIsCustomer
      ? [
          'id' => $ppAuthUser->id,
          'name' => $ppAuthUser->name,
          'email' => $ppAuthUser->email,
      ]
      : null;
  // Saved products (product_id => wishlist row id) so wishlist hearts render filled and can be removed.
  $ppWishlist = $ppIsCustomer
      ? \App\Models\Wishlist::where('user_id', $ppAuthUser->id)->pluck('id', 'product_id')
      : collect();
  $ppBasePath = rtrim((string) (parse_url((string) config('app.url'), PHP_URL_PATH) ?: ''), '/');
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
  window.PP_SITE = {
    name: @json(config('app.name', 'Vastutathastu')),
    basePath: @json($ppBasePath),
    home: @json(route('home')),
    collection: @json(route('collection')),
    shop: @json(route('shop')),
    cart: @json(route('cart')),
    checkout: @json(route('checkout')),
    blog: @json(route('blog')),
    about: @json(route('about')),
    support: @json(route('support')),
    login: @json(route('login')),
    signup: @json(route('signup')),
    forgotPassword: @json(route('customer.password.request')),
    resetPassword: @json(route('customer.password.update')),
    loginSubmit: @json(route('login.submit')),
    signupSubmit: @json(route('signup.submit')),
    checkEmail: @json(route('check.email')),
    logout: @json(route('logout')),
    account: @json(url('/account')),
    accountOverview: @json(url('/account/overview')),
    wishlistStore: @json(url('/account-api/wishlist')),
    wishlistItems: @json((object) $ppWishlist->all()),
    cartStore: @json(route('cart.api.store')),
    cartUpdateBase: @json(url('/cart-api')),
    cartBuyNow: @json(route('cart.api.buy-now')),
    cartIndex: @json(route('cart.api.index')),
    couponApply: @json(route('cart.api.coupon.apply')),
    couponRemove: @json(route('cart.api.coupon.remove')),
    cartCount: @json(app(\App\Services\CartService::class)->count()),
    assetBase: @json(asset('frontend')),
    csrf: @json(csrf_token()),
    authenticated: @json((bool) $ppIsCustomer),
    user: @json($ppUserPayload),
    searchUrl: @json(route('search')),
    newsletterSubscribe: @json(route('newsletter.subscribe')),
    newsletterCheck: @json(route('newsletter.check')),
    logo: @json(asset('vastu/images/logo.svg').'?v=2'),
    logoAlt: @json(asset('vastu/images/logo.svg').'?v=2'),
    logoHeader: @json(asset('vastu/images/logo.svg').'?v=2'),
    logoDark: @json(asset('vastu/images/logo.svg').'?v=2'),
    categories: @json($ppCategories),
    social: {
      facebook: @json(\App\Support\SiteSettings::get('social_facebook') ?: 'https://www.facebook.com/'),
      instagram: @json(\App\Support\SiteSettings::get('social_instagram') ?: 'https://www.instagram.com/'),
      youtube: @json(\App\Support\SiteSettings::get('social_youtube') ?: 'https://www.youtube.com/'),
      pinterest: "https://www.pinterest.com/",
      tiktok: "https://www.tiktok.com/"
    }
  };

  // CSRF token helper. Signing in/out (also in another tab) or an expired session gives the
  // browser a new token, so a page that was already open would get "CSRF token mismatch".
  // Scripts retry once after refresh(); the token is also refreshed when the tab is shown again.
  (function () {
    var url = @json(route('csrf.refresh'));
    var pending = null;
    function apply(token) {
      if (!token) return;
      window.PP_SITE.csrf = token;
      var meta = document.querySelector('meta[name="csrf-token"]');
      if (meta) meta.setAttribute('content', token);
      document.querySelectorAll('input[name="_token"]').forEach(function (i) { i.value = token; });
    }
    window.PP_CSRF = {
      current: function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return (meta && meta.getAttribute('content')) || window.PP_SITE.csrf || '';
      },
      refresh: function () {
        if (pending) return pending;
        pending = fetch(url, { credentials: 'same-origin', cache: 'no-store', headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.ok ? r.json() : {}; })
          .then(function (d) { apply(d && d.token); return window.PP_CSRF.current(); })
          .catch(function () { return window.PP_CSRF.current(); })
          .finally(function () { pending = null; });
        return pending;
      }
    };
    var hiddenAt = 0;
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { hiddenAt = Date.now(); return; }
      if (hiddenAt && Date.now() - hiddenAt > 5000) window.PP_CSRF.refresh();
    });
  })();
</script>
<script src="{{ asset('frontend/js/pp-loader.js') }}?v=1"></script>
<script src="{{ asset('frontend/js/pp-confirm.js') }}?v=1"></script>
@endonce
