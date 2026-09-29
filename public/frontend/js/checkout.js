/**
 * Checkout: JS validation, guest email check, Razorpay place + verify
 */
(function () {
  'use strict';

  var root = document.getElementById('pp-checkout-app');
  var form = document.getElementById('pp-checkout-form');
  var placeBtn = document.getElementById('pp-place-order');
  if (!root || !form || !placeBtn) return;

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content
    || (form.querySelector('input[name="_token"]') || {}).value
    || (window.PP_SITE && window.PP_SITE.csrf)
    || '';

  var placeUrl = root.getAttribute('data-place-url');
  var verifyUrl = root.getAttribute('data-verify-url');
  var checkEmailUrl = root.getAttribute('data-check-email-url');
  var loginUrl = root.getAttribute('data-login-url') || '/login';
  var isLoggedIn = root.getAttribute('data-logged-in') === '1';
  var emailOk = isLoggedIn;
  var emailTimer = null;

  function alertBox(message, show) {
    var el = document.getElementById('pp-checkout-alert');
    if (!el) return;
    if (!show || !message) {
      el.hidden = true;
      el.textContent = '';
      return;
    }
    el.hidden = false;
    el.textContent = message;
  }

  function setError(name, message) {
    var input = form.querySelector('[name="' + name + '"]');
    var feedback = form.querySelector('[data-error-for="' + name + '"]');
    if (input) {
      if (message) input.classList.add('is-invalid');
      else input.classList.remove('is-invalid');
    }
    if (feedback) feedback.textContent = message || '';
  }

  function clearErrors() {
    form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
    form.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; });
    alertBox('', false);
  }

  function val(name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? String(el.value || '').trim() : '';
  }

  function validate() {
    clearErrors();
    var ok = true;
    var required = [
      ['first_name', 'First name is required.'],
      ['last_name', 'Last name is required.'],
      ['country', 'Country is required.'],
      ['address_line1', 'Street address is required.'],
      ['city', 'City is required.'],
      ['state', 'State is required.'],
      ['pincode', 'Zip / PIN code is required.'],
      ['phone', 'Phone is required.'],
      ['email', 'Email is required.']
    ];

    required.forEach(function (pair) {
      if (!val(pair[0])) {
        setError(pair[0], pair[1]);
        ok = false;
      }
    });

    var email = val('email');
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setError('email', 'Enter a valid email address.');
      ok = false;
    }

    var phone = val('phone').replace(/\s+/g, '');
    if (phone && phone.length < 8) {
      setError('phone', 'Enter a valid phone number.');
      ok = false;
    }

    if (!isLoggedIn && email && emailOk === false) {
      setError('email', 'This email is already registered. Please log in.');
      ok = false;
    }

    return ok;
  }

  function checkEmail(email) {
    var status = document.getElementById('pp-email-status');
    if (isLoggedIn || !email) {
      emailOk = true;
      if (status) status.textContent = '';
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      emailOk = false;
      return;
    }
    if (status) status.textContent = 'Checking email…';

    fetch(checkEmailUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ email: email })
    }).then(function (res) { return res.json(); }).then(function (data) {
      if (data.available) {
        emailOk = true;
        setError('email', '');
        if (status) status.textContent = 'Email is available — we’ll create your account at checkout.';
      } else {
        emailOk = false;
        setError('email', 'This email is already registered. Please log in.');
        if (status) {
          status.innerHTML = 'This email is already registered. <a href="' + loginUrl + '">Log in</a> to continue.';
        }
      }
    }).catch(function () {
      emailOk = true;
      if (status) status.textContent = '';
    });
  }

  var emailInput = form.querySelector('[name="email"]');
  if (emailInput && !isLoggedIn) {
    emailInput.addEventListener('input', function () {
      emailOk = null;
      clearTimeout(emailTimer);
      emailTimer = setTimeout(function () { checkEmail(val('email')); }, 450);
    });
    emailInput.addEventListener('blur', function () { checkEmail(val('email')); });
  }

  function loadRazorpay() {
    return new Promise(function (resolve, reject) {
      if (window.Razorpay) return resolve(window.Razorpay);
      var s = document.createElement('script');
      s.src = 'https://checkout.razorpay.com/v1/checkout.js';
      s.onload = function () { resolve(window.Razorpay); };
      s.onerror = function () { reject(new Error('Could not load Razorpay.')); };
      document.head.appendChild(s);
    });
  }

  function setBusy(busy) {
    if (window.PPLoader) {
      if (busy) window.PPLoader.start(placeBtn, 'Processing payment…', 'PROCESSING…');
      else window.PPLoader.stop(placeBtn, true);
      return;
    }
    placeBtn.disabled = !!busy;
    placeBtn.textContent = busy ? 'PROCESSING…' : 'PLACE ORDER';
  }

  function payload() {
    return {
      first_name: val('first_name'),
      last_name: val('last_name'),
      company: val('company'),
      country: val('country'),
      address_line1: val('address_line1'),
      address_line2: val('address_line2'),
      city: val('city'),
      state: val('state'),
      pincode: val('pincode'),
      phone: val('phone'),
      email: val('email'),
      notes: val('notes')
    };
  }

  function openRazorpay(orderId, rzp) {
    return loadRazorpay().then(function (Razorpay) {
      return new Promise(function (resolve, reject) {
        var options = {
          key: rzp.key,
          amount: rzp.amount,
          currency: rzp.currency || 'INR',
          name: rzp.name || 'Vastutathastu',
          description: rzp.description || 'Order payment',
          order_id: rzp.order_id,
          prefill: rzp.prefill || {},
          theme: { color: '#0b251f' },
          handler: function (response) {
            resolve({
              order_id: orderId,
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_order_id: response.razorpay_order_id,
              razorpay_signature: response.razorpay_signature
            });
          },
          modal: {
            ondismiss: function () {
              reject(new Error('Payment cancelled.'));
            }
          }
        };
        var rzpCheckout = new Razorpay(options);
        rzpCheckout.on('payment.failed', function () {
          reject(new Error('Payment failed. Please try again.'));
        });
        rzpCheckout.open();
      });
    });
  }

  function verifyPayment(body) {
    return fetch(verifyUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify(body)
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: res.ok, data: data };
      });
    });
  }

  placeBtn.addEventListener('click', function () {
    if (!validate()) {
      alertBox('Please fix the highlighted fields.', true);
      return;
    }
    if (!isLoggedIn && emailOk === false) {
      alertBox('This email is already registered. Please log in first.', true);
      return;
    }

    setBusy(true);
    alertBox('', false);

    fetch(placeUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload())
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: res.ok, data: data };
      });
    }).then(function (res) {
      if (!res.ok || !res.data.success) {
        throw new Error((res.data && res.data.message) || 'Could not start payment.');
      }
      if (res.data.csrf) csrf = res.data.csrf;
      return openRazorpay(res.data.order_id, res.data.razorpay);
    }).then(function (payment) {
      return verifyPayment(payment);
    }).then(function (res) {
      if (!res.ok || !res.data.success) {
        throw new Error((res.data && res.data.message) || 'Payment verification failed.');
      }
      window.location.href = res.data.redirect || '/order';
    }).catch(function (err) {
      setBusy(false);
      alertBox(err.message || 'Checkout failed.', true);
    });
  });
})();
