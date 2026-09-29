/**
 * Login / Register / Forgot / Reset — JS validation + AJAX auth + loaders
 */
(function () {
  'use strict';

  function site() { return window.PP_SITE || {}; }
  function csrf() {
    return window.PP_CSRF
      ? window.PP_CSRF.current()
      : ((document.querySelector('meta[name="csrf-token"]') || {}).content || site().csrf || '');
  }
  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
  }
  function loader() { return window.PPLoader || null; }
  function startLoad(btn, message) {
    var L = loader();
    if (L) L.start(btn, message, message);
    else if (btn) btn.disabled = true;
  }
  function stopLoad(btn, force) {
    var L = loader();
    if (L) L.stop(btn, force);
    else if (btn) btn.disabled = false;
  }

  function showErr(el, msg) {
    if (!el) return;
    var wrap = el.closest('.form-floating') || el.closest('.form-group') || el.parentElement;
    var err = wrap ? wrap.querySelector('.field-error') : null;
    if (!err && wrap) {
      err = document.createElement('div');
      err.className = 'field-error text-danger mt-1';
      wrap.appendChild(err);
    }
    if (err) err.textContent = msg || '';
    el.classList.toggle('is-invalid', !!msg);
  }

  function clearFormErrors(form) {
    form.querySelectorAll('.field-error').forEach(function (e) { e.textContent = ''; });
    form.querySelectorAll('.is-invalid').forEach(function (e) { e.classList.remove('is-invalid'); });
    var alert = form.querySelector('.auth-form-alert');
    if (alert) {
      alert.hidden = true;
      alert.textContent = '';
      alert.classList.add('d-none');
    }
  }

  function setAlert(form, msg, isError) {
    var alert = form.querySelector('.auth-form-alert');
    if (!alert) return;
    alert.textContent = msg || '';
    alert.hidden = !msg;
    alert.classList.toggle('d-none', !msg);
    alert.classList.toggle('alert-danger', !!isError);
    alert.classList.toggle('alert-success', !isError && !!msg);
  }

  function postJson(url, payload, csrfRetried) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    }).then(function (response) {
      if (response.status === 419 && !csrfRetried && window.PP_CSRF) {
        return window.PP_CSRF.refresh().then(function () {
          return postJson(url, payload, true);
        });
      }
      return response.json().then(function (data) {
        return { ok: response.ok, data: data };
      }).catch(function () {
        return { ok: response.ok, data: {} };
      });
    });
  }

  function checkEmail(email) {
    if (!site().checkEmail) return Promise.resolve({ available: true });
    return postJson(site().checkEmail, { email: email }).then(function (res) {
      return res.data || { available: true };
    }).catch(function () { return { available: true }; });
  }

  function applyServerErrors(form, errors) {
    Object.keys(errors || {}).forEach(function (key) {
      var input = form.querySelector('[name="' + key + '"]');
      var messages = errors[key];
      showErr(input, Array.isArray(messages) ? messages[0] : String(messages));
    });
  }

  function bindLogin(form) {
    form.setAttribute('novalidate', 'novalidate');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormErrors(form);

      var email = form.querySelector('[name="email"]');
      var password = form.querySelector('[name="password"]');
      var ok = true;

      if (!email.value.trim()) { showErr(email, 'Email is required.'); ok = false; }
      else if (!isValidEmail(email.value.trim())) { showErr(email, 'Please enter a valid email.'); ok = false; }
      if (!password.value) { showErr(password, 'Password is required.'); ok = false; }
      else if (password.value.length < 6) { showErr(password, 'Password must be at least 6 characters.'); ok = false; }

      if (!ok) {
        setAlert(form, 'Please fix the highlighted fields.', true);
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      startLoad(btn, 'Signing in…');

      postJson(site().loginSubmit, {
        email: email.value.trim(),
        password: password.value,
        remember: !!(form.querySelector('[name="remember"]') || {}).checked
      }).then(function (res) {
        if (res.ok && res.data && res.data.success) {
          localStorage.setItem('pp_customer_authenticated', 'true');
          setAlert(form, res.data.message || 'Signed in successfully.', false);
          window.location.href = res.data.redirect || site().accountOverview || '/account/overview';
          return;
        }
        stopLoad(btn, true);
        if (res.data && res.data.errors) {
          applyServerErrors(form, res.data.errors);
          var first = Object.keys(res.data.errors)[0];
          setAlert(form, (res.data.errors[first] && res.data.errors[first][0]) || 'Login failed.', true);
        } else {
          setAlert(form, (res.data && res.data.message) || 'Login failed.', true);
        }
      }).catch(function () {
        stopLoad(btn, true);
        setAlert(form, 'Network error. Please try again.', true);
      });
    });
  }

  function bindRegister(form) {
    form.setAttribute('novalidate', 'novalidate');

    var email = form.querySelector('[name="email"]');
    if (email) {
      email.addEventListener('blur', function () {
        var value = email.value.trim();
        if (!value || !isValidEmail(value)) return;
        checkEmail(value).then(function (result) {
          if (result.available === false) {
            showErr(email, result.message || 'This email is already registered.');
          }
        });
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormErrors(form);

      var name = form.querySelector('[name="name"]');
      var firstName = form.querySelector('[name="first_name"]');
      var lastName = form.querySelector('[name="last_name"]');
      var emailEl = form.querySelector('[name="email"]');
      var password = form.querySelector('[name="password"]');
      var confirm = form.querySelector('[name="password_confirmation"]');
      var ok = true;

      var fullName = name
        ? name.value.trim()
        : ((firstName ? firstName.value.trim() : '') + ' ' + (lastName ? lastName.value.trim() : '')).trim();

      if (!fullName) {
        showErr(name || firstName, 'Name is required.');
        ok = false;
      }
      if (!emailEl.value.trim()) { showErr(emailEl, 'Email is required.'); ok = false; }
      else if (!isValidEmail(emailEl.value.trim())) { showErr(emailEl, 'Please enter a valid email.'); ok = false; }
      if (!password.value) { showErr(password, 'Password is required.'); ok = false; }
      else if (password.value.length < 6) { showErr(password, 'Password must be at least 6 characters.'); ok = false; }
      if (confirm) {
        if (!confirm.value) { showErr(confirm, 'Please confirm your password.'); ok = false; }
        else if (confirm.value !== password.value) { showErr(confirm, 'Passwords do not match.'); ok = false; }
      }

      if (!ok) {
        setAlert(form, 'Please fix the highlighted fields.', true);
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      startLoad(btn, 'Creating account…');

      var payload = {
        name: fullName,
        email: emailEl.value.trim(),
        password: password.value,
        password_confirmation: confirm ? confirm.value : password.value
      };

      checkEmail(payload.email).then(function (result) {
        if (result.available === false) {
          showErr(emailEl, result.message || 'This email is already registered.');
          setAlert(form, result.message || 'This email is already registered.', true);
          stopLoad(btn, true);
          return;
        }

        return postJson(site().signupSubmit, payload).then(function (res) {
          if (res.ok && res.data && res.data.success) {
            localStorage.setItem('pp_customer_authenticated', 'true');
            setAlert(form, res.data.message || 'Account created successfully.', false);
            window.location.href = res.data.redirect || site().accountOverview || '/account/overview';
            return;
          }
          stopLoad(btn, true);
          if (res.data && res.data.errors) {
            applyServerErrors(form, res.data.errors);
            var first = Object.keys(res.data.errors)[0];
            setAlert(form, (res.data.errors[first] && res.data.errors[first][0]) || 'Registration failed.', true);
          } else {
            setAlert(form, (res.data && res.data.message) || 'Registration failed.', true);
          }
        });
      }).catch(function () {
        stopLoad(btn, true);
        setAlert(form, 'Network error. Please try again.', true);
      });
    });
  }

  function bindForgot(form) {
    form.setAttribute('novalidate', 'novalidate');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormErrors(form);

      var email = form.querySelector('[name="email"]');
      var ok = true;
      if (!email.value.trim()) { showErr(email, 'Please enter your email address.'); ok = false; }
      else if (!isValidEmail(email.value.trim())) { showErr(email, 'Please enter a valid email address.'); ok = false; }

      if (!ok) {
        setAlert(form, 'Please enter a valid email address.', true);
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      startLoad(btn, 'Sending email…');

      var url = form.getAttribute('action') || site().forgotPassword || '/forgot-password';
      postJson(url, { email: email.value.trim() }).then(function (res) {
        stopLoad(btn, true);
        if (res.ok && res.data && res.data.success) {
          setAlert(form, res.data.message || 'Reset link sent. Please check your email.', false);
          email.value = '';
          return;
        }
        if (res.data && res.data.errors) {
          applyServerErrors(form, res.data.errors);
          var first = Object.keys(res.data.errors)[0];
          setAlert(form, (res.data.errors[first] && res.data.errors[first][0]) || res.data.message || 'Could not send reset link.', true);
        } else {
          setAlert(form, (res.data && res.data.message) || 'Could not send reset link.', true);
        }
      }).catch(function () {
        stopLoad(btn, true);
        setAlert(form, 'Network error. Please try again.', true);
      });
    });
  }

  function bindReset(form) {
    form.setAttribute('novalidate', 'novalidate');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormErrors(form);

      var email = form.querySelector('[name="email"]');
      var password = form.querySelector('[name="password"]');
      var confirm = form.querySelector('[name="password_confirmation"]');
      var token = form.querySelector('[name="token"]');
      var ok = true;

      if (!email.value.trim()) { showErr(email, 'Email is required.'); ok = false; }
      else if (!isValidEmail(email.value.trim())) { showErr(email, 'Please enter a valid email.'); ok = false; }
      if (!password.value) { showErr(password, 'Password is required.'); ok = false; }
      else if (password.value.length < 6) { showErr(password, 'Password must be at least 6 characters.'); ok = false; }
      if (!confirm.value) { showErr(confirm, 'Please confirm your password.'); ok = false; }
      else if (confirm.value !== password.value) { showErr(confirm, 'Passwords do not match.'); ok = false; }
      if (!token || !token.value) {
        setAlert(form, 'Reset token is missing. Please use the link from your email.', true);
        ok = false;
      }

      if (!ok) {
        setAlert(form, 'Please fix the highlighted fields.', true);
        return;
      }

      var btn = form.querySelector('[type="submit"]');
      startLoad(btn, 'Updating password…');

      var url = form.getAttribute('action') || site().resetPassword || '/reset-password';
      postJson(url, {
        token: token.value,
        email: email.value.trim(),
        password: password.value,
        password_confirmation: confirm.value
      }).then(function (res) {
        if (res.ok && res.data && res.data.success) {
          setAlert(form, res.data.message || 'Password updated. Redirecting to login…', false);
          window.setTimeout(function () {
            window.location.href = res.data.redirect || site().login || '/login';
          }, 900);
          return;
        }
        stopLoad(btn, true);
        if (res.data && res.data.errors) {
          applyServerErrors(form, res.data.errors);
          var first = Object.keys(res.data.errors)[0];
          setAlert(form, (res.data.errors[first] && res.data.errors[first][0]) || res.data.message || 'Could not reset password.', true);
        } else {
          setAlert(form, (res.data && res.data.message) || 'Could not reset password.', true);
        }
      }).catch(function () {
        stopLoad(btn, true);
        setAlert(form, 'Network error. Please try again.', true);
      });
    });
  }

  function init() {
    var loginForm = document.getElementById('customer-login-form');
    var registerForm = document.getElementById('customer-register-form');
    var forgotForm = document.getElementById('customer-forgot-form');
    var resetForm = document.getElementById('customer-reset-form');
    if (loginForm) bindLogin(loginForm);
    if (registerForm) bindRegister(registerForm);
    if (forgotForm) bindForgot(forgotForm);
    if (resetForm) bindReset(resetForm);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
