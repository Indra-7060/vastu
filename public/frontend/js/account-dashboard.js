(function () {
  'use strict';

  var app = document.getElementById('account-app') || document.body;
  var page = (app.dataset && app.dataset.accountPage) || document.body.dataset.accountPage || 'overview';
  var R = window.PP_ACCOUNT_ROUTES || {};
  var store = window.PPAccountStore;
  var data = store.get();
  var asset = store.asset || function (p) { return p; };

  var navItems = [
    ['overview', 'Overview', 'fa-grid-2'],
    ['orders', 'Order History', 'fa-bag-shopping'],
    ['information', 'Information', 'fa-user'],
    ['addresses', 'Addresses', 'fa-location-dot'],
    ['wishlist', 'Wishlist', 'fa-heart'],
    ['reviews', 'My Reviews', 'fa-star'],
    ['notifications', 'Notifications', 'fa-bell']
  ];

  function unreadCount() {
    return (data.notifications || []).filter(function (n) { return !n.read; }).length;
  }

  function accountUrl(slug) {
    return String(R.accountBase || '/account').replace(/\/?$/, '') + '/' + slug;
  }

  function confirmAction(opts) {
    if (window.PPConfirm) return window.PPConfirm(opts);
    return Promise.resolve(window.confirm((opts && (opts.text || opts.title)) || 'Are you sure?'));
  }

  function esc(value) {
    var d = document.createElement('div');
    d.textContent = value == null ? '' : String(value);
    return d.innerHTML;
  }

  function money(value) {
    return value == null || value === '' ? 'Price not provided' : new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(Number(value));
  }

  function announce(message, type) {
    var el = document.querySelector('.account-feedback');
    if (!el) {
      el = document.createElement('div');
      el.className = 'account-feedback';
      el.setAttribute('role', 'status');
      var host = document.querySelector('.account-content') || app;
      if (host) host.insertBefore(el, host.firstChild);
      else document.body.appendChild(el);
    }
    if (!message) {
      el.hidden = true;
      el.textContent = '';
      el.className = 'account-feedback';
      return;
    }
    type = type === 'error' || type === 'failed' ? 'error' : (type === 'success' ? 'success' : 'info');
    el.className = 'account-feedback is-' + type + ' is-visible';
    el.innerHTML = '<span class="account-feedback-ico" aria-hidden="true">' +
      (type === 'success' ? '✓' : (type === 'error' ? '!' : 'i')) +
      '</span><span class="account-feedback-text"></span>';
    var text = el.querySelector('.account-feedback-text');
    if (text) text.textContent = message;
    else el.textContent = message;
    el.hidden = false;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    clearTimeout(el._hideTimer);
    el._hideTimer = window.setTimeout(function () {
      el.hidden = true;
      el.classList.remove('is-visible');
    }, type === 'error' ? 6000 : 4000);
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
  }

  function csrf() {
    return R.csrf || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  }

  function api(method, url, payload) {
    var opts = {
      method: method,
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    };
    if (payload !== undefined) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(payload);
    }
    return fetch(url, opts).then(function (response) {
      return response.json().then(function (body) {
        return { ok: response.ok, status: response.status, data: body };
      }).catch(function () {
        return { ok: response.ok, status: response.status, data: {} };
      });
    });
  }

  function setFieldError(form, name, message) {
    var input = form.querySelector('[name="' + name + '"]');
    if (!input) return;
    input.classList.toggle('is-invalid', !!message);
    var wrap = input.closest('label') || input.parentElement;
    var err = wrap ? wrap.querySelector('.field-error') : null;
    if (err) err.textContent = message || '';
  }

  function clearFormErrors(form) {
    form.querySelectorAll('.field-error').forEach(function (e) { e.textContent = ''; });
    form.querySelectorAll('.is-invalid').forEach(function (e) { e.classList.remove('is-invalid'); });
  }

  function applyServerErrors(form, errors) {
    Object.keys(errors || {}).forEach(function (key) {
      var msg = Array.isArray(errors[key]) ? errors[key][0] : String(errors[key]);
      var map = {
        first_name: 'firstName',
        last_name: 'lastName',
        birth_date: 'birthDate',
        current_password: 'currentPassword',
        new_password: 'newPassword',
        new_password_confirmation: 'newPasswordConfirmation',
        address_line1: 'line1',
        pincode: 'postcode',
        label: 'type',
        is_default: 'default',
        name: 'full_name'
      };
      setFieldError(form, map[key] || key, msg);
    });
  }

  function header() {
    return '';
  }

  function footer() {
    return '';
  }

  function navigation() {
    return '<button class="account-nav-toggle" type="button" aria-expanded="false" aria-controls="accountNav">Account menu <span>+</span></button>' +
      '<nav id="accountNav" class="account-nav" aria-label="Customer account"><h2>Account</h2>' +
      navItems.map(function (item) {
        return '<a href="' + esc(accountUrl(item[0])) + '"' + (item[0] === page ? ' class="active" aria-current="page"' : '') + '>' +
          '<i class="far ' + item[2] + '" aria-hidden="true"></i><span>' + item[1] + '</span>' +
          (item[0] === 'notifications' && unreadCount() && page !== 'notifications' ? '<b class="account-nav-count" aria-label="' + unreadCount() + ' unread">' + unreadCount() + '</b>' : '') + '</a>';
      }).join('') +
      '<button type="button" data-action="logout"><i class="far fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Logout</span></button></nav>';
  }

  function panel(title, body, action) {
    return '<section class="account-panel"><div class="account-panel-head"><h2>' + title + '</h2>' + (action || '') + '</div>' + body + '</section>';
  }

  function empty(title, copy) {
    return '<div class="account-empty"><span aria-hidden="true">◇</span><h3>' + title + '</h3><p>' + copy + '</p></div>';
  }

  function findOrder(id) {
    return (data.orders || []).find(function (o) { return String(o.id) === String(id); });
  }

  function orderItemsTable(items) {
    var rows = (items || []).map(function (item) {
      var meta = '';
      if (item.color || item.size) {
        meta = '<span>' +
          esc((item.color ? ('Colour: ' + item.color) : '') + (item.color && item.size ? ' · ' : '') + (item.size ? ('Size: ' + item.size) : '')) +
          '</span>';
      }
      if (item.package_label) {
        meta += '<span>' + esc('Pack: ' + item.package_label) + '</span>';
      }
      return '<tr>' +
        '<td class="account-order-item-cell"><img src="' + esc(item.image) + '" alt="" width="52" height="68"><div><strong>' + esc(item.title) + '</strong>' + meta + '<span>' + esc(item.status || '') + '</span></div></td>' +
        '<td data-label="Qty">' + esc(item.qty) + '</td>' +
        '<td data-label="Price">' + esc(item.price_formatted || money(item.price)) + '</td>' +
        '<td data-label="Total">' + esc(item.total_formatted || money(item.total)) + '</td>' +
        '</tr>';
    }).join('');
    return '<div class="account-table-wrap"><table class="account-order-table">' +
      '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>' +
      '<tbody>' + (rows || '<tr><td colspan="4">No items</td></tr>') + '</tbody></table></div>';
  }

  // Flipkart-style tracker: arrival date + Ordered → Packed → Shipped → Delivered with dates.
  function trackingHtml(o) {
    var t = o.tracking;
    if (!t || !t.steps) return '';
    var head;
    if (t.cancelled) head = '<p class="account-track-eta is-cancelled">Order cancelled' + (t.cancelled_on ? ' on ' + esc(t.cancelled_on) : '') + '</p>';
    else if (t.delivered) head = '<p class="account-track-eta is-done">Delivered' + (t.steps[3].date ? ' on ' + esc(t.steps[3].date) : '') + '</p>';
    else if (o.expected_delivery) head = '<p class="account-track-eta">Arriving by <strong>' + esc(o.expected_delivery) + '</strong></p>';
    else head = '<p class="account-track-eta">We will share the expected delivery date soon.</p>';
    var steps = t.steps.map(function (s) {
      return '<li class="account-track-step' + (s.done ? ' is-done' : '') + (s.current ? ' is-current' : '') + '">' +
        '<span class="account-track-dot" aria-hidden="true"></span>' +
        '<span class="account-track-label">' + esc(s.label) + '</span>' +
        '<span class="account-track-date">' + (s.date && s.done ? esc(s.date) : '&nbsp;') + '</span></li>';
    }).join('');
    return '<div class="account-track' + (t.cancelled ? ' is-cancelled' : '') + '">' + head +
      '<ol class="account-track-steps">' + steps + '</ol>' +
      (t.latest_note ? '<p class="account-track-note">' + esc(t.latest_note) + '</p>' : '') + '</div>';
  }

  function orderDetailHtml(o) {
    if (!o) return '';
    var ship = (o.shipping_lines || []).map(function (line) { return esc(line); }).join('<br>');
    return '<div class="account-order-detail" data-order-detail="' + esc(o.id) + '">' +
      '<div class="account-order-detail-head">' +
        '<div><p>Order details</p><h3>' + esc(o.number) + '</h3></div>' +
        '<button type="button" class="account-text-button" data-close-order>Close</button>' +
      '</div>' +
      '<div class="account-order-meta">' +
        '<div><span>Date</span><strong>' + esc(o.date_long || o.date) + '</strong></div>' +
        '<div><span>Status</span><strong class="account-status">' + esc(o.status) + '</strong></div>' +
        '<div><span>Payment</span><strong>' + esc(o.payment_mode) + (o.payment_status ? ' · <em class="account-pay account-pay--' + esc(String(o.payment_status).toLowerCase()) + '">' + esc(o.payment_status) + '</em>' : '') + '</strong></div>' +
        '<div><span>Total</span><strong>' + esc(o.total_formatted || money(o.total)) + '</strong></div>' +
      '</div>' +
      trackingHtml(o) +
      orderItemsTable(o.items) +
      '<div class="account-order-totals">' +
        '<div><span>Subtotal</span><strong>' + esc(o.subtotal_formatted || money(o.subtotal)) + '</strong></div>' +
        '<div><span>Shipping</span><strong>' + esc(o.shipping_formatted || money(o.shipping)) + '</strong></div>' +
        '<div class="is-grand"><span>' + (o.payment_status && o.payment_status !== 'Paid' ? 'Total' : 'Total paid') + '</span><strong>' + esc(o.total_formatted || money(o.total)) + '</strong></div>' +
      '</div>' +
      '<div class="account-order-ship">' +
        '<h4><i class="far fa-truck" aria-hidden="true"></i> Shipping to</h4>' +
        '<p><strong>' + esc(o.shipping_name || '') + '</strong><br>' + ship +
        (o.shipping_phone ? '<br>Phone: ' + esc(o.shipping_phone) : '') + '</p>' +
      '</div></div>';
  }

  function orderTable(orders, opts) {
    opts = opts || {};
    if (!orders.length) return empty('No orders yet', 'Your purchases will appear here after checkout.');
    var canOpen = opts.detail !== false;
    var rows = orders.map(function (o) {
      var totalLabel = o.total_formatted ? esc(o.total_formatted) : money(o.total);
      var openAttrs = canOpen
        ? ' class="account-order-row is-clickable" data-open-order="' + esc(o.id) + '" role="button" tabindex="0"'
        : ' class="account-order-row"';
      return '<tr' + openAttrs + '>' +
        '<td data-label="Order"><span class="account-order-id"><i class="far fa-receipt" aria-hidden="true"></i>' + esc(o.number) + '</span></td>' +
        '<td data-label="Date">' + esc(o.date) + '</td>' +
        '<td data-label="Items">' + esc(o.items_count || (o.items || []).length || 0) + '</td>' +
        '<td data-label="Status"><span class="account-status">' + esc(o.status) + '</span>' +
          (o.tracking && !o.tracking.delivered && !o.tracking.cancelled && o.expected_delivery ? '<small class="account-row-eta">Arriving ' + esc(o.expected_delivery) + '</small>' : '') + '</td>' +
        '<td data-label="Total"><strong>' + totalLabel + '</strong></td>' +
        (canOpen ? '<td data-label=""><button type="button" class="account-order-view" data-open-order="' + esc(o.id) + '">View</button></td>' : '') +
        '</tr>';
    }).join('');
    return '<div class="account-orders-panel">' +
      '<div class="account-table-wrap"><table class="account-orders-table">' +
      '<thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th>Total</th>' +
      (canOpen ? '<th></th>' : '') + '</tr></thead><tbody>' + rows + '</tbody></table></div>' +
      '<div id="accountOrderDetail" class="account-order-detail-host" hidden></div></div>';
  }

  function overview() {
    var summaries = [
      ['Orders', (data.orders || []).length, accountUrl('orders')],
      ['Addresses', (data.addresses || []).length, accountUrl('addresses')],
      ['Wishlist', (data.wishlist || []).length, accountUrl('wishlist')],
      ['Reviews', (data.reviews || []).length, accountUrl('reviews')]
    ];
    return '<div class="account-welcome"><p>WELCOME BACK</p><h1>Hello, ' + esc(data.customer.firstName) + '</h1><span>Manage your details, orders, wishlist, and reviews.</span></div>' +
      '<div class="account-summary">' + summaries.map(function (s) {
        return '<a href="' + esc(s[2]) + '"><strong>' + s[1] + '</strong><span>' + s[0] + '</span><small>View details →</small></a>';
      }).join('') + '</div>' +
      panel('Recent orders', orderTable((data.orders || []).slice(0, 3), { detail: true }), '<a href="' + esc(accountUrl('orders')) + '" class="account-text-button">View all</a>') +
      '<div class="account-quick"><a href="' + esc(accountUrl('information')) + '">Edit profile</a><a href="' + esc(accountUrl('addresses')) + '">Add an address</a><a href="' + esc(R.collection || '/collection') + '">Continue shopping</a></div>';
  }

  function currentPage() {
    var n = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
    return Number.isFinite(n) && n > 0 ? n : 1;
  }

  var PAGE_SIZE = 6;

  function paginateItems(items) {
    var list = items || [];
    var total = list.length;
    var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    var page = Math.min(currentPage(), pages);
    var start = (page - 1) * PAGE_SIZE;
    return {
      items: list.slice(start, start + PAGE_SIZE),
      page: page,
      pages: pages,
      total: total,
      from: total ? start + 1 : 0,
      to: Math.min(start + PAGE_SIZE, total)
    };
  }

  function paginationNav(meta) {
    if (!meta.total) return '';
    var count = '<p class="account-page-count">Showing ' + meta.from + '–' + meta.to + ' of ' + meta.total + '</p>';
    if (meta.pages <= 1) return '<div class="account-pagination-wrap">' + count + '</div>';

    var base = accountUrl(page);
    var html = '<nav class="account-pagination collection-pagination" aria-label="Pagination">';
    if (meta.page <= 1) html += '<span class="page-btn disabled">Prev</span>';
    else html += '<a class="page-btn" href="' + esc(base + '?page=' + (meta.page - 1)) + '">Prev</a>';

    for (var i = 1; i <= meta.pages; i++) {
      if (i === meta.page) html += '<span class="page-btn active" aria-current="page">' + i + '</span>';
      else html += '<a class="page-btn" href="' + esc(base + '?page=' + i) + '">' + i + '</a>';
    }

    if (meta.page >= meta.pages) html += '<span class="page-btn disabled">Next</span>';
    else html += '<a class="page-btn" href="' + esc(base + '?page=' + (meta.page + 1)) + '">Next</a>';
    html += '</nav>';
    return '<div class="account-pagination-wrap">' + count + html + '</div>';
  }

  function orders() {
    var meta = paginateItems(data.orders || []);
    return '<div class="account-title"><p>ACCOUNT</p><h1>Order History</h1></div>' +
      panel('All orders', orderTable(meta.items, { detail: true }) + paginationNav(meta));
  }

  function information() {
    var c = data.customer;
    return '<div class="account-title"><p>ACCOUNT</p><h1>Information</h1></div>' +
      panel('Contact', '<form id="informationForm" class="account-form" novalidate><div class="account-form-grid">' +
        '<label>First name<input name="firstName" value="' + esc(c.firstName) + '" required maxlength="120"><span class="field-error"></span></label>' +
        '<label>Last name<input name="lastName" value="' + esc(c.lastName) + '" maxlength="120"><span class="field-error"></span></label>' +
        '<label>Email address<input type="email" name="email" value="' + esc(c.email) + '" required><span class="field-error"></span></label>' +
        '<label>Phone number<input type="tel" name="phone" value="' + esc(c.phone) + '" pattern="[0-9 +()-]{7,20}"><span class="field-error"></span></label>' +
        '<label>Date of birth<input type="date" name="birthDate" value="' + esc(c.birthDate || '') + '"><span class="field-error"></span></label>' +
        '</div><hr><h3>Change password</h3><div class="account-form-grid">' +
        '<label>Current password<input type="password" name="currentPassword" autocomplete="current-password"><span class="field-error"></span></label>' +
        '<label>New password<input type="password" name="newPassword" minlength="6" autocomplete="new-password"><span class="field-error"></span></label>' +
        '<label>Confirm new password<input type="password" name="newPasswordConfirmation" minlength="6" autocomplete="new-password"><span class="field-error"></span></label>' +
        '</div><label class="account-check"><input type="checkbox" name="marketing"' + (c.marketing ? ' checked' : '') + '> Receive news and product updates</label>' +
        '<button class="account-primary" type="submit">Save Changes</button></form>');
  }

  function addressCard(a) {
    return '<article class="address-card" data-address-id="' + esc(a.id) + '"><div><span>' + esc(a.type || 'Shipping') + (a.default ? ' · Default' : '') + '</span><h3>' + esc(a.name) + '</h3><p>' + esc(a.line1) + '<br>' + esc(a.city) + ', ' + esc(a.postcode) + '<br>' + esc(a.country) + '</p></div><div><button type="button" data-edit-address="' + esc(a.id) + '">Edit</button><button type="button" data-default-address="' + esc(a.id) + '">Make default</button><button type="button" data-delete-address="' + esc(a.id) + '">Delete</button></div></article>';
  }

  function addresses() {
    return '<div class="account-title"><p>ACCOUNT</p><h1>Addresses</h1></div>' +
      panel('Saved addresses', '<div id="addressList" class="address-grid">' + ((data.addresses || []).length ? data.addresses.map(addressCard).join('') : empty('No addresses added', 'Add a billing or shipping address for a faster checkout.')) + '</div>', '<button class="account-text-button" type="button" data-add-address>Add</button>') +
      '<dialog id="addressDialog" class="account-dialog">' +
        '<form id="addressForm" class="account-form" method="post" action="#" novalidate>' +
          '<div class="account-panel-head"><h2 id="addressDialogTitle">Add address</h2><button type="button" data-close-address aria-label="Close">×</button></div>' +
          '<div id="addressFormAlert" class="account-form-alert" role="alert" hidden></div>' +
          '<input type="hidden" name="id" value="">' +
          '<label>Full name<input type="text" name="full_name" required maxlength="255" autocomplete="name"><span class="field-error"></span></label>' +
          '<label>Address<input type="text" name="line1" required maxlength="255" autocomplete="street-address"><span class="field-error"></span></label>' +
          '<div class="account-form-grid">' +
            '<label>City<input type="text" name="city" required maxlength="120" autocomplete="address-level2"><span class="field-error"></span></label>' +
            '<label>Postal code<input type="text" name="postcode" required maxlength="20" autocomplete="postal-code"><span class="field-error"></span></label>' +
          '</div>' +
          '<label>Country<input type="text" name="country" required maxlength="120" autocomplete="country-name"><span class="field-error"></span></label>' +
          '<label>Type<select name="type"><option value="Shipping">Shipping</option><option value="Billing">Billing</option></select><span class="field-error"></span></label>' +
          '<label class="account-check"><input type="checkbox" name="default" value="1"> Make default address</label>' +
          '<button class="account-primary" type="button" data-save-address>Save address</button>' +
        '</form>' +
      '</dialog>';
  }

  function productCard(p) {
    var discount = Number(p.discount_percent || 0);
    var hasSellingPrice = Number(p.price) > 0;
    var pricing = hasSellingPrice
      ? '<span class="pp-price pp-price--has-selling-price"><span class="pp-price__mrp">' + (discount ? '<s>' + money(p.mrp) + '</s>' : money(p.mrp)) + '</span>' + (discount ? '<span class="pp-price__discount">-' + discount + '%</span>' : '') + '<span class="pp-price__selling">' + money(p.price) + '</span></span>'
      : '<span class="pp-price pp-price--mrp-only"><span class="pp-price__mrp">' + money(p.mrp) + '</span></span>';
    return '<article class="account-product" data-wishlist-id="' + esc(p.id) + '"><a href="' + esc(p.url) + '"><img src="' + esc(p.image) + '" alt="' + esc(p.name) + '"></a><div><h3><a href="' + esc(p.url) + '">' + esc(p.name) + '</a></h3><p>' + pricing + '</p><span class="account-availability">' + esc(p.availability) + '</span><a class="account-primary" href="' + esc(p.url) + '">View product</a><button class="account-remove" type="button" data-remove-wishlist="' + esc(p.id) + '">Remove</button></div></article>';
  }

  function wishlist() {
    var meta = paginateItems(data.wishlist || []);
    var body = meta.total
      ? '<div id="wishlistList" class="account-products">' + meta.items.map(productCard).join('') + '</div>' + paginationNav(meta)
      : empty('No wishlist items yet', 'Select the heart on a product to save it here.');
    return '<div class="account-title"><p>ACCOUNT</p><h1>Wishlist</h1></div>' + panel('Saved pieces', body);
  }

  function reviewCard(r) {
    var stars = '';
    for (var i = 1; i <= 5; i++) {
      stars += '<span class="account-star" style="opacity:' + (i <= r.rating ? '1' : '0.25') + '">★</span>';
    }
    return '<article class="account-product account-review-card" data-review-id="' + esc(r.id) + '">' +
      '<a href="' + esc(r.product_url) + '"><img src="' + esc(r.product_image) + '" alt="' + esc(r.product_name) + '"></a>' +
      '<div><h3><a href="' + esc(r.product_url) + '">' + esc(r.product_name) + '</a></h3>' +
      '<div class="account-review-stars" aria-label="Rated ' + esc(r.rating) + ' out of 5">' + stars + '</div>' +
      '<p class="account-review-comment">' + esc(r.comment) + '</p>' +
      '<span class="account-review-date">' + esc(r.date) + '</span>' +
      '<button class="account-remove" type="button" data-remove-review="' + esc(r.id) + '">Delete review</button></div></article>';
  }

  function reviews() {
    var meta = paginateItems(data.reviews || []);
    var body = meta.total
      ? '<div id="reviewList" class="account-products">' + meta.items.map(reviewCard).join('') + '</div>' + paginationNav(meta)
      : empty('No reviews yet', 'Reviews you write on products will appear here.');
    return '<div class="account-title"><p>ACCOUNT</p><h1>My Reviews</h1></div>' + panel('Your product reviews', body);
  }

  function notificationCard(n) {
    return '<article class="account-note' + (n.read ? '' : ' is-unread') + '" data-note-id="' + esc(n.id) + '">' +
      '<span class="account-note-dot" aria-hidden="true"></span>' +
      '<div class="account-note-body">' +
        '<div class="account-note-head"><h3>' + esc(n.title) + '</h3>' +
          (n.statusLabel ? '<span class="account-note-status account-note-status--' + esc(n.status) + '">' + esc(n.statusLabel) + '</span>' : '') +
        '</div>' +
        '<p>' + esc(n.body) + '</p>' +
        (n.message ? '<blockquote class="account-note-message"><span>Message from our team</span>' + esc(n.message) + '</blockquote>' : '') +
        '<div class="account-note-foot"><time>' + esc(n.date) + '</time>' +
          '<button class="account-remove" type="button" data-remove-note="' + esc(n.id) + '">Remove</button></div>' +
      '</div></article>';
  }

  function notifications() {
    var meta = paginateItems(data.notifications || []);
    var body = meta.total
      ? '<div class="account-notes">' + meta.items.map(notificationCard).join('') + '</div>' + paginationNav(meta)
      : empty('No notifications yet', 'Updates about your consultation requests will appear here.');
    return '<div class="account-title"><p>ACCOUNT</p><h1>Notifications</h1></div>' + panel('Updates from Vastutathastu', body);
  }

  var renderers = {
    overview: overview,
    orders: orders,
    information: information,
    addresses: addresses,
    wishlist: wishlist,
    reviews: reviews,
    notifications: notifications
  };

  app.innerHTML = header() + '<main class="account-shell"><div class="account-layout">' + navigation() + '<div class="account-content"><div class="account-feedback" role="status" hidden></div>' + (renderers[page] ? renderers[page]() : overview()) + '</div></div></main>' + footer();

  if (page === 'notifications' && unreadCount() && R.notificationsRead) {
    api('POST', R.notificationsRead, {}).then(function (res) {
      if (!res.ok) return;
      (data.notifications || []).forEach(function (n) { n.read = true; });
      store.set(data);
      var dot = document.querySelector('.vt-hdr__icon .vt-notify-dot');
      if (dot) dot.remove();
    });
  }

  // Keep body page marker in sync for any legacy scripts
  document.body.dataset.accountPage = page;

  function showOrderDetail(orderId) {
    var host = document.getElementById('accountOrderDetail');
    if (!host) return;
    var order = findOrder(orderId);
    if (!order) return;
    host.innerHTML = orderDetailHtml(order);
    host.hidden = false;
    document.querySelectorAll('.account-order-row.is-active').forEach(function (row) {
      row.classList.remove('is-active');
    });
    document.querySelectorAll('[data-open-order="' + orderId + '"]').forEach(function (el) {
      var row = el.closest('tr');
      if (row) row.classList.add('is-active');
    });
    host.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideOrderDetail() {
    var host = document.getElementById('accountOrderDetail');
    if (!host) return;
    host.hidden = true;
    host.innerHTML = '';
    document.querySelectorAll('.account-order-row.is-active').forEach(function (row) {
      row.classList.remove('is-active');
    });
  }

  document.addEventListener('keydown', function (event) {
    var row = event.target.closest('.account-order-row[data-open-order]');
    if (!row) return;
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      showOrderDetail(row.getAttribute('data-open-order'));
    }
  });

  document.addEventListener('click', function (event) {
    var toggle = event.target.closest('.account-nav-toggle');
    if (toggle) {
      var open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      document.querySelector('#accountNav').classList.toggle('open', !open);
    }

    if (event.target.closest('[data-action="logout"]')) {
      store.logout(function () { window.location.href = R.home || '/'; });
      return;
    }

    var openOrder = event.target.closest('[data-open-order]');
    if (openOrder) {
      event.preventDefault();
      showOrderDetail(openOrder.getAttribute('data-open-order'));
      return;
    }

    if (event.target.closest('[data-close-order]')) {
      hideOrderDetail();
      return;
    }

    var add = event.target.closest('[data-add-address]');
    if (add) {
      var form = document.querySelector('#addressForm');
      if (!form) return;
      form.reset();
      form.querySelector('[name="id"]').value = '';
      clearFormErrors(form);
      var alertBox = document.getElementById('addressFormAlert');
      if (alertBox) { alertBox.hidden = true; alertBox.textContent = ''; }
      document.querySelector('#addressDialogTitle').textContent = 'Add address';
      document.querySelector('#addressDialog').showModal();
      return;
    }

    if (event.target.closest('[data-close-address]')) {
      document.querySelector('#addressDialog').close();
      return;
    }

    var edit = event.target.closest('[data-edit-address]');
    if (edit) {
      var a = (data.addresses || []).find(function (x) { return String(x.id) === String(edit.dataset.editAddress); });
      if (!a) return;
      var f = document.querySelector('#addressForm');
      clearFormErrors(f);
      f.querySelector('[name="id"]').value = a.id;
      f.querySelector('[name="full_name"]').value = a.name || '';
      f.querySelector('[name="line1"]').value = a.line1 || '';
      f.querySelector('[name="city"]').value = a.city || '';
      f.querySelector('[name="postcode"]').value = a.postcode || '';
      f.querySelector('[name="country"]').value = a.country || '';
      f.querySelector('[name="type"]').value = a.type || 'Shipping';
      f.querySelector('[name="default"]').checked = !!a.default;
      document.querySelector('#addressDialogTitle').textContent = 'Edit address';
      document.querySelector('#addressDialog').showModal();
      return;
    }

    var saveAddressBtn = event.target.closest('[data-save-address]');
    if (saveAddressBtn) {
      event.preventDefault();
      submitAddressForm();
      return;
    }

    var del = event.target.closest('[data-delete-address]');
    if (del) {
      event.preventDefault();
      confirmAction({
        eyebrow: 'Addresses',
        title: 'Delete this address?',
        text: 'This address will be removed from your account.',
        cancelLabel: 'Keep address',
        confirmLabel: 'Delete'
      }).then(function (ok) {
        if (!ok) return;
        api('DELETE', R.addressesDestroy + '/' + del.dataset.deleteAddress).then(function (res) {
          if (!res.ok) { announce((res.data && res.data.message) || 'Could not delete address.', 'error'); return; }
          data.addresses = (data.addresses || []).filter(function (x) { return String(x.id) !== String(del.dataset.deleteAddress); });
          store.set(data);
          del.closest('.address-card').remove();
          announce(res.data.message || 'Address deleted.', 'success');
        });
      });
      return;
    }

    var def = event.target.closest('[data-default-address]');
    if (def) {
      api('POST', R.addressesDefault + '/' + def.dataset.defaultAddress + '/default').then(function (res) {
        if (!res.ok) { announce('Could not update default address.', 'error'); return; }
        (data.addresses || []).forEach(function (x) { x.default = String(x.id) === String(def.dataset.defaultAddress); });
        store.set(data);
        announce(res.data.message || 'Default address updated.', 'success');
        location.reload();
      });
    }

    var removeWish = event.target.closest('[data-remove-wishlist]');
    if (removeWish) {
      event.preventDefault();
      var wishCard = removeWish.closest('.account-product');
      var wishName = wishCard ? (wishCard.querySelector('h3') || {}).textContent : '';
      confirmAction({
        eyebrow: 'Wishlist',
        title: 'Remove from wishlist?',
        text: wishName
          ? ('“' + String(wishName).trim() + '” will be removed from your wishlist.')
          : 'This product will be removed from your wishlist.',
        cancelLabel: 'Keep item',
        confirmLabel: 'Remove'
      }).then(function (ok) {
        if (!ok) return;
        api('DELETE', R.wishlistDestroy + '/' + removeWish.dataset.removeWishlist).then(function (res) {
          if (!res.ok) { announce('Could not remove item.', 'error'); return; }
          data.wishlist = (data.wishlist || []).filter(function (x) { return String(x.id) !== String(removeWish.dataset.removeWishlist); });
          store.set(data);
          if (wishCard) wishCard.remove();
          announce(res.data.message || 'Removed from wishlist.', 'success');
        });
      });
      return;
    }

    var removeNote = event.target.closest('[data-remove-note]');
    if (removeNote && R.notificationsDestroy) {
      var noteId = removeNote.dataset.removeNote;
      api('DELETE', R.notificationsDestroy + '/' + encodeURIComponent(noteId)).then(function (res) {
        if (!res.ok) { announce('Could not remove notification.', 'error'); return; }
        data.notifications = (data.notifications || []).filter(function (x) { return String(x.id) !== String(noteId); });
        store.set(data);
        var card = removeNote.closest('.account-note');
        if (card) card.remove();
        if (!(data.notifications || []).length) { var list = document.querySelector('.account-notes'); if (list) list.outerHTML = empty('No notifications yet', 'Updates about your consultation requests will appear here.'); }
        announce(res.data.message || 'Notification removed.', 'success');
      });
      return;
    }
    var removeReview = event.target.closest('[data-remove-review]');
    if (removeReview) {
      event.preventDefault();
      var reviewCard = removeReview.closest('.account-review-card');
      var reviewName = reviewCard ? (reviewCard.querySelector('h3, .account-review-card__title, a') || {}).textContent : '';
      confirmAction({
        eyebrow: 'My Reviews',
        title: 'Delete this review?',
        text: reviewName
          ? ('Your review for “' + String(reviewName).trim() + '” will be deleted.')
          : 'This review will be permanently deleted.',
        cancelLabel: 'Keep review',
        confirmLabel: 'Delete'
      }).then(function (ok) {
        if (!ok) return;
        api('DELETE', R.reviewsDestroy + '/' + removeReview.dataset.removeReview).then(function (res) {
          if (!res.ok) { announce('Could not delete review.', 'error'); return; }
          data.reviews = (data.reviews || []).filter(function (x) { return String(x.id) !== String(removeReview.dataset.removeReview); });
          store.set(data);
          if (reviewCard) reviewCard.remove();
          announce(res.data.message || 'Review deleted.', 'success');
        });
      });
    }
  });

  document.addEventListener('submit', function (event) {
    if (event.target.id === 'informationForm') {
      event.preventDefault();
      var form = event.target;
      clearFormErrors(form);

      var firstName = form.firstName.value.trim();
      var lastName = form.lastName.value.trim();
      var email = form.email.value.trim();
      var phone = form.phone.value.trim();
      var birthDate = form.birthDate.value;
      var currentPassword = form.currentPassword.value;
      var newPassword = form.newPassword.value;
      var newPasswordConfirmation = form.newPasswordConfirmation.value;
      var ok = true;

      if (!firstName) { setFieldError(form, 'firstName', 'First name is required.'); ok = false; }
      if (!email) { setFieldError(form, 'email', 'Email is required.'); ok = false; }
      else if (!isValidEmail(email)) { setFieldError(form, 'email', 'Please enter a valid email.'); ok = false; }
      if (phone && !/^[0-9 +()-]{7,20}$/.test(phone)) { setFieldError(form, 'phone', 'Enter a valid phone number.'); ok = false; }
      if (newPassword || newPasswordConfirmation || currentPassword) {
        if (!currentPassword) { setFieldError(form, 'currentPassword', 'Current password is required to change password.'); ok = false; }
        if (!newPassword || newPassword.length < 6) { setFieldError(form, 'newPassword', 'New password must be at least 6 characters.'); ok = false; }
        if (newPassword !== newPasswordConfirmation) { setFieldError(form, 'newPasswordConfirmation', 'New passwords do not match.'); ok = false; }
      }
      if (!ok) { announce('Please fix the highlighted fields.', 'error'); return; }

      var btn = form.querySelector('[type="submit"]');
      if (window.PPLoader) window.PPLoader.start(btn, 'Saving profile…', 'Saving…');
      else if (btn) btn.disabled = true;

      var payload = {
        first_name: firstName,
        last_name: lastName,
        email: email,
        phone: phone || null,
        birth_date: birthDate || null,
        marketing_opt_in: form.marketing.checked
      };
      if (newPassword) {
        payload.current_password = currentPassword;
        payload.new_password = newPassword;
        payload.new_password_confirmation = newPasswordConfirmation;
      }

      function releaseBtn() {
        if (window.PPLoader) window.PPLoader.stop(btn, true);
        else if (btn) btn.disabled = false;
      }

      var saveProfile = function () {
        api('PUT', R.profile, payload).then(function (res) {
          releaseBtn();
          if (!res.ok) {
            if (res.data && res.data.errors) applyServerErrors(form, res.data.errors);
            announce((res.data && res.data.message) || 'Could not save profile.', 'error');
            return;
          }
          if (res.data.user) {
            data.customer = Object.assign(data.customer, {
              firstName: res.data.user.firstName,
              lastName: res.data.user.lastName,
              email: res.data.user.email,
              phone: res.data.user.phone,
              birthDate: res.data.user.birthDate,
              marketing: res.data.user.marketing
            });
            window.PP_ACCOUNT_USER = Object.assign(window.PP_ACCOUNT_USER || {}, res.data.user);
            store.set(data);
          }
          form.currentPassword.value = '';
          form.newPassword.value = '';
          form.newPasswordConfirmation.value = '';
          announce(res.data.message || 'Your information has been saved.', 'success');
        }).catch(function () {
          releaseBtn();
          announce('Network error. Please try again.', 'error');
        });
      };

      if (email !== (window.PP_ACCOUNT_USER && window.PP_ACCOUNT_USER.email)) {
        api('POST', R.checkEmail, { email: email }).then(function (res) {
          if (res.data && res.data.available === false) {
            setFieldError(form, 'email', res.data.message || 'This email is already registered.');
            announce(res.data.message || 'This email is already registered.', 'error');
            releaseBtn();
            return;
          }
          saveProfile();
        }).catch(saveProfile);
      } else {
        saveProfile();
      }
    }

    if (event.target.id === 'addressForm') {
      event.preventDefault();
      event.stopPropagation();
      submitAddressForm();
    }
  });

  function showAddressAlert(message, type) {
    var alertBox = document.getElementById('addressFormAlert');
    if (!alertBox) {
      announce(message, type || 'error');
      return;
    }
    if (!message) {
      alertBox.hidden = true;
      alertBox.textContent = '';
      alertBox.className = 'account-form-alert';
      return;
    }
    type = type === 'success' ? 'success' : 'error';
    alertBox.className = 'account-form-alert is-' + type;
    alertBox.textContent = message;
    alertBox.hidden = false;
  }

  function fieldValue(form, name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? String(el.value || '').trim() : '';
  }

  function submitAddressForm() {
    var aform = document.getElementById('addressForm');
    if (!aform) return;

    clearFormErrors(aform);
    showAddressAlert('');

    var fullName = fieldValue(aform, 'full_name');
    var line1 = fieldValue(aform, 'line1');
    var city = fieldValue(aform, 'city');
    var postcode = fieldValue(aform, 'postcode');
    var country = fieldValue(aform, 'country');
    var typeEl = aform.querySelector('[name="type"]');
    var defaultEl = aform.querySelector('[name="default"]');
    var idEl = aform.querySelector('[name="id"]');
    var valid = true;

    if (!fullName) { setFieldError(aform, 'full_name', 'Full name is required.'); valid = false; }
    if (!line1) { setFieldError(aform, 'line1', 'Address is required.'); valid = false; }
    if (!city) { setFieldError(aform, 'city', 'City is required.'); valid = false; }
    if (!postcode) { setFieldError(aform, 'postcode', 'Postal code is required.'); valid = false; }
    else if (!/^[A-Za-z0-9\s\-]{3,20}$/.test(postcode)) {
      setFieldError(aform, 'postcode', 'Enter a valid postal code.');
      valid = false;
    }
    if (!country) { setFieldError(aform, 'country', 'Country is required.'); valid = false; }

    if (!valid) {
      showAddressAlert('Please fill all required fields.', 'error');
      var firstInvalid = aform.querySelector('.is-invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    var addressPayload = {
      name: fullName,
      address_line1: line1,
      city: city,
      pincode: postcode,
      country: country,
      label: typeEl ? typeEl.value : 'Shipping',
      is_default: !!(defaultEl && defaultEl.checked)
    };

    var id = idEl ? idEl.value : '';
    var saveBtn = aform.querySelector('[data-save-address]');
    if (window.PPLoader) window.PPLoader.start(saveBtn, 'Saving address…', 'Saving…');
    else if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving…';
    }

    var req = id
      ? api('PUT', R.addressesUpdate + '/' + id, addressPayload)
      : api('POST', R.addressesStore, addressPayload);

    req.then(function (res) {
      if (window.PPLoader) window.PPLoader.stop(saveBtn, true);
      else if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save address';
      }
      if (!res.ok) {
        if (res.data && res.data.errors) applyServerErrors(aform, res.data.errors);
        showAddressAlert((res.data && res.data.message) || 'Could not save address.', 'error');
        return;
      }
      showAddressAlert('');
      announce(res.data.message || 'Address saved.', 'success');
      document.querySelector('#addressDialog').close();
      location.reload();
    }).catch(function () {
      if (window.PPLoader) window.PPLoader.stop(saveBtn, true);
      else if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save address';
      }
      showAddressAlert('Network error. Please try again.', 'error');
    });
  }

  // Live-clear field errors while typing
  document.addEventListener('input', function (event) {
    var form = event.target.closest('#addressForm');
    if (!form) return;
    clearFormErrors(form);
    showAddressAlert('');
  });
})();
