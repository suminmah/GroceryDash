<?php
// frontend/views/pages/order-track.php
$pageTitle = 'Track Order #' . htmlspecialchars($order['order_number'] ?? '') . ' — GroceryDash';
require __DIR__ . '/../layouts/header.php';

// Safe status normalization mapping logic
$status = strtolower($order['status'] ?? 'pending');

$order = $order ?? [];
?>

<div class="container" style="padding: 3rem 1rem 5rem; max-width: 900px; font-family: 'Poppins', system-ui, -apple-system, sans-serif;">
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px; background: #fff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; color: #2c7a4d; background-color: #f0fdf4 !important;">
                    <i class="bi bi-box2-heart fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold tracking-wider" style="letter-spacing: 0.08em; font-size: 0.75rem;">Tracking Code Reference</span>
                    <h2 class="fw-bold mb-0 text-dark" style="font-size: 1.6rem; letter-spacing: -0.02em;">#<?= htmlspecialchars($order['order_number']) ?></h2>
                </div>
            </div>
            <div class="text-sm-end">
                <p class="text-muted mb-1 small">Order Date: <strong class="text-dark"><?= date('M d, Y', strtotime($order['created_at'] ?? 'now')) ?></strong></p>
                <span class="badge rounded-pill px-3 py-2 fw-semibold text-uppercase border
                    <?= $status === 'delivered' ? 'bg-success text-white border-success' : ($status === 'cancelled' ? 'bg-danger text-white border-danger' : 'text-warning border-warning') ?>" 
                    style="font-size: 0.75rem; letter-spacing: 0.05em; background-color: <?= $status === 'delivered' ? '#2c7a4d' : ($status === 'cancelled' ? '#dc2626' : '#fffdf2') ?> !important;">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i> <?= htmlspecialchars($status) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: #fff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;">
                <h5 class="fw-bold mb-4 text-dark" style="font-size: 1.1rem;">Delivery Shipment Timeline</h5>
                
                <div class="position-relative ps-2">
                    <div class="position-absolute" style="left: 21px; top: 12px; bottom: 12px; width: 3px; background-color: #f3f4f6; z-index: 1;"></div>
                    
                    <div class="position-absolute" style="left: 21px; top: 12px; width: 3px; background-color: #2c7a4d; z-index: 1; transition: height 0.5s ease;
                        height: <?= $status === 'delivered' ? 'calc(100% - 24px)' : ($status === 'shipped' ? '66%' : ($status === 'processing' ? '33%' : '0px')) ?>;">
                    </div>

                    <div class="d-flex align-items-start mb-4 position-relative" style="z-index: 2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                             style="width: 30px; height: 30px; background: #2c7a4d; color: #fff; border: 3px solid #fff; margin-left: 2px;">
                            <i class="bi bi-check" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="mb-0 fw-bold text-dark">Order Confirmed</h6>
                            <small class="text-muted">We have successfully accepted and verified your order pool criteria request.</small>
                        </div>
                    </div>

                    <?php $isProcessing = in_array($status, ['processing', 'shipped', 'delivered']); ?>
                    <div class="d-flex align-items-start mb-4 position-relative" style="z-index: 2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                             style="width: 30px; height: 30px; background: <?= $isProcessing ? '#2c7a4d' : '#fff' ?>; color: <?= $isProcessing ? '#fff' : '#9ca3af' ?>; border: 3px solid <?= $isProcessing ? '#fff' : '#e5e7eb' ?>; margin-left: 2px;">
                            <i class="bi <?= $status === 'processing' ? 'bi-arrow-repeat spin' : 'bi-box-seam' ?>" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="mb-0 <?= $isProcessing ? 'fw-bold text-dark' : 'text-muted fw-normal' ?>">Processing & Packing</h6>
                            <small class="text-muted">Your goods are being hand-picked and carefully packed from local stockrooms.</small>
                        </div>
                    </div>

                    <?php $isShipped = in_array($status, ['shipped', 'delivered']); ?>
                    <div class="d-flex align-items-start mb-4 position-relative" style="z-index: 2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                             style="width: 30px; height: 30px; background: <?= $isShipped ? '#2c7a4d' : '#fff' ?>; color: <?= $isShipped ? '#fff' : '#9ca3af' ?>; border: 3px solid <?= $isShipped ? '#fff' : '#e5e7eb' ?>; margin-left: 2px;">
                            <i class="bi bi-truck" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="mb-0 <?= $isShipped ? 'fw-bold text-dark' : 'text-muted fw-normal' ?>">Out For Delivery</h6>
                            <small class="text-muted">Our dedicated rider log agent has left our hub and is en-route to your door.</small>
                        </div>
                    </div>

                    <?php $isDelivered = ($status === 'delivered'); ?>
                    <div class="d-flex align-items-start position-relative" style="z-index: 2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                             style="width: 30px; height: 30px; background: <?= $isDelivered ? '#2c7a4d' : '#fff' ?>; color: <?= $isDelivered ? '#fff' : '#9ca3af' ?>; border: 3px solid <?= $isDelivered ? '#fff' : '#e5e7eb' ?>; margin-left: 2px;">
                            <i class="bi bi-house-check" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="ps-3">
                            <h6 class="mb-0 <?= $isDelivered ? 'fw-bold text-dark' : 'text-muted fw-normal' ?>">Delivered Successfully</h6>
                            <small class="text-muted">Package dropped off safely at your designated destination location drop center points.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px; background: #fff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;">
                <h5 class="fw-bold mb-4 text-dark" style="font-size: 1.1rem;">Delivery & Payment Info</h5>
                
                <div class="mb-4">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-geo-alt text-muted mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <span class="d-block small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Shipping Destination</span>
                            <span class="text-dark fw-medium" style="font-size: 0.95rem;"><?= htmlspecialchars($order['delivery_address'] ?? 'Provided on invoice receipt mappings') ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-2 pt-2">
                        <i class="bi bi-credit-card text-muted mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <span class="d-block small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Payment Gateway</span>
                            <span class="text-dark fw-bold text-uppercase" style="font-size: 0.9rem;"><?= htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery (COD)') ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-auto p-3 rounded-3" style="background-color: #f9fafb; border: 1px solid #f3f4f6;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-medium">Total Cost (Inc. Tax & Fees):</span>
                        <span class="fw-bold" style="font-size: 1.35rem; color: #2c7a4d; letter-spacing: -0.02em;">
                            Rs. <?= number_format((float)($order['total_amount'] ?? 0), 2) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="<?= APP_URL ?>/shop" class="btn btn-outline-success btn-lg px-5 py-2 rounded-3" style="border-color: #2c7a4d; color: #2c7a4d; font-size: 1rem; font-weight: 500;">
            <i class="bi bi-arrow-left me-2" style="margin-top: 0.125rem;"></i> Continue Grocery Shopping
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