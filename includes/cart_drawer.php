<?php
$drawerCart = array_values($_SESSION['cart'] ?? []);
$drawerCount = count($drawerCart);
?>
<style>
  .cart-drawer-trigger {
    position: relative;
    display: inline-flex;
    align-items: center;
  }

  .cart-drawer-count {
    position: absolute;
    top: -9px;
    right: 2px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #e53935;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    line-height: 18px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(229, 57, 53, .35);
  }

  .cart-drawer-count.is-empty {
    display: none;
  }

  .cart-drawer-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1998;
    background: rgba(18, 18, 18, .46);
    opacity: 0;
    pointer-events: none;
    transition: opacity .28s ease;
  }

  .cart-drawer-backdrop.open {
    opacity: 1;
    pointer-events: auto;
  }

  .cart-drawer {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 1999;
    width: min(430px, 100vw);
    height: 100vh;
    background: #fff;
    box-shadow: -24px 0 60px rgba(0, 0, 0, .2);
    transform: translateX(105%);
    transition: transform .32s cubic-bezier(.22, .61, .36, 1);
    display: flex;
    flex-direction: column;
  }

  .cart-drawer.open {
    transform: translateX(0);
  }

  .cart-drawer-header {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .cart-drawer-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: #1f1f1f;
  }

  .cart-drawer-close,
  .cart-qty-btn,
  .cart-remove-btn {
    border: 0;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }

  .cart-drawer-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #333;
    background: #f6f6f6;
  }

  .cart-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 18px;
    background: #fafafa;
  }

  .cart-empty-state {
    min-height: 55vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #777;
    gap: 8px;
  }

  .cart-empty-state i {
    font-size: 3rem;
    color: #ddd;
  }

  .cart-drawer-item {
    display: grid;
    grid-template-columns: 76px 1fr;
    gap: 12px;
    padding: 12px;
    margin-bottom: 12px;
    background: #fff;
    border: 1px solid #f0f0f0;
    border-radius: 8px;
  }

  .cart-drawer-item img {
    width: 76px;
    height: 76px;
    object-fit: cover;
    border-radius: 8px;
  }

  .cart-item-name {
    margin: 0 0 4px;
    font-weight: 800;
    color: #202020;
    font-size: .98rem;
  }

  .cart-item-price {
    margin: 0;
    color: #777;
    font-size: .86rem;
  }

  .cart-item-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 12px;
  }

  .cart-qty-control {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e4e4e4;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
  }

  .cart-qty-btn {
    width: 32px;
    height: 32px;
    color: #f05a22;
    font-weight: 900;
  }

  .cart-qty-value {
    width: 34px;
    text-align: center;
    font-weight: 800;
    color: #222;
  }

  .cart-remove-btn {
    color: #d93636;
    font-size: .88rem;
    gap: 5px;
  }

  .cart-item-total {
    font-weight: 900;
    color: #151515;
    white-space: nowrap;
  }

  .cart-drawer-footer {
    padding: 18px 20px 20px;
    border-top: 1px solid #ededed;
    background: #fff;
    box-shadow: 0 -8px 28px rgba(0, 0, 0, .06);
  }

  .cart-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    color: #555;
  }

  .cart-summary-row.total {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #ddd;
    color: #111;
    font-size: 1.1rem;
    font-weight: 900;
  }

  .cart-footer-actions {
    display: grid;
    grid-template-columns: 1fr 1.4fr;
    gap: 10px;
    margin-top: 16px;
  }

  .cart-clear-btn,
  .cart-checkout-btn,
  .cart-menu-btn {
    border-radius: 8px;
    min-height: 44px;
    font-weight: 800;
  }

  .cart-checkout-btn {
    background: #f05a22;
    border-color: #f05a22;
    color: #fff;
  }

  .cart-checkout-btn:hover {
    background: #d94d1b;
    color: #fff;
  }

  body.cart-drawer-locked {
    overflow: hidden;
  }

  @media (max-width: 575px) {
    .cart-drawer-header,
    .cart-drawer-footer {
      padding-left: 16px;
      padding-right: 16px;
    }
  }
</style>

<div class="cart-drawer-backdrop" id="cartDrawerBackdrop"></div>
<aside class="cart-drawer" id="cartDrawer" aria-hidden="true" aria-label="Shopping cart">
  <div class="cart-drawer-header">
    <div>
      <h2 class="cart-drawer-title">Your Cart</h2>
      <small class="text-muted"><span id="cartDrawerHeaderCount"><?php echo $drawerCount; ?></span> item(s)</small>
    </div>
    <button class="cart-drawer-close" type="button" id="cartDrawerClose" aria-label="Close cart">
      <i class="fa fa-times"></i>
    </button>
  </div>
  <div class="cart-drawer-body" id="cartDrawerBody"></div>
  <div class="cart-drawer-footer" id="cartDrawerFooter">
    <div class="cart-summary-row">
      <span>Subtotal</span>
      <strong id="cartDrawerSubtotal">Rs. 0.00</strong>
    </div>
    <div class="cart-summary-row">
      <span>Delivery</span>
      <strong>Free</strong>
    </div>
    <div class="cart-summary-row total">
      <span>Total</span>
      <strong id="cartDrawerTotal">Rs. 0.00</strong>
    </div>
    <div class="cart-footer-actions">
      <button class="btn btn-outline-danger cart-clear-btn" type="button" id="cartDrawerClear">Clear</button>
      <button class="btn cart-checkout-btn" type="button" id="cartDrawerCheckout" data-bs-toggle="modal" data-bs-target="#checkoutModal">Checkout</button>
    </div>
  </div>
</aside>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="checkoutModalLabel">Delivery Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="checkoutForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="drawerMobile" class="form-label">Mobile Number *</label>
            <input type="tel" class="form-control" id="drawerMobile" name="mobile" pattern="[0-9]{10}" maxlength="10" required>
          </div>
          <div class="mb-3">
            <label for="drawerAddress" class="form-label">Delivery Address *</label>
            <textarea class="form-control" id="drawerAddress" name="address" rows="3" required></textarea>
          </div>
          <div class="alert alert-info mb-0">
            Total amount: <strong id="checkoutModalTotal">Rs. 0.00</strong>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="checkoutBtn">Place Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  window.initialCartDrawerItems = <?php echo json_encode($drawerCart); ?>;
</script>
<script src="<?php echo asset('js/cart_drawer.js'); ?>"></script>
