<?php
$pageTitle = 'Help Center — GroceryDash';
require __DIR__ . '/../layouts/header.php';
?>

<section class="premium-help-section">
  <div class="premium-help-hero">
    <h1>How can we help you?</h1>
    <p style="font-size: 1.2rem; color: var(--green); margin-bottom: 2rem;">Find answers to common questions or contact our support team.</p>
    
    <div class="premium-search-box">
      <form method="GET" action="<?= APP_URL ?>/help" style="display: flex; width: 100%;">
        <i class="bi bi-search" style="font-size: 1.2rem; color: #94a3b8; padding: 1rem 0 1rem 1.5rem; align-self: center;"></i>
        <input type="text" name="q" placeholder="Search for help (e.g., delivery, returns)" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit">Search</button>
      </form>
    </div>
  </div>

  <div class="premium-faq-grid">
    <div class="premium-faq-category">
      <h2><i class="bi bi-cart3"></i> Orders & Payment</h2>
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
        <p>Yes, you can cancel within 2 hours of placing the order or before the order is packed. Go to <a href="<?= APP_URL ?>/account/orders" style="color: #4f46e5; text-decoration: none; font-weight: 500;">My Orders</a> and click "Cancel".</p>
      </details>
    </div>

    <div class="premium-faq-category">
      <h2><i class="bi bi-truck"></i> Delivery</h2>
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

    <div class="premium-faq-category">
      <h2><i class="bi bi-arrow-counterclockwise"></i> Returns & Refunds</h2>
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

    <div class="premium-faq-category">
      <h2><i class="bi bi-shield-check"></i> Account & Security</h2>
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

  <div class="premium-help-contact">
    <h2>Still need help?</h2>
    <p style="font-size: 1.15rem; color: #475569; margin-bottom: 3rem;">Our dedicated support team is available everyday from 9 AM to 8 PM.</p>
    
    <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
      <div style="background: #fff; padding: 2.5rem; border-radius: 20px; width: 280px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; transition: transform 0.2s;">
        <i class="bi bi-chat-dots-fill" style="font-size: 3rem; color: #4f46e5; margin-bottom: 1rem; display: block;"></i>
        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: #0f172a;">Live Chat</h3>
        <p style="color: #64748b; margin-bottom: 1.5rem;">Fastest response time.</p>
        <button class="btn btn-outline" style="border-radius: 50px; font-weight: 600; color: #4f46e5; border-color: #4f46e5; width: 100%;">Start Chat</button>
      </div>
      
      <div style="background: #fff; padding: 2.5rem; border-radius: 20px; width: 280px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; transition: transform 0.2s;">
        <i class="bi bi-telephone-fill" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem; display: block;"></i>
        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: #0f172a;">Call Us</h3>
        <p style="color: #64748b; margin-bottom: 1.5rem;">+977 980-1234567</p>
        <a href="tel:+9779801234567" class="btn btn-outline" style="border-radius: 50px; font-weight: 600; color: #10b981; border-color: #10b981; width: 100%; display: inline-block;">Call Now</a>
      </div>

      <div style="background: #fff; padding: 2.5rem; border-radius: 20px; width: 280px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; transition: transform 0.2s;">
        <i class="bi bi-envelope-fill" style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem; display: block;"></i>
        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: #0f172a;">Email Us</h3>
        <p style="color: #64748b; margin-bottom: 1.5rem;">hello@grocerydash.com</p>
        <a href="mailto:hello@grocerydash.com" class="btn btn-outline" style="border-radius: 50px; font-weight: 600; color: #f59e0b; border-color: #f59e0b; width: 100%; display: inline-block;">Send Email</a>
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