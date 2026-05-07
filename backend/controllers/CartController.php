<?php
// backend/controllers/CartController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/functions.php';

class CartController {
    private Product $product;

    public function __construct() {
        $this->product = new Product();
    }

    /** GET /cart */
    public function index() {
        $cartItems = $this->buildCartItems();
        $totals    = cartTotals($cartItems);
        require __DIR__ . '/../../frontend/views/pages/cart.php';
    }

    /** POST /cart/add */
    public function add() {
        // Start or get cart session
    $_SESSION['cart'] = $_SESSION['cart'] ?? [];

    // Get and validate input
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity  = (int) ($_POST['quantity'] ?? 1);
    
    if ($productId <= 0 || $quantity <= 0) {
        // Invalid request – redirect back with error
        $_SESSION['flash_error'] = 'Invalid product or quantity.';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? APP_URL . '/shop');
        exit;
    }

    // Fetch product with stock
    $productModel = new ProductModel();
    $product = $productModel->findById($productId);
    
    if (!$product) {
        $_SESSION['flash_error'] = 'Product not found.';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? APP_URL . '/shop');
        exit;
    }

    // Get available stock (the model already aliases as 'stock_qty')
    $availableStock = (int) ($product['stock_qty'] ?? 0);
    
    // Calculate new quantity (capped by available stock)
    $cartKey = 'p_' . $productId;
    $currentQty = (int) ($_SESSION['cart'][$cartKey]['quantity'] ?? 0);
    $newQty = min($currentQty + $quantity, $availableStock);
    
    if ($newQty <= 0) {
        // If quantity would be zero, remove item from cart
        unset($_SESSION['cart'][$cartKey]);
        $_SESSION['flash_info'] = 'Item removed from cart (out of stock).';
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'quantity'   => $newQty,
        ];
        $_SESSION['flash_success'] = 'Product added to cart.';
    }
    
    // Redirect back to previous page or cart page
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/cart'));
    exit;
    }

    /** POST /cart/update */
    public function update() {
        verifyCsrf();
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = (int) ($_POST['quantity']   ?? 0);
        $key       = 'p_' . $productId;

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$key]);
        } else {
            $product = $this->product->findById($productId);
            if ($product) {
                $_SESSION['cart'][$key]['quantity'] = min($quantity, $product['stock']);
            }
        }

        $cartItems = $this->buildCartItems();
        $totals    = cartTotals($cartItems);
        $this->jsonResponse([
            'success'      => true,
            'cart_count'   => cartCount(),
            'subtotal'     => formatPrice($totals['subtotal']),
            'delivery_fee' => formatPrice($totals['delivery_fee']),
            'total'        => formatPrice($totals['total']),
        ]);
    }

    /** POST /cart/remove */
    public function remove() {
        verifyCsrf();
        $key = 'p_' . (int) ($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$key]);
        $totals = cartTotals($this->buildCartItems());
        $this->jsonResponse([
            'success'    => true,
            'cart_count' => cartCount(),
            'total'      => formatPrice($totals['total']),
        ]);
    }

    /** Build enriched cart items by merging session data with DB product data */
    public function buildCartItems(): array {
        $items = [];
        foreach ($_SESSION['cart'] ?? [] as $entry) {
            $product = $this->product->findById($entry['product_id']);
            if ($product) {
                $items[] = array_merge($product, ['quantity' => $entry['quantity']]);
            }
        }
        return $items;
    }

    private function jsonResponse(array $data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
