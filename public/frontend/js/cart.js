/**
 * Cart: add / buy now / update qty / remove + header count + live minicart
 */
(function () {
  'use strict';

  function site() {
    return window.PP_SITE || {};
  }

  function csrf() {
    return window.PP_CSRF
      ? window.PP_CSRF.current()
      : ((document.querySelector('meta[name="csrf-token"]') || {}).content || site().csrf || '');
  }

  function toast(message) {
    var el = document.getElementById('pp-cart-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'pp-cart-toast';
      el.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:99999;background:#0b251f;color:#fff;padding:12px 18px;font-size:14px;border-radius:4px;box-shadow:0 8px 24px rgba(0,0,0,.2);';
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.hidden = false;
    clearTimeout(el._t);
    el._t = setTimeout(function () { el.hidden = true; }, 2500);
  }

  function ensureBadges() {
    document.querySelectorAll('.cart-filter-btn, .site-cart-link, a[aria-label="Shopping bag"], a[aria-label="Shopping cart"]').forEach(function (btn) {
      if (btn.querySelector('[data-cart-count]')) return;
      var badge = document.createElement('span');
      badge.className = 'site-cart-count';
      badge.setAttribute('data-cart-count', '');
      badge.hidden = true;
      badge.textContent = '0';
      btn.classList.add('site-cart-link');
      btn.style.position = btn.style.position || 'relative';
      btn.appendChild(badge);
    });
  }

  function updateBadges(count) {
    count = Number(count || 0);
    if (window.PP_SITE) window.PP_SITE.cartCount = count;
    ensureBadges();
    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
      el.textContent = String(count);
      el.hidden = count < 1;
    });
    document.querySelectorAll('[data-cart-items-label]').forEach(function (el) {
      el.textContent = 'Items (' + count + ')';
    });
  }

  function request(method, url, body, csrfRetried) {
    var opts = {
      method: method,
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      cache: 'no-store' // the bag changes often; never reuse a cached answer
    };
    if (body !== undefined) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(body);
    }
    return fetch(url, opts).then(function (res) {
      if (res.status === 419 && !csrfRetried && window.PP_CSRF) {
        return window.PP_CSRF.refresh().then(function () {
          return request(method, url, body, true);
        });
      }
      return res.json().then(function (data) {
        return { ok: res.ok, status: res.status, data: data };
      }).catch(function () {
        return { ok: res.ok, status: res.status, data: {} };
      });
    });
  }

  function cleanVariant(value) {
    var v = String(value == null ? '' : value).trim();
    if (!v || v === '?' || v.toLowerCase() === 'select' || v.toLowerCase() === 'select size') return null;
    return v;
  }

  function productRoot(el) {
    if (!el) return document.querySelector('.product-design') || document;
    return el.closest('.product-design')
      || el.closest('.card-product, .collection-page__card, .product-recommendation-card')
      || document.querySelector('.product-design')
      || document;
  }

  function productContext(root) {
    root = root || document.querySelector('.product-design') || document;
    var qtyEl = root.querySelector('[data-product-qty-value]');
    var colourEl = root.querySelector('[data-selected-colour]');
    var sizeEl = root.querySelector('[data-selected-size]');
    var activePackage = root.querySelector('[data-accessory-package].is-active, [data-accessory-package][aria-pressed="true"]');
    var qty = qtyEl ? parseInt(qtyEl.textContent, 10) : 1;
    if (!qty || qty < 1) qty = 1;

    var color = cleanVariant(colourEl ? colourEl.textContent : '');
    var size = cleanVariant(sizeEl ? sizeEl.textContent : '');

    if (!color) {
      var activeColour = root.querySelector('[data-colour].is-active, [data-colour][aria-pressed="true"]');
      if (activeColour) color = cleanVariant(activeColour.getAttribute('data-colour'));
    }
    if (!size) {
      var activeSize = root.querySelector('[data-size].is-active, [data-size][aria-pressed="true"]');
      if (activeSize) size = cleanVariant(activeSize.getAttribute('data-size'));
    }

    if (!color) color = cleanVariant(root.getAttribute('data-default-color'));
    if (!size) size = cleanVariant(root.getAttribute('data-default-size'));

    var packageKey = null;
    if (activePackage) {
      packageKey = cleanVariant(activePackage.getAttribute('data-accessory-package'));
    }
    if (!packageKey) {
      var cartBtn = root.querySelector('[data-add-to-cart], .product-design__cart, [data-buy-now], .product-design__buy');
      if (cartBtn) packageKey = cleanVariant(cartBtn.getAttribute('data-default-package'));
    }
    if (!packageKey) packageKey = cleanVariant(root.getAttribute('data-default-package'));

    return {
      quantity: qty,
      color: color,
      size: size,
      package_key: packageKey
    };
  }

  function getProductId(el, root) {
    if (!el && !root) return null;
    if (el) {
      if (el.getAttribute && el.getAttribute('data-product-id')) return el.getAttribute('data-product-id');
      var nearest = el.closest && el.closest('[data-product-id]');
      if (nearest) return nearest.getAttribute('data-product-id');
    }
    if (root && root.getAttribute && root.getAttribute('data-product-id')) {
      return root.getAttribute('data-product-id');
    }
    if (root && root.querySelector) {
      var nested = root.querySelector('[data-product-id]');
      if (nested) return nested.getAttribute('data-product-id');
    }
    return null;
  }

  function setBusy(button, busy, label) {
    if (!button) return;
    if (window.PPLoader) {
      if (busy) window.PPLoader.start(button, label || 'Updating cart…', label || 'Please wait…');
      else window.PPLoader.stop(button, true);
      return;
    }
    if (busy) {
      if (!button.dataset.originalText) {
        button.dataset.originalText = (button.textContent || '').trim();
      }
      button.classList.add('is-busy');
      button.setAttribute('aria-busy', 'true');
      if ('disabled' in button) button.disabled = true;
      if (label) button.textContent = label;
      return;
    }
    button.classList.remove('is-busy');
    button.removeAttribute('aria-busy');
    if ('disabled' in button) button.disabled = false;
    if (label != null) button.textContent = label;
    else if (button.dataset.originalText) button.textContent = button.dataset.originalText;
  }

  function escapeHtml(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function cartItemsList(cart) {
    var raw = cart && cart.items != null ? cart.items : [];
    if (Array.isArray(raw)) return raw;
    if (raw && typeof raw === 'object') return Object.keys(raw).map(function (k) { return raw[k]; });
    return [];
  }

  function mountMinicartToBody() {
    var drawer = document.querySelector('[data-minicart-root]');
    if (drawer && drawer.parentElement !== document.body) {
      document.body.appendChild(drawer);
    }
  }

  function openBagDrawer() {
    mountMinicartToBody();
    var drawer = document.querySelector('[data-minicart-root], .minicart-16');
    if (!drawer) return;
    drawer.classList.add('show');
    drawer.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('pp-minicart-open');
    document.body.classList.add('pp-minicart-open');
  }

  function closeBagDrawer() {
    document.querySelectorAll('[data-minicart-root], .minicart-16').forEach(function (drawer) {
      drawer.classList.remove('show');
      drawer.setAttribute('aria-hidden', 'true');
    });
    document.documentElement.classList.remove('pp-minicart-open');
    document.body.classList.remove('pp-minicart-open');
  }

  function sameVariant(row, color, size) {
    var v = rowVariant(row);
    var c = color ? String(color).trim() : null;
    var s = size ? String(size).trim() : null;
    var p = arguments.length > 3 && arguments[3] ? String(arguments[3]).trim() : null;
    return (v.color || null) === (c || null) && (v.size || null) === (s || null) && (v.package_key || null) === (p || null);
  }

  function rowsForLine(productId, color, size, packageKey) {
    return Array.from(document.querySelectorAll('[data-cart-row][data-product-id="' + productId + '"]'))
      .filter(function (row) { return sameVariant(row, color, size, packageKey); });
  }

  function updateCartTotals(cart) {
    if (!cart) return;
    document.querySelectorAll('[data-cart-subtotal]').forEach(function (el) {
      el.textContent = cart.subtotal_formatted;
    });
    document.querySelectorAll('[data-cart-shipping]').forEach(function (el) {
      el.textContent = (cart.shipping && cart.shipping.amount_formatted) || 'Free';
    });
    document.querySelectorAll('[data-cart-total]').forEach(function (el) {
      el.textContent = cart.total_formatted || cart.subtotal_formatted;
    });
    document.querySelectorAll('[data-cart-shipping-message]').forEach(function (el) {
      if (cart.shipping && cart.shipping.is_free) {
        el.textContent = 'Free shipping applied.';
      } else if (cart.shipping) {
        el.textContent = 'Free shipping on orders of ₹ ' + Number(cart.shipping.free_shipping_threshold).toLocaleString('en-IN') + ' or more.';
      }
    });
    updateCouponUi(cart);
  }

  function updateCouponUi(cart) {
    var discount = (cart && cart.discount) || {};
    var hasCode = !!discount.code;
    var hasAmount = Number(discount.amount || 0) > 0;
    var showRow = hasCode && (hasAmount || !!discount.free_shipping);

    document.querySelectorAll('[data-cart-discount-row]').forEach(function (row) {
      if (showRow) row.removeAttribute('hidden');
      else row.setAttribute('hidden', 'hidden');
    });
    document.querySelectorAll('[data-cart-discount]').forEach(function (el) {
      el.textContent = '-' + (discount.amount_formatted || '₹ 0.00');
    });
    document.querySelectorAll('[data-cart-discount-label]').forEach(function (el) {
      el.textContent = hasCode ? ('Discount (' + discount.code + ')') : 'Discount';
    });

    document.querySelectorAll('[data-coupon-box]').forEach(function (box) {
      var input = box.querySelector('[data-coupon-input]');
      var applyBtn = box.querySelector('[data-coupon-apply]');
      var removeBtn = box.querySelector('[data-coupon-remove]');
      var message = box.querySelector('[data-coupon-message]');
      if (input) {
        input.value = discount.code || '';
        if (hasCode) input.setAttribute('readonly', 'readonly');
        else input.removeAttribute('readonly');
      }
      if (applyBtn) {
        if (hasCode) applyBtn.setAttribute('hidden', 'hidden');
        else applyBtn.removeAttribute('hidden');
      }
      if (removeBtn) {
        if (hasCode) removeBtn.removeAttribute('hidden');
        else removeBtn.setAttribute('hidden', 'hidden');
      }
      if (message) {
        if (discount.message) {
          message.textContent = discount.message;
          message.removeAttribute('hidden');
          message.classList.toggle('text-danger', !hasCode);
          message.classList.toggle('text-success', hasCode);
        } else if (hasCode) {
          message.textContent = 'Coupon applied.';
          message.removeAttribute('hidden');
          message.classList.remove('text-danger');
          message.classList.add('text-success');
        } else {
          message.textContent = '';
          message.setAttribute('hidden', 'hidden');
        }
      }
    });
  }

  function applyCoupon(code, button) {
    var url = site().couponApply || '/cart-api/coupon';
    setBusy(button, true, 'Applying…');
    return request('POST', url, { code: code }).then(function (res) {
      setBusy(button, false, 'Apply');
      if (res.ok && res.data && res.data.cart) {
        updateCartTotals(res.data.cart);
        updateBadges(res.data.cart.count);
        toast(res.data.message || 'Coupon applied.');
      } else {
        if (res.data && res.data.cart) updateCartTotals(res.data.cart);
        toast((res.data && res.data.message) || 'Could not apply coupon.');
        document.querySelectorAll('[data-coupon-message]').forEach(function (el) {
          el.textContent = (res.data && res.data.message) || 'Could not apply coupon.';
          el.removeAttribute('hidden');
          el.classList.add('text-danger');
          el.classList.remove('text-success');
        });
      }
      return res;
    });
  }

  function removeCoupon(button) {
    var url = site().couponRemove || '/cart-api/coupon';
    setBusy(button, true, 'Removing…');
    return request('DELETE', url).then(function (res) {
      setBusy(button, false, 'Remove');
      if (res.ok && res.data && res.data.cart) {
        updateCartTotals(res.data.cart);
        updateBadges(res.data.cart.count);
        toast(res.data.message || 'Coupon removed.');
      } else {
        toast((res.data && res.data.message) || 'Could not remove coupon.');
      }
      return res;
    });
  }

  function refreshCartRow(row, item, cart) {
    var productId = row ? row.getAttribute('data-product-id') : (item && item.product_id);
    var variant = item
      ? { color: item.color || null, size: item.size || null, package_key: item.package_key || null }
      : rowVariant(row);
    var rows = productId
      ? rowsForLine(productId, variant.color, variant.size, variant.package_key)
      : (row ? [row] : []);

    if (!item) {
      rows.forEach(function (r) { r.remove(); });
      if (cart) {
        updateBadges(cart.count);
        updateCartTotals(cart);
        if (cart.count < 1 && row && row.closest('main')) window.location.reload();
      }
      return;
    }

    rows.forEach(function (r) {
      r.setAttribute('data-color', item.color || '');
      r.setAttribute('data-size', item.size || '');
      r.setAttribute('data-package-key', item.package_key || '');
      var input = r.querySelector('[data-cart-qty-input]');
      if (input) input.value = item.quantity;
      r.querySelectorAll('[data-cart-line-total]').forEach(function (el) {
        el.textContent = item.line_total_formatted;
      });
    });

    if (cart) {
      updateCartTotals(cart);
      updateBadges(cart.count);
    }
  }

  function addToCart(productId, options, button) {
    options = options || {};
    var url = site().cartStore || '/cart-api';
    var defaultLabel = (button && button.dataset.originalText)
      || (button && (button.textContent || '').trim())
      || 'ADD TO CART';
    setBusy(button, true, 'ADDING…');

    return request('POST', url, {
      product_id: Number(productId),
      quantity: options.quantity || 1,
      color: options.color || null,
      size: options.size || null,
      package_key: options.package_key || null
    }).then(function (res) {
      if (res.ok) {
        setBusy(button, false, 'ADDED');
        if (button) {
          button.classList.add('is-added');
          setTimeout(function () {
            button.classList.remove('is-added');
            setBusy(button, false, defaultLabel);
          }, 1600);
        }
        toast((res.data && res.data.message) || 'Added to cart.');
        updateBadges(res.data.count);
        if (res.data.cart) renderMinicart(res.data.cart);
        else loadCart();
        if (options.openBag !== false) {
          openBagDrawer();
        }
      } else {
        setBusy(button, false, defaultLabel);
        toast((res.data && res.data.message) || 'Could not add to cart.');
        // Still show the bag (e.g. the item is already in it at the maximum quantity).
        if (options.openBag !== false && res.status === 422) {
          loadCart().then(function () { openBagDrawer(); });
        }
      }
      // Let the page react (the product page's bottom bar shows the result and the bag quantity).
      document.dispatchEvent(new CustomEvent('pp:cart-add', {
        detail: { ok: !!res.ok, productId: Number(productId), cart: res.data && res.data.cart, message: res.data && res.data.message }
      }));
      return res;
    }).catch(function () {
      setBusy(button, false, defaultLabel);
      toast('Network error. Please try again.');
      document.dispatchEvent(new CustomEvent('pp:cart-add', { detail: { ok: false, productId: Number(productId) } }));
      return { ok: false };
    });
  }

  function buyNow(productId, options, button) {
    options = options || {};
    var url = site().cartBuyNow || '/cart-api/buy-now';
    var defaultLabel = (button && (button.textContent || '').trim()) || 'BUY NOW';
    setBusy(button, true, 'PLEASE WAIT…');
    return request('POST', url, {
      product_id: Number(productId),
      quantity: options.quantity || 1,
      color: options.color || null,
      size: options.size || null,
      package_key: options.package_key || null
    }).then(function (res) {
      if (res.ok) {
        updateBadges(res.data.count);
        window.location.href = (res.data && res.data.redirect) || site().checkout || '/checkout';
        return res;
      }
      setBusy(button, false, defaultLabel);
      toast((res.data && res.data.message) || 'Could not continue to checkout.');
      return res;
    }).catch(function () {
      setBusy(button, false, defaultLabel);
      toast('Network error. Please try again.');
      return { ok: false };
    });
  }

  function rowVariant(row) {
    if (!row) return { color: null, size: null, package_key: null };
    var color = row.getAttribute('data-color');
    var size = row.getAttribute('data-size');
    var packageKey = row.getAttribute('data-package-key');
    return {
      color: color ? String(color).trim() : null,
      size: size ? String(size).trim() : null,
      package_key: packageKey ? String(packageKey).trim() : null
    };
  }

  function updateQty(productId, quantity, row) {
    var base = (site().cartUpdateBase || '/cart-api').replace(/\/$/, '');
    var variant = rowVariant(row);
    return request('PUT', base + '/' + productId, {
      quantity: quantity,
      color: variant.color,
      size: variant.size,
      package_key: variant.package_key
    }).then(function (res) {
      if (!res.ok) {
        toast((res.data && res.data.message) || 'Could not update cart.');
        return res;
      }
      refreshCartRow(row, res.data.item, res.data.cart);
      if (res.data.cart) renderMinicart(res.data.cart);
      toast((res.data && res.data.message) || (quantity < 1 ? 'Removed from cart.' : 'Cart updated.'));
      return res;
    });
  }

  function confirmRemove(title) {
    return new Promise(function (resolve) {
      var existing = document.getElementById('pp-cart-confirm');
      if (existing) existing.remove();

      var overlay = document.createElement('div');
      overlay.id = 'pp-cart-confirm';
      overlay.className = 'pp-confirm';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.setAttribute('aria-labelledby', 'pp-cart-confirm-title');
      overlay.innerHTML =
        '<div class="pp-confirm__dialog">' +
          '<button type="button" class="pp-confirm__close" data-pp-confirm="cancel" aria-label="Close">&times;</button>' +
          '<p class="pp-confirm__eyebrow">Shopping bag</p>' +
          '<h3 id="pp-cart-confirm-title" class="pp-confirm__title">Remove this item?</h3>' +
          '<p class="pp-confirm__text"></p>' +
          '<div class="pp-confirm__actions">' +
            '<button type="button" class="pp-confirm__btn pp-confirm__btn--ghost" data-pp-confirm="cancel">Keep item</button>' +
            '<button type="button" class="pp-confirm__btn pp-confirm__btn--solid" data-pp-confirm="ok">Remove</button>' +
          '</div>' +
        '</div>';

      var textEl = overlay.querySelector('.pp-confirm__text');
      if (textEl) {
        textEl.textContent = title
          ? ('“' + title + '” will be removed from your bag.')
          : 'This item will be removed from your bag.';
      }

      function finish(ok) {
        document.removeEventListener('keydown', onKey);
        overlay.remove();
        resolve(ok);
      }

      function onKey(e) {
        if (e.key === 'Escape') finish(false);
      }

      overlay.addEventListener('click', function (e) {
        var action = e.target.closest('[data-pp-confirm]');
        if (action) {
          finish(action.getAttribute('data-pp-confirm') === 'ok');
          return;
        }
        if (e.target === overlay) finish(false);
      });

      document.addEventListener('keydown', onKey);
      document.body.appendChild(overlay);
      requestAnimationFrame(function () {
        overlay.classList.add('is-open');
        var okBtn = overlay.querySelector('[data-pp-confirm="ok"]');
        if (okBtn) okBtn.focus();
      });
    });
  }

  function removeItem(productId, row) {
    var base = (site().cartUpdateBase || '/cart-api').replace(/\/$/, '');
    var variant = rowVariant(row);
    return request('DELETE', base + '/' + productId, {
      color: variant.color,
      size: variant.size,
      package_key: variant.package_key
    }).then(function (res) {
      if (!res.ok) {
        toast((res.data && res.data.message) || 'Could not remove item.');
        return res;
      }
      refreshCartRow(row, null, res.data.cart);
      if (res.data.cart) renderMinicart(res.data.cart);
      toast((res.data && res.data.message) || 'Removed from cart.');
      return res;
    });
  }

  function renderMinicart(cart) {
    var list = document.querySelector('[data-minicart-items]')
      || document.querySelector('.minicart-16 .cart-content ul.product');
    if (!list) return;

    list.classList.add('pp-minicart-live');
    var items = cartItemsList(cart);

    if (!items.length) {
      list.innerHTML = '<li class="list-content pp-minicart-empty">Your bag is empty.</li>';
    } else {
      list.innerHTML = items.map(function (item) {
        var meta = '';
        if (item.color || item.size) {
          meta = '<span class="product-size d-block">' +
            escapeHtml(
              (item.color ? ('Colour: ' + item.color) : '') +
              (item.color && item.size ? ' - ' : '') +
              (item.size ? ('Size: ' + item.size) : '')
            ) +
            '</span>';
        }
        if (item.package_label) {
          meta += '<span class="product-size d-block">' + escapeHtml('Pack: ' + item.package_label) + '</span>';
        }
        return '<li class="list-content" data-cart-row data-product-id="' + escapeHtml(item.product_id) + '" data-color="' + escapeHtml(item.color || '') + '" data-size="' + escapeHtml(item.size || '') + '" data-package-key="' + escapeHtml(item.package_key || '') + '">' +
          '<div class="prd-item">' +
          '<div class="img"><a href="' + escapeHtml(item.url) + '"><img src="' + escapeHtml(item.image) + '" alt=""></a></div>' +
          '<div class="cart_btn">' +
          '<a class="product-name" href="' + escapeHtml(item.url) + '">' + escapeHtml(item.title) + '</a>' +
          '<span class="price" data-cart-line-total>' + escapeHtml(item.line_total_formatted) + '</span>' + meta +
          '<div class="pp-minicart-row-actions">' +
          '<div class="quantity-block">' +
          '<button type="button" class="quantity-arrow-minus" data-cart-qty="minus" aria-label="Decrease">−</button>' +
          '<input class="quantity-num" type="number" min="0" max="' + escapeHtml(item.max_quantity) + '" value="' + escapeHtml(item.quantity) + '" data-cart-qty-input>' +
          '<button type="button" class="quantity-arrow-plus" data-cart-qty="plus" aria-label="Increase">+</button>' +
          '</div>' +
          '<button type="button" class="close_icon" data-cart-remove>Remove</button>' +
          '</div></div></div></li>';
      }).join('');
    }

    document.querySelectorAll('[data-cart-subtotal]').forEach(function (sub) {
      if (cart) sub.textContent = cart.subtotal_formatted || '₹ 0.00';
    });

    var countLabel = document.querySelector('[data-minicart-count-label]');
    if (countLabel && cart) {
      countLabel.textContent = cart.count ? ('(' + cart.count + ')') : '';
    }

    var checkout = document.querySelector('.minicart-16 .checkout-btn');
    if (checkout && site().checkout) checkout.setAttribute('href', site().checkout);

    updateBadges(cart ? cart.count : 0);
    if (cart) document.dispatchEvent(new CustomEvent('pp:cart-changed', { detail: { cart: cart } }));
  }

  function loadCart() {
    return request('GET', site().cartIndex || '/cart-api').then(function (res) {
      if (res.ok && res.data && res.data.cart) {
        updateBadges(res.data.cart.count);
        renderMinicart(res.data.cart);
      }
      return res;
    });
  }

  document.addEventListener('click', function (event) {
    var addBtn = event.target.closest('[data-add-to-cart], .product-design__cart, .product-btn[data-add-to-cart], .collection-page__quick');
    if (addBtn && (addBtn.hasAttribute('data-add-to-cart') || addBtn.classList.contains('product-design__cart') || addBtn.classList.contains('collection-page__quick'))) {
      var root = productRoot(addBtn);
      var productId = getProductId(addBtn, root);
      if (!productId) return;
      event.preventDefault();
      event.stopPropagation();
      var addOpts = productContext(root);
      if (!addOpts.color) addOpts.color = cleanVariant(addBtn.getAttribute('data-default-color'));
      if (!addOpts.size) addOpts.size = cleanVariant(addBtn.getAttribute('data-default-size'));
      if (!addOpts.package_key) addOpts.package_key = cleanVariant(addBtn.getAttribute('data-default-package'));
      addToCart(productId, Object.assign({ openBag: true }, addOpts), addBtn);
      return;
    }

    var buyBtn = event.target.closest('.product-design__buy, [data-buy-now]');
    if (buyBtn) {
      var buyRoot = productRoot(buyBtn);
      var buyProductId = getProductId(buyBtn, buyRoot);
      if (!buyProductId) return;
      event.preventDefault();
      event.stopPropagation();
      var buyOpts = productContext(buyRoot);
      if (!buyOpts.color) buyOpts.color = cleanVariant(buyBtn.getAttribute('data-default-color'));
      if (!buyOpts.size) buyOpts.size = cleanVariant(buyBtn.getAttribute('data-default-size'));
      if (!buyOpts.package_key) buyOpts.package_key = cleanVariant(buyBtn.getAttribute('data-default-package'));
      buyNow(buyProductId, buyOpts, buyBtn);
      return;
    }

    var qtyBtn = event.target.closest('[data-cart-qty]');
    if (qtyBtn) {
      event.preventDefault();
      event.stopPropagation();
      var row = qtyBtn.closest('[data-cart-row]');
      if (!row) return;
      var input = row.querySelector('[data-cart-qty-input]');
      var pid = row.getAttribute('data-product-id');
      if (!input || !pid) return;
      var current = parseInt(input.value, 10);
      if (isNaN(current)) current = 1;
      var max = parseInt(input.getAttribute('max'), 10) || 99;
      var next = qtyBtn.getAttribute('data-cart-qty') === 'plus' ? current + 1 : current - 1;
      next = Math.max(0, Math.min(max, next));
      input.value = next;
      updateQty(pid, next, row);
      return;
    }

    var removeBtn = event.target.closest('[data-cart-remove]');
    if (removeBtn) {
      event.preventDefault();
      event.stopPropagation();
      var removeRow = removeBtn.closest('[data-cart-row]');
      if (!removeRow) return;
      var removeId = removeRow.getAttribute('data-product-id');
      if (!removeId) return;
      var nameEl = removeRow.querySelector('.product-name, .cart_title, a.cart_title');
      var productTitle = nameEl ? String(nameEl.textContent || '').trim() : '';
      confirmRemove(productTitle).then(function (ok) {
        if (ok) removeItem(removeId, removeRow);
      });
      return;
    }

    var couponApplyBtn = event.target.closest('[data-coupon-apply]');
    if (couponApplyBtn) {
      event.preventDefault();
      event.stopPropagation();
      var applyBox = couponApplyBtn.closest('[data-coupon-box]');
      var applyInput = applyBox ? applyBox.querySelector('[data-coupon-input]') : null;
      var code = applyInput ? String(applyInput.value || '').trim() : '';
      if (!code) {
        toast('Enter a coupon code.');
        return;
      }
      applyCoupon(code, couponApplyBtn);
      return;
    }

    var couponRemoveBtn = event.target.closest('[data-coupon-remove]');
    if (couponRemoveBtn) {
      event.preventDefault();
      event.stopPropagation();
      removeCoupon(couponRemoveBtn);
      return;
    }

    var bagBtn = event.target.closest('.cart-filter-btn');
    if (bagBtn) {
      event.preventDefault();
      event.stopPropagation();
      loadCart().finally(function () {
        openBagDrawer();
      });
      return;
    }

    if (event.target.closest('[data-minicart-close], .minicart-close-icon, .minicart-16-overlay')) {
      event.preventDefault();
      closeBagDrawer();
    }
  }, true);

  document.addEventListener('change', function (event) {
    var input = event.target.closest('[data-cart-qty-input]');
    if (!input) return;
    var row = input.closest('[data-cart-row]');
    if (!row) return;
    var pid = row.getAttribute('data-product-id');
    var qty = parseInt(input.value, 10);
    if (isNaN(qty)) qty = 1;
    var max = parseInt(input.getAttribute('max'), 10) || 99;
    qty = Math.max(0, Math.min(max, qty));
    input.value = qty;
    updateQty(pid, qty, row);
  });

  // Init
  mountMinicartToBody();
  ensureBadges();
  if (site().cartCount != null) updateBadges(site().cartCount);
  loadCart();

  // A page restored with the Back/Forward button (or a tab left open while the bag was changed
  // elsewhere) still shows the old bag, so ask the server again to keep the count and drawer current.
  var cartPath = (site().cart || '/cart').replace(/^https?:\/\/[^/]+/, '');
  var lastSync = Date.now();
  function resync() {
    lastSync = Date.now();
    loadCart().then(function (res) {
      var cart = res && res.ok && res.data && res.data.cart;
      // The cart page renders its rows on the server: reload it when they no longer match.
      if (cart && window.location.pathname === cartPath) {
        var shown = document.querySelectorAll('main [data-cart-row]').length > 0;
        if (shown !== cart.count > 0) window.location.reload();
      }
    });
  }
  window.addEventListener('pageshow', function (e) { if (e.persisted) resync(); });
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible' && Date.now() - lastSync > 1500) resync();
  });

  window.PPCart = {
    add: addToCart,
    buyNow: buyNow,
    updateBadges: updateBadges,
    load: loadCart,
    renderMinicart: renderMinicart,
    open: openBagDrawer,
    close: closeBagDrawer
  };
})();
