<?php
// frontend/views/admin/dashboard.php

// 1. Ensure the session state is initialized
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Authorization Guard: Ensure user is logged in AND is an admin
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    // If a user tries to access this page unlawfully, clear any invalid session data and redirect
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    // Send them back to the login page with a clean state
    header("Location: " . APP_URL . "/login");
    exit;
}

// 3. Clear browser history cache for this view
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$pageTitle = 'Admin Dashboard';
$icon = 'tachometer-alt';

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
                        <td><?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= formatPrice($order['total']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    

        <?php endif; ?>
    </main>
