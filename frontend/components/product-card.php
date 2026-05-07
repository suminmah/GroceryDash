<?php
// frontend/components/product-card.php
if (!isset($product) || !is_array($product)) return;

// Safe defaults
$productId   = $product['product_id'] ?? $product['id'] ?? 0;
$name        = $product['name'] ?? 'Unnamed Product';
$category    = $product['category_name'] ?? '';
$unit        = $product['unit'] ?? '';
$origPrice   = (float)($product['price'] ?? 0);
$salePrice   = isset($product['sale_price']) ? (float)$product['sale_price'] : null;
$price       = $salePrice ?? $origPrice;
$saving      = ($salePrice && $origPrice > 0) ? round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
$image       = $product['image'] ?? '';
$stockQty    = (int)($product['stock_qty'] ?? 0);
?>
<article class="product-card" data-product-id="<?= $productId ?>">
    <?php if ($saving > 0): ?>
        <span class="badge-sale">-<?= $saving ?>%</span>
    <?php endif; ?>

    <a href="<?= APP_URL ?>/product/<?= $productId ?>" class="product-img-link">
        <img class="product-img" 
             src="<?= productImageUrl($image) ?>" 
             alt="<?= e($name) ?>" 
             width="200" height="200">
    </a>

    <div class="product-info">
        <?php if ($category): ?>
            <span class="product-category"><?= e($category) ?></span>
        <?php endif; ?>

        <h3 class="product-name">
            <a href="<?= APP_URL ?>/product/<?= $productId ?>"><?= e($name) ?></a>
        </h3>

        <?php if ($unit): ?>
            <div class="product-unit"><?= e($unit) ?></div>
        <?php endif; ?>

        <div class="product-price-row">
            <span class="product-price"><?= formatPrice($price) ?></span>
            <?php if ($salePrice): ?>
                <span class="product-orig"><?= formatPrice($origPrice) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($stockQty <= 0): ?>
        <button class="btn-add-cart" disabled>Out of Stock</button>
    <?php else: ?>
        <form method="POST" action="<?= APP_URL ?>/cart/add" class="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <button type="submit" class="btn-add-cart">+ Add to Cart</button>
        </form>
    <?php endif; ?>
</article>