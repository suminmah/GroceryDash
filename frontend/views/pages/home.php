<?php
// frontend/views/pages/home.php
$pageTitle = 'GroceryDash — Fresh Grocery Delivered in 30 Minutes';
require_once __DIR__ . '/../../../backend/models/Category.php';
require __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../backend/models/Product.php';
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
      <?php $featured = (new Product())->getFeatured(); ?>
      <?php foreach ($featured as $product): ?>
        <?php require __DIR__ . '/../../components/product-card.php'; ?>
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
