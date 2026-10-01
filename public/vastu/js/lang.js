/**
 * Language menu: English (default) · हिंदी · मराठी.
 * English is the page as written; Google's translation engine is loaded only after a visitor
 * picks Hindi or Marathi. The choice carries across pages opened by clicking links in the same tab,
 * but a refresh, a new tab or a new visit always starts in English again.
 */
(function () {
  'use strict';

  var LANGS = { en: ['English', 'EN'], hi: ['हिंदी', 'हि'], mr: ['मराठी', 'म'] };

  function current() {
    var m = document.cookie.match(/(?:^|;\s*)googtrans=\/en\/(hi|mr)(?:;|$)/);
    return m ? m[1] : 'en';
  }

  // Cookie for this host and for the parent domain (Google may set either).
  function setCookie(value, expires) {
    var host = location.hostname;
    var base = 'googtrans=' + value + '; path=/; SameSite=Lax' + (expires ? '; expires=' + expires : '');
    document.cookie = base;
    if (host.indexOf('.') > 0 && !/^\d+\.\d+\.\d+\.\d+$/.test(host)) {
      document.cookie = base + '; domain=' + host;
      document.cookie = base + '; domain=.' + host.split('.').slice(-2).join('.');
    }
  }

  var KEY = 'vt-lang';
  function remember(lang) {
    try { if (lang === 'en') sessionStorage.removeItem(KEY); else sessionStorage.setItem(KEY, lang); } catch (e) {}
  }
  function remembered() {
    try { return sessionStorage.getItem(KEY); } catch (e) { return null; }
  }
  function clearChoice() { setCookie('', 'Thu, 01 Jan 1970 00:00:00 GMT'); remember('en'); }

  function choose(lang) {
    if (lang === current()) return;
    if (lang === 'en') clearChoice();
    else { setCookie('/en/' + lang); remember(lang); }
    // Not location.reload(): a reload means "back to English" (see init), this is a fresh page load.
    location.replace(location.href.split('#')[0]);
  }

  // English by default: keep Hindi/Marathi only when the visitor chose it in this tab and then
  // followed a link. A refresh (or a new tab / new visit) resets the page to English.
  function resetIfNeeded() {
    var lang = current();
    if (lang === 'en') return 'en';
    var nav = (performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || {};
    if (nav.type === 'reload' || remembered() !== lang) { clearChoice(); return 'en'; }
    return lang;
  }

  // Hindi / Marathi helpers ------------------------------------------------------------
  // Google translates the page as it loads; text the site's scripts write later (bag drawer, notices,
  // button states) is translated here from a built-in phrase list instead, which is instant and
  // reliable. Quantity boxes and counts show Devanagari digits; scripts still use normal numbers.
  var DIGITS = '०१२३४५६७८९';
  function toLocal(v) { return String(v == null ? '' : v).replace(/[0-9]/g, function (d) { return DIGITS[d]; }); }
  function toLatin(v) { return String(v == null ? '' : v).replace(/[०-९]/g, function (d) { return String(DIGITS.indexOf(d)); }); }
  var valueDesc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');

  function localizeNumber(input) {
    if (input.__vtLocal) return;
    input.__vtLocal = true;
    var current = valueDesc.get.call(input);
    if (input.type === 'number') { input.type = 'text'; input.setAttribute('inputmode', 'numeric'); }
    Object.defineProperty(input, 'value', {
      configurable: true,
      get: function () { return toLatin(valueDesc.get.call(this)); },
      set: function (v) { valueDesc.set.call(this, toLocal(v)); }
    });
    valueDesc.set.call(input, toLocal(current));
    input.addEventListener('input', function () { valueDesc.set.call(this, toLocal(valueDesc.get.call(this))); });
  }

  // Phrases the site's scripts write after the page has loaded (Google only translates the page as
  // it loads). Keys are the exact English text; anything not listed is left for the engine.
  var PHRASES = {
    hi: {
      'Add to cart': 'कार्ट में जोड़ें', 'Open cart': 'कार्ट खोलें', 'Added to cart ✓': 'कार्ट में जोड़ा गया ✓',
      'Added to cart.': 'कार्ट में जोड़ा गया।', 'Adding…': 'जोड़ा जा रहा है…', 'ADDING…': 'जोड़ा जा रहा है…',
      'Apply': 'लागू करें', 'Applying…': 'लागू किया जा रहा है…', 'Cart updated.': 'कार्ट अपडेट हो गया।',
      'Could not add to cart.': 'कार्ट में नहीं जोड़ा जा सका।', 'Could not apply coupon.': 'कूपन लागू नहीं हो सका।',
      'Could not continue to checkout.': 'चेकआउट जारी नहीं रखा जा सका।', 'Could not remove coupon.': 'कूपन हटाया नहीं जा सका।',
      'Could not remove item.': 'आइटम हटाया नहीं जा सका।', 'Could not update cart.': 'कार्ट अपडेट नहीं हो सका।',
      'Coupon applied.': 'कूपन लागू हो गया।', 'Coupon removed.': 'कूपन हटा दिया गया।', 'Discount': 'छूट',
      'Enter a coupon code.': 'कूपन कोड दर्ज करें।', 'Free shipping applied.': 'मुफ़्त शिपिंग लागू।', 'Free': 'मुफ़्त',
      'Network error. Please try again.': 'नेटवर्क त्रुटि। कृपया फिर से प्रयास करें।',
      'PLEASE WAIT…': 'कृपया प्रतीक्षा करें…', 'Please wait…': 'कृपया प्रतीक्षा करें…', 'Remove': 'हटाएँ',
      'Removed from cart.': 'कार्ट से हटा दिया गया।', 'Removing…': 'हटाया जा रहा है…', 'Updating cart…': 'कार्ट अपडेट हो रहा है…',
      'This item will be removed from your bag.': 'यह आइटम आपके बैग से हटा दिया जाएगा।',
      'Your bag is empty.': 'आपका बैग खाली है।', 'Explore the shop →': 'दुकान देखें →', 'Shopping bag': 'शॉपिंग बैग',
      'Remove this item?': 'यह आइटम हटाएँ?', 'Keep item': 'आइटम रखें', 'Cart item not found.': 'कार्ट आइटम नहीं मिला।',
      'This product option is out of stock.': 'यह विकल्प स्टॉक में नहीं है।',
      'This product is available on request. Please enquire and our team will help you.': 'यह उत्पाद अनुरोध पर उपलब्ध है। कृपया पूछताछ करें, हमारी टीम आपकी मदद करेगी।',
      'Subscribing…': 'सदस्यता ली जा रही है…', 'Thanks for subscribing!': 'सदस्यता लेने के लिए धन्यवाद!',
      'Please enter your email address.': 'कृपया अपना ईमेल पता दर्ज करें।', 'Please enter a valid email address.': 'कृपया सही ईमेल पता दर्ज करें।',
      'A product in your cart is no longer available.': 'आपके कार्ट का एक उत्पाद अब उपलब्ध नहीं है।', 'Invalid coupon code.': 'अमान्य कूपन कोड।',
      'Please log in to use this member offer.': 'इस सदस्य ऑफ़र के लिए कृपया लॉग इन करें।',
      'This coupon does not apply to items in your cart.': 'यह कूपन आपके कार्ट के आइटम पर लागू नहीं होता।',
      'This coupon does not provide a discount for your cart.': 'यह कूपन आपके कार्ट पर कोई छूट नहीं देता।',
      'This coupon has expired.': 'यह कूपन समाप्त हो गया है।', 'This coupon has reached its usage limit.': 'यह कूपन अपनी उपयोग सीमा तक पहुँच गया है।',
      'This coupon is not active yet.': 'यह कूपन अभी सक्रिय नहीं है।', 'This offer is only for first-time customers.': 'यह ऑफ़र केवल पहली बार खरीदने वाले ग्राहकों के लिए है।',
      'You have already used this coupon.': 'आप यह कूपन पहले ही उपयोग कर चुके हैं।', 'Your cart is empty.': 'आपका कार्ट खाली है।',
      'This email is already registered. Please log in to continue checkout.': 'यह ईमेल पहले से पंजीकृत है। चेकआउट जारी रखने के लिए कृपया लॉग इन करें।',
      'Order amount is too low for payment.': 'भुगतान के लिए ऑर्डर राशि बहुत कम है।', 'Payment signature verification failed.': 'भुगतान सत्यापन विफल रहा।'
    },
    mr: {
      'Add to cart': 'कार्टमध्ये जोडा', 'Open cart': 'कार्ट उघडा', 'Added to cart ✓': 'कार्टमध्ये जोडले ✓',
      'Added to cart.': 'कार्टमध्ये जोडले.', 'Adding…': 'जोडत आहे…', 'ADDING…': 'जोडत आहे…',
      'Apply': 'लागू करा', 'Applying…': 'लागू करत आहे…', 'Cart updated.': 'कार्ट अपडेट झाले.',
      'Could not add to cart.': 'कार्टमध्ये जोडता आले नाही.', 'Could not apply coupon.': 'कूपन लागू करता आले नाही.',
      'Could not continue to checkout.': 'चेकआउट सुरू ठेवता आले नाही.', 'Could not remove coupon.': 'कूपन काढता आले नाही.',
      'Could not remove item.': 'वस्तू काढता आली नाही.', 'Could not update cart.': 'कार्ट अपडेट करता आले नाही.',
      'Coupon applied.': 'कूपन लागू झाले.', 'Coupon removed.': 'कूपन काढले.', 'Discount': 'सवलत',
      'Enter a coupon code.': 'कूपन कोड टाका.', 'Free shipping applied.': 'मोफत शिपिंग लागू.', 'Free': 'मोफत',
      'Network error. Please try again.': 'नेटवर्क त्रुटी. कृपया पुन्हा प्रयत्न करा.',
      'PLEASE WAIT…': 'कृपया प्रतीक्षा करा…', 'Please wait…': 'कृपया प्रतीक्षा करा…', 'Remove': 'काढा',
      'Removed from cart.': 'कार्टमधून काढले.', 'Removing…': 'काढत आहे…', 'Updating cart…': 'कार्ट अपडेट करत आहे…',
      'This item will be removed from your bag.': 'ही वस्तू तुमच्या बॅगमधून काढली जाईल.',
      'Your bag is empty.': 'तुमची बॅग रिकामी आहे.', 'Explore the shop →': 'दुकान पहा →', 'Shopping bag': 'शॉपिंग बॅग',
      'Remove this item?': 'ही वस्तू काढायची?', 'Keep item': 'वस्तू ठेवा', 'Cart item not found.': 'कार्टमधील वस्तू सापडली नाही.',
      'This product option is out of stock.': 'हा पर्याय स्टॉकमध्ये नाही.',
      'This product is available on request. Please enquire and our team will help you.': 'हे उत्पादन मागणीनुसार उपलब्ध आहे. कृपया चौकशी करा, आमची टीम तुम्हाला मदत करेल.',
      'Subscribing…': 'सदस्यता घेत आहे…', 'Thanks for subscribing!': 'सदस्यता घेतल्याबद्दल धन्यवाद!',
      'Please enter your email address.': 'कृपया तुमचा ईमेल पत्ता टाका.', 'Please enter a valid email address.': 'कृपया योग्य ईमेल पत्ता टाका.',
      'A product in your cart is no longer available.': 'तुमच्या कार्टमधील एक उत्पादन आता उपलब्ध नाही.', 'Invalid coupon code.': 'अवैध कूपन कोड.',
      'Please log in to use this member offer.': 'ही सदस्य ऑफर वापरण्यासाठी कृपया लॉग इन करा.',
      'This coupon does not apply to items in your cart.': 'हे कूपन तुमच्या कार्टमधील वस्तूंवर लागू होत नाही.',
      'This coupon does not provide a discount for your cart.': 'हे कूपन तुमच्या कार्टवर कोणतीही सवलत देत नाही.',
      'This coupon has expired.': 'हे कूपन कालबाह्य झाले आहे.', 'This coupon has reached its usage limit.': 'या कूपनची वापर मर्यादा संपली आहे.',
      'This coupon is not active yet.': 'हे कूपन अजून सक्रिय नाही.', 'This offer is only for first-time customers.': 'ही ऑफर फक्त पहिल्यांदा खरेदी करणाऱ्या ग्राहकांसाठी आहे.',
      'You have already used this coupon.': 'तुम्ही हे कूपन आधीच वापरले आहे.', 'Your cart is empty.': 'तुमचे कार्ट रिकामे आहे.',
      'This email is already registered. Please log in to continue checkout.': 'हा ईमेल आधीच नोंदणीकृत आहे. चेकआउट सुरू ठेवण्यासाठी कृपया लॉग इन करा.',
      'Order amount is too low for payment.': 'पेमेंटसाठी ऑर्डरची रक्कम खूप कमी आहे.', 'Payment signature verification failed.': 'पेमेंट पडताळणी अयशस्वी झाली.'
    }
  };

  // Messages with numbers or names in them (from the server): pattern → sentence per language.
  var PATTERNS = {
    hi: [
      [/^Only (\d+) item\(s\) are available for this product option\.$/, function (m) { return 'इस विकल्प के लिए केवल ' + toLocal(m[1]) + ' आइटम उपलब्ध हैं।'; }],
      [/^Only (\d+) item\(s\) of (.+) are available\.$/, function (m) { return '“' + nameOf(m[2]) + '” के केवल ' + toLocal(m[1]) + ' आइटम उपलब्ध हैं।'; }],
      [/^(.+) is out of stock\.$/, function (m) { return '“' + nameOf(m[1]) + '” स्टॉक में नहीं है।'; }],
      [/^Add at least (\d+) eligible item\(s\) to use this coupon\.$/, function (m) { return 'यह कूपन उपयोग करने के लिए कम से कम ' + toLocal(m[1]) + ' योग्य आइटम जोड़ें।'; }],
      [/^Add (\d+) matching eligible item\(s\) to use this Buy X, Get Y offer\.$/, function (m) { return 'यह ऑफ़र पाने के लिए ' + toLocal(m[1]) + ' योग्य आइटम और जोड़ें।'; }],
      [/^Minimum cart amount for this coupon is (.+)\.$/, function (m) { return 'इस कूपन के लिए न्यूनतम कार्ट राशि ' + toLocal(m[1]) + ' है।'; }]
    ],
    mr: [
      [/^Only (\d+) item\(s\) are available for this product option\.$/, function (m) { return 'या पर्यायासाठी फक्त ' + toLocal(m[1]) + ' वस्तू उपलब्ध आहेत.'; }],
      [/^Only (\d+) item\(s\) of (.+) are available\.$/, function (m) { return '“' + nameOf(m[2]) + '” च्या फक्त ' + toLocal(m[1]) + ' वस्तू उपलब्ध आहेत.'; }],
      [/^(.+) is out of stock\.$/, function (m) { return '“' + nameOf(m[1]) + '” स्टॉकमध्ये नाही.'; }],
      [/^Add at least (\d+) eligible item\(s\) to use this coupon\.$/, function (m) { return 'हे कूपन वापरण्यासाठी किमान ' + toLocal(m[1]) + ' पात्र वस्तू जोडा.'; }],
      [/^Add (\d+) matching eligible item\(s\) to use this Buy X, Get Y offer\.$/, function (m) { return 'ही ऑफर मिळवण्यासाठी आणखी ' + toLocal(m[1]) + ' पात्र वस्तू जोडा.'; }],
      [/^Minimum cart amount for this coupon is (.+)\.$/, function (m) { return 'या कूपनसाठी किमान कार्ट रक्कम ' + toLocal(m[1]) + ' आहे.'; }]
    ]
  };
  function nameOf(n) { return names[String(n).trim()] || n; }

  var LANG = resetIfNeeded();

  // Product names: Google translates them where the page shows them (product page title, tiles);
  // elements carrying data-vt-orig="English name" let us remember those translations for this visit
  // and reuse them in the bag drawer and pop-ups.
  var NAMES_KEY = 'vt-names-' + LANG;
  var names = {};
  try { names = JSON.parse(sessionStorage.getItem(NAMES_KEY) || '{}') || {}; } catch (e) {}
  function learnNames() {
    var changed = false;
    document.querySelectorAll('[data-vt-orig]').forEach(function (el) {
      var orig = el.getAttribute('data-vt-orig');
      var shown = el.textContent.replace(/\s+/g, ' ').trim();
      if (orig && shown && shown !== orig && /[ऀ-ॿ]/.test(shown) && names[orig] !== shown) { names[orig] = shown; changed = true; }
    });
    if (changed) { try { sessionStorage.setItem(NAMES_KEY, JSON.stringify(names)); } catch (e) {} }
    return changed;
  }
  function tr(text) {
    if (LANG === 'en' || text == null) return text;
    var str = String(text), key = str.trim();
    var hit = (PHRASES[LANG] || {})[key] || names[key];
    if (hit) return str.replace(key, hit);
    var list = PATTERNS[LANG] || [];
    for (var i = 0; i < list.length; i++) {
      var m = key.match(list[i][0]);
      if (m) return str.replace(key, list[i][1](m));
    }
    return str;
  }
  function num(text) { return LANG === 'en' ? String(text) : toLocal(text); }
  function strong(v) { return '<strong>' + v + '</strong>'; }
  var SENTENCES = {
    hi: {
      shipOver: function (a) { return strong(a) + ' से अधिक के ऑर्डर पर मुफ़्त शिपिंग'; },
      shipAway: function (a) { return 'मुफ़्त शिपिंग के लिए बस ' + strong(a) + ' और'; },
      shipUnlocked: function () { return 'आपको ' + strong('मुफ़्त शिपिंग') + ' मिल गई है'; },
      removeText: function (n) { return '“' + n + '” आपके बैग से हटा दिया जाएगा।'; },
      inCart: function (q) { return 'आपके कार्ट में ' + q; },
      pack: function (l) { return 'पैक: ' + l; },
      items: function (n) { return 'आइटम (' + n + ')'; },
      orderNext: function (n) { return 'अगली खरीदारी के लिए ' + strong(n) + ' सेकंड में आपको होम पेज पर ले जाया जाएगा।'; }
    },
    mr: {
      shipOver: function (a) { return strong(a) + ' पेक्षा जास्त किमतीच्या ऑर्डरवर मोफत शिपिंग'; },
      shipAway: function (a) { return 'मोफत शिपिंगसाठी फक्त ' + strong(a) + ' बाकी'; },
      shipUnlocked: function () { return 'तुम्हाला ' + strong('मोफत शिपिंग') + ' मिळाली आहे'; },
      removeText: function (n) { return '“' + n + '” तुमच्या बॅगमधून काढले जाईल.'; },
      inCart: function (q) { return 'तुमच्या कार्टमध्ये ' + q; },
      pack: function (l) { return 'पॅक: ' + l; },
      items: function (n) { return 'वस्तू (' + n + ')'; },
      orderNext: function (n) { return 'पुढील खरेदीसाठी ' + strong(n) + ' सेकंदांत तुम्हाला मुख्य पृष्ठावर नेले जाईल.'; }
    }
  };
  // Public helpers for the site's scripts: vtLang.say('shipAway', '₹649') returns HTML in the
  // current language, or null in English (the caller then keeps its own English text).
  window.vtLang = {
    lang: LANG,
    t: tr,
    num: num,
    name: function (n) { return LANG === 'en' ? n : (names[String(n).trim()] || n); },
    say: function (key) {
      var set = SENTENCES[LANG];
      if (!set || !set[key]) return null;
      var args = Array.prototype.slice.call(arguments, 1).map(function (a) { return typeof a === 'string' ? num(a) : a; });
      return set[key].apply(null, args);
    }
  };

  // Text nodes the scripts write: phrase → translation, bare prices / counts → Devanagari digits.
  var MONEY = /^\s*(₹\s?[\d,]+(\.\d+)?|\(\d+\))\s*$/;
  function translateTextNode(node) {
    var parent = node.parentElement;
    if (!parent || parent.closest('script,style,textarea,.notranslate,[translate="no"]')) return;
    var v = node.nodeValue, out = tr(v);
    if (out === v && MONEY.test(v)) out = toLocal(v);
    if (out !== v) node.nodeValue = out;
  }
  function translateTree(root) {
    if (root.nodeType === 3) { translateTextNode(root); return; }
    if (root.nodeType !== 1) return;
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    var n; var list = [];
    while ((n = walker.nextNode())) list.push(n);
    list.forEach(translateTextNode);
  }

  function languageHelpers() {
    var NUM = 'input[type="number"], input.quantity-num, input[data-vt-qty]';
    function scan(root) {
      if (!root.querySelectorAll) return;
      if (root.matches && root.matches(NUM)) localizeNumber(root);
      root.querySelectorAll(NUM).forEach(localizeNumber);
    }
    // Numbers scripts read back from text (product quantity, bag count): keep the real digits and
    // show Devanagari ones through CSS (data-vt-num + ::after in vastu.css).
    var SHOWN = '[data-product-qty-value], [data-cart-count]';
    function mirror(el) {
      if (!el.classList.contains('notranslate')) { el.classList.add('notranslate'); el.setAttribute('translate', 'no'); }
      var v = toLocal(toLatin(el.textContent.trim()));
      if (el.getAttribute('data-vt-num') !== v) el.setAttribute('data-vt-num', v);
    }
    function mirrorAll() { document.querySelectorAll(SHOWN).forEach(mirror); }
    // Account badge initials ("JD") shown as their Devanagari letter names ("जेडी").
    var LETTERS = {
      A: 'ए', B: 'बी', C: 'सी', D: 'डी', E: 'ई', F: 'एफ', G: 'जी', H: 'एच', I: 'आय', J: 'जे', K: 'के', L: 'एल', M: 'एम',
      N: 'एन', O: 'ओ', P: 'पी', Q: 'क्यू', R: 'आर', S: 'एस', T: 'टी', U: 'यू', V: LANG === 'hi' ? 'वी' : 'व्ही',
      W: 'डब्ल्यू', X: 'एक्स', Y: 'वाय', Z: LANG === 'hi' ? 'ज़ेड' : 'झेड'
    };
    function localInitials(el) {
      var orig = el.getAttribute('data-vt-initials');
      if (orig === null) { orig = el.textContent.trim(); el.setAttribute('data-vt-initials', orig); }
      if (!/^[A-Za-z]{1,2}$/.test(orig)) return;              // already Devanagari (or empty): leave it
      var out = orig.toUpperCase().split('').map(function (c) { return LETTERS[c] || c; }).join('');
      if (el.textContent !== out) el.textContent = out;
      el.classList.toggle('is-long', out.length > 4);
    }
    function initialsAll() { document.querySelectorAll('.site-account-avatar').forEach(localInitials); }
    scan(document.body);
    mirrorAll();
    initialsAll();
    // Let the engine do its first pass over the page, then take over script-written text.
    var settled = false;
    setTimeout(function () { settled = true; learnNames(); translateTree(document.body); }, 3500);
    // Newly learned names also apply to copies already on the page (drawer, phone-layout rows).
    setInterval(function () { if (settled && learnNames()) translateTree(document.body); }, 3000);
    new MutationObserver(function (records) {
      var numbers = false;
      records.forEach(function (r) {
        if (r.type === 'childList') r.addedNodes.forEach(function (n) { if (n.nodeType === 1) scan(n); if (settled) translateTree(n); });
        else if (r.type === 'characterData' && settled) translateTextNode(r.target);
        var t = r.target.nodeType === 1 ? r.target : r.target.parentElement;
        if (t && t.closest && t.closest(SHOWN)) numbers = true;
        else if (r.type === 'childList' && t && t.querySelector && t.querySelector(SHOWN)) numbers = true;
      });
      if (numbers) mirrorAll();
      if (records.some(function (r) { return r.type === 'childList' && Array.prototype.some.call(r.addedNodes, function (n) { return n.nodeType === 1 && (n.matches('.site-account-avatar') || n.querySelector('.site-account-avatar')); }); })) initialsAll();
    }).observe(document.body, { subtree: true, childList: true, characterData: true });
  }

  function loadEngine() {
    window.vtTranslateInit = function () {
      /* global google */
      new google.translate.TranslateElement({ pageLanguage: 'en', includedLanguages: 'en,hi,mr', autoDisplay: false }, 'vt-gt');
    };
    var s = document.createElement('script');
    s.src = 'https://translate.google.com/translate_a/element.js?cb=vtTranslateInit';
    s.async = true;
    document.head.appendChild(s);
  }

  function init() {
    var lang = LANG;
    document.querySelectorAll('[data-vt-lang]').forEach(function (box) {
      var btn = box.querySelector('[data-vt-lang-btn]');
      var menu = box.querySelector('.vt-lang__menu');
      box.querySelector('[data-vt-lang-long]').textContent = LANGS[lang][0];
      box.querySelector('[data-vt-lang-short]').textContent = LANGS[lang][1];
      btn.setAttribute('aria-label', 'Language: ' + LANGS[lang][0] + ' — change language');
      menu.querySelectorAll('[data-lang]').forEach(function (item) {
        item.setAttribute('aria-checked', item.getAttribute('data-lang') === lang ? 'true' : 'false');
        item.addEventListener('click', function () { choose(item.getAttribute('data-lang')); });
      });

      function open(show) {
        menu.hidden = !show;
        btn.setAttribute('aria-expanded', show ? 'true' : 'false');
        if (show) (menu.querySelector('[aria-checked="true"]') || menu.querySelector('button')).focus();
      }
      btn.addEventListener('click', function (e) { e.stopPropagation(); open(menu.hidden); });
      document.addEventListener('click', function (e) { if (!box.contains(e.target)) open(false); });
      box.addEventListener('keydown', function (e) {
        var items = Array.prototype.slice.call(menu.querySelectorAll('button'));
        var i = items.indexOf(document.activeElement);
        if (e.key === 'Escape') { open(false); btn.focus(); }
        else if (e.key === 'ArrowDown' && !menu.hidden) { e.preventDefault(); items[(i + 1) % items.length].focus(); }
        else if (e.key === 'ArrowUp' && !menu.hidden) { e.preventDefault(); items[(i - 1 + items.length) % items.length].focus(); }
      });
    });
    document.documentElement.setAttribute('data-lang', lang);
    if (lang !== 'en') { loadEngine(); languageHelpers(); }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
