<?php
// frontend/views/pages/cart.php
$pageTitle = 'Your Cart — GroceryDash';
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
    <div class="empty-state text-center" style="padding: 3rem 1rem;">
      <span style="font-size:4rem">🛒</span>
      <h2>Your cart is empty</h2>
      <p>Add some fresh groceries to get started!</p>
      <a href="<?= APP_URL ?>/shop" class="btn btn-primary">Start Shopping</a>
    </div>

  <?php else: ?>
    <div class="cart-layout">

      <div class="cart-items">
        <?php foreach ($cartItems as $item):
          $unitPrice = $item['sale_price'] ?? $item['price'];
          $lineTotal = $unitPrice * $item['quantity'];
          
          // Dynamic image source resolution checking
          if (!empty($item['image'])) {
              $productImgUrl = (str_starts_with($item['image'], 'http') || str_contains($item['image'], '/assets/'))
                  ? $item['image']
                  : APP_URL . '/assets/images/products/' . ltrim($item['image'], '/');
          } else {
              $productImgUrl = APP_URL . '/assets/images/products/placeholder.png';
          }
        ?>
          <div class="cart-item" id="cart-item-<?= $item['product_id'] ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
            
            <img src="<?= htmlspecialchars($productImgUrl) ?>" 
                 alt="<?= e($item['name']) ?>" 
                 width="80" 
                 height="80" 
                 style="object-fit: contain; border-radius: 8px;">

            <div class="cart-item-info" style="flex-grow: 1; padding-left: 1.5rem;">
              <a href="<?= APP_URL ?>/product/<?= e($item['product_id']) ?>" class="cart-item-name" style="font-weight: 600; text-decoration: none; color: #111827;">
                <?= e($item['name']) ?>
              </a>
              </div>

            <div class="qty-control" style="display: flex; align-items: center; gap: 0.5rem;">
              <button class="qty-btn js-update-qty" data-action="dec" data-id="<?= $item['product_id'] ?>" data-csrf="<?= csrfToken() ?>">−</button>
              <span class="qty-display" style="font-weight: 600; min-width: 20px; text-align: center;"><?= $item['quantity'] ?></span>
              <button class="qty-btn js-update-qty" data-action="inc" data-id="<?= $item['product_id'] ?>" data-csrf="<?= csrfToken() ?>">+</button>
            </div>

            <div class="cart-item-total" style="font-weight: 700; min-width: 100px; text-align: right;">
                <?= formatPrice($lineTotal) ?>
            </div>

            <button class="cart-remove js-remove-item" data-id="<?= $item['product_id'] ?>" data-csrf="<?= csrfToken() ?>" aria-label="Remove" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1.1rem; margin-left: 1rem;">✕</button>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="cart-summary">
        <h3>Order Summary</h3>

        <div class="summary-row">
          <span>Subtotal</span>
          <span id="summarySubtotal"><?= formatPrice($totals['subtotal']) ?></span>
        </div>
        <div class="summary-row">
          <span>Delivery</span>
          <span id="summaryDelivery">
            <?= $totals['delivery_fee'] == 0
              ? '<span class="text-green" style="color: #16a34a; font-weight: 600;">FREE</span>'
              : formatPrice($totals['delivery_fee']) ?>
          </span>
        </div>
        <?php if ($totals['delivery_fee'] > 0): ?>
          <p class="delivery-nudge" style="color: #2563eb; font-size: 0.85rem; margin-top: 0.25rem;">
            Add <?= formatPrice(FREE_DELIVERY_THRESHOLD - $totals['subtotal']) ?> more for free delivery!
          </p>
        <?php endif; ?>

        <div class="coupon-row" style="display: flex; gap: 0.5rem; margin: 1rem 0;">
          <input type="text" placeholder="Coupon code" class="coupon-input" id="couponInput" style="flex-grow: 1; padding: 0.375rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
          <button class="btn btn-sm btn-outline" id="applyCoupon">Apply</button>
        </div>

        <div class="summary-total" style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem; font-weight: 700; font-size: 1.2rem;">
          <span>Total</span>
          <span id="summaryTotal"><?= formatPrice($totals['total']) ?></span>
        </div>

        <a href="<?= APP_URL ?>/checkout" class="btn btn-primary btn-full" style="display: block; text-align: center; margin-top: 1rem;">
          Proceed to Checkout →
        </a>
        <a href="<?= APP_URL ?>/shop" class="btn btn-outline btn-full" style="display: block; text-align: center; margin-top: .5rem">
          Continue Shopping
        </a>
      </div>

    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Handle Item Removal
    document.querySelectorAll('.js-remove-item').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-id');
            const csrfToken = this.getAttribute('data-csrf');
            
            const payload = new URLSearchParams();
            payload.append('product_id', productId);
            payload.append('csrf_token', csrfToken);
            
            // Add slight visual feedback
            const btn = this;
            btn.style.opacity = '0.5';
            
            fetch("<?= APP_URL ?>/cart/remove", {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.cart_count === 0) {
                        window.location.reload(); // Empty state
                        return;
                    }
                    
                    // Update global headers
                    document.querySelectorAll('.cart-count, .cart-counter, .cart-badge').forEach(c => c.innerText = data.cart_count);
                    
                    // Update order summary
                    if (document.getElementById('summarySubtotal')) document.getElementById('summarySubtotal').innerHTML = data.subtotal;
                    if (document.getElementById('summaryDelivery')) document.getElementById('summaryDelivery').innerHTML = data.delivery_fee == '$0.00' || data.delivery_fee == '0' || data.delivery_fee == 'Rs. 0.00' || data.delivery_fee.includes('0.00') ? '<span class="text-green" style="color: #16a34a; font-weight: 600;">FREE</span>' : data.delivery_fee;
                    if (document.getElementById('summaryTotal')) document.getElementById('summaryTotal').innerHTML = data.total;
                    
                    // Animate and remove item row seamlessly
                    const cartRow = btn.closest('.cart-item');
                    if (cartRow) {
                        cartRow.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        cartRow.style.opacity = '0';
                        cartRow.style.transform = 'scale(0.95)';
                        setTimeout(() => cartRow.remove(), 300);
                    }
                } else {
                    alert(data.message || 'Failed to remove item.');
                    btn.style.opacity = '1';
                }
            })
            .catch(err => {
                console.error(err);
                btn.style.opacity = '1';
            });
        });
    });

    // 2. Handle Quantity Updates
    document.querySelectorAll('.js-update-qty').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-id');
            const csrfToken = this.getAttribute('data-csrf');
            const action = this.getAttribute('data-action');
            
            const displaySpan = this.parentElement.querySelector('.qty-display');
            let currentQty = parseInt(displaySpan.innerText, 10);
            
            let newQty = action === 'inc' ? currentQty + 1 : currentQty - 1;
            if (newQty < 0) newQty = 0;
            
            const payload = new URLSearchParams();
            payload.append('product_id', productId);
            payload.append('quantity', newQty);
            payload.append('csrf_token', csrfToken);
            
            // Disable all buttons in this group temporarily to prevent spam clicks
            const btns = this.parentElement.querySelectorAll('.qty-btn');
            btns.forEach(b => b.disabled = true);
            
            fetch("<?= APP_URL ?>/cart/update", {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // Reload to safely recalculate line totals and delivery thresholds
                } else {
                    alert(data.message || 'Failed to update quantity.');
                    btns.forEach(b => b.disabled = false);
                }
            })
            .catch(err => {
                console.error(err);
                btns.forEach(b => b.disabled = false);
            });
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>