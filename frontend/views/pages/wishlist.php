<?php
$pageTitle = $pageTitle ?? 'My Wishlist — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container" style="padding: 2rem 0 4rem">
  <div class="section-header" style="margin-bottom: 1.5rem">
    <h1>My Wishlist</h1>
    <?php if (!empty($items)): ?>
      <span style="color:#777">
        <?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?>
      </span>
    <?php endif; ?>
  </div>

  <?php if (empty($items)): ?>
    <div style="text-align:center; padding:4rem 0">
      <div style="font-size:4rem; margin-bottom:1rem">🤍</div>
      <h2 style="margin-bottom:0.5rem">Your wishlist is empty</h2>
      <p style="color:#777; margin-bottom:1.5rem">
        Save items you love by clicking the heart icon on any product.
      </p>
      <a href="<?= APP_URL ?>/shop" class="btn btn-primary">Browse Products</a>
    </div>

  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($items as $product):
        $productId  = (int) $product['product_id'];
        $origPrice  = (float) $product['price'];
        $rawSale    = $product['sale_price'] ?? null;
        $salePrice  = (is_numeric($rawSale) && (float)$rawSale > 0 && (float)$rawSale < $origPrice)
                        ? (float) $rawSale : null;
        $currentPrice = $salePrice ?? $origPrice;
        $saving     = $salePrice ? (int) round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
        $inStock    = (int)($product['stock'] ?? 0) > 0;
      ?>
        <article class="product-card"
                 id="wishlist-item-<?= $productId ?>"
                 style="position:relative; display:flex; flex-direction:column;">

          <?php if ($saving > 0): ?>
            <span style="position:absolute; top:12px; left:12px; background:#dc3545;
                         color:#fff; padding:4px 8px; font-size:.75rem; font-weight:700;
                         border-radius:4px; z-index:5;">
              -<?= $saving ?>% OFF
            </span>
          <?php endif; ?>

            <!-- Heart button -->
            <article class="product-card" id="wishlist-item-<?= (int)$productId ?>">
              <div class="product-img-wrap">
                  <img src="<?= APP_URL . '/assets/images/products/' . e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                  
                  <button type="button" 
                          class="wishlist-btn wishlisted" 
                          data-product-id="<?= (int)$productId ?>" 
                          data-csrf="<?= e(csrfToken()) ?>"
                          data-remove-card="wishlist-item-<?= (int)$productId ?>"
                          aria-label="Remove item directly">
                      ❤️
                  </button>
              </div>
            </article>
            
          <div class="product-info" style="flex-grow:1; display:flex; flex-direction:column;">
            <h3 class="product-name" style="font-size:1rem; margin:0 0 .25rem; line-height:1.4;">
              <a href="<?= APP_URL ?>/product/<?= $productId ?>"
                 style="text-decoration:none; color:#212529; font-weight:600;">
                <?= e($product['name']) ?>
              </a>
            </h3>

            <div style="display:flex; align-items:baseline; gap:.5rem; margin-bottom:1rem;">
              <span style="color:#198754; font-weight:700; font-size:1.15rem;">
                <?= formatPrice($currentPrice) ?>
              </span>
              <?php if ($salePrice): ?>
                <span style="text-decoration:line-through; color:#dc3545; font-size:.9rem;">
                  <?= formatPrice($origPrice) ?>
                </span>
              <?php endif; ?>
            </div>

            <form method="POST" action="<?= APP_URL ?>/cart/add" style="margin:0; width:100%;">
              <input type="hidden" name="product_id" value="<?= $productId ?>">
              <input type="hidden" name="quantity"   value="1">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <button type="submit"
                      class="btn btn-primary btn-sm"
                      style="width:100%;"
                      <?= !$inStock ? 'disabled' : '' ?>>
                <?= $inStock ? '+ Add to Cart' : 'Out of Stock' ?>
              </button>
            </form>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>