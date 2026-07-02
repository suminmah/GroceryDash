<?php
// frontend/views/pages/order-track.php
$pageTitle = 'Track Order #' . htmlspecialchars($order['order_number'] ?? '') . ' — GroceryDash';
require __DIR__ . '/../layouts/header.php';

// Safe status normalization mapping logic
$status = strtolower($order['status'] ?? 'pending');

$order = $order ?? [];
?>

<div class="container" style="padding: 3rem 1rem 5rem; max-width: 900px; font-family: 'Poppins', system-ui, -apple-system, sans-serif;">
    
    <!-- Hero Card -->
    <div class="premium-track-card mb-4 delay-1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="premium-track-hero-icon">
                    <svg style="width:32px; height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path></svg>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold tracking-wider" style="letter-spacing: 0.08em; font-size: 0.75rem;">Tracking Code Reference</span>
                    <h2 class="fw-bold mb-0 text-dark" style="font-size: 1.6rem; letter-spacing: -0.02em;">#<?= htmlspecialchars($order['order_number']) ?></h2>
                </div>
            </div>
            <div class="text-sm-end">
                <p class="text-muted mb-1 small">Order Date: <strong class="text-dark"><?= date('M d, Y', strtotime($order['created_at'] ?? 'now')) ?></strong></p>
                <span class="status-badge" style="<?= $status === 'delivered' ? 'background: #dcfce7; color: #166534;' : ($status === 'cancelled' ? 'background: #fee2e2; color: #991b1b;' : '') ?>">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i> <?= htmlspecialchars(ucfirst($status)) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Timeline Column -->
        <div class="col-lg-7">
            <div class="premium-track-card h-100 delay-2" style="padding: 2rem;">
                <h5 class="fw-bold text-dark" style="font-size: 1.25rem;">Delivery Shipment Timeline</h5>
                
                <div class="premium-timeline">
                    <div class="premium-timeline-progress" style="height: <?= $status === 'delivered' ? '100%' : ($status === 'shipped' ? '66%' : ($status === 'processing' ? '33%' : '0')) ?>;"></div>

                    <!-- Step 1: Confirmed -->
                    <div class="timeline-step completed">
                        <div class="timeline-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div class="timeline-content">
                            <h6>Order Confirmed</h6>
                            <p>We have successfully accepted and verified your order.</p>
                        </div>
                    </div>

                    <!-- Step 2: Processing -->
                    <?php $isProcessingActive = ($status === 'processing'); ?>
                    <?php $isProcessingCompleted = in_array($status, ['shipped', 'delivered']); ?>
                    <div class="timeline-step <?= $isProcessingCompleted ? 'completed' : ($isProcessingActive ? 'active' : '') ?>">
                        <div class="timeline-icon">
                            <?php if ($isProcessingActive): ?>
                                <svg class="spin" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <?php else: ?>
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="timeline-content">
                            <h6>Processing & Packing</h6>
                            <p>Your goods are being hand-picked and carefully packed.</p>
                        </div>
                    </div>

                    <!-- Step 3: Shipped -->
                    <?php $isShippedActive = ($status === 'shipped'); ?>
                    <?php $isShippedCompleted = ($status === 'delivered'); ?>
                    <div class="timeline-step <?= $isShippedCompleted ? 'completed' : ($isShippedActive ? 'active' : '') ?>">
                        <div class="timeline-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div class="timeline-content">
                            <h6>Out For Delivery</h6>
                            <p>Our dedicated rider is en-route to your door.</p>
                        </div>
                    </div>

                    <!-- Step 4: Delivered -->
                    <div class="timeline-step <?= ($status === 'delivered') ? 'completed' : '' ?>">
                        <div class="timeline-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        </div>
                        <div class="timeline-content">
                            <h6>Delivered Successfully</h6>
                            <p>Package dropped off safely at your location.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Column -->
        <div class="col-lg-5">
            <div class="premium-track-card h-100 delay-2" style="padding: 2rem; display: flex; flex-direction: column;">
                <h5 class="fw-bold text-dark mb-4" style="font-size: 1.25rem;">Delivery & Payment Info</h5>
                
                <div class="premium-info-block">
                    <div class="premium-info-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div class="premium-info-text">
                        <strong>Shipping Destination</strong>
                        <span><?= htmlspecialchars($order['delivery_address'] ?? 'Provided on checkout') ?></span>
                    </div>
                </div>

                <div class="premium-info-block">
                    <div class="premium-info-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div class="premium-info-text">
                        <strong>Payment Gateway</strong>
                        <span class="d-inline-flex align-items-center gap-2">
                            <?= match(strtolower($order['payment_method'] ?? '')) {
                                'cod' => 'Cash on Delivery (COD)',
                                'fonepay' => '<img src="' . APP_URL . '/assets/images/fonepay-logo.png" alt="Fonepay" style="height:20px;"> Fonepay',
                                'esewa' => '<img src="' . APP_URL . '/assets/images/esewa-logo.webp" alt="eSewa" style="height:20px;"> eSewa',
                                'khalti' => '<img src="' . APP_URL . '/assets/images/khalti-logo.png" alt="Khalti" style="height:20px;"> Khalti',
                                'online' => 'Online Payment',
                                default => strtoupper($order['payment_method'] ?? 'COD')
                            } ?>
                        </span>
                    </div>
                </div>

                <div class="mt-auto premium-total-block">
                    <span>Total Cost (Inc. Tax & Fees)</span>
                    <strong>Rs. <?= number_format((float)($order['total'] ?? $order['total_amount'] ?? 0), 2) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="premium-actions mt-5">
        <a href="<?= APP_URL ?>/shop" class="btn btn-primary btn-pill" style="min-width: 300px;">
            <i class="bi bi-arrow-left me-2"></i> Continue Grocery Shopping
        </a>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spin {
    animation: spin 2s linear infinite;
    display: inline-block;
}
.breadcrumb-item + .breadcrumb-item::before {
    content: "›" !important;
    font-size: 1.2rem;
    vertical-align: top;
    line-height: 1;
}
/* Override Bootstrap hover colors to fit your custom branding exactly */
.btn-outline-success:hover {
    background-color: #2c7a4d !important;
    border-color: #2c7a4d !important;
    color: #fff !important;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>