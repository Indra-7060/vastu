/* Product page behaviour (modelled on the swarovski.com product page):
   expandable info sections, delivery option selection, full-screen image viewer with zoom,
   sticky add-to-bag bar and swipe dots on phones. */
(function () {
  var root = document.querySelector('.product-design');
  if (!root) return;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Expandable sections ---------- */
  root.querySelectorAll('[data-pdp-acc]').forEach(function (acc) {
    var btn = acc.querySelector('.pdp-acc__head button');
    var body = acc.querySelector('.pdp-acc__body');
    if (!btn || !body) return;
    btn.addEventListener('click', function () {
      var open = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      acc.classList.toggle('is-open', open);
      if (reduceMotion) { body.hidden = !open; return; }
      if (open) {
        body.hidden = false;
        var h = body.scrollHeight;
        body.style.height = '0px';
        requestAnimationFrame(function () { body.style.height = h + 'px'; });
      } else {
        body.style.height = body.scrollHeight + 'px';
        requestAnimationFrame(function () { body.style.height = '0px'; });
      }
      body.addEventListener('transitionend', function done(e) {
        if (e.propertyName !== 'height') return;
        body.removeEventListener('transitionend', done);
        body.style.height = '';
        if (btn.getAttribute('aria-expanded') !== 'true') body.hidden = true;
      });
    });
  });

  /* ---------- Delivery options (visual selection) ---------- */
  root.querySelectorAll('[data-pdp-delivery]').forEach(function (opt) {
    opt.addEventListener('click', function () {
      root.querySelectorAll('.pdp-delivery__option').forEach(function (o) {
        var on = o === opt;
        o.classList.toggle('is-selected', on);
        o.setAttribute('aria-checked', on ? 'true' : 'false');
      });
    });
  });

  /* ---------- Full-screen image viewer ---------- */
  var gallery = root.querySelector('[data-pdp-gallery]');
  var viewer = document.querySelector('[data-pdp-viewer]');
  if (gallery && viewer) {
    var stage = viewer.querySelector('[data-pdp-viewer-stage]');
    var img = viewer.querySelector('[data-pdp-viewer-img]');
    var count = viewer.querySelector('[data-pdp-viewer-count]');
    var thumbs = viewer.querySelector('[data-pdp-viewer-thumbs]');
    var lastFocus = null;
    var index = 0;

    function sources() {
      return Array.prototype.map.call(gallery.querySelectorAll('.product-design__image'), function (b) {
        return b.getAttribute('data-product-image') || (b.querySelector('img') || {}).src;
      }).filter(Boolean);
    }
    function show(i) {
      var list = sources();
      if (!list.length) return;
      index = (i + list.length) % list.length;
      stage.classList.remove('is-zoomed');
      img.src = list[index];
      img.alt = (document.getElementById('product-design-title') || {}).textContent || '';
      count.textContent = (index + 1) + ' / ' + list.length;
      thumbs.querySelectorAll('button').forEach(function (t, n) { t.classList.toggle('is-active', n === index); t.setAttribute('aria-current', n === index ? 'true' : 'false'); });
    }
    function open(i) {
      var list = sources();
      thumbs.innerHTML = list.map(function (src, n) {
        return '<button type="button" aria-label="Show image ' + (n + 1) + '" data-n="' + n + '"><img src="' + src + '" alt=""></button>';
      }).join('');
      viewer.classList.toggle('pdp-viewer--single', list.length < 2);
      lastFocus = document.activeElement;
      viewer.hidden = false;
      void viewer.offsetWidth;
      viewer.classList.add('is-open');
      document.body.classList.add('pdp-viewer-open');
      show(i);
      viewer.querySelector('[data-pdp-viewer-close]').focus();
    }
    function close() {
      viewer.classList.remove('is-open');
      document.body.classList.remove('pdp-viewer-open');
      setTimeout(function () { viewer.hidden = true; }, reduceMotion ? 0 : 300);
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    gallery.addEventListener('click', function (e) {
      var btn = e.target.closest('.product-design__image');
      if (!btn) return;
      var all = Array.prototype.slice.call(gallery.querySelectorAll('.product-design__image'));
      open(Math.max(0, all.indexOf(btn)));
    });
    viewer.querySelector('[data-pdp-viewer-close]').addEventListener('click', close);
    viewer.querySelector('[data-pdp-viewer-prev]').addEventListener('click', function () { show(index - 1); });
    viewer.querySelector('[data-pdp-viewer-next]').addEventListener('click', function () { show(index + 1); });
    thumbs.addEventListener('click', function (e) {
      var t = e.target.closest('button[data-n]');
      if (t) show(Number(t.getAttribute('data-n')));
    });
    document.addEventListener('keydown', function (e) {
      if (viewer.hidden) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') show(index - 1);
      else if (e.key === 'ArrowRight') show(index + 1);
      else if (e.key === 'Tab') {
        var items = Array.prototype.filter.call(viewer.querySelectorAll('button'), function (b) { return b.offsetParent !== null; });
        if (!items.length) return;
        var first = items[0], last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    // Click to zoom; the zoomed image follows the pointer.
    function aim(e) {
      var r = img.getBoundingClientRect();
      var x = Math.min(100, Math.max(0, ((e.clientX - r.left) / r.width) * 100));
      var y = Math.min(100, Math.max(0, ((e.clientY - r.top) / r.height) * 100));
      img.style.transformOrigin = x + '% ' + y + '%';
    }
    var swipeX = null, moved = false;
    stage.addEventListener('pointerdown', function (e) { swipeX = e.clientX; moved = false; });
    stage.addEventListener('pointermove', function (e) {
      if (swipeX !== null && Math.abs(e.clientX - swipeX) > 8) moved = true;
      if (stage.classList.contains('is-zoomed')) aim(e);
    });
    stage.addEventListener('pointerup', function (e) {
      var dx = swipeX === null ? 0 : e.clientX - swipeX;
      swipeX = null;
      if (!stage.classList.contains('is-zoomed') && Math.abs(dx) > 50) { show(index + (dx < 0 ? 1 : -1)); return; }
      if (moved && !stage.classList.contains('is-zoomed')) return;
      if (e.target !== img) { if (!stage.classList.contains('is-zoomed')) close(); return; }
      aim(e);
      stage.classList.toggle('is-zoomed');
    });
  }

  /* ---------- Phone gallery dots ---------- */
  var dots = root.querySelectorAll('.pdp-gallery-dots span');
  if (gallery && dots.length) {
    var ticking = false;
    gallery.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () {
        ticking = false;
        var i = Math.round(gallery.scrollLeft / Math.max(1, gallery.clientWidth));
        dots.forEach(function (d, n) { d.classList.toggle('is-active', n === i); });
      });
    }, { passive: true });
  }

  /* ---------- Column alignment ----------
     The taller column scrolls normally; the shorter one uses native position: sticky (smooth,
     handled by the browser). Scrolling down it sticks by its bottom edge, scrolling up it sticks
     just below the header. JavaScript only acts when the scroll direction changes: it pins the
     column where it currently is (margin-top) and moves the stick point, so nothing jumps. */
  (function () {
    var wrap = root.querySelector('.product-design__top');
    var cols = [root.querySelector('.product-design__gallery-wrap'), root.querySelector('.product-design__details')];
    if (!wrap || !cols[0] || !cols[1]) return;
    var bar = document.querySelector('[data-vt-header]');
    var desktop = window.matchMedia('(min-width: 768px)');
    var GAP = 21;
    var follower = null, dir = 0, lastY = window.pageYOffset, offset = 0;

    function topLimit() { return (bar ? bar.offsetHeight : 0) + GAP; }
    function set(off, top) {
      offset = Math.round(off);
      follower.style.setProperty('--pdp-follow-offset', offset + 'px');
      follower.style.setProperty('--pdp-follow-top', Math.round(top) + 'px');
    }
    function reset() {
      cols.forEach(function (c) {
        c.classList.remove('is-pdp-follower');
        c.style.removeProperty('--pdp-follow-offset');
        c.style.removeProperty('--pdp-follow-top');
        c.style.transform = '';
      });
      follower = null;
    }
    function measure() {
      reset();
      if (!desktop.matches) return;
      var a = cols[0].offsetHeight, b = cols[1].offsetHeight;
      if (Math.abs(a - b) < 2) return;
      follower = a < b ? cols[0] : cols[1];
      follower.classList.add('is-pdp-follower');
      dir = 0;
      place(1);
    }
    // Pin the column at its current spot and stick by the edge that matters for this direction.
    function place(newDir) {
      if (!follower) return;
      var h = follower.offsetHeight;
      var vh = window.innerHeight;
      var limit = topLimit();
      if (h <= vh - limit - GAP) { set(0, limit); dir = newDir; return; }   // fits: stick below header
      var wrapTop = wrap.getBoundingClientRect().top;
      var max = Math.max(0, wrap.offsetHeight - h);
      set(Math.min(max, Math.max(0, follower.getBoundingClientRect().top - wrapTop)), newDir > 0 ? vh - h - GAP : limit);
      dir = newDir;
    }

    window.addEventListener('scroll', function () {
      if (!follower) return;
      var y = window.pageYOffset;
      var d = y > lastY ? 1 : (y < lastY ? -1 : 0);
      lastY = y;
      if (d && d !== dir) { place(d); return; }
      // Scrolling up: once the column has reached the header it is pinned there, so the spacer
      // above it can go (no visible change) — it then stays pinned right up to the section top.
      if (dir < 0 && offset > 0 && follower.getBoundingClientRect().top <= topLimit() + 1) set(0, topLimit());
    }, { passive: true });
    var t = null;
    function remeasure() { clearTimeout(t); t = setTimeout(measure, 60); }
    window.addEventListener('resize', remeasure);
    window.addEventListener('load', measure);
    root.addEventListener('transitionend', function (e) { if (e.propertyName === 'height') remeasure(); });
    root.querySelectorAll('img').forEach(function (img) { if (!img.complete) img.addEventListener('load', remeasure); });
    measure();
  })();

  /* ---------- Sticky add-to-bag bar ---------- */
  var sticky = document.querySelector('[data-pdp-sticky]');
  var mainAdd = root.querySelector('[data-pdp-add]');
  if (sticky && mainAdd && 'IntersectionObserver' in window) {
    var price = root.querySelector('[data-product-price]');
    var stickyPrice = sticky.querySelector('[data-pdp-sticky-price]');
    var stickyBtn = sticky.querySelector('[data-pdp-sticky-add]') || sticky.querySelector('.pdp-sticky__btn');
    function syncPrice() { if (price && stickyPrice) stickyPrice.innerHTML = price.innerHTML; }
    syncPrice();
    if (price && 'MutationObserver' in window) new MutationObserver(syncPrice).observe(price, { childList: true, subtree: true, characterData: true });

    var footer = document.querySelector('.vt-footer');
    var pastButton = false, footerInView = false;
    function update() {
      var on = pastButton && !footerInView;
      sticky.classList.toggle('is-visible', on);
      sticky.setAttribute('aria-hidden', on ? 'false' : 'true');
      stickyBtn.tabIndex = on ? 0 : -1;
    }
    new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { pastButton = !en.isIntersecting && en.boundingClientRect.top < 0; });
      update();
    }).observe(mainAdd);
    if (footer) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { footerInView = en.isIntersecting; });
        update();
      }).observe(footer);
    }
    // Bottom-bar button: adds via the main button (same quantity), then shows the real result.
    var productId = Number(mainAdd.getAttribute('data-product-id')) || 0;
    var inBag = sticky.querySelector('[data-pdp-sticky-inbag]');
    var waiting = false, resetTimer = null;
    function showInBag(cart) {
      if (!inBag || !cart) return;
      var qty = (cart.items || []).reduce(function (sum, item) {
        return sum + (Number(item.product_id) === productId ? Number(item.quantity) || 0 : 0);
      }, 0);
      inBag.textContent = qty ? qty + ' in your cart' : '';
      inBag.hidden = !qty;
      // Already in the bag: the bottom-bar button opens the bag instead of adding again.
      openMode = qty > 0;
      if (!waiting) stickyBtn.textContent = label();
    }
    var openMode = false;
    function label() { return openMode ? 'Open cart' : 'Add to cart'; }
    if (stickyBtn.hasAttribute('data-pdp-sticky-add')) stickyBtn.addEventListener('click', function () {
      if (openMode && window.PPCart) {
        window.PPCart.load().then(function () { window.PPCart.open(); });
        return;
      }
      if (waiting) return;
      waiting = true;
      stickyBtn.textContent = 'Adding…';
      stickyBtn.setAttribute('aria-busy', 'true');
      mainAdd.click();
    });
    document.addEventListener('pp:cart-add', function (e) {
      var d = e.detail || {};
      if (d.productId !== productId) return;
      if (d.cart) showInBag(d.cart);
      if (!waiting) return;
      waiting = false;
      stickyBtn.removeAttribute('aria-busy');
      stickyBtn.textContent = d.ok ? 'Added to cart ✓' : label();
      clearTimeout(resetTimer);
      resetTimer = setTimeout(function () { stickyBtn.textContent = label(); }, 2000);
    });
    // Bag changed anywhere (quantity changed or item removed in the bag drawer).
    document.addEventListener('pp:cart-changed', function (e) { showInBag(e.detail && e.detail.cart); });
    // Quantity already in the bag when the page opens.
    if (inBag && window.fetch) {
      fetch((window.PP_SITE && window.PP_SITE.cartIndex) || '/cart-api', { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
        .then(function (r) { return r.json(); })
        .then(function (data) { showInBag(data && data.cart); })
        .catch(function () {});
    }
  }
})();
