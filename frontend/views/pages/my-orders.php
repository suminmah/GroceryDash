<?php
$pageTitle = 'My Orders — FreshCart';
require __DIR__ . '/../layouts/header.php';
?>

<div class="user-orders-container">
    <div class="page-header">
        <h1>My Orders</h1>
        <p>Manage and track your recent grocery deliveries.</p>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <div class="empty-icon">📦</div>
            <p>You haven't placed any orders yet.</p>
            <a href="<?= APP_URL ?>/products" class="btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            <span class="label">Order ID</span>
                            <strong>#<?= htmlspecialchars($order['id']) ?></strong>
                        </div>
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                    
                    <div class="order-body">
                        <div class="info-group">
                            <span class="label">Placed on</span>
                            <span><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="info-group">
                            <span class="label">Total Amount</span>
                            <span class="order-total"><?= formatPrice($order['total']) ?></span>
                        </div>
                    </div>

                    <div class="order-footer">
                        <a href="<?= APP_URL ?>/account/orders/<?= $order['id'] ?>" class="btn-outline">
                            View Order Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>