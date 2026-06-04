<?php
// backend/config/app.php

define('APP_NAME',    'GroceryDash');
define('APP_URL',     'http://localhost/grocery-shop/public');
define('APP_VERSION', '1.0.0');

// Delivery settings
define('FREE_DELIVERY_THRESHOLD', 500);   // Rs. 500
define('DELIVERY_FEE',            40);    // Rs. 40

// Session
define('SESSION_NAME',    'grocerydash_sess');
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days

// Pagination
define('PRODUCTS_PER_PAGE', 12);

// Timezone
date_default_timezone_set('Asia/Kathmandu');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
