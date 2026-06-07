<?php
/**
 * Admin Dashboard Metric Panel View Template
 * * @var array $stats          Contains count totals and computed operational metrics
 * @var int   $customerCount  Explicit count tally of unique user entities
 * @var array $recentOrders   Unfilled or newly generated incoming order matrices
 */

// Initialize view metrics strictly with safe fallback baselines
$pageTitle    = 'Admin Dashboard — GroceryDash';
$icon         = 'tachometer-alt';
$stats        = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$customerCount = (int)($customerCount ?? 0);
?>

<main class="admin-main">
    <div class="admin-view-header">
        <h1>Dashboard Overview</h1>
        <p class="text-muted">Real-time status metrics and processing monitors.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon orders-icon">📦</div>
            <div class="stat-card-content">
                <h3>Total Orders</h3>
                <div class="stat-number"><?= number_format((int)($stats['total_orders'] ?? 0)) ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon revenue-icon">💰</div>
            <div class="stat-card-content">
                <h3>Gross Revenue</h3>
                <div class="stat-number"><?= formatPrice($stats['total_revenue'] ?? 0) ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon customers-icon">👥</div>
            <div class="stat-card-content">
                <h3>Active Customers</h3>
                <div class="stat-number"><?= number_format($customerCount) ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon pending-icon">⏳</div>
            <div class="stat-card-content">
                <h3>Pending Orders</h3>
                <div class="stat-number"><?= number_format((int)($stats['pending_count'] ?? 0)) ?></div>
            </div>
        </div>
    </div>

    <div class="recent-orders-section">
        <div class="section-header-flex">
            <h2>Recent Operational Activity</h2>
            <?php if (!empty($recentOrders)): ?>
                <a href="<?= APP_URL ?>/admin/orders" class="btn-text-link">View All Orders →</a>
            <?php endif; ?>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div class="empty-state-card">
                <span class="empty-icon">🎉</span>
                <p>No active pending order backlogs found in the processing queues.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Customer Name</th>
                            <th>Total Gross</th>
                            <th>Workflow Status</th>
                            <th>Timestamp Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentOrders as $order): ?> 
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars((string)($order['id'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars((string)($order['customer_name'] ?? 'Guest Profile'), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="price-data-cell">
                                <?= formatPrice($order['total'] ?? 0) ?>
                            </td>
                            <td>
                                <?php $statusFlag = strtolower($order['status'] ?? 'pending'); ?>
                                <span class="status-badge status-<?= htmlspecialchars($statusFlag, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string)($order['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars(date('M d, Y • h:i A', strtotime($order['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>