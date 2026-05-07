<?php
$pageTitle = 'My Orders — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <h1>My Orders</h1>
    <?php if (empty($orders)): ?>
        <p>You haven't placed any orders yet. <a href="<?= APP_URL ?>/shop">Start shopping →</a></p>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <strong>Order #<?= htmlspecialchars($order['order_id']) ?></strong>
                        <span class="status <?= strtolower($order['status']) ?>"><?= $order['status'] ?></span>
                    </div>
                    <div class="order-details">
                        <span>Date: <?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                        <span>Total: ₹<?= number_format($order['total'], 2) ?></span>
                        <a href="<?= APP_URL ?>/account/orders/<?= $order['order_id'] ?>">View Details →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>