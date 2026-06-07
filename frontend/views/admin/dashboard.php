<?php
/**
 * Admin Dashboard Metric Panel View Template
 * @var string|null $pageTitle
 * @var string|null $icon
 * @var array $stats          Contains count totals and computed operational metrics
 * @var int   $customerCount  Explicit count tally of unique user entities
 * @var array $recentOrders   Unfilled or newly generated incoming order matrices
 */

// Initialize view metrics strictly with safe fallback baselines
$pageTitle     = $pageTitle ?? 'Admin Dashboard — GroceryDash';
$icon          = $icon ?? 'tachometer-alt';
$stats         = $stats ?? [];
$recentOrders  = $recentOrders ?? [];
$customerCount = (int)($customerCount ?? 0);
?>

<main class="admin-main">
    <div class="admin-view-header mb-4 pb-2 border-bottom">
        <h1 class="font-weight-bold m-0 text-dark" style="font-size: 1.75rem; letter-spacing: -0.025em;">Dashboard Overview</h1>
        <p class="text-muted m-0" style="font-size: 0.9rem;">Real-time status metrics and processing monitors.</p>
    </div>

    <div class="stats-grid grid-row-layout">
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

    <div class="recent-orders-section mt-5">
        <div class="section-header-premium">
            <h2>Recent Operational Activity</h2>
            <a href="<?= APP_URL ?>/admin/orders" class="btn-premium-secondary" style="padding: 8px 16px !important; font-size: 0.825rem !important;">
                <i class="bi bi-collection-play-fill me-1"></i> View All Orders
            </a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div class="empty-state-card text-center p-5 rounded border bg-white shadow-sm">
                <span class="empty-icon display-4 d-block mb-2">🎉</span>
                <p class="text-muted font-weight-medium m-0">No active pending order backlogs found in the processing queues.</p>
            </div>
        <?php else: ?>
            <div class="table-container shadow-sm rounded bg-white overflow-hidden">
                <table class="admin-table w-100 m-0">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Invoice ID</th>
                            <th style="width: 35%;">Customer Name</th>
                            <th style="width: 15%;">Total Gross</th>
                            <th style="width: 15%;">Workflow Status</th>
                            <th style="width: 20%;">Timestamp Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentOrders as $order): ?> 
                        <tr>
                            <td>
                                <span class="text-secondary font-weight-bold">#<?= htmlspecialchars((string)($order['id'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <strong class="customer-name-label"><?= htmlspecialchars((string)($order['user_name'] ?? 'Guest Profile'), ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <span class="price-data-cell"><?= formatPrice($order['total'] ?? 0) ?></span>
                            </td>
                            <td>
                                <?php $statusFlag = strtolower($order['status'] ?? 'pending'); ?>
                                <span class="status-badge status-<?= htmlspecialchars($statusFlag, ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-circle-fill small me-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                                    <?= htmlspecialchars((string)($order['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.85rem;">
                                    <?= htmlspecialchars(date('M d, Y • h:i A', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>