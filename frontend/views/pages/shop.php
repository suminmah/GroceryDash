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
    <aside class="shop-sidebar">
      <form method="GET" action="<?= APP_URL ?>/shop" id="filterForm">

        <div class="filter-group">
          <h4>Category</h4>
          <?php foreach ($categories as $cat): ?>
            <label class="filter-check">
              <input type="radio" name="category" value="<?= e($cat['slug']) ?>"
                <?= ($filters['category'] === $cat['slug']) ? 'checked' : '' ?>
                onchange="this.form.submit()">
              <?= e($cat['name']) ?>
            </label>
          <?php endforeach; ?>
          <?php if (isset($filters['category']) && $filters['category']): ?>
            <a href="<?= APP_URL ?>/shop" class="clear-filter">Clear ✕</a>
          <?php endif; ?>
        </div>

        <div class="filter-group">
          <h4>Price Range</h4>
          <div class="price-inputs">
            <input type="number" name="min_price" placeholder="Min"
              value="<?= e($filters['min_price']) ?>" class="input-sm">
            <span>–</span>
            <input type="number" name="max_price" placeholder="Max"
              value="<?= e($filters['max_price']) ?>" class="input-sm">
          </div>
          <button type="submit" class="btn btn-sm btn-outline">Apply</button>
          <button type="button" class="btn btn-sm btn-reset" onclick="location='<?= APP_URL ?>/shop'">
          <a>Reset</a>
          </button>
        </div>

        <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">
        <input type="hidden" name="search" value="<?= e($filters['search']) ?>">
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
