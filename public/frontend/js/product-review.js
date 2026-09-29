/**
 * Product page review form — client-side validation (no page refresh on errors)
 */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var form = document.getElementById('product-review-form');
    if (!form) return;

    var ratingInput = document.getElementById('review-rating');
    var stars = form.querySelectorAll('.review-star-btn');
    var ratingError = document.getElementById('rating-error');
    var formAlert = document.getElementById('review-form-alert');

    function paintStars(value) {
      stars.forEach(function (btn) {
        var r = parseInt(btn.getAttribute('data-rating'), 10);
        btn.style.opacity = r <= value ? '1' : '0.25';
      });
    }

    paintStars(parseInt(ratingInput.value || '5', 10));

    stars.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var r = parseInt(btn.getAttribute('data-rating'), 10);
        ratingInput.value = String(r);
        paintStars(r);
        if (ratingError) ratingError.textContent = '';
        hideAlert();
      });
    });

    function showErr(el, msg) {
      if (!el) return;
      var wrap = el.closest('.form-group') || el.parentElement;
      var err = wrap ? wrap.querySelector('.field-error') : null;
      if (err) err.textContent = msg || '';
      el.classList.toggle('is-invalid', !!msg);
      el.setAttribute('aria-invalid', msg ? 'true' : 'false');
    }

    function clearErr(el) {
      showErr(el, '');
    }

    function showAlert(msg) {
      if (!formAlert) return;
      formAlert.textContent = msg;
      formAlert.hidden = !msg;
      formAlert.classList.toggle('d-none', !msg);
    }

    function hideAlert() {
      showAlert('');
    }

    function validate() {
      var ok = true;
      var messages = [];

      var rating = parseInt(ratingInput.value || '0', 10);
      if (!rating || rating < 1 || rating > 5) {
        ok = false;
        if (ratingError) ratingError.textContent = 'Please select a rating.';
        messages.push('Please select a rating.');
      } else if (ratingError) {
        ratingError.textContent = '';
      }

      var comment = form.querySelector('[name="comment"]');
      var name = form.querySelector('[name="reviewer_name"]');
      var email = form.querySelector('[name="reviewer_email"]');

      clearErr(comment);
      clearErr(name);
      clearErr(email);

      var commentVal = comment ? comment.value.trim() : '';
      if (!commentVal) {
        ok = false;
        showErr(comment, 'The review field is required.');
        messages.push('The review field is required.');
      } else if (commentVal.length < 10) {
        ok = false;
        showErr(comment, 'Review must be at least 10 characters.');
        messages.push('Review must be at least 10 characters.');
      }

      var nameVal = name ? name.value.trim() : '';
      if (!nameVal) {
        ok = false;
        showErr(name, 'Name is required.');
        messages.push('Name is required.');
      }

      var emailVal = email ? email.value.trim() : '';
      if (!emailVal) {
        ok = false;
        showErr(email, 'Email is required.');
        messages.push('Email is required.');
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
        ok = false;
        showErr(email, 'Please enter a valid email.');
        messages.push('Please enter a valid email.');
      }

      return { ok: ok, messages: messages };
    }

    ['comment', 'reviewer_name', 'reviewer_email'].forEach(function (fieldName) {
      var el = form.querySelector('[name="' + fieldName + '"]');
      if (!el) return;
      el.addEventListener('input', function () {
        clearErr(el);
        hideAlert();
      });
    });

    form.addEventListener('submit', function (e) {
      var result = validate();
      if (result.ok) return;

      e.preventDefault();
      e.stopPropagation();

      showAlert(result.messages[0] || 'Please fill in all required fields.');

      var firstInvalid = form.querySelector('.is-invalid') || form.querySelector('#review-star-picker');
      if (firstInvalid && typeof firstInvalid.focus === 'function') {
        firstInvalid.focus({ preventScroll: false });
      }

      var box = document.getElementById('write-review');
      if (box) box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();
