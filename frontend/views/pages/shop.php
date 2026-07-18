<?php
// frontend/views/pages/shop.php
$pageTitle = 'Shop Fresh Groceries Online — GroceryDash';
$filters = $filters ?? [];
$categories = $categories ?? [];
$products = $products ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
require __DIR__ . '/../layouts/header.php';
?>

<section class="section">
  <div class="container shop-layout">

    <!-- ─── Sidebar filters ─── -->
    <aside class="shop-sidebar" style="background: #ffffff; border: 1px solid #eef1f0; border-radius: 12px; padding: 1.25rem;">
  <form method="GET" action="<?= APP_URL ?>/shop" id="filterForm">
    
    <input type="hidden" name="sort" value="<?= e($filters['sort'] ?? '') ?>">
    <input type="hidden" name="search" value="<?= e($filters['search'] ?? '') ?>">

    <div class="filter-group" style="margin-bottom: 1.5rem; border-bottom: 1px solid #f8f9fa; padding-bottom: 1.25rem;">
      <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; color: #212529; display: flex; justify-content: space-between; align-items: center;">
        <span>Category</span>
        <?php if (!empty($filters['category'])): ?>
          <a href="<?= APP_URL ?>/shop?<?= http_build_query(array_filter(['search' => $filters['search'] ?? '', 'sort' => $filters['sort'] ?? ''])) ?>" class="clear-filter" style="font-size: 0.75rem; color: #dc3545; text-decoration: none; font-weight: 400;">Clear ✕</a>
        <?php endif; ?>
      </h4>
      
      <div class="filter-options-list" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 220px; overflow-y: auto; padding-right: 4px;">
        <?php foreach ($categories as $cat): ?>
          <?php $isCurrentCat = ((string)($filters['category'] ?? '') === (string)$cat['slug']); ?>
          <label class="filter-check" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; color: <?= $isCurrentCat ? '#198754' : '#495057' ?>; font-weight: <?= $isCurrentCat ? '600' : '400' ?>;">
            <input type="radio" 
                   name="category" 
                   value="<?= e($cat['slug']) ?>"
                   <?= $isCurrentCat ? 'checked' : '' ?>
                   onchange="this.form.submit()"
                   style="accent-color: #198754; cursor: pointer;">
            <span><?= e($cat['name']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="filter-group">
      <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; color: #212529;">Price Range</h4>
      
      <div class="price-inputs" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
        <input type="number" 
               name="min_price" 
               placeholder="Min"
               min="0"
               value="<?= e($filters['min_price'] ?? '') ?>" 
               class="input-sm"
               style="width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.875rem;">
        
        <span style="color: #6c757d;">–</span>
        
        <input type="number" 
               name="max_price" 
               placeholder="Max"
               min="0"
               value="<?= e($filters['max_price'] ?? '') ?>" 
               class="input-sm"
               style="width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.875rem;">
      </div>
      
      <div class="sidebar-actions" style="display: flex; gap: 0.5rem; width: 100%; align-items: center; justify-content: space-between; box-sizing: border-box;">
  
        <button type="submit" 
                class="btn btn-sm btn-outline" 
                style="flex: 1; height: 38px; background: #198754; color: #fff; border: 1px solid #198754; padding: 0 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; line-height: 1; margin: 0;">
          Apply
        </button>
        
        <button type="button" 
                class="btn btn-sm btn-reset" 
                onclick="window.location.href='<?= APP_URL ?>/shop'"
                style="flex: 1; height: 38px; background: #ffffff; color: #198754; border: 1px solid #198754; padding: 0 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; line-height: 1; margin: 0;">
          Reset
        </button>
        
      </div>
    </div>

  </form>
</aside>

    <!-- ─── Product grid ─── -->
    <div class="shop-main">

      <div class="shop-toolbar">
        <p class="results-count">
          <?= $total ?> product<?= $total !== 1 ? 's' : '' ?> found
          <?= $filters['search'] ? 'for <em>"' . e($filters['search']) . '"</em>' : '' ?>
        </p>
        <select class="sort-select" onchange="location='<?= APP_URL ?>/shop?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)), sort: this.value})">
          <option value=""         <?= !$filters['sort']           ? 'selected' : '' ?>>Relevance</option>
          <option value="popular"  <?= $filters['sort']==='popular'  ? 'selected' : '' ?>>Most Popular</option>
          <option value="price_asc"<?= $filters['sort']==='price_asc'? 'selected' : '' ?>>Price: Low → High</option>
          <option value="price_desc"<?= $filters['sort']==='price_desc'?'selected':'' ?>>Price: High → Low</option>
          <option value="newest"   <?= $filters['sort']==='newest'   ? 'selected' : '' ?>>Newest</option>
        </select>
      </div>

      <?php if (empty($products)): ?>
        <div class="empty-state">
          <span style="font-size:3rem">🔍</span>
          <h3>No products found</h3>
          <p>Try changing your filters or <a href="<?= APP_URL ?>/shop">view all products</a>.</p>
        </div>
      <?php else: ?>
        <div class="products-grid">
          <?php $wishlistProductIds = $wishlistedIds ?? []; ?>
          <?php foreach ($products as $product): ?>
            <?php require __DIR__ . '/../../components/product-card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <?php
                $params = array_merge($filters, ['page' => $i]);
                $qs     = http_build_query(array_filter($params));
              ?>
              <a href="<?= APP_URL ?>/shop?<?= $qs ?>"
                 class="page-btn <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
