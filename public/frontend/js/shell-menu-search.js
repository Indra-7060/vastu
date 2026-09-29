/**
 * Account / support shell: reliable Menu + Search openers.
 * Mounts panels to body and opens mmenu / search even if theme handlers miss.
 */
(function ($) {
  'use strict';

  function mountToBody(selector) {
    var el = document.querySelector(selector);
    if (el && el.parentElement !== document.body) {
      document.body.appendChild(el);
    }
    return el;
  }

  function openSearch() {
    mountToBody('.search-16-wrap');
    var wrap = document.querySelector('.search-16-wrap');
    if (!wrap) return;
    wrap.classList.add('show');
    var input = wrap.querySelector('.js-frontend-search-input');
    if (input) {
      setTimeout(function () { input.focus(); }, 50);
    }
  }

  function closeSearch() {
    var wrap = document.querySelector('.search-16-wrap');
    if (wrap) wrap.classList.remove('show');
  }

  function openMenu() {
    var $menu = $('#menu');
    if (!$menu.length) return;

    function tryOpen(attempt) {
      var api = $menu.data('mmenu');
      if (api && typeof api.open === 'function') {
        api.open();
        return;
      }
      if (attempt < 8) {
        window.setTimeout(function () { tryOpen(attempt + 1); }, 60);
        return;
      }
      $menu.addClass('mm-menu_opened');
    }

    tryOpen(0);
  }

  $(function () {
    if (!document.body.classList.contains('customer-account-page') &&
        !document.body.classList.contains('support-page')) {
      return;
    }

    // Search panel must sit on body so sticky header stacking never covers it.
    mountToBody('.search-16-wrap');

    $(document).on('click.ppShell', '.account-search-link, .cart-search-btn, .js-mm-search', function (e) {
      e.preventDefault();
      openSearch();
    });

    $(document).on('click.ppShell', '.account-menu-link.menubar, .menubar[href="#menu"], #menu-btn', function (e) {
      e.preventDefault();
      openMenu();
    });

    $(document).on('click.ppShell', '.search-close-icon, .open-search-16-overlay', function (e) {
      e.preventDefault();
      closeSearch();
    });
  });
})(jQuery);
