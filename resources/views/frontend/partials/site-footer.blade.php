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
      'Contact Us' => $info('contact-us'),
  ];
  // Contact details, social links and footer text: Admin → Settings → Site Details.
  $S = \App\Support\SiteSettings::class;
  $socialLinks = array_filter([
      'YouTube' => $S::get('social_youtube'),
      'Instagram' => $S::get('social_instagram'),
      'Facebook' => $S::get('social_facebook'),
  ]);
  $phone = $S::get('contact_phone');
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
        @if($S::get('footer_location') || $phone || $email)
        <address class="vt-ftr__address">
          @if($S::get('footer_location'))<span>{{ $S::get('footer_location') }}</span>@endif
          @if($phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a>@endif
          @if($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@endif
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

        @if($socialLinks)
        <ul class="vt-ftr__social" aria-label="Follow Vastutathastu">
          @foreach($socialLinks as $label => $href)
            <li>
              <a class="vt-ftr__soc vt-ftr__soc--{{ strtolower($label) }}" href="{{ $href }}" target="_blank" rel="noopener" aria-label="Vastutathastu on {{ $label }}" title="{{ $label }}">
                @if($label === 'Instagram')
                  <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><defs><radialGradient id="vt-ig-grad" cx="30%" cy="107%" r="150%"><stop offset="0" stop-color="#fdf497"/><stop offset=".05" stop-color="#fdf497"/><stop offset=".45" stop-color="#fd5949"/><stop offset=".6" stop-color="#d6249f"/><stop offset=".9" stop-color="#285aeb"/></radialGradient></defs><rect x="1" y="1" width="22" height="22" rx="6" fill="url(#vt-ig-grad)"/><rect x="5.5" y="5.5" width="13" height="13" rx="3.8" fill="none" stroke="#fff" stroke-width="1.6"/><circle cx="12" cy="12" r="3.1" fill="none" stroke="#fff" stroke-width="1.6"/><circle cx="16.1" cy="7.9" r=".95" fill="#fff"/></svg>
                @elseif($label === 'YouTube')
                  <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><rect x="1" y="4.5" width="22" height="15" rx="4.5" fill="#ff0000"/><path d="M10 8.8v6.4l5.6-3.2z" fill="#fff"/></svg>
                @else
                  <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><rect x="1" y="1" width="22" height="22" rx="6" fill="#1877f2"/><path d="M13.4 20v-6.3h2.1l.3-2.5h-2.4V9.6c0-.7.2-1.2 1.2-1.2h1.3V6.2a17 17 0 0 0-1.9-.1c-1.9 0-3.2 1.2-3.2 3.3v1.8H8.7v2.5h2.1V20" fill="#fff"/></svg>
                @endif
              </a>
            </li>
          @endforeach
        </ul>
        @endif
      </div>
    </div>

    <div class="vt-ftr__bar">
      <p class="vt-ftr__copy">© {{ date('Y') }} Vastutathastu. All rights reserved.</p>
      <nav class="vt-ftr__legal" aria-label="Legal">
        <a href="{{ $info('privacy-policy') }}">Privacy Policy</a><span aria-hidden="true">•</span>
        <a href="{{ $info('terms-and-conditions') }}">Terms &amp; Conditions</a><span aria-hidden="true">•</span>
        <a href="{{ $info('shipping-and-returns') }}">Shipping &amp; Returns</a>
      </nav>
      <p class="vt-ftr__locale">India • English</p>
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
