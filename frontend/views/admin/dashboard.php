<?php
// frontend/views/admin/dashboard.php
?>

<!-- Main Content -->
    <main class="admin-main">
        <h1>Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="stat-number"><?= number_format($stats['total_orders'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <h3>Revenue</h3>
                <div class="stat-number"><?= formatPrice($stats['total_revenue'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <h3>Customers</h3>
                <div class="stat-number"><?= number_format($customerCount ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="stat-number"><?= number_format($stats['pending_count'] ?? 0) ?></div>
            </div>
        </div>

        <!-- Optional: Recent orders table -->
        <?php if (!empty($recentOrders)): ?>
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentOrders as $order): ?> 
                    <tr>
                        <td><?= $order['order_id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= formatPrice($order['total']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    

        <?php endif; ?>
    </main>
