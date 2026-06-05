<?php
// frontend/views/pages/home.php
$pageTitle = 'GroceryDash — Fresh Grocery Delivered in 30 Minutes';
require_once __DIR__ . '/../../../backend/models/Category.php';
require __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../backend/models/Product.php';

// Initialize wishlisted IDs
$wishlistedIds = $wishlistedIds ?? [];
?>

<!-- ═══ HERO ══════════════════════════════════════════════ -->
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-text">
      <span class="hero-eyebrow">🌿 100% Fresh, Guaranteed</span>
      <h1 class="hero-heading">
        Groceries Delivered<br>
        <em>Fast &amp; Fresh</em> to Your Door
      </h1>
      <p class="hero-sub">
        Order by 10 PM for same-day delivery. Free delivery on orders above
        <?= formatPrice(FREE_DELIVERY_THRESHOLD) ?>.
      </p>
      <form class="hero-pincode" action="<?= APP_URL ?>/shop" method="GET">
        <input type="text" name="pincode" placeholder="Enter your pincode…" class="pincode-input">
        <button type="submit" class="btn btn-primary">Shop Now →</button>
      </form>
      <div class="hero-stats">
        <div><strong>5,000+</strong><span>Products</span></div>
        <div><strong>30 min</strong><span>Avg. Delivery</span></div>
        <div><strong>4.8 ★</strong><span>App Rating</span></div>
      </div>
    </div>
    <div class="hero-image">
      <div class="hero-img-placeholder" aria-hidden="true">🥦🍎🥛🥚🍞</div>
    </div>
  </div>
</section>

<!-- ═══ CATEGORIES ═════════════════════════════════════════ -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Shop by Category</h2>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= APP_URL ?>/shop?category=<?= e($cat['slug']) ?>" class="category-card">
          <div class="cat-icon" aria-hidden="true">
            <?= match($cat['slug']) {
              'vegetables' => '🥦',
              'fruits'     => '🍎',
              'dairy-eggs' => '🥛',
              'bakery'     => '🍞',
              'meat-fish'  => '🍗',
              'beverages'  => '🧃',
              default      => '🛒'
            } ?>
          </div>
          <span class="cat-name"><?= e($cat['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══ FEATURED PRODUCTS ══════════════════════════════════ -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Today's Top Picks</h2>
      <a href="<?= APP_URL ?>/shop" class="link-all">View all →</a>
    </div>
    
    <div class="products-grid">
      <?php foreach ($featured ?? [] as $product): ?>
        <?php 
          // 1. Resolve product ID keys securely
          $productId   = $product['product_id'] ?? $product['id'] ?? 0;
          $name        = $product['name'] ?? 'Unnamed Product';
          $image       = $product['image'] ?? '';

          // 2. Exact pricing calculations from product-card component
          $origPrice   = (float)($product['price'] ?? 0);
          $rawSale     = $product['sale_price'] ?? null;

          // Validate markdown status safely to protect against string 'NULL' anomalies
          $salePrice   = (is_numeric($rawSale) && (float)$rawSale > 0 && (float)$rawSale < $origPrice) ? (float)$rawSale : null;
          $currentPrice = $salePrice ?? $origPrice;
          $saving      = ($salePrice && $origPrice > 0) ? (int)round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
        ?>
        
        <article class="product-card" data-product-id="<?= $productId ?>" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
          
          <?php if ($saving > 0): ?>
            <span class="badge-sale" style="position: absolute; top: 12px; left: 12px; background: #dc3545; color: #fff; padding: 4px 8px; font-size: 0.75rem; font-weight: bold; border-radius: 4px; z-index: 5;">
                -<?= $saving ?>% OFF
            </span>
          <?php endif; ?>

          <div class="product-img-wrap" style="position: relative; text-align: center; margin-bottom: 1rem;">
            <?php if (!empty($image)): ?>
              <img src="<?= APP_URL . '/assets/images/products/' . e($image) ?>"
                   alt="<?= e($name) ?>" 
                   class="product-img"
                   style="max-height: 150px; object-fit: contain; width: auto; max-width: 100%;">
            <?php else: ?>
              <div class="product-img-placeholder">🛒</div>
            <?php endif; ?>

            <button
              type="button"
              class="wishlist-btn <?= (in_array($productId, $wishlistedIds ?? [])) ? 'wishlisted' : '' ?>"
              data-product-id="<?= (int)$productId ?>"
              data-csrf="<?= e(csrfToken()) ?>"
              aria-label="Toggle wishlist">
              <?= in_array($productId, $wishlistedIds) ? '❤️' : '🤍' ?>
            </button>
          </div> <div class="product-info" style="flex-grow: 1; display: flex; flex-direction: column; justify-content: flex-end;">
            <h3 class="product-name" style="font-size: 1rem; margin: 0 0 0.25rem 0; line-height: 1.4;">
              <a href="<?= APP_URL ?>/product/<?= $productId ?>" style="text-decoration: none; color: #212529; font-weight: 600;">
                <?= e($name) ?>
              </a>
            </h3>
            
            <div class="product-price-row" style="display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: 1rem;">
              <span class="product-price" style="color: #198754; font-weight: 700; font-size: 1.15rem;">
                <?= formatPrice($currentPrice) ?>
              </span>
              
              <?php if ($salePrice !== null): ?>
                <span class="product-orig" style="text-decoration: line-through; color: #dc3545; font-size: 0.9rem; font-weight: 500; margin-left: 4px;">
                    <?= formatPrice($origPrice) ?>
                </span>
              <?php endif; ?>
            </div>
            
            <form method="POST" action="<?= APP_URL ?>/cart/add" style="margin: 0; width: 100%;">
                <input type="hidden" name="product_id" value="<?= $productId ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" class="btn btn-primary btn-sm add-to-cart" style="width: 100%; background: #198754; color: #fff; border: none; padding: 0.6rem; border-radius: 6px; font-weight: 600; cursor: pointer;">
                  + Add to Cart
                </button>
            </form>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ VALUE PROPS ════════════════════════════════════════ -->
<section class="section">
  <div class="container">
    <div class="value-props">
      <div class="vp">
        <span class="vp-icon">⚡</span>
        <h3>30-Min Delivery</h3>
        <p>Lightning-fast delivery to your doorstep, every day of the week.</p>
      </div>
      <div class="vp">
        <span class="vp-icon">🌿</span>
        <h3>Farm Fresh</h3>
        <p>Sourced directly from local farms — fresh, never frozen.</p>
      </div>
      <div class="vp">
        <span class="vp-icon">🛡️</span>
        <h3>Freshness Guarantee</h3>
        <p>Not satisfied? We'll replace it or refund — no questions asked.</p>
      </div>
      <div class="vp">
        <span class="vp-icon">💰</span>
        <h3>Best Prices</h3>
        <p>Price-matched with your local market, every single day.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══ CTA BANNER ═════════════════════════════════════════ -->
<section class="cta-banner">
  <div class="container cta-inner">
    <div>
      <h2>Get Rs. 100 off your first order</h2>
      <p>Use code <strong>FRESH100</strong> at checkout. New customers only.</p>
    </div>
    <a href="<?= APP_URL ?>/shop" class="btn btn-white">Start Shopping</a>
  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
