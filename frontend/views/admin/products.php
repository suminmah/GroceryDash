<?php
/**
 * Admin Subsystem - Master Products Inventory Catalog Matrix View
 * @var array $products      Collection arrays containing catalog data entities
 * @var int   $totalPages    Computed ceiling limitation parameter for data chunk batches
 * @var int   $page          The current operational iteration checkpoint index counter
 */
$pageTitle  = $pageTitle ?? 'Products Management — Admin';
$products   = $products ?? [];
$totalPages = (int)($totalPages ?? 1);
$page       = (int)($page ?? 1);
?>

<div class="admin-view-header mb-4 pb-2 border-bottom d-flex align-items-center justify-content-between">
    <div>
        <h1 class="font-weight-bold text-dark m-0" style="font-size: 1.75rem; letter-spacing: -0.025em;">Products Catalog</h1>
        <p class="text-muted m-0" style="font-size: 0.9rem;">Manage inventory stock, pricing metrics, and retail classification matrices.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/products/new" class="btn btn-success d-inline-flex align-items-center" style="border-radius: 8px; padding: 10px 20px; font-weight: 500;">
        <i class="bi bi-plus-lg me-2"></i> Add New Product
    </a>
</div>

<div class="table-container shadow-sm rounded bg-white overflow-hidden mb-4">
    <table class="admin-table w-100 m-0">
        <thead>
            <tr>
                <th style="width: 10%;">ID Mapping</th>
                <th style="width: 45%;">Item Description</th>
                <th style="width: 15%;">Unit Cost</th>
                <th style="width: 15%;">Stock Level</th>
                <th style="width: 15%; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): 
                    // 🔍 Structural Check: Safe fallback toggle matching common ORM keys ('id' vs 'product_id')
                    $pId = (int)($product['id'] ?? $product['product_id'] ?? 0); 
                ?>
                <tr>
                    <td>
                        <span class="text-secondary font-weight-bold">#<?= $pId ?></span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong class="text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars((string)($product['name'] ?? 'Malformed Product Title'), ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($product['sku'])): ?>
                                <span class="text-muted small" style="font-size: 0.75rem; letter-spacing: 0.02em;">SKU: <?= htmlspecialchars((string)$product['sku'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="price-data-cell font-weight-bold text-dark"><?= formatPrice($product['price'] ?? 0) ?></span>
                    </td>
                    <td>
                        <?php 
                            $stock = (int)($product['stock_qty'] ?? 0);
                            $threshold = (int)($product['buffer_threshold'] ?? 5);
                            
                            if ($stock <= 0) {
                                $badgeClass = 'bg-danger text-white';
                                $badgeText = 'Out of Stock';
                            } elseif ($stock <= $threshold) {
                                $badgeClass = 'bg-warning text-dark';
                                $badgeText = $stock . ' Low Stock';
                            } else {
                                $badgeClass = 'bg-light text-success border border-success-subtle';
                                $badgeText = number_format($stock) . ' Units';
                            }
                        ?>
                        <span class="badge <?= $badgeClass ?>" style="padding: 6px 10px; font-weight: 600; font-size: 0.8rem;">
                            <?= $badgeText ?>
                        </span>
                    </td>
                    <td style="text-align: center; white-space: nowrap;">
                        <div class="d-inline-flex align-items-center gap-1">
                            
                            <a href="<?= APP_URL ?>/admin/products/<?= $pId ?>/edit" 
                               class="btn btn-sm btn-light border text-warning" 
                               title="Edit Product Details">
                                <i class="bi bi-pencil-fill"></i>
                            </a>

                            <form action="<?= APP_URL ?>/admin/products/delete" 
                                  method="POST" 
                                  class="d-inline m-0" 
                                  onsubmit="return confirm('Are you sure you want to completely purge this catalog item record? This operation cannot be rolled back.');">
                                
                                <input type="hidden" name="csrf_token" value="<?= function_exists('csrfToken') ? csrfToken() : ''; ?>">
                                <input type="hidden" name="id" value="<?= $pId ?>">
                                
                                <button type="submit" 
                                        class="btn btn-sm btn-light border text-danger" 
                                        title="Delete Product Profile">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="p-5 text-center text-muted bg-light">
                        <div class="py-4">
                            <span style="font-size: 2.5rem; display: block; margin-bottom: 12px;">🍏</span>
                            <p class="m-0 font-weight-bold text-secondary">The catalog inventory database log trace is empty.</p>
                            <p class="text-muted small m-0">Click 'Add New Product' above to populate this catalog layout.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Product navigation matrix mapping offsets" class="d-flex justify-content-end mt-4">
    <ul class="pagination pagination-sm m-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
        
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-item page-link bg-white border-end-0 text-success" href="?page=<?= max(1, $page - 1) ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link <?= ($page == $i) ? 'bg-success border-success text-white' : 'bg-white text-secondary border-end-0' ?>" 
                   href="?page=<?= $i ?>" style="font-weight: 500; min-width: 36px; text-align: center;">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-item page-link bg-white text-success" href="?page=<?= min($totalPages, $page + 1) ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>

    </ul>
</nav>
<?php endif; ?>