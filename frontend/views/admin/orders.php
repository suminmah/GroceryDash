<?php 
$pageTitle = $pageTitle ?? 'Admin Orders — GroceryDash'; 
$orders = $orders ?? []; 
$totalPages = $totalPages ?? 1; 
$page = $page ?? 1;
?>

<div class="admin-main">
    <h1>Orders</h1>
         <a href="<?= APP_URL ?>/admin/orders?status=pending" class="btn-primary">
                View Pending Orders
        </a>
</div>
        <div class="table-container">
            <?php if (empty($orders)): ?>
                <div class="p-4 text-center">No orders found.</div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                            </td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="<?= APP_URL ?>/admin/orders/<?= $order['id'] ?>" class="btn-view">
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
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-btn <?= ($page == $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>