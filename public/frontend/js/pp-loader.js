/**
 * Global page + button loader for Vastutathastu storefront.
 * Usage: PPLoader.show('Signing in…'); PPLoader.hide();
 *        PPLoader.setButton(btn, true, 'Please wait…');
 */
(function (w) {
  'use strict';

  var el = null;
  var depth = 0;

  function ensure() {
    if (el && el.parentNode) return el;
    el = document.createElement('div');
    el.id = 'pp-page-loader';
    el.className = 'pp-page-loader';
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML =
      '<div class="pp-page-loader__box">' +
        '<div class="pp-page-loader__spin" aria-hidden="true"></div>' +
        '<p class="pp-page-loader__text">Please wait…</p>' +
      '</div>';
    var parent = document.body || document.documentElement;
    parent.appendChild(el);
    return el;
  }

  function show(message) {
    depth += 1;
    var node = ensure();
    var text = node.querySelector('.pp-page-loader__text');
    if (text) text.textContent = message || 'Please wait…';
    node.classList.add('is-visible');
    node.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('pp-loading');
  }

  function hide(force) {
    depth = force ? 0 : Math.max(0, depth - 1);
    if (depth > 0) return;
    if (!el) return;
    el.classList.remove('is-visible');
    el.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('pp-loading');
  }

  function setButton(btn, busy, label) {
    if (!btn) return;
    if (busy) {
      if (!btn.dataset.ppOriginalHtml) btn.dataset.ppOriginalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.classList.add('pp-btn-loading');
      btn.setAttribute('aria-busy', 'true');
      var text = label || 'Please wait…';
      btn.innerHTML =
        '<span class="pp-btn-spinner" aria-hidden="true"></span>' +
        '<span class="pp-btn-loading-text">' + text + '</span>';
    } else {
      btn.disabled = false;
      btn.classList.remove('pp-btn-loading');
      btn.removeAttribute('aria-busy');
      if (btn.dataset.ppOriginalHtml) {
        btn.innerHTML = btn.dataset.ppOriginalHtml;
        delete btn.dataset.ppOriginalHtml;
      }
    }
  }

  function start(btn, message, buttonLabel) {
    show(message);
    setButton(btn, true, buttonLabel || message || 'Please wait…');
  }

  function stop(btn, force) {
    setButton(btn, false);
    hide(force);
  }

  w.PPLoader = {
    show: show,
    hide: hide,
    setButton: setButton,
    start: start,
    stop: stop
  };
})(window);
