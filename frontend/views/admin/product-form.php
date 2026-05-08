<?php $pageTitle = $pageTitle ?? 'Add Product — Admin'; 
require __DIR__ . '/../layouts/header.php'; 
$product = $product ?? null;
?>

<h1><?= $product ? 'Edit' : 'Add' ?> Product</h1>
<form method="POST" action="<?= APP_URL ?>/admin/products<?= $product ? '/' . $product['product_id'] : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <label>Name: <input type="text" name="name" value="<?= e($product['name'] ?? '') ?>" required></label>
    <label>Category: 
        <select name="category_id">
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Price: <input type="number" step="0.01" name="price" value="<?= $product['price'] ?? '' ?>" required></label>
    <button type="submit">Save</button>
</form>
<?php require __DIR__ . '/../layouts/footer.php'; ?>