(function () {
  'use strict';

  var routes = window.PP_ACCOUNT_ROUTES || {};
  var assetBase = String(routes.assetBase || '/frontend').replace(/\/?$/, '/');

  function asset(path) {
    if (!path) return '';
    if (/^https?:\/\//i.test(path) || path.charAt(0) === '/') return path;
    return assetBase + String(path).replace(/^\.?\/?/, '');
  }

  function read() {
    var data = window.PP_ACCOUNT_DATA || {
      customer: { firstName: 'Guest', lastName: '', email: '', phone: '', birthDate: '', marketing: true },
      orders: [],
      addresses: [],
      wishlist: [],
      reviews: []
    };
    if (window.PP_ACCOUNT_USER) {
      data.customer = Object.assign({}, data.customer || {}, {
        firstName: window.PP_ACCOUNT_USER.firstName || data.customer.firstName,
        lastName: window.PP_ACCOUNT_USER.lastName || '',
        email: window.PP_ACCOUNT_USER.email || data.customer.email,
        phone: window.PP_ACCOUNT_USER.phone || '',
        birthDate: window.PP_ACCOUNT_USER.birthDate || data.customer.birthDate || '',
        marketing: typeof window.PP_ACCOUNT_USER.marketing === 'boolean' ? window.PP_ACCOUNT_USER.marketing : true
      });
    }
    return data;
  }

  window.PPAccountStore = {
    mode: 'database',
    isAuthenticated: function () { return !!window.PP_ACCOUNT_USER; },
    get: read,
    set: function (data) { window.PP_ACCOUNT_DATA = data; },
    asset: asset,
    logout: function (done) {
      fetch(routes.logout, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': routes.csrf || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      }).finally(function () {
        if (typeof done === 'function') done();
      });
    }
  };
})();
