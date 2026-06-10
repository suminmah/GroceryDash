<?php 
$product = $product ?? [];
$isEdit = isset($product) && !empty($product['id']);
$formAction = $isEdit ? APP_URL . "/admin/products/{$product['id']}/edit" : APP_URL . "/admin/products/add";
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="font-weight-bold text-dark h3 mb-1"><?= $isEdit ? 'Edit Product Catalog Item' : 'Add New Product Item' ?></h1>
            <p class="text-muted small mb-0">Dual database table insertion sync system: <code>products</code> + <code>inventory</code></p>
        </div>
        <a href="<?= APP_URL ?>/admin/products" class="btn btn-sm btn-light border text-secondary shadow-sm">
            <i class="bi bi-arrow-left"></i> Cancel
        </a>
    </div>

    <form action="<?= $formAction ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?php if (function_exists('csrfTokenField')) { csrfTokenField(); } ?>

        <div class="row g-4">
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm border-0 rounded mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title text-dark font-weight-bold mb-4" style="font-size: 1.1rem;">Product Details</h5>
                        
                        <div class="mb-4">
                            <label for="name" class="form-label text-muted small font-weight-bold">Product Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., Fresh Tomatoes" required autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label text-muted small font-weight-bold">Product Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Provide description summary text..."><?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded">
                    <div class="card-body p-4">
                        <h5 class="card-title text-dark font-weight-bold mb-4" style="font-size: 1.1rem;">Price and Stock Allocation</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="price" class="form-label text-muted small font-weight-bold">Base Price (Rs.) *</label>
                                <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" value="<?= htmlspecialchars((string)($product['price'] ?? '')) ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label for="sale_price" class="form-label text-muted small font-weight-bold">Sale Price (Rs.)</label>
                                <input type="number" step="0.01" min="0" name="sale_price" id="sale_price" class="form-control" value="<?= htmlspecialchars((string)($product['sale_price'] ?? '')) ?>" placeholder="Optional">
                            </div>

                            <div class="col-md-4">
                                <label for="quantity" class="form-label text-muted small font-weight-bold">Initial Inventory Quantity *</label>
                                <input type="number" min="0" name="quantity" id="quantity" class="form-control" value="<?= isset($product['quantity']) ? (int)$product['quantity'] : (isset($product['stock']) ? (int)$product['stock'] : '') ?>" placeholder="e.g., 150" required>
                            </div>

                            <div class="col-md-12">
                                <label for="unit" class="form-label text-muted small font-weight-bold">Measurement Unit</label>
                                <input type="text" name="unit" id="unit" class="form-control" value="<?= htmlspecialchars($product['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., 500g, 1 dozen, 1 L">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 rounded mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title text-dark font-weight-bold mb-3" style="font-size: 1.1rem;">Taxonomy & Marketing Flags</h5>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label text-muted small font-weight-bold">Category *</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select active category...</option>
                                
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php 
                                        // Safely extract the ID depending on whether it's an object or an array row array matrix
                                        $catId = is_array($cat) ? (int)$cat['id'] : (int)$cat->id;
                                        $catName = is_array($cat) ? $cat['name'] : $cat->name;
                                        
                                        $match = ($isEdit && (int)$product['category_id'] === $catId) ? 'selected' : ''; 
                                        ?>
                                        <option value="<?= $catId ?>" <?= $match ?>><?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No categories found in database. Please create one first.</option>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Please map this inventory profile to a primary department category node.</div>
                        </div>

                        <div class="mb-3">
                            <label for="is_perishable" class="form-label text-muted small font-weight-bold d-block">Is Perishable?</label>
                            <select name="is_perishable" id="is_perishable" class="form-select">
                                <option value="0" <?= (isset($product['is_perishable']) && (int)$product['is_perishable'] === 0) ? 'selected' : '' ?>>No (Ambient Stability)</option>
                                <option value="1" <?= (isset($product['is_perishable']) && (int)$product['is_perishable'] === 1) ? 'selected' : '' ?>>Yes (Requires Refrigeration)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="is_featured" class="form-label text-muted small font-weight-bold d-block">Featured Item Placement</label>
                            <select name="is_featured" id="is_featured" class="form-select">
                                <option value="0" <?= (isset($product['is_featured']) && (int)$product['is_featured'] === 0) ? 'selected' : '' ?>>Standard Listing</option>
                                <option value="1" <?= (isset($product['is_featured']) && (int)$product['is_featured'] === 1) ? 'selected' : '' ?>>Featured Listing</option>
                            </select>
                        </div>

                        <div>
                            <label for="is_active" class="form-label text-muted small font-weight-bold d-block">Visibility State</label>
                            <select name="is_active" id="is_active" class="form-select">
                                <option value="1" <?= (!isset($product['is_active']) || (int)$product['is_active'] === 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= (isset($product['is_active']) && (int)$product['is_active'] === 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="product-image-container">
                    <?php 
                    // Secure filename tracking with a fallback standard icon image placeholder
                    $displayImage = !empty($product['image']) ? $product['image'] : 'default.jpg'; 
                    ?>
                    
                    <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($displayImage, ENT_QUOTES, 'UTF-8') ?>" 
                        class="img-fluid rounded" 
                        alt="<?= htmlspecialchars($product['name'] ?? 'Catalog Item') ?>"
                        style="max-height: 150px; object-fit: contain;">
                </div>

                <button type="submit" class="btn <?= $isEdit ? 'btn-warning text-dark font-weight-bold' : 'btn-success' ?> w-100 py-2 shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-cloud-check-fill"></i>
                    <?= $isEdit ? 'Commit Structural Changes' : 'Add Product' ?>
                </button>
            </div>
        </div>
    </form>
</div>