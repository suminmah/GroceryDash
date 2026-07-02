<?php
// frontend/views/pages/order-confirmation.php
$pageTitle = 'Order Confirmed! — GroceryDash';
require __DIR__ . '/../layouts/header.php';
$order = $order ?? [];
?>

<div class="premium-confirmation-wrapper" style="padding:3rem 1rem; max-width:800px; margin:0 auto; text-align:center;">

  <div class="success-animation">
    <svg viewBox="0 0 52 52">
      <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
      <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
    </svg>
  </div>
  <h1 style="font-size: 2.2rem; margin-bottom: 0.5rem; color: var(--text-primary);">Order Confirmed!</h1>
  <p style="font-size:1.1rem; color:var(--text-muted); margin-bottom: 2rem;">
    Thank you for your order. We're already packing your groceries!
  </p>

  <div class="premium-summary-card">
    <div class="premium-meta-grid">
      <div class="meta-item">
        <span class="meta-label">Order #</span>
        <span class="meta-value"><?= e($order['order_number']) ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Status</span>
        <span class="status-badge"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Payment</span>
        <span class="meta-value">
          <?= match(strtolower($order['payment_method'] ?? '')) {
              'cod' => 'Cash on Delivery',
              'fonepay' => '<img src="' . APP_URL . '/assets/images/fonepay-logo.png" alt="Fonepay" style="height:24px;">',
              'esewa' => '<img src="' . APP_URL . '/assets/images/esewa-logo.webp" alt="eSewa" style="height:24px;">',
              'khalti' => '<img src="' . APP_URL . '/assets/images/khalti-logo.png" alt="Khalti" style="height:24px;">',
              'online' => 'Online Payment',
              default => strtoupper($order['payment_method'] ?? 'COD')
          } ?>
        </span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Delivery Slot</span>
        <span class="meta-value"><?= e($order['delivery_slot'] ?? 'Today') ?></span>
      </div>
    </div>

    <table class="premium-items-table">
      <thead>
        <tr><th style="width: 60%">Item</th><th style="width: 15%; text-align: center;">Qty</th><th style="text-align: right;">Price</th></tr>
      </thead>
      <tbody>
        <?php foreach ($order['items'] as $item): ?>
          <tr>
            <td class="item-name"><?= e($item['name']) ?></td>
            <td style="text-align: center; color: var(--text-muted);"><?= $item['quantity'] ?></td>
            <td style="text-align: right;"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="2" style="text-align: right; padding-right: 1.5rem;">Delivery Fee</td><td style="text-align: right;"><?= formatPrice($order['delivery_fee']) ?></td></tr>
        <tr class="total-row"><td colspan="2" style="text-align: right; padding-right: 1.5rem;">Total Amount</td><td style="text-align: right;"><?= formatPrice($order['total']) ?></td></tr>
      </tfoot>
    </table>

    <div class="premium-address">
      <svg style="width: 24px; height: 24px; flex-shrink: 0; color: var(--green);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
      <div>
        <strong style="display: block; color: var(--text-primary); margin-bottom: 0.2rem;">Delivery Address</strong>
        <?= e($order['delivery_address'] ?? 'Address not provided') ?>
      </div>
    </div>
  </div>

  <div class="premium-actions">
    <a href="<?= APP_URL ?>/order/track/<?= e($order['order_number']) ?>" class="btn btn-primary btn-pill">
      Track My Order
    </a>
    <a href="<?= APP_URL ?>/shop" class="btn btn-outline btn-pill">
      Continue Shopping
    </a>
  </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
