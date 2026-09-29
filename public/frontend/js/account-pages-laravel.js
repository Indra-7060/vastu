(function () {
  if (!window.PP_ACCOUNT_ROUTES) return;
  var R = window.PP_ACCOUNT_ROUTES;
  function rewrite() {
    document.querySelectorAll('a[href]').forEach(function (a) {
      var href = a.getAttribute('href') || '';
      var map = {
        'index.html': R.home,
        'collection.html': R.collection,
        'shop-cart.html': R.cart,
        'blog-1.html': R.blog,
        'about.html': R.about,
        'page-signup.html': R.signup,
        'account-overview.html': R.accountBase + '/overview',
        'account-orders.html': R.accountBase + '/orders',
        'account-information.html': R.accountBase + '/information',
        'account-addresses.html': R.accountBase + '/addresses',
        'account-favorites.html': R.accountBase + '/wishlist',
        'account-wishlists.html': R.accountBase + '/wishlist',
        'shipping.html': R.support + '/shipping',
        'faqs.html': R.support + '/faqs'
      };
      if (map[href]) a.setAttribute('href', map[href]);
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', rewrite);
  else setTimeout(rewrite, 0);
})();
