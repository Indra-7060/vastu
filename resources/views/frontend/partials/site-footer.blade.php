@php
  // Every footer item stays on Vastutathastu pages: shop items open their category
  // (empty until products are added) and the rest open placeholder info pages.
  $info = fn (string $slug) => route('info', $slug);
  // Shop links: the real categories shown on the homepage (Admin → Categories → "Show on home").
  $shopLinks = \App\Models\Category::query()->where('is_active', true)->where('show_on_home', true)
      ->orderBy('sort_order')->orderBy('title')->take(6)->get()
      ->mapWithKeys(fn ($c) => [$c->title => $c->frontendUrl()])->all();
  $guidanceLinks = [
      'Vastu Consultation' => $info('vastu-consultation'),
      'Astrology' => $info('astrology'),
      'Numerology' => $info('numerology'),
      'Energy Analysis' => $info('energy-analysis'),
      'Book Appointment' => $info('book-appointment'),
  ];
  $aboutLinks = [
      'Our Story' => $info('our-story'),
      'Testimonials' => $info('testimonials'),
      'Blogs' => route('blog'),
      'Contact Us' => route('contact'),
  ];
  // Contact details, social links and footer text: Admin → Settings → Site Details.
  $S = \App\Support\SiteSettings::class;
  $socialLinks = array_filter([
      'YouTube' => $S::get('social_youtube'),
      'Instagram' => $S::get('social_instagram'),
      'Facebook' => $S::get('social_facebook'),
  ]);
  $phone = $S::get('contact_phone');
  // All phone numbers: the main one + "More phone numbers" (one per line), shown as "96733 66920" (the link dials +91)
  $fmtPhone = function (string $n) {
      $d = preg_replace('/\D+/', '', $n);
      if (strlen($d) === 12 && str_starts_with($d, '91')) { $d = substr($d, 2); }
      return strlen($d) === 10 ? substr($d, 0, 5).' '.substr($d, 5) : trim($n);
  };
  $phones = collect(array_merge([$phone], preg_split('/[\r\n,]+/', (string) $S::get('contact_phones_more'))))
      ->map(fn ($n) => trim((string) $n))->filter()
      ->unique(fn ($n) => substr(preg_replace('/\D+/', '', $n), -10))
      ->map(fn ($n) => ['label' => $fmtPhone($n), 'tel' => (strlen($d = preg_replace('/\D+/', '', $n)) === 10 ? '+91'.$d : '+'.ltrim($d, '+'))])
      ->values();
  $email = $S::get('contact_email');
  $whatsapp = preg_replace('/\D+/', '', $S::get('contact_whatsapp'));
  // Social order in the footer: Instagram, YouTube, then Facebook (only those that are set).
  $socialLinks = array_filter(array_replace(['Instagram' => null, 'YouTube' => null, 'Facebook' => null], $socialLinks));
  $navGroups = ['Explore' => $shopLinks, 'Guidance' => $guidanceLinks, 'Company' => $aboutLinks];
  $navLabels = ['Explore' => 'Category', 'Guidance' => 'Guidance', 'Company' => 'Quick links'];
@endphp
<footer class="vt-footer site-footer">
  @php $cta = \App\Support\SiteBanners::first('consultation_cta'); @endphp
  @if($cta)
  <section class="vt-cta" aria-labelledby="vt-cta-title">
    <div class="vt-cta__copy">
      @if($cta->subtitle)<p class="vt-kicker">{{ $cta->subtitle }}</p>@endif
      <h2 class="vt-cta__title" id="vt-cta-title">{{ $cta->title }}</h2>
      @if($cta->description)<p class="vt-cta__text">{{ $cta->description }}</p>@endif
    </div>
    @if($cta->button_text)
      <a class="vt-cta__btn" href="{{ \App\Support\SiteBanners::link($cta->button_link, route('info', 'book-a-consultation')) }}">{{ $cta->button_text }}</a>
    @endif
  </section>
  @endif

  {{-- Editorial footer: brand · navigation · newsletter (+ social). --}}
  <div class="vt-ftr" data-vt-ftr>
    <div class="vt-ftr__inner">
      <div class="vt-ftr__brand vt-ftr__reveal">
        <a class="vt-ftr__logo notranslate" translate="no" href="{{ route('home') }}" aria-label="Vastutathastu home">
          <img src="{{ asset('vastu/images/logo.svg') }}?v=2" width="244" height="68" alt="Vastutathastu — The Trusted Brand">
        </a>
        @if($S::get('footer_about'))<p class="vt-ftr__about">{{ $S::get('footer_about') }}</p>@endif
        @if($S::get('footer_location') || $phones->isNotEmpty() || $email)
        <address class="vt-ftr__address">
          @if($S::get('footer_location'))
            <span class="vt-ftr__contact"><svg class="vt-ftr__cicon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21.25s-7-5.6-7-11.25a7 7 0 0 1 14 0c0 5.65-7 11.25-7 11.25Z"/><circle cx="12" cy="10" r="2.6"/></svg><span>{{ $S::get('footer_location') }}</span></span>
          @endif
          @if($phones->isNotEmpty())
            <span class="vt-ftr__contact vt-ftr__phones"><svg class="vt-ftr__cicon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5.2 3.75h3.1l1.55 3.9-1.95 1.3a10.9 10.9 0 0 0 5.15 5.15l1.3-1.95 3.9 1.55v3.1a1.55 1.55 0 0 1-1.55 1.55A14.9 14.9 0 0 1 3.65 5.3 1.55 1.55 0 0 1 5.2 3.75Z"/></svg><span class="vt-ftr__phone-list">@foreach($phones as $p)<span class="vt-ftr__phone"><a href="tel:{{ $p['tel'] }}">{{ $p['label'] }}</a>@unless($loop->last)<span class="vt-ftr__phone-sep">,</span>@endunless</span>@endforeach</span></span>
          @endif
          @if($email)
            <a class="vt-ftr__contact" href="mailto:{{ $email }}"><svg class="vt-ftr__cicon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4.5 7 7.5 6 7.5-6"/></svg><span>{{ $email }}</span></a>
          @endif
        </address>
        @endif
      </div>

      <nav class="vt-ftr__nav vt-ftr__reveal" aria-label="Footer">
        @foreach($navGroups as $heading => $links)
          @continue(empty($links))
          <details class="vt-ftr__group vt-ftr__group--{{ \Illuminate\Support\Str::slug($heading) }}" open>
            <summary class="vt-ftr__heading">{{ $navLabels[$heading] ?? $heading }}</summary>
            <ul class="vt-ftr__links">
              @foreach($links as $label => $href)
                <li style="--i: {{ $loop->index }}"><a href="{{ $href }}">{{ $label }}</a></li>
              @endforeach
            </ul>
          </details>
        @endforeach
      </nav>

      <div class="vt-ftr__news vt-ftr__reveal">
        <p class="vt-ftr__heading vt-ftr__heading--news">Stay Connected</p>
        <h2 class="vt-ftr__news-title">Sacred guidance, delivered.</h2>
        @if($S::get('newsletter_text'))<p class="vt-ftr__news-text">{{ $S::get('newsletter_text') }}</p>@endif
        <form class="vt-ftr__signup" data-newsletter-form action="{{ route('newsletter.subscribe') }}" method="post" novalidate>
          @csrf
          <div class="vt-ftr__field">
            <input type="email" name="email" placeholder="Enter your email" autocomplete="email" aria-label="Email address" maxlength="255">
            <button type="submit">Join</button>
          </div>
          <div class="pp-newsletter-status" data-newsletter-status role="status" hidden></div>
        </form>

        {{-- Social: Instagram, Facebook, YouTube in their brand colours. Facebook stays a static icon
             until its link is set in Admin → Settings → Site Details (social_facebook). --}}
        @php $socialIcons = ['Instagram' => 'instagram', 'Facebook' => 'facebook', 'YouTube' => 'youtube']; @endphp
        <ul class="vt-ftr__social" aria-label="Follow Vastutathastu">
          @foreach($socialIcons as $label => $icon)
            <li>
              @if(! empty($socialLinks[$label]))
                <a class="vt-ftr__soc vt-ftr__soc--{{ $icon }}" href="{{ $socialLinks[$label] }}" target="_blank" rel="noopener" aria-label="Vastutathastu on {{ $label }}">
                  @include('frontend.partials.brand-icon', ['name' => $icon, 'size' => 28])
                </a>
              @else
                <span class="vt-ftr__soc vt-ftr__soc--{{ $icon }} is-static" role="img" aria-label="{{ $label }} (coming soon)">
                  @include('frontend.partials.brand-icon', ['name' => $icon, 'size' => 28])
                </span>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </div>

    <div class="vt-ftr__bar">
      <p class="vt-ftr__copy">© {{ date('Y') }} Vastutathastu. All rights reserved. <span class="vt-ftr__sep" aria-hidden="true">|</span> <a class="vt-ftr__credit" href="https://nivtech.co.in/" target="_blank" rel="noopener">Develop by Nivtech</a></p>
      <nav class="vt-ftr__legal" aria-label="Legal">
        <a href="{{ $info('privacy-policy') }}">Privacy Policy</a><span aria-hidden="true">•</span>
        <a href="{{ $info('terms-and-conditions') }}">Terms &amp; Conditions</a><span aria-hidden="true">•</span>
        <a href="{{ $info('shipping-and-returns') }}">Shipping &amp; Returns</a>
      </nav>
    </div>
  </div>
  <script>
    // Footer: gentle reveal on scroll, and nav groups become an accordion on phones.
    (function () {
      var root = document.querySelector('[data-vt-ftr]');
      if (!root) return;
      var mq = window.matchMedia('(max-width: 767px)');
      var groups = root.querySelectorAll('.vt-ftr__group');
      var news = root.querySelector('.vt-ftr__news');
      function syncGroups() {
        groups.forEach(function (g) { g.open = !mq.matches; });
        // On phones the newsletter block has no box of its own (its parts join the column), so it can't be observed.
        if (mq.matches && news) news.classList.add('is-in');
      }
      syncGroups();
      (mq.addEventListener ? mq.addEventListener('change', syncGroups) : mq.addListener(syncGroups));
      groups.forEach(function (g) {
        g.querySelector('summary').addEventListener('click', function (e) { if (!mq.matches) e.preventDefault(); });
      });
      if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      root.classList.add('is-anim');
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } });
      }, { rootMargin: '0px 0px -8% 0px' });
      root.querySelectorAll('.vt-ftr__reveal').forEach(function (el) { io.observe(el); });
    })();
  </script>
</footer>
@include('frontend.partials.whatsapp-button')
@include('frontend.partials.call-button')
