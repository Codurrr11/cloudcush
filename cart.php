<?php include 'includes/head.php'; ?>

<?php include 'includes/header.php'; ?>

<main class="cart-page">

  <!-- Cart Hero Header -->
  <section class="cart-hero">
    <div class="container cart-hero-content">
      <div class="cart-hero-text">
        <h1 class="cart-hero-title">Your Cart</h1>
        <p class="cart-hero-subtitle">Review your selected premium comfort essentials</p>
      </div>

      <!-- Breadcrumb Navigation -->
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="./" class="breadcrumb-link">Home</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Cart</span>
      </nav>
    </div>
  </section>

  <!-- Cart Main Container -->
  <div class="container">

    <!-- Empty Cart UI -->
    <div class="cart-empty-state" id="cartEmptyState" style="display: none;">
      <div class="empty-icon-wrap">
        <!-- Clean premium illustration icon area -->
        <i class="ri-shopping-bag-3-line"></i>
      </div>
      <h2 class="empty-title">Your cart feels a bit light</h2>
      <p class="empty-desc">Explore our dermatologist-tested, breathable CloudCush diapers and find the perfect comfort for your baby's growth stage.</p>
      <a href="products.php" class="btn-continue-shopping">
        Continue Shopping <i class="ri-arrow-right-line"></i>
      </a>
    </div>

    <!-- Active Cart Layout -->
    <div class="cart-layout" id="cartActiveLayout">

      <!-- Left Column: Items List -->
      <section class="cart-main-content">
        <div id="cartItemsList" class="cart-items-list">
          <!-- Dynamically populated by JS -->
        </div>
      </section>

      <!-- Right Column: Summary Sidebar -->
      <aside class="cart-sidebar">
        <div class="summary-card">
          <h3 class="summary-title">Order Summary</h3>

          <div class="summary-rows">
            <div class="summary-row">
              <span class="summary-label">Subtotal</span>
              <span class="summary-value" id="cartSubtotal">₹0</span>
            </div>

            <div class="summary-row">
              <span class="summary-label">Estimated Shipping</span>
              <span class="summary-value" id="cartShipping">Calculated next</span>
            </div>

            <div class="summary-row discount-row" id="cartDiscountRow" style="display: none;">
              <span class="summary-label">Promo Discount</span>
              <span class="summary-value" id="cartDiscount">-₹0</span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row total-row">
              <span class="summary-label">Total</span>
              <span class="summary-value" id="cartTotal">₹0</span>
            </div>
          </div>

          <!-- Coupon Promo Field -->
          <div class="coupon-section">
            <div class="coupon-field">
              <input type="text" id="couponInput" class="coupon-input" placeholder="Promo Code" aria-label="Promo Code">
              <button id="couponBtn" class="btn-coupon-apply">Apply</button>
            </div>
            <p id="couponFeedback" class="coupon-feedback"></p>
          </div>

          <!-- Checkout CTA -->
          <button class="btn-checkout-big" id="checkoutBtn">
            Proceed to Checkout
          </button>

          <!-- Secure Checkout Indicator -->
          <div class="secure-checkout-note">
            <i class="ri-shield-user-line"></i> Secure SSL Checkout. Powered by Razorpay.
          </div>
        </div>
      </aside>

    </div>

  </div>

</main>

<?php include 'includes/footer.php'; ?>
