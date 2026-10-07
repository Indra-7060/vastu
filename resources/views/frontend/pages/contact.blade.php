{{-- Contact Us: address, phone / WhatsApp, email, Google Map and an enquiry form.
     Details come from Admin → Settings → Site Details; enquiries are saved to Admin → Contact List. --}}
@php
  $S = \App\Support\SiteSettings::class;
  $address = $S::get('footer_location') ?: 'S.No.24, Rama Icon, 4th Floor, Opp. Sanas Kridangan, Near Apollo Hospital, Sadashiv Peth, Pune - 411030';
  $phone = $S::get('contact_phone') ?: '+91 9021 900 600';
  $phoneHref = preg_replace('/[^\d+]/', '', $phone);
  $wa = preg_replace('/\D+/', '', (string) $S::get('contact_whatsapp'));
  if (strlen($wa) === 10) { $wa = '91'.$wa; }
  $email = $S::get('contact_email') ?: 'vastubalaji@gmail.com';
  $mapsLink = 'https://www.google.com/maps?ll=18.503309,73.851995&z=17&t=m&hl=en-GB&gl=US&cid=12657182276774707966';
  $mapEmbed = 'https://maps.google.com/maps?q='.rawurlencode('VastuTathastu, Sadashiv Peth, Pune').'&t=m&z=15&output=embed&iwloc=near';
  $sent = session('contact_sent');
  $pageTitle = 'Contact Us - Vastutathastu';
@endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Contact Vastutathastu in Pune for Vastu, astrology and numerology consultations or help with sacred products. Call, WhatsApp, email or send us a message.">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=212">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body class="vt-page vt-page--contact">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-contact">
      {{-- 1. Page heading --}}
      <section class="vt-contact__hero">
        <nav class="vt-contact__crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">›</span><span>Contact us</span></nav>
        <p class="vt-contact__kicker">We're here to help</p>
        <h1 class="vt-contact__title">Contact Us</h1>
        <p class="vt-contact__intro">Questions about Vastu, astrology, numerology or our sacred products? Reach out and our team will guide you personally.</p>
      </section>

      {{-- 2. Address · Phone / WhatsApp · Email --}}
      <section class="vt-contact__cards" aria-label="Contact details">
        <article class="vt-contact__card">
          <span class="vt-contact__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21.25s-7-5.6-7-11.25a7 7 0 0 1 14 0c0 5.65-7 11.25-7 11.25Z"/><circle cx="12" cy="10" r="2.6"/></svg></span>
          <h2 class="vt-contact__label">Visit us</h2>
          <address class="vt-contact__text">{{ $address }}</address>
          <a class="vt-contact__action" href="{{ $mapsLink }}" target="_blank" rel="noopener">Get directions <span aria-hidden="true">→</span></a>
        </article>
        <article class="vt-contact__card">
          <span class="vt-contact__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5.2 3.75h3.1l1.55 3.9-1.95 1.3a10.9 10.9 0 0 0 5.15 5.15l1.3-1.95 3.9 1.55v3.1a1.55 1.55 0 0 1-1.55 1.55A14.9 14.9 0 0 1 3.65 5.3 1.55 1.55 0 0 1 5.2 3.75Z"/></svg></span>
          <h2 class="vt-contact__label">Call or WhatsApp</h2>
          <p class="vt-contact__text"><a href="tel:{{ $phoneHref }}">{{ $phone }}</a></p>
          <div class="vt-contact__actions">
            <a class="vt-contact__action" href="tel:{{ $phoneHref }}">Call now <span aria-hidden="true">→</span></a>
            @if($wa)<a class="vt-contact__action vt-contact__action--wa" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">WhatsApp <span aria-hidden="true">→</span></a>@endif
          </div>
        </article>
        <article class="vt-contact__card">
          <span class="vt-contact__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4.5 7 7.5 6 7.5-6"/></svg></span>
          <h2 class="vt-contact__label">Email us</h2>
          <p class="vt-contact__text"><a href="mailto:{{ $email }}">{{ $email }}</a></p>
          <a class="vt-contact__action" href="mailto:{{ $email }}">Send an email <span aria-hidden="true">→</span></a>
        </article>
      </section>

      {{-- 3. Enquiry form + Google Map --}}
      <section class="vt-contact__main" aria-labelledby="vt-contact-form-title">
        <div class="vt-contact__formcard">
          <h2 class="vt-contact__formtitle" id="vt-contact-form-title">Enquire Now</h2>
          <p class="vt-contact__formintro">Fill in the following form and our expert support will contact you shortly.</p>

          <div class="vt-contact__done" data-contact-done @unless($sent) hidden @endunless role="status">
            <span class="vt-contact__doneicon" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span>
            <p data-contact-done-text>{{ $sent ?: 'Thank you! We have received your message and will get back to you shortly.' }}</p>
            <button type="button" class="vt-contact__again" data-contact-again>Send another message</button>
          </div>

          <form class="vt-contact__form" method="POST" action="{{ route('contact.submit') }}" data-contact-form novalidate @if($sent) hidden @endif>
            @csrf
            <div class="vt-contact__hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="vt-contact__grid">
              <div class="vt-field">
                <label for="c-name">Full name <span aria-hidden="true">*</span></label>
                <input id="c-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="120" placeholder="Your name">
                <p class="vt-field__err" data-err="name">@error('name'){{ $message }}@enderror</p>
              </div>
              <div class="vt-field">
                <label for="c-phone">Phone number <span aria-hidden="true">*</span></label>
                <input id="c-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required maxlength="20" placeholder="+91 98765 43210" inputmode="tel">
                <p class="vt-field__err" data-err="phone">@error('phone'){{ $message }}@enderror</p>
              </div>
              <div class="vt-field">
                <label for="c-email">Email</label>
                <input id="c-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="190" placeholder="you@example.com">
                <p class="vt-field__err" data-err="email">@error('email'){{ $message }}@enderror</p>
              </div>
              <div class="vt-field">
                <label for="c-city">City</label>
                <input id="c-city" type="text" name="city" value="{{ old('city') }}" autocomplete="address-level2" maxlength="80" placeholder="e.g. Pune">
                <p class="vt-field__err" data-err="city">@error('city'){{ $message }}@enderror</p>
              </div>
              <div class="vt-field vt-field--full">
                <label for="c-subject">Enquiry about</label>
                <select id="c-subject" name="subject">
                  <option value="">Choose a topic</option>
                  @foreach($subjects as $sub)<option value="{{ $sub }}" @selected(old('subject') === $sub)>{{ $sub }}</option>@endforeach
                </select>
                <p class="vt-field__err" data-err="subject">@error('subject'){{ $message }}@enderror</p>
              </div>
              <div class="vt-field vt-field--full">
                <label for="c-message">Your questions in detail <span aria-hidden="true">*</span></label>
                <textarea id="c-message" name="message" rows="5" required maxlength="3000" placeholder="Tell us how we can help…">{{ old('message') }}</textarea>
                <p class="vt-field__err" data-err="message">@error('message'){{ $message }}@enderror</p>
              </div>
            </div>
            <p class="vt-contact__formerr" data-contact-error hidden role="alert"></p>
            <button type="submit" class="vt-contact__submit" data-contact-submit>
              <span data-label>Send message</span>
              <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12H4"/><path d="M15 17s5-3.68 5-5-5-5-5-5"/></svg>
            </button>
          </form>
        </div>

        <div class="vt-contact__map">
          <iframe title="Vastutathastu office location on Google Maps" src="{{ $mapEmbed }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
          <a class="vt-contact__mapbtn" href="{{ $mapsLink }}" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21.25s-7-5.6-7-11.25a7 7 0 0 1 14 0c0 5.65-7 11.25-7 11.25Z"/><circle cx="12" cy="10" r="2.6"/></svg>
            Open in Google Maps
          </a>
        </div>
      </section>
    </main>

    @include('frontend.partials.site-footer')
  </div>

  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-4') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
  <script>
    // Contact form: checks fields in the browser, sends without reloading, shows a thank-you message.
    (function () {
      var form = document.querySelector('[data-contact-form]');
      if (!form || !window.fetch) return;
      var done = document.querySelector('[data-contact-done]');
      var doneText = document.querySelector('[data-contact-done-text]');
      var formErr = form.querySelector('[data-contact-error]');
      var btn = form.querySelector('[data-contact-submit]');
      var label = btn.querySelector('[data-label]');
      function setErr(name, msg) {
        var el = form.querySelector('[data-err="' + name + '"]');
        var input = form.elements[name];
        if (el) el.textContent = msg || '';
        if (input) { input.classList.toggle('is-invalid', !!msg); input.setAttribute('aria-invalid', msg ? 'true' : 'false'); }
      }
      function check() {
        var ok = true, v = function (n) { return (form.elements[n].value || '').trim(); };
        ['name', 'phone', 'email', 'message'].forEach(function (n) { setErr(n, ''); });
        if (v('name').length < 2) { setErr('name', 'Please enter your name.'); ok = false; }
        if (!/^[0-9+\s\-()]{7,20}$/.test(v('phone'))) { setErr('phone', 'Please enter a valid phone number.'); ok = false; }
        if (v('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('email'))) { setErr('email', 'Please enter a valid email address.'); ok = false; }
        if (v('message').length < 5) { setErr('message', 'Please tell us a little more about your enquiry.'); ok = false; }
        return ok;
      }
      form.addEventListener('input', function (e) { if (e.target.name) setErr(e.target.name, ''); });
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        formErr.hidden = true;
        if (!check()) { var bad = form.querySelector('.is-invalid'); if (bad) bad.focus(); return; }
        btn.disabled = true; label.textContent = 'Sending…';
        var token = window.PP_CSRF ? window.PP_CSRF.current() : (form.querySelector('[name=_token]') || {}).value;
        var send = function (tok) {
          return fetch(form.action, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': tok, 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form) });
        };
        send(token).then(function (res) {
          if (res.status === 419 && window.PP_CSRF) return window.PP_CSRF.refresh().then(send);
          return res;
        }).then(function (res) {
          return res.json().catch(function () { return {}; }).then(function (data) { return { res: res, data: data }; });
        }).then(function (r) {
          if (r.res.ok && r.data.ok) {
            form.reset(); form.hidden = true; doneText.textContent = r.data.message; done.hidden = false;
            done.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } else if (r.res.status === 422 && r.data.errors) {
            Object.keys(r.data.errors).forEach(function (n) { setErr(n, r.data.errors[n][0]); });
          } else if (r.res.status === 429) {
            formErr.textContent = 'Too many messages in a short time. Please wait a minute and try again.'; formErr.hidden = false;
          } else {
            formErr.textContent = 'Sorry, your message could not be sent. Please try again or call us.'; formErr.hidden = false;
          }
        }).catch(function () {
          formErr.textContent = 'Network error. Please check your connection and try again.'; formErr.hidden = false;
        }).then(function () { btn.disabled = false; label.textContent = 'Send message'; });
      });
      document.querySelector('[data-contact-again]').addEventListener('click', function () { done.hidden = true; form.hidden = false; form.elements.name.focus(); });
    })();
  </script>
</body>

</html>
