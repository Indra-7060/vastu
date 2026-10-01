(function () {
  'use strict';

  const heartIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>';
  const closeIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>';
  let lastTrigger = null;

  function site() {
    return window.PP_SITE || {};
  }

  function csrfToken() {
    return window.PP_CSRF
      ? window.PP_CSRF.current()
      : ((document.querySelector('meta[name="csrf-token"]') || {}).content || site().csrf || '');
  }

  function accountOverviewUrl() {
    return site().accountOverview || ((site().account || '/account').replace(/\/?$/, '') + '/overview');
  }

  function userInitials() {
    var user = site().user || {};
    var name = String(user.name || '').trim();
    if (!name) return 'U';
    var parts = name.split(/\s+/);
    var initials = (parts[0] || 'U').charAt(0);
    if (parts[1]) initials += parts[1].charAt(0);
    return initials.toUpperCase();
  }

  function loggedInAccountIconHtml() {
    return '<span class="site-account-avatar notranslate" translate="no" aria-hidden="true">' + userInitials() + '</span>';
  }

  function applyLoggedInAccountIcons() {
    if (!site().authenticated) return;
    document.querySelectorAll('.signin-cart-btn').forEach(function (btn) {
      btn.classList.add('is-logged-in');
      btn.setAttribute('data-account-logged-in', '1');
      btn.setAttribute('aria-label', 'My account');
      // With unread notifications the header icon keeps pointing at the Notifications page.
      if (site().accountOverview && !btn.querySelector('.vt-notify-dot')) btn.setAttribute('href', site().accountOverview);
      if (!btn.querySelector('.site-account-avatar')) {
        // Vastutathastu header: swap only the icon for the initials badge and keep the "My account" label.
        var icon = btn.classList.contains('vt-utility__link') ? btn.querySelector('.vt-icon, img') : null;
        if (icon) {
          icon.insertAdjacentHTML('beforebegin', loggedInAccountIconHtml());
          icon.remove();
        } else {
          var dot = btn.querySelector('.vt-notify-dot');
          btn.innerHTML = loggedInAccountIconHtml();
          if (dot) btn.appendChild(dot);
        }
      }
    });
  }

  function addHeaderButtons() {
    applyLoggedInAccountIcons();

    if (document.querySelector('.site-wishlist-entry')) return;

    const accountButton = document.querySelector('.mobile_menu_widget_icons .signin-cart-btn, .cart-btn .signin-cart-btn, .signin-cart-btn');
    if (!accountButton) return;

    const item = accountButton.closest('li');
    if (!item || !item.parentElement) return;

    const wishlistItem = document.createElement('li');
    wishlistItem.className = 'position-relative ms-2 site-wishlist-entry';
    const favoritesUrl = site().account
      ? String(site().account).replace(/\/?$/, '') + '/wishlist'
      : '#';
    wishlistItem.innerHTML = '<a href="' + favoritesUrl + '" class="wishlist-panel-btn" aria-label="Open wishlist">' + heartIcon + '</a>';
    const cartItem = item.parentElement.querySelector(':scope > li .cart-filter-btn');
    item.parentElement.insertBefore(wishlistItem, cartItem ? cartItem.closest('li') : item.nextSibling);

    if (site().authenticated && site().accountOverview) {
      accountButton.setAttribute('href', site().accountOverview);
      accountButton.setAttribute('aria-label', 'My account');
    }
  }

  function drawerMarkup() {
    const cartUrl = site().cart || '/cart';
    const checkoutUrl = site().checkout || '/checkout';
    return '<div class="site-drawer-overlay" aria-hidden="true"></div>' +
      '<aside id="siteWishlistDrawer" class="site-drawer site-wishlist" aria-hidden="true" aria-labelledby="wishlistTitle">' +
        '<div class="site-drawer-head"><h2 id="wishlistTitle">Wishlist</h2><button class="site-drawer-close" type="button" aria-label="Close wishlist">' + closeIcon + '</button></div>' +
        '<div class="site-wishlist-list">' + '' + '</div>' +
        '<div class="site-wishlist-footer"><div class="site-wishlist-total"><span><b class="site-item-count">0</b> Items</span><strong></strong></div><a class="site-btn site-btn-outline" href="' + cartUrl + '">Add all to cart</a><a class="site-btn" href="' + checkoutUrl + '">Checkout</a></div>' +
      '</aside>' +
      '<aside id="siteAccountDrawer" class="site-drawer site-account" aria-hidden="true" aria-labelledby="accountTitle">' +
        '<div class="site-drawer-head"><h2 id="accountTitle">My account</h2><button class="site-drawer-close" type="button" aria-label="Close account panel">' + closeIcon + '</button></div>' +
        '<div class="site-account-inner"><div class="site-account-tabs" role="tablist"><button class="is-active" data-account-tab="login" type="button">Login</button><button data-account-tab="register" type="button">Register</button></div>' +
          loginForm() + registerForm() + '<p class="site-form-message" aria-live="polite"></p>' +
        '</div>' +
      '</aside>';
  }

  function wishlistRow(image) {
    const src = (site().assetBase ? String(site().assetBase).replace(/\/?$/, '/') : '') + image;
    return '<article class="site-wishlist-row"><img src="' + src + '" alt="Cropped Varsity"><div class="site-wishlist-info"><h3>CROPPED VARSITY</h3><p class="site-price">₹ 3,198.00</p><p>Colour: White</p><p>Size: S</p><div class="site-qty"><button type="button" data-qty="minus" aria-label="Decrease quantity">−</button><span>1</span><button type="button" data-qty="plus" aria-label="Increase quantity">+</button></div><button class="site-row-cart" type="button">Add to cart</button><button class="site-remove" type="button">Remove</button></div></article>';
  }

  function loginForm() {
    return '<form class="site-account-form is-active" data-account-panel="login" novalidate>' +
      '<label>Email address<input type="email" name="email" required autocomplete="email" placeholder="Email address"><span class="site-field-error" data-error-for="email"></span></label>' +
      '<label>Password<span class="site-password"><input type="password" name="password" required minlength="6" autocomplete="current-password" placeholder="Password"><button type="button" class="site-show-password">Show</button></span><span class="site-field-error" data-error-for="password"></span></label>' +
      '<a class="site-forgot" href="' + (site().forgotPassword || '/forgot-password') + '">Forgot password?</a>' +
      '<button class="site-btn" type="submit">Sign in</button></form>';
  }

  function registerForm() {
    return '<form class="site-account-form" data-account-panel="register" novalidate>' +
      '<label>Full name<input type="text" name="name" required autocomplete="name" placeholder="Full name"><span class="site-field-error" data-error-for="name"></span></label>' +
      '<label>Email address<input type="email" name="email" required autocomplete="email" placeholder="Email address"><span class="site-field-error" data-error-for="email"></span></label>' +
      '<label>Password<span class="site-password"><input type="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Password"><button type="button" class="site-show-password">Show</button></span><span class="site-field-error" data-error-for="password"></span></label>' +
      '<label>Confirm password<span class="site-password"><input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password" placeholder="Confirm password"><button type="button" class="site-show-password">Show</button></span><span class="site-field-error" data-error-for="password_confirmation"></span></label>' +
      '<button class="site-btn" type="submit">Create account</button></form>';
  }

  function openDrawer(drawer, trigger) {
    closeDrawers(false);
    lastTrigger = trigger || document.activeElement;
    document.body.classList.add('site-drawer-open');
    document.querySelector('.site-drawer-overlay').classList.add('is-open');
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    window.setTimeout(function () { drawer.querySelector('button, input, a').focus(); }, 120);
  }

  function closeDrawers(restoreFocus) {
    document.body.classList.remove('site-drawer-open');
    const overlay = document.querySelector('.site-drawer-overlay');
    if (overlay) overlay.classList.remove('is-open');
    document.querySelectorAll('.site-drawer').forEach(function (drawer) {
      drawer.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
    });
    if (restoreFocus !== false && lastTrigger) lastTrigger.focus();
  }

  function updateCount() {
    const count = document.querySelectorAll('.site-wishlist-row').length;
    document.querySelectorAll('.site-wishlist-count, .site-item-count').forEach(function (el) { el.textContent = count; });
  }

  function clearFieldErrors(form) {
    form.querySelectorAll('.site-field-error').forEach(function (el) { el.textContent = ''; });
    form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
  }

  function setFieldError(form, name, message) {
    const input = form.querySelector('[name="' + name + '"]');
    const err = form.querySelector('[data-error-for="' + name + '"]');
    if (input) input.classList.add('is-invalid');
    if (err) err.textContent = message || '';
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
  }

  function validateLogin(form) {
    clearFieldErrors(form);
    var ok = true;
    var email = (form.email.value || '').trim();
    var password = form.password.value || '';

    if (!email) {
      setFieldError(form, 'email', 'Email is required.');
      ok = false;
    } else if (!isValidEmail(email)) {
      setFieldError(form, 'email', 'Please enter a valid email.');
      ok = false;
    }

    if (!password) {
      setFieldError(form, 'password', 'Password is required.');
      ok = false;
    } else if (password.length < 6) {
      setFieldError(form, 'password', 'Password must be at least 6 characters.');
      ok = false;
    }

    return ok;
  }

  function validateRegister(form) {
    clearFieldErrors(form);
    var ok = true;
    var name = (form.name.value || '').trim();
    var email = (form.email.value || '').trim();
    var password = form.password.value || '';
    var confirm = form.password_confirmation.value || '';

    if (!name) {
      setFieldError(form, 'name', 'Full name is required.');
      ok = false;
    }

    if (!email) {
      setFieldError(form, 'email', 'Email is required.');
      ok = false;
    } else if (!isValidEmail(email)) {
      setFieldError(form, 'email', 'Please enter a valid email.');
      ok = false;
    }

    if (!password) {
      setFieldError(form, 'password', 'Password is required.');
      ok = false;
    } else if (password.length < 6) {
      setFieldError(form, 'password', 'Password must be at least 6 characters.');
      ok = false;
    }

    if (!confirm) {
      setFieldError(form, 'password_confirmation', 'Please confirm your password.');
      ok = false;
    } else if (password !== confirm) {
      setFieldError(form, 'password_confirmation', 'Passwords do not match.');
      ok = false;
    }

    return ok;
  }

  function setMessage(text, isError) {
    var message = document.querySelector('.site-form-message');
    if (!message) return;
    message.textContent = text || '';
    message.classList.toggle('is-error', !!isError);
  }

  function postJson(url, payload, csrfRetried) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
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
        return { ok: response.ok, status: response.status, data: data };
      }).catch(function () {
        return { ok: response.ok, status: response.status, data: {} };
      });
    });
  }

  function checkDuplicateEmail(email) {
    var url = site().checkEmail;
    if (!url) return Promise.resolve({ available: true });
    return postJson(url, { email: email }).then(function (res) {
      return res.data || { available: true };
    }).catch(function () {
      return { available: true };
    });
  }

  function handleAuthSuccess(data) {
    localStorage.setItem('pp_customer_authenticated', 'true');
    setMessage(data.message || 'Success.');
    var redirect = data.redirect || sessionStorage.getItem('pp_account_return') || accountOverviewUrl();
    sessionStorage.removeItem('pp_account_return');
    window.setTimeout(function () { window.location.href = redirect; }, 350);
  }

  function applyServerErrors(form, errors) {
    Object.keys(errors || {}).forEach(function (key) {
      var messages = errors[key];
      setFieldError(form, key, Array.isArray(messages) ? messages[0] : String(messages));
    });
  }

  function bindEvents() {
    document.addEventListener('click', function (event) {
      const account = event.target.closest('.signin-cart-btn');
      const wishlist = event.target.closest('.wishlist-panel-btn');
      if (account) {
        if (site().authenticated) {
          event.preventDefault();
          event.stopImmediatePropagation();
          window.location.href = site().accountOverview || accountOverviewUrl();
          return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        openDrawer(document.querySelector('#siteAccountDrawer'), account);
        return;
      }
      if (wishlist) {
        if (!site().authenticated) {
          event.preventDefault();
          event.stopImmediatePropagation();
          sessionStorage.setItem('pp_account_return', accountOverviewUrl().replace(/overview$/, 'wishlist'));
          openDrawer(document.querySelector('#siteAccountDrawer'), wishlist);
          return;
        }
        return;
      }
      if (event.target.closest('.site-drawer-close') || event.target.classList.contains('site-drawer-overlay')) closeDrawers();
      const tab = event.target.closest('[data-account-tab]');
      if (tab) {
        document.querySelectorAll('[data-account-tab]').forEach(function (button) { button.classList.toggle('is-active', button === tab); });
        document.querySelectorAll('[data-account-panel]').forEach(function (panel) { panel.classList.toggle('is-active', panel.dataset.accountPanel === tab.dataset.accountTab); });
        setMessage('');
      }
      const show = event.target.closest('.site-show-password');
      if (show) {
        const input = show.parentElement.querySelector('input');
        input.type = input.type === 'password' ? 'text' : 'password';
        show.textContent = input.type === 'password' ? 'Show' : 'Hide';
      }
      const qty = event.target.closest('[data-qty]');
      if (qty) {
        const value = qty.parentElement.querySelector('span');
        const next = Number(value.textContent) + (qty.dataset.qty === 'plus' ? 1 : -1);
        value.textContent = Math.max(1, next);
      }
      const remove = event.target.closest('.site-remove');
      if (remove) { remove.closest('.site-wishlist-row').remove(); updateCount(); }
      const cart = event.target.closest('.site-row-cart');
      if (cart) { cart.textContent = 'Added'; cart.classList.add('is-added'); }
    }, true);

    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeDrawers(); });

    document.querySelectorAll('.site-account-form').forEach(function (form) {
      var emailInput = form.querySelector('input[name="email"]');
      if (emailInput && form.dataset.accountPanel === 'register') {
        var emailTimer = null;
        emailInput.addEventListener('blur', function () {
          var email = (emailInput.value || '').trim();
          if (!email || !isValidEmail(email)) return;
          clearTimeout(emailTimer);
          emailTimer = setTimeout(function () {
            checkDuplicateEmail(email).then(function (result) {
              if (result.available === false) {
                setFieldError(form, 'email', result.message || 'This email is already registered.');
              }
            });
          }, 200);
        });
      }

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        setMessage('');

        var isRegister = form.dataset.accountPanel === 'register';
        var valid = isRegister ? validateRegister(form) : validateLogin(form);
        if (!valid) {
          setMessage('Please fix the highlighted fields.', true);
          var firstInvalid = form.querySelector('.is-invalid');
          if (firstInvalid) firstInvalid.focus();
          return;
        }

        var submitBtn = form.querySelector('[type="submit"]');
        var loadMsg = isRegister ? 'Creating account…' : 'Signing in…';
        if (window.PPLoader) {
          window.PPLoader.start(submitBtn, loadMsg, loadMsg);
        } else if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.dataset.originalText = submitBtn.textContent;
          submitBtn.textContent = loadMsg;
        }

        function finish() {
          if (window.PPLoader) {
            window.PPLoader.stop(submitBtn, true);
          } else if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || (isRegister ? 'Create account' : 'Sign in');
          }
        }

        var payload = isRegister
          ? {
              name: form.name.value.trim(),
              email: form.email.value.trim(),
              password: form.password.value,
              password_confirmation: form.password_confirmation.value
            }
          : {
              email: form.email.value.trim(),
              password: form.password.value,
              remember: true
            };

        var run = function () {
          var url = isRegister ? site().signupSubmit : site().loginSubmit;
          if (!url) {
            setMessage('Auth routes are not configured.', true);
            finish();
            return;
          }

          postJson(url, payload).then(function (res) {
            if (res.ok && res.data && res.data.success) {
              handleAuthSuccess(res.data);
              return;
            }

            if (res.data && res.data.errors) {
              applyServerErrors(form, res.data.errors);
              var first = Object.keys(res.data.errors)[0];
              setMessage((res.data.errors[first] && res.data.errors[first][0]) || 'Please check your details.', true);
            } else if (res.data && res.data.message) {
              setMessage(res.data.message, true);
            } else {
              setMessage('Something went wrong. Please try again.', true);
            }
            finish();
          }).catch(function () {
            setMessage('Network error. Please try again.', true);
            finish();
          });
        };

        if (isRegister) {
          checkDuplicateEmail(payload.email).then(function (result) {
            if (result.available === false) {
              setFieldError(form, 'email', result.message || 'This email is already registered.');
              setMessage(result.message || 'This email is already registered.', true);
              finish();
              return;
            }
            run();
          });
        } else {
          run();
        }
      });
    });
  }

  function initStickyHeader() {
    const headers = Array.from(document.querySelectorAll('#page > .header')).filter(function (el) { return el.offsetParent !== null; });
    if (!headers.length) return;
    let ticking = false;
    function update() {
      headers.forEach(function (header) { header.classList.toggle('site-header-fixed', window.scrollY > 160); });
      ticking = false;
    }
    window.addEventListener('scroll', function () { if (!ticking) { requestAnimationFrame(update); ticking = true; } }, { passive: true });
    update();
  }

  function init() {
    addHeaderButtons();
    document.body.insertAdjacentHTML('beforeend', drawerMarkup());
    bindEvents();
    initStickyHeader();
    const params = new URLSearchParams(window.location.search);
    if (params.get('account') === 'required' && !site().authenticated) {
      const accountTrigger = Array.from(document.querySelectorAll('.signin-cart-btn')).find(function (button) {
        return button.offsetParent !== null;
      });
      openDrawer(document.querySelector('#siteAccountDrawer'), accountTrigger || null);
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
