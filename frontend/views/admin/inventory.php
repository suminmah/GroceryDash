<?php
$pageTitle = 'Inventory - GroceryDash Admin';
$items = $items ?? [];
$lowCount = $lowCount ?? 0;
$token = csrfToken();
?>


<div class="admin-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Inventory</h1>
            <span class="badge bg-<?= ($lowCount > 0) ? 'danger' : 'success' ?>">
                <?= $lowCount ?> Low Stock
            </span>
            <p class="text-muted">Monitor and update stock levels for all products.</p>
        </div>
</div>
        <div class="table-container">
            <?php if (empty($items)): ?>
                <div class="p-4 text-center">No inventory items found.</div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th class="text-end">Quick Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                            </td>
                            <td>
                                <span class="stock-count <?= ($item['quantity'] < 10) ? 'low-stock' : '' ?>">
                                    <?= (int)$item['quantity'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="<?= APP_URL ?>/admin/inventory/<?= $item['product_id'] ?>/restock" class="inline-update-form">
                                    <input type="hidden" name="csrf_token" value="<?= $token ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" class="form-control-sm" min="0">
                                    <button type="submit" class="btn-update-sm">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>