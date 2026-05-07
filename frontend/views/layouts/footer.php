<?php // frontend/views/layouts/footer.php ?>
</main><!-- /main-content -->

<footer class="site-footer">
  <div class="container footer-grid">

    <div class="footer-brand">
      <div class="logo" style="font-size:1.4rem">🛒 Grocery<strong>Dash</strong></div>
      <p>Your neighbourhood grocery store, delivered fresh to your door in 30 minutes or less.</p>
      <div class="social-links">
        <a href="#" aria-label="Facebook">FB</a>
        <a href="#" aria-label="Instagram">IG</a>
        <a href="#" aria-label="Twitter">TW</a>
      </div>
    </div>

    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="<?= APP_URL ?>/shop">All Products</a></li>
        <li><a href="<?= APP_URL ?>/offers">Today's Offers</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=vegetables">Vegetables</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=fruits">Fruits</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=dairy-eggs">Dairy & Eggs</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Support</h4>
      <ul>
        <li><a href="<?= APP_URL ?>/help">Help Centre</a></li>
        <li><a href="<?= APP_URL ?>/delivery">Delivery Info</a></li>
        <li><a href="<?= APP_URL ?>/about">About Us</a></li>
        <li><a href="<?= APP_URL ?>/order/track/">Track Order</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Account</h4>
      <ul>
        <?php if (isLoggedIn()): ?>
          <li><a href="<?= APP_URL ?>/account/orders">My Orders</a></li>
          <li><a href="<?= APP_URL ?>/logout">Logout</a></li>
        <?php else: ?>
          <li><a href="<?= APP_URL ?>/login">Sign In</a></li>
          <li><a href="<?= APP_URL ?>/register">Create Account</a></li>
        <?php endif; ?>
        <li><a href="<?= APP_URL ?>/cart">My Cart</a></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="container">
      <span>© <?= date('Y') ?> GroceryDash. All rights reserved.</span>
      <span class="footer-legal">
        <a href="#">Privacy Policy</a> · <a href="#">Terms of Use</a>
      </span>
    </div>
  </div>
</footer>

<!-- Mobile bottom nav -->
<nav class="mobile-nav" aria-label="Mobile navigation">
  <a href="<?= APP_URL ?>/"     class="mob-nav-item">🏠<span>Home</span></a>
  <a href="<?= APP_URL ?>/shop" class="mob-nav-item">🛍️<span>Shop</span></a>
  <a href="<?= APP_URL ?>/search" class="mob-nav-item">🔍<span>Search</span></a>
  <a href="<?= APP_URL ?>/cart" class="mob-nav-item">
    🛒<span>Cart</span>
    <span class="cart-badge" id="cartBadgeMob"><?= cartCount() ?: '' ?></span>
  </a>
  <a href="<?= APP_URL ?>/<?= isLoggedIn() ? 'account/orders' : 'login' ?>" class="mob-nav-item">👤<span>Account</span></a>
</nav>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
