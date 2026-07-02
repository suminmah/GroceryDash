<?php
// frontend/views/pages/order-detail.php
$pageTitle = $pageTitle ?? 'Order Detail'; 
$order = $order ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="container" style="padding: 3rem 1rem 5rem; max-width: 900px; font-family: 'Poppins', system-ui, -apple-system, sans-serif;">
    
    <!-- Hero Card -->
    <div class="premium-track-card mb-4 delay-1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="premium-track-hero-icon">
                    <svg style="width:32px; height:32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold tracking-wider" style="letter-spacing: 0.08em; font-size: 0.75rem;">Order Details</span>
                    <h2 class="fw-bold mb-0 text-dark" style="font-size: 1.6rem; letter-spacing: -0.02em;">#<?= htmlspecialchars($order['order_number'] ?? $order['id'] ?? '') ?></h2>
                </div>
            </div>
            <div class="text-sm-end">
                <p class="text-muted mb-1 small">Order Date: <strong class="text-dark"><?= date('M d, Y', strtotime($order['created_at'] ?? 'now')) ?></strong></p>
                <span class="status-badge" style="<?= strtolower($order['status'] ?? '') === 'delivered' ? 'background: #dcfce7; color: #166534;' : (strtolower($order['status'] ?? '') === 'cancelled' ? 'background: #fee2e2; color: #991b1b;' : '') ?>">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i> <?= htmlspecialchars(ucfirst($order['status'] ?? 'Pending')) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Items Card -->
    <div class="premium-track-card delay-2" style="padding: 2rem;">
        <h5 class="fw-bold text-dark mb-4" style="font-size: 1.25rem;">Items Purchased</h5>
        
        <table class="premium-items-table">
            <thead>
                <tr>
                    <th style="width: 60%">Item</th>
                    <th style="width: 15%; text-align: center;">Qty</th>
                    <th style="text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] ?? [] as $item): ?>
                    <tr>
                        <td class="item-name"><?= e($item['name'] ?? 'Unknown Item') ?></td>
                        <td style="text-align: center; color: var(--text-muted);"><?= $item['quantity'] ?? 1 ?></td>
                        <td style="text-align: right;"><?= formatPrice(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; padding-right: 1.5rem;">Delivery Fee</td>
                    <td style="text-align: right;"><?= formatPrice($order['delivery_fee'] ?? 0) ?></td>
                </tr>
                <?php if (($order['discount'] ?? 0) > 0): ?>
                <tr>
                    <td colspan="2" style="text-align: right; padding-right: 1.5rem; color: #16a34a;">Discount</td>
                    <td style="text-align: right; color: #16a34a;">-<?= formatPrice($order['discount']) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-right: 1.5rem;">Total Amount</td>
                    <td style="text-align: right;"><?= formatPrice($order['total'] ?? $order['total_amount'] ?? 0) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="premium-actions mt-5 d-flex justify-content-between flex-wrap">
            <a href="<?= APP_URL ?>/account/orders" class="btn btn-outline btn-pill">
                <i class="bi bi-arrow-left me-2"></i> Back to Orders
            </a>
            <?php if (!empty($order['order_number'])): ?>
            <a href="<?= APP_URL ?>/order/track/<?= e($order['order_number']) ?>" class="btn btn-primary btn-pill">
                Track Order <i class="bi bi-arrow-right ms-2"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>