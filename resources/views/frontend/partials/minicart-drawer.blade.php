{{-- Live shopping bag drawer (mounted to body by cart.js) --}}
<div class="minicart-16" data-minicart-root aria-hidden="true">
  <div class="minicart-16-overlay" data-minicart-close></div>
  <aside class="cart-main" role="dialog" aria-modal="true" aria-label="Shopping cart">
    <div class="cart-headers d-flex align-items-center justify-content-between">
      <h4 class="title">SHOPPING CART <span class="pp-minicart-count" data-minicart-count-label></span></h4>
      <button type="button" class="minicart-close-icon" data-minicart-close aria-label="Close bag">&times;</button>
    </div>

    <div class="ship-bar text-center">
      <h4 class="ship-title" data-minicart-ship>Free shipping calculated at checkout</h4>
      <div class="progress" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar" data-minicart-ship-bar style="width: 40%"></div>
      </div>
    </div>

    <div class="cart-content">
      <ul class="product pp-minicart-live" data-minicart-items>
        <li class="list-content pp-minicart-empty">Your bag is empty.</li>
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
