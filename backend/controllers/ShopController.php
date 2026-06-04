<?php
// ============================================================
//  backend/controllers/ShopController.php
//  Handles: homepage, shop listing, product detail, search,
//           offers — all read-only frontend pages.
// ============================================================

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/InventoryModel.php';
require_once __DIR__ . '/../helpers/functions.php';

class ShopController
{
    private ProductModel  $products;
    private CategoryModel $categories;
    private InventoryModel $inventory;

    public function __construct()
    {
        $this->products   = new ProductModel();
        $this->categories = new CategoryModel();
        $this->inventory  = new InventoryModel();
    }

    // ─────────────────────────────────────────────────────────
    //  GET /
    // ─────────────────────────────────────────────────────────

    /**
     * Homepage — featured products + root categories
     */
    public function home()
    {
        // 8 newest products as "featured" (no is_featured column in schema)
        $featured   = array_slice($this->products->getAll([], 1), 0, 8);
        $rootCats   = $this->categories->getRootCategories();
        $pageTitle  = 'GroceryDash — Fresh Grocery Delivered Fast';

        $wishlistedIds = [];
        if (isLoggedIn()) {
            require_once __DIR__ . '/../models/WishlistModel.php';
            $wishlistModel = (new WishlistModel())->getProductIds(
                (int) $_SESSION['user']['id']
            );
            $wishlistedIds = $wishlistModel;
        }
        require __DIR__ . '/../../frontend/views/pages/home.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /shop
    // ─────────────────────────────────────────────────────────

    /**
     * Shop listing page — supports category, search, price, sort, page
     */
    public function shop()
    {
        
        // 1. Get all categories for the sidebar filter
        $categories = $this->categories->getAll();   // now includes parent_id

        // 2. Handle category filter (by slug, not ID)
        $categorySlug = $_GET['category'] ?? '';
        $categoryId = null;
        $descendantIds = [];

        if ($categorySlug !== '') {
            $cat = $this->categories->findBySlug($categorySlug);
            if ($cat) {
                $categoryId = (int)$cat['id'];
                $descendantIds = $this->categories->getAllDescendantIds($categoryId);
            }
        }

        // 3. Build filters array for ProductModel
        $filters = [
            'category'      => $categorySlug,          // for view highlighting
            'category_id'   => $categoryId,
            'category_ids'  => $descendantIds,
            'search'        => trim($_GET['search']    ?? ''),
            'min_price'     => $_GET['min_price']       ?? '',
            'max_price'     => $_GET['max_price']       ?? '',
            'is_perishable' => $_GET['is_perishable']   ?? '',
            'in_stock_only' => !empty($_GET['in_stock']),
            'sort'          => $_GET['sort']             ?? '',
        ];

        $page = (int)($_GET['page'] ?? 1);
        $products   = $this->products->getAll($filters, $page);
        $totalPages = $this->products->totalPages($filters);

        // 4. Load view with all variables
        require __DIR__ . '/../../frontend/views/pages/shop.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /product/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * Product detail page
     */
    public function detail(int $productId)
    {
        $product = $this->products->findById($productId);

        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }

        // Breadcrumb: leaf → root
        $breadcrumb = $this->categories->getBreadcrumb(
            (int) $product['category_id']
        );

        // Related products (same category, different id)
        $related = $this->products->getRelated(
            (int) $product['category_id'],
            $productId,
            4
        );

        // Live inventory data
        $inventory = $this->inventory->findByProductId($productId);

        $pageTitle = htmlspecialchars($product['name'], ENT_QUOTES) . ' — GroceryDash';

        require __DIR__ . '/../../frontend/views/pages/product.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /search
    // ─────────────────────────────────────────────────────────

    /**
     * Search results page
     */
    public function search()
    {
        $query    = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        $products = $query ? $this->products->search($query) : [];
        $pageTitle = $query
            ? "Search results for \"" . htmlspecialchars($query, ENT_QUOTES) . "\" — GroceryDash"
            : 'Search — GroceryDash';

        require __DIR__ . '/../../frontend/views/pages/search.php';
    }

    // ─────────────────────────────────────────────────────────
    //  GET /category/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * Category landing page (resolves sub-category IDs too)
     */
    public function category(int $categoryId)
    {
        $category = $this->categories->findById($categoryId);

        if (!$category) {
            http_response_code(404);
            require __DIR__ . '/../../frontend/views/errors/404.php';
            return;
        }

        // Include all sub-category products
        $ids        = $this->categories->getAllDescendantIds($categoryId);
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $products   = $this->products->getByCategory($ids, $page);

        $breadcrumb = $this->categories->getBreadcrumb($categoryId);
        $subCats    = $this->categories->getSubCategories($categoryId);

        $pageTitle  = htmlspecialchars($category['name'], ENT_QUOTES) . ' — GroceryDash';

        require __DIR__ . '/../../frontend/views/pages/category.php';
    }

    public function offers()
    {
        // Fetch products with discount (if you have a discount column)
        // Or just show products with a special flag, or fetch all and mark some as "sale"
        $productModel = new ProductModel();
        // Example: get all products and manually pick sale items (you need logic)
        $products = $productModel->getAll([], 1);
        // For now, just show a simple view with no offers or fake data
        $pageTitle = 'Special Offers — GroceryDash';
        require __DIR__ . '/../../frontend/views/pages/offers.php';
    }
}
