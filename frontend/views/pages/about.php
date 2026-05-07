<?php
$pageTitle = 'About Us — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<section class="about-section">
  <div class="container">
    <div class="about-hero">
      <h1>About GroceryDash</h1>
      <p>Your trusted partner for fresh groceries, delivered fast.</p>
    </div>

    <div class="about-grid">
      <div class="about-story">
        <h2>Our Story</h2>
        <p>GroceryDash was founded in 2024 with a simple mission: to make fresh, quality groceries accessible to everyone in Kathmandu Valley. What started as a small family-run delivery service has grown into a trusted platform serving thousands of happy customers every day.</p>
        <p>We believe that grocery shopping should be convenient, affordable, and enjoyable. That’s why we hand-pick every product, work directly with local farmers and trusted brands, and ensure your order reaches you on time – every time.</p>
      </div>

      <div class="about-mission">
        <h2>Our Mission</h2>
        <ul>
          <li>✅ <strong>Freshness guaranteed</strong> – We source directly from farms and trusted suppliers.</li>
          <li>✅ <strong>Fast & reliable delivery</strong> – Same‑day delivery when you order before 2 PM.</li>
          <li>✅ <strong>Best value</strong> – Competitive prices with regular offers and bulk discounts.</li>
          <li>✅ <strong>Zero Hassle</strong> – Easy returns, cash on delivery, and 24/7 customer support.</li>
        </ul>
      </div>
    </div>

    <div class="about-stats">
      <div class="stat">
        <span class="stat-number">5000+</span>
        <span class="stat-label">Happy Customers</span>
      </div>
      <div class="stat">
        <span class="stat-number">200+</span>
        <span class="stat-label">Local Products</span>
      </div>
      <div class="stat">
        <span class="stat-number">30 min</span>
        <span class="stat-label">Average Pick Time</span>
      </div>
      <div class="stat">
        <span class="stat-number">100%</span>
        <span class="stat-label">Quality Checked</span>
      </div>
    </div>

    <div class="about-team">
      <h2>Meet the Team</h2>
      <div class="team-grid">
        <div class="team-card">
          <img src="<?= APP_URL ?>/assets/images/team/Avatar1.png" alt="Founder">
          <h3>Sumin Shrestha</h3>
          <p>Founder & CEO</p>
        </div>
        <div class="team-card">
          <img src="<?= APP_URL ?>/assets/images/team/Avatar2.png" alt="Co-Founder">
          <h3>Rohan Maharjan</h3>
          <p>Co-Founder</p>
        </div>
        <!-- Add more team members as needed -->
      </div>
      <p style="text-align:center; margin-top:1rem;">We are a passionate group of food lovers, tech enthusiasts, and delivery experts committed to making your grocery experience delightful.</p>
    </div>

    <div class="about-contact">
      <h2>Get in Touch</h2>
      <p>📞 <strong>Customer Support:</strong> +977 980-1234567 (9 AM – 8 PM, everyday)</p>
      <p>✉️ <strong>Email:</strong> hello@grocerydash.com</p>
      <p>📍 <strong>Office:</strong> Lazimpat, Kathmandu, Nepal</p>
      <p><a href="<?= APP_URL ?>/help" class="btn-outline">Go to Help Center →</a></p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>