<?php
/**
 * Admin Subsystem - Master Orders Log Tracking Matrix View
 * @var array $orders  Passed from AdminController containing the raw transactional entries
 */
// 🔍 Check if the pending filter is currently applied via URL query parameters
$currentFilter = $_GET['status'] ?? 'all';
$isPendingFilterActive = ($currentFilter === 'pending');
?>

<div class="admin-view-header mb-4 pb-2 border-bottom">
    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem; letter-spacing: -0.025em;">Orders Management</h1>
    <p class="text-muted" style="font-size: 0.9rem;">Monitor, filter, and track transactional client lifecycle payloads.</p>
</div>

<div class="mb-4 d-flex align-items-center gap-2">
    <?php if ($isPendingFilterActive): ?>
        <a href="<?= APP_URL ?>/admin/orders" class="btn btn-warning d-inline-flex align-items-center shadow-sm" style="padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 6px;">
            <i class="bi bi-x-circle-fill me-2"></i> Showing Pending Only (Click to Clear Filter)
        </a>
    <?php else: ?>
        <a href="<?= APP_URL ?>/admin/orders?status=pending" class="btn btn-outline-success d-inline-flex align-items-center shadow-sm" style="padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 6px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> View Pending Orders Only
        </a>
    <?php endif; ?>
</div>

<div class="table-container shadow-sm rounded bg-white overflow-hidden">
    <table class="admin-table w-100 m-0">
        <thead>
            <tr>
                <th style="width: 10%;">Invoice ID</th>
                <th style="width: 25%;">Customer Profile</th>
                <th style="width: 13%;">Total Value</th>
                <th style="width: 15%;">Order Status</th> <th style="width: 13%;">Payment Status</th>  <th style="width: 14%;">Submission Date</th>
                <th style="width: 10%; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <span class="text-secondary font-weight-bold">#<?= htmlspecialchars((string)($order['id'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    
                    <td>
                        <div class="d-flex flex-column">
                            <strong class="text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars((string)($order['customer_name'] ?? $order['user_name'] ?? 'Guest Customer'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </td>
                    
                    <td>
                        <span class="price-data-cell font-weight-bold text-dark"><?= formatPrice($order['total'] ?? 0) ?></span>
                    </td>
                    
                    <td>
                        <?php 
                        $orderFlag = strtolower(trim($order['status'] ?? 'pending')); 
                        
                        // Explicit mapping for your specific grocery operational lifecycle:
                        $orderBadgeMap = [
                            'pending'   => 'bg-warning-subtle text-warning border border-warning-subtle',
                            'packed'    => 'bg-info-subtle text-info border border-info-subtle',
                            'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                            'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle'
                        ];
                        
                        $oBadgeClass = $orderBadgeMap[$orderFlag] ?? 'bg-light text-secondary border';
                        
                        // Select context-appropriate icons dynamically to support your flow
                        $iconMap = [
                            'pending'   => 'bi-hourglass-split',
                            'packed'    => 'bi-box-seam-fill',
                            'delivered' => 'bi-check-circle-fill',
                            'cancelled' => 'bi-x-circle-fill'
                        ];
                        $oIcon = $iconMap[$orderFlag] ?? 'bi-circle';
                        ?>
                        <span class="badge <?= $oBadgeClass ?> d-inline-flex align-items-center gap-1" style="padding: 6px 10px; font-weight: 600; font-size: 0.8rem; border-radius: 6px;">
                            <i class="bi <?= $oIcon ?>" style="font-size: 0.85rem;"></i>
                            <?= htmlspecialchars(ucfirst($order['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    
                    <td>
                        <?php 
                        $rawPaymentValue = $order['payment_status'] ?? $order['payment'] ?? 'pending';
                        $paymentFlag = strtolower(trim((string)$rawPaymentValue)); 
                        $paymentBadgeMap = [
                            'pending'  => 'bg-warning text-dark border-0',
                            'paid'     => 'bg-success text-white border-0',
                            'refunded' => 'bg-secondary text-white border-0',
                            'failed'   => 'bg-danger text-white border-0'
                        ];
                        $pBadgeClass = $paymentBadgeMap[$paymentFlag] ?? 'bg-light text-secondary border';
                        ?>
                        <span class="badge <?= $pBadgeClass ?> d-inline-flex align-items-center gap-1" style="padding: 6px 10px; font-weight: 600; font-size: 0.8rem; border-radius: 6px;">
                            <i class="bi bi-credit-card-2-front" style="font-size: 0.85rem;"></i>
                            <?= htmlspecialchars(ucfirst($order['payment_status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    
                    <td>
                        <span class="text-muted small">
                            <?= htmlspecialchars(date('M d, Y • h:i A', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    
                    <td style="text-align: center; white-space: nowrap;">
                        <div class="d-inline-flex align-items-center gap-1">
                            <a href="<?= APP_URL ?>/admin/orders/<?= (int)($order['id'] ?? 0) ?>" class="btn btn-sm btn-light border text-primary" title="View Details">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="<?= APP_URL ?>/admin/orders/<?= (int)($order['id'] ?? 0) ?>/edit" class="btn btn-sm btn-light border text-warning" title="Edit Status Controls">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="<?= APP_URL ?>/admin/orders/cancel/<?= (int)($order['id'] ?? 0) ?>" method="POST" class="d-inline m-0" onsubmit="return confirm('Cancel this order record? This action is permanent.');">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                                <button type="submit" class="btn btn-sm btn-light border text-danger" title="Cancel Order">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="p-5 text-center text-muted bg-light">
                        <div class="py-4">
                            <span style="font-size: 2.5rem; display: block; margin-bottom: 10px;">📥</span>
                            <p class="m-0 font-weight-bold text-secondary">The transactional ledger is completely empty.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>