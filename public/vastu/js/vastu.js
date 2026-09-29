/* Vastutathastu storefront behaviour: mega menu, product tiles, listing filters/sort,
   "load more", scroll reveal and the homepage carousels. */
(function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var desktop = window.matchMedia('(min-width: 992px)');

  /* ---------- Mega menu (desktop hover flyouts) ---------- */
  (function () {
    var items = document.querySelectorAll('[data-vt-mega]');
    var backdrop = document.querySelector('[data-vt-mega-backdrop]');
    var header = document.querySelector('.vt-header');
    if (!items.length || !header) return;

    var openItem = null;
    var openTimer = null;
    var closeTimer = null;

    function open(item) {
      if (!desktop.matches || !item.querySelector('[data-vt-mega-panel]')) return;
      clearTimeout(closeTimer);
      if (openItem === item) return;
      if (openItem) openItem.classList.remove('is-open');
      var switching = !!openItem;
      openItem = item;
      item.classList.add('is-open');
      header.classList.toggle('is-mega-switching', switching);
      header.classList.add('is-mega-open');
      if (backdrop) backdrop.classList.add('is-visible');
      item.querySelector('a').setAttribute('aria-expanded', 'true');
    }

    function close() {
      if (!openItem) return;
      openItem.classList.remove('is-open');
      openItem.querySelector('a').setAttribute('aria-expanded', 'false');
      openItem = null;
      header.classList.remove('is-mega-open', 'is-mega-switching');
      if (backdrop) backdrop.classList.remove('is-visible');
    }

    items.forEach(function (item) {
      item.addEventListener('mouseenter', function () {
        clearTimeout(openTimer);
        openTimer = setTimeout(function () { open(item); }, openItem ? 0 : 140);
      });
      item.addEventListener('mouseleave', function () {
        clearTimeout(openTimer);
        closeTimer = setTimeout(close, 160);
      });
      item.addEventListener('focusin', function () { open(item); });
    });

    header.addEventListener('focusout', function (e) {
      if (!header.contains(e.relatedTarget)) close();
    });
    if (backdrop) backdrop.addEventListener('mouseenter', close);
    // Leaving the header (and its open panel) always closes the menu, and a page shown again with the
    // Back button starts with the menu closed, so the header can't be left in a half-open state.
    header.addEventListener('mouseleave', function () { clearTimeout(openTimer); closeTimer = setTimeout(close, 160); });
    header.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
    window.addEventListener('pageshow', function () {
      clearTimeout(openTimer);
      close();
      items.forEach(function (item) {
        item.classList.remove('is-open');
        var link = item.querySelector('a');
        if (link) link.setAttribute('aria-expanded', 'false');
      });
      header.classList.remove('is-mega-open', 'is-mega-switching');
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    desktop.addEventListener && desktop.addEventListener('change', close);
  })();

  /* ---------- Product tiles: image swiper with progress bar ---------- */
  function initTile(media) {
    if (media.dataset.vtReady) return;
    media.dataset.vtReady = '1';
    var images = media.querySelectorAll('.vt-tile__img');
    var bar = media.querySelector('.vt-tile__progress-bar');
    if (images.length < 2) return;
    var index = 0;

    function show(i) {
      index = (i + images.length) % images.length;
      images.forEach(function (img, n) { img.classList.toggle('is-active', n === index); });
      if (bar) bar.style.transform = 'translateX(' + (index * 100) + '%)';
    }

    // Desktop: the pointer position across the image picks the slide (like the reference PLP).
    media.addEventListener('mousemove', function (e) {
      var rect = media.getBoundingClientRect();
      var slot = Math.min(images.length - 1, Math.floor(((e.clientX - rect.left) / rect.width) * images.length));
      if (slot !== index) show(slot);
    });
    media.addEventListener('mouseleave', function () { show(0); });

    // Touch: swipe left / right.
    var startX = null;
    media.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    media.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 30) {
        show(index + (dx < 0 ? 1 : -1));
      }
      startX = null;
    });
  }

  /* ---------- Scroll reveal (fade in tiles / teasers as they enter) ---------- */
  var revealObserver = ('IntersectionObserver' in window && !reduceMotion)
    ? new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            revealObserver.unobserve(entry.target);
          }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 })
    : null;

  function initReveal(root) {
    (root || document).querySelectorAll('[data-vt-reveal]:not(.is-revealed)').forEach(function (el, i) {
      if (!revealObserver) { el.classList.add('is-revealed'); return; }
      el.style.setProperty('--vt-delay', (i % 4) * 70 + 'ms');
      revealObserver.observe(el);
    });
    (root || document).querySelectorAll('[data-vt-swiper]').forEach(initTile);
  }
  document.documentElement.classList.add('vt-js');
  initReveal(document);

  /* ---------- Header: transparent over the hero, solid white once the page scrolls (reference) ---------- */
  (function () {
    var header = document.querySelector('[data-vt-header]');
    if (!header) return;
    var ticking = false;
    function update() {
      ticking = false;
      header.classList.toggle('is-solid', window.pageYOffset > 0);
    }
    update();
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
  })();

  /* ---------- Homepage hero slideshow: carousel that keeps sliding on its own every 5 s,
     wrap-around, drag / swipe, page dots. Choosing a slide restarts the 5 s wait. ---------- */
  (function () {
    var hero = document.querySelector('[data-vt-hero]');
    if (!hero) return;
    requestAnimationFrame(function () { hero.classList.add('is-ready'); });

    var viewportEl = hero.querySelector('.vt-hero__viewport');
    var track = hero.querySelector('[data-vt-hero-track]');
    var slides = Array.prototype.slice.call(track.querySelectorAll('[data-vt-hero-slide]'));
    var dots = hero.querySelectorAll('[data-vt-hero-dot]');
    var n = slides.length;
    if (n < 2) return;

    // Clones at both ends make the loop seamless (last <- first ... last -> first).
    function cloneOf(slide) {
      var c = slide.cloneNode(true);
      c.classList.remove('is-selected');
      c.removeAttribute('data-vt-hero-slide');
      c.setAttribute('aria-hidden', 'true');
      c.querySelectorAll('[id]').forEach(function (el) { el.removeAttribute('id'); });
      c.querySelectorAll('a, button').forEach(function (el) { el.tabIndex = -1; });
      c.querySelectorAll('h1').forEach(function (h) {
        var h2 = document.createElement('h2');
        h2.className = h.className;
        h2.textContent = h.textContent;
        h.parentNode.replaceChild(h2, h);
      });
      return c;
    }
    track.appendChild(cloneOf(slides[0]));
    track.insertBefore(cloneOf(slides[n - 1]), slides[0]);

    var EASE = 'transform 1s cubic-bezier(.22, 1, .36, 1)';
    var pos = 1;       // position in the track (clones included)
    var index = 0;     // real slide index
    var timer = null;

    function width() { return viewportEl.clientWidth; }
    function setX(px, animate) {
      track.style.transition = animate && !reduceMotion ? EASE : 'none';
      track.style.transform = 'translate3d(' + px + 'px, 0, 0)';
    }
    function currentX() {
      var m = new (window.DOMMatrixReadOnly || window.WebKitCSSMatrix)(getComputedStyle(track).transform);
      return m.m41;
    }
    function normalise() {
      if (pos === 0 || pos === n + 1) { pos = index + 1; setX(-pos * width(), false); }
    }
    function mark() {
      slides.forEach(function (slide, i) {
        var on = i === index;
        slide.classList.toggle('is-selected', on);
        slide.setAttribute('aria-hidden', on ? 'false' : 'true');
        slide.querySelectorAll('a, button').forEach(function (el) { el.tabIndex = on ? 0 : -1; });
      });
      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-selected', i === index);
        dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
      });
      track.querySelectorAll('video').forEach(function (v) {
        if (v.closest('.vt-hero__slide') === slides[index]) { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
        else v.pause();
      });
    }
    function go(to, animate) {
      index = ((to % n) + n) % n;
      pos = to + 1;
      if (pos < 0 || pos > n + 1) pos = index + 1;
      setX(-pos * width(), animate !== false);
      mark();
      if (animate === false || reduceMotion) normalise();
    }
    track.addEventListener('transitionend', function (e) { if (e.target === track) normalise(); });

    function stop() { clearInterval(timer); timer = null; }
    function start() {
      stop();
      if (reduceMotion) return;
      timer = setInterval(function () { if (!document.hidden) go(index + 1); }, 5000);
    }
    // The visitor picked a slide: show it, then carry on sliding 5 s later.
    function takeControl() { start(); }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { takeControl(); go(i); });
    });
    hero.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
      takeControl();
      go(index + (e.key === 'ArrowRight' ? 1 : -1));
    });

    // Drag / swipe (3px threshold, like the reference).
    var startX = 0, startY = 0, baseX = 0, tracking = false, moved = false, suppressClick = false;
    viewportEl.addEventListener('pointerdown', function (e) {
      if (e.button !== 0) return;
      tracking = true; moved = false;
      startX = e.clientX; startY = e.clientY;
    });
    viewportEl.addEventListener('pointermove', function (e) {
      if (!tracking) return;
      var dx = e.clientX - startX, dy = e.clientY - startY;
      if (!moved) {
        if (Math.abs(dx) < 3 && Math.abs(dy) < 3) return;
        if (Math.abs(dy) > Math.abs(dx)) { tracking = false; return; }
        moved = true;
        stop();
        normalise();
        baseX = currentX();
        hero.classList.add('is-dragging');
        try { viewportEl.setPointerCapture(e.pointerId); } catch (err) {}
      }
      setX(baseX + dx, false);
    });
    function release(e) {
      if (!tracking) return;
      tracking = false;
      if (!moved) return;
      hero.classList.remove('is-dragging');
      suppressClick = true;
      var dx = e.clientX - startX;
      var threshold = Math.min(120, width() * 0.12);
      var from = pos - 1;
      if (dx < -threshold) go(from + 1);
      else if (dx > threshold) go(from - 1);
      else go(from);
      start();
    }
    viewportEl.addEventListener('pointerup', release);
    viewportEl.addEventListener('pointercancel', release);
    viewportEl.addEventListener('click', function (e) {
      if (suppressClick) { e.preventDefault(); e.stopPropagation(); suppressClick = false; }
    }, true);

    window.addEventListener('resize', function () { setX(-pos * width(), false); });

    go(0, false);
    start();
  })();

  /* ---------- Book a consultation pop-up (every link to the consultation page opens it) ---------- */
  (function () {
    var modal = document.querySelector('[data-vt-consult-modal]');
    if (!modal) return;
    var dialog = modal.querySelector('[role="dialog"]');
    var form = modal.querySelector('[data-vt-consult-form]');
    var done = modal.querySelector('[data-vt-consult-done]');
    var status = modal.querySelector('[data-vt-consult-status]');
    var submit = form.querySelector('[type="submit"]');
    var lastTrigger = null;
    var closeTimer = null;

    function isConsultLink(a) {
      return a.hasAttribute('data-vt-consult') || /\/info\/book-a-consultation\/?(?:[?#].*)?$/.test(a.getAttribute('href') || '');
    }
    function open(trigger) {
      clearTimeout(closeTimer);
      lastTrigger = trigger || document.activeElement;
      form.hidden = false;
      done.hidden = true;
      modal.hidden = false;
      document.body.classList.add('vt-consult-open');
      void modal.offsetWidth; // start the fade / slide-up from the hidden state
      modal.classList.add('is-open');
      setTimeout(function () { (form.querySelector('input:not([type=hidden]):placeholder-shown') || form.querySelector('input:not([type=hidden])')).focus(); }, 60);
    }
    function close() {
      modal.classList.remove('is-open');
      document.body.classList.remove('vt-consult-open');
      closeTimer = setTimeout(function () { modal.hidden = true; }, reduceMotion ? 0 : 350);
      if (lastTrigger && lastTrigger.focus) lastTrigger.focus();
    }
    function clearErrors() {
      form.querySelectorAll('.vt-field').forEach(function (f) { f.classList.remove('has-error'); });
      form.querySelectorAll('[data-error-for]').forEach(function (e) { e.textContent = ''; });
      status.textContent = '';
    }
    function showError(name, text) {
      var slot = form.querySelector('[data-error-for="' + name + '"]');
      if (slot) { slot.textContent = text; slot.closest('.vt-field').classList.add('has-error'); }
      else status.textContent = text;
    }

    document.addEventListener('click', function (e) {
      var a = e.target.closest('a[href], [data-vt-consult]');
      if (!a || !isConsultLink(a) || e.metaKey || e.ctrlKey || e.shiftKey) return;
      e.preventDefault();
      open(a);
    });
    modal.querySelectorAll('[data-vt-consult-close]').forEach(function (el) { el.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) {
      if (modal.hidden) return;
      if (e.key === 'Escape') { close(); return; }
      if (e.key !== 'Tab') return;
      // Keep keyboard focus inside the dialog.
      var items = Array.prototype.filter.call(dialog.querySelectorAll('button, input, select, textarea, a[href]'), function (el) { return el.offsetParent !== null && !el.disabled; });
      if (!items.length) return;
      var first = items[0], last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearErrors();
      var ok = true;
      [['name', 'Please enter your name.'], ['phone', 'Please enter your phone / WhatsApp number.'], ['email', 'Please enter your email.']].forEach(function (pair) {
        var input = form.elements[pair[0]];
        if (!input.value.trim()) { showError(pair[0], pair[1]); ok = false; }
      });
      var email = form.elements.email.value.trim();
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('email', 'Please enter a valid email address.'); ok = false; }
      if (!ok) { var bad = form.querySelector('.has-error input, .has-error select, .has-error textarea'); if (bad) bad.focus(); return; }

      submit.disabled = true;
      submit.textContent = 'Sending…';
      fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: new FormData(form)
      }).then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (data) { return { status: res.status, data: data }; });
      }).then(function (res) {
        if (res.status >= 200 && res.status < 300) {
          form.reset();
          form.hidden = true;
          done.hidden = false;
          var text = done.querySelector('[data-vt-consult-done-text]');
          if (text && res.data.message) text.textContent = res.data.message;
          done.querySelector('button').focus();
        } else if (res.status === 422 && res.data.errors) {
          Object.keys(res.data.errors).forEach(function (k) { showError(k, res.data.errors[k][0]); });
        } else if (res.status === 429) {
          status.textContent = 'Too many requests. Please wait a minute and try again.';
        } else if (res.status === 419) {
          status.textContent = 'Your session expired. Please refresh the page and try again.';
        } else {
          status.textContent = 'Something went wrong. Please try again.';
        }
      }).catch(function () {
        status.textContent = 'Network error. Please check your connection and try again.';
      }).then(function () {
        submit.disabled = false;
        submit.textContent = 'Send enquiry';
      });
    });
  })();

  /* ---------- Homepage refresh: open on "Shop by Category" so its tiles animate in first ---------- */
  (function () {
    var cats = document.querySelector('.vt-page--home .vt-categories');
    if (!cats || !window.vtHomeReload) return;
    var target = 0;
    function jump() {
      var bar = document.querySelector('[data-vt-header]');
      target = Math.max(0, Math.round(cats.getBoundingClientRect().top + window.pageYOffset - (bar ? bar.offsetHeight : 0)));
      window.scrollTo(0, target);
    }
    jump();
    // Fonts and images can shift the layout; re-align once on load unless the visitor has scrolled.
    window.addEventListener('load', function () {
      if (Math.abs(window.pageYOffset - target) < 4) jump();
    });
  })();

  /* ---------- Section motion: reveal headings, tiles, cards and split sections as they enter ---------- */
  (function () {
    window.vtMotionReady = true;
    if (!document.documentElement.classList.contains('vt-motion')) return;
    // [selector, stagger step (ms), items per row]
    var groups = [
      ['.vt-cat', 100, 4],
      ['.vt-carousel__slide', 100, 4],
      ['.vt-article', 120, 3],
      ['.vt-footer__main > *', 80, 5],
      ['.vt-insta__tile', 100, 6],
      ['.vt-insta__head', 0, 1],
      ['.vt-section-head, .vt-intro__inner, .vt-wisdom__head, .vt-products .vt-h3, .vt-split, .vt-cta', 0, 1]
    ];
    var pending = [];
    function reveal(el) {
      el.classList.add('is-animated');
      io.unobserve(el);
      var n = pending.indexOf(el);
      if (n > -1) pending.splice(n, 1);
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        // Also reveal anything already scrolled past (reload mid-page, anchor jumps).
        if (entry.isIntersecting || entry.boundingClientRect.bottom < 0) reveal(entry.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.1 });

    groups.forEach(function (g) {
      document.querySelectorAll(g[0]).forEach(function (el, i) {
        el.style.setProperty('--vt-delay', (i % g[2]) * g[1] + 'ms');
        pending.push(el);
        io.observe(el);
      });
    });

    // A fast jump can skip an element without it ever intersecting; catch those on scroll.
    var queued = false;
    // Capture phase so carousel (element) scrolls are seen as well as page scrolls.
    document.addEventListener('scroll', function () {
      if (queued || !pending.length) return;
      queued = true;
      requestAnimationFrame(function () {
        queued = false;
        pending.slice().forEach(function (el) {
          var r = el.getBoundingClientRect();
          // Carousel slides scrolled off to the side wait for the observer instead.
          if (r.top < window.innerHeight && r.left < window.innerWidth && r.right > 0) reveal(el);
        });
      });
    }, { passive: true, capture: true });
  })();

  /* ---------- Listing: drawers (filters / sort) ---------- */
  (function () {
    var overlay = document.querySelector('[data-vt-overlay]');
    var active = null;
    var lastTrigger = null;

    function openDrawer(name, trigger) {
      var drawer = document.querySelector('[data-vt-drawer="' + name + '"]');
      if (!drawer) return;
      closeDrawer(false);
      active = drawer;
      lastTrigger = trigger || null;
      if (overlay) { overlay.hidden = false; requestAnimationFrame(function () { overlay.classList.add('is-visible'); }); }
      drawer.classList.add('is-open');
      drawer.setAttribute('aria-hidden', 'false');
      if (trigger) trigger.setAttribute('aria-expanded', 'true');
      document.body.classList.add('vt-lock');
      var focusable = drawer.querySelector('button, a, input, summary');
      if (focusable) setTimeout(function () { focusable.focus(); }, 50);
    }

    function closeDrawer(restore) {
      if (!active) return;
      active.classList.remove('is-open');
      active.setAttribute('aria-hidden', 'true');
      if (lastTrigger) lastTrigger.setAttribute('aria-expanded', 'false');
      if (overlay) {
        overlay.classList.remove('is-visible');
        setTimeout(function () { if (!active) overlay.hidden = true; }, 350);
      }
      document.body.classList.remove('vt-lock');
      if (restore !== false && lastTrigger) lastTrigger.focus();
      active = null;
    }

    document.querySelectorAll('[data-vt-open]').forEach(function (btn) {
      btn.addEventListener('click', function () { openDrawer(btn.getAttribute('data-vt-open'), btn); });
    });
    document.querySelectorAll('[data-vt-close]').forEach(function (btn) {
      btn.addEventListener('click', function () { closeDrawer(); });
    });
    if (overlay) overlay.addEventListener('click', function () { closeDrawer(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
  })();

  /* ---------- Skeleton loading: images show a soft shimmer until they have loaded ---------- */
  var SKEL_BOXES = '.vt-tile__media, .vt-cat__img, .vt-card__img, .vt-mega__img, .product-design__image, .vt-split__media, .vt-article__img, .vt-insta__media, .vt-gallery__open';
  function skeletonImages(root) {
    (root || document).querySelectorAll(SKEL_BOXES).forEach(function (box) {
      var img = box.querySelector('img');
      if (!img || box.hasAttribute('data-vt-skel')) return;
      box.setAttribute('data-vt-skel', '');
      if (img.complete && img.naturalWidth) return;
      box.classList.add('vt-skel');
      function done() { box.classList.remove('vt-skel'); }
      img.addEventListener('load', done, { once: true });
      img.addEventListener('error', done, { once: true });
    });
  }
  skeletonImages(document);
  window.vtSkeletonImages = skeletonImages;

  /* ---------- Listing: load more ---------- */
  (function () {
    var button = document.querySelector('[data-vt-load]');
    var grid = document.querySelector('[data-vt-grid]');
    if (!button || !grid || !window.fetch) return;

    button.addEventListener('click', function (e) {
      e.preventDefault();
      if (button.classList.contains('is-loading')) return;
      button.classList.add('is-loading');
      button.textContent = 'Loading…';
      // Placeholder tiles while the next products load.
      var skeletons = [];
      for (var k = 0; k < 4; k++) {
        var sk = document.createElement('div');
        sk.className = 'vt-tile vt-tile--skel';
        sk.setAttribute('aria-hidden', 'true');
        sk.innerHTML = '<div class="vt-tile__media vt-skel"></div><div class="vt-tile__info"><span class="vt-skel-line"></span><span class="vt-skel-line vt-skel-line--short"></span></div>';
        grid.appendChild(sk);
        skeletons.push(sk);
      }
      function clearSkeletons() { skeletons.forEach(function (sk) { sk.remove(); }); }
      var url = button.getAttribute('href');
      url += (url.indexOf('?') > -1 ? '&' : '?') + 'partial=1';

      fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          clearSkeletons();
          var holder = document.createElement('div');
          holder.innerHTML = data.html;
          var added = Array.prototype.slice.call(holder.children);
          added.forEach(function (node) { grid.appendChild(node); });
          initReveal(grid);
          skeletonImages(grid);
          var shown = document.querySelector('[data-vt-shown]');
          var meter = document.querySelector('[data-vt-meter]');
          if (shown) shown.textContent = data.shown;
          if (meter) meter.style.width = Math.round((data.shown / Math.max(1, data.total)) * 100) + '%';
          if (data.next) {
            button.setAttribute('href', data.next);
            button.textContent = 'Load more';
            button.classList.remove('is-loading');
          } else {
            button.remove();
          }
          if (added[0]) {
            var link = added[0].querySelector('a');
            if (link) link.focus({ preventScroll: true });
          }
        })
        .catch(function () { window.location.href = button.getAttribute('href'); });
    });
  })();

  /* ---------- Listing: banner zoom-out on load (teaser animation) ---------- */
  document.querySelectorAll('[data-vt-zoom]').forEach(function (img) {
    if (reduceMotion) return;
    function go() { img.classList.add('is-in'); }
    if (img.complete) requestAnimationFrame(go); else img.addEventListener('load', go);
  });

  /* ---------- Listing: "Read more" for long intros ---------- */
  document.querySelectorAll('[data-vt-readmore]').forEach(function (p) {
    if (p.textContent.trim().length < 150) return;
    p.classList.add('is-clamped');
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'vt-readmore';
    btn.textContent = 'Read more';
    btn.addEventListener('click', function () {
      var clamped = p.classList.toggle('is-clamped');
      btn.textContent = clamped ? 'Read more' : 'Read less';
    });
    p.insertAdjacentElement('afterend', btn);
  });

  /* ---------- Homepage product carousel: endless glide ----------
     The product row moves on its own and loops seamlessly (the items are repeated once).
     Hovering pauses it for as long as the pointer is over it; clicking a product stops it, and
     it starts again when the visitor comes back to this page (Back button / bfcache restore).
     It also rests while off screen or in a background tab. Swiping still works on touch screens. */
  document.querySelectorAll('[data-vt-carousel]').forEach(function (carousel) {
    var track = carousel.querySelector('[data-vt-track]');
    if (!track) return;
    var originals = Array.prototype.slice.call(track.children);
    if (!carousel.hasAttribute('data-vt-marquee') || originals.length < 2 || reduceMotion) return;

    // Repeat the items once so the loop has no visible jump.
    originals.forEach(function (slide) {
      var copy = slide.cloneNode(true);
      copy.classList.add('is-clone', 'is-animated');
      copy.setAttribute('aria-hidden', 'true');
      copy.querySelectorAll('a, button').forEach(function (el) { el.tabIndex = -1; });
      track.appendChild(copy);
    });
    carousel.classList.add('is-marquee');

    var SPEED = 40;                 // px per second
    var pos = track.scrollLeft;
    var last = 0, raf = 0;
    var hovering = false, touching = false, stopped = false, onScreen = true, resumeTimer = null;

    function loopWidth() { return track.children[originals.length].offsetLeft - track.children[0].offsetLeft; }
    function running() { return !hovering && !touching && !stopped && onScreen && !document.hidden; }
    function frame(t) {
      raf = 0;
      if (!running()) { last = 0; return; }
      if (last) {
        pos += SPEED * Math.min(64, t - last) / 1000;
        var w = loopWidth();
        if (w > 0 && pos >= w) pos -= w;
        track.scrollLeft = pos;
      }
      last = t;
      raf = requestAnimationFrame(frame);
    }
    function play() {
      if (raf || !running()) return;
      pos = track.scrollLeft;       // continue from wherever the row is (e.g. after a swipe)
      last = 0;
      raf = requestAnimationFrame(frame);
    }

    carousel.addEventListener('mouseenter', function () { hovering = true; });
    carousel.addEventListener('mouseleave', function () { hovering = false; play(); });
    carousel.addEventListener('focusin', function () { hovering = true; });
    carousel.addEventListener('focusout', function () { hovering = false; play(); });
    track.addEventListener('touchstart', function () { touching = true; clearTimeout(resumeTimer); }, { passive: true });
    track.addEventListener('touchend', function () {
      clearTimeout(resumeTimer);
      resumeTimer = setTimeout(function () { touching = false; play(); }, 2500);
    }, { passive: true });
    // A swipe past the repeated half wraps back seamlessly.
    track.addEventListener('scroll', function () {
      if (raf) return;
      var w = loopWidth();
      if (w > 0 && track.scrollLeft >= w) track.scrollLeft -= w;
    }, { passive: true });
    // Opening a product stops the carousel…
    track.addEventListener('click', function (e) { if (e.target.closest('a')) stopped = true; });
    // …and it starts again when the visitor comes back to this page.
    window.addEventListener('pageshow', function () { stopped = false; hovering = false; touching = false; play(); });
    document.addEventListener('visibilitychange', play);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        onScreen = entries[0].isIntersecting;
        play();
      }).observe(carousel);
    }
    play();
  });

  // Respect reduced-motion: show the poster frame instead of the looping video.
  var video = document.querySelector('.vt-hero__media');
  if (video && reduceMotion) {
    video.removeAttribute('autoplay');
    video.pause();
  }
})();
