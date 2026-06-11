<?php
// frontend/views/admin/layout.php
// This master layout controls fluid viewport sizing and encapsulates the main views safely
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin' ?> | GroceryDash Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css?v=<?= time() ?>">

    <style>
        /* 🎨 Clean, Fluid Layout Style Reset overrides */
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #f8fafc;
        }

        /* Fluid Master Grid Framework Container Wrapper */
        .admin-dashboard-layout {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: stretch; /* Enforces sidebar and content heights to always stay aligned */
        }

        /* 👑 High-Performance Sidebar Style Configuration */
        .admin-sidebar-panel {
            width: 260px;          /* Clean fixed desktop width matrix rule */
            flex-shrink: 0;        /* Prevents content layout from compressing navigation items */
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .admin-sidebar-sticky {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;       /* Permits sidebar scroll vectors if nav lists expand */
            padding: 1.5rem 1.25rem;
        }

        /* 🖥️ Responsive Content Workspace Area Panel */
        .admin-main-workspace {
            flex-grow: 1;
            min-width: 0;          /* Critical fix to prevent text data wrappers from breaking layout tables */
            padding: 2.5rem !important;
            background-color: #f8fafc;
        }

        /* Premium Link Interaction Feedback States */
        .nav-pills .nav-link {
            border-radius: 8px;
            padding: 0.625rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            margin-bottom: 0.25rem;
        }
        
        .nav-pills .nav-link:not(.active):hover {
            background-color: #f1f5f9;
            color: #157347 !important;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">

    <div class="admin-dashboard-layout">
        <aside class="admin-sidebar-panel">
            <div class="admin-sidebar-sticky">
                
                <div class="admin-logo-branding text-center mb-4 pb-3 border-bottom">
                    <img src="<?= htmlspecialchars($adminLogo ?? APP_URL . '/assets/images/logo.png') ?>" 
                         alt="Website Logo" 
                         class="img-fluid" 
                         style="max-height: 70px; width: auto; object-fit: contain;">
                </div>
                
                <h6 class="text-uppercase text-muted fw-bold px-2 mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">Core Operations</h6>
                
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/dashboard">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'orders') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/orders">
                            <i class="bi bi-box-seam me-2"></i> Orders Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'products') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/products">
                            <i class="bi bi-basket me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'inventory') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/inventory">
                            <i class="bi bi-clipboard-data me-2"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'admin/categories') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/categories">
                            <i class="bi bi-tags me-2"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'admin/slots') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/slots">
                            <i class="bi bi-calendar me-2"></i> Delivery Slots
                        </a>
                    </li>

                    <li class="nav-item mt-4">
                        <h6 class="text-uppercase text-muted fw-bold px-2 mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">Management</h6>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'admin/users') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/users">
                            <i class="bi bi-shield-lock me-2"></i> System Users
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'customers') && !str_contains($_SERVER['REQUEST_URI'], 'admin/users') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/customers">
                            <i class="bi bi-people me-2"></i> Customers
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'settings/logo') ? 'active bg-success text-white' : 'text-secondary' ?>" 
                           href="<?= APP_URL ?>/admin/settings/logo">
                            <i class="bi bi-image me-2"></i> Logo Settings
                        </a>
                    </li>

                    <li class="nav-item mt-auto pt-4 border-top">
                        <a class="nav-link text-danger" href="<?= APP_URL ?>/logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="admin-main-workspace">
            <?= $content; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>