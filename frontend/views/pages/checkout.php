<?php
// frontend/views/pages/checkout.php
$pageTitle = 'Checkout — FreshCart';
require __DIR__ . '/../layouts/header.php';
$error = flash('checkout_error');
$totals = $totals ?? ['subtotal' => 0, 'delivery_fee' => 0, 'total' => 0];
$selectedSlotId = $_POST['delivery_slot_id'] ?? null;
?>

<div class="container" style="padding:2rem 0 4rem">
  <h1 style="margin-bottom:1.5rem">Checkout</h1>

  <?php if ($error): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/checkout" class="checkout-layout">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

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
        <?php if (empty($slots)): ?>
            <div class="alert alert-warning">No delivery slots available. Please try again later.</div>
        <?php else: ?>
            <div class="slot-grid">
                <?php foreach ($slots as $slot): ?>
                    <div class="slot-item">
                        <div class="form-check border rounded p-3 <?= ($selectedSlotId == $slot['slot_id']) ? 'border-success bg-success bg-opacity-10' : '' ?>">
                            <input class="form-check-input" type="radio" name="delivery_slot_id" 
                                  value="<?= $slot['slot_id'] ?>" 
                                  id="slot_<?= $slot['slot_id'] ?>"
                                  <?= ($selectedSlotId == $slot['slot_id']) ? 'checked' : '' ?>
                                  required>
                            <label class="form-check-label w-100" for="slot_<?= $slot['slot_id'] ?>">
                                <strong><?= date('l, M j', strtotime($slot['slot_date'])) ?></strong><br>
                                <?= date('g:i A', strtotime($slot['start_time'])) ?> – 
                                <?= date('g:i A', strtotime($slot['end_time'])) ?>
                                <small class="text-muted d-block">
                                    <?= $slot['available'] ?? ($slot['capacity'] - $slot['booked']) ?> slots available
                                </small>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (flash('slot_error')): ?>
            <div class="alert alert-danger mt-2"><?= flash('slot_error') ?></div>
        <?php endif; ?>
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
            <input type="radio" name="payment_method" value="esewa">
            <span style="display: flex; align-items: center; gap: 8px;"><img src="<?= APP_URL ?>/assets/images/esewa-logo.webp" alt="eSewa" style="height:45px; object-fit: contain;"> eSewa</span>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment_method" value="khalti">
            <span style="display: flex; align-items: center; gap: 8px;"><img src="<?= APP_URL ?>/assets/images/khalti-logo.png" alt="Khalti" style="height:28px; object-fit: contain;"> Khalti</span>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment_method" value="fonepay">
            <span style="display: flex; align-items: center; gap: 8px;"><img src="<?= APP_URL ?>/assets/images/fonepay-logo.png" alt="Fonepay" style="height:60px; object-fit: contain;"> Fonepay QR</span>
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
        <?php foreach ($cartItems ?? [] as $item):
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
