<?php
// frontend/views/pages/search.php
// Expected variables from ShopController::search():
// - $products: Array of matching product items fetched from the database
// - $searchQuery: The string text the user typed (e.g., $_GET['q'])

require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="font-family: 'DM Sans', sans-serif; padding: 0 15px; margin-top: 2rem; margin-bottom: 2rem;">
    <div class="search-summary mb-4 pb-3" style="border-bottom: 1px solid #dee2e6; margin-bottom: 1.5rem; padding-bottom: 1rem;">
        <h1 class="h3 fw-bold text-dark" style="font-family: 'Syne', sans-serif; font-size: 1.75rem; color: #212529; font-weight: 700; margin-bottom: 0.5rem;">
            Search Results 
            <?php if (!empty($searchQuery)): ?>
                for <span class="text-success" style="color: #198754;">"<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>"</span>
            <?php endif; ?>
        </h1>
        <p class="text-muted small mb-0" style="color: #6c757d; font-size: 0.875rem;">
            Found <?= count($products ?? []) ?> matching <?= count($products ?? []) === 1 ? 'item' : 'items' ?>
        </p>
    </div>

    <?php if (!empty($products) && count($products) > 0): ?>
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 2rem;">
            <?php foreach ($products as $product): ?>
                
                <?php 
                    // ==========================================
                    // 1. IMAGE FIX: FORCE RE-ALIGNMENT TO .JPG
                    // ==========================================
                    $rawDbValue = isset($product['image']) ? trim($product['image']) : '';
                    $pureFileName = !empty($rawDbValue) ? basename($rawDbValue) : '';

                    if (empty($pureFileName) || $pureFileName === 'products' || $pureFileName === 'images') {
                        if (!empty($product['name'])) {
                            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $product['name'])));
                            $finalImage = $slug . '.jpg';
                        } else {
                            $finalImage = 'default-product.jpg';
                        }
                    } else {
                        $pathInfo = pathinfo($pureFileName);
                        $finalImage = $pathInfo['filename'] . '.jpg';
                    }

                    $computedImgUrl = APP_URL . '/assets/images/products/' . $finalImage;

                    // ==========================================
                    // 2. STOCK FIX: AUTOMATIC COLUMN DETECTION
                    // ==========================================
                    // This dynamically finds your "58" value regardless of column naming rules
                    $currentStock = 0;
                    if (isset($normalizedProduct['stock'])) {
                        $currentStock = (int)$normalizedProduct['stock'];
                    } elseif (isset($normalizedProduct['quantity'])) {
                        $currentStock = (int)$normalizedProduct['quantity'];
                    } elseif (isset($normalizedProduct['qty'])) {
                        $currentStock = (int)$normalizedProduct['qty'];
                    }
                ?>

                <div class="product-card" style="background: #fff; border: 1px solid #eef1f0; border-radius: 12px; padding: 1.25rem; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                    
                    <?php if (!empty($product['discount_price'])): ?>
                        <span class="badge bg-danger position-absolute" style="position: absolute; top: 10px; left: 10px; z-index: 2; background-color: #dc3545; color: #fff; border-radius: 4px; font-size: 0.75rem; padding: 0.25rem 0.5rem; font-weight: 600;">
                            OFFER
                        </span>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>/product/<?= htmlspecialchars($product['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" style="display: block; text-align: center; margin-bottom: 1rem; text-decoration: none;">
                        <img src="<?= htmlspecialchars($computedImgUrl, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($product['name'] ?? 'Product', ENT_QUOTES, 'UTF-8') ?>" 
                             style="max-height: 140px; object-fit: contain; width: auto; max-width: 100%; display: inline-block;">
                    </a>

                    <div class="product-info" style="margin-bottom: 1rem;">
                        <span class="text-muted d-block small mb-1" style="display: block; text-transform: uppercase; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px; color: #6c757d;">
                            <?= htmlspecialchars($product['category_name'] ?? 'Groceries', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <h3 class="h6 fw-semibold mb-2" style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; min-height: 40px; line-height: 1.4;">
                            <a href="<?= APP_URL ?>/product/<?= htmlspecialchars($product['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" style="text-decoration: none; color: #212529;">
                                <?= htmlspecialchars($product['name'] ?? 'Generic Product', ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h3>
                        
                        <?php if ($currentStock <= 0): ?>
                            <span class="text-danger small d-block mb-2 fw-medium" style="color: #dc3545; font-size: 0.875rem; font-weight: 500;">⚠️ Out of Stock</span>
                        <?php elseif ($currentStock <= 5): ?>
                            <span class="text-warning small d-block mb-2 fw-medium" style="color: #ffc107; font-size: 0.875rem; font-weight: 500;">⏳ Only <?= $currentStock ?> left!</span>
                        <?php else: ?>
                            <span class="text-success small d-block mb-2 fw-medium" style="color: #198754; font-size: 0.875rem; font-weight: 500;">✓ In Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-action mt-auto pt-2" style="margin-top: auto; padding-top: 0.5rem; border-top: 1px solid #eef1f0; display: flex; align-items: center; justify-content: space-between;">
                        <div class="pricing-block">
                            <?php if (!empty($product['sale_price'])): ?>
                                <span class="text-success fw-bold d-block h5 mb-0" style="color: #198754; font-weight: 700; font-size: 1.25rem; display: block; margin-bottom: 0;">Rs. <?= number_format((float)$product['sale_price'], 2) ?></span>
                                <span class="text-muted text-decoration-line-through small" style="font-size: 0.8rem; text-decoration: line-through; color: #6c757d;">Rs. <?= number_format((float)$product['price'], 2) ?></span>
                            <?php else: ?>
                                <span class="text-success fw-bold h5 mb-0 d-block" style="color: #198754; font-weight: 700; font-size: 1.25rem; display: block; margin-bottom: 0;">Rs. <?= number_format((float)($product['price'] ?? 0), 2) ?></span>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="<?= APP_URL ?>/cart/add" style="margin: 0;">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" 
                                    class="btn btn-sm btn-success d-flex align-items-center justify-content-center" 
                                    style="width: 36px; height: 36px; padding: 0; border-radius: 50%; background-color: #198754; border: none; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                    title="Add to Cart"
                                    <?= $currentStock <= 0 ? 'disabled style="opacity: 0.5; background-color: #6c757d; cursor: not-allowed;"' : '' ?>>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: block;"><path d="M12 5v14M5 12h14"/></svg>
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="text-center py-5 my-4" style="text-align: center; padding: 3rem 0; margin-top: 1.5rem; margin-bottom: 1.5rem; background: #f9fafb; border-radius: 16px; border: 1px dashed #d1d5db;">
            <div class="empty-icon mb-3" style="font-size: 3.5rem; margin-bottom: 1rem;">🔍❌</div>
            <h2 class="h4 fw-bold text-dark mb-2" style="font-size: 1.5rem; font-weight: 700; color: #212529; margin-bottom: 0.5rem;">No Matching Groceries Found</h2>
            <p class="text-muted mx-auto mb-4" style="max-width: 420px; color: #6c757d; margin-left: auto; margin-right: auto; margin-bottom: 1.5rem;">
                We couldn't find anything matching your exact query. Check for typos or broader terms.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="<?= APP_URL ?>/shop" style="background: #198754; color: #fff; text-decoration: none; padding: 0.5rem 1.5rem; border-radius: 50px; font-weight: 500; font-size: 0.9rem;">
                    Browse All Products
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/../layouts/footer.php';
?>