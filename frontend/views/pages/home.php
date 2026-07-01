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
    <div class="hero-inner">
        
        <div class="hero-content-left">
            <span class="hero-eyebrow">100% Fresh, Guaranteed</span>
            <h1 class="hero-heading">Groceries Delivered <em>Fast & Fresh</em> to Your Door</h1>
            <p class="hero-sub">Order by 10 PM for same-day delivery. Free delivery on orders above Rs. 500.00.</p>
            
            <form class="hero-pincode" method="GET" action="<?= APP_URL ?>/shop">
                <input type="text" name="pincode" class="pincode-input" placeholder="Enter your pincode...">
                <button type="submit" class="search-btn" style="border-radius:8px; padding: 0 1.5rem;">Shop Now</button>
            </form>
            
            <div class="hero-stats">
                <div><strong>5,000+</strong><span>Products</span></div>
                <div><strong>30 min</strong><span>Avg Delivery</span></div>
                <div><strong>4.8 ★</strong><span>App Rating</span></div>
            </div>
        </div>

        <div class="hero-content-right">
            <img src="<?= APP_URL ?>/assets/images/hero_grocery_banner.png" alt="Fresh Groceries" class="hero-image" style="width:100%; border-radius: 12px; box-shadow: 0 12px 24px rgba(0,0,0,0.06); object-fit: cover; max-height: 380px;">
        </div>

    </div>
</section>

<!-- ═══ CATEGORIES ═════════════════════════════════════════ -->
<section class="section-full-width section-alt">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Shop by Category</h2>
      <a href="<?= APP_URL ?>/categories" class="link-all">View all →</a>
    </div>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= APP_URL ?>/shop?category=<?= e($cat['slug']) ?>" class="category-card">
          <div class="cat-icon" aria-hidden="true">
            <?= match($cat['slug']) {
              'vegetables'   => '🥦',
              'fruits'       => '🍎',
              'dairy-eggs'   => '🥛',
              'bakery'       => '🍞',
              'meat-fish'    => '🍗',
              'beverages'    => '🧃',
              'organic-food' => '🌿',
              'canned-foods' => '🥫',
              'oil'          => '🫒',
              default        => '🛒'
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
      <?php 
        $wishlistProductIds = $wishlistedIds ?? [];
        foreach ($featured ?? [] as $product): 
      ?>
        <?php require __DIR__ . '/../../components/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ VALUE PROPS ════════════════════════════════════════ -->
<section class="section-full-width section-alt">
  <div class="container">
    
    <div class="value-props">
      
      <div class="vp">
        <span class="vp-icon" aria-hidden="true">⚡</span>
        <h3>30-Min Delivery</h3>
        <p>Lightning-fast delivery to your doorstep, every day of the week.</p>
      </div>
      
      <div class="vp">
        <span class="vp-icon" aria-hidden="true">🌿</span>
        <h3>Farm Fresh</h3>
        <p>Sourced directly from local farms — fresh, never frozen.</p>
      </div>
      
      <div class="vp">
        <span class="vp-icon" aria-hidden="true">🛡️</span>
        <h3>Freshness Guarantee</h3>
        <p>Not satisfied? We'll replace it or refund — no questions asked.</p>
      </div>
      
      <div class="vp">
        <span class="vp-icon" aria-hidden="true">💰</span>
        <h3>Best Prices</h3>
        <p>Price-matched with your local market, every single day.</p>
      </div>
      
    </div>
    
  </div>
</section>

<!-- ═══ CTA BANNER ═════════════════════════════════════════ -->
<section class="cta-banner">
  <div class="container cta-inner">
    
    <div class="cta-message-block">
      <h2>Get Rs. 100 off your first order</h2>
      <p>Use code <strong style="text-transform: uppercase; background: rgba(255,255,255,0.15); padding: 2px 6px; border-radius: 4px;">FRESH100</strong> at checkout. New customers only.</p>
    </div>
    
    <a href="<?= APP_URL ?>/shop" class="btn-white">Start Shopping</a>
    
  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
