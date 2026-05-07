<?php $pageTitle = $pageTitle ?? 'Admin Orders'; ?>
<?php $orders = $orders ?? []; ?>
<?php $pages = $pages ?? 1; ?>

<div class=admin-main>
<h1>Orders</h1>

<?php if (empty($orders)): ?>
    <p>No orders found.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['order_id'] ?></td>      <!-- changed from 'id' -->
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= formatPrice($order['total_amount']) ?></td>   <!-- changed from 'total' -->
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                <td><a href="<?= APP_URL ?>/admin/orders/<?= $order['order_id'] ?>">View</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?>" class="<?= ($i == ($_GET['page'] ?? 1)) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
</div>