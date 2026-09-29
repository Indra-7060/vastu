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
  $legalLinks = [
      'Privacy Policy' => $info('privacy-policy'),
      'Terms & Conditions' => $info('terms-and-conditions'),
      'Shipping & Returns' => $info('shipping-and-returns'),
  ];
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

  <div class="vt-footer__main">
    <div class="vt-footer__brand">
      <a class="vt-footer__logo" href="{{ route('home') }}" aria-label="Vastutathastu home">
        <img src="{{ asset('vastu/images/logo.svg') }}?v=2" width="244" height="68" alt="Vastutathastu — The Trusted Brand">
      </a>
      @if($S::get('footer_about'))<p class="vt-footer__about">{{ $S::get('footer_about') }}</p>@endif
      @if($S::get('footer_location') || $S::get('footer_tagline'))
      <p class="vt-footer__place">
        {{ $S::get('footer_location') }}
        @if($S::get('footer_tagline'))<br>{{ $S::get('footer_tagline') }}@endif
      </p>
      @endif
      {{-- The phone number is used by the floating call button (bottom-left). --}}
      @if($email)
      <p class="vt-footer__contact"><a href="mailto:{{ $email }}">{{ $email }}</a></p>
      @endif
    </div>

    <nav class="vt-footer__col vt-footer__col--shop" aria-label="Category">
      <p class="vt-kicker">Category</p>
      <ul>
        @foreach($shopLinks as $label => $href)
          <li><a href="{{ $href }}">{{ $label }}</a></li>
        @endforeach
      </ul>
    </nav>

    <nav class="vt-footer__col vt-footer__col--guidance" aria-label="Guidance">
      <p class="vt-kicker">Guidance</p>
      <ul>
        @foreach($guidanceLinks as $label => $href)
          <li><a href="{{ $href }}">{{ $label }}</a></li>
        @endforeach
      </ul>
    </nav>

    <nav class="vt-footer__col vt-footer__col--about" aria-label="Quick links">
      <p class="vt-kicker">Quick links</p>
      <ul>
        @foreach($aboutLinks as $label => $href)
          <li><a href="{{ $href }}">{{ $label }}</a></li>
        @endforeach
      </ul>
    </nav>

    <div class="vt-footer__news">
      <p class="vt-kicker">Stay Connected</p>
      <p class="vt-footer__news-title">{{ $S::get('newsletter_title') }}</p>
      @if($S::get('newsletter_text'))<p class="vt-footer__news-text">{{ $S::get('newsletter_text') }}</p>@endif
      <form class="vt-signup" data-newsletter-form action="{{ route('newsletter.subscribe') }}" method="post" novalidate>
        @csrf
        <input type="email" name="email" placeholder="Enter your email" autocomplete="email" aria-label="Email address" maxlength="255">
        <button type="submit">JOIN</button>
      </form>
      <div class="pp-newsletter-status" data-newsletter-status role="status" hidden></div>
      @if($socialLinks)
      <p class="vt-social vt-social--icons">
        @foreach($socialLinks as $label => $href)
          <a href="{{ $href }}" target="_blank" rel="noopener" aria-label="{{ $label }}" title="{{ $label }}">
            @if($label === 'YouTube')
              <svg viewBox="0 0 28 20" width="30" height="22" aria-hidden="true"><rect width="28" height="20" rx="5" fill="#ff0000"/><path d="M11 5.8v8.4L18.4 10z" fill="#fff"/></svg>
            @elseif($label === 'Instagram')
              <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><defs><radialGradient id="vtIgGrad" cx="30%" cy="107%" r="150%"><stop offset="0" stop-color="#fdf497"/><stop offset=".05" stop-color="#fdf497"/><stop offset=".45" stop-color="#fd5949"/><stop offset=".6" stop-color="#d6249f"/><stop offset=".9" stop-color="#285AEB"/></radialGradient></defs><rect width="24" height="24" rx="6" fill="url(#vtIgGrad)"/><rect x="5.5" y="5.5" width="13" height="13" rx="4" fill="none" stroke="#fff" stroke-width="1.8"/><circle cx="12" cy="12" r="3.2" fill="none" stroke="#fff" stroke-width="1.8"/><circle cx="16.4" cy="7.6" r="1.1" fill="#fff"/></svg>
            @elseif($label === 'Facebook')
              <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#1877f2"/><path d="M13.3 19.5v-6.2h2.1l.3-2.4h-2.4V9.4c0-.7.2-1.2 1.2-1.2h1.3V6.1a17 17 0 0 0-1.9-.1c-1.9 0-3.2 1.2-3.2 3.3v1.8H8.6v2.4h2.1v6.2z" fill="#fff"/></svg>
            @else
              {{ $label }}
            @endif
          </a>
        @endforeach
      </p>
      @endif
    </div>
  </div>

  <div class="vt-footer__bottom">
    <span>&copy; {{ date('Y') }} Vastutathastu. All rights reserved.</span>
    <span class="vt-footer__legal">
      @foreach($legalLinks as $label => $href)
        <a href="{{ $href }}">{{ $label }}</a>@if(!$loop->last)<span aria-hidden="true">•</span>@endif
      @endforeach
    </span>
    <span class="vt-footer__locale">India • English</span>
  </div>
</footer>
@include('frontend.partials.whatsapp-button')
