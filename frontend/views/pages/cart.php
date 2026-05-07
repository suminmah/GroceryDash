<?php
// frontend/views/pages/cart.php
$pageTitle = 'Your Cart — FreshCart';
require __DIR__ . '/../layouts/header.php';

if (!isset($totals)) {
  $totals = [
    'subtotal' => 0,
    'delivery_fee' => 0,
    'total' => 0,
  ];
}
?>

<div class="container" style="padding:2rem 0 4rem">
  <h1 style="margin-bottom:1.5rem">🛒 Your Cart</h1>

  <?php if (empty($cartItems)): ?>
    <div class="empty-state">
      <span style="font-size:4rem">🛒</span>
      <h2>Your cart is empty</h2>
      <p>Add some fresh groceries to get started!</p>
      <a href="<?= APP_URL ?>/shop" class="btn btn-primary">Start Shopping</a>
    </div>

  <?php else: ?>
    <div class="cart-layout">

      <!-- Cart items -->
      <div class="cart-items">
        <?php foreach ($cartItems as $item):
          $unitPrice = $item['sale_price'] ?? $item['price'];
          $lineTotal = $unitPrice * $item['quantity'];
        ?>
          <div class="cart-item" id="cart-item-<?= $item['product_id'] ?>">
            <img
              src="<?= e($item['image'] ?? APP_URL . '/assets/images/placeholder.png') ?>"
              alt="<?= e($item['name']) ?>" width="80" height="80">

            <div class="cart-item-info">
              <a href="<?= APP_URL ?>/product/<?= e($item['slug']) ?>" class="cart-item-name">
                <?= e($item['name']) ?>
              </a>
              <div class="cart-item" id="cart-item-<?= $item['product_id'] ?>"></div>
            </div>

            <div class="qty-control">
              <button class="qty-btn js-update-qty" data-action="dec" data-id="<?= $item['product_id'] ?>" data-csrf="<?= csrfToken() ?>">−</button>
              <span class="qty-display"><?= $item['quantity'] ?></span>
              <button class="qty-btn js-update-qty" data-action="inc" data-id="<?= $item['product_id'] ?>" data-csrf="<?= csrfToken() ?>">+</button>
            </div>

            <div class="cart-item-total"><?= formatPrice($lineTotal) ?></div>

            <button class="cart-remove js-remove-item" data-id="<?= $item['product_id'] ?>" data-csrf="<?= csrfToken() ?>" aria-label="Remove">✕</button>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="cart-summary">
        <h3>Order Summary</h3>

        <div class="summary-row">
          <span>Subtotal</span>
          <span id="summarySubtotal"><?= formatPrice($totals['subtotal']) ?></span>
        </div>
        <div class="summary-row">
          <span>Delivery</span>
          <span id="summaryDelivery">
            <?= $totals['delivery_fee'] === 0
              ? '<span class="text-green">FREE</span>'
              : formatPrice($totals['delivery_fee']) ?>
          </span>
        </div>
        <?php if ($totals['delivery_fee'] > 0): ?>
          <p class="delivery-nudge">
            Add <?= formatPrice(FREE_DELIVERY_THRESHOLD - $totals['subtotal']) ?> more for free delivery!
          </p>
        <?php endif; ?>

        <div class="coupon-row">
          <input type="text" placeholder="Coupon code" class="coupon-input" id="couponInput">
          <button class="btn btn-sm btn-outline" id="applyCoupon">Apply</button>
        </div>

        <div class="summary-total">
          <span>Total</span>
          <span id="summaryTotal"><?= formatPrice($totals['total']) ?></span>
        </div>

        <a href="<?= APP_URL ?>/checkout" class="btn btn-primary btn-full">
          Proceed to Checkout →
        </a>
        <a href="<?= APP_URL ?>/shop" class="btn btn-outline btn-full" style="margin-top:.5rem">
          Continue Shopping
        </a>
      </div>

    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
