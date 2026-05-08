<?php
// ============================================================
//  backend/controllers/AdminController.php
//  Admin-only actions: dashboard, orders, products, inventory,
//  delivery slots, customers
// ============================================================

require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/InventoryModel.php';
require_once __DIR__ . '/../models/DeliverySlotModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/functions.php';

class AdminController
{
    private OrderModel        $orders;
    private ProductModel      $products;
    private CategoryModel     $categories;
    private InventoryModel    $inventory;
    private DeliverySlotModel $slots;
    private UserModel         $users;

    public function __construct()
    {
        // requireLogin();
        // if (!isAdmin()) {
        //     http_response_code(403);
        //     die('Admin access required.');
        // }
        $this->orders     = new OrderModel();
        $this->products   = new ProductModel();
        $this->categories = new CategoryModel();
        $this->inventory  = new InventoryModel();
        $this->slots      = new DeliverySlotModel();
        $this->users      = new UserModel();
    }

    // ─────────────────────────────────────────────────────────
    //  GET /admin
    // ─────────────────────────────────────────────────────────

    /**
     * Admin dashboard — summary stats + recent orders + low stock
     */
    public function dashboard()
    {
        $stats       = $this->orders->getDashboardStats();
        $recentOrders= $this->orders->getAll('', 1, 5);
        $lowStock    = $this->inventory->getLowStockItems();
        $topProducts = $this->orders->getTopProducts(5);
        $customerCount = $this->users->countCustomers();

        // Revenue chart: last 14 days
        $revenueData = $this->orders->getDailyRevenue(
            date('Y-m-d', strtotime('-13 days')),
            date('Y-m-d')
        );

        $pageTitle = 'Admin Dashboard — GroceryDash';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/dashboard.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    // ─────────────────────────────────────────────────────────
    //  Orders
    // ─────────────────────────────────────────────────────────

    /** GET /admin/orders */
    public function ordersList()
    {
        $status  = $_GET['status'] ?? '';
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $orders  = $this->orders->getAll($status, $page, 20);
        $total   = $this->orders->count($status);
        $pages   = (int) ceil($total / 20);

        $pageTitle = 'Orders — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/orders.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** GET /admin/orders/{id} */
    public function orderDetail(int $orderId)
    {
        $order = $this->orders->findById($orderId);
        if (!$order) { http_response_code(404); die('Order not found.'); }

        $pageTitle = 'Order #' . $orderId . ' — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/order-detail.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** POST /admin/orders/{id}/status */
    public function updateOrderStatus(int $orderId)
    {
        verifyCsrf();
        $status = trim($_POST['status'] ?? '');

        try {
            $this->orders->updateStatus($orderId, $status);
            flash('success', "Order #$orderId status updated to $status.");
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
        }
        redirect(APP_URL . '/admin/orders/' . $orderId);
    }

    /** POST /admin/orders/{id}/cancel */
    public function cancelOrder(int $orderId)
    {
        verifyCsrf();
        try {
            $this->orders->cancel($orderId);
            flash('success', "Order #$orderId cancelled and stock restored.");
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect(APP_URL . '/admin/orders');
    }

    // ─────────────────────────────────────────────────────────
    //  Products
    // ─────────────────────────────────────────────────────────

    /** GET /admin/products */
    public function productsList()
    {
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $products = $this->products->getAll([], $page);
        $total    = $this->products->count();
        $pages    = $this->products->totalPages();

        $pageTitle = 'Products — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/products.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** GET /admin/products/new */
    public function productForm()
    {
        $categories = $this->categories->getAll();
        $product    = null;  // null = new product form
        $pageTitle  = 'Add Product — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/product-form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** GET /admin/products/{id}/edit */
    public function productEdit(int $productId)
    {
        $product = $this->products->findById($productId);
        if (!$product) { http_response_code(404); die('Product not found.'); }
        $categories = $this->categories->getAll();
        $pageTitle  = 'Edit Product — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/product-form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** POST /admin/products (create) */
    public function productCreate()
    {
        verifyCsrf();
        $data = [
            'sku'          => trim($_POST['sku']          ?? ''),
            'name'         => trim($_POST['name']         ?? ''),
            'category_id'  => (int)   ($_POST['category_id']  ?? 0),
            'price'        => (float) ($_POST['price']        ?? 0),
            'is_perishable'=> (int)   ($_POST['is_perishable'] ?? 0),
        ];

        try {
            $productId = $this->products->create($data);
            // Create inventory row with 0 stock
            $this->inventory->create([
                'product_id'       => $productId,
                'stock_qty'        => (int) ($_POST['stock_qty']        ?? 0),
                'buffer_threshold' => (int) ($_POST['buffer_threshold'] ?? 0),
            ]);
            flash('success', 'Product created successfully.');
            redirect(APP_URL . '/admin/products');
        } catch (PDOException $e) {
            flash('error', 'Could not create product: ' . $e->getMessage());
            redirect(APP_URL . '/admin/products/new');
        }
    }

    /** POST /admin/products/{id} (update) */
    public function productUpdate(int $productId)
    {
        verifyCsrf();
        $data = [
            'sku'          => trim($_POST['sku']          ?? ''),
            'name'         => trim($_POST['name']         ?? ''),
            'category_id'  => (int)   ($_POST['category_id']  ?? 0),
            'price'        => (float) ($_POST['price']        ?? 0),
            'is_perishable'=> (int)   ($_POST['is_perishable'] ?? 0),
        ];
        $this->products->update($productId, $data);
        flash('success', 'Product updated.');
        redirect(APP_URL . '/admin/products');
    }

    // ─────────────────────────────────────────────────────────
    //  Inventory
    // ─────────────────────────────────────────────────────────

    /** GET /admin/inventory */
    public function inventoryList()
    {
        $items     = $this->inventory->getAll();
        $lowCount  = $this->inventory->countLowStock();
        $pageTitle = 'Inventory — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/inventory.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** POST /admin/inventory/{productId}/restock */
    public function restock(int $productId)
    {
        verifyCsrf();
        $qty = max(0, (int) ($_POST['quantity'] ?? 0));
        $this->inventory->setStock($productId, $qty);

        $buf = (int) ($_POST['buffer_threshold'] ?? -1);
        if ($buf >= 0) {
            $this->inventory->setBufferThreshold($productId, $buf);
        }

        flash('success', 'Stock updated.');
        redirect(APP_URL . '/admin/inventory');
    }

    // ─────────────────────────────────────────────────────────
    //  Delivery Slots
    // ─────────────────────────────────────────────────────────

    /** GET /admin/slots */
    public function slotsList()
    {
        $slots     = $this->slots->getAll();
        $summary   = $this->slots->getCapacitySummaryByDate(
            date('Y-m-d'),
            date('Y-m-d', strtotime('+14 days'))
        );
        $pageTitle = 'Delivery Slots — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/slots.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** POST /admin/slots (create single slot) */
    public function slotCreate()
    {
        verifyCsrf();
        $this->slots->create([
            'slot_date'    => $_POST['slot_date'],
            'start_time'   => $_POST['start_time'],
            'end_time'     => $_POST['end_time'],
            'max_capacity' => (int) ($_POST['max_capacity'] ?? 1),
        ]);
        flash('success', 'Delivery slot created.');
        redirect(APP_URL . '/admin/slots');
    }

    /** POST /admin/slots/bulk (generate a week of slots) */
    public function slotBulkCreate()
    {
        verifyCsrf();
        $templates = [
            ['start_time' => '08:00:00', 'end_time' => '12:00:00', 'max_capacity' => 30],
            ['start_time' => '12:00:00', 'end_time' => '16:00:00', 'max_capacity' => 30],
            ['start_time' => '16:00:00', 'end_time' => '20:00:00', 'max_capacity' => 30],
        ];

        $fromDate = $_POST['from_date'] ?? '';
        $toDate   = $_POST['to_date']   ?? '';
        $start    = DateTime::createFromFormat('Y-m-d', $fromDate);
        $end      = DateTime::createFromFormat('Y-m-d', $toDate);

        if (!$start || !$end) {
            flash('error', 'Invalid date range.');
            redirect(APP_URL . '/admin/slots');
        }

        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        if ($start > $end) {
            flash('error', 'The start date must be before or equal to the end date.');
            redirect(APP_URL . '/admin/slots');
        }

        $created = 0;
        $interval = new DateInterval('P1D');

        for ($date = clone $start; $date <= $end; $date->add($interval)) {
            foreach ($templates as $template) {
                $this->slots->create([
                    'slot_date'    => $date->format('Y-m-d'),
                    'start_time'   => $template['start_time'],
                    'end_time'     => $template['end_time'],
                    'max_capacity' => $template['max_capacity'],
                ]);
                $created++;
            }
        }

        flash('success', "$created delivery slots created.");
        redirect(APP_URL . '/admin/slots');
    }

    /** POST /admin/slots/{id}/delete */
    public function slotDelete(int $slotId)
    {
        verifyCsrf();
        $this->slots->delete($slotId);
        flash('success', 'Slot deleted.');
        redirect(APP_URL . '/admin/slots');
    }

    // ─────────────────────────────────────────────────────────
    //  Categories
    // ─────────────────────────────────────────────────────────

    /** GET /admin/categories */
    public function categoriesList()
    {
        $tree      = $this->categories->getTree();
        $pageTitle = 'Categories — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/categories.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    /** POST /admin/categories (create) */
    public function categoryCreate()
    {
        verifyCsrf();
        $this->categories->create([
            'name'      => trim($_POST['name']      ?? ''),
            'parent_id' => $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null,
        ]);
        flash('success', 'Category created.');
        redirect(APP_URL . '/admin/categories');
    }

    // ─────────────────────────────────────────────────────────
    //  Customers
    // ─────────────────────────────────────────────────────────

    /** GET /admin/customers */
    public function customersList()
    {
        $customers = $this->users->getAllCustomers();
        $pageTitle = 'Customers — Admin';
        ob_start();
        require __DIR__ . '/../../frontend/views/admin/customers.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }
}
