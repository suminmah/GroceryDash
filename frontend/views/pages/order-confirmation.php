<?php
// frontend/views/pages/order-confirmation.php
$pageTitle = 'Order Confirmed! — GroceryDash';
require __DIR__ . '/../layouts/header.php';
$order = $order ?? [];
?>

<div class="container" style="padding:3rem 0;max-width:700px;margin-inline:auto;text-align:center">

  <div class="confirm-icon">✅</div>
  <h1>Order Confirmed!</h1>
  <p style="font-size:1.1rem;color:#555">
    Thank you for your order. We're already packing your groceries!
  </p>

  <div class="order-summary-box">
    <div class="order-meta">
      <div><span>Order #</span><strong><?= e($order['order_number']) ?></strong></div>
      <div><span>Status</span><strong class="status-badge"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></strong></div>
      <div><span>Payment</span><strong><?= strtoupper($order['payment_method']) ?></strong></div>
      <div><span>Delivery slot</span><strong><?= e($order['delivery_slot'] ?? 'Today') ?></strong></div>
    </div>

    <table class="order-items-table">
      <thead>
        <tr><th>Item</th><th>Qty</th><th>Price</th></tr>
      </thead>
      <tbody>
        <?php foreach ($order['items'] as $item): ?>
          <tr>
            <td><?= e($item['name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="2">Delivery</td><td><?= formatPrice($order['delivery_fee']) ?></td></tr>
        <tr class="total-row"><td colspan="2"><strong>Total</strong></td><td><strong><?= formatPrice($order['total']) ?></strong></td></tr>
      </tfoot>
    </table>

    <div class="delivery-address">
      <strong>Delivering to:</strong> <?= e($order['delivery_address'] ?? 'Address not provided') ?>
    </div>
  </div>

  <div class="confirm-actions">
    <a href="<?= APP_URL ?>/order/track/<?= e($order['order_number']) ?>" class="btn btn-primary">
    Track My Order
    </a>
    <a href="<?= APP_URL ?>/shop" class="btn btn-outline">
      Continue Shopping
    </a>
  </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
