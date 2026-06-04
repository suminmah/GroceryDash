<?php
// public/index.php — Front Controller / Router
require_once __DIR__ . '/../backend/config/app.php';
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/functions.php';
require_once __DIR__ . '/../backend/controllers/ShopController.php';
require_once __DIR__ . '/../backend/controllers/CheckoutController.php';
require_once __DIR__ . '/../backend/controllers/AdminController.php';
require_once __DIR__ . '/../backend/controllers/AuthController.php';
require_once __DIR__ . '/../backend/controllers/OrderController.php';
require_once __DIR__ . '/../backend/models/Category.php';
require_once __DIR__ . '/../backend/controllers/WishlistController.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_name(SESSION_NAME);
session_start();

if (function_exists('csrfToken')) {
    csrfToken(); // Ensure CSRF token is generated for the session
}


$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = '/grocery-shop/public';

if (str_starts_with($uri, $base)) { 
    $uri = substr($uri, strlen($base)); 
}
$uri = '/' . trim($uri, '/');

function match_route(string $uri, string $pattern, array &$params = []): bool {
    $parts    = explode('/', trim($pattern, '/'));
    $uriParts = explode('/', trim($uri, '/'));
    if (count($parts) !== count($uriParts)) return false;
    $params = [];
    foreach ($parts as $i => $p) {
        if (str_starts_with($p, '{') && str_ends_with($p, '}')) { $params[trim($p, '{}')] = $uriParts[$i]; }
        elseif ($p !== $uriParts[$i]) { return false; }
    }
    return true;
}

function calcTotals(array $cartItems): array {
    $subtotal = 0.0;
    foreach ($cartItems as $item) { $subtotal += (float) $item['price'] * (int) $item['quantity']; }
    $fee = $subtotal >= FREE_DELIVERY_THRESHOLD ? 0.0 : (float) DELIVERY_FEE;
    return ['subtotal' => $subtotal, 'delivery_fee' => $fee, 'total' => $subtotal + $fee];
}

$params = [];

if ($method === 'GET') {
    if ($uri === '/' || $uri === '')                                  { (new ShopController())->home(); }
    elseif ($uri === '/shop')                                         { (new ShopController())->shop(); }
    elseif (match_route($uri, '/product/{id}', $params))             { (new ShopController())->detail((int) $params['id']); }
    elseif (match_route($uri, '/category/{id}', $params))            { (new ShopController())->category((int) $params['id']); }
    elseif ($uri === '/search')                                       { (new ShopController())->search(); }
    elseif ($uri === '/cart') {
        require_once __DIR__ . '/../backend/models/ProductModel.php';
        $pm = new ProductModel(); $cartItems = [];
        foreach ($_SESSION['cart'] ?? [] as $entry) {
            $p = $pm->findById((int) $entry['product_id']);
            if ($p) $cartItems[] = array_merge($p, ['quantity' => $entry['quantity']]);
        }
        $totals = calcTotals($cartItems); $pageTitle = 'Your Cart — GroceryDash';
        require __DIR__ . '/../frontend/views/pages/cart.php';
    }
    elseif ($uri === '/offers')                                         { (new ShopController())->offers(); }
    elseif ($uri === '/checkout')                                     { (new CheckoutController())->form(); }
    elseif (match_route($uri, '/order/confirmation/{id}', $params))  { (new CheckoutController())->confirmation((int) $params['id']); }
    elseif (match_route($uri, '/order/track/{id}', $params)) {
    // Dynamically instantiate your order processing layer
    require_once __DIR__ . '/../backend/controllers/OrderController.php';
    (new OrderController())->track($params['id']);
    }
    elseif ($uri === '/account/orders')                               { (new CheckoutController())->myOrders(); }
    elseif (match_route($uri, '/account/orders/{id}', $params))      { (new OrderController())->orderDetail((int) $params['id']); }
    elseif ($uri === '/login')                                        { (new AuthController())->loginForm(); }
    elseif ($uri === '/register')                                     { (new AuthController())->registerForm(); }
    elseif ($uri === '/logout')                                       { (new AuthController())->logout(); }
    elseif ($uri === '/delivery')                                     { require __DIR__ . '/../frontend/views/pages/delivery.php'; }
    elseif ($uri === '/about')                                        { require __DIR__ . '/../frontend/views/pages/about.php'; }
    elseif ($uri === '/help')                                         { require __DIR__ . '/../frontend/views/pages/help.php'; }
    elseif (in_array($uri, ['/admin', '/admin/dashboard']))           { (new AdminController())->dashboard(); }
    elseif ($uri === '/admin/orders')                                 { (new AdminController())->ordersList(); }
    elseif (match_route($uri, '/admin/orders/{id}', $params))        { (new AdminController())->orderDetail((int) $params['id']); }
    elseif ($uri === '/admin/products')                               { (new AdminController())->productsList(); }
    elseif ($uri === '/admin/products/new')                           { (new AdminController())->productForm(); }
    elseif (match_route($uri, '/admin/products/{id}/edit', $params)) { (new AdminController())->productEdit((int) $params['id']); }
    elseif ($uri === '/admin/inventory')                              { (new AdminController())->inventoryList(); }
    elseif (match_route($uri, '/admin/inventory/{id}/restock', $params))   { (new AdminController())->inventoryList(); }
    elseif ($uri === '/admin/slots')                                  { (new AdminController())->slotsList(); }
    elseif ($uri === '/admin/categories')                             { (new AdminController())->categoriesList(); }
    elseif ($uri === '/admin/customers')                              { (new AdminController())->customersList(); }
    elseif ($uri === '/admin/settings/logo')                          { (new AdminController())->logoForm(); }
    elseif ($uri === '/account/wishlist')                               { (new WishlistController())->index(); }
    elseif ($uri === '/csrf-token') {
        header('Content-Type: application/json');
        echo json_encode(['token' => csrfToken()]);
    }
    else { http_response_code(404); require __DIR__ . '/../frontend/views/errors/404.php'; }

} elseif ($method === 'POST') {
    if ($uri === '/login')                                                  { (new AuthController())->login(); }
    elseif ($uri === '/register')                                           { (new AuthController())->register(); }
    elseif ($uri === '/cart/add') { require_once __DIR__ . '/../backend/controllers/CartController.php'; (new CartController())->add(); }
    elseif ($uri === '/cart/update') { require_once __DIR__ . '/../backend/controllers/CartController.php'; (new CartController())->update(); }
    elseif ($uri === '/cart/remove') { require_once __DIR__ . '/../backend/controllers/CartController.php'; (new CartController())->remove(); }
    elseif ($uri === '/checkout')                                           { (new CheckoutController())->place(); }
    elseif (match_route($uri, '/order/cancel/{id}', $params))              { (new CheckoutController())->cancel((int) $params['id']); }
    elseif (match_route($uri, '/admin/orders/{id}/status', $params))       { (new AdminController())->updateOrderStatus((int) $params['id']); }
    elseif (match_route($uri, '/admin/orders/{id}/cancel', $params))       { (new AdminController())->cancelOrder((int) $params['id']); }
    elseif ($uri === '/admin/products')                                     { (new AdminController())->productCreate(); }
    elseif (match_route($uri, '/admin/products/{id}', $params))            { (new AdminController())->productUpdate((int) $params['id']); }
    elseif (match_route($uri, '/admin/inventory/{id}/restock', $params))   { (new AdminController())->restock((int) $params['id']); }
    elseif ($uri === '/admin/slots')                                        { (new AdminController())->slotCreate(); }
    elseif ($uri === '/admin/slots/bulk')                                   { (new AdminController())->slotBulkCreate(); }
    elseif (match_route($uri, '/admin/slots/{id}/delete', $params))        { (new AdminController())->slotDelete((int) $params['id']); }
    elseif ($uri === '/admin/categories')                                   { (new AdminController())->categoryCreate(); }
    elseif ($uri === '/admin/settings/logo')                                { (new AdminController())->updateLogo(); }
    elseif ($uri === '/wishlist/toggle') { (new WishlistController())->toggle(); }
    else { http_response_code(405); echo 'Method not allowed.'; }
} else { http_response_code(405); echo 'Method not allowed.'; }
