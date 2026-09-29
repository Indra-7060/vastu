/**
 * Footer newsletter subscribe: JS validation + AJAX save
 */
(function () {
  'use strict';

  function site() {
    return window.PP_SITE || {};
  }

  function csrf() {
    return site().csrf
      || (document.querySelector('meta[name="csrf-token"]') || {}).content
      || '';
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(String(value || '').trim());
  }

  function ensureStatus(form) {
    var el = form.querySelector('[data-newsletter-status]');
    if (el) return el;
    el = document.createElement('div');
    el.className = 'pp-newsletter-status';
    el.setAttribute('data-newsletter-status', '');
    el.setAttribute('role', 'status');
    el.hidden = true;
    form.appendChild(el);
    return el;
  }

  function setStatus(form, message, type) {
    var el = ensureStatus(form);
    if (!message) {
      el.hidden = true;
      el.textContent = '';
      el.className = 'pp-newsletter-status';
      return;
    }
    el.hidden = false;
    el.textContent = message;
    el.className = 'pp-newsletter-status is-' + (type || 'info');
  }

  function setBusy(btn, busy) {
    if (!btn) return;
    if (window.PPLoader) {
      if (busy) window.PPLoader.start(btn, 'Subscribing…', 'Please wait…');
      else window.PPLoader.stop(btn, true);
      return;
    }
    if (busy) {
      if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.classList.add('is-busy');
      btn.setAttribute('aria-busy', 'true');
    } else {
      btn.disabled = false;
      btn.classList.remove('is-busy');
      btn.removeAttribute('aria-busy');
      if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
    }
  }

  function subscribe(form) {
    var input = form.querySelector('input[name="email"], input[type="email"]');
    var btn = form.querySelector('button[type="submit"], .subscribe-btn');
    if (!input) return;

    var email = String(input.value || '').trim();
    setStatus(form, '', '');
    input.classList.remove('is-invalid');

    if (!email) {
      input.classList.add('is-invalid');
      setStatus(form, 'Please enter your email address.', 'error');
      input.focus();
      return;
    }

    if (!isValidEmail(email)) {
      input.classList.add('is-invalid');
      setStatus(form, 'Please enter a valid email address.', 'error');
      input.focus();
      return;
    }

    var url = form.getAttribute('action')
      || site().newsletterSubscribe
      || '/newsletter/subscribe';

    setBusy(btn, true);

    fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ email: email })
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: res.ok, status: res.status, data: data };
      }).catch(function () {
        return { ok: res.ok, status: res.status, data: {} };
      });
    }).then(function (res) {
      setBusy(btn, false);

      if (res.ok && res.data && res.data.success) {
        setStatus(form, res.data.message || 'Thanks for subscribing!', 'success');
        input.value = '';
        input.classList.remove('is-invalid');
        return;
      }

      var msg = (res.data && res.data.message)
        || (res.data && res.data.errors && res.data.errors.email && res.data.errors.email[0])
        || 'Could not subscribe. Please try again.';

      if (res.status === 422) {
        input.classList.add('is-invalid');
      }

      setStatus(form, msg, 'error');
    }).catch(function () {
      setBusy(btn, false);
      setStatus(form, 'Network error. Please try again.', 'error');
    });
  }

  var checkTimer = null;

  function checkDuplicate(form, input) {
    var email = String(input.value || '').trim();
    if (!email || !isValidEmail(email)) return;

    var url = site().newsletterCheck || '/newsletter/check';
    fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ email: email })
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: res.ok, data: data };
      });
    }).then(function (res) {
      if (!res.data) return;
      if (res.data.available === false) {
        input.classList.add('is-invalid');
        setStatus(form, res.data.message || 'This email is already subscribed.', 'error');
      }
    }).catch(function () { /* ignore preview errors */ });
  }

  document.addEventListener('submit', function (event) {
    var form = event.target.closest('.footer_mailchimp_form, [data-newsletter-form]');
    if (!form) return;
    event.preventDefault();
    event.stopPropagation();
    subscribe(form);
  }, true);

  document.addEventListener('input', function (event) {
    var input = event.target.closest('.footer_mailchimp_form input[name="email"], [data-newsletter-form] input[name="email"]');
    if (!input) return;
    input.classList.remove('is-invalid');
    var form = input.closest('form');
    if (form) setStatus(form, '', '');
    clearTimeout(checkTimer);
  });

  document.addEventListener('blur', function (event) {
    var input = event.target.closest('.footer_mailchimp_form input[name="email"], [data-newsletter-form] input[name="email"]');
    if (!input) return;
    var form = input.closest('form');
    if (!form) return;
    clearTimeout(checkTimer);
    checkTimer = setTimeout(function () {
      checkDuplicate(form, input);
    }, 200);
  }, true);
})();
