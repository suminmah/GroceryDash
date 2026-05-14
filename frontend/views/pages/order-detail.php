<?php $pageTitle = $pageTitle ?? 'Order Detail'; 

require __DIR__ . '/../layouts/header.php'; 

$order = $order ?? []; ?>

<h1>Order #<?= $order['id'] ?? 'N/A' ?></h1>
<p>Customer: <?= e($order['customer_name']) ?> (<?= e($order['customer_email']) ?>)</p>
<p>Status: <?= e($order['status']) ?></p>
<p>Total: <?= formatPrice($order['total']) ?></p>
<h3>Items</h3>
<ul>
<?php foreach ($order['items'] as $item): ?>
    <li><?= e($item['name']) ?> x <?= $item['quantity'] ?> = <?= formatPrice($item['line_total']) ?></li>
<?php endforeach; ?>
</ul>
<form method="POST" action="<?= APP_URL ?>/admin/orders/<?= $order['id'] ?>/status">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <select name="status">
        <option value="Pending">Pending</option>
        <option value="Confirmed">Confirmed</option>
        <option value="Out for Delivery">Out for Delivery</option>
        <option value="Delivered">Delivered</option>
        <option value="Cancelled">Cancelled</option>
    </select>
    <button type="submit">Update Status</button>
</form>
<?php require __DIR__ . '/../layouts/footer.php'; ?>