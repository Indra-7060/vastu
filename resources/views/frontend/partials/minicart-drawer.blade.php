{{-- Live shopping bag drawer (mounted to body by cart.js) --}}
<div class="minicart-16" data-minicart-root aria-hidden="true">
  <div class="minicart-16-overlay" data-minicart-close></div>
  <aside class="cart-main" role="dialog" aria-modal="true" aria-label="Shopping cart">
    <div class="cart-headers d-flex align-items-center justify-content-between">
      <h4 class="title">SHOPPING CART <span class="pp-minicart-count" data-minicart-count-label></span></h4>
      <button type="button" class="minicart-close-icon" data-minicart-close aria-label="Close bag">&times;</button>
    </div>

    {{-- Free-shipping progress: filled live by cart.js from the bag subtotal --}}
    <div class="ship-bar pp-ship" data-minicart-shipbar>
      <p class="pp-ship__msg notranslate" translate="no" data-minicart-ship aria-live="polite">Free shipping on eligible orders</p>
      <div class="pp-ship__track" role="progressbar" aria-label="Progress to free shipping" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
        <div class="pp-ship__fill" data-minicart-ship-bar style="width: 0%"></div>
      </div>
    </div>

    <div class="cart-content">
      <ul class="product pp-minicart-live" data-minicart-items>
        <li class="list-content pp-minicart-empty">Your bag is empty.<a class="pp-minicart-empty__link" href="{{ route('shop') }}">Explore the shop →</a></li>
      </ul>

      <div class="pp-minicart-footer">
        <div class="total_price">
          <h5 class="sub-title">SUBTOTAL: <span class="total_price" data-cart-subtotal>₹ 0.00</span></h5>
        </div>
        <div class="minicart-btn-wrap">
          <a class="cart-btn" href="{{ route('cart') }}">VIEW CART</a>
          <a class="checkout-btn" href="{{ route('checkout') }}">CHECKOUT</a>
        </div>
      </div>
    </div>
  </aside>
</div>
