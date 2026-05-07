<?php
$pageTitle = $pageTitle ?? 'Products — Admin';
$products = $products ?? [];
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
?>


    <div class="admin-main">
        <h1>Products</h1>
        <a href="<?= APP_URL ?>/admin/products/new" class="btn-primary">+ Add Product</a>
    </div>
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product['product_id'] ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= formatPrice($product['price']) ?></td>
                <td><?= (int)($product['stock_qty'] ?? 0) ?></td>
                <td><a href="<?= APP_URL ?>/admin/products/<?= $product['product_id'] ?>/edit">Edit</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-btn <?= ($page == $i) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

