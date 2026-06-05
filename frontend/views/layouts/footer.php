<footer class="site-footer">
  <div class="footer-inner">
    
    <div class="footer-brand-col">
      <a href="<?= APP_URL ?>/" class="logo">
        <span class="logo-text">Grocery<strong>Dash</strong></span>
      </a>
      <p class="footer-bio">
        Your neighborhood grocery store, delivered fresh to your door in 30 minutes or less. Clean, fresh, guaranteed.
      </p>
      <div class="footer-socials">
        <a href="#" class="social-icon-btn" aria-label="Facebook">FB</a>
        <a href="#" class="social-icon-btn" aria-label="Instagram">IG</a>
        <a href="#" class="social-icon-btn" aria-label="Twitter">TW</a>
      </div>
    </div>

    <div class="footer-links-col">
      <h3 class="footer-heading">Shop</h3>
      <ul class="footer-links-list">
        <li><a href="<?= APP_URL ?>/shop" class="footer-link">All Products</a></li>
        <li><a href="<?= APP_URL ?>/offers" class="footer-link">Today's Offers</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=vegetables" class="footer-link">Vegetables</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=fruits" class="footer-link">Fruits</a></li>
        <li><a href="<?= APP_URL ?>/shop?category=dairy-eggs" class="footer-link">Dairy & Eggs</a></li>
      </ul>
    </div>

    <div class="footer-links-col">
      <h3 class="footer-heading">Support</h3>
      <ul class="footer-links-list">
        <li><a href="<?= APP_URL ?>/help" class="footer-link">Help Centre</a></li>
        <li><a href="<?= APP_URL ?>/delivery" class="footer-link">Delivery Info</a></li>
        <li><a href="<?= APP_URL ?>/about" class="footer-link">About Us</a></li>
        <li><a href="<?= APP_URL ?>/account/orders" class="footer-link">Track Order</a></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <p class="copyright-text">
        &copy; <?= date('Y') ?> GroceryDash. All rights reserved.
      </p>
      <div class="footer-legal-links">
        <a href="<?= APP_URL ?>/privacy" class="footer-legal-link">Privacy Policy</a>
        <a href="<?= APP_URL ?>/terms" class="footer-legal-link">Terms of Use</a>
      </div>
    </div>
  </div>
</footer>

<button id="scrollToTopBtn" class="scroll-btn-top" aria-label="Scroll back to top horizontal boundary" title="Go to top">
  ▲
</button>


<script>
document.addEventListener("DOMContentLoaded", function () {
  const scrollBtn = document.getElementById("scrollToTopBtn");

  if (scrollBtn) {
    // Reveal Scroll Trigger Button gracefully based on screen matrix coordinate depth
    window.addEventListener("scroll", () => {
      if (window.scrollY > 400) {
        scrollBtn.classList.add("show");
      } else {
        scrollBtn.classList.remove("show");
      }
    });

    // Handle automated smooth scrolling execution
    scrollBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  }
});
</script>