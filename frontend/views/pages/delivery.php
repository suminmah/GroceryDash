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

<section class="delivery-section">
  <div class="container">
    <h1>Delivery Information</h1>
    
    <div class="delivery-grid">
      <!-- Left column: Policies -->
      <div class="delivery-policies">
        <div class="policy-card">
          <span class="policy-icon">🚚</span>
          <h3>Free Delivery</h3>
          <p>On orders above Rs. 500. Free delivery for all orders above the threshold. Otherwise a nominal fee of Rs. 40 applies.</p>
        </div>
        <div class="policy-card">
          <span class="policy-icon">⏱️</span>
          <h3>Same Day Delivery</h3>
          <p>Order before 2 PM to get your groceries delivered the same day. After 2 PM, delivery next day.</p>
        </div>
        <div class="policy-card">
          <span class="policy-icon">🔄</span>
          <h3>Easy Returns</h3>
          <p>Not satisfied? Return within 24 hours for a full refund or replacement.</p>
        </div>
        <div class="policy-card">
          <span class="policy-icon">✅</span>
          <h3>Quality Guarantee</h3>
          <p>All products are farm-fresh and quality checked before packing.</p>
        </div>
      </div>

      <!-- Right column: Upcoming slots (static or dynamic) -->
      <div class="delivery-slots">
        <h2>Available Delivery Slots</h2>
        <div class="slot-list">
          <?php foreach ($slots as $slot): ?>
            <div class="slot-item <?= $slot['available'] ? 'available' : 'unavailable' ?>">
              <div class="slot-date">
                <?= date('D, M j', strtotime($slot['slot_date'])) ?>
              </div>
              <div class="slot-time">
                <?= date('g:i A', strtotime($slot['start_time'])) ?> – 
                <?= date('g:i A', strtotime($slot['end_time'])) ?>
              </div>
              <?php if ($slot['available']): ?>
                <span class="slot-badge">Available</span>
              <?php else: ?>
                <span class="slot-badge full">Full</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="slot-note">* Slots are updated daily. Book your slot at checkout.</p>
      </div>
    </div>

    <div class="delivery-zones">
      <h2>Delivery Zones</h2>
      <p>We currently deliver across the entire Kathmandu Valley. New areas are added every month.</p>
      <ul>
        <li>Kathmandu Metropolitan City</li>
        <li>Lalitpur (Patan)</li>
        <li>Bhaktapur</li>
        <li>Kirtipur</li>
        <li>Madhyapur Thimi</li>
      </ul>
    </div>

    <div class="delivery-faq">
      <h2>Frequently Asked Questions</h2>
      <details>
        <summary>What is the delivery fee?</summary>
        <p>Rs. 40 for orders under Rs. 500. Free delivery for orders Rs. 500 and above.</p>
      </details>
      <details>
        <summary>Can I change my delivery slot after placing an order?</summary>
        <p>Yes, you can change it from your order details page up to 2 hours before the original slot.</p>
      </details>
      <details>
        <summary>Do you deliver on Sundays?</summary>
        <p>Yes, we deliver 7 days a week, including public holidays, from 8 AM to 8 PM.</p>
      </details>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>