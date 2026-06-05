<?php
// components/product-card.php
if (!isset($product) || !is_array($product)) return;

/**
 * IDENTITY NORMALIZER
 * Dedicated view pages pass 'product_id', standard shop queries pass 'id'.
 * We read both fallback states and force a clean integer value.
 */
$productId    = (int)($product['product_id']);
$name         = $product['name'] ?? 'Unnamed Product';
$category     = $product['category_name'] ?? '';
$unit         = $product['unit'] ?? '';
$image        = $product['image'] ?? '';

$origPrice    = (float)($product['price'] ?? 0);
$rawSale      = $product['sale_price'] ?? null;
$salePrice    = (is_numeric($rawSale) && (float)$rawSale > 0 && (float)$rawSale < $origPrice) ? (float)$rawSale : null;
$currentPrice = $salePrice ?? $origPrice;
$saving       = ($salePrice && $origPrice > 0) ? (int)round((($origPrice - $salePrice) / $origPrice) * 100) : 0;

$stockQty     = (int)($product['stock'] ?? $product['stock_qty'] ?? 0);

// Use the dynamic unified productId instead of $pId
$isFav = in_array($productId, $wishlistProductIds ?? []); 
$escapedId = htmlspecialchars((string)$productId, ENT_QUOTES, 'UTF-8');
?>

<article class="product-card" id="product-card-<?= $escapedId ?>" data-product-id="<?= $escapedId ?>" style="position: relative; background: #fff; border: 1px solid #eef1f0; border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between;">
    
    <?php if ($saving > 0): ?>
        <span class="badge-sale" style="position: absolute; top: 10px; left: 10px; background: #dc3545; color: #fff; padding: 4px 8px; font-size: 0.75rem; font-weight: bold; border-radius: 4px; z-index: 3;">
            -<?= $saving ?>% OFF
        </span>
    <?php endif; ?>

    <button type="button"
            class="wishlist-btn <?= $isFav ? 'wishlisted' : '' ?>"
            id="wishlist-btn-<?= $escapedId ?>"
             data-product-id="<?= $escapedId ?>"
             data-csrf="<?= csrfToken() ?>"
             data-remove-card="product-card-<?= $escapedId ?>"
             title="Toggle Wishlist"
             aria-label="Toggle Wishlist"
             style="position: absolute; top: 10px; right: 10px; background: #fff; border: 1px solid #eee; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 3; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 0;">
         <span class="a11y_contrast"><?= $isFav ? '❤️' : '🤍' ?></span>
    </button>

    <a href="<?= APP_URL ?>/product/<?= $escapedId ?>" class="product-img-link" style="text-align: center; display: block; padding-top: 15px; margin-bottom: 1rem;">
        <?php if (!empty($image)): ?>
            <img class="product-img" 
                 src="<?= APP_URL . '/assets/images/products/' . e($image) ?>" 
                 alt="<?= e($name) ?>" 
                 style="max-height: 140px; object-fit: contain; width: auto; max-width: 100%;"
                 loading="lazy">
        <?php else: ?>
            <div class="product-img-placeholder" style="font-size: 2.5rem; line-height: 140px; background: #f8f9fa; border-radius: 8px; text-align: center;">🛒</div>
        <?php endif; ?>
    </a>

    <div class="product-info" style="flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-end;">
        <?php if ($category): ?>
            <span class="product-category" style="text-transform: uppercase; font-size: 0.75rem; color: #6c757d; font-weight: 600; display: block; margin-bottom: 0.25rem;"><?= e($category) ?></span>
        <?php endif; ?>

        <h3 class="product-name" style="font-size: 1rem; margin: 0 0 0.25rem 0; line-height: 1.4;">
            <a href="<?= APP_URL ?>/product/<?= $escapedId ?>" style="text-decoration: none; color: #212529; font-weight: 600;"><?= e($name) ?></a>
        </h3>

        <?php if ($unit): ?>
            <div class="product-unit" style="color: #6c757d; font-size: 0.85rem; margin-bottom: 0.5rem;"><?= e($unit) ?></div>
        <?php endif; ?>

        <div class="product-price-row" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <span class="product-price" style="color: #198754; font-weight: 700; font-size: 1.2rem;"><?= function_exists('formatPrice') ? formatPrice($currentPrice) : 'Rs. ' . number_format($currentPrice, 2) ?></span>
            <?php if ($salePrice !== null): ?>
                <span class="product-orig" style="text-decoration: line-through; color: #6c757d; font-size: 0.85rem;"><?= function_exists('formatPrice') ? formatPrice($origPrice) : 'Rs. ' . number_format($origPrice, 2) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($stockQty <= 0): ?>
        <button class="btn-add-cart" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed; padding: 0.5rem; border-radius: 6px; background: #eee; border: 1px solid #ccc;">Out of Stock</button>
    <?php else: ?>
        <form method="POST" action="<?= APP_URL ?>/cart/add" class="add-to-cart-form" style="margin: 0; width: 100%;">
            <input type="hidden" name="product_id" value="<?= $escapedId ?>">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <button type="submit" class="btn-add-cart js-add-to-cart" data-product-id="<?= $escapedId ?>" data-csrf="<?= csrfToken() ?>" style="width: 100%; background: #198754; color: #fff; border: none; padding: 0.5rem; border-radius: 6px; font-weight: 600; cursor: pointer;">+ Add to Cart</button>
        </form>
    <?php endif; ?>
</article>