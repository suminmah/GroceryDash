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
require_once __DIR__ . '/../models/Setting.php';

class AdminController
{
    private OrderModel        $orders;
    private ProductModel      $products;
    private CategoryModel     $categories;
    private InventoryModel    $inventory;
    private DeliverySlotModel $slots;
    private UserModel         $users;
    private Setting           $settings;

    /**
     * Controller Initialization Lifecycle
     * Automatically applies comprehensive security guards over all child routes.
     */
    public function __construct()
    {
        // 🔒 Global Security Shield: Centralized Auth Guard Enforcement
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
            if (isset($_GET['get_category'])) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized administrative context access.']);
                exit;
            }
            header("Location: " . APP_URL . "/login");
            exit;
        }

        // Initialize Structural Models
        $this->orders     = new OrderModel();
        $this->products   = new ProductModel();
        $this->categories = new CategoryModel();
        $this->inventory  = new InventoryModel();
        $this->slots      = new DeliverySlotModel();
        $this->users      = new UserModel();
        $this->settings   = new Setting(Database::connect());
    }

    /**
     * ── 🎨 DRY View Rendering Isolation Wrapper ───────────────────────────
     */
    private function render(string $viewPath, string $pageTitle, array $data = []): void
    {
        // Extract array contexts into scope variables
        extract($data);
        
        // Context configuration mapping parameters fallback logic
        $savedLogo = $this->settings->get('site_logo');
        $adminLogo = !empty($savedLogo) ? $savedLogo : '/assets/images/logo.png';
        if (!str_starts_with($adminLogo, 'http') && !str_contains($adminLogo, '/grocery-shop/public')) {
            $adminLogo = APP_URL . '/' . ltrim($adminLogo, '/');
        }

        ob_start();
        require __DIR__ . "/../../frontend/views/admin/{$viewPath}.php";
        $content = ob_get_clean();
        
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    // ─────────────────────────────────────────────────────────
    //  Dashboard Panel
    // ─────────────────────────────────────────────────────────

    /** GET /admin */
    public function dashboard()
    {
        $this->render('dashboard', 'Admin Dashboard — GroceryDash', [
            'stats'         => $this->orders->getDashboardStats(),
            'recentOrders'  => $this->orders->getAll('', 1, 5),
            'lowStock'      => $this->inventory->getLowStockItems(),
            'topProducts'   => $this->orders->getTopProducts(5),
            'customerCount' => $this->users->countCustomers(),
            'revenueData'   => $this->orders->getDailyRevenue(date('Y-m-d', strtotime('-13 days')), date('Y-m-d'))
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  Orders Management
    // ─────────────────────────────────────────────────────────

    /** GET /admin/orders */
    public function ordersList()
    {
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $total  = $this->orders->count($status);

        $this->render('orders', 'Orders — Admin', [
            'status' => $status,
            'page'   => $page,
            'orders' => $this->orders->getAll($status, $page, 20),
            'pages'  => (int)ceil($total / 20)
        ]);
    }

    /** GET /admin/orders/{id} */
    public function orderDetail(int $orderId)
    {
        $order = $this->orders->findById($orderId);
        if (!$order) { 
            http_response_code(404); 
            die('Order missing from system files.'); 
        }

        $this->render('order-detail', 'Order #' . $orderId . ' — Admin', [
            'order' => $order
        ]);
    }

    /** POST /admin/orders/{id}/status */
    public function updateOrderStatus(int $orderId)
    {
        verifyCsrf();
        $status = trim($_POST['status'] ?? '');

        try {
            $this->orders->updateStatus($orderId, $status);
            flash('success', "Order #{$orderId} status updated to {$status}.");
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
            flash('success', "Order #{$orderId} cancelled and inventory items restored.");
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect(APP_URL . '/admin/orders');
    }

    // ─────────────────────────────────────────────────────────
    //  Products Catalogs
    // ─────────────────────────────────────────────────────────

    /** GET /admin/products */
    public function productsList()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));

        $this->render('products', 'Products — Admin', [
            'products' => $this->products->getAll([], $page),
            'page'     => $page,
            'total'    => $this->products->count(),
            'pages'    => $this->products->totalPages()
        ]);
    }

    /** GET /admin/products/new */
    public function productForm()
    {
        $this->render('product-form', 'Add Product — Admin', [
            'categories' => $this->categories->getAll(),
            'product'    => null
        ]);
    }

    /** GET /admin/products/{id}/edit */
    public function productEdit(int $productId)
    {
        $product = $this->products->findById($productId);
        if (!$product) { 
            http_response_code(404); 
            die('Target product profile not found.'); 
        }

        $this->render('product-form', 'Edit Product — Admin', [
            'product'    => $product,
            'categories' => $this->categories->getAll()
        ]);
    }

    /** POST /admin/products */
    public function productCreate()
    {
        verifyCsrf();
        $data = [
            'sku'           => trim($_POST['sku'] ?? ''),
            'name'          => trim($_POST['name'] ?? ''),
            'category_id'   => (int)($_POST['category_id'] ?? 0),
            'price'         => (float)($_POST['price'] ?? 0),
            'is_perishable' => (int)($_POST['is_perishable'] ?? 0),
        ];

        try {
            $productId = $this->products->create($data);
            $this->inventory->create([
                'product_id'       => $productId,
                'stock_qty'        => (int)($_POST['stock_qty'] ?? 0),
                'buffer_threshold' => (int)($_POST['buffer_threshold'] ?? 0),
            ]);
            flash('success', 'Product created successfully.');
            redirect(APP_URL . '/admin/products');
        } catch (PDOException $e) {
            flash('error', 'Could not create product record: ' . $e->getMessage());
            redirect(APP_URL . '/admin/products/new');
        }
    }

    /** POST /admin/products/{id} */
    public function productUpdate(int $productId)
    {
        verifyCsrf();
        $data = [
            'sku'           => trim($_POST['sku'] ?? ''),
            'name'          => trim($_POST['name'] ?? ''),
            'category_id'   => (int)($_POST['category_id'] ?? 0),
            'price'         => (float)($_POST['price'] ?? 0),
            'is_perishable' => (int)($_POST['is_perishable'] ?? 0),
        ];
        $this->products->update($productId, $data);
        flash('success', 'Product details saved.');
        redirect(APP_URL . '/admin/products');
    }

    // ─────────────────────────────────────────────────────────
    //  Inventory Trackers
    // ─────────────────────────────────────────────────────────

    /** GET /admin/inventory */
    public function inventoryList()
    {
        $this->render('inventory', 'Inventory — Admin', [
            'items'    => $this->inventory->getAll(),
            'lowCount' => $this->inventory->countLowStock()
        ]);
    }

    /** POST /admin/inventory/{productId}/restock */
    public function restock(int $productId)
    {
        verifyCsrf();
        $qty = max(0, (int)($_POST['quantity'] ?? 0));
        $this->inventory->setStock($productId, $qty);

        $buf = (int)($_POST['buffer_threshold'] ?? -1);
        if ($buf >= 0) {
            $this->inventory->setBufferThreshold($productId, $buf);
        }

        flash('success', 'Stock variables updated.');
        redirect(APP_URL . '/admin/inventory');
    }

    // ─────────────────────────────────────────────────────────
    //  Delivery Slots Engine
    // ─────────────────────────────────────────────────────────

    /** GET /admin/slots */
    public function slotsList()
    {
        $this->render('slots', 'Delivery Slots — Admin', [
            'slots'   => $this->slots->getAll(),
            'summary' => $this->slots->getCapacitySummaryByDate(date('Y-m-d'), date('Y-m-d', strtotime('+14 days')))
        ]);
    }

    /** POST /admin/slots */
    public function slotCreate()
    {
        verifyCsrf();
        $this->slots->create([
            'slot_date'  => $_POST['slot_date'],
            'start_time' => $_POST['start_time'],
            'end_time'   => $_POST['end_time'],
            'capacity'   => (int)($_POST['max_capacity'] ?? 10),
        ]);
        flash('success', 'Delivery tracking window generated.');
        redirect(APP_URL . '/admin/slots');
    }

    /** POST /admin/slots/bulk */
    public function slotBulkCreate()
    {
        verifyCsrf();
        $templates = [
            ['start_time' => '08:00:00', 'end_time' => '12:00:00', 'capacity' => 30],
            ['start_time' => '12:00:00', 'end_time' => '16:00:00', 'capacity' => 30],
            ['start_time' => '16:00:00', 'end_time' => '20:00:00', 'capacity' => 30],
        ];

        $start = DateTime::createFromFormat('Y-m-d', $_POST['from_date'] ?? '');
        $end   = DateTime::createFromFormat('Y-m-d', $_POST['to_date'] ?? '');

        if (!$start || !$end || $start->setTime(0,0) > $end->setTime(0,0)) {
            flash('error', 'Operational range configurations invalid.');
            redirect(APP_URL . '/admin/slots');
        }

        $created  = 0;
        $interval = new DateInterval('P1D');

        for ($date = clone $start; $date <= $end; $date->add($interval)) {
            foreach ($templates as $template) {
                $this->slots->create([
                    'slot_date'  => $date->format('Y-m-d'),
                    'start_time' => $template['start_time'],
                    'end_time'   => $template['end_time'],
                    'capacity'   => $template['capacity'],
                ]);
                $created++;
            }
        }

        flash('success', "{$created} workflow distribution items committed.");
        redirect(APP_URL . '/admin/slots');
    }

    /** POST /admin/slots/{id}/delete */
    public function slotDelete(int $slotId)
    {
        verifyCsrf();
        $this->slots->delete($slotId);
        flash('success', 'Slot item scrubbed from database records.');
        redirect(APP_URL . '/admin/slots');
    }

    // ─────────────────────────────────────────────────────────
    //  Categories Management (Includes API Handlers)
    // ─────────────────────────────────────────────────────────

    /** GET /admin/categories */
    public function categoriesList()
    {
        // 🌟 REFACTORED API HANDLER: Clean data parsing separation
        if (isset($_GET['get_category'])) {
            if (ob_get_length()) ob_clean(); 
            header('Content-Type: application/json');
            
            try {
                $id = (int)$_GET['get_category'];
                // Clean Fix: Route execution through your unified Model context wrapper mapping
                $category = $this->categories->findById($id);
                echo json_encode($category ?: ['error' => 'Category entry not present']);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            exit; 
        }

        $this->render('categories', 'Categories — Admin', [
            'categories' => $this->categories->getTree()
        ]);
    }

    /** POST /admin/categories */
    public function categoryCreate()
    {
        verifyCsrf();
        $this->categories->create([
            'name'      => trim($_POST['name'] ?? ''),
            'parent_id' => $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null,
        ]);
        flash('success', 'Category tracking profile initialized.');
        redirect(APP_URL . '/admin/categories');
    }

    // ─────────────────────────────────────────────────────────
    //  Customer Subsystems
    // ─────────────────────────────────────────────────────────

    /** GET /admin/customers */
    public function customersList()
    {
        $this->render('customers', 'Customers — Admin', [
            'customers' => $this->users->getAllCustomers()
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  Logo Context & Brand Setup Utilities
    // ─────────────────────────────────────────────────────────

    /** GET /admin/settings/logo */
    public function logoForm() 
    {
        $this->render('logo-settings', 'Logo Settings', [
            'error'       => flash('logo_error'),
            'success'     => flash('logo_success'),
            'currentLogo' => !empty($this->settings->get('site_logo')) ? $this->settings->get('site_logo') : APP_URL . '/assets/images/logo.png'
        ]);
    }

    /** POST /admin/settings/logo */
    public function updateLogo() 
    {
        verifyCsrf();

        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            flash('logo_error', 'Please select a valid image file to upload.');
            redirect(APP_URL . '/admin/settings/logo');
        }

        $file = $_FILES['logo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('logo_error', 'An error occurred during file upload.');
            redirect(APP_URL . '/admin/settings/logo');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            flash('logo_error', 'File size exceeds the 2MB limit.');
            redirect(APP_URL . '/admin/settings/logo');
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo        = new finfo(FILEINFO_MIME_TYPE);
        $mimeType     = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimes)) {
            flash('logo_error', 'Invalid file type format constraint matched.');
            redirect(APP_URL . '/admin/settings/logo');
        }

        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename  = 'logo_' . time() . '.' . $ext; 
        $uploadDir = __DIR__ . '/../../public/uploads/logo/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $relativeUrlPath = APP_URL . '/uploads/logo/' . $filename;
            $this->settings->set('site_logo', $relativeUrlPath);
            flash('logo_success', 'Website logo updated successfully!');
        } else {
            flash('logo_error', 'Failed to write image data parameters onto disk file blocks.');
        }

        redirect(APP_URL . '/admin/settings/logo');
    }
}