/**
 * Wishlist hearts (DB-backed, logged-in customers).
 * Icon-only hearts toggle: click to save, click again to remove. Saved products render filled on load.
 * Text buttons (e.g. "Add to wishlist" on the product page) keep the add-only behaviour with the page loader.
 */
(function () {
  var SELECTOR = '[data-wishlist-product], .collection-page__save, .product-design__wish, .product-recommendation-card__wish, .home-accessory-card__wish';

  function site() { return window.PP_SITE || {}; }
  function csrf() {
    return site().csrf || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  }
  // product_id => wishlist row id
  var saved = Object.assign({}, site().wishlistItems || {});

  function storeUrl() {
    return site().wishlistStore || ((site().account || '').replace(/\/account\/?$/, '') + '/account-api/wishlist');
  }

  function toast(message) {
    var el = document.getElementById('pp-wishlist-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'pp-wishlist-toast';
      el.setAttribute('role', 'status');
      el.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:99999;background:#0b251f;color:#fff;padding:12px 18px;font-size:14px;border-radius:4px;box-shadow:0 8px 24px rgba(0,0,0,.2);';
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.hidden = false;
    clearTimeout(el._t);
    el._t = setTimeout(function () { el.hidden = true; }, 2500);
  }

  function productIdOf(btn) {
    var id = btn.getAttribute('data-wishlist-product') || btn.getAttribute('data-product-id');
    if (!id) {
      var card = btn.closest('[data-product-id]');
      if (card) id = card.getAttribute('data-product-id');
    }
    return id;
  }

  function isIconButton(btn) { return !btn.textContent.trim(); }

  function paint(btn, on) {
    btn.classList.toggle('is-wishlisted', on);
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    var label = btn.getAttribute('aria-label') || '';
    var name = label.replace(/^(Add|Remove) /, '').replace(/ (to|from) wishlist$/, '');
    if (name) btn.setAttribute('aria-label', (on ? 'Remove ' : 'Add ') + name + (on ? ' from wishlist' : ' to wishlist'));
  }

  function paintAll(productId, on) {
    document.querySelectorAll(SELECTOR).forEach(function (btn) {
      if (productIdOf(btn) === String(productId) && isIconButton(btn)) paint(btn, on);
    });
  }

  function request(method, url, body) {
    return fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: body ? JSON.stringify(body) : undefined
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) { return { ok: res.ok, data: data }; });
    });
  }

  function toggle(productId, btn) {
    if (!site().authenticated) {
      sessionStorage.setItem('pp_account_return', window.location.href);
      window.location.href = (site().login || '/login') + '?account=required';
      return;
    }
    if (btn.classList.contains('is-busy')) return;

    var icon = isIconButton(btn);
    var removing = icon && !!saved[productId];

    // Icon hearts update instantly (rolled back on failure); text buttons use the page loader.
    if (icon) {
      btn.classList.add('is-busy');
      paintAll(productId, !removing);
      if (!removing) {
        btn.classList.remove('is-popping');
        void btn.offsetWidth;
        btn.classList.add('is-popping');
      }
    } else if (window.PPLoader) {
      window.PPLoader.start(btn, 'Adding to wishlist…', 'Adding…');
    } else {
      btn.disabled = true;
    }

    var call = removing
      ? request('DELETE', storeUrl().replace(/\/$/, '') + '/' + saved[productId])
      : request('POST', storeUrl(), { product_id: Number(productId) });

    call.then(function (res) {
      if (res.ok) {
        if (removing) delete saved[productId];
        else if (res.data && res.data.item) saved[productId] = res.data.item.id;
        paintAll(productId, !removing);
      } else if (icon) {
        paintAll(productId, removing);
      }
      toast((res.data && res.data.message) || (res.ok ? (removing ? 'Removed from wishlist.' : 'Added to wishlist.') : 'Could not update your wishlist.'));
    }).catch(function () {
      if (icon) paintAll(productId, removing);
      toast('Network error. Please try again.');
    }).then(function () {
      btn.classList.remove('is-busy');
      if (!icon) {
        if (window.PPLoader) window.PPLoader.stop(btn, true);
        else btn.disabled = false;
      }
    });
  }

  // Saved products show a filled heart on load (also for tiles added later by "Load more").
  function paintSaved(root) {
    if (root.matches && root.matches(SELECTOR)) root = root.parentNode || root;
    (root || document).querySelectorAll(SELECTOR).forEach(function (btn) {
      var id = productIdOf(btn);
      if (id && isIconButton(btn)) paint(btn, !!saved[id]);
    });
  }
  paintSaved(document);
  if ('MutationObserver' in window) {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        m.addedNodes.forEach(function (n) { if (n.nodeType === 1 && n.querySelectorAll) paintSaved(n); });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }

  document.addEventListener('click', function (event) {
    var btn = event.target.closest(SELECTOR);
    if (!btn) return;
    var productId = productIdOf(btn);
    if (!productId) return;
    event.preventDefault();
    toggle(String(productId), btn);
  });
})();
