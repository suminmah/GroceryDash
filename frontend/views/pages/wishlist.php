<?php
$pageTitle = $pageTitle ?? 'My Wishlist — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container" style="padding: 2rem 0 4rem">
  <div class="section-header" style="margin-bottom: 1.5rem">
    <h1>My Wishlist</h1>
    <?php if (!empty($items)): ?>
      <span style="color:#777"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
    <?php endif; ?>
  </div>

  <?php if (empty($items)): ?>
    <div class="empty-wishlist" style="text-align: center; padding: 3rem 1rem;">
        <span style="font-size: 4rem;">🤍</span>
        <h2>Your wishlist is empty!</h2>
        <p>Explore our shop and tap the heart icon to save items here.</p>
        <a href="<?= APP_URL ?>/" class="btn" style="background: #198754; color: #fff; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 1rem;">Go Shopping</a>
    </div>
  <?php else: ?>
    <?php 
        // This array is used by the product-card component to highlight the heart
        $wishlistProductIds = array_map(function($item) {
            return (int)$item['product_id'];
        }, $items);
    ?>
    <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; padding: 1rem 0;">
        <?php foreach ($items as $product): ?>
            <?php include __DIR__ . '/../components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>