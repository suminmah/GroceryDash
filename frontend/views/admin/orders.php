<?php 
/**
 * Admin Orders Dashboard Overview View
 * @var string|null $pageTitle
 * @var array $orders
 * @var int $totalPages
 * @var int $page
 */
$pageTitle = $pageTitle ?? 'Admin Orders — GroceryDash'; 
$orders = $orders ?? []; 
$totalPages = (int)($totalPages ?? 1); 
$page = (int)($page ?? 1);

// Retain existing URL parameters (like status filtering) during pagination step actions
$queryParams = $_GET;
?>

<div class="admin-main">
    <div class="admin-header-flex">
        <h1>Orders Management</h1>
        <a href="<?= APP_URL ?>/admin/orders?status=pending" class="btn-primary">
            ⚠️ View Pending Orders
        </a>
    </div>
</div>

<div class="table-container">
    <?php if (empty($orders)): ?>
        <div class="p-4 text-center text-muted">No operational orders found matching criteria.</div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Total Capital</th>
                    <th>Status Flag</th>
                    <th>Timestamp Date</th>
                    <th class="text-end">Action Control</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td>
                        <span class="customer-name-label">
                            <?= htmlspecialchars((string)$order['customer_name'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="price-data-cell"><?= formatPrice($order['total']) ?></td>
                    <td>
                        <?php $statusLower = strtolower($order['status'] ?? 'pending'); ?>
                        <span class="status-badge status-<?= htmlspecialchars($statusLower, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string)($order['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(date('M d, Y • h:i A', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/admin/orders/<?= htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-view">
                            View Details
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="pagination-container" aria-label="Orders page pagination navigation">
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): 
            $queryParams['page'] = $i;
            $queryString = http_build_query($queryParams);
        ?>
            <a href="?<?= $queryString ?>" class="page-btn <?= ($page === $i) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</nav>
<?php endif; ?>