<?php
// frontend/views/admin/layout.php
// This layout contains the sidebar and wraps the page content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin' ?> | GroceryDash Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <!-- <aside class="admin-sidebar">
            <h3>Admin Menu</h3>
            <ul class="admin-nav">
                <li><a href="<?= APP_URL ?>/admin/dashboard" class="<?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>">📊 Dashboard</a></li>
                <li><a href="<?= APP_URL ?>/admin/orders" class="<?= str_contains($_SERVER['REQUEST_URI'], '/orders') ? 'active' : '' ?>">📦 Orders</a></li>
                <li><a href="<?= APP_URL ?>/admin/products" class="<?= str_contains($_SERVER['REQUEST_URI'], '/products') ? 'active' : '' ?>">🛒 Products</a></li>
                <li><a href="<?= APP_URL ?>/admin/inventory" class="<?= str_contains($_SERVER['REQUEST_URI'], '/inventory') ? 'active' : '' ?>">📦 Inventory</a></li>
                <li><a href="<?= APP_URL ?>/admin/categories" class="<?= str_contains($_SERVER['REQUEST_URI'], '/categories') ? 'active' : '' ?>">🏷️ Categories</a></li>
                <li><a href="<?= APP_URL ?>/admin/slots" class="<?= str_contains($_SERVER['REQUEST_URI'], '/slots') ? 'active' : '' ?>">⏰ Delivery Slots</a></li>
                <li><a href="<?= APP_URL ?>/admin/customers" class="<?= str_contains($_SERVER['REQUEST_URI'], '/customers') ? 'active' : '' ?>">👥 Customers</a></li>
                <li class="logout-item"><a href="<?= APP_URL ?>/logout">🚪 Logout</a></li>
            </ul>
        </aside> -->

        <aside class="col-md-3 col-lg-2 bg-light vh-100 p-3">
            <h3 class="text-success mb-4">Admin Menu</h3>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/dashboard">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'orders') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/orders">
                        <i class="bi bi-box-seam"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'products') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/products">
                        <i class="bi bi-basket"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'inventory') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/inventory">
                        <i class="bi bi-clipboard-data"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'categories') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/categories">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'slots') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/slots">
                        <i class="bi bi-calendar-clock"></i> Delivery Slots
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'customers') ? 'active bg-success text-white' : 'text-dark' ?>" 
                       href="<?= APP_URL ?>/admin/customers">
                        <i class="bi bi-people"></i> Customers
                    </a>
                </li>
                <li class="nav-item mt-3 pt-3 border-top">
                    <a class="nav-link text-danger" href="<?= APP_URL ?>/logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content – where the page-specific content will be injected -->
        <main class="admin-main">
            <?=  $content; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>