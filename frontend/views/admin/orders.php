<?php
/**
 * Admin Subsystem - Master Orders Log Tracking Matrix View
 * @var array $orders  Passed from AdminController containing the raw transactional entries
 */
?>

<div class="admin-view-header mb-4 pb-2 border-bottom">
    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem; letter-spacing: -0.025em;">Orders Management</h1>
    <p class="text-muted" style="font-size: 0.9rem;">Monitor, filter, and track transactional client lifecycle payloads.</p>
</div>

<div class="mb-4 d-flex align-items-center justify-content-between">
    <button class="btn-premium-secondary" style="padding: 8px 16px !important; font-size: 0.85rem !important;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> View Pending Orders Only
    </button>
</div>

<div class="table-container shadow-sm rounded bg-white overflow-hidden">
    <table class="admin-table w-100 m-0">
        <thead>
            <tr>
                <th style="width: 12%;">Invoice ID</th>
                <th style="width: 28%;">Customer Profile</th>
                <th style="width: 15%;">Total Value</th>
                <th style="width: 15%;">Order Status</th>
                <th style="width: 18%;">Submission Date</th>
                <th style="width: 12%; text-align: center;">Actions</th>
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
                            <strong class="text-dark"><?= htmlspecialchars((string)($order['user_name'] ?? 'Guest Customer'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </td>
                    <td>
                        <span class="price-data-cell font-weight-bold"><?= formatPrice($order['total'] ?? 0) ?></span>
                    </td>
                    <td>
                        <?php $statusFlag = strtolower($order['status'] ?? 'pending'); ?>
                        <span class="status-badge status-<?= htmlspecialchars($statusFlag, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-circle-fill small me-1" style="font-size: 0.45rem; vertical-align: middle;"></i>
                            <?= htmlspecialchars((string)($order['status'] ?? 'Pending'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-muted small">
                            <?= htmlspecialchars(date('M d, Y • h:i A', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td style="text-align: center; white-space: nowrap;">
                    <div class="d-inline-flex align-items-center gap-1">
                        
                        <a href="<?= APP_URL ?>/admin/orders/<?= (int)($order['id'] ?? 0) ?>" 
                        class="btn btn-sm btn-light border text-primary" 
                        title="View Details">
                            <i class="bi bi-eye-fill"></i>
                        </a>

                        <a href="<?= APP_URL ?>/admin/orders/<?= (int)($order['id'] ?? 0) ?>/edit" 
                        class="btn btn-sm btn-light border text-warning" 
                        title="Edit Order / Update Status">
                            <i class="bi bi-pencil-fill"></i>
                        </a>

                        <form action="<?= APP_URL ?>/admin/orders/cancel/<?= (int)($order['id'] ?? 0) ?>" 
                            method="POST" 
                            class="d-inline m-0" 
                            onsubmit="return confirm('Are you sure you want to completely purge/cancel this order matrix record? This action is irreversible.');">
                            
                            <input type="hidden" name="csrf_token" value="<?= csrfToken(); ?>">
                            
                            <button type="submit" 
                                    class="btn btn-sm btn-light border text-danger" 
                                    title="Delete / Cancel Order">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>

                    </div>
                </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="p-5 text-center text-muted bg-light">
                        <div class="py-4">
                            <span style="font-size: 2.5rem; display: block; margin-bottom: 10px;">📥</span>
                            <p class="m-0 font-weight-bold text-secondary">The structural transactional ledger is completely empty.</p>
                            <p class="text-muted small m-0">New customer checkouts will appear automatically within this processing grid.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>