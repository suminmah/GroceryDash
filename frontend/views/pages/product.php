<?php
// frontend/views/pages/product.php
$product = $product ?? [];
$product = array_merge([
    'name'          => 'Product',
    'sale_price'    => null,
    'price'         => 0,
    'category_slug' => '',
    'category_name' => '',
    'image'         => APP_URL . '/assets/images/products/',
    'unit'          => '',
    'stock'         => 0,
    'description'   => '',
    'id'            => 0,
], $product);

$pageTitle = e($product['name']) . ' — GroceryDash';
$price     = $product['sale_price'] ?? $product['price'];
$origPrice = $product['price'];
$saving    = $product['sale_price']
    ? round((($origPrice - $price) / $origPrice) * 100) : 0;

require __DIR__ . '/../layouts/header.php';
?>

<div class="container" style="padding-top:2rem">
  <!-- Breadcrumb -->
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="<?= APP_URL ?>/">Home</a> /
    <a href="<?= APP_URL ?>/shop?category=<?= e($product['category_slug']) ?>">
      <?= e($product['category_name']) ?>
    </a> /
    <span><?= e($product['name']) ?></span>
  </nav>

  <div class="product-detail-layout">

    <!-- Images -->
    <div class="product-gallery">
      <img
        src="<?= productImageUrl($product['image'] ?? '') ?>"
        alt="<?= htmlspecialchars($product['name']) ?>"
        class="product-main-img"
        width="500" height="500">
    </div>

    <!-- Info -->
    <div class="product-detail-info">
      <span class="product-category"><?= e($product['category_name']) ?></span>
      <h1 class="product-detail-name"><?= e($product['name']) ?></h1>
      <div class="product-detail-unit">Per <?= e($product['unit']) ?></div>

      <div class="product-detail-price">
        <span class="price-big"><?= formatPrice((float)$price) ?></span>
        <?php if ($saving > 0): ?>
          <span class="price-orig"><?= formatPrice((float)$origPrice) ?></span>
          <span class="badge-sale">Save <?= $saving ?>%</span>
        <?php endif; ?>
      </div>

      <div class="stock-badge <?= (int)$product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
        <?= (int)$product['stock'] > 0
          ? '✓ In Stock — Delivery today'
          : '✗ Currently out of stock' ?>
      </div>

      <?php if ((int)$product['stock'] > 0): ?>
        <div class="qty-add-row">
          <div class="qty-control">
            <button class="qty-btn" id="qtyMinus">−</button>
            <input type="number" id="qtyInput" value="1" min="1" max="<?= (int)$product['stock'] ?>" class="qty-input" readonly>
            <button class="qty-btn" id="qtyPlus">+</button>
          </div>
          <button
            class="btn btn-primary btn-lg js-add-to-cart"
            data-product-id="<?= $product['id'] ?>"
            data-qty-input="qtyInput"
            data-csrf="<?= csrfToken() ?>">
            🛒 Add to Cart
          </button>
        </div>
      <?php endif; ?>

      <?php if ($product['description']): ?>
        <div class="product-description">
          <h3>About this product</h3>
          <p><?= nl2br(e($product['description'])) ?></p>
        </div>
      <?php endif; ?>

      <div class="product-meta">
        <div class="meta-item">🚚 <strong>Delivery:</strong> Same day before 8 PM</div>
        <div class="meta-item">🔄 <strong>Returns:</strong> Easy return within 24 hrs</div>
        <div class="meta-item">✅ <strong>Quality:</strong> Farm-fresh guarantee</div>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($related)): ?>
    <section class="section" style="margin-top:3rem">
      <h2 class="section-title">You might also like</h2>
      <div class="products-grid">
        <?php foreach ($related as $product): ?>
          <?php require __DIR__ . '/../../components/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
