/**
 * Header search panel: results appear while you type.
 *  - Debounced: the server is asked only after typing pauses (DEBOUNCE_MS).
 *  - Memoised: every answer is cached per query, so repeating / going back to a query is instant
 *    and makes no request.
 *  - Stale answers are dropped (in-flight request aborted, sequence check), so results never jump back.
 *  - Category suggestions filter instantly on every key, with the typed text highlighted.
 *  - Arrow keys move through the results; Enter opens the highlighted one (or the full results page).
 */
(function ($) {
  "use strict";

  var DEBOUNCE_MS = 250;
  var CACHE_LIMIT = 60;
  var cache = new Map();   // normalised query -> server response
  var timer = null;
  var activeRequest = null;
  var sequence = 0;

  function escapeHtml(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function normalise(query) {
    return $.trim(String(query || "")).replace(/\s+/g, " ").toLowerCase();
  }

  // Escaped text with every typed word wrapped in <mark>.
  function highlight(text, query) {
    var safe = escapeHtml(text);
    var words = normalise(query).split(" ").filter(Boolean)
      .map(function (w) { return escapeHtml(w).replace(/[.*+?^${}()|[\]\\]/g, "\\$&"); });
    if (!words.length) return safe;
    return safe.replace(new RegExp("(" + words.join("|") + ")", "gi"), "<mark>$1</mark>");
  }

  function remember(key, data) {
    if (cache.has(key)) cache.delete(key);
    cache.set(key, data);
    if (cache.size > CACHE_LIMIT) cache.delete(cache.keys().next().value);
  }

  function priceHtml(product) {
    if (product.price_on_request) {
      return '<span class="pp-price"><span class="pp-price__selling pp-price--request">Price on request</span></span>';
    }
    var discount = Number(product.discount_percent || 0);
    if (!product.has_selling_price) {
      return '<span class="pp-price pp-price--mrp-only"><span class="pp-price__mrp">' + escapeHtml(product.mrp) + '</span></span>';
    }
    return '<span class="pp-price pp-price--has-selling-price">' +
      (discount ? '<span class="pp-price__mrp"><s>' + escapeHtml(product.mrp) + '</s></span><span class="pp-price__discount">-' + discount + '%</span>' : '') +
      '<span class="pp-price__selling">' + escapeHtml(product.selling_price) + '</span></span>';
  }

  function message($wrap, text) {
    $wrap.find(".js-search-results").html('<div class="js-search-empty text-muted py-3">' + escapeHtml(text) + '</div>');
  }

  function render($wrap, data, query) {
    var $results = $wrap.find(".js-search-results");
    var $title = $wrap.find(".js-search-results-title");
    var $seeAll = $wrap.find(".js-search-see-all");
    var products = data.products || [];

    if (!products.length) {
      $title.text("TOP RESULTS");
      message($wrap, "No products found for “" + query + "”.");
      $seeAll.hide();
      return;
    }

    $title.text(data.total > products.length ? "TOP RESULTS (" + data.total + ")" : data.total + (data.total === 1 ? " RESULT" : " RESULTS"));
    $results.html(products.map(function (product) {
      return (
        '<a href="' + escapeHtml(product.url) + '" class="d-block w-100 js-search-hit">' +
          '<div class="product-item d-flex align-items-center">' +
            '<div class="img flex-shrink-0 overflow-hidden">' +
              '<img src="' + escapeHtml(product.image) + '" alt="" loading="lazy" class="img-fluid w-100">' +
            '</div>' +
            '<div class="text">' +
              (product.category ? '<span class="search-hit__cat">' + escapeHtml(product.category) + '</span>' : '') +
              '<h4 class="name">' + highlight(product.title, query) + '</h4>' +
              '<p class="price">' + priceHtml(product) + '</p>' +
            '</div>' +
          '</div>' +
        '</a>'
      );
    }).join(""));

    if (data.see_all_url) {
      $seeAll.find("a").attr("href", data.see_all_url)
        .find(".su-text").text(data.total > products.length ? "SEE ALL " + data.total + " PRODUCTS" : "SEE ALL PRODUCTS");
      $seeAll.show();
    } else {
      $seeAll.hide();
    }
  }

  // Instant: show only the category suggestions that contain what was typed.
  function filterSuggestions($wrap, query) {
    var q = normalise(query);
    var shown = 0;
    $wrap.find(".js-search-suggestions li").each(function () {
      var $a = $(this).find(".js-search-suggestion");
      var label = String($a.data("query") || $a.text());
      if (!$a.data("label")) $a.data("label", label);
      var match = !q || label.toLowerCase().indexOf(q) !== -1 ||
        q.split(" ").every(function (w) { return label.toLowerCase().indexOf(w) !== -1; });
      $(this).toggle(match);
      $a.html(q ? highlight($a.data("label"), q) : escapeHtml($a.data("label")));
      if (match) shown++;
    });
    $wrap.find(".suggestions").toggle(shown > 0);
  }

  function reset($wrap) {
    if (activeRequest) { activeRequest.abort(); activeRequest = null; }
    clearTimeout(timer);
    $wrap.removeClass("is-searching");
    $wrap.find(".js-search-results-title").text("TOP RESULTS");
    message($wrap, "Start typing to search products…");
    $wrap.find(".js-search-see-all").hide();
  }

  function search($wrap, rawQuery) {
    var query = $.trim(String(rawQuery || "")).replace(/\s+/g, " ");
    var key = normalise(query);
    filterSuggestions($wrap, query);
    if (!key) { reset($wrap); return; }

    // Memoised answer: show it now, no request, no waiting.
    if (cache.has(key)) {
      clearTimeout(timer);
      if (activeRequest) { activeRequest.abort(); activeRequest = null; }
      $wrap.removeClass("is-searching");
      render($wrap, cache.get(key), query);
      return;
    }

    // Debounce: wait for a pause in typing before asking the server.
    $wrap.addClass("is-searching");
    // Skeleton rows while the first results for this query load (existing results just dim).
    if (!$wrap.find(".js-search-hit").length) {
      $wrap.find(".js-search-results").html(new Array(4).join(
        '<div class="search-skel" aria-hidden="true"><span class="search-skel__img"></span><span class="search-skel__text"><span></span><span></span></span></div>'
      ));
    }
    clearTimeout(timer);
    timer = setTimeout(function () {
      var mine = ++sequence;
      if (activeRequest) activeRequest.abort();
      activeRequest = $.ajax({
        url: $wrap.data("search-url"),
        method: "GET",
        dataType: "json",
        data: { q: query },
        headers: { Accept: "application/json" }
      }).done(function (data) {
        remember(key, data);
        if (mine !== sequence) return;                 // a newer search has started
        if (normalise($wrap.find(".js-frontend-search-input").val()) !== key) return;
        render($wrap, data, query);
      }).fail(function (xhr) {
        if (xhr.statusText === "abort" || mine !== sequence) return;
        $wrap.find(".js-search-results-title").text("TOP RESULTS");
        message($wrap, "Unable to search right now. Press Enter to see all results.");
        $wrap.find(".js-search-see-all").hide();
      }).always(function () {
        if (mine === sequence) { $wrap.removeClass("is-searching"); activeRequest = null; }
      });
    }, DEBOUNCE_MS);
  }

  function hits($wrap) { return $wrap.find(".js-search-hit"); }

  function moveActive($wrap, step) {
    var $hits = hits($wrap);
    if (!$hits.length) return;
    var i = $hits.index($hits.filter(".is-active"));
    i = i === -1 ? (step > 0 ? 0 : $hits.length - 1) : (i + step + $hits.length) % $hits.length;
    $hits.removeClass("is-active").eq(i).addClass("is-active")[0].scrollIntoView({ block: "nearest" });
  }

  $(function () {
    $(document).on("input", ".js-frontend-search-input", function () {
      search($(this).closest(".search-16-wrap"), $(this).val());
    });

    $(document).on("keydown", ".js-frontend-search-input", function (e) {
      var $wrap = $(this).closest(".search-16-wrap");
      if (e.key === "ArrowDown") { e.preventDefault(); moveActive($wrap, 1); }
      else if (e.key === "ArrowUp") { e.preventDefault(); moveActive($wrap, -1); }
      else if (e.key === "Enter") {
        var $active = hits($wrap).filter(".is-active");
        if ($active.length) { e.preventDefault(); window.location.href = $active.attr("href"); return; }
        // Typed a category name exactly (e.g. "murti"): go to that category.
        var typed = normalise($(this).val());
        var $cat = $wrap.find(".js-search-suggestion").filter(function () {
          return normalise($(this).data("query")) === typed;
        }).first();
        if ($cat.length) { e.preventDefault(); window.location.href = $cat.attr("href"); }
      } else if (e.key === "Escape") {
        $wrap.removeClass("show");
      }
    });

    $(document).on("mouseenter", ".js-search-hit", function () {
      $(this).addClass("is-active").siblings().removeClass("is-active");
    });

    // Clicking a suggestion (a category name, e.g. "Murti") opens that category's page —
    // the link's normal behaviour, so nothing to intercept here.

    $(document).on("submit", ".js-frontend-search-form", function (e) {
      if (!normalise($(this).find(".js-frontend-search-input").val())) e.preventDefault();
    });

    // Put the cursor in the box as soon as the panel opens.
    $(document).on("click", ".cart-search-btn, .js-mm-search, .account-search-link", function () {
      setTimeout(function () {
        var input = document.querySelector(".search-16-wrap.show .js-frontend-search-input");
        if (input) input.focus();
      }, 120);
    });
  });
})(jQuery);
