<?php
$pageTitle = 'About Us — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<section class="premium-about-section" style="padding-top: 2rem;">
  <div class="container" style="max-width: 1200px;">
    
    <div class="premium-about-hero">
      <h1>About GroceryDash</h1>
      <p>Your trusted partner for fresh, quality groceries, delivered exceptionally fast right to your doorstep.</p>
    </div>

    <div class="premium-about-grid">
      <div class="premium-about-card">
        <h2><i class="bi bi-book"></i> Our Story</h2>
        <p style="font-size: 1.05rem; line-height: 1.7; color: #475569; margin-bottom: 1rem;">GroceryDash was founded in 2024 with a simple mission: to make fresh, quality groceries accessible to everyone in Kathmandu Valley. What started as a small family-run delivery service has quickly grown into a trusted platform serving thousands of happy customers every day.</p>
        <p style="font-size: 1.05rem; line-height: 1.7; color: #475569; margin: 0;">We believe that grocery shopping should be convenient, affordable, and enjoyable. That’s why we hand-pick every product, work directly with local farmers and trusted brands, and ensure your order reaches you perfectly.</p>
      </div>

      <div class="premium-about-card">
        <h2><i class="bi bi-bullseye"></i> Our Mission</h2>
        <ul class="premium-mission-list">
          <li><i class="bi bi-check-circle-fill"></i> <span><strong>Freshness guaranteed</strong> – We source directly from farms and trusted suppliers to bring you the best.</span></li>
          <li><i class="bi bi-check-circle-fill"></i> <span><strong>Fast & reliable delivery</strong> – Enjoy same‑day delivery when you order before 2 PM.</span></li>
          <li><i class="bi bi-check-circle-fill"></i> <span><strong>Unbeatable value</strong> – Competitive prices with regular offers, cashbacks, and bulk discounts.</span></li>
          <li><i class="bi bi-check-circle-fill"></i> <span><strong>Zero Hassle</strong> – Easy returns, cash on delivery, and dedicated 24/7 customer support.</span></li>
        </ul>
      </div>
    </div>

    <div class="premium-about-stats">
      <div class="premium-stat">
        <span class="premium-stat-number">5K+</span>
        <span class="premium-stat-label">Happy Customers</span>
      </div>
      <div class="premium-stat">
        <span class="premium-stat-number">200+</span>
        <span class="premium-stat-label">Local Products</span>
      </div>
      <div class="premium-stat">
        <span class="premium-stat-number">30m</span>
        <span class="premium-stat-label">Average Pick Time</span>
      </div>
      <div class="premium-stat">
        <span class="premium-stat-number">100%</span>
        <span class="premium-stat-label">Quality Checked</span>
      </div>
    </div>

    <div class="premium-team-section">
      <h2>Meet the Team</h2>
      <p style="color: #475569; font-size: 1.15rem; max-width: 600px; margin: 0 auto 3rem auto;">We are a passionate group of food lovers, tech enthusiasts, and delivery experts committed to making your grocery experience delightful.</p>
      
      <div class="premium-team-grid">
        <div class="premium-team-card">
          <img src="<?= APP_URL ?>/assets/images/team/Avatar1.png" alt="Founder" style="display: block; margin: 0 auto 1.5rem auto;">
          <h3 style="margin-bottom: 0;">Sumin Shrestha</h3>
        </div>
        <div class="premium-team-card">
          <img src="<?= APP_URL ?>/assets/images/team/Avatar2.png" alt="Co-Founder" style="display: block; margin: 0 auto 1.5rem auto;">
          <h3 style="margin-bottom: 0;">Rohan Maharjan</h3>
        </div>
        <div class="premium-team-card">
          <img src="<?= APP_URL ?>/assets/images/team/Avatar3.png" alt="Co-Founder" style="display: block; margin: 0 auto 1.5rem auto;">
          <h3 style="margin-bottom: 0;">Dikshya Maharjan</h3>
        </div>
      </div>
    </div>

    <div class="premium-about-contact">
      <h2>We'd Love to Hear From You</h2>
      <p>Have a question or need assistance? Our support team is available everyday from 9 AM to 8 PM.</p>
      
      <div class="contact-grid">
        <div class="contact-item">
          <i class="bi bi-telephone-fill"></i>
          <h4 style="margin-bottom: 0.5rem; font-size: 1.1rem;">Call Us</h4>
          <span style="color: #475569; font-weight: 500;">+977 980-1234567</span>
        </div>
        <div class="contact-item">
          <i class="bi bi-envelope-fill"></i>
          <h4 style="margin-bottom: 0.5rem; font-size: 1.1rem;">Email Us</h4>
          <span style="color: #475569; font-weight: 500;">hello@grocerydash.com</span>
        </div>
        <div class="contact-item">
          <i class="bi bi-geo-alt-fill"></i>
          <h4 style="margin-bottom: 0.5rem; font-size: 1.1rem;">Visit Us</h4>
          <span style="color: #475569; font-weight: 500;">Lazimpat, Kathmandu</span>
        </div>
      </div>

      <div style="margin-top: 3rem;">
        <a href="<?= APP_URL ?>/help" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 50px; font-weight: 600;">Visit Help Center <i class="bi bi-arrow-right ms-2"></i></a>
      </div>
    </div>

  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>