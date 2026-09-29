{{-- "Book a consultation" pop-up. Any link to /info/book-a-consultation (or [data-vt-consult]) opens it. --}}
@php
  $vtUser = auth()->check() && optional(auth()->user())->isCustomer() ? auth()->user() : null;
  $vtInterests = \App\Http\Controllers\ConsultationController::INTERESTS;
@endphp
<div class="vt-consult" data-vt-consult-modal hidden>
  <div class="vt-consult__backdrop" data-vt-consult-close></div>
  <div class="vt-consult__dialog" role="dialog" aria-modal="true" aria-labelledby="vt-consult-title" tabindex="-1">
    <button type="button" class="vt-consult__close" data-vt-consult-close aria-label="Close">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>
    </button>

    <form class="vt-consult__form" action="{{ route('consultation.store') }}" method="post" novalidate data-vt-consult-form>
      @csrf
      <h2 class="vt-consult__title" id="vt-consult-title">Request a personal consultation</h2>

      <div class="vt-consult__row">
        <div class="vt-field">
          <label for="vt-consult-name">Your name</label>
          <input id="vt-consult-name" type="text" name="name" placeholder="Enter your name" autocomplete="name" maxlength="100" required value="{{ $vtUser?->name }}">
          <p class="vt-field__error" data-error-for="name"></p>
        </div>
        <div class="vt-field">
          <label for="vt-consult-phone">Phone / WhatsApp</label>
          <input id="vt-consult-phone" type="tel" name="phone" placeholder="+91 00000 00000" autocomplete="tel" inputmode="tel" maxlength="20" required value="{{ $vtUser?->phone }}">
          <p class="vt-field__error" data-error-for="phone"></p>
        </div>
      </div>

      <div class="vt-field">
        <label for="vt-consult-email">Email</label>
        <input id="vt-consult-email" type="email" name="email" placeholder="you@example.com" autocomplete="email" maxlength="255" required value="{{ $vtUser?->email }}">
        <p class="vt-field__error" data-error-for="email"></p>
      </div>

      <div class="vt-field">
        <label for="vt-consult-interest">Interest</label>
        <div class="vt-field__select">
          <select id="vt-consult-interest" name="interest" required>
            @foreach($vtInterests as $interest)
              <option value="{{ $interest }}">{{ $interest }}</option>
            @endforeach
          </select>
        </div>
        <p class="vt-field__error" data-error-for="interest"></p>
      </div>

      <div class="vt-field">
        <label for="vt-consult-message">Message</label>
        <textarea id="vt-consult-message" name="message" rows="3" maxlength="2000" placeholder="Tell us briefly what you would like guidance with"></textarea>
        <p class="vt-field__error" data-error-for="message"></p>
      </div>

      <button type="submit" class="vt-consult__submit">Send enquiry</button>
      <p class="vt-consult__status" data-vt-consult-status role="status" aria-live="polite"></p>
    </form>

    <div class="vt-consult__done" data-vt-consult-done hidden>
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m7.5 12.5 3 3 6-6.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h2 class="vt-consult__title">Thank you</h2>
      <p data-vt-consult-done-text>Our team will contact you shortly to schedule your consultation.</p>
      <button type="button" class="vt-consult__submit" data-vt-consult-close>Close</button>
    </div>
  </div>
</div>
