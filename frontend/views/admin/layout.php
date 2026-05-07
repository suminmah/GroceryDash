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
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
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
        </aside>

        <!-- Main Content – where the page-specific content will be injected -->
        <main class="admin-main">
            <?=  $content; ?>
        </main>
    </div>
</body>
</html>