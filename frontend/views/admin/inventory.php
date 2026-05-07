<?php
$pageTitle = 'Inventory';
require __DIR__ . '/../layouts/header.php';
$items = $items ?? [];
?>
<h1>Inventory</h1>
<table class="admin-table">
    <thead><tr><th>Product</th><th>Current Stock</th><th>Update</th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= e($item['product_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>
                <form method="POST" action="<?= APP_URL ?>/admin/inventory/<?= $item['product_id'] ?>/restock">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="number" name="stock_qty" value="<?= $item['quantity'] ?>">
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/footer.php'; ?>