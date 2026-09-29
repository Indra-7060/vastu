(function () {
  'use strict';

  var app = document.getElementById('support-app') || document.body;
  var page = (app.dataset && app.dataset.supportPage) || document.body.dataset.supportPage || 'faqs';
  var R = window.PP_SUPPORT_ROUTES || {};
  var site = window.PP_SITE || {};
  var base = R.supportBase || '/support';
  var assetBase = (R.assetBase || site.assetBase || '/frontend').replace(/\/$/, '');

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function asset(path) {
    return assetBase + '/' + String(path || '').replace(/^\//, '');
  }

  function supportUrl(slug) {
    return base + '/' + slug;
  }

  var routes = [
    ['shipping', 'Shipping', supportUrl('shipping')],
    ['returns', 'Returns & Exchanges', supportUrl('returns')],
    ['start-return', 'Start My Return', supportUrl('start-return')],
    ['international', 'International Customers', supportUrl('international')],
    ['size-guide', 'Size Guide', supportUrl('size-guide')],
    ['faqs', 'FAQs', supportUrl('faqs')],
    ['terms', 'Terms and Conditions', supportUrl('terms')],
    ['privacy', 'Privacy and Cookies Policy', supportUrl('privacy')],
    ['affiliates', 'Affiliates', supportUrl('affiliates')]
  ];

  function header() {
    return '';
  }

  function footer() {
    return '';
  }

  function nav() {
    return '<button class="support-nav-toggle" type="button" aria-expanded="false" aria-controls="supportNav">Customer Care <span>+</span></button>' +
      '<nav id="supportNav" class="support-nav" aria-label="Customer care">' +
      routes.map(function (r) {
        return '<a href="' + esc(r[2]) + '"' + (r[0] === page ? ' class="active" aria-current="page"' : '') + '>' + r[1] + '</a>';
      }).join('') +
      '</nav>';
  }

  function section(title, body) {
    return '<section class="support-copy-section"><h2>' + title + '</h2>' + body + '</section>';
  }

  function paragraphs(items) {
    return items.map(function (p) { return '<p>' + p + '</p>'; }).join('');
  }

  function accordion(items) {
    return '<div class="support-accordion">' + items.map(function (item, i) {
      return '<article><button type="button" aria-expanded="false" aria-controls="answer-' + page + '-' + i + '">' +
        '<span>' + item[0] + '</span><b aria-hidden="true">⌄</b></button>' +
        '<div id="answer-' + page + '-' + i + '" hidden>' + paragraphs(item[1]) + '</div></article>';
    }).join('') + '</div>';
  }

  var contents = {
    shipping: function () {
      return section('Shipping', '<p class="support-lead">Thoughtful delivery for every Vastutathastu order.</p>' + accordion([
        ['Order processing', ['Orders are prepared Monday through Friday. Please allow 1–2 business days for processing before dispatch.', 'During launches and seasonal promotions, processing may require an additional business day.']],
        ['Domestic delivery', ['Standard delivery generally arrives within 3–7 business days after dispatch. Available delivery options and charges are shown at checkout.']],
        ['Tracking your order', ['When your order leaves our studio, a dispatch email with tracking details is sent to the email address used at checkout.']],
        ['Address changes', ['Contact customer care as soon as possible. Once an order has been dispatched, its delivery address cannot be changed.']],
        ['Lost or delayed parcels', ['If tracking has not updated for five business days, contact us with your order number so we can investigate with the carrier.']]
      ]));
    },
    returns: function () {
      return section('Returns & Exchanges', '<p class="support-lead">We want every piece to feel considered, comfortable, and right for you.</p>' + accordion([
        ['Return eligibility', ['Unworn, unwashed items with original tags may be returned within 7 days of delivery. Items must be free from fragrance, makeup, marks, or alteration.']],
        ['Exchanges', ['Size exchanges are subject to availability. Start a return and select the exchange option; we will reserve the replacement when possible.']],
        ['Non-returnable items', ['Final-sale merchandise, gift cards, personalised products, and items marked non-returnable cannot be returned.']],
        ['Refund timing', ['Approved refunds are issued to the original payment method within 5–10 business days after inspection. Bank processing times may vary.']],
        ['Damaged or incorrect orders', ['Contact customer care within 48 hours of delivery with photographs and your order number. We will arrange the appropriate resolution.']]
      ]) + '<a class="support-primary" href="' + esc(supportUrl('start-return')) + '">Start My Return</a>');
    },
    'start-return': function () {
      return section('Start My Return', '<p class="support-lead">Enter your order details to begin a return or exchange.</p>' +
        '<form class="support-form" id="returnForm"><div><label>Order number<input name="order" required placeholder="e.g. TPP-1048"></label>' +
        '<label>Email address<input type="email" name="email" required></label></div>' +
        '<label>Reason for return<select name="reason" required><option value="">Select a reason</option>' +
        '<option>Size or fit</option><option>Changed my mind</option><option>Item arrived damaged</option>' +
        '<option>Incorrect item received</option></select></label>' +
        '<label>Additional details<textarea name="details" rows="5"></textarea></label>' +
        '<label class="support-check"><input type="checkbox" required> I confirm the item is unworn and has its original tags.</label>' +
        '<button class="support-primary" type="submit">Continue Return</button><p class="support-form-status" role="status"></p></form>');
    },
    international: function () {
      return section('International Customers', '<p class="support-lead">Vastutathastu pieces can travel beyond India.</p>' + accordion([
        ['Available destinations', ['International availability is shown in the country selector at checkout. If your destination is unavailable, contact customer care for assistance.']],
        ['Duties and taxes', ['Import duties, taxes, and brokerage charges are determined by the destination country and are the customer’s responsibility unless checkout states otherwise.']],
        ['Delivery estimates', ['International delivery generally requires 7–18 business days after dispatch. Customs inspections may extend this timeframe.']],
        ['International returns', ['International customers may request a return within 7 days of delivery. Return postage, duties, and taxes are not refundable.']],
        ['Currency and payment', ['Displayed currency may be estimated. Your card provider determines the final conversion rate and may apply international transaction fees.']]
      ]));
    },
    'size-guide': function () {
      return section('Size Guide', '<p class="support-lead">Use your body measurements to select the closest size. Measurements are shown in inches.</p>' +
        '<div class="size-table-wrap"><table class="size-table"><tbody><tr><th>XS</th><td>32″</td><td>28″</td><td>36″</td><td>15″</td><td>14″</td></tr>' +
'<tr><th>S</th><td>34″</td><td>30″</td><td>38″</td><td>16″</td><td>14.5″</td></tr>' +
'<tr><th>M</th><td>36″</td><td>32″</td><td>40″</td><td>17.5″</td><td>15″</td></tr>' +
'<tr><th>L</th><td>38″</td><td>34″</td><td>42″</td><td>19″</td><td>15.5″</td></tr>' +
'<tr><th>XL</th><td>40″</td><td>36″</td><td>44″</td><td>20.5″</td><td>16″</td></tr>' +
'<tr><th>XXL</th><td>42″</td><td>38″</td><td>46″</td><td>21.5″</td><td>16.5″</td></tr></tbody></table></div>' +
        accordion([
          ['How to measure', ['Bust: measure around the fullest part of your chest. Waist: measure around your natural waist. Hip: measure around the fullest part of your seat.']],
          ['Between sizes', ['Choose the larger size for a relaxed fit or the smaller size for a closer fit. Refer to the fit note on each product page.']],
          ['Need assistance?', ['Contact customer care with your measurements and the product name for personalised sizing guidance.']]
        ]));
    },
    faqs: function () {
      return section('FAQs', accordion([
        ['Orders', ['Orders can be reviewed immediately after checkout. If you need to request a change, contact customer care before dispatch.']],
        ['Payment', ['We accept the payment methods displayed at checkout. Payments are securely processed and charged when the order is confirmed.']],
        ['Shipping and delivery', ['Domestic delivery generally takes 3–7 business days after dispatch. Tracking is provided by email.']],
        ['Return Eligibility', ['We do not offer refunds. If you are not satisfied with your purchase, the product may be exchanged within 7 days of delivery, provided it is unworn, unwashed, unused, and has its original tags attached. Items must be free from fragrance, makeup, marks, or alterations.']],
        ['Exchanges', ['You may exchange your purchase for another available product within the same price range. Exchanges are subject to product availability. <br> <br>  Alternatively, you may opt for a store coupon worth the value of your original purchase, which can be redeemed within 45 days from the date of issue.<br> <br>  Please note: All purchases are eligible for exchange or store credit only. No refunds will be provided.']],
        ['Miscellaneous', ['For product care, availability, gifting, or styling questions, contact our customer care team.']],
        ['Sale returns', ['Items marked final sale are not returnable. Other promotional items follow the eligibility shown on their product page.']],
        ['Gift cards', ['Gift cards are delivered electronically, cannot be exchanged for cash, and are non-refundable.']]
      ]));
    },
    terms: function () {
      return section('Terms and Conditions', '<p class="support-lead">Effective 11 August 2026</p>' +
        section('Use of this website', paragraphs(['By accessing this website, you agree to use it lawfully and in accordance with these terms. Content, product descriptions, photography, and branding remain the property of Vastutathastu.'])) +
        section('Orders and availability', paragraphs(['All orders are subject to acceptance and product availability. We may cancel or limit an order where pricing, inventory, payment, or fraud-screening issues occur.'])) +
        section('Pricing and payment', paragraphs(['Prices and applicable taxes are displayed at checkout. We may correct inadvertent errors before dispatch and will contact you if an order is affected.'])) +
        section('Liability', paragraphs(['Nothing in these terms excludes rights that cannot lawfully be excluded. To the extent permitted by law, our liability is limited to the value of the affected purchase.'])) +
        section('Changes', paragraphs(['We may update these terms periodically. The version displayed at the time of purchase applies to that transaction.'])));
    },
    privacy: function () {
      return section('Privacy and Cookies Policy', '<p class="support-lead">Effective 11 August 2026</p>' + accordion([
        ['Information we collect', ['We collect information you provide during checkout, account creation, customer-care enquiries, returns, and newsletter registration.']],
        ['How we use information', ['Information is used to fulfil orders, process payments, provide support, prevent fraud, improve our services, and send marketing where permission has been given.']],
        ['Cookies', ['Essential cookies operate the website and checkout. Analytics and preference cookies help us understand use and remember choices. Browser settings can be used to limit non-essential cookies.']],
        ['Sharing and retention', ['We share information only with service providers required for payments, fulfilment, delivery, analytics, and legal compliance. Records are retained only as long as necessary.']],
        ['Your choices', ['You may request access, correction, deletion, or restriction of eligible personal information and may unsubscribe from marketing at any time.']],
        ['Contact', ['For privacy questions or requests, contact Vastutathastu customer care team and include "Privacy Request" in the subject line.']]
      ]));
    },
    affiliates: function () {
      return section('Affiliates', '<p class="support-lead">Partner with Vastutathastu and share thoughtful dressing with your community.</p>' +
        '<div class="affiliate-benefits"><article><strong>Curated partnership</strong><p>Campaign direction and product stories aligned with your audience.</p></article>' +
        '<article><strong>Commission</strong><p>Earn commission on qualifying purchases made through your unique link.</p></article>' +
        '<article><strong>Early access</strong><p>Preview selected launches, editorial stories, and seasonal collections.</p></article></div>' +
        '<form class="support-form" id="affiliateForm"><div><label>Full name<input name="name" required></label>' +
        '<label>Email address<input type="email" name="email" required></label></div>' +
        '<div><label>Primary platform<input name="platform" required></label>' +
        '<label>Profile or website URL<input type="url" name="url" required></label></div>' +
        '<label>Tell us about your audience<textarea name="audience" rows="5" required></textarea></label>' +
        '<button class="support-primary" type="submit">Apply now</button><p class="support-form-status" role="status"></p></form>');
    }
  };

  var render = contents[page] || contents.faqs;
  app.innerHTML = header() +
    '<main class="support-shell"><div class="support-layout">' + nav() +
    '<div class="support-content">' + render() + '</div></div></main>' + footer();

  document.body.dataset.supportPage = page;

  document.addEventListener('click', function (e) {
    var t = e.target.closest('.support-nav-toggle');
    if (t) {
      var open = t.getAttribute('aria-expanded') === 'true';
      t.setAttribute('aria-expanded', String(!open));
      document.querySelector('#supportNav').classList.toggle('open', !open);
    }
    var b = e.target.closest('.support-accordion button');
    if (b) {
      var expanded = b.getAttribute('aria-expanded') === 'true';
      b.setAttribute('aria-expanded', String(!expanded));
      document.getElementById(b.getAttribute('aria-controls')).hidden = expanded;
      b.querySelector('b').textContent = expanded ? '⌄' : '⌃';
    }
  });

  document.addEventListener('submit', function (e) {
    if (e.target.matches('.support-form, .account-newsletter form')) {
      e.preventDefault();
      if (!e.target.checkValidity()) {
        e.target.reportValidity();
        return;
      }
      var status = e.target.querySelector('.support-form-status');
      if (status) status.textContent = 'Thank you. Your information has been received.';
      e.target.reset();
    }
  });
}());
