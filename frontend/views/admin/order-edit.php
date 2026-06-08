<?php
/**
 * Admin Subsystem - Order Fulfillment Modification Form Panel View
 * @var array $order  Unified dataset row array containing order profiles
 */
$pageTitle = 'Process Order Manifest — Admin';
?>

<div class="admin-view-header mb-4 pb-2 border-bottom">
    <a href="<?= APP_URL ?>/admin/orders" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left"></i> Return to Orders List
    </a>
    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem; letter-spacing: -0.025em;">Process Order #<?= (int)$order['id'] ?></h1>
    <p class="text-muted" style="font-size: 0.9rem;">Customer Profile: <strong><?= htmlspecialchars($order['customer_name'] ?? 'Guest account mapping') ?></strong></p>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-12">
        <div class="card shadow-sm border-0 rounded bg-white p-4 mb-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2 text-secondary">Workflow Lifecycle Controls</h5>
            
            <form action="<?= APP_URL ?>/admin/orders/<?= (int)$order['id'] ?>/update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold small text-muted">Fulfillment Status Mapping</label>
                        <select name="status" id="status" class="form-select">
                            <?php 
                            $statuses = ['Pending', 'Packed', 'Delivered', 'Cancelled']; // 🌟 Matches perfectly!
                            foreach ($statuses as $st): 
                                $selected = (strtolower($order['status'] ?? '') === strtolower($st)) ? 'selected' : '';
                            ?>
                                <option value="<?= $st ?>" <?= $selected ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="pending" <?= (strtolower($order['payment_status'] ?? '') === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= (strtolower($order['payment_status'] ?? '') === 'paid') ? 'selected' : '' ?>>Paid</option>
                            <option value="refunded" <?= (strtolower($order['payment_status'] ?? '') === 'refunded') ? 'selected' : '' ?>>Refunded</option>
                            <option value="failed" <?=  (strtolower($order['payment_status'] ?? '') === 'failed') ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 p-3 bg-light rounded border border-light-subtle">
                        <div class="row text-center">
                            <div class="col-4">
                                <span class="d-block small text-muted">Subtotal</span>
                                <strong class="text-dark"><?= formatPrice($order['subtotal'] ?? 0) ?></strong>
                            </div>
                            <div class="col-4">
                                <span class="d-block small text-muted">Delivery Fee</span>
                                <strong class="text-dark"><?= formatPrice($order['delivery_fee'] ?? 0) ?></strong>
                            </div>
                            <div class="col-4">
                                <span class="d-block small text-muted">Total Payable</span>
                                <strong class="text-success h6 fw-bold"><?= formatPrice($order['total'] ?? 0) ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success px-4" style="border-radius: 6px; font-weight: 600;">
                            <i class="bi bi-save me-2"></i> Commit Lifecycle Modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>