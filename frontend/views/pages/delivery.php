<?php
$pageTitle = 'Delivery Information — GroceryDash';
require __DIR__ . '/../layouts/header.php';

// Optional: Fetch upcoming delivery slots from database
$slots = [];
if (class_exists('DeliverySlotModel')) {
    $slotModel = new DeliverySlotModel();
    if (method_exists($slotModel, 'getUpcoming')) {
        $slots = $slotModel->getUpcoming(5);
    } else {
        // Fallback to static slots if method doesn't exist
        $slots = [
            ['slot_date' => date('Y-m-d'), 'start_time' => '09:00', 'end_time' => '11:00', 'available' => true],
            ['slot_date' => date('Y-m-d'), 'start_time' => '11:00', 'end_time' => '13:00', 'available' => true],
            ['slot_date' => date('Y-m-d', strtotime('+1 day')), 'start_time' => '09:00', 'end_time' => '11:00', 'available' => true],
            ['slot_date' => date('Y-m-d', strtotime('+1 day')), 'start_time' => '11:00', 'end_time' => '13:00', 'available' => false],
        ];
    }
} else {
    // Fallback static slots if model doesn't exist
    $slots = [
        ['slot_date' => date('Y-m-d'), 'start_time' => '09:00', 'end_time' => '11:00', 'available' => true],
        ['slot_date' => date('Y-m-d'), 'start_time' => '11:00', 'end_time' => '13:00', 'available' => true],
        ['slot_date' => date('Y-m-d', strtotime('+1 day')), 'start_time' => '09:00', 'end_time' => '11:00', 'available' => true],
        ['slot_date' => date('Y-m-d', strtotime('+1 day')), 'start_time' => '11:00', 'end_time' => '13:00', 'available' => false],
    ];
}
?>

<section class="premium-delivery-section">
  <div class="premium-delivery-hero">
    <h1>Delivery Information</h1>
    <p>Everything you need to know about how we get fresh groceries to your doorstep.</p>
  </div>
  
  <div class="premium-delivery-grid">
    <!-- Left column: Policies -->
    <div class="premium-policy-grid">
      <div class="premium-policy-card">
        <i class="bi bi-truck"></i>
        <h3>Free Delivery</h3>
        <p style="color: #64748b; margin-top: 0.5rem; line-height: 1.5;">On orders above Rs. 500. Free delivery for all orders above the threshold. Otherwise a nominal fee of Rs. 40 applies.</p>
      </div>
      <div class="premium-policy-card">
        <i class="bi bi-stopwatch"></i>
        <h3>Same Day Delivery</h3>
        <p style="color: #64748b; margin-top: 0.5rem; line-height: 1.5;">Order before 2 PM to get your groceries delivered the same day. After 2 PM, delivery next day.</p>
      </div>
      <div class="premium-policy-card">
        <i class="bi bi-arrow-counterclockwise"></i>
        <h3>Easy Returns</h3>
        <p style="color: #64748b; margin-top: 0.5rem; line-height: 1.5;">Not satisfied? Return within 24 hours for a full refund or replacement. No questions asked.</p>
      </div>
      <div class="premium-policy-card">
        <i class="bi bi-patch-check"></i>
        <h3>Quality Guarantee</h3>
        <p style="color: #64748b; margin-top: 0.5rem; line-height: 1.5;">All products are farm-fresh and rigorously quality checked before packing by our experts.</p>
      </div>
    </div>

    <!-- Right column: Upcoming slots (static or dynamic) -->
    <div class="premium-slot-container">
      <h2 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: #1e293b;">Available Delivery Slots</h2>
      <p style="color: #64748b; margin-bottom: 2rem;">Slots are updated daily. Secure yours at checkout.</p>
      
      <div class="premium-slot-list">
        <?php foreach ($slots as $slot): ?>
          <div class="premium-slot-item <?= $slot['available'] ? 'available' : 'unavailable' ?>">
            <div>
              <div style="font-weight: 600; font-size: 1.1rem; color: #334155;">
                <?= date('D, M j', strtotime($slot['slot_date'])) ?>
              </div>
              <div style="color: #64748b; margin-top: 0.25rem;">
                <i class="bi bi-clock me-1"></i>
                <?= date('g:i A', strtotime($slot['start_time'])) ?> – 
                <?= date('g:i A', strtotime($slot['end_time'])) ?>
              </div>
            </div>
            <div>
              <?php if ($slot['available']): ?>
                <span class="premium-slot-badge">Available</span>
              <?php else: ?>
                <span class="premium-slot-badge">Full</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="premium-help-contact" style="margin-top: 5rem;">
    <h2>Delivery Zones</h2>
    <p style="max-width: 600px; margin: 0 auto 2rem; color: #64748b; line-height: 1.6;">We currently deliver across the entire Kathmandu Valley. Our logistics network is constantly expanding to bring fresh groceries to more neighborhoods.</p>
    
    <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
      <span style="background: #fff; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 500; color: var(--green-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">Kathmandu</span>
      <span style="background: #fff; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 500; color: var(--green-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">Lalitpur (Patan)</span>
      <span style="background: #fff; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 500; color: var(--green-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">Bhaktapur</span>
      <span style="background: #fff; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 500; color: var(--green-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">Kirtipur</span>
      <span style="background: #fff; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 500; color: var(--green-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">Thimi</span>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>