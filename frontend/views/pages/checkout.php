<?php
// frontend/views/pages/checkout.php
$pageTitle = 'Checkout — FreshCart';
require __DIR__ . '/../layouts/header.php';
$error = flash('checkout_error');
?>

<div class="container" style="padding:2rem 0 4rem">
  <h1 style="margin-bottom:1.5rem">Checkout</h1>

  <?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/checkout" class="checkout-layout">
    <input type="hidden" name="_token" value="<?= csrfToken() ?>">

    <!-- ─── Left column ─── -->
    <div class="checkout-left">

      <!-- Delivery address -->
      <div class="checkout-section">
        <h3>📍 Delivery Address</h3>

        <?php if (!empty($addresses)): ?>
          <?php foreach ($addresses as $addr): ?>
            <label class="address-option">
              <input type="radio" name="saved_address" value="<?= $addr['id'] ?>"
                <?= $addr['is_default'] ? 'checked' : '' ?>>
              <div>
                <strong><?= e($addr['label']) ?></strong><br>
                <?= e($addr['line1']) ?>, <?= e($addr['city']) ?> — <?= e($addr['pincode']) ?>
              </div>
            </label>
          <?php endforeach; ?>
          <details style="margin-top:1rem">
            <summary class="link">+ Add a new address</summary>
            <div class="address-form">
              <?php include __DIR__ . '/../../components/address-fields.php'; ?>
            </div>
          </details>
        <?php else: ?>
          <?php include __DIR__ . '/../../components/address-fields.php'; ?>
        <?php endif; ?>
      </div>

      <!-- Delivery slot -->
      <div class="checkout-section">
        <h3>🕐 Choose Delivery Slot</h3>
        <div class="slot-grid">
          <?php
          $slots = [
            '8 AM – 12 PM'  => 'Morning',
            '12 PM – 4 PM'  => 'Afternoon',
            '4 PM – 8 PM'   => 'Evening',
            'Express 30 min'=> 'Express (+Rs. 20)',
          ];
          foreach ($slots as $val => $label):
          ?>
            <label class="slot-option">
              <input type="radio" name="delivery_slot" value="<?= e($val) ?>"
                <?= $val === '4 PM – 8 PM' ? 'checked' : '' ?>>
              <span><?= e($label) ?></span>
              <small><?= e($val) ?></small>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Payment -->
      <div class="checkout-section">
        <h3>💳 Payment Method</h3>
        <div class="payment-options">
          <label class="payment-option">
            <input type="radio" name="payment_method" value="cod" checked>
            <span>💵 Cash on Delivery</span>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment_method" value="online">
            <span>💳 Pay Online (UPI / Card)</span>
          </label>
        </div>
      </div>

      <!-- Notes -->
      <div class="checkout-section">
        <h3>📝 Order Notes <small>(optional)</small></h3>
        <textarea name="notes" rows="3" class="textarea" placeholder="Delivery instructions, gate code, etc."></textarea>
      </div>
    </div>

    <!-- ─── Right column: summary ─── -->
    <div class="checkout-right">
      <div class="cart-summary sticky-summary">
        <h3>Order Summary</h3>
        <?php foreach ($cartItems as $item):
          $unitPrice = $item['sale_price'] ?? $item['price'];
        ?>
          <div class="summary-item">
            <span><?= e(truncate($item['name'], 28)) ?> ×<?= $item['quantity'] ?></span>
            <span><?= formatPrice($unitPrice * $item['quantity']) ?></span>
          </div>
        <?php endforeach; ?>

        <hr style="border:none;border-top:1px solid #eee;margin:1rem 0">
        <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($totals['subtotal']) ?></span></div>
        <div class="summary-row"><span>Delivery</span>
          <span><?= $totals['delivery_fee'] === 0 ? 'FREE' : formatPrice($totals['delivery_fee']) ?></span>
        </div>
        <div class="summary-total"><span>Total</span><span><?= formatPrice($totals['total']) ?></span></div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:1.5rem">
          ✓ Place Order
        </button>
        <p style="font-size:.75rem;color:#888;margin-top:.5rem;text-align:center">
          🔒 100% secure checkout
        </p>
      </div>
    </div>

  </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
