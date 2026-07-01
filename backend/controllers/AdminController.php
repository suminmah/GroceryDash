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
    private function render(string $viewPath, string $pageTitle, array $data = [])
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

    public function orderEdit(int $orderId)
    {
        // 1. Extract database structural payload data points from the model layer
        // Uses the fixed, resilient method we updated earlier
        $order = $this->orders->findById($orderId);

        // 2. Defensive Exception Guard: Throw explicit 404 fallback code if ID is corrupted or non-existent
        if (!$order) {
            http_response_code(404);
            flash('error', 'The requested transaction token could not be mapped to an active database entity record.');
            redirect(APP_URL . '/admin/orders');
        }

        // 3. Render the update sub-view dashboard panel framework
        $this->render('order-edit', 'Modify Order Record — Admin', [
            'order' => $order
        ]);
    }

    /**
     * POST /admin/orders/{id}/update
     * Safe atomic processing stream to commit database status modification values.
     */
    public function orderUpdate(int $orderId)
    {
        verifyCsrf();
        
        $status       = trim($_POST['status'] ?? 'pending');
        $paymentState = trim($_POST['payment_status'] ?? 'pending');

        $updated = $this->orders->updateStatus($orderId, $status, $paymentState);

        if ($updated) {
            if(function_exists('flash')) {
                flash('success', "Order #{$orderId} details saved successfully.");
            } else {
                if(function_exists('flash')) {
                    flash('error', "Failed to write updates of Order #{$orderId}.");
                }
            }
        }

        header('Location: ' . APP_URL . '/admin/orders');
        exit;
    }

    /** POST /admin/orders/{id}/status */
    public function updateOrderStatus(int $orderId)
    {
        verifyCsrf();
        $status = trim($_POST['status'] ?? '');
        $paymentState = trim($_POST['payment_status'] ?? 'Pending');

        try {
            $this->orders->updateStatus($orderId, $status, $paymentState);
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

    /** GET /admin/products */
    public function productsList()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));

        $this->render('products', 'Products — Admin', [
            'products' => $this->products->getAll([], $page),
            'page'     => $page,
            'total'    => $this->products->count() ?? 0,
            'pages'    => $this->products->totalPages() ?? 1
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

        $imageFilename = 'default.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName    = $_FILES['image']['name'];
            $fileSize    = $_FILES['image']['size'];
            $fileType    = $_FILES['image']['type'];
            
            // Extract file extension cleanly
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            // Sanitize and secure the filename to prevent collision errors
            // Generates names like: prod_64593bc1a23e5.png
            $newFileName = 'prod_' . uniqid() . '.' . $fileExtension;
            
            // Enforce whitelist constraint rules on common image formats
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Absolute destination folder mapping layout path
                $uploadFileDir = __DIR__ . '/../../public/assets/images/products/';
                $dest_path = $uploadFileDir . $newFileName;
                
                // Execute block relocation from temporary memory cache down to drive disk
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imageFilename = $newFileName; // 🌟 SUCCESS: Assign the new clean filename string
                }
            }
        }
        
        // 1. Prepare data matching your exact 'products' table columns (Image 1)
        $productData = [
            'category_id'   => (int)($_POST['category_id'] ?? 0),
            'name'          => trim($_POST['name'] ?? ''),
            'slug'          => trim(strtolower(str_replace(' ', '-', $_POST['name'] ?? ''))), // Auto-generate slug
            'description'   => !empty($_POST['description']) ? trim($_POST['description']) : null,
            'price'         => (float)($_POST['price'] ?? 0),
            'is_perishable' => (int)($_POST['is_perishable'] ?? 0),
            'sale_price'    => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'unit'          => trim($_POST['unit'] ?? ''),
            'stock'         => (int)($_POST['quantity'] ?? 0), // Base main table stock field mirroring inventory
            'image'         => $imageFilename,
            'is_featured'   => (int)($_POST['is_featured'] ?? 0),
            'is_active'     => (int)($_POST['is_active'] ?? 1)
        ];

           

        try {
            // Begin Transaction if your DB wrapper supports it
            
            // Insert into products table and fetch the new primary key ID
            $productId = $this->products->create($productData);
            
            // 🌟 THE CRITICAL SCHEMA FIX: Map 'quantity' precisely to match your inventory table layout!
            $this->inventory->create([
                'product_id' => $productId,
                'quantity'   => (int)($_POST['quantity'] ?? 0), // Fixed from 'stock_qty' -> 'quantity'
            ]);
            
            flash('success', 'Product and matching inventory profile created successfully.');
            redirect(APP_URL . '/admin/products');
            
        } catch (PDOException $e) {
            flash('error', 'Could not save product record data metrics: ' . $e->getMessage());
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
        verifyCsrf(); // Ensure CSRF validation runs

        // 1. Sanitize incoming text parameters
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        
        // 2. Clear out the broken array warnings: check explicitly if it's set
        // If your code was checking $_POST['id'] or session variables, safely handle them:
        $creatorId = $_SESSION['user_id'] ?? null; 

        // 3. Clean up the Parent ID relational logic (Crucial for the FK error)
        // Convert empty selections, zeros, or empty strings cleanly into a database NULL
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' && $_POST['parent_id'] != 0 
                    ? (int)$_POST['parent_id'] 
                    : null;

        if (empty($name)) {
            flash('category_error', 'Category naming identities are mandatory fields.');
            redirect(APP_URL . '/admin/categories');
        }

        try {
            $categoryModel = new CategoryModel();
            
            // Pass a highly organized payload to the execution model layer
            $categoryModel->create([
                'name'       => $name,
                'slug'       => $slug ?: (slugify($name)), // Fallback fallback formatting
                'parent_id'  => $parentId, // Will pass a valid integer or clean NULL
                'created_by' => $creatorId
            ]);

            flash('success', 'Category tracking mapping completed successfully.');
            redirect(APP_URL . '/admin/categories');

        } catch (Throwable $e) {
            flash('category_error', 'Failed to create category matrix: ' . $e->getMessage());
            redirect(APP_URL . '/admin/categories/new');
        }
    }

    public function categoryEdit(int $id)
    {
        $category = $this->categories->findById($id);

        // Guard Clause: Prevent admin updates on non-existent categories
        if (!$category) {
            flash('error', 'The requested database catalog map does not exist.');
            redirect(APP_URL . '/admin/categories');
        }

        // Fetch the rest of the array so the dropdown parent options select properly
        $allCategories = $this->categories->getAllCategories();

        // Set layout parameters and pass values to your admin sidebar layout views wrapper
        $pageTitle = "Edit Category: " . $category['name'];
        
        // Output standard layout configuration variables
        ob_start();
        include __DIR__ . '/../../frontend/views/admin/category-edit.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    public function categoryDelete()
    {
        // 1. Secure the endpoint against cross-site request forgery
        if (function_exists('verifyCsrf')) {
            verifyCsrf(); 
        }

        // 2. Extract and cast the target identification key
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'Invalid category identifier sequence parameters.');
            redirect(APP_URL . '/admin/categories');
        }

        try {
            $categoryModel = new CategoryModel();
            
            // 3. Fire the database deletion sequence
            $success = $categoryModel->delete($id);

            if ($success) {
                flash('success', 'The category record configuration was successfully purged.');
            } else {
                flash('error', 'Failed to remove the category. It may have already been deleted.');
            }

        } catch (Throwable $e) {
            // Handle cases where foreign keys prevent deletion (e.g., items still assigned to this category)
            flash('error', 'Database Constraint Protection: Cannot delete this category while active items or child categories are mapped to it.');
        }

        // 4. Send the administrator back to the refreshed index panel view
        redirect(APP_URL . '/admin/categories');
    }

    public function categoryForm()
    {
        $categoryModel = new CategoryModel();
        $errors = [];
        
        // 1. Determine State: Are we editing an existing row or adding a fresh one?
        // Check both query strings (GET) and hidden inputs (POST)
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        $isEdit = ($id > 0);

        if ($isEdit) {
            // Edit Mode: Fetch the real record from MySQL
            $category = $categoryModel->findById($id);
            if (!$category) {
                http_response_code(404);
                die("Database Error: Category ID #{$id} does not exist.");
            }
        } else {
            // Add Mode: Initialize an empty schema template array
            $category = [
                'id'          => null,
                'name'        => '',
                'slug'        => '',
                'parent_id'   => null,
                'sort_order'  => 0,
                'is_active'   => 1,
                'created_by' => null
            ];
        }

        // 2. Handle Form Submission (POST Data Processing)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            // Capture and scrub incoming payloads
            $name        = trim($_POST['name'] ?? '');
            $slug        = trim($_POST['slug'] ?? '');
            $parent_id   = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $sort_order  = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $is_active   = isset($_POST['is_active']) ? 1 : 0;
            $created_by = !empty($_POST['created_by']) ? (int)$_POST['created_by'] : ($_SESSION['user']['id']) ?? null;

            // Shared Validation Engine Rule
            if (empty($name)) {
                $errors['name'] = 'Category name is strictly required.';
            }

            // Auto-slug generator fallback fallback logic
            if (empty($slug) && !empty($name)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            }

            // Keep form filled with what the user typed if errors occur
            $category = [
                'id'          => $isEdit ? $id : null,
                'name'        => $name,
                'slug'        => $slug,
                'parent_id'   => $parent_id,
                'sort_order'  => $sort_order,
                'is_active'   => $is_active,
                'created_by' => $created_by
            ];

            // 3. Execution Layer if Validation Passes
            if (empty($errors)) {
                if ($isEdit) {
                    // Execute Database UPDATE
                    $saved = $categoryModel->update($id, $category);
                } else {
                    // Execute Database CREATE (MySQL automatically assigns auto-increment ID)
                    $saved = $categoryModel->create($category);
                }

                if ($saved) {
                    // Flash clear state and redirect to index list view
                    header('Location: /grocery-shop/public/admin/categories');
                    exit;
                } else {
                    $errors['global'] = 'Database Write Error: Transaction aborted.';
                }
            }
        }

        // Fetch parent dropdown listings dynamically for the view dropdown matrix
        $parentOptions = $categoryModel->getAll(); 

        ob_start();
        require_once __DIR__ . '/../../frontend/views/admin/category-edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../frontend/views/admin/layout.php';
    }

    // ─────────────────────────────────────────────────────────
    //  Customer Subsystems
    // ─────────────────────────────────────────────────────────

    /** GET /admin/customers */
    public function customersList()
    {
        $this->render('customers', 'Customers — Admin', [
            'customers' => $this->users->getAllCustomersWithAuth()
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

    // ─────────────────────────────────────────────────────────
    //  Administrative User Creation Actions
    // ─────────────────────────────────────────────────────────

/** GET /admin/users */
    public function usersIndex()
    {
        $pageTitle = 'System Users — Admin';
        
        // 1. Fetch your user credentials dataset matrix
        $userModel = new UserModel();
        $users = $userModel->getAllRawAuthUsers(); 
        $users = $users ?? []; 

        // 2. Open buffer tracking pool
        ob_start();
        
        // 3. Load the structural partial view data file
        require __DIR__ . '/../../frontend/views/admin/users.php';
        
        // 4. Capture the view contents directly into the layout's $content parameter
        $content = ob_get_clean();

        // 5. Render the full workspace view frame shell
        require __DIR__ . '/../../frontend/views/admin/layout.php';
        exit;
    }

    /** GET /admin/customers */
    public function customersIndex()
    {
        $this->render('customers', 'Customer Profiles Index', [
            'customers' => $this->users->getAllCustomersWithAuth()
        ]);
    }

    /** GET /admin/customers/new */
    public function customerForm()
    {
        $this->render('customer-form', 'Add New System User — Admin', [
            'error'   => flash('customer_error'),
            'success' => flash('customer_success')
        ]);
    }

    /** POST /admin/customers */
    public function customerCreate()
    {
        // 🔒 Global CSRF security boundary check
        verifyCsrf();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = strtolower(trim($_POST['role'] ?? 'customer'));

        // Basic Validation Guard
        if (empty($name) || empty($email) || empty($password)) {
            flash('customer_error', 'All registration profile parameters are strictly mandatory.');
            redirect(APP_URL . '/admin/customers/new');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('customer_error', 'Please submit a valid electronic communication address.');
            redirect(APP_URL . '/admin/customers/new');
        }

        try {
            // Cryptographic securely hashed protection sequence
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Binding data array matching structure layouts
            $userData = [
                'name'     => $name,
                'email'    => $email,
                'password' => $hashedPassword,
                'role'     => $role === 'admin' ? 'admin' : 'customer'
            ];

            // Execution through database data mapper instance
            $userId = $this->users->create($userData);

            if ($userId) {
                flash('success', "User account profile for '{$name}' generated successfully.");
                redirect(APP_URL . '/admin/customers');
            } else {
                throw new RuntimeException("Database subsystem rejected insert payload mapping.");
            }

        } catch (PDOException $e) {
            // Catching potential unique key violations (e.g., duplicate email records)
            if ((int)$e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                flash('customer_error', 'An account profile matching that email address already exists.');
            } else {
                flash('customer_error', 'Failed saving profile to storage files: ' . $e->getMessage());
            }
            redirect(APP_URL . '/admin/customers/new');
        } catch (Throwable $e) {
            flash('customer_error', $e->getMessage());
            redirect(APP_URL . '/admin/customers/new');
        }
    }

    public function toggleStatus() {
        // 1. Enforce strict session validation (Make sure an admin is logged in)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['admin_logged_in'])) {
            die("Unauthorized access attempt configuration context validation.");
        }

        // 2. Security validation check: Verify CSRF token payload authenticity 
        // (Replace with your actual framework token checking function name if different)
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            die("Security error: Invalid token match payload tracking authorization verification.");
        }

        // 3. Extract and safely process parameters
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $currentStatus = isset($_POST['current_status']) ? intval($_POST['current_status']) : 1;
        
        // Compute inverse value: if active (1) make suspended (0), if suspended (0) make active (1)
        $newStatus = ($currentStatus === 1) ? 0 : 1;

        if ($userId > 0) {
            // 4. Fire the SQL command directly since UserModel does not expose updateStatus
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE users SET is_active = :is_active WHERE id = :id");
            $stmt->execute([
                ':is_active' => $newStatus,
                ':id'        => $userId
            ]);

            // Set a user success feedback message flash state if your app supports it
            $_SESSION['flash_message'] = "User operational lifecycle privileges updated.";
        }

        // 5. Instantly route back to reload your updated table cleanly
        header("Location: " . APP_URL . "/admin/users");
        exit;
    }
}