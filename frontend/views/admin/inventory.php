<?php
/**
 * Admin Subsystem - Master Stock Inventory Control Panel View
 * @var array $items       Unified collection arrays containing storage balances and safety parameters
 * @var int   $lowCount    Pre-calculated dashboard alerting threshold parameter logic mapping
 * @var string $token      The active cross-site request forgery protection hash payload
 */
$pageTitle = $pageTitle ?? 'Inventory Matrix — Admin';
$items     = $items ?? [];
$lowCount  = (int)($lowCount ?? 0);
$token     = $token ?? (function_exists('csrfToken') ? csrfToken() : '');
?>

<div class="admin-view-header mb-4 pb-2 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="font-weight-bold text-dark m-0" style="font-size: 1.75rem; letter-spacing: -0.025em;">Inventory</h1>
        <p class="text-muted m-0" style="font-size: 0.9rem;">Monitor, increment, and audit warehouse stocking levels against safety thresholds.</p>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-<?= ($lowCount > 0) ? 'danger animate-pulse' : 'success-subtle text-success border border-success-subtle' ?>" 
              style="padding: 10px 16px; font-weight: 700; font-size: 0.85rem; border-radius: 8px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $lowCount ?> Critical Risk Items
        </span>
    </div>
</div>

<div class="table-container shadow-sm rounded bg-white overflow-hidden">
    <?php if (empty($items)): ?>
        <div class="p-5 text-center text-muted">
            <span style="font-size: 2.5rem; display: block; margin-bottom: 12px;">📦</span>
            <p class="m-0 font-weight-bold text-secondary">The inventory distribution manifest index is unallocated.</p>
            <p class="text-muted small m-0">Verify your database connections or register novel catalog inventory stock instances.</p>
        </div>
    <?php else: ?>
        <table class="admin-table w-100 m-0">
            <thead>
                <tr>
                    <th style="width: 12%;">Product ID</th>
                    <th style="width: 43%;">Item Name</th>
                    <th style="width: 20%;">Warehouse Stock</th>
                    <th style="width: 25%; text-align: right;">Quick Restock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): 
                    // 🔍 Structural Fallback Evaluation mapping variable aliases safely
                    $pId       = (int)($item['id'] ?? $item['product_id'] ?? 0);
                    $stock     = (int)($item['stock_qty'] ?? $item['quantity'] ?? 0);
                    $threshold = (int)($item['buffer_threshold'] ?? 10);
                    $isLow     = ($stock <= $threshold);
                ?>
                <tr>
                    <td>
                        <span class="text-secondary font-weight-bold">#<?= $pId ?></span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong class="text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars((string)($item['product_name'] ?? $item['name'] ?? 'Undefined Variant'), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="text-muted small" style="font-size: 0.75rem;">Safety Reserve Threshold Level: <?= $threshold ?> Units</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $isLow ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-light text-dark border' ?>" 
                              style="padding: 6px 12px; font-weight: 600; font-size: 0.85rem;">
                            <i class="bi <?= $isLow ? 'bi-graph-down-arrow' : 'bi-box-seam-fill' ?> me-2"></i>
                            <?= number_format($stock) ?> Available
                        </span>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="<?= APP_URL ?>/admin/inventory/<?= $pId ?>/restock" class="d-inline-block m-0">
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            
                            <div class="input-group input-group-sm" style="max-width: 180px; float: right;">
                                <input type="number" 
                                       name="quantity" 
                                       value="<?= $stock ?>" 
                                       class="form-control text-center fw-bold border-secondary-subtle" 
                                       min="0" 
                                       style="font-size: 0.85rem;"
                                       aria-label="Direct stock target parameter modifications input">
                                <button type="submit" 
                                        class="btn btn-success fw-bold px-3" 
                                        title="Commit balance increments to operational tracking matrix">
                                    <i class="bi bi-check-lg"></i> Update
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>