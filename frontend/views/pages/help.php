<?php
$pageTitle = 'Help Center — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<section class="help-section">
  <div class="container">
    <div class="help-header">
      <h1>How can we help you?</h1>
      <p>Find answers to common questions or contact our support team.</p>
    </div>

    <!-- Search / quick links -->
    <div class="help-search">
      <form method="GET" action="<?= APP_URL ?>/help">
        <input type="text" name="q" placeholder="Search for help (e.g., delivery, payment, returns)" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit">Search</button>
      </form>
    </div>

    <!-- FAQ Categories -->
    <div class="help-faq-grid">
      <div class="faq-category">
        <h2>🛒 Orders & Payment</h2>
        <details>
          <summary>How do I place an order?</summary>
          <p>Browse products, add items to your cart, proceed to checkout, enter delivery details, choose a payment method, and confirm your order. You'll receive an SMS/email confirmation.</p>
        </details>
        <details>
          <summary>What payment methods do you accept?</summary>
          <p>We accept Cash on Delivery (COD), credit/debit cards (Visa, Mastercard), eSewa, Khalti, Fonepay, and bank transfers.</p>
        </details>
        <details>
          <summary>Can I modify or cancel my order?</summary>
          <p>Yes, you can cancel within 2 hours of placing the order or before the order is packed. Go to <a href="<?= APP_URL ?>/account/orders">My Orders</a> and click "Cancel".</p>
        </details>
      </div>

      <div class="faq-category">
        <h2>🚚 Delivery</h2>
        <details>
          <summary>What are your delivery hours?</summary>
          <p>We deliver 7 days a week from 8 AM to 8 PM. Same-day delivery available for orders placed before 2 PM.</p>
        </details>
        <details>
          <summary>How much is delivery fee?</summary>
          <p>Free delivery on orders above Rs. 500. A flat ₹40 fee applies for orders below Rs. 500.</p>
        </details>
        <details>
          <summary>Can I choose a specific delivery time?</summary>
          <p>Yes, during checkout you can select an available delivery slot (morning, afternoon, or evening).</p>
        </details>
      </div>

      <div class="faq-category">
        <h2>🔄 Returns & Refunds</h2>
        <details>
          <summary>What is your return policy?</summary>
          <p>You can return damaged, expired, or incorrect items within 24 hours of delivery. We offer full refund or replacement.</p>
        </details>
        <details>
          <summary>How do I request a return?</summary>
          <p>Call our support at +977 980-1234567 or email hello@grocerydash.com with your order ID and photos of the item.</p>
        </details>
        <details>
          <summary>How long does a refund take?</summary>
          <p>Refunds are processed within 3–5 business days to your original payment method.</p>
        </details>
      </div>

      <div class="faq-category">
        <h2>👤 Account & Security</h2>
        <details>
          <summary>How do I reset my password?</summary>
          <p>Click "Forgot Password" on the login page. We'll send a reset link to your registered email.</p>
        </details>
        <details>
          <summary>How do I delete my account?</summary>
          <p>Contact customer support with your account details. Account deletion is permanent.</p>
        </details>
        <details>
          <summary>Is my payment information secure?</summary>
          <p>Yes, we use SSL encryption and never store your full card details.</p>
        </details>
      </div>
    </div>

    <!-- Contact Support -->
    <div class="help-contact">
      <h2>Still need help?</h2>
      <div class="contact-options">
        <div class="contact-card">
          <span class="contact-icon">💬</span>
          <h3>Live Chat</h3>
          <p>Available 9 AM – 8 PM</p>
          <button class="btn-outline" id="start-chat">Start Chat</button>
        </div>
        <div class="contact-card">
          <span class="contact-icon">📞</span>
          <h3>Call Us</h3>
          <p>+977 980-1234567</p>
          <p>9 AM – 8 PM, everyday</p>
        </div>
        <div class="contact-card">
          <span class="contact-icon">✉️</span>
          <h3>Email</h3>
          <p>hello@grocerydash.com</p>
          <p>Response within 24 hrs</p>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Simple live chat simulation – replace with actual widget if needed
document.getElementById('start-chat')?.addEventListener('click', function() {
  alert('Our support team is online. Please email or call for immediate assistance.');
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>